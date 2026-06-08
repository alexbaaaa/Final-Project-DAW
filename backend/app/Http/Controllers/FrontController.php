<?php

namespace App\Http\Controllers;

use App\Models\Calendar;
use App\Models\Event;
use App\Models\PortalUser;
use App\Models\Swimmer;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class FrontController extends Controller
{
    public function users(): JsonResponse
    {
        $users = PortalUser::query()
            ->orderBy('id')
            ->get(['id', 'alias', 'first_name', 'last_name', 'birth_date', 'user_type', 'swimmer_id', 'created_at'])
            ->map(fn (PortalUser $user): array => $this->serializeUser($user));

        return response()->json([
            'data' => $users,
        ]);
    }

    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'alias' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $user = PortalUser::query()
            ->where('alias', $credentials['alias'])
            ->first();

        if (!$user || !Hash::check($credentials['password'], (string) $user->getAttribute('password'))) {
            return response()->json([
                'message' => 'Invalid alias or password.',
            ], 401);
        }

        $swimmers = $this->resolveSwimmers($user);

        return response()->json([
            'data' => [
                'user' => $this->serializeUser($user),
                'swimmer' => $this->serializeSwimmer($swimmers->first()),
                'swimmers' => $swimmers->map(fn (Swimmer $swimmer): array => $this->serializeSwimmer($swimmer))->values(),
            ],
        ]);
    }

    public function appData(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'user_id' => ['required', 'integer', 'min:1'],
            'month' => ['nullable', 'date_format:Y-m'],
        ]);

        $user = PortalUser::query()->findOrFail($validated['user_id']);
        $swimmers = $this->resolveSwimmers($user);
        $categories = $swimmers
            ->pluck('category')
            ->filter()
            ->unique()
            ->values();
        $month = isset($validated['month'])
            ? Carbon::createFromFormat('Y-m', $validated['month'])->startOfMonth()
            : now()->startOfMonth();
        $startDate = $month->copy()->startOfMonth();
        $endDate = $month->copy()->endOfMonth();

        $events = Event::query()
            ->whereBetween('event_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->when($categories->isNotEmpty(), function ($query) use ($categories): void {
                $query->where(function ($nestedQuery) use ($categories): void {
                    $nestedQuery->where('categories', 'like', '%All%');

                    foreach ($categories as $category) {
                        $nestedQuery->orWhere('categories', 'like', '%' . $category . '%');
                    }
                });
            })
            ->orderBy('event_date')
            ->orderBy('id')
            ->get(['id', 'event_name', 'description', 'event_date', 'categories'])
            ->map(fn (Event $event): array => [
                'id' => (int) $event->id,
                'event_name' => (string) $event->event_name,
                'description' => (string) $event->description,
                'event_date' => (string) $event->event_date,
                'categories' => (string) $event->categories,
            ]);

        $calendarDays = Calendar::query()
            ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
            ->orderBy('date')
            ->get(['id', 'date', 'day_type', 'title', 'description'])
            ->map(fn (Calendar $calendarDay): array => [
                'id' => (int) $calendarDay->id,
                'date' => $calendarDay->date->toDateString(),
                'day_type' => (string) $calendarDay->day_type,
                'title' => $calendarDay->title,
                'description' => $calendarDay->description,
            ]);

        return response()->json([
            'data' => [
                'month' => $month->format('Y-m'),
                'user' => $this->serializeUser($user),
                'swimmer' => $this->serializeSwimmer($swimmers->first()),
                'swimmers' => $swimmers->map(fn (Swimmer $swimmer): array => $this->serializeSwimmer($swimmer))->values(),
                'events' => $events,
                'calendar' => $calendarDays,
            ],
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function serializeUser(PortalUser $user): array
    {
        return [
            'id' => (int) $user->id,
            'alias' => (string) $user->alias,
            'first_name' => (string) $user->first_name,
            'last_name' => (string) $user->last_name,
            'full_name' => trim($user->first_name . ' ' . $user->last_name),
            'birth_date' => $user->birth_date?->toDateString(),
            'user_type' => (string) $user->user_type,
            'swimmer_id' => $user->swimmer_id !== null ? (int) $user->swimmer_id : null,
            'swimmer_ids' => $this->linkedSwimmerIds($user),
            'created_at' => $user->created_at?->toISOString(),
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    private function serializeSwimmer(?Swimmer $swimmer): ?array
    {
        if (!$swimmer) {
            return null;
        }

        return [
            'id' => (int) $swimmer->id,
            'first_name' => (string) $swimmer->first_name,
            'last_name' => (string) $swimmer->last_name,
            'full_name' => trim($swimmer->first_name . ' ' . $swimmer->last_name),
            'birth_date' => $swimmer->birth_date?->toDateString(),
            'age' => $swimmer->age,
            'category' => (string) $swimmer->category,
            'gender' => (string) $swimmer->gender,
        ];
    }

    /**
     * @return Collection<int, Swimmer>
     */
    private function resolveSwimmers(PortalUser $user): Collection
    {
        $swimmerIds = $this->linkedSwimmerIds($user);

        if ($user->swimmer_id !== null) {
            $swimmerIds[] = (int) $user->swimmer_id;
        }

        $swimmerIds = array_values(array_unique($swimmerIds));
        $swimmers = empty($swimmerIds)
            ? collect()
            : Swimmer::query()
                ->whereIn('id', $swimmerIds)
                ->orderBy('first_name')
                ->orderBy('last_name')
                ->get();

        $matchedSwimmer = Swimmer::query()
            ->where('first_name', $user->first_name)
            ->where('last_name', $user->last_name)
            ->first();

        if ($matchedSwimmer && !$swimmers->contains('id', $matchedSwimmer->id)) {
            $swimmers->push($matchedSwimmer);
        }

        return $swimmers->values();
    }

    /**
     * @return array<int, int>
     */
    private function linkedSwimmerIds(PortalUser $user): array
    {
        return DB::connection('auth_pgsql')
            ->table('portal_user_swimmers')
            ->where('user_id', $user->id)
            ->pluck('swimmer_id')
            ->map(fn ($swimmerId): int => (int) $swimmerId)
            ->all();
    }
}

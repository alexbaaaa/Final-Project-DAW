<?php

namespace App\Http\Controllers;

use App\Models\Calendar;
use App\Models\Event;
use App\Models\PortalUser;
use App\Models\Swimmer;
use App\Models\Time;
use App\Models\TrainingGroup;
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
            ->get(['id', 'alias', 'first_name', 'last_name', 'birth_date', 'user_type', 'swimmer_id', 'is_enabled', 'must_change_password', 'created_at'])
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

        if (!$user->is_enabled) {
            return response()->json([
                'message' => 'This account is disabled until the swimmer is at least 16 years old.',
            ], 403);
        }

        $swimmers = $this->resolveSwimmers($user);

        return response()->json([
            'data' => [
                'user' => $this->serializeUser($user),
                'requires_password_change' => (bool) $user->must_change_password,
                'swimmer' => $this->serializeSwimmer($swimmers->first()),
                'swimmers' => $swimmers->map(fn (Swimmer $swimmer): array => $this->serializeSwimmer($swimmer))->values(),
            ],
        ]);
    }

    public function changePassword(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'user_id' => ['required', 'integer', 'min:1'],
            'current_password' => ['required', 'string'],
            'password' => [
                'required',
                'confirmed',
                'string',
                'min:8',
                'regex:/[A-Z]/',
                'regex:/[^A-Za-z0-9]/',
            ],
        ]);

        $user = PortalUser::query()->findOrFail($payload['user_id']);

        if (!$user->is_enabled) {
            return response()->json([
                'message' => 'This account is disabled until the swimmer is at least 16 years old.',
            ], 403);
        }

        if (!Hash::check($payload['current_password'], (string) $user->getAttribute('password'))) {
            return response()->json([
                'message' => 'Current password is not valid.',
            ], 401);
        }

        $user->forceFill([
            'password' => $payload['password'],
            'must_change_password' => false,
        ])->save();

        $swimmers = $this->resolveSwimmers($user);

        return response()->json([
            'data' => [
                'user' => $this->serializeUser($user->refresh()),
                'requires_password_change' => false,
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
            'swimmer_id' => ['nullable', 'integer', 'min:1'],
        ]);

        $user = PortalUser::query()->findOrFail($validated['user_id']);

        if (!$user->is_enabled) {
            return response()->json([
                'message' => 'This account is disabled until the swimmer is at least 16 years old.',
            ], 423);
        }

        if ($user->must_change_password) {
            return response()->json([
                'message' => 'Password change is required before accessing the application.',
            ], 403);
        }

        $swimmers = $this->resolveSwimmers($user);
        $selectedSwimmer = null;

        if (isset($validated['swimmer_id'])) {
            $selectedSwimmer = $swimmers->firstWhere('id', (int) $validated['swimmer_id']);

            if (!$selectedSwimmer) {
                return response()->json([
                    'message' => 'Selected swimmer is not linked to this user.',
                ], 403);
            }
        }

        $profileSwimmer = $selectedSwimmer ?? $swimmers->first();
        $activeSwimmers = $selectedSwimmer ? collect([$selectedSwimmer]) : $swimmers;
        $categories = $activeSwimmers
            ->pluck('category')
            ->filter()
            ->unique()
            ->values();
        $month = isset($validated['month'])
            ? Carbon::createFromFormat('Y-m', $validated['month'])->startOfMonth()
            : now()->startOfMonth();
        $startDate = $month->copy()->startOfMonth();
        $endDate = $month->copy()->endOfMonth();
        $weekStartDate = now()->startOfWeek(Carbon::MONDAY);
        $weekEndDate = now()->endOfWeek(Carbon::SUNDAY);
        $queryStartDate = $startDate->lessThan($weekStartDate) ? $startDate->copy() : $weekStartDate->copy();
        $queryEndDate = $endDate->greaterThan($weekEndDate) ? $endDate->copy() : $weekEndDate->copy();
        $trainingGroup = $this->resolveTrainingGroup($profileSwimmer);

        $events = Event::query()
            ->whereRaw('COALESCE(event_start_date, event_date) <= ?', [$queryEndDate->toDateString()])
            ->whereRaw('COALESCE(event_end_date, event_start_date, event_date) >= ?', [$queryStartDate->toDateString()])
            ->when($categories->isNotEmpty(), function ($query) use ($categories): void {
                $query->where(function ($nestedQuery) use ($categories): void {
                    $nestedQuery->where('categories', 'like', '%All%');

                    foreach ($categories as $category) {
                        $nestedQuery->orWhere('categories', 'like', '%' . $category . '%');
                    }
                });
            })
            ->orderByRaw('COALESCE(event_start_date, event_date)')
            ->orderBy('id')
            ->get(['id', 'event_name', 'description', 'event_date', 'event_start_date', 'event_end_date', 'event_type', 'categories', 'day_scope', 'created_at'])
            ->map(fn (Event $event): array => [
                'id' => (int) $event->id,
                'event_name' => (string) $event->event_name,
                'description' => (string) $event->description,
                'event_date' => $event->event_date?->toDateString(),
                'event_start_date' => $event->event_start_date?->toDateString() ?? $event->event_date?->toDateString(),
                'event_end_date' => $event->event_end_date?->toDateString() ?? $event->event_start_date?->toDateString() ?? $event->event_date?->toDateString(),
                'event_type' => (string) ($event->event_type ?? 'event'),
                'categories' => (string) $event->categories,
                'day_scope' => (string) ($event->day_scope ?? 'full_day'),
                'created_at' => $event->created_at?->toISOString(),
            ]);

        $calendarDays = Calendar::query()
            ->whereBetween('date', [$queryStartDate->toDateString(), $queryEndDate->toDateString()])
            ->when($categories->isNotEmpty(), function ($query) use ($categories): void {
                $query->where(function ($nestedQuery) use ($categories): void {
                    $nestedQuery->where('categories', 'like', '%All%');

                    foreach ($categories as $category) {
                        $nestedQuery->orWhere('categories', 'like', '%' . $category . '%');
                    }
                });
            })
            ->orderBy('date')
            ->get(['id', 'date', 'day_type', 'event_id', 'categories', 'day_scope', 'title', 'description'])
            ->map(fn (Calendar $calendarDay): array => [
                'id' => (int) $calendarDay->id,
                'date' => $calendarDay->date->toDateString(),
                'day_type' => (string) $calendarDay->day_type,
                'event_id' => $calendarDay->event_id ? (int) $calendarDay->event_id : null,
                'categories' => (string) ($calendarDay->categories ?? 'All'),
                'day_scope' => (string) ($calendarDay->day_scope ?? 'full_day'),
                'title' => $calendarDay->title,
                'description' => $calendarDay->description,
            ]);

        $times = $profileSwimmer
            ? Time::query()
                ->where('swimmer_id', $profileSwimmer->id)
                ->orderByDesc('date')
                ->orderByDesc('id')
                ->get(['id', 'swimmer_id', 'test_type', 'time', 'date', 'location'])
                ->map(fn (Time $time): array => [
                    'id' => (int) $time->id,
                    'swimmer_id' => (int) $time->swimmer_id,
                    'test_type' => (string) $time->test_type,
                    'time' => (string) $time->time,
                    'date' => $time->date?->toDateString(),
                    'location' => (string) $time->location,
                ])
            : collect();

        return response()->json([
            'data' => [
                'month' => $month->format('Y-m'),
                'user' => $this->serializeUser($user),
                'selected_swimmer_id' => $selectedSwimmer ? (int) $selectedSwimmer->id : null,
                'swimmer' => $this->serializeSwimmer($profileSwimmer),
                'swimmers' => $swimmers->map(fn (Swimmer $swimmer): array => $this->serializeSwimmer($swimmer))->values(),
                'events' => $events,
                'calendar' => $calendarDays,
                'times' => $times,
                'training_group' => $this->serializeTrainingGroup($trainingGroup),
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
            'swimmer_id' => $user->user_type !== 'other' && $user->swimmer_id !== null ? (int) $user->swimmer_id : null,
            'swimmer_ids' => $user->user_type === 'other' ? [] : $this->linkedSwimmerIds($user),
            'is_enabled' => (bool) $user->is_enabled,
            'must_change_password' => (bool) $user->must_change_password,
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
     * @return array<string, mixed>|null
     */
    private function serializeTrainingGroup(?TrainingGroup $trainingGroup): ?array
    {
        if (!$trainingGroup) {
            return null;
        }

        return [
            'id' => (int) $trainingGroup->id,
            'name' => (string) $trainingGroup->name,
            'categories' => (string) $trainingGroup->categories,
            'schedule' => is_array($trainingGroup->schedule) ? $trainingGroup->schedule : [],
            'formatted_schedule' => $trainingGroup->formattedSchedule(),
        ];
    }

    /**
     * @return Collection<int, Swimmer>
     */
    private function resolveSwimmers(PortalUser $user): Collection
    {
        if ($user->user_type === 'other') {
            return collect();
        }

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

        if ($user->user_type === 'swimmer') {
            $matchedSwimmer = Swimmer::query()
                ->where('first_name', $user->first_name)
                ->where('last_name', $user->last_name)
                ->first();

            if ($matchedSwimmer && !$swimmers->contains('id', $matchedSwimmer->id)) {
                $swimmers->push($matchedSwimmer);
            }
        }

        return $swimmers->values();
    }

    private function resolveTrainingGroup(?Swimmer $swimmer): ?TrainingGroup
    {
        $category = trim((string) ($swimmer?->category ?? ''));

        if ($category === '') {
            return null;
        }

        return TrainingGroup::query()
            ->where(function ($query) use ($category): void {
                $query->where('categories', 'like', '%All%')
                    ->orWhere('categories', 'like', '%' . $category . '%');
            })
            ->orderBy('id')
            ->get()
            ->first(fn (TrainingGroup $trainingGroup): bool => $this->trainingGroupContainsCategory($trainingGroup, $category));
    }

    private function trainingGroupContainsCategory(TrainingGroup $trainingGroup, string $category): bool
    {
        $categories = array_filter(array_map('trim', explode(',', (string) $trainingGroup->categories)));

        return in_array('All', $categories, true) || in_array($category, $categories, true);
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

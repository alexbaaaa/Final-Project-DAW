<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class EventController extends Controller
{
    private const EVENT_TYPES = [
        'competition' => 'Competition',
        'trophy' => 'Trofeo',
        'championship' => 'Campeonato',
        'event' => 'Event',
    ];

    private const DAY_SCOPES = [
        'full_day' => 'Full day',
        'morning' => 'Morning only',
        'afternoon' => 'Afternoon only',
    ];

    private const CATEGORIES = [
        'All',
        'Prebenjamin',
        'Benjamin',
        'Alevin',
        'Infantil',
        'Junior',
        'Absoluto Joven',
        'Absoluto',
        'Master',
    ];

    public function index(): View
    {
        $events = Event::query()
            ->orderBy('id')
            ->get(['id', 'event_name', 'description', 'event_date', 'event_start_date', 'event_end_date', 'event_type', 'categories', 'day_scope']);

        return view('events.index', [
            'events' => $events,
            'categoryOptions' => self::CATEGORIES,
            'dayScopeLabels' => self::DAY_SCOPES,
            'eventTypeLabels' => self::EVENT_TYPES,
        ]);
    }

    public function create(): View
    {
        return view('events.create', [
            'categoryOptions' => self::CATEGORIES,
            'dayScopeLabels' => self::DAY_SCOPES,
            'eventTypeLabels' => self::EVENT_TYPES,
            'selectedCategories' => [],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Event::query()->create($this->validatedPayload($request));

        return redirect()
            ->route('admin.events.index')
            ->with('status_success', 'Event created successfully.');
    }

    public function show(int $event): View
    {
        return view('events.show', [
            'event' => Event::query()->findOrFail($event),
            'categoryOptions' => self::CATEGORIES,
            'dayScopeLabels' => self::DAY_SCOPES,
            'eventTypeLabels' => self::EVENT_TYPES,
        ]);
    }

    public function edit(int $event): View
    {
        return view('events.edit', [
            'event' => Event::query()->findOrFail($event),
            'categoryOptions' => self::CATEGORIES,
            'dayScopeLabels' => self::DAY_SCOPES,
            'eventTypeLabels' => self::EVENT_TYPES,
        ]);
    }

    public function update(Request $request, int $event): RedirectResponse
    {
        $record = Event::query()->findOrFail($event);
        $record->fill($this->validatedPayload($request));
        $record->save();

        return redirect()
            ->route('admin.events.edit', $event)
            ->with('status_success', 'Event updated successfully.');
    }

    public function destroy(int $event): RedirectResponse
    {
        Event::query()->findOrFail($event)->delete();

        return redirect()
            ->route('admin.events.index')
            ->with('status_success', "Event #{$event} deleted successfully.");
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedPayload(Request $request): array
    {
        $validated = $request->validate([
            'event_name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'event_type' => ['required', 'string', Rule::in(array_keys(self::EVENT_TYPES))],
            'event_start_date' => ['required', 'date'],
            'event_end_date' => ['nullable', 'date', 'after_or_equal:event_start_date'],
            'day_scope' => ['required', 'string', Rule::in(array_keys(self::DAY_SCOPES))],
            'categories' => ['required', 'array', 'min:1'],
            'categories.*' => ['required', 'string', Rule::in(self::CATEGORIES)],
        ]);

        $validated['event_end_date'] = $validated['event_end_date'] ?? $validated['event_start_date'];
        $validated['event_date'] = $validated['event_start_date'];
        $validated['categories'] = $this->normalizeCategories($validated['categories']);

        return $validated;
    }

    /**
     * @param array<int, string> $categories
     */
    private function normalizeCategories(array $categories): string
    {
        $categories = array_values(array_unique($categories));

        if (in_array('All', $categories, true)) {
            return 'All';
        }

        $orderedCategories = array_values(array_filter(
            self::CATEGORIES,
            fn (string $category): bool => $category !== 'All' && in_array($category, $categories, true)
        ));

        if (count($orderedCategories) === count($this->concreteCategories())) {
            return 'All';
        }

        return implode(', ', $orderedCategories);
    }

    /**
     * @return array<int, string>
     */
    private function concreteCategories(): array
    {
        return array_values(array_filter(
            self::CATEGORIES,
            fn (string $category): bool => $category !== 'All'
        ));
    }
}

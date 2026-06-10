<?php

namespace App\Http\Controllers;

use App\Models\Calendar;
use App\Models\Event;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CalendarController extends Controller
{
    /**
     * @var array<string, string>
     */
    private const DAY_TYPES = [
        'holiday_training' => 'Holiday with training',
        'holiday_no_training' => 'Holiday without training',
        'training_suspended' => 'Training suspended',
        'registration_deadline' => 'Registration deadline',
        'schedule_change' => 'Schedule change',
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
        $calendarDays = Calendar::query()
            ->with('event:id,event_name,event_date,event_start_date,event_end_date')
            ->orderByDesc('date')
            ->get(['id', 'date', 'day_type', 'event_id', 'categories', 'day_scope', 'title', 'description']);

        return view('calendar.index', [
            'calendarDays' => $calendarDays,
            'categoryOptions' => self::CATEGORIES,
            'dayScopeLabels' => self::DAY_SCOPES,
            'dayTypeLabels' => self::DAY_TYPES,
        ]);
    }

    public function create(): View
    {
        return view('calendar.create', [
            'categoryOptions' => self::CATEGORIES,
            'dayScopeLabels' => self::DAY_SCOPES,
            'dayTypeLabels' => self::DAY_TYPES,
            'eventOptions' => $this->eventOptions(),
            'selectedCategories' => [],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Calendar::query()->create($this->validatedPayload($request));

        return redirect()
            ->route('admin.calendar.index')
            ->with('status_success', 'Calendar day created successfully.');
    }

    public function show(int $calendar): View
    {
        return view('calendar.show', [
            'calendarDay' => Calendar::query()->with('event')->findOrFail($calendar),
            'categoryOptions' => self::CATEGORIES,
            'dayScopeLabels' => self::DAY_SCOPES,
            'dayTypeLabels' => self::DAY_TYPES,
        ]);
    }

    public function edit(int $calendar): View
    {
        return view('calendar.edit', [
            'calendarDay' => Calendar::query()->with('event')->findOrFail($calendar),
            'categoryOptions' => self::CATEGORIES,
            'dayScopeLabels' => self::DAY_SCOPES,
            'dayTypeLabels' => self::DAY_TYPES,
            'eventOptions' => $this->eventOptions(),
        ]);
    }

    public function update(Request $request, int $calendar): RedirectResponse
    {
        $record = Calendar::query()->findOrFail($calendar);
        $record->fill($this->validatedPayload($request, $calendar));
        $record->save();

        return redirect()
            ->route('admin.calendar.edit', $calendar)
            ->with('status_success', 'Calendar day updated successfully.');
    }

    public function destroy(int $calendar): RedirectResponse
    {
        Calendar::query()->findOrFail($calendar)->delete();

        return redirect()
            ->route('admin.calendar.index')
            ->with('status_success', "Calendar day #{$calendar} deleted successfully.");
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedPayload(Request $request, ?int $calendar = null): array
    {
        $validated = $request->validate([
            'date' => [
                'required',
                'date',
                Rule::unique('calendar', 'date')->ignore($calendar),
            ],
            'day_type' => ['required', 'string', Rule::in(array_keys(self::DAY_TYPES))],
            'event_id' => [
                Rule::requiredIf($request->input('day_type') === 'registration_deadline'),
                'nullable',
                'integer',
                Rule::exists('events', 'id'),
            ],
            'categories' => ['required', 'array', 'min:1'],
            'categories.*' => ['required', 'string', Rule::in(self::CATEGORIES)],
            'day_scope' => ['required', 'string', Rule::in(array_keys(self::DAY_SCOPES))],
            'title' => ['nullable', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
        ]);

        if ($validated['day_type'] !== 'registration_deadline') {
            $validated['event_id'] = null;
        }

        $validated['categories'] = $this->normalizeCategories($validated['categories']);

        return $validated;
    }

    private function eventOptions()
    {
        return Event::query()
            ->orderBy('event_start_date')
            ->orderBy('event_date')
            ->orderBy('event_name')
            ->get(['id', 'event_name', 'event_date', 'event_start_date', 'event_end_date']);
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

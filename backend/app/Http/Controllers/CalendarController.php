<?php

namespace App\Http\Controllers;

use App\Models\Calendar;
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
    ];

    public function index(): View
    {
        $calendarDays = Calendar::query()
            ->orderByDesc('date')
            ->get(['id', 'date', 'day_type', 'title', 'description']);

        return view('calendar.index', [
            'calendarDays' => $calendarDays,
            'dayTypeLabels' => self::DAY_TYPES,
        ]);
    }

    public function create(): View
    {
        return view('calendar.create', [
            'dayTypeLabels' => self::DAY_TYPES,
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
            'calendarDay' => Calendar::query()->findOrFail($calendar),
            'dayTypeLabels' => self::DAY_TYPES,
        ]);
    }

    public function edit(int $calendar): View
    {
        return view('calendar.edit', [
            'calendarDay' => Calendar::query()->findOrFail($calendar),
            'dayTypeLabels' => self::DAY_TYPES,
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
        return $request->validate([
            'date' => [
                'required',
                'date',
                Rule::unique('calendar', 'date')->ignore($calendar),
            ],
            'day_type' => ['required', 'string', Rule::in(array_keys(self::DAY_TYPES))],
            'title' => ['nullable', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
        ]);
    }
}

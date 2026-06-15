<?php

namespace App\Http\Controllers;

use App\Models\TrainingGroup;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class TrainingGroupController extends Controller
{
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
        $trainingGroups = TrainingGroup::query()
            ->orderBy('id')
            ->get(['id', 'name', 'categories']);

        return view('training-groups.index', [
            'categoryOptions' => self::CATEGORIES,
            'trainingGroups' => $trainingGroups,
        ]);
    }

    public function create(): View
    {
        return view('training-groups.create', [
            'categoryOptions' => self::CATEGORIES,
            'dayOptions' => $this->dayOptions(),
            'selectedCategories' => [],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        TrainingGroup::query()->create($this->validatedPayload($request));

        return redirect()
            ->route('admin.training_groups.index')
            ->with('status_success', 'Training group created successfully.');
    }

    public function show(int $trainingGroup): View
    {
        return view('training-groups.show', [
            'categoryOptions' => self::CATEGORIES,
            'trainingGroup' => TrainingGroup::query()->findOrFail($trainingGroup),
        ]);
    }

    public function edit(int $trainingGroup): View
    {
        return view('training-groups.edit', [
            'categoryOptions' => self::CATEGORIES,
            'dayOptions' => $this->dayOptions(),
            'trainingGroup' => TrainingGroup::query()->findOrFail($trainingGroup),
        ]);
    }

    public function update(Request $request, int $trainingGroup): RedirectResponse
    {
        $record = TrainingGroup::query()->findOrFail($trainingGroup);
        $record->fill($this->validatedPayload($request));
        $record->save();

        return redirect()
            ->route('admin.training_groups.edit', $trainingGroup)
            ->with('status_success', 'Training group updated successfully.');
    }

    public function destroy(int $trainingGroup): RedirectResponse
    {
        TrainingGroup::query()->findOrFail($trainingGroup)->delete();

        return redirect()
            ->route('admin.training_groups.index')
            ->with('status_success', "Training group #{$trainingGroup} deleted successfully.");
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedPayload(Request $request): array
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'categories' => ['required', 'array', 'min:1'],
            'categories.*' => ['required', 'string', Rule::in(self::CATEGORIES)],
            'schedule_entries' => ['required', 'string'],
        ]);

        $scheduleEntries = json_decode((string) $validated['schedule_entries'], true);

        if (!is_array($scheduleEntries)) {
            throw ValidationException::withMessages([
                'schedule_entries' => 'Add at least one valid schedule block.',
            ]);
        }

        return [
            'name' => $validated['name'],
            'categories' => $this->normalizeCategories($validated['categories']),
            'schedule' => $this->validatedScheduleEntries($scheduleEntries),
        ];
    }

    /**
     * @param array<int, mixed> $scheduleEntries
     * @return array<int, array{days: array<int, string>, from: string, to: string}>
     */
    private function validatedScheduleEntries(array $scheduleEntries): array
    {
        $allowedDays = array_keys(TrainingGroup::dayDefinitions());

        Validator::make(['schedule_entries' => $scheduleEntries], [
            'schedule_entries' => ['required', 'array', 'min:1'],
            'schedule_entries.*.days' => ['required', 'array', 'min:1'],
            'schedule_entries.*.days.*' => ['required', 'string', Rule::in($allowedDays)],
            'schedule_entries.*.from' => ['required', 'date_format:H:i'],
            'schedule_entries.*.to' => ['required', 'date_format:H:i'],
        ], [
            'schedule_entries.required' => 'Add at least one schedule block.',
            'schedule_entries.min' => 'Add at least one schedule block.',
            'schedule_entries.*.days.required' => 'Select at least one day for each schedule block.',
            'schedule_entries.*.from.required' => 'Enter a start time for each schedule block.',
            'schedule_entries.*.to.required' => 'Enter an end time for each schedule block.',
        ])->validate();

        return array_map(function (array $entry) use ($allowedDays): array {
            if ((string) $entry['from'] >= (string) $entry['to']) {
                throw ValidationException::withMessages([
                    'schedule_entries' => 'Every schedule block must end after it starts.',
                ]);
            }

            return [
                'days' => array_values(array_filter(
                    $allowedDays,
                    fn (string $day): bool => in_array($day, $entry['days'], true)
                )),
                'from' => (string) $entry['from'],
                'to' => (string) $entry['to'],
            ];
        }, $scheduleEntries);
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

    /**
     * @return array<int, array{value: string, short: string, label: string}>
     */
    private function dayOptions(): array
    {
        return array_map(
            fn (string $value, array $definition): array => [
                'value' => $value,
                'short' => $definition['short'],
                'label' => $definition['label'],
            ],
            array_keys(TrainingGroup::dayDefinitions()),
            TrainingGroup::dayDefinitions()
        );
    }
}

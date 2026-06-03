<?php

namespace App\Http\Controllers;

use App\Models\Swimmer;
use App\Models\Time;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SwimmerController extends Controller
{
    /**
     * @var array<int, string>
     */
    private const CATEGORIES = [
        'Prebenjamin',
        'Benjamin',
        'Alevin',
        'Infantil',
        'Junior',
        'Absoluto Joven',
        'Absoluto',
        'Master',
    ];

    /**
     * @var array<int, string>
     */
    private const GENDERS = ['Male', 'Female', 'Other'];

    public function index(): View
    {
        $swimmers = Swimmer::query()
            ->orderBy('id')
            ->get(['id', 'first_name', 'last_name', 'age', 'category', 'gender']);

        return view('swimmers.index', [
            'swimmers' => $swimmers,
        ]);
    }

    public function create(): View
    {
        return view('swimmers.create');
    }

    public function store(Request $request): RedirectResponse
    {
        Swimmer::query()->create($this->validatedPayload($request));

        return redirect()
            ->route('admin.swimmers.index')
            ->with('status_success', 'Swimmer created successfully.');
    }

    public function show(int $swimmer): View
    {
        $record = Swimmer::query()->findOrFail($swimmer);
        $times = Time::query()
            ->where('swimmer_id', $record->id)
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->get(['id', 'test_type', 'time', 'date', 'location']);

        return view('swimmers.show', [
            'swimmer' => $record,
            'times' => $times,
        ]);
    }

    public function edit(int $swimmer): View
    {
        return view('swimmers.edit', [
            'swimmer' => Swimmer::query()->findOrFail($swimmer),
        ]);
    }

    public function update(Request $request, int $swimmer): RedirectResponse
    {
        $record = Swimmer::query()->findOrFail($swimmer);
        $record->fill($this->validatedPayload($request));
        $record->save();

        return redirect()
            ->route('admin.swimmers.edit', $swimmer)
            ->with('status_success', 'Swimmer updated successfully.');
    }

    public function destroy(int $swimmer): RedirectResponse
    {
        Swimmer::query()->findOrFail($swimmer)->delete();

        return redirect()
            ->route('admin.swimmers.index')
            ->with('status_success', "Swimmer #{$swimmer} deleted successfully.");
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedPayload(Request $request): array
    {
        return $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'age' => ['required', 'integer', 'min:1', 'max:120'],
            'category' => ['required', 'string', Rule::in(self::CATEGORIES)],
            'gender' => ['required', 'string', Rule::in(self::GENDERS)],
        ]);
    }
}

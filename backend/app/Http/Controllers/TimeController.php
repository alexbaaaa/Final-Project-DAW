<?php

namespace App\Http\Controllers;

use App\Models\Time;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TimeController extends Controller
{
    public function index(): View
    {
        $times = Time::query()
            ->orderBy('id')
            ->get(['id', 'swimmer_id', 'test_type', 'time', 'date', 'location']);

        return view('times.index', [
            'times' => $times,
        ]);
    }

    public function create(): View
    {
        return view('times.create');
    }

    public function store(Request $request): RedirectResponse
    {
        Time::query()->create($this->validatedPayload($request));

        return redirect()
            ->route('admin.times.index')
            ->with('status_success', 'Time created successfully.');
    }

    public function show(int $time): View
    {
        return view('times.show', [
            'timeRecord' => Time::query()->findOrFail($time),
        ]);
    }

    public function edit(int $time): View
    {
        return view('times.edit', [
            'timeRecord' => Time::query()->findOrFail($time),
        ]);
    }

    public function update(Request $request, int $time): RedirectResponse
    {
        $record = Time::query()->findOrFail($time);
        $record->fill($this->validatedPayload($request));
        $record->save();

        return redirect()
            ->route('admin.times.edit', $time)
            ->with('status_success', 'Time updated successfully.');
    }

    public function destroy(int $time): RedirectResponse
    {
        Time::query()->findOrFail($time)->delete();

        return redirect()
            ->route('admin.times.index')
            ->with('status_success', "Time #{$time} deleted successfully.");
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedPayload(Request $request): array
    {
        return $request->validate([
            'swimmer_id' => ['required', 'integer', 'min:1', 'exists:swimmers,id'],
            'test_type' => ['required', 'string', 'max:100'],
            'time' => ['required', 'string', 'max:20'],
            'date' => ['required', 'date'],
            'location' => ['required', 'string', 'max:150'],
        ]);
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TimeController extends Controller
{
    public function index(): View
    {
        return view('times.index', [
            'times' => $this->times(),
        ]);
    }

    public function create(): View
    {
        return view('times.create');
    }

    public function store(): RedirectResponse
    {
        return redirect()
            ->route('admin.times.index')
            ->with('status_success', 'Time created successfully (visual demo mode).');
    }

    public function show(int $time): View
    {
        return view('times.show', [
            'timeRecord' => $this->findTime($time),
        ]);
    }

    public function edit(int $time): View
    {
        return view('times.edit', [
            'timeRecord' => $this->findTime($time),
        ]);
    }

    public function update(int $time): RedirectResponse
    {
        return redirect()
            ->route('admin.times.edit', $time)
            ->with('status_success', 'Time updated successfully (visual demo mode).');
    }

    public function destroy(int $time): RedirectResponse
    {
        return redirect()
            ->route('admin.times.index')
            ->with('status_success', "Time #{$time} deleted successfully (visual demo mode).");
    }

    /**
     * @return array<int, array<string, int|string>>
     */
    private function times(): array
    {
        return [
            [
                'id' => 1,
                'swimmer_id' => 1,
                'test_type' => '100m Freestyle',
                'time' => '00:59.44',
                'date' => '2026-05-12',
                'location' => 'Granada Pool',
            ],
            [
                'id' => 2,
                'swimmer_id' => 2,
                'test_type' => '50m Butterfly',
                'time' => '00:31.26',
                'date' => '2026-05-19',
                'location' => 'Sevilla Aquatic Center',
            ],
            [
                'id' => 3,
                'swimmer_id' => 3,
                'test_type' => '200m Backstroke',
                'time' => '02:17.02',
                'date' => '2026-05-23',
                'location' => 'Malaga Sports Complex',
            ],
        ];
    }

    /**
     * @return array<string, int|string>
     */
    private function findTime(int $id): array
    {
        foreach ($this->times() as $time) {
            if ($time['id'] === $id) {
                return $time;
            }
        }

        return $this->times()[0];
    }
}

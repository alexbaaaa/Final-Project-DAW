<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class EventController extends Controller
{
    public function index(): View
    {
        return view('events.index', [
            'events' => $this->events(),
        ]);
    }

    public function create(): View
    {
        return view('events.create');
    }

    public function store(): RedirectResponse
    {
        return redirect()
            ->route('admin.events.index')
            ->with('status_success', 'Event created successfully (visual demo mode).');
    }

    public function show(int $event): View
    {
        return view('events.show', [
            'event' => $this->findEvent($event),
        ]);
    }

    public function edit(int $event): View
    {
        return view('events.edit', [
            'event' => $this->findEvent($event),
        ]);
    }

    public function update(int $event): RedirectResponse
    {
        return redirect()
            ->route('admin.events.edit', $event)
            ->with('status_success', 'Event updated successfully (visual demo mode).');
    }

    public function destroy(int $event): RedirectResponse
    {
        return redirect()
            ->route('admin.events.index')
            ->with('status_success', "Event #{$event} deleted successfully (visual demo mode).");
    }

    /**
     * @return array<int, array<string, int|string>>
     */
    private function events(): array
    {
        return [
            [
                'id' => 1,
                'event_name' => 'Regional Championship',
                'description' => 'Official meet for 50m and 100m tests.',
                'event_date' => '2026-06-08',
                'categories' => 'Junior, Senior',
            ],
            [
                'id' => 2,
                'event_name' => 'City Cup',
                'description' => 'Sprint focused local competition.',
                'event_date' => '2026-07-14',
                'categories' => 'Cadet, Junior',
            ],
            [
                'id' => 3,
                'event_name' => 'Summer Trial Day',
                'description' => 'Internal event for performance tracking.',
                'event_date' => '2026-08-02',
                'categories' => 'All categories',
            ],
        ];
    }

    /**
     * @return array<string, int|string>
     */
    private function findEvent(int $id): array
    {
        foreach ($this->events() as $event) {
            if ($event['id'] === $id) {
                return $event;
            }
        }

        return $this->events()[0];
    }
}

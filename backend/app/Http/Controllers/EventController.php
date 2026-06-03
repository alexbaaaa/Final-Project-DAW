<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EventController extends Controller
{
    public function index(): View
    {
        $events = Event::query()
            ->orderBy('id')
            ->get(['id', 'event_name', 'description', 'event_date', 'categories']);

        return view('events.index', [
            'events' => $events,
        ]);
    }

    public function create(): View
    {
        return view('events.create');
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
        ]);
    }

    public function edit(int $event): View
    {
        return view('events.edit', [
            'event' => Event::query()->findOrFail($event),
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
        return $request->validate([
            'event_name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'event_date' => ['required', 'date'],
            'categories' => ['required', 'string', 'max:255'],
        ]);
    }
}

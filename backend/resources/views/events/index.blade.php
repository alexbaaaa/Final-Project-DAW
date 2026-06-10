@extends('app.back')

@section('title', 'Events')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
        <div>
            <h1 class="h3 admin-page-title mb-1">Events</h1>
            <p class="admin-subtle mb-0">Manage event records.</p>
        </div>
        <a href="{{ route('admin.events.create') }}" class="btn admin-btn-primary">Create Event</a>
    </div>

    <div class="card admin-card shadow-sm">
        <div class="table-responsive">
            <table class="table admin-table align-middle mb-0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Event Name</th>
                        <th>Type</th>
                        <th>Description</th>
                        <th>Dates</th>
                        <th>Day Scope</th>
                        <th>Categories</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($events as $event)
                        @php
                            $eventCategories = array_filter(array_map('trim', explode(',', (string) $event['categories'])));
                            $concreteCategories = array_values(array_filter($categoryOptions, fn ($category) => $category !== 'All'));

                            if (in_array('All', $eventCategories, true) || count(array_diff($concreteCategories, $eventCategories)) === 0) {
                                $eventCategories = ['All'];
                            }

                            $startDate = $event->event_start_date?->toDateString() ?? $event->event_date?->toDateString();
                            $endDate = $event->event_end_date?->toDateString() ?? $startDate;
                        @endphp
                        <tr>
                            <td>{{ $event['id'] }}</td>
                            <td>{{ $event['event_name'] }}</td>
                            <td>{{ $eventTypeLabels[$event['event_type']] ?? $event['event_type'] ?? 'Event' }}</td>
                            <td>{{ $event['description'] ?? 'N/A' }}</td>
                            <td>{{ $startDate === $endDate ? $startDate : $startDate.' - '.$endDate }}</td>
                            <td>{{ $dayScopeLabels[$event['day_scope']] ?? $event['day_scope'] ?? 'Full day' }}</td>
                            <td>
                                <div class="d-flex flex-wrap gap-1">
                                    @foreach($eventCategories as $category)
                                        <span class="badge text-bg-secondary">{{ $category }}</span>
                                    @endforeach
                                </div>
                            </td>
                            <td>
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="{{ route('admin.events.show', $event['id']) }}" class="btn btn-sm btn-outline-primary">Show</a>
                                    <a href="{{ route('admin.events.edit', $event['id']) }}" class="btn btn-sm btn-outline-warning">Update</a>
                                    <form action="{{ route('admin.events.destroy', $event['id']) }}" method="post" onsubmit="return confirm('Do you really want to delete this event?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection

@extends('app.back')

@section('title', 'Event Details')

@section('content')
    @php
        $eventCategories = array_filter(array_map('trim', explode(',', (string) $event['categories'])));
        $concreteCategories = array_values(array_filter($categoryOptions, fn ($category) => $category !== 'All'));

        if (in_array('All', $eventCategories, true) || count(array_diff($concreteCategories, $eventCategories)) === 0) {
            $eventCategories = ['All'];
        }

        $startDate = $event->event_start_date?->toDateString() ?? $event->event_date?->toDateString();
        $endDate = $event->event_end_date?->toDateString() ?? $startDate;
    @endphp

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
        <h1 class="h3 admin-page-title mb-0">Event Details</h1>
        <a href="{{ route('admin.events.index') }}" class="btn btn-outline-secondary">Back</a>
    </div>

    <div class="card admin-card shadow-sm">
        <div class="card-body p-4">
            <div class="row g-3">
                <div class="col-md-3"><strong>ID</strong><div>{{ $event['id'] }}</div></div>
                <div class="col-md-5"><strong>Event Name</strong><div>{{ $event['event_name'] }}</div></div>
                <div class="col-md-4"><strong>Type</strong><div>{{ $eventTypeLabels[$event['event_type']] ?? $event['event_type'] ?? 'Event' }}</div></div>
                <div class="col-md-4"><strong>Dates</strong><div>{{ $startDate === $endDate ? $startDate : $startDate.' - '.$endDate }}</div></div>
                <div class="col-md-4"><strong>Day Scope</strong><div>{{ $dayScopeLabels[$event['day_scope']] ?? $event['day_scope'] ?? 'Full day' }}</div></div>
                <div class="col-md-12"><strong>Description</strong><div>{{ $event['description'] ?? 'N/A' }}</div></div>
                <div class="col-md-12">
                    <strong>Categories Involved</strong>
                    <div class="d-flex flex-wrap gap-1 mt-1">
                        @foreach($eventCategories as $category)
                            <span class="badge text-bg-secondary">{{ $category }}</span>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

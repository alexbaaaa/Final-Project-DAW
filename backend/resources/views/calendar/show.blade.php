@extends('app.back')

@section('title', 'Calendar Day Details')

@section('content')
    @php
        $calendarCategories = array_filter(array_map('trim', explode(',', (string) $calendarDay['categories'])));
        $concreteCategories = array_values(array_filter($categoryOptions, fn ($category) => $category !== 'All'));

        if (in_array('All', $calendarCategories, true) || count(array_diff($concreteCategories, $calendarCategories)) === 0) {
            $calendarCategories = ['All'];
        }
    @endphp

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
        <h1 class="h3 admin-page-title mb-0">Calendar Day Details</h1>
        <a href="{{ route('admin.calendar.index') }}" class="btn btn-outline-secondary">Back</a>
    </div>

    <div class="card admin-card shadow-sm">
        <div class="card-body p-4">
            <div class="row g-3">
                <div class="col-md-2"><strong>ID</strong><div>{{ $calendarDay['id'] }}</div></div>
                <div class="col-md-4"><strong>Date</strong><div>{{ $calendarDay->date->toDateString() }}</div></div>
                <div class="col-md-6"><strong>Type</strong><div>{{ $dayTypeLabels[$calendarDay['day_type']] ?? $calendarDay['day_type'] }}</div></div>
                <div class="col-md-6"><strong>Related Event</strong><div>{{ $calendarDay->event?->event_name ?? 'N/A' }}</div></div>
                <div class="col-md-6"><strong>Day Scope</strong><div>{{ $dayScopeLabels[$calendarDay['day_scope']] ?? $calendarDay['day_scope'] ?? 'Full day' }}</div></div>
                <div class="col-md-12">
                    <strong>Categories Involved</strong>
                    <div class="d-flex flex-wrap gap-1 mt-1">
                        @foreach($calendarCategories as $category)
                            <span class="badge text-bg-secondary">{{ $category }}</span>
                        @endforeach
                    </div>
                </div>
                <div class="col-md-12"><strong>Title</strong><div>{{ $calendarDay['title'] ?? 'N/A' }}</div></div>
                <div class="col-md-12"><strong>Description</strong><div>{{ $calendarDay['description'] ?? 'N/A' }}</div></div>
            </div>
        </div>
    </div>
@endsection

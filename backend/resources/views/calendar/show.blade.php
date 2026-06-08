@extends('app.back')

@section('title', 'Calendar Day Details')

@section('content')
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
                <div class="col-md-12"><strong>Title</strong><div>{{ $calendarDay['title'] ?? 'N/A' }}</div></div>
                <div class="col-md-12"><strong>Description</strong><div>{{ $calendarDay['description'] ?? 'N/A' }}</div></div>
            </div>
        </div>
    </div>
@endsection

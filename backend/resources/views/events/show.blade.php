@extends('app.back')

@section('title', 'Event Details')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
        <h1 class="h3 admin-page-title mb-0">Event Details</h1>
        <a href="{{ route('admin.events.index') }}" class="btn btn-outline-secondary">Back</a>
    </div>

    <div class="card admin-card shadow-sm">
        <div class="card-body p-4">
            <div class="row g-3">
                <div class="col-md-3"><strong>ID</strong><div>{{ $event['id'] }}</div></div>
                <div class="col-md-5"><strong>Event Name</strong><div>{{ $event['event_name'] }}</div></div>
                <div class="col-md-4"><strong>Date</strong><div>{{ $event['event_date'] }}</div></div>
                <div class="col-md-12"><strong>Description</strong><div>{{ $event['description'] }}</div></div>
                <div class="col-md-12"><strong>Categories Involved</strong><div>{{ $event['categories'] }}</div></div>
            </div>
        </div>
    </div>
@endsection

@extends('app.back')

@section('title', 'Time Details')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
        <h1 class="h3 admin-page-title mb-0">Time Details</h1>
        <a href="{{ route('admin.times.index') }}" class="btn btn-outline-secondary">Back</a>
    </div>

    <div class="card admin-card shadow-sm">
        <div class="card-body p-4">
            <div class="row g-3">
                <div class="col-md-2"><strong>ID</strong><div>{{ $timeRecord['id'] }}</div></div>
                <div class="col-md-2"><strong>Swimmer ID</strong><div>{{ $timeRecord['swimmer_id'] }}</div></div>
                <div class="col-md-4"><strong>Test Type</strong><div>{{ $timeRecord['test_type'] }}</div></div>
                <div class="col-md-2"><strong>Time</strong><div>{{ $timeRecord['time'] }}</div></div>
                <div class="col-md-2"><strong>Date</strong><div>{{ $timeRecord['date'] }}</div></div>
                <div class="col-md-12"><strong>Location</strong><div>{{ $timeRecord['location'] }}</div></div>
            </div>
        </div>
    </div>
@endsection

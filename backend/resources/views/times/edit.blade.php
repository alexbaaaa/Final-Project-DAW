@extends('app.back')

@section('title', 'Update Time')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
        <div>
            <h1 class="h3 admin-page-title mb-1">Update Time</h1>
            <p class="admin-subtle mb-0">Modify a swimmer time record.</p>
        </div>
        <a href="{{ route('admin.times.index') }}" class="btn btn-outline-secondary">Back</a>
    </div>

    <div class="card admin-card shadow-sm">
        <div class="card-body p-4">
            <form action="{{ route('admin.times.update', $timeRecord['id']) }}" method="post" class="row g-3" onsubmit="return confirm('Do you want to apply these changes?');">
                @csrf
                @method('PUT')
                <div class="col-md-4">
                    <label for="swimmer_id" class="form-label">Swimmer ID</label>
                    <input type="number" id="swimmer_id" name="swimmer_id" class="form-control" min="1" value="{{ $timeRecord['swimmer_id'] }}" required>
                </div>
                <div class="col-md-8">
                    <label for="test_type" class="form-label">Test Type</label>
                    <input type="text" id="test_type" name="test_type" class="form-control" value="{{ $timeRecord['test_type'] }}" required>
                </div>
                <div class="col-md-4">
                    <label for="time" class="form-label">Time</label>
                    <input type="text" id="time" name="time" class="form-control" value="{{ $timeRecord['time'] }}" required>
                </div>
                <div class="col-md-4">
                    <label for="date" class="form-label">Date</label>
                    <input type="date" id="date" name="date" class="form-control" value="{{ $timeRecord['date'] }}" required>
                </div>
                <div class="col-md-4">
                    <label for="location" class="form-label">Location</label>
                    <input type="text" id="location" name="location" class="form-control" value="{{ $timeRecord['location'] }}" required>
                </div>
                <div class="col-12 d-flex justify-content-end gap-2 pt-2">
                    <a href="{{ route('admin.times.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    <button type="submit" class="btn admin-btn-warning">Update</button>
                </div>
            </form>
        </div>
    </div>
@endsection

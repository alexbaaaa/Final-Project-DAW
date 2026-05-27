@extends('app.back')

@section('title', 'Create Time')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
        <div>
            <h1 class="h3 admin-page-title mb-1">Create Time</h1>
            <p class="admin-subtle mb-0">Add a new performance time record.</p>
        </div>
        <a href="{{ route('admin.times.index') }}" class="btn btn-outline-secondary">Back</a>
    </div>

    <div class="card admin-card shadow-sm">
        <div class="card-body p-4">
            <form action="{{ route('admin.times.store') }}" method="post" class="row g-3">
                @csrf
                <div class="col-md-4">
                    <label for="swimmer_id" class="form-label">Swimmer ID</label>
                    <input type="number" id="swimmer_id" name="swimmer_id" class="form-control" min="1" required>
                </div>
                <div class="col-md-8">
                    <label for="test_type" class="form-label">Test Type</label>
                    <input type="text" id="test_type" name="test_type" class="form-control" placeholder="Example: 100m Freestyle" required>
                </div>
                <div class="col-md-4">
                    <label for="time" class="form-label">Time</label>
                    <input type="text" id="time" name="time" class="form-control" placeholder="00:00.00" required>
                </div>
                <div class="col-md-4">
                    <label for="date" class="form-label">Date</label>
                    <input type="date" id="date" name="date" class="form-control" required>
                </div>
                <div class="col-md-4">
                    <label for="location" class="form-label">Location</label>
                    <input type="text" id="location" name="location" class="form-control" required>
                </div>
                <div class="col-12 d-flex justify-content-end gap-2 pt-2">
                    <a href="{{ route('admin.times.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    <button type="submit" class="btn admin-btn-primary">Create</button>
                </div>
            </form>
        </div>
    </div>
@endsection

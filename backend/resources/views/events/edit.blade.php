@extends('app.back')

@section('title', 'Update Event')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
        <div>
            <h1 class="h3 admin-page-title mb-1">Update Event</h1>
            <p class="admin-subtle mb-0">Modify event information.</p>
        </div>
        <a href="{{ route('admin.events.index') }}" class="btn btn-outline-secondary">Back</a>
    </div>

    <div class="card admin-card shadow-sm">
        <div class="card-body p-4">
            <form action="{{ route('admin.events.update', $event['id']) }}" method="post" class="row g-3" onsubmit="return confirm('Do you want to apply these changes?');">
                @csrf
                @method('PUT')
                <div class="col-md-6">
                    <label for="event_name" class="form-label">Event Name</label>
                    <input type="text" id="event_name" name="event_name" class="form-control" value="{{ $event['event_name'] }}" required>
                </div>
                <div class="col-md-6">
                    <label for="event_date" class="form-label">Event Date</label>
                    <input type="date" id="event_date" name="event_date" class="form-control" value="{{ $event['event_date'] }}" required>
                </div>
                <div class="col-md-12">
                    <label for="description" class="form-label">Description</label>
                    <textarea id="description" name="description" class="form-control" rows="3" required>{{ $event['description'] }}</textarea>
                </div>
                <div class="col-md-12">
                    <label for="categories" class="form-label">Categories Involved</label>
                    <input type="text" id="categories" name="categories" class="form-control" value="{{ $event['categories'] }}" required>
                </div>
                <div class="col-12 d-flex justify-content-end gap-2 pt-2">
                    <a href="{{ route('admin.events.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    <button type="submit" class="btn admin-btn-warning">Update</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@extends('app.back')

@section('title', 'Create Event')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
        <div>
            <h1 class="h3 admin-page-title mb-1">Create Event</h1>
            <p class="admin-subtle mb-0">Add a new event record.</p>
        </div>
        <a href="{{ route('admin.events.index') }}" class="btn btn-outline-secondary">Back</a>
    </div>

    <div class="card admin-card shadow-sm">
        <div class="card-body p-4">
            <form action="{{ route('admin.events.store') }}" method="post" class="row g-3">
                @csrf
                <div class="col-md-6">
                    <label for="event_name" class="form-label">Event Name</label>
                    <input type="text" id="event_name" name="event_name" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label for="event_date" class="form-label">Event Date</label>
                    <input type="date" id="event_date" name="event_date" class="form-control" required>
                </div>
                <div class="col-md-12">
                    <label for="description" class="form-label">Description</label>
                    <textarea id="description" name="description" class="form-control" rows="3" required></textarea>
                </div>
                <div class="col-md-12">
                    <label for="categories" class="form-label">Categories Involved</label>
                    <input type="text" id="categories" name="categories" class="form-control" placeholder="Example: Junior, Senior" required>
                </div>
                <div class="col-12 d-flex justify-content-end gap-2 pt-2">
                    <a href="{{ route('admin.events.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    <button type="submit" class="btn admin-btn-primary">Create</button>
                </div>
            </form>
        </div>
    </div>
@endsection

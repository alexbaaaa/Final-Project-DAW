@extends('app.back')

@section('title', 'Update Calendar Day')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
        <div>
            <h1 class="h3 admin-page-title mb-1">Update Calendar Day</h1>
            <p class="admin-subtle mb-0">Modify a holiday or training exception.</p>
        </div>
        <a href="{{ route('admin.calendar.index') }}" class="btn btn-outline-secondary">Back</a>
    </div>

    <div class="card admin-card shadow-sm">
        <div class="card-body p-4">
            <form action="{{ route('admin.calendar.update', $calendarDay['id']) }}" method="post" class="row g-3" onsubmit="return confirm('Do you want to apply these changes?');">
                @csrf
                @method('PUT')
                <div class="col-md-6">
                    <label for="date" class="form-label">Date</label>
                    <input type="date" id="date" name="date" class="form-control" value="{{ old('date', $calendarDay->date->toDateString()) }}" required>
                </div>
                <div class="col-md-6">
                    <label for="day_type" class="form-label">Day Type</label>
                    <select id="day_type" name="day_type" class="form-select" required>
                        @foreach($dayTypeLabels as $value => $label)
                            <option value="{{ $value }}" @if(old('day_type', $calendarDay['day_type']) === $value) selected @endif>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-12">
                    <label for="title" class="form-label">Title</label>
                    <input type="text" id="title" name="title" class="form-control" value="{{ old('title', $calendarDay['title']) }}" maxlength="150">
                </div>
                <div class="col-md-12">
                    <label for="description" class="form-label">Description</label>
                    <textarea id="description" name="description" class="form-control" rows="3">{{ old('description', $calendarDay['description']) }}</textarea>
                </div>
                <div class="col-12 d-flex justify-content-end gap-2 pt-2">
                    <a href="{{ route('admin.calendar.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    <button type="submit" class="btn admin-btn-warning">Update</button>
                </div>
            </form>
        </div>
    </div>
@endsection

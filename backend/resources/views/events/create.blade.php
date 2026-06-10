@extends('app.back')

@section('title', 'Create Event')

@section('content')
    @php
        $selectedCategories = old('categories', $selectedCategories);
        $selectedEventType = old('event_type', 'event');
        $selectedDayScope = old('day_scope', 'full_day');
    @endphp

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
                    <input type="text" id="event_name" name="event_name" class="form-control" value="{{ old('event_name') }}" required>
                </div>
                <div class="col-md-6">
                    <label for="event_type" class="form-label">Event Type</label>
                    <select id="event_type" name="event_type" class="form-select" required>
                        @foreach($eventTypeLabels as $value => $label)
                            <option value="{{ $value }}" @selected($selectedEventType === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="event_start_date" class="form-label">Start Date</label>
                    <input type="date" id="event_start_date" name="event_start_date" class="form-control" value="{{ old('event_start_date') }}" required>
                </div>
                <div class="col-md-4">
                    <label for="event_end_date" class="form-label">End Date</label>
                    <input type="date" id="event_end_date" name="event_end_date" class="form-control" value="{{ old('event_end_date') }}">
                    <small class="text-secondary">Leave empty for one-day events.</small>
                </div>
                <div class="col-md-4">
                    <label for="day_scope" class="form-label">Day Scope</label>
                    <select id="day_scope" name="day_scope" class="form-select" required>
                        @foreach($dayScopeLabels as $value => $label)
                            <option value="{{ $value }}" @selected($selectedDayScope === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-12">
                    <label for="description" class="form-label">Description</label>
                    <textarea id="description" name="description" class="form-control" rows="3">{{ old('description') }}</textarea>
                </div>
                <div class="col-md-12">
                    <label class="form-label">Categories Involved</label>
                    <div class="border rounded p-3">
                        <div class="row g-2">
                            @foreach($categoryOptions as $category)
                                <div class="col-sm-6 col-lg-4">
                                    <div class="form-check">
                                        <input
                                            class="form-check-input"
                                            id="category_{{ $loop->index }}"
                                            name="categories[]"
                                            data-event-category
                                            @if($category === 'All') data-category-all="true" @endif
                                            type="checkbox"
                                            value="{{ $category }}"
                                            @checked(in_array($category, $selectedCategories, true))
                                        >
                                        <label class="form-check-label" for="category_{{ $loop->index }}">{{ $category }}</label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <small class="text-secondary">All includes every category and cannot be combined with another option.</small>
                </div>
                <div class="col-12 d-flex justify-content-end gap-2 pt-2">
                    <a href="{{ route('admin.events.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    <button type="submit" class="btn admin-btn-primary">Create</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    (() => {
        const categoryFields = Array.from(document.querySelectorAll('[data-event-category]'));
        const allField = categoryFields.find((field) => field.dataset.categoryAll === 'true');

        if (!allField) {
            return;
        }

        categoryFields.forEach((field) => {
            field.addEventListener('change', () => {
                if (field === allField && field.checked) {
                    categoryFields
                        .filter((categoryField) => categoryField !== allField)
                        .forEach((categoryField) => {
                            categoryField.checked = false;
                        });

                    return;
                }

                if (field !== allField && field.checked) {
                    allField.checked = false;
                }
            });
        });
    })();
</script>
@endpush

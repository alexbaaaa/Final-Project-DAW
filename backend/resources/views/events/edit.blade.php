@extends('app.back')

@section('title', 'Update Event')

@section('content')
    @php
        $storedCategories = array_filter(array_map('trim', explode(',', (string) $event['categories'])));
        $concreteCategories = array_values(array_filter($categoryOptions, fn ($category) => $category !== 'All'));

        if (in_array('All', $storedCategories, true) || count(array_diff($concreteCategories, $storedCategories)) === 0) {
            $storedCategories = ['All'];
        }

        $selectedCategories = old('categories', $storedCategories);
        $selectedEventType = old('event_type', $event['event_type'] ?? 'event');
        $selectedDayScope = old('day_scope', $event['day_scope'] ?? 'full_day');
        $eventStartDate = old('event_start_date', $event->event_start_date?->toDateString() ?? $event->event_date?->toDateString());
        $eventEndDate = old('event_end_date', $event->event_end_date?->toDateString() ?? $eventStartDate);
    @endphp

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
                    <input type="text" id="event_name" name="event_name" class="form-control" value="{{ old('event_name', $event['event_name']) }}" required>
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
                    <input type="date" id="event_start_date" name="event_start_date" class="form-control" value="{{ $eventStartDate }}" required>
                </div>
                <div class="col-md-4">
                    <label for="event_end_date" class="form-label">End Date</label>
                    <input type="date" id="event_end_date" name="event_end_date" class="form-control" value="{{ $eventEndDate }}">
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
                    <textarea id="description" name="description" class="form-control" rows="3">{{ old('description', $event['description']) }}</textarea>
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
                    <button type="submit" class="btn admin-btn-warning">Update</button>
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

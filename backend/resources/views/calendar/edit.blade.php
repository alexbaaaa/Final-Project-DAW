@extends('app.back')

@section('title', 'Update Calendar Day')

@section('content')
    @php
        $storedCategories = array_filter(array_map('trim', explode(',', (string) $calendarDay['categories'])));
        $concreteCategories = array_values(array_filter($categoryOptions, fn ($category) => $category !== 'All'));

        if (in_array('All', $storedCategories, true) || count(array_diff($concreteCategories, $storedCategories)) === 0) {
            $storedCategories = ['All'];
        }

        $selectedCategories = old('categories', $storedCategories);
        $selectedDayScope = old('day_scope', $calendarDay['day_scope'] ?? 'full_day');
        $selectedDayType = old('day_type', $calendarDay['day_type']);
        $selectedEventId = old('event_id', $calendarDay['event_id']);
    @endphp

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
                            <option value="{{ $value }}" @selected($selectedDayType === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6 @if($selectedDayType !== 'registration_deadline') d-none @endif" data-calendar-event-field>
                    <label for="event_id" class="form-label">Related Event</label>
                    <select id="event_id" name="event_id" class="form-select" @if($selectedDayType === 'registration_deadline') required @endif>
                        <option value="">Select event</option>
                        @foreach($eventOptions as $eventOption)
                            @php
                                $eventStartDate = $eventOption->event_start_date?->toDateString() ?? $eventOption->event_date?->toDateString();
                                $eventEndDate = $eventOption->event_end_date?->toDateString() ?? $eventStartDate;
                                $eventDateLabel = $eventStartDate === $eventEndDate ? $eventStartDate : $eventStartDate.' - '.$eventEndDate;
                            @endphp
                            <option value="{{ $eventOption['id'] }}" @selected((string) $selectedEventId === (string) $eventOption['id'])>
                                {{ $eventOption['event_name'] }} @if($eventDateLabel) ({{ $eventDateLabel }}) @endif
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="day_scope" class="form-label">Day Scope</label>
                    <select id="day_scope" name="day_scope" class="form-select" required>
                        @foreach($dayScopeLabels as $value => $label)
                            <option value="{{ $value }}" @selected($selectedDayScope === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
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
                                            id="calendar_category_{{ $loop->index }}"
                                            name="categories[]"
                                            data-calendar-category
                                            @if($category === 'All') data-category-all="true" @endif
                                            type="checkbox"
                                            value="{{ $category }}"
                                            @checked(in_array($category, $selectedCategories, true))
                                        >
                                        <label class="form-check-label" for="calendar_category_{{ $loop->index }}">{{ $category }}</label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <small class="text-secondary">All includes every category and cannot be combined with another option.</small>
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

@push('scripts')
<script>
    (() => {
        const dayTypeField = document.getElementById('day_type');
        const eventFieldWrapper = document.querySelector('[data-calendar-event-field]');
        const eventField = document.getElementById('event_id');
        const categoryFields = Array.from(document.querySelectorAll('[data-calendar-category]'));
        const allField = categoryFields.find((field) => field.dataset.categoryAll === 'true');

        const toggleEventField = () => {
            if (!dayTypeField || !eventFieldWrapper || !eventField) {
                return;
            }

            const isRegistrationDeadline = dayTypeField.value === 'registration_deadline';

            eventFieldWrapper.classList.toggle('d-none', !isRegistrationDeadline);
            eventField.required = isRegistrationDeadline;

            if (!isRegistrationDeadline) {
                eventField.value = '';
            }
        };

        if (dayTypeField) {
            dayTypeField.addEventListener('change', toggleEventField);
            toggleEventField();
        }

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

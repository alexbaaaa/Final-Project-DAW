@php
    $selectedCategories = old('categories', $selectedCategories ?? []);
    $scheduleEntries = old('schedule_entries', json_encode($trainingGroup?->schedule ?? []));

    if (!is_string($scheduleEntries)) {
        $scheduleEntries = json_encode($scheduleEntries);
    }
@endphp

<form
    action="{{ $action }}"
    method="post"
    class="row g-3"
    @if(($method ?? null) === 'PUT') data-confirm-message="Do you want to apply these changes?" @endif
>
    @csrf
    @if($method)
        @method($method)
    @endif

    <div class="col-md-6">
        <label for="name" class="form-label">Name</label>
        <input
            type="text"
            id="name"
            name="name"
            class="form-control @error('name') is-invalid @enderror"
            value="{{ old('name', $trainingGroup['name'] ?? '') }}"
            maxlength="150"
            required
        >
        @error('name')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-12">
        <label class="form-label">Categories Included</label>
        <div class="border rounded p-3 @error('categories') border-danger @enderror">
            <div class="row g-2">
                @foreach($categoryOptions as $category)
                    <div class="col-sm-6 col-lg-4">
                        <div class="form-check">
                            <input
                                class="form-check-input"
                                id="training_group_category_{{ $loop->index }}"
                                name="categories[]"
                                data-training-group-category
                                @if($category === 'All') data-category-all="true" @endif
                                type="checkbox"
                                value="{{ $category }}"
                                @checked(in_array($category, $selectedCategories, true))
                            >
                            <label class="form-check-label" for="training_group_category_{{ $loop->index }}">{{ $category }}</label>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        @error('categories')
            <div class="text-danger small mt-1">{{ $message }}</div>
        @enderror
        <small class="text-secondary">All includes every category and cannot be combined with another option.</small>
    </div>

    <div class="col-md-12" data-training-schedule-root>
        <label class="form-label">Schedule</label>
        <input type="hidden" id="schedule_entries" name="schedule_entries" value="{{ $scheduleEntries }}">

        <div class="training-schedule-list mb-3" data-schedule-list></div>
        <div class="text-danger small mb-2 @if(!$errors->has('schedule_entries')) d-none @endif" data-schedule-error>
            {{ $errors->first('schedule_entries') }}
        </div>

        <div class="training-schedule-editor border rounded p-3 mb-3 d-none" data-schedule-editor>
            <div class="row g-3">
                <div class="col-md-12">
                    <span class="form-label d-block">Days</span>
                    <div class="d-flex flex-wrap gap-3">
                        @foreach($dayOptions as $dayOption)
                            <div class="form-check">
                                <input
                                    class="form-check-input"
                                    id="schedule_day_{{ $dayOption['value'] }}"
                                    data-schedule-day
                                    type="checkbox"
                                    value="{{ $dayOption['value'] }}"
                                >
                                <label class="form-check-label" for="schedule_day_{{ $dayOption['value'] }}">
                                    {{ $dayOption['label'] }}
                                </label>
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="col-md-3">
                    <label for="schedule_from" class="form-label">From</label>
                    <input type="time" id="schedule_from" class="form-control" data-schedule-from>
                </div>
                <div class="col-md-3">
                    <label for="schedule_to" class="form-label">To</label>
                    <input type="time" id="schedule_to" class="form-control" data-schedule-to>
                </div>
                <div class="col-md-6 d-flex align-items-end gap-2">
                    <button type="button" class="btn admin-btn-primary" data-schedule-add>Add</button>
                    <button type="button" class="btn btn-outline-secondary" data-schedule-cancel>Cancel</button>
                </div>
            </div>
        </div>

        <button type="button" class="btn btn-outline-primary btn-sm" data-schedule-open>Add schedule</button>
    </div>

    <div class="col-12 d-flex justify-content-end gap-2 pt-2">
        <a href="{{ $cancelRoute }}" class="btn btn-outline-secondary">Cancel</a>
        <button type="submit" class="btn {{ $submitClass }}">{{ $submitLabel }}</button>
    </div>
</form>

@push('scripts')
<script>
    (() => {
        const categoryFields = Array.from(document.querySelectorAll('[data-training-group-category]'));
        const allField = categoryFields.find((field) => field.dataset.categoryAll === 'true');

        if (allField) {
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
        }

        const root = document.querySelector('[data-training-schedule-root]');

        if (!root) {
            return;
        }

        const dayDefinitions = @json($dayOptions);
        const dayOrder = dayDefinitions.map((dayDefinition) => dayDefinition.value);
        const dayByValue = dayDefinitions.reduce((days, dayDefinition) => {
            days[dayDefinition.value] = dayDefinition;

            return days;
        }, {});
        const scheduleField = document.getElementById('schedule_entries');
        const scheduleList = root.querySelector('[data-schedule-list]');
        const scheduleEditor = root.querySelector('[data-schedule-editor]');
        const scheduleError = root.querySelector('[data-schedule-error]');
        const openButton = root.querySelector('[data-schedule-open]');
        const addButton = root.querySelector('[data-schedule-add]');
        const cancelButton = root.querySelector('[data-schedule-cancel]');
        const fromField = root.querySelector('[data-schedule-from]');
        const toField = root.querySelector('[data-schedule-to]');
        const dayFields = Array.from(root.querySelectorAll('[data-schedule-day]'));
        const form = root.closest('form');
        let scheduleEntries = parseInitialEntries();

        const setError = (message = '') => {
            if (!scheduleError) {
                return;
            }

            scheduleError.textContent = message;
            scheduleError.classList.toggle('d-none', message === '');
        };

        const updateScheduleField = () => {
            scheduleField.value = JSON.stringify(scheduleEntries);
        };

        const renderScheduleEntries = () => {
            scheduleList.innerHTML = '';

            if (scheduleEntries.length === 0) {
                const emptyState = document.createElement('p');
                emptyState.className = 'training-schedule-empty mb-0';
                emptyState.textContent = 'No schedule blocks added yet.';
                scheduleList.appendChild(emptyState);
                updateScheduleField();

                return;
            }

            scheduleEntries.forEach((entry, index) => {
                const item = document.createElement('div');
                item.className = 'training-schedule-entry';

                const text = document.createElement('span');
                text.textContent = formatScheduleEntry(entry);

                const removeButton = document.createElement('button');
                removeButton.type = 'button';
                removeButton.className = 'btn btn-sm btn-outline-danger training-schedule-remove';
                removeButton.dataset.index = String(index);
                removeButton.setAttribute('aria-label', 'Remove schedule block');
                removeButton.textContent = 'x';

                item.appendChild(text);
                item.appendChild(removeButton);
                scheduleList.appendChild(item);
            });

            updateScheduleField();
        };

        const resetEditor = () => {
            dayFields.forEach((field) => {
                field.checked = false;
            });
            fromField.value = '';
            toField.value = '';
        };

        openButton.addEventListener('click', () => {
            setError();
            scheduleEditor.classList.remove('d-none');
        });

        cancelButton.addEventListener('click', () => {
            resetEditor();
            setError();
            scheduleEditor.classList.add('d-none');
        });

        addButton.addEventListener('click', () => {
            const days = dayFields
                .filter((field) => field.checked)
                .map((field) => field.value)
                .sort((firstDay, secondDay) => dayOrder.indexOf(firstDay) - dayOrder.indexOf(secondDay));
            const from = fromField.value;
            const to = toField.value;

            if (days.length === 0) {
                setError('Select at least one day.');

                return;
            }

            if (!from || !to) {
                setError('Enter both schedule times.');

                return;
            }

            if (from >= to) {
                setError('The end time must be after the start time.');

                return;
            }

            scheduleEntries.push({ days, from, to });
            resetEditor();
            setError();
            scheduleEditor.classList.add('d-none');
            renderScheduleEntries();
        });

        scheduleList.addEventListener('click', (event) => {
            const removeButton = event.target.closest('[data-index]');

            if (!removeButton) {
                return;
            }

            scheduleEntries.splice(Number(removeButton.dataset.index), 1);
            renderScheduleEntries();
        });

        form.addEventListener('submit', (event) => {
            if (event.defaultPrevented) {
                return;
            }

            if (form.dataset.isSubmitting === 'true') {
                event.preventDefault();

                return;
            }

            if (scheduleEntries.length === 0) {
                event.preventDefault();
                setError('Add at least one schedule block.');

                return;
            }

            if (form.dataset.confirmMessage && !window.confirm(form.dataset.confirmMessage)) {
                event.preventDefault();

                return;
            }

            updateScheduleField();
            form.dataset.isSubmitting = 'true';

            const submitButton = event.submitter || form.querySelector('button[type="submit"]');

            if (submitButton) {
                submitButton.disabled = true;
                submitButton.textContent = 'Saving...';
            }
        });

        renderScheduleEntries();

        function parseInitialEntries() {
            try {
                const parsedEntries = JSON.parse(scheduleField.value || '[]');

                if (!Array.isArray(parsedEntries)) {
                    return [];
                }

                return parsedEntries
                    .map(normalizeEntry)
                    .filter(Boolean);
            } catch (error) {
                return [];
            }
        }

        function normalizeEntry(entry) {
            if (!entry || !Array.isArray(entry.days)) {
                return null;
            }

            const days = dayOrder.filter((day) => entry.days.includes(day));
            const from = typeof entry.from === 'string' ? entry.from : '';
            const to = typeof entry.to === 'string' ? entry.to : '';

            if (days.length === 0 || !from || !to) {
                return null;
            }

            return { days, from, to };
        }

        function formatScheduleEntry(entry) {
            return `${formatDays(entry.days)}: ${formatTime(entry.from)} a ${formatTime(entry.to)}`;
        }

        function formatDays(days) {
            const weekdays = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'];

            if (sameDays(days, weekdays)) {
                return 'M-F (Lunes a viernes)';
            }

            if (days.length > 1 && isContiguous(days)) {
                const firstDay = dayByValue[days[0]];
                const lastDay = dayByValue[days[days.length - 1]];

                return `${firstDay.short}-${lastDay.short} (${firstDay.label} a ${lastDay.label.toLowerCase()})`;
            }

            const shortDays = days.map((day) => dayByValue[day].short).join(',');
            const labels = days.map((day) => dayByValue[day].label).join(', ');

            return `${shortDays} (${labels})`;
        }

        function sameDays(days, expectedDays) {
            return days.length === expectedDays.length && days.every((day, index) => day === expectedDays[index]);
        }

        function isContiguous(days) {
            const indexes = days.map((day) => dayOrder.indexOf(day));

            return Math.max(...indexes) - Math.min(...indexes) + 1 === indexes.length;
        }

        function formatTime(time) {
            return String(time || '').replace(/^0(\d:\d{2})$/, '$1');
        }
    })();
</script>
@endpush

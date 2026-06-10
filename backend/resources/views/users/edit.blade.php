@extends('app.back')

@section('title', 'Update App Users')

@section('content')
    @php
        $selectedType = old('user_type', $user['user_type']);
        $oldSwimmerId = old('swimmer_id', $selectedSwimmerId);
        $oldSwimmerIds = array_map('intval', old('swimmer_ids', $selectedSwimmerIds));
        $availableGuardianIds = array_map('intval', array_keys($guardianSwimmerOptions));
        $oldSwimmerIds = array_values(array_unique(array_intersect($oldSwimmerIds, $availableGuardianIds)));
        $initialSwimmerIds = count($oldSwimmerIds) ? $oldSwimmerIds : [null];
    @endphp

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
        <div>
            <h1 class="h3 admin-page-title mb-1">Update User</h1>
            <p class="admin-subtle mb-0">Modify account information.</p>
        </div>
        <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">Back</a>
    </div>

    <div class="card admin-card shadow-sm">
        <div class="card-body p-4">
            <form action="{{ route('admin.users.update', $user['id']) }}" method="post" class="row g-3" onsubmit="return confirm('Do you want to apply these changes?');">
                @csrf
                @method('PUT')
                <div class="col-md-6">
                    <label for="first_name" class="form-label">First Name</label>
                    <input type="text" id="first_name" name="first_name" class="form-control" value="{{ old('first_name', $user['first_name']) }}" required>
                </div>
                <div class="col-md-6">
                    <label for="last_name" class="form-label">Last Name</label>
                    <input type="text" id="last_name" name="last_name" class="form-control" value="{{ old('last_name', $user['last_name']) }}" required>
                </div>
                <div class="col-md-6">
                    <label for="alias" class="form-label">Alias</label>
                    <input type="text" id="alias" class="form-control" value="{{ $user['alias'] }}" disabled>
                </div>
                <div class="col-md-6">
                    <label for="user_type" class="form-label">User Type</label>
                    <select id="user_type" name="user_type" class="form-select" required>
                        @if($user['user_type'] === 'swimmer')
                            <option value="swimmer" @selected($selectedType === 'swimmer')>Swimmer</option>
                        @endif
                        <option value="legal_guardian" @selected($selectedType === 'legal_guardian')>Legal Guardian</option>
                        <option value="other" @selected($selectedType === 'other')>Other</option>
                    </select>
                </div>
                <div class="col-md-6 @if($selectedType !== 'swimmer') d-none @endif" id="singleSwimmerAssociationField">
                    <label for="swimmer_id" class="form-label">Associated Swimmer</label>
                    <select id="swimmer_id" name="swimmer_id" class="form-select">
                        <option value="">Choose swimmer</option>
                        @foreach($swimmerOptions as $swimmerId => $swimmerName)
                            <option value="{{ $swimmerId }}" @selected((int) $oldSwimmerId === (int) $swimmerId)>{{ $swimmerName }}</option>
                        @endforeach
                    </select>
                    <small class="text-secondary">Swimmer users must be linked to one swimmer.</small>
                </div>
                <div class="col-md-6 @if($selectedType !== 'legal_guardian') d-none @endif" id="multipleSwimmerAssociationField">
                    <label class="form-label">Associated Swimmers</label>
                    <div class="d-grid gap-2" id="guardianSwimmerSelectList"></div>
                    <button type="button" class="btn btn-sm btn-outline-secondary mt-2 d-none" id="addGuardianSwimmerButton">Another one</button>
                    <small class="d-block text-secondary mt-2">Legal guardians must be linked to at least one under-16 swimmer.</small>
                </div>
                <div class="col-12 d-flex justify-content-end gap-2 pt-2">
                    <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    <button type="submit" class="btn admin-btn-warning">Update</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    (() => {
        const typeField = document.getElementById('user_type');
        const singleSwimmerWrapper = document.getElementById('singleSwimmerAssociationField');
        const singleSwimmerField = document.getElementById('swimmer_id');
        const multipleSwimmerWrapper = document.getElementById('multipleSwimmerAssociationField');
        const selectList = document.getElementById('guardianSwimmerSelectList');
        const addButton = document.getElementById('addGuardianSwimmerButton');
        const swimmerOptions = @json($guardianSwimmerOptions);
        let selectedRows = @json($initialSwimmerIds);

        if (!typeField || !singleSwimmerWrapper || !singleSwimmerField || !multipleSwimmerWrapper || !selectList || !addButton) {
            return;
        }

        const normalizeRows = () => {
            selectedRows = selectedRows.filter((value, index, rows) => value || index === 0 || index === rows.length - 1);

            if (!selectedRows.length) {
                selectedRows = [null];
            }
        };

        const selectedValues = (currentIndex = null) => selectedRows
            .filter((value, index) => value && index !== currentIndex)
            .map((value) => String(value));

        const renderGuardianSelects = () => {
            normalizeRows();
            selectList.innerHTML = '';

            selectedRows.forEach((selectedValue, index) => {
                const select = document.createElement('select');
                select.className = 'form-select';
                select.name = 'swimmer_ids[]';
                select.disabled = typeField.value !== 'legal_guardian';
                select.required = typeField.value === 'legal_guardian' && index === 0;

                const placeholder = document.createElement('option');
                placeholder.value = '';
                placeholder.textContent = 'Choose swimmer';
                select.appendChild(placeholder);

                const usedValues = selectedValues(index);

                Object.entries(swimmerOptions).forEach(([swimmerId, swimmerName]) => {
                    if (usedValues.includes(String(swimmerId))) {
                        return;
                    }

                    const option = document.createElement('option');
                    option.value = swimmerId;
                    option.textContent = swimmerName;
                    option.selected = String(selectedValue || '') === String(swimmerId);
                    select.appendChild(option);
                });

                select.addEventListener('change', (event) => {
                    selectedRows[index] = event.target.value || null;
                    renderGuardianSelects();
                    toggleAssociatedSwimmer();
                });

                selectList.appendChild(select);
            });
        };

        const toggleAssociatedSwimmer = () => {
            const isSwimmer = typeField.value === 'swimmer';
            const isLegalGuardian = typeField.value === 'legal_guardian';

            if (!isLegalGuardian) {
                selectedRows = [null];
            }

            renderGuardianSelects();

            const firstSelectHasValue = Boolean(selectedRows[0]);
            const allVisibleSelectsHaveValue = selectedRows.every(Boolean);
            const hasAvailableSwimmers = selectedRows.filter(Boolean).length < Object.keys(swimmerOptions).length;

            singleSwimmerWrapper.classList.toggle('d-none', !isSwimmer);
            singleSwimmerField.required = isSwimmer;
            multipleSwimmerWrapper.classList.toggle('d-none', !isLegalGuardian);
            addButton.classList.toggle('d-none', !isLegalGuardian || !firstSelectHasValue || !allVisibleSelectsHaveValue || !hasAvailableSwimmers);

            if (!isSwimmer) {
                singleSwimmerField.value = '';
            }
        };

        addButton.addEventListener('click', () => {
            selectedRows.push(null);
            renderGuardianSelects();
            toggleAssociatedSwimmer();
        });

        typeField.addEventListener('change', toggleAssociatedSwimmer);
        renderGuardianSelects();
        toggleAssociatedSwimmer();
    })();
</script>
@endpush

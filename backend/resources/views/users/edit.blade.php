@extends('app.back')

@section('title', 'Update App Users')

@section('content')
    @php
        $selectedType = old('user_type', $user['user_type']);
        $oldSwimmerIds = array_map('intval', old('swimmer_ids', $selectedSwimmerIds));
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
                    <label for="birth_date" class="form-label">Birth Date</label>
                    <input type="date" id="birth_date" name="birth_date" class="form-control" value="{{ old('birth_date', $user->birth_date?->toDateString()) }}" required>
                </div>
                <div class="col-md-6">
                    <label for="user_type" class="form-label">User Type</label>
                    <select id="user_type" name="user_type" class="form-select" required>
                        <option value="swimmer" @selected($selectedType === 'swimmer')>Swimmer</option>
                        <option value="legal_guardian" @selected($selectedType === 'legal_guardian')>Legal Guardian</option>
                    </select>
                </div>
                <div class="col-md-6 @if($selectedType !== 'legal_guardian') d-none @endif" id="swimmerAssociationField">
                    <label for="swimmer_ids" class="form-label">Associated Swimmers</label>
                    <select id="swimmer_ids" name="swimmer_ids[]" class="form-select" multiple>
                        @foreach($guardianSwimmerOptions as $swimmerId => $swimmerName)
                            <option value="{{ $swimmerId }}" @selected(in_array((int) $swimmerId, $oldSwimmerIds, true))>{{ $swimmerName }}</option>
                        @endforeach
                    </select>
                    <small class="text-secondary">Only swimmers under 16 are available for legal guardians.</small>
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
        const swimmerFieldWrapper = document.getElementById('swimmerAssociationField');
        const swimmerField = document.getElementById('swimmer_ids');

        if (!typeField || !swimmerFieldWrapper || !swimmerField) {
            return;
        }

        const toggleAssociatedSwimmer = () => {
            const isLegalGuardian = typeField.value === 'legal_guardian';

            swimmerFieldWrapper.classList.toggle('d-none', !isLegalGuardian);
            swimmerField.required = isLegalGuardian;

            if (!isLegalGuardian) {
                Array.from(swimmerField.options).forEach((option) => {
                    option.selected = false;
                });
            }
        };

        typeField.addEventListener('change', toggleAssociatedSwimmer);
        toggleAssociatedSwimmer();
    })();
</script>
@endpush

@extends('app.back')

@section('title', 'Create App Users')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
        <div>
            <h1 class="h3 admin-page-title mb-1">Create User</h1>
            <p class="admin-subtle mb-0">Register a user account for React login.</p>
        </div>
        <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">Back</a>
    </div>

    <div class="card admin-card shadow-sm">
        <div class="card-body p-4">
            <form action="{{ route('admin.users.store') }}" method="post" class="row g-3">
                @csrf
                <div class="col-md-6">
                    <label for="first_name" class="form-label">First Name</label>
                    <input type="text" id="first_name" name="first_name" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label for="last_name" class="form-label">Last Name</label>
                    <input type="text" id="last_name" name="last_name" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label for="user_type" class="form-label">User Type</label>
                    <select id="user_type" name="user_type" class="form-select" required>
                        <option value="swimmer">Swimmer</option>
                        <option value="legal_guardian">Legal Guardian</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" id="password" name="password" class="form-control" minlength="8" required>
                </div>
                <div class="col-md-6 d-none" id="swimmerAssociationField">
                    <label for="swimmer_id" class="form-label">Associated Swimmer</label>
                    <select id="swimmer_id" name="swimmer_id" class="form-select">
                        <option value="">Select swimmer</option>
                        @foreach($guardianSwimmerOptions as $swimmerId => $swimmerName)
                            <option value="{{ $swimmerId }}">{{ $swimmerName }}</option>
                        @endforeach
                    </select>
                    <small class="text-secondary">Only swimmers under 16 are available for legal guardians.</small>
                </div>
                <div class="col-12 d-flex justify-content-end gap-2 pt-2">
                    <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    <button type="submit" class="btn admin-btn-primary">Create</button>
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
        const swimmerField = document.getElementById('swimmer_id');

        if (!typeField || !swimmerFieldWrapper || !swimmerField) {
            return;
        }

        const toggleAssociatedSwimmer = () => {
            const isLegalGuardian = typeField.value === 'legal_guardian';

            swimmerFieldWrapper.classList.toggle('d-none', !isLegalGuardian);
            swimmerField.required = isLegalGuardian;

            if (!isLegalGuardian) {
                swimmerField.value = '';
            }
        };

        typeField.addEventListener('change', toggleAssociatedSwimmer);
        toggleAssociatedSwimmer();
    })();
</script>
@endpush

@extends('app.back')

@section('title', 'Update App Users')

@section('content')
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
                    <input type="text" id="first_name" name="first_name" class="form-control" value="{{ $user['first_name'] }}" required>
                </div>
                <div class="col-md-6">
                    <label for="last_name" class="form-label">Last Name</label>
                    <input type="text" id="last_name" name="last_name" class="form-control" value="{{ $user['last_name'] }}" required>
                </div>
                <div class="col-md-6">
                    <label for="user_type" class="form-label">User Type</label>
                    <select id="user_type" name="user_type" class="form-select" required>
                        <option value="swimmer" @if($user['user_type'] === 'swimmer') selected @endif>Swimmer</option>
                        <option value="legal_guardian" @if($user['user_type'] === 'legal_guardian') selected @endif>Legal Guardian</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" id="password" name="password" class="form-control" minlength="8" placeholder="Leave blank to keep current password">
                </div>
                <div class="col-md-6 @if($user['user_type'] !== 'legal_guardian') d-none @endif" id="swimmerAssociationField">
                    <label for="swimmer_id" class="form-label">Associated Swimmer</label>
                    <select id="swimmer_id" name="swimmer_id" class="form-select">
                        <option value="">Select swimmer</option>
                        @foreach($guardianSwimmerOptions as $swimmerId => $swimmerName)
                            <option value="{{ $swimmerId }}" @if((int) $user['swimmer_id'] === (int) $swimmerId) selected @endif>{{ $swimmerName }}</option>
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

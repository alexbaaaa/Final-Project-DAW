@extends('app.back')

@section('title', 'Update Admin Area Users')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
        <div>
            <h1 class="h3 admin-page-title mb-1">Update User - Admin Area</h1>
            <p class="admin-subtle mb-0">Modify admin panel account information.</p>
        </div>
        <a href="{{ route('admin.users_admin.index') }}" class="btn btn-outline-secondary">Back</a>
    </div>

    <div class="card admin-card shadow-sm">
        <div class="card-body p-4">
            <form action="{{ route('admin.users_admin.update', $userAdmin['id']) }}" method="post" class="row g-3" onsubmit="return confirm('Do you want to apply these changes?');">
                @csrf
                @method('PUT')
                <div class="col-md-6">
                    <label for="first_name" class="form-label">First Name</label>
                    <input type="text" id="first_name" name="first_name" class="form-control" value="{{ $userAdmin['first_name'] }}" required>
                </div>
                <div class="col-md-6">
                    <label for="last_name" class="form-label">Last Name</label>
                    <input type="text" id="last_name" name="last_name" class="form-control" value="{{ $userAdmin['last_name'] }}" required>
                </div>
                <div class="col-md-6">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" id="password" name="password" class="form-control" minlength="8" placeholder="Leave blank to keep current password">
                </div>
                <div class="col-md-6">
                    <label for="swimmer_id" class="form-label">Associated Swimmer (Optional)</label>
                    <select id="swimmer_id" name="swimmer_id" class="form-select">
                        <option value="">No swimmer</option>
                        @foreach($swimmerOptions as $swimmerId => $swimmerName)
                            <option value="{{ $swimmerId }}" @if((int) $userAdmin['swimmer_id'] === (int) $swimmerId) selected @endif>{{ $swimmerName }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 d-flex justify-content-end gap-2 pt-2">
                    <a href="{{ route('admin.users_admin.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    <button type="submit" class="btn admin-btn-warning">Update</button>
                </div>
            </form>
        </div>
    </div>
@endsection


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
                    <label for="alias" class="form-label">Alias</label>
                    <input type="text" id="alias" name="alias" class="form-control" value="{{ old('alias', $userAdmin['alias']) }}" required>
                </div>
                <div class="col-md-6">
                    <label for="role" class="form-label">Role</label>
                    <select id="role" name="role" class="form-select" required>
                        @foreach($roles as $role)
                            <option value="{{ $role }}" @selected(old('role', $userAdmin['role']) === $role)>{{ ucfirst($role) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12">
                    <small class="text-secondary">When the alias changes, the password is regenerated to match the alias.</small>
                </div>
                <div class="col-12 d-flex justify-content-end gap-2 pt-2">
                    <a href="{{ route('admin.users_admin.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    <button type="submit" class="btn admin-btn-warning">Update</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@extends('app.back')

@section('title', 'Create Admin Area Users')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
        <div>
            <h1 class="h3 admin-page-title mb-1">Create User - Admin Area</h1>
            <p class="admin-subtle mb-0">Register a user account for admin panel access.</p>
        </div>
        <a href="{{ route('admin.users_admin.index') }}" class="btn btn-outline-secondary">Back</a>
    </div>

    <div class="card admin-card shadow-sm">
        <div class="card-body p-4">
            <form action="{{ route('admin.users_admin.store') }}" method="post" class="row g-3">
                @csrf
                <div class="col-md-6">
                    <label for="alias" class="form-label">Alias</label>
                    <input type="text" id="alias" name="alias" class="form-control" value="{{ old('alias') }}" required>
                </div>
                <div class="col-md-6">
                    <label for="role" class="form-label">Role</label>
                    <select id="role" name="role" class="form-select" required>
                        @foreach($roles as $role)
                            <option value="{{ $role }}" @selected(old('role') === $role)>{{ ucfirst($role) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12">
                    <small class="text-secondary">The initial password is generated automatically and will be exactly the same as the alias.</small>
                </div>
                <div class="col-12 d-flex justify-content-end gap-2 pt-2">
                    <a href="{{ route('admin.users_admin.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    <button type="submit" class="btn admin-btn-primary">Create</button>
                </div>
            </form>
        </div>
    </div>
@endsection

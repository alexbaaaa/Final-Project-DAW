@extends('app.back')

@section('title', 'Admin Area Users')

@section('content')
    @php
        $currentAdminUser = $currentAdminUser ?? null;
        $roleLabels = [
            'root' => 'Root',
            'master' => 'Master',
            'admin' => 'Admin',
        ];
    @endphp

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
        <div>
            <h1 class="h3 admin-page-title mb-1">Admin Area Users</h1>
            <p class="admin-subtle mb-0">Manage users with access to the admin panel.</p>
        </div>
        @if($currentAdminUser?->isRoot())
            <a href="{{ route('admin.users_admin.create') }}" class="btn admin-btn-primary">Create User</a>
        @endif
    </div>

    <div class="card admin-card shadow-sm">
        <div class="table-responsive">
            <table class="table admin-table align-middle mb-0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Alias</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Password</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($usersAdmin as $userAdmin)
                        <tr>
                            <td>{{ $userAdmin['id'] }}</td>
                            <td><code>{{ $userAdmin['alias'] }}</code></td>
                            <td>{{ $roleLabels[$userAdmin['role']] ?? $userAdmin['role'] }}</td>
                            <td>
                                @if($userAdmin['is_enabled'])
                                    <span class="badge text-bg-success">Enabled</span>
                                @else
                                    <span class="badge text-bg-secondary">Disabled</span>
                                @endif
                            </td>
                            <td>
                                @if($userAdmin['must_change_password'])
                                    <span class="badge text-bg-warning">Default</span>
                                @else
                                    <span class="badge text-bg-success">Changed</span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex justify-content-end gap-2">
                                    @if($currentAdminUser?->isRoot() && $userAdmin['role'] !== 'root')
                                        <a href="{{ route('admin.users_admin.edit', $userAdmin['id']) }}" class="btn btn-sm btn-outline-warning">Update</a>
                                    @endif
                                    @if($currentAdminUser?->isRoot() && in_array($userAdmin['role'], ['master', 'admin'], true))
                                        <form action="{{ route('admin.users_admin.toggle', $userAdmin['id']) }}" method="post">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-sm btn-outline-secondary">
                                                {{ $userAdmin['is_enabled'] ? 'Disable' : 'Enable' }}
                                            </button>
                                        </form>
                                    @endif
                                    @if($currentAdminUser?->isRoot())
                                        <form action="{{ route('admin.users_admin.reset_password', $userAdmin['id']) }}" method="post" onsubmit="return confirm('Reset this admin user password to its default value?');">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-sm btn-outline-secondary">Reset Password</button>
                                        </form>
                                    @endif
                                    @if($currentAdminUser?->canDeleteAdminUser($userAdmin))
                                        <form action="{{ route('admin.users_admin.destroy', $userAdmin['id']) }}" method="post" onsubmit="return confirm('Do you really want to delete this admin user?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection

@extends('app.back')

@section('title', 'Admin Area Users')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
        <div>
            <h1 class="h3 admin-page-title mb-1">Admin Area Users</h1>
            <p class="admin-subtle mb-0">Manage users with access to the admin panel.</p>
        </div>
        <a href="{{ route('admin.users_admin.create') }}" class="btn admin-btn-primary">Create User</a>
    </div>

    <div class="card admin-card shadow-sm">
        <div class="table-responsive">
            <table class="table admin-table align-middle mb-0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>First Name</th>
                        <th>Last Name</th>
                        <th>Associated Swimmer</th>
                        <th>Password Hash</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($usersAdmin as $userAdmin)
                        @php
                            $swimmerName = $userAdmin['swimmer_id'] ? ($swimmerOptions[$userAdmin['swimmer_id']] ?? 'Unknown') : 'N/A';
                            $passwordValue = (string) ($userAdmin['password'] ?? '');
                            $passwordPreview = strlen($passwordValue) > 18 ? substr($passwordValue, 0, 18) . '...' : $passwordValue;
                        @endphp
                        <tr>
                            <td>{{ $userAdmin['id'] }}</td>
                            <td>{{ $userAdmin['first_name'] }}</td>
                            <td>{{ $userAdmin['last_name'] }}</td>
                            <td>{{ $swimmerName }}</td>
                            <td><code>{{ $passwordPreview }}</code></td>
                            <td>
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="{{ route('admin.users_admin.show', $userAdmin['id']) }}" class="btn btn-sm btn-outline-primary">Show</a>
                                    <a href="{{ route('admin.users_admin.edit', $userAdmin['id']) }}" class="btn btn-sm btn-outline-warning">Update</a>
                                    <form action="{{ route('admin.users_admin.destroy', $userAdmin['id']) }}" method="post" onsubmit="return confirm('Do you really want to delete this admin user?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection


@extends('app.back')

@section('title', 'App Users')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
        <div>
            <h1 class="h3 admin-page-title mb-1">App Users</h1>
            <p class="admin-subtle mb-0">Manage login users for the React application.</p>
        </div>
        <a href="{{ route('admin.users.create') }}" class="btn admin-btn-primary">Create User</a>
    </div>

    <div class="card admin-card shadow-sm">
        <div class="table-responsive">
            <table class="table admin-table align-middle mb-0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>First Name</th>
                        <th>Last Name</th>
                        <th>User Type</th>
                        <th>Associated Swimmer</th>
                        <th>Password Hash</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $user)
                        @php
                            $userTypeLabel = $user['user_type'] === 'legal_guardian' ? 'Legal Guardian' : 'Swimmer';
                            $swimmerName = $user['swimmer_id'] ? ($swimmerOptions[$user['swimmer_id']] ?? 'Unknown') : 'N/A';
                            $passwordValue = (string) ($user['password'] ?? '');
                            $passwordPreview = strlen($passwordValue) > 18 ? substr($passwordValue, 0, 18) . '...' : $passwordValue;
                        @endphp
                        <tr>
                            <td>{{ $user['id'] }}</td>
                            <td>{{ $user['first_name'] }}</td>
                            <td>{{ $user['last_name'] }}</td>
                            <td>{{ $userTypeLabel }}</td>
                            <td>{{ $swimmerName }}</td>
                            <td><code>{{ $passwordPreview }}</code></td>
                            <td>
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="{{ route('admin.users.show', $user['id']) }}" class="btn btn-sm btn-outline-primary">Show</a>
                                    <a href="{{ route('admin.users.edit', $user['id']) }}" class="btn btn-sm btn-outline-warning">Update</a>
                                    <form action="{{ route('admin.users.destroy', $user['id']) }}" method="post" onsubmit="return confirm('Do you really want to delete this user?');">
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

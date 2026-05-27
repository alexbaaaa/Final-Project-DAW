@extends('app.back')

@section('title', 'Swimmers')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
        <div>
            <h1 class="h3 admin-page-title mb-1">Swimmers</h1>
            <p class="admin-subtle mb-0">Manage swimmer records.</p>
        </div>
        <a href="{{ route('admin.swimmers.create') }}" class="btn admin-btn-primary">Create Swimmer</a>
    </div>

    <div class="card admin-card shadow-sm">
        <div class="table-responsive">
            <table class="table admin-table align-middle mb-0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>First Name</th>
                        <th>Last Name</th>
                        <th>Age</th>
                        <th>Category</th>
                        <th>Gender</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($swimmers as $swimmer)
                        <tr>
                            <td>{{ $swimmer['id'] }}</td>
                            <td>{{ $swimmer['first_name'] }}</td>
                            <td>{{ $swimmer['last_name'] }}</td>
                            <td>{{ $swimmer['age'] }}</td>
                            <td>{{ $swimmer['category'] }}</td>
                            <td>{{ $swimmer['gender'] }}</td>
                            <td>
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="{{ route('admin.swimmers.show', $swimmer['id']) }}" class="btn btn-sm btn-outline-primary">Show</a>
                                    <a href="{{ route('admin.swimmers.edit', $swimmer['id']) }}" class="btn btn-sm btn-outline-warning">Update</a>
                                    <form action="{{ route('admin.swimmers.destroy', $swimmer['id']) }}" method="post" onsubmit="return confirm('Do you really want to delete this swimmer?');">
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

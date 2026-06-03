@extends('app.back')

@section('title', 'SwimmingUP-Times')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
        <div>
            <h1 class="h3 admin-page-title mb-1">Times</h1>
            <p class="admin-subtle mb-0">Manage swimmers performance records.</p>
        </div>
        <a href="{{ route('admin.times.create') }}" class="btn admin-btn-primary">Create Time</a>
    </div>

    <div class="card admin-card shadow-sm">
        <div class="table-responsive">
            <table class="table admin-table align-middle mb-0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Swimmer ID</th>
                        <th>Test Type</th>
                        <th>Time</th>
                        <th>Date</th>
                        <th>Location</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($times as $timeRecord)
                        <tr>
                            <td>{{ $timeRecord['id'] }}</td>
                            <td>{{ $timeRecord['swimmer_id'] }}</td>
                            <td>{{ $timeRecord['test_type'] }}</td>
                            <td>{{ $timeRecord['time'] }}</td>
                            <td>{{ $timeRecord['date'] }}</td>
                            <td>{{ $timeRecord['location'] }}</td>
                            <td>
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="{{ route('admin.times.show', $timeRecord['id']) }}" class="btn btn-sm btn-outline-primary">Show</a>
                                    <a href="{{ route('admin.times.edit', $timeRecord['id']) }}" class="btn btn-sm btn-outline-warning">Update</a>
                                    <form action="{{ route('admin.times.destroy', $timeRecord['id']) }}" method="post" onsubmit="return confirm('Do you really want to delete this time record?');">
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

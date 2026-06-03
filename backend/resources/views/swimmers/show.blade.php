@extends('app.back')

@section('title', 'Swimmer Details')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
        <h1 class="h3 admin-page-title mb-0">Swimmer Details</h1>
        <a href="{{ route('admin.swimmers.index') }}" class="btn btn-outline-secondary">Back</a>
    </div>

    <div class="card admin-card shadow-sm">
        <div class="card-body p-4">
            <div class="row g-3">
                <div class="col-md-3"><strong>ID</strong><div>{{ $swimmer['id'] }}</div></div>
                <div class="col-md-3"><strong>First Name</strong><div>{{ $swimmer['first_name'] }}</div></div>
                <div class="col-md-3"><strong>Last Name</strong><div>{{ $swimmer['last_name'] }}</div></div>
                <div class="col-md-3"><strong>Age</strong><div>{{ $swimmer['age'] }}</div></div>
                <div class="col-md-6"><strong>Category</strong><div>{{ $swimmer['category'] }}</div></div>
                <div class="col-md-6"><strong>Gender</strong><div>{{ $swimmer['gender'] }}</div></div>
            </div>
        </div>
    </div>

    <div class="card admin-card shadow-sm mt-4">
        <div class="card-body p-4">
            <h2 class="h5 admin-page-title mb-3">Associated Times</h2>

            @if($times->isEmpty())
                <p class="admin-subtle mb-0">This swimmer does not have any times yet.</p>
            @else
                <div class="table-responsive">
                    <table class="table admin-table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Test Type</th>
                                <th>Time</th>
                                <th>Date</th>
                                <th>Location</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($times as $timeRecord)
                                <tr>
                                    <td>{{ $timeRecord['id'] }}</td>
                                    <td>{{ $timeRecord['test_type'] }}</td>
                                    <td>{{ $timeRecord['time'] }}</td>
                                    <td>{{ $timeRecord['date'] }}</td>
                                    <td>{{ $timeRecord['location'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
@endsection

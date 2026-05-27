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
@endsection

@extends('app.back')

@section('title', 'App Users Details')

@section('content')
    @php
        $userTypeLabel = $user['user_type'] === 'legal_guardian' ? 'Legal Guardian' : 'Swimmer';
        $swimmerName = count($userSwimmerNames) ? implode(', ', $userSwimmerNames) : 'N/A';
    @endphp

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
        <h1 class="h3 admin-page-title mb-0">User Details</h1>
        <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">Back</a>
    </div>

    <div class="card admin-card shadow-sm">
        <div class="card-body p-4">
            <div class="row g-3">
                <div class="col-md-2"><strong>ID</strong><div>{{ $user['id'] }}</div></div>
                <div class="col-md-4"><strong>Alias</strong><div><code>{{ $user['alias'] }}</code></div></div>
                <div class="col-md-4"><strong>First Name</strong><div>{{ $user['first_name'] }}</div></div>
                <div class="col-md-4"><strong>Last Name</strong><div>{{ $user['last_name'] }}</div></div>
                <div class="col-md-4"><strong>Birth Date</strong><div>{{ $user->birth_date?->toDateString() ?? 'N/A' }}</div></div>
                <div class="col-md-2"><strong>User Type</strong><div>{{ $userTypeLabel }}</div></div>
                <div class="col-md-6"><strong>Associated Swimmers</strong><div>{{ $swimmerName }}</div></div>
            </div>
        </div>
    </div>
@endsection

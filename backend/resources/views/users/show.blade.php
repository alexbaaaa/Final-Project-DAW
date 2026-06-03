@extends('app.back')

@section('title', 'App Users Details')

@section('content')
    @php
        $userTypeLabel = $user['user_type'] === 'legal_guardian' ? 'Legal Guardian' : 'Swimmer';
        $swimmerName = $user['swimmer_id'] ? ($swimmerOptions[$user['swimmer_id']] ?? 'Unknown') : 'N/A';
    @endphp

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
        <h1 class="h3 admin-page-title mb-0">User Details</h1>
        <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">Back</a>
    </div>

    <div class="card admin-card shadow-sm">
        <div class="card-body p-4">
            <div class="row g-3">
                <div class="col-md-2"><strong>ID</strong><div>{{ $user['id'] }}</div></div>
                <div class="col-md-4"><strong>First Name</strong><div>{{ $user['first_name'] }}</div></div>
                <div class="col-md-4"><strong>Last Name</strong><div>{{ $user['last_name'] }}</div></div>
                <div class="col-md-2"><strong>User Type</strong><div>{{ $userTypeLabel }}</div></div>
                <div class="col-md-6"><strong>Associated Swimmer</strong><div>{{ $swimmerName }}</div></div>
                <div class="col-md-6"><strong>Password Hash</strong><div><code>{{ (string) ($user['password'] ?? '') }}</code></div></div>
            </div>
        </div>
    </div>
@endsection

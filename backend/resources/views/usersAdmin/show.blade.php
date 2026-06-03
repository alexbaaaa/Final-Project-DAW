@extends('app.back')

@section('title', 'Admin Area Users Details')

@section('content')
    @php
        $swimmerName = $userAdmin['swimmer_id'] ? ($swimmerOptions[$userAdmin['swimmer_id']] ?? 'Unknown') : 'N/A';
    @endphp

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
        <h1 class="h3 admin-page-title mb-0">User Details - Admin Area</h1>
        <a href="{{ route('admin.users_admin.index') }}" class="btn btn-outline-secondary">Back</a>
    </div>

    <div class="card admin-card shadow-sm">
        <div class="card-body p-4">
            <div class="row g-3">
                <div class="col-md-2"><strong>ID</strong><div>{{ $userAdmin['id'] }}</div></div>
                <div class="col-md-4"><strong>First Name</strong><div>{{ $userAdmin['first_name'] }}</div></div>
                <div class="col-md-4"><strong>Last Name</strong><div>{{ $userAdmin['last_name'] }}</div></div>
                <div class="col-md-2"><strong>Associated Swimmer</strong><div>{{ $swimmerName }}</div></div>
                <div class="col-md-12"><strong>Password Hash</strong><div><code>{{ (string) ($userAdmin['password'] ?? '') }}</code></div></div>
            </div>
        </div>
    </div>
@endsection


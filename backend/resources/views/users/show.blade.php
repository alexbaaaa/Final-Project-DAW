@extends('app.back')

@section('title', 'App Users Details')

@section('content')
    @php
        $userTypeLabel = match ($user['user_type']) {
            'legal_guardian' => 'Legal Guardian',
            'other' => 'Other',
            default => 'Swimmer',
        };
        $userTypeClass = match ($user['user_type']) {
            'legal_guardian' => 'bg-warning-subtle text-warning-emphasis border border-warning-subtle',
            'other' => 'bg-secondary-subtle text-secondary-emphasis border border-secondary-subtle',
            default => 'bg-primary-subtle text-primary-emphasis border border-primary-subtle',
        };
        $swimmerName = count($userSwimmerNames) ? implode(', ', $userSwimmerNames) : 'N/A';
        $isEnabled = (bool) ($user['is_enabled'] ?? true);
        $isUnderageSwimmer = $user['user_type'] === 'swimmer'
            && $user->birth_date !== null
            && $user->birth_date->age < 16;
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
                <div class="col-md-2"><strong>User Type</strong><div><span class="badge rounded-pill {{ $userTypeClass }}">{{ $userTypeLabel }}</span></div></div>
                <div class="col-md-4">
                    <strong>Status</strong>
                    <div>
                        @if($isEnabled)
                            <span class="badge text-bg-success">Enabled</span>
                        @elseif($isUnderageSwimmer)
                            <span class="badge text-bg-secondary">Disabled - Under 16</span>
                        @else
                            <span class="badge text-bg-secondary">Disabled</span>
                        @endif
                    </div>
                </div>
                <div class="col-md-6"><strong>Associated Swimmers</strong><div>{{ $swimmerName }}</div></div>
            </div>
        </div>
    </div>
@endsection

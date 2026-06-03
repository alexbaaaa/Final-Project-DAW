@extends('app.back')

@section('title', 'Home')

@section('content')
    <div class="row justify-content-center py-3 py-lg-4">
        <div class="col-xl-8">
            <div class="card admin-card shadow-sm">
                <div class="card-body text-center p-4 p-lg-5">
                    <h1 class="h3 admin-page-title mb-3">Admin Dashboard</h1>
                    <p class="admin-subtle mb-4">
                        Manage swimmers, events, times and users from the navigation menu.
                    </p>
                    <a href="{{ route('admin.events.create') }}" class="btn admin-btn-warning px-4">
                        Create Event
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection

@extends('app.app')

@section('title', 'Admin Area - Welcome')

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-7">
            <div class="card admin-card shadow-sm">
                <div class="card-body p-4 p-lg-5 text-center">
                    <span class="badge admin-badge mb-3">Admin Area</span>
                    <h1 class="h3 admin-page-title mb-3">Welcome to Swimming Up Admin</h1>
                    <p class="admin-subtle mb-4">
                        This is the management area where you can organize swimmers, events and times.
                    </p>
                    <a href="{{ route('admin.home') }}" class="btn admin-btn-primary px-4">Enter the App</a>
                </div>
            </div>
        </div>
    </div>
@endsection

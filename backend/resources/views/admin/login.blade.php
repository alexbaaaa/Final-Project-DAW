@extends('app.app')

@section('title', 'Admin Area - Login')

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-5">
            <div class="card admin-card shadow-sm">
                <div class="card-body p-4 p-lg-5">
                    <h1 class="h4 admin-page-title mb-2">Admin Login</h1>
                    <p class="admin-subtle mb-4">Access the Swimming Up administration area.</p>

                    @if(session('status_error') || $showError)
                        <div class="alert alert-danger" role="alert">
                            {{ session('status_error', 'Password and alias do not match.') }}
                        </div>
                    @endif

                    <form action="{{ route('admin.authenticate') }}" method="post" class="d-grid gap-3">
                        @csrf
                        <div>
                            <label for="alias" class="form-label">Alias</label>
                            <input type="text" id="alias" name="alias" class="form-control" value="{{ old('alias') }}" placeholder="Enter alias" required>
                        </div>

                        <div>
                            <label for="password" class="form-label">Password</label>
                            <input type="password" id="password" name="password" class="form-control" placeholder="Enter password" required>
                        </div>

                        <button type="submit" class="btn admin-btn-primary">Login</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

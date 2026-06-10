@extends('app.app')

@section('title', 'Change Password')

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-5">
            <div class="card admin-card shadow-sm">
                <div class="card-body p-4 p-lg-5">
                    <h1 class="h4 admin-page-title mb-2">Change Password</h1>
                    <p class="admin-subtle mb-4">
                        Your current password was generated automatically. You must set a new one before accessing the admin area.
                    </p>

                    @if($errors->any())
                        <div class="alert alert-danger" role="alert">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('admin.password.update') }}" method="post" class="d-grid gap-3">
                        @csrf
                        <div>
                            <label for="password" class="form-label">New Password</label>
                            <input type="password" id="password" name="password" class="form-control" required>
                        </div>

                        <div>
                            <label for="password_confirmation" class="form-label">Repeat Password</label>
                            <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" required>
                        </div>

                        <small class="text-secondary">
                            Minimum 8 characters, at least one uppercase letter and at least one non-alphanumeric character.
                        </small>

                        <button type="submit" class="btn admin-btn-primary">Update Password</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

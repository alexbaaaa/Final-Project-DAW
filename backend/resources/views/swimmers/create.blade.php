@extends('app.back')

@section('title', 'Create Swimmer')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
        <div>
            <h1 class="h3 admin-page-title mb-1">Create Swimmer</h1>
            <p class="admin-subtle mb-0">Add a new swimmer record.</p>
        </div>
        <a href="{{ route('admin.swimmers.index') }}" class="btn btn-outline-secondary">Back</a>
    </div>

    <div class="card admin-card shadow-sm">
        <div class="card-body p-4">
            <form action="{{ route('admin.swimmers.store') }}" method="post" class="row g-3">
                @csrf
                <div class="col-md-6">
                    <label for="first_name" class="form-label">First Name</label>
                    <input type="text" id="first_name" name="first_name" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label for="last_name" class="form-label">Last Name</label>
                    <input type="text" id="last_name" name="last_name" class="form-control" required>
                </div>
                <div class="col-md-4">
                    <label for="age" class="form-label">Age</label>
                    <input type="number" id="age" name="age" class="form-control" min="1" required>
                </div>
                <div class="col-md-4">
                    <label for="category" class="form-label">Category</label>
                    <select id="category" name="category" class="form-select" required>
                        <option value="Prebenjamin">Prebenjamin</option>
                        <option value="Benjamin">Benjamin</option>
                        <option value="Alevin">Alevin</option>
                        <option value="Infantil">Infantil</option>
                        <option value="Junior">Junior</option>
                        <option value="Absoluto Joven">Absoluto Joven</option>
                        <option value="Absoluto" selected>Absoluto</option>
                        <option value="Master">Master</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="gender" class="form-label">Gender</label>
                    <select id="gender" name="gender" class="form-select" required>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                        <option value="Other" selected>Other</option>
                    </select>
                </div>
                <div class="col-12 d-flex justify-content-end gap-2 pt-2">
                    <a href="{{ route('admin.swimmers.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    <button type="submit" class="btn admin-btn-primary">Create</button>
                </div>
            </form>
        </div>
    </div>
@endsection

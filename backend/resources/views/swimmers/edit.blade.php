@extends('app.back')

@section('title', 'Update Swimmer')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
        <div>
            <h1 class="h3 admin-page-title mb-1">Update Swimmer</h1>
            <p class="admin-subtle mb-0">Modify swimmer information.</p>
        </div>
        <a href="{{ route('admin.swimmers.index') }}" class="btn btn-outline-secondary">Back</a>
    </div>

    <div class="card admin-card shadow-sm">
        <div class="card-body p-4">
            <form action="{{ route('admin.swimmers.update', $swimmer['id']) }}" method="post" class="row g-3" onsubmit="return confirm('Do you want to apply these changes?');">
                @csrf
                @method('PUT')
                <div class="col-md-6">
                    <label for="first_name" class="form-label">First Name</label>
                    <input type="text" id="first_name" name="first_name" class="form-control" value="{{ $swimmer['first_name'] }}" required>
                </div>
                <div class="col-md-6">
                    <label for="last_name" class="form-label">Last Name</label>
                    <input type="text" id="last_name" name="last_name" class="form-control" value="{{ $swimmer['last_name'] }}" required>
                </div>
                <div class="col-md-4">
                    <label for="age" class="form-label">Age</label>
                    <input type="number" id="age" name="age" class="form-control" min="1" value="{{ $swimmer['age'] }}" required>
                </div>
                <div class="col-md-4">
                    <label for="category" class="form-label">Category</label>
                    <input type="text" id="category" name="category" class="form-control" value="{{ $swimmer['category'] }}" required>
                </div>
                <div class="col-md-4">
                    <label for="gender" class="form-label">Gender</label>
                    <select id="gender" name="gender" class="form-select" required>
                        <option value="Male" @if($swimmer['gender'] === 'Male') selected @endif>Male</option>
                        <option value="Female" @if($swimmer['gender'] === 'Female') selected @endif>Female</option>
                        <option value="Other" @if($swimmer['gender'] === 'Other') selected @endif>Other</option>
                    </select>
                </div>
                <div class="col-12 d-flex justify-content-end gap-2 pt-2">
                    <a href="{{ route('admin.swimmers.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    <button type="submit" class="btn admin-btn-warning">Update</button>
                </div>
            </form>
        </div>
    </div>
@endsection

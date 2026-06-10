@extends('app.back')

@section('title', 'Create Training Group')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
        <div>
            <h1 class="h3 admin-page-title mb-1">Create Training Group</h1>
            <p class="admin-subtle mb-0">Add a training schedule for one or more categories.</p>
        </div>
        <a href="{{ route('admin.training_groups.index') }}" class="btn btn-outline-secondary">Back</a>
    </div>

    <div class="card admin-card shadow-sm">
        <div class="card-body p-4">
            @include('training-groups._form', [
                'action' => route('admin.training_groups.store'),
                'cancelRoute' => route('admin.training_groups.index'),
                'dayOptions' => $dayOptions,
                'method' => null,
                'selectedCategories' => $selectedCategories,
                'submitClass' => 'admin-btn-primary',
                'submitLabel' => 'Create',
                'trainingGroup' => null,
            ])
        </div>
    </div>
@endsection

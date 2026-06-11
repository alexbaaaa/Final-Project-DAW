@extends('app.back')

@section('title', 'Update Training Group')

@section('content')
    @php
        $storedCategories = array_filter(array_map('trim', explode(',', (string) $trainingGroup['categories'])));
        $concreteCategories = array_values(array_filter($categoryOptions, fn ($category) => $category !== 'All'));

        if (in_array('All', $storedCategories, true) || count(array_diff($concreteCategories, $storedCategories)) === 0) {
            $storedCategories = ['All'];
        }
    @endphp

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
        <div>
            <h1 class="h3 admin-page-title mb-1">Update Training Group</h1>
            <p class="admin-subtle mb-0">Modify training group information.</p>
        </div>
        <a href="{{ route('admin.training_groups.index') }}" class="btn btn-outline-secondary">Back</a>
    </div>

    <div class="card admin-card shadow-sm">
        <div class="card-body p-4">
            @include('training-groups._form', [
                'action' => route('admin.training_groups.update', $trainingGroup['id']),
                'cancelRoute' => route('admin.training_groups.index'),
                'dayOptions' => $dayOptions,
                'method' => 'PUT',
                'selectedCategories' => $storedCategories,
                'submitClass' => 'admin-btn-warning',
                'submitLabel' => 'Update',
                'trainingGroup' => $trainingGroup,
            ])
        </div>
    </div>
@endsection

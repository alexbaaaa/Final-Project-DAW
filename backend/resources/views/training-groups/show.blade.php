@extends('app.back')

@section('title', 'Training Group Details')

@section('content')
    @php
        $trainingGroupCategories = array_filter(array_map('trim', explode(',', (string) $trainingGroup['categories'])));
        $concreteCategories = array_values(array_filter($categoryOptions, fn ($category) => $category !== 'All'));

        if (in_array('All', $trainingGroupCategories, true) || count(array_diff($concreteCategories, $trainingGroupCategories)) === 0) {
            $trainingGroupCategories = ['All'];
        }
    @endphp

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
        <h1 class="h3 admin-page-title mb-0">Training Group Details</h1>
        <a href="{{ route('admin.training_groups.index') }}" class="btn btn-outline-secondary">Back</a>
    </div>

    <div class="card admin-card shadow-sm">
        <div class="card-body p-4">
            <div class="row g-3">
                <div class="col-md-2"><strong>ID</strong><div>{{ $trainingGroup['id'] }}</div></div>
                <div class="col-md-5"><strong>Name</strong><div>{{ $trainingGroup['name'] }}</div></div>
                <div class="col-md-12">
                    <strong>Categories</strong>
                    <div class="d-flex flex-wrap gap-1 mt-1">
                        @foreach($trainingGroupCategories as $category)
                            <span class="badge text-bg-secondary">{{ $category }}</span>
                        @endforeach
                    </div>
                </div>
                <div class="col-md-12">
                    <strong>Schedule</strong>
                    <div class="training-schedule-summary mt-2">
                        @forelse($trainingGroup->formattedSchedule() as $scheduleLine)
                            <div>{{ $scheduleLine }}</div>
                        @empty
                            <div class="text-secondary">No schedule blocks added.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

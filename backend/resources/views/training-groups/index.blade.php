@extends('app.back')

@section('title', 'Training Groups')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
        <div>
            <h1 class="h3 admin-page-title mb-1">Training Groups</h1>
            <p class="admin-subtle mb-0">Manage training schedules by swimmer category.</p>
        </div>
        <a href="{{ route('admin.training_groups.create') }}" class="btn admin-btn-primary">Create Training Group</a>
    </div>

    <div class="card admin-card shadow-sm">
        <div class="table-responsive">
            <table class="table admin-table align-middle mb-0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Categories</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($trainingGroups as $trainingGroup)
                        @php
                            $trainingGroupCategories = array_filter(array_map('trim', explode(',', (string) $trainingGroup['categories'])));
                            $concreteCategories = array_values(array_filter($categoryOptions, fn ($category) => $category !== 'All'));

                            if (in_array('All', $trainingGroupCategories, true) || count(array_diff($concreteCategories, $trainingGroupCategories)) === 0) {
                                $trainingGroupCategories = ['All'];
                            }
                        @endphp
                        <tr>
                            <td>{{ $trainingGroup['id'] }}</td>
                            <td>{{ $trainingGroup['name'] }}</td>
                            <td>
                                <div class="d-flex flex-wrap gap-1">
                                    @foreach($trainingGroupCategories as $category)
                                        <span class="badge text-bg-secondary">{{ $category }}</span>
                                    @endforeach
                                </div>
                            </td>
                            <td>
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="{{ route('admin.training_groups.show', $trainingGroup['id']) }}" class="btn btn-sm btn-outline-primary">Show</a>
                                    <a href="{{ route('admin.training_groups.edit', $trainingGroup['id']) }}" class="btn btn-sm btn-outline-warning">Update</a>
                                    <form action="{{ route('admin.training_groups.destroy', $trainingGroup['id']) }}" method="post" onsubmit="return confirm('Do you really want to delete this training group?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-secondary py-4">No training groups have been created yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection

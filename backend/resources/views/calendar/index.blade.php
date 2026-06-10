@extends('app.back')

@section('title', 'Calendar')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
        <div>
            <h1 class="h3 admin-page-title mb-1">Calendar</h1>
            <p class="admin-subtle mb-0">Manage special training and holiday days.</p>
        </div>
        <a href="{{ route('admin.calendar.create') }}" class="btn admin-btn-primary">Create Calendar Day</a>
    </div>

    <div class="card admin-card shadow-sm">
        <div class="table-responsive">
            <table class="table admin-table align-middle mb-0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Date</th>
                        <th>Type</th>
                        <th>Event</th>
                        <th>Day Scope</th>
                        <th>Categories</th>
                        <th>Title</th>
                        <th>Description</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($calendarDays as $calendarDay)
                        @php
                            $calendarCategories = array_filter(array_map('trim', explode(',', (string) $calendarDay['categories'])));
                            $concreteCategories = array_values(array_filter($categoryOptions, fn ($category) => $category !== 'All'));

                            if (in_array('All', $calendarCategories, true) || count(array_diff($concreteCategories, $calendarCategories)) === 0) {
                                $calendarCategories = ['All'];
                            }
                        @endphp
                        <tr>
                            <td>{{ $calendarDay['id'] }}</td>
                            <td>{{ $calendarDay->date->toDateString() }}</td>
                            <td>{{ $dayTypeLabels[$calendarDay['day_type']] ?? $calendarDay['day_type'] }}</td>
                            <td>{{ $calendarDay->event?->event_name ?? 'N/A' }}</td>
                            <td>{{ $dayScopeLabels[$calendarDay['day_scope']] ?? $calendarDay['day_scope'] ?? 'Full day' }}</td>
                            <td>
                                <div class="d-flex flex-wrap gap-1">
                                    @foreach($calendarCategories as $category)
                                        <span class="badge text-bg-secondary">{{ $category }}</span>
                                    @endforeach
                                </div>
                            </td>
                            <td>{{ $calendarDay['title'] ?? 'N/A' }}</td>
                            <td>{{ $calendarDay['description'] ?? 'N/A' }}</td>
                            <td>
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="{{ route('admin.calendar.show', $calendarDay['id']) }}" class="btn btn-sm btn-outline-primary">Show</a>
                                    <a href="{{ route('admin.calendar.edit', $calendarDay['id']) }}" class="btn btn-sm btn-outline-warning">Update</a>
                                    <form action="{{ route('admin.calendar.destroy', $calendarDay['id']) }}" method="post" onsubmit="return confirm('Do you really want to delete this calendar day?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection

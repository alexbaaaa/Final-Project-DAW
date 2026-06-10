@extends('app.back')

@section('title', 'Create Time')

@section('content')
    @php
        $distanceByStyle = [
            'Libres' => ['50m', '100m', '200m', '400m', '800m', '1500m'],
            'Espalda' => ['50m', '100m', '200m'],
            'Braza' => ['50m', '100m', '200m'],
            'Mariposa' => ['50m', '100m', '200m'],
            'Estilos' => ['200m', '400m'],
        ];

        $styleAliases = [
            'libres' => 'Libres',
            'freestyle' => 'Libres',
            'espalda' => 'Espalda',
            'backstroke' => 'Espalda',
            'braza' => 'Braza',
            'breaststroke' => 'Braza',
            'mariposa' => 'Mariposa',
            'butterfly' => 'Mariposa',
            'estilos' => 'Estilos',
            'medley' => 'Estilos',
        ];

        $rawTestType = (string) old('test_type', '50m Libres');
        $selectedStyle = 'Libres';
        $selectedDistance = '50m';

        if (preg_match('/(50m|100m|200m|400m|800m|1500m)/i', $rawTestType, $distanceMatches) === 1) {
            $selectedDistance = strtolower($distanceMatches[1]);
        }

        $styleFragment = str_ireplace($selectedDistance, '', $rawTestType);
        $styleFragment = str_replace('-', ' ', $styleFragment);
        $styleFragment = trim(preg_replace('/\s+/', ' ', $styleFragment) ?? '');
        $styleKey = strtolower($styleFragment);

        if (isset($styleAliases[$styleKey])) {
            $selectedStyle = $styleAliases[$styleKey];
        }

        if (!in_array($selectedDistance, $distanceByStyle[$selectedStyle], true)) {
            $selectedDistance = $distanceByStyle[$selectedStyle][0];
        }
    @endphp

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
        <div>
            <h1 class="h3 admin-page-title mb-1">Create Time</h1>
            <p class="admin-subtle mb-0">Add a new performance time record.</p>
        </div>
        <a href="{{ route('admin.times.index') }}" class="btn btn-outline-secondary">Back</a>
    </div>

    <div class="card admin-card shadow-sm">
        <div class="card-body p-4">
            <form action="{{ route('admin.times.store') }}" method="post" class="row g-3">
                @csrf
                <div class="col-md-4">
                    <label for="swimmer_id" class="form-label">Swimmer ID</label>
                    <input type="number" id="swimmer_id" name="swimmer_id" class="form-control" min="1" required>
                </div>
                <div class="col-md-4">
                    <label for="test_style" class="form-label">Style</label>
                    <select id="test_style" class="form-select" required>
                        @foreach(array_keys($distanceByStyle) as $style)
                            <option value="{{ $style }}" @if($selectedStyle === $style) selected @endif>{{ $style }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="test_distance" class="form-label">Distance</label>
                    <select id="test_distance" class="form-select" data-initial-distance="{{ $selectedDistance }}" required></select>
                </div>
                <input type="hidden" id="test_type" name="test_type" value="{{ $selectedDistance }} {{ $selectedStyle }}" required>
                <div class="col-md-4">
                    <label for="time" class="form-label">Time</label>
                    <input type="text" id="time" name="time" class="form-control" placeholder="00:00.00" required>
                </div>
                <div class="col-md-4">
                    <label for="date" class="form-label">Date</label>
                    <input type="date" id="date" name="date" class="form-control" required>
                </div>
                <div class="col-md-4">
                    <label for="location" class="form-label">Location</label>
                    <input type="text" id="location" name="location" class="form-control" required>
                </div>
                <div class="col-12 d-flex justify-content-end gap-2 pt-2">
                    <a href="{{ route('admin.times.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    <button type="submit" class="btn admin-btn-primary">Create</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    (() => {
        const styleField = document.getElementById('test_style');
        const distanceField = document.getElementById('test_distance');
        const testTypeField = document.getElementById('test_type');

        if (!styleField || !distanceField || !testTypeField) {
            return;
        }

        const distanceByStyle = {
            Libres: ['50m', '100m', '200m', '400m', '800m', '1500m'],
            Espalda: ['50m', '100m', '200m'],
            Braza: ['50m', '100m', '200m'],
            Mariposa: ['50m', '100m', '200m'],
            Estilos: ['200m', '400m'],
        };

        const refreshDistances = () => {
            const style = styleField.value;
            const allowedDistances = distanceByStyle[style] || [];
            const previousDistance = distanceField.value;

            distanceField.innerHTML = '';

            allowedDistances.forEach((distance) => {
                const option = document.createElement('option');
                option.value = distance;
                option.textContent = distance;
                distanceField.appendChild(option);
            });

            if (allowedDistances.includes(previousDistance)) {
                distanceField.value = previousDistance;
            }
        };

        const syncTestType = () => {
            testTypeField.value = `${distanceField.value} ${styleField.value}`.trim();
        };

        styleField.addEventListener('change', () => {
            refreshDistances();
            syncTestType();
        });

        distanceField.addEventListener('change', syncTestType);

        refreshDistances();

        const initialDistance = distanceField.dataset.initialDistance;
        if (initialDistance) {
            const exists = Array.from(distanceField.options).some((option) => option.value === initialDistance);
            if (exists) {
                distanceField.value = initialDistance;
            }
        }

        syncTestType();
    })();
</script>
@endpush

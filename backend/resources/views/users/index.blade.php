@extends('app.back')

@section('title', 'App Users')

@section('content')
    @php
        $currentAdminUser = $currentAdminUser ?? null;
    @endphp

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
        <div>
            <h1 class="h3 admin-page-title mb-1">App Users</h1>
            <p class="admin-subtle mb-0">Manage login users for the application.</p>
        </div>
        <div class="d-flex flex-wrap align-items-center justify-content-end gap-2">
            <form action="{{ route('admin.users.index') }}" method="get" data-users-filter-form>
                <label for="user_type_filter" class="visually-hidden">Filter by user type</label>
                <select id="user_type_filter" name="user_type" class="form-select form-select-sm" data-users-filter-select>
                    <option value="all" @selected($activeUserTypeFilter === 'all')>All</option>
                    <option value="swimmer" @selected($activeUserTypeFilter === 'swimmer')>Swimmer</option>
                    <option value="legal_guardian" @selected($activeUserTypeFilter === 'legal_guardian')>Legal Guardian</option>
                    <option value="other" @selected($activeUserTypeFilter === 'other')>Other</option>
                </select>
                <noscript>
                    <button type="submit" class="btn btn-sm btn-outline-secondary mt-2">Filter</button>
                </noscript>
            </form>
            <a href="{{ route('admin.users.create') }}" class="btn admin-btn-primary">Create User</a>
        </div>
    </div>

    <div id="usersTableContainer">
        @include('users.partials.table')
    </div>
@endsection

@push('scripts')
<script>
    (() => {
        const form = document.querySelector('[data-users-filter-form]');
        const select = document.querySelector('[data-users-filter-select]');
        const tableContainer = document.getElementById('usersTableContainer');

        if (!form || !select || !tableContainer) {
            return;
        }

        const buildUrl = () => {
            const url = new URL(form.action, window.location.href);
            const formData = new FormData(form);

            formData.forEach((value, key) => {
                url.searchParams.set(key, value);
            });

            return url;
        };

        const loadFilteredUsers = async () => {
            const url = buildUrl();

            select.disabled = true;
            tableContainer.setAttribute('aria-busy', 'true');
            tableContainer.classList.add('opacity-50');

            try {
                const response = await fetch(url, {
                    method: 'GET',
                    headers: {
                        Accept: 'text/html',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    credentials: 'same-origin',
                });

                if (!response.ok) {
                    throw new Error(`Unexpected response status ${response.status}`);
                }

                tableContainer.innerHTML = await response.text();
                window.history.replaceState({}, '', url);
            } catch (error) {
                form.submit();
            } finally {
                select.disabled = false;
                tableContainer.removeAttribute('aria-busy');
                tableContainer.classList.remove('opacity-50');
            }
        };

        form.addEventListener('submit', (event) => {
            event.preventDefault();
            loadFilteredUsers();
        });

        select.addEventListener('change', () => {
            loadFilteredUsers();
        });
    })();
</script>
@endpush

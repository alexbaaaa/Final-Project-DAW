@php
    $currentAdminUser = $currentAdminUser ?? null;
@endphp

<div class="card admin-card shadow-sm">
    <div class="table-responsive">
        <table class="table admin-table align-middle mb-0">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Alias</th>
                    <th>First Name</th>
                    <th>Last Name</th>
                    <th>User Type</th>
                    <th>Status</th>
                    <th>Associated Swimmers</th>
                    <th>Password</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $user)
                    @php
                        $userTypeLabel = match ($user['user_type']) {
                            'legal_guardian' => 'Legal Guardian',
                            'other' => 'Other',
                            default => 'Swimmer',
                        };
                        $userTypeClass = match ($user['user_type']) {
                            'legal_guardian' => 'bg-warning-subtle text-warning-emphasis border border-warning-subtle',
                            'other' => 'bg-secondary-subtle text-secondary-emphasis border border-secondary-subtle',
                            default => 'bg-primary-subtle text-primary-emphasis border border-primary-subtle',
                        };
                        $swimmerNames = $userSwimmerNames[(int) $user['id']] ?? [];
                        $isEnabled = (bool) ($user['is_enabled'] ?? true);
                        $isUnderageSwimmer = $user['user_type'] === 'swimmer'
                            && $user->birth_date !== null
                            && $user->birth_date->age < 16;
                    @endphp
                    <tr>
                        <td>{{ $user['id'] }}</td>
                        <td><code>{{ $user['alias'] }}</code></td>
                        <td>{{ $user['first_name'] }}</td>
                        <td>{{ $user['last_name'] }}</td>
                        <td><span class="badge rounded-pill {{ $userTypeClass }}">{{ $userTypeLabel }}</span></td>
                        <td>
                            @if($isEnabled)
                                <span class="badge text-bg-success">Enabled</span>
                            @elseif($isUnderageSwimmer)
                                <span class="badge text-bg-secondary">Disabled - Under 16</span>
                            @else
                                <span class="badge text-bg-secondary">Disabled</span>
                            @endif
                        </td>
                        <td>
                            @if($user['user_type'] === 'legal_guardian' && count($swimmerNames) > 1)
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        {{ count($swimmerNames) }} swimmers
                                    </button>
                                    <ul class="dropdown-menu">
                                        @foreach($swimmerNames as $swimmerName)
                                            <li><span class="dropdown-item-text">{{ $swimmerName }}</span></li>
                                        @endforeach
                                    </ul>
                                </div>
                            @else
                                {{ count($swimmerNames) ? implode(', ', $swimmerNames) : 'N/A' }}
                            @endif
                        </td>
                        <td>
                            @if($user['must_change_password'])
                                <span class="badge text-bg-warning">Default</span>
                            @else
                                <span class="badge text-bg-success">Changed</span>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('admin.users.show', $user['id']) }}" class="btn btn-sm btn-outline-primary">Show</a>
                                <a href="{{ route('admin.users.edit', $user['id']) }}" class="btn btn-sm btn-outline-warning">Update</a>
                                @if($currentAdminUser?->isRoot())
                                    <form action="{{ route('admin.users.reset_password', $user['id']) }}" method="post" onsubmit="return confirm('Reset this user password to its default value?');">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-sm btn-outline-secondary">Reset Password</button>
                                    </form>
                                @endif
                                <form action="{{ route('admin.users.destroy', $user['id']) }}" method="post" onsubmit="return confirm('Do you really want to delete this user?');">
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

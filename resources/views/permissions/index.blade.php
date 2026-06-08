@extends('layouts.app')
@section('title', 'Permissions Management')
@section('page-title', 'Permissions Management')

@push('styles')
<style>
    .perm-table th, .perm-table td { vertical-align: middle; font-size: .85rem; }
    .perm-table thead th { background: #2c3e50; color: #fff; font-size: .75rem; text-transform: uppercase; letter-spacing: .5px; }
    .role-col { text-align: center; width: 110px; }
    .toggle-btn { cursor: pointer; border: none; background: none; font-size: 1.3rem; transition: transform .1s; }
    .toggle-btn:hover { transform: scale(1.2); }
    .toggle-btn.has  { color: #198754; }
    .toggle-btn.none { color: #dee2e6; }
    .toggle-btn.locked { cursor: not-allowed; opacity: .5; }
    .group-header td { background: #f8f9fa; font-weight: 700; font-size: .75rem; text-transform: uppercase;
        letter-spacing: .5px; color: #6c757d; padding: 6px 12px !important; }
</style>
@endpush

@section('content')

<div class="alert alert-info d-flex align-items-center gap-2 mb-3">
    <i class="bi bi-info-circle-fill fs-5"></i>
    <span>Only <strong>Super Admin</strong> can change role permissions. Changes take effect immediately for all users with that role.</span>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center py-3">
        <span><i class="bi bi-shield-lock me-2"></i>Role → Permission Matrix</span>
        <div class="d-flex gap-2 align-items-center small text-muted">
            <i class="bi bi-check-circle-fill text-success"></i> Has permission &nbsp;
            <i class="bi bi-circle text-secondary" style="color:#dee2e6!important"></i> No permission
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table perm-table mb-0">
                <thead>
                    <tr>
                        <th style="width:260px">Permission</th>
                        @foreach($roles as $role)
                        <th class="role-col">
                            <div>{{ ucfirst(str_replace('_', ' ', $role->name)) }}</div>
                            <small style="opacity:.7;font-weight:400">{{ $role->permissions->count() }} perms</small>
                        </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($grouped as $group => $perms)
                    <tr class="group-header">
                        <td colspan="{{ $roles->count() + 1 }}">{{ $group }}</td>
                    </tr>
                    @foreach($perms as $perm)
                    <tr>
                        <td class="ps-3">{{ ucfirst($perm->name) }}</td>
                        @foreach($roles as $role)
                        <td class="role-col">
                            @php $has = $role->permissions->contains('id', $perm->id); @endphp
                            @if($role->name === 'super_admin')
                                {{-- Super admin always has all permissions, locked --}}
                                <button class="toggle-btn has locked" disabled title="Super Admin always has all permissions">
                                    <i class="bi bi-check-circle-fill"></i>
                                </button>
                            @else
                                <button class="toggle-btn {{ $has ? 'has' : 'none' }}"
                                    data-role="{{ $role->id }}"
                                    data-perm="{{ $perm->id }}"
                                    title="{{ $has ? 'Click to revoke' : 'Click to grant' }}"
                                    onclick="togglePermission(this)">
                                    <i class="bi bi-{{ $has ? 'check-circle-fill' : 'circle' }}"></i>
                                </button>
                            @endif
                        </td>
                        @endforeach
                    </tr>
                    @endforeach
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function togglePermission(btn) {
    var roleId = btn.dataset.role;
    var permId = btn.dataset.perm;

    btn.disabled = true;
    btn.style.opacity = '.5';

    fetch('{{ route('permissions.toggle') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ role_id: roleId, permission_id: permId })
    })
    .then(function(res) { return res.json(); })
    .then(function(data) {
        btn.disabled = false;
        btn.style.opacity = '1';

        if (data.has) {
            btn.classList.remove('none');
            btn.classList.add('has');
            btn.querySelector('i').className = 'bi bi-check-circle-fill';
            btn.title = 'Click to revoke';
        } else {
            btn.classList.remove('has');
            btn.classList.add('none');
            btn.querySelector('i').className = 'bi bi-circle';
            btn.title = 'Click to grant';
        }

        // Update permission count in header
        var colIdx = btn.closest('td').cellIndex;
        var header = document.querySelector('.perm-table thead tr th:nth-child(' + (colIdx + 1) + ') small');
        if (header) {
            var count = document.querySelectorAll('.perm-table tbody td:nth-child(' + (colIdx + 1) + ') .toggle-btn.has:not(.locked)').length;
            header.textContent = count + ' perms';
        }
    })
    .catch(function() {
        btn.disabled = false;
        btn.style.opacity = '1';
        alert('Error updating permission. Please try again.');
    });
}
</script>
@endpush
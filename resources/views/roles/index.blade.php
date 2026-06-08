@extends('layouts.app')
@section('title', 'Role Management')
@section('page-title', 'Role Management')

@push('styles')
<style>
.role-card { border: 2px solid #eaecf0; border-radius: 12px; }
.role-card.system { border-color: #3498db22; background: #f8fbff; }
.role-card.custom { border-color: #e74c3c22; }
</style>
@endpush

@section('content')

<div class="d-flex justify-content-between align-items-center mb-3">
    <div class="text-muted small">
        System roles (Admin/Manager/Cashier) ke permissions change karo, ya naye custom roles banao.
    </div>
    <a href="{{ route('roles.create') }}" class="btn btn-danger btn-sm">
        <i class="bi bi-plus-circle me-1"></i>New Custom Role
    </a>
</div>

{{-- System Roles --}}
<div class="section-title mb-2" style="font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.8px;color:#9ca3af">
    System Roles — Permissions edit karo
</div>
<div class="row g-3 mb-4">
    @foreach($systemRoles as $role)
    @php
        $colors = ['admin'=>'#e74c3c','manager'=>'#3498db','cashier'=>'#198754'];
        $icons  = ['admin'=>'👑','manager'=>'🏪','cashier'=>'💼'];
        $color  = $colors[$role->name] ?? '#6c757d';
        $icon   = $icons[$role->name]  ?? '🔧';
    @endphp
    <div class="col-md-4">
        <div class="card role-card system h-100">
            <div class="card-header py-3 d-flex justify-content-between align-items-center"
                style="border-left: 3px solid {{ $color }}">
                <div class="d-flex align-items-center gap-2">
                    <span style="font-size:1.2rem">{{ $icon }}</span>
                    <div>
                        <div class="fw-semibold">{{ ucfirst($role->name) }}</div>
                        <div class="text-muted" style="font-size:.72rem">
                            {{ $role->users_count }} user(s) · {{ count($role->permissionsList) }} permissions
                        </div>
                    </div>
                </div>
                <a href="{{ route('roles.edit', $role) }}" class="btn btn-sm btn-outline-primary" title="Edit Permissions">
                    <i class="bi bi-pencil me-1"></i>Edit
                </a>
            </div>
            <div class="card-body py-2 px-3">
                @if(count($role->permissionsList) > 0)
                <div class="d-flex flex-wrap gap-1">
                    @foreach(collect($role->permissionsList)->take(8) as $perm)
                    <span class="badge bg-light text-dark border" style="font-size:.68rem">{{ $perm }}</span>
                    @endforeach
                    @if(count($role->permissionsList) > 8)
                    <span class="badge bg-secondary" style="font-size:.68rem">+{{ count($role->permissionsList) - 8 }} more</span>
                    @endif
                </div>
                @else
                <div class="text-muted small text-center py-2">Koi permission nahi</div>
                @endif
            </div>
        </div>
    </div>
    @endforeach
</div>

{{-- Custom Roles --}}
<div class="section-title mb-2" style="font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.8px;color:#9ca3af">
    Custom Roles
</div>
@if($customRoles->isEmpty())
<div class="card">
    <div class="card-body text-center py-4">
        <i class="bi bi-shield-plus text-muted" style="font-size:2.5rem"></i>
        <div class="mt-2 fw-semibold text-muted">Koi custom role nahi hai</div>
        <div class="text-muted small mb-3">Apni zarurat ke hisab se role banao</div>
        <a href="{{ route('roles.create') }}" class="btn btn-danger btn-sm">
            <i class="bi bi-plus-circle me-1"></i>Pehla Custom Role Banao
        </a>
    </div>
</div>
@else
<div class="row g-3">
    @foreach($customRoles as $role)
    <div class="col-md-6 col-lg-4">
        <div class="card role-card custom h-100">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold"
                        style="width:34px;height:34px;background:#e67e22;font-size:.85rem;flex-shrink:0">
                        {{ strtoupper(substr($role->name, 0, 1)) }}
                    </div>
                    <div>
                        <div class="fw-semibold">{{ $role->name }}</div>
                        <div class="text-muted" style="font-size:.72rem">{{ $role->users_count }} user(s) assigned</div>
                    </div>
                </div>
                <div class="d-flex gap-1">
                    <a href="{{ route('roles.edit', $role) }}" class="btn btn-sm btn-outline-warning" title="Edit">
                        <i class="bi bi-pencil"></i>
                    </a>
                    @if($role->users_count == 0)
                    <form method="POST" action="{{ route('roles.destroy', $role) }}"
                        onsubmit="return confirm('Delete role \'{{ $role->name }}\'?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                    </form>
                    @else
                    <button class="btn btn-sm btn-outline-secondary" disabled
                        title="{{ $role->users_count }} user(s) is role pe hain">
                        <i class="bi bi-trash"></i>
                    </button>
                    @endif
                </div>
            </div>
            <div class="card-body py-2 px-3">
                @if(count($role->permissionsList) > 0)
                <div class="d-flex flex-wrap gap-1">
                    @foreach($role->permissionsList as $perm)
                    <span class="badge bg-light text-dark border" style="font-size:.68rem">{{ $perm }}</span>
                    @endforeach
                </div>
                @else
                <div class="text-center text-muted py-2 small">
                    <i class="bi bi-exclamation-circle me-1"></i>Koi permission assign nahi
                </div>
                @endif
            </div>
        </div>
    </div>
    @endforeach
</div>
@endif
@endsection

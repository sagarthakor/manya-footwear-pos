@extends('layouts.app')
@php $isSystem = isset($role) && in_array($role->name, ['admin','manager','cashier']); @endphp
@section('title', isset($role) ? 'Edit Role' : 'New Role')
@section('page-title', isset($role) ? 'Edit Role: '.$role->name : 'Create Custom Role')

@push('styles')
<style>
    .perm-group-header {
        background: #f8f9fa;
        font-size: .72rem; font-weight: 700;
        text-transform: uppercase; letter-spacing: .8px;
        color: #6c757d; padding: .5rem .75rem;
        border-bottom: 1px solid #f0f2f8;
    }
    .perm-item {
        display: flex; align-items: center; gap: .6rem;
        padding: .5rem .75rem;
        border-bottom: 1px solid #f9fafb;
        cursor: pointer;
        transition: background .12s;
    }
    .perm-item:last-child { border-bottom: none; }
    .perm-item:hover { background: #fafbfd; }
    .perm-item input[type=checkbox] { width: 16px; height: 16px; accent-color: #e74c3c; cursor: pointer; }
    .perm-item label { cursor: pointer; font-size: .87rem; margin: 0; flex: 1; }

    .select-all-btn {
        font-size: .72rem; color: #e74c3c; cursor: pointer;
        text-decoration: none; border: none; background: none; padding: 0;
    }
    .select-all-btn:hover { text-decoration: underline; }
    .selected-count {
        font-size: .7rem; color: #6b7280;
    }
</style>
@endpush

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-9">

        <form action="{{ isset($role) ? route('roles.update', $role) : route('roles.store') }}" method="POST">
            @csrf
            @if(isset($role)) @method('PUT') @endif

            {{-- Role Name --}}
            <div class="card mb-3">
                <div class="card-header py-3">
                    <i class="bi bi-shield-plus me-2"></i>Role Details
                </div>
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Role Name</label>
                            @if($isSystem)
                            <input type="text" class="form-control bg-light"
                                value="{{ $role->name }}" disabled>
                            <div class="text-muted small mt-1">
                                <i class="bi bi-lock me-1"></i>System role — naam change nahi ho sakta, sirf permissions change ho sakti hain
                            </div>
                            @else
                            <input type="text" name="name"
                                class="form-control @error('name') is-invalid @enderror"
                                value="{{ old('name', $role->name ?? '') }}"
                                placeholder="e.g. Store Incharge, Billing Staff"
                                {{ isset($role) ? '' : 'required' }}>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="text-muted small mt-1">Letters, numbers, spaces, hyphens allowed</div>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <div class="alert alert-info mb-0 py-2 small">
                                <i class="bi bi-info-circle me-1"></i>
                                Is role pe assigned users sirf neeche di gayi permissions ke hisab se kaam kar sakte hain.
                                Agar koi permission nahi di toh user sirf dashboard dekh sakta hai.
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Permissions --}}
            <div class="card mb-3">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-key me-2"></i>Assign Permissions</span>
                    <div class="d-flex align-items-center gap-3">
                        <span class="selected-count" id="totalCount">0 selected</span>
                        <button type="button" class="select-all-btn" onclick="toggleAllPerms(true)">Select All</button>
                        <span class="text-muted small">|</span>
                        <button type="button" class="select-all-btn" onclick="toggleAllPerms(false)">Clear All</button>
                    </div>
                </div>
                <div class="card-body p-0">
                    @if($grouped->isEmpty())
                        <div class="text-center text-muted py-4">
                            Koi active permission nahi hai. Modules enable karo pehle.
                        </div>
                    @else
                        <div class="row g-0">
                            @foreach($grouped as $group => $perms)
                            <div class="col-md-6" style="border-bottom:1px solid #f0f2f8">
                                <div class="perm-group-header d-flex justify-content-between">
                                    <span>{{ $group }}</span>
                                    <button type="button" class="select-all-btn"
                                        onclick="toggleGroup('{{ $group }}', true)">All</button>
                                </div>
                                @foreach($perms as $perm)
                                <div class="perm-item" onclick="togglePerm('perm-{{ $perm->id }}')">
                                    <input type="checkbox"
                                        name="permissions[]"
                                        id="perm-{{ $perm->id }}"
                                        value="{{ $perm->id }}"
                                        data-group="{{ $group }}"
                                        onchange="updateCount()"
                                        {{ in_array($perm->id, $rolePerms ?? []) ? 'checked' : '' }}
                                        onclick="event.stopPropagation()">
                                    <label for="perm-{{ $perm->id }}">{{ ucfirst($perm->name) }}</label>
                                </div>
                                @endforeach
                            </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-danger">
                    <i class="bi bi-{{ isset($role) ? 'save' : 'plus-circle' }} me-1"></i>
                    {{ isset($role) ? 'Update Role' : 'Create Role' }}
                </button>
                <a href="{{ route('roles.index') }}" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function togglePerm(id) {
    var cb = document.getElementById(id);
    if (cb) { cb.checked = !cb.checked; updateCount(); }
}

function toggleAllPerms(check) {
    document.querySelectorAll('input[name="permissions[]"]').forEach(function(cb) {
        cb.checked = check;
    });
    updateCount();
}

function toggleGroup(group, check) {
    document.querySelectorAll('input[data-group="' + group + '"]').forEach(function(cb) {
        cb.checked = check;
    });
    updateCount();
}

function updateCount() {
    var count = document.querySelectorAll('input[name="permissions[]"]:checked').length;
    document.getElementById('totalCount').textContent = count + ' selected';
}

// Init count on load
updateCount();
</script>
@endpush
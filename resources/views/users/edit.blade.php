@extends('layouts.app')
@section('title', 'Edit User')
@section('page-title', 'Edit User')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header py-3">
                <i class="bi bi-pencil me-2"></i>Edit: {{ $user->name }}
                @if($user->id === auth()->id())
                    <span class="badge bg-secondary ms-2">This is you</span>
                @endif
                @if($user->isSuperAdmin())
                    <span class="badge ms-2 text-white" style="background:#6f42c1">⭐ Super Admin</span>
                @endif
            </div>
            <div class="card-body">
                @if($user->isSuperAdmin())
                <div class="alert alert-warning mb-3">
                    <i class="bi bi-shield-lock-fill me-2"></i>
                    <strong>Super Admin account</strong> — This account will always remain active.
                </div>
                @endif

                <form action="{{ route('users.update', $user) }}" method="POST">
                    @csrf @method('PUT')
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Full Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                            value="{{ old('name', $user->name) }}" required>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Email Address <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                            value="{{ old('email', $user->email) }}" required>
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Role <span class="text-danger">*</span></label>
                        @if($user->id === auth()->id())
                            <input type="hidden" name="role" value="{{ $user->role }}">
                            <input type="text" class="form-control bg-light" value="{{ $user->role_label }}" disabled>
                            <small class="text-muted">You cannot change your own role</small>
                        @else
                            <select name="role" class="form-select @error('role') is-invalid @enderror" required>
                                @php
                                    $systemLabels = [
                                        'super_admin' => '⭐ Super Admin',
                                        'admin'       => '👑 Admin',
                                        'manager'     => '🏪 Manager',
                                        'cashier'     => '💼 Cashier',
                                    ];
                                    $systemRoleNames = ['super_admin', 'admin', 'manager', 'cashier'];
                                @endphp
                                <optgroup label="System Roles">
                                    @foreach(array_intersect($allowedRoles, $systemRoleNames) as $role)
                                    <option value="{{ $role }}" {{ old('role', $user->role) === $role ? 'selected' : '' }}>
                                        {{ $systemLabels[$role] }}
                                    </option>
                                    @endforeach
                                </optgroup>
                                @if(count($customRoles) > 0)
                                <optgroup label="Custom Roles">
                                    @foreach($customRoles as $role)
                                    <option value="{{ $role }}" {{ old('role', $user->role) === $role ? 'selected' : '' }}>
                                        🔧 {{ $role }}
                                    </option>
                                    @endforeach
                                </optgroup>
                                @endif
                            </select>
                            @error('role')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        @endif
                    </div>

                    @if(!$user->isSuperAdmin())
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_active"
                                {{ old('is_active', $user->is_active) ? 'checked' : '' }}
                                {{ $user->id === auth()->id() ? 'disabled' : '' }}>
                            <label class="form-check-label fw-semibold">Active (can login)</label>
                        </div>
                        @if($user->id === auth()->id())
                            <input type="hidden" name="is_active" value="1">
                        @endif
                    </div>
                    @else
                    <input type="hidden" name="is_active" value="1">
                    @endif

                    <hr>
                    <p class="text-muted small mb-2">
                        <i class="bi bi-info-circle me-1"></i>
                        Leave password fields empty to keep the current password unchanged.
                    </p>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">New Password</label>
                        <input type="password" name="password"
                            class="form-control @error('password') is-invalid @enderror"
                            placeholder="Leave blank to keep current password">
                        @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Confirm New Password</label>
                        <input type="password" name="password_confirmation" class="form-control">
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-save me-1"></i>Update User
                        </button>
                        <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

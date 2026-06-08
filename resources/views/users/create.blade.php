@extends('layouts.app')
@section('title', 'Add User')
@section('page-title', 'Add New User')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header py-3"><i class="bi bi-person-plus me-2"></i>New User Details</div>
            <div class="card-body">
                <form action="{{ route('users.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Full Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                            value="{{ old('name') }}" required placeholder="e.g. Ramesh Kumar">
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Email Address <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                            value="{{ old('email') }}" required placeholder="e.g. ramesh@shop.com">
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Role <span class="text-danger">*</span></label>
                        <select name="role" class="form-select @error('role') is-invalid @enderror" required>
                            <option value="">-- Select Role --</option>
                            <optgroup label="System Roles">
                                @php
                                    $systemLabels = [
                                        'super_admin' => '⭐ Super Admin — Full System Access',
                                        'admin'       => '👑 Admin — Full Access (can manage users)',
                                        'manager'     => '🏪 Manager — Products + Reports + Sales',
                                        'cashier'     => '💼 Cashier — Billing & Stock only',
                                    ];
                                    $systemRoles = ['super_admin', 'admin', 'manager', 'cashier'];
                                @endphp
                                @foreach(array_intersect($allowedRoles, $systemRoles) as $role)
                                <option value="{{ $role }}" {{ old('role') === $role ? 'selected' : '' }}>
                                    {{ $systemLabels[$role] }}
                                </option>
                                @endforeach
                            </optgroup>
                            @if(count($customRoles) > 0)
                            <optgroup label="Custom Roles">
                                @foreach($customRoles as $role)
                                <option value="{{ $role }}" {{ old('role') === $role ? 'selected' : '' }}>
                                    🔧 {{ $role }}
                                </option>
                                @endforeach
                            </optgroup>
                            @endif
                        </select>
                        @error('role')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        @if(count($customRoles) > 0)
                        <div class="text-muted small mt-1">
                            <i class="bi bi-info-circle me-1"></i>Custom roles ke permissions <a href="{{ route('roles.index') }}">Roles page</a> pe manage karo
                        </div>
                        @endif
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Password <span class="text-danger">*</span></label>
                        <input type="password" name="password"
                            class="form-control @error('password') is-invalid @enderror"
                            required placeholder="Minimum 6 characters">
                        @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Confirm Password <span class="text-danger">*</span></label>
                        <input type="password" name="password_confirmation" class="form-control" required>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-person-check me-1"></i>Add User
                        </button>
                        <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

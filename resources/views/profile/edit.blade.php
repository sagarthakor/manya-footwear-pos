@extends('layouts.app')
@section('title', 'My Profile')
@section('page-title', 'My Profile')

@section('content')
<div class="row g-4">

    {{-- Profile Info --}}
    <div class="col-md-6">
        <div class="card">
            <div class="card-header py-3">
                <i class="bi bi-person-circle me-2"></i>Profile Information
            </div>
            <div class="card-body">
                @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                @endif

                {{-- Role badge --}}
                <div class="text-center mb-4">
                    <div class="rounded-circle d-inline-flex align-items-center justify-content-center fw-bold text-white mb-3"
                        style="width:72px;height:72px;font-size:1.8rem;
                            background:{{ $user->role === 'super_admin' ? '#6f42c1' : ($user->role === 'admin' ? '#dc3545' : ($user->role === 'manager' ? '#0d6efd' : '#198754')) }}">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>
                    <div>
                        <span class="badge rounded-pill text-white px-3 py-2"
                            style="font-size:.85rem;background:{{ $user->role === 'super_admin' ? '#6f42c1' : ($user->role === 'admin' ? '#dc3545' : ($user->role === 'manager' ? '#0d6efd' : '#198754')) }}">
                            {{ $user->role_label }}
                        </span>
                    </div>
                </div>

                <form method="POST" action="{{ route('profile.update') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Full Name</label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                            value="{{ old('name', $user->name) }}" required>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Email Address</label>
                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                            value="{{ old('email', $user->email) }}" required>
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-save me-1"></i>Update Profile
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- Change Password --}}
    <div class="col-md-6">
        <div class="card">
            <div class="card-header py-3">
                <i class="bi bi-shield-lock me-2"></i>Change Password
            </div>
            <div class="card-body">
                @if($errors->has('current_password'))
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ $errors->first('current_password') }}
                </div>
                @endif

                <form method="POST" action="{{ route('profile.password') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Current Password</label>
                        <input type="password" name="current_password"
                            class="form-control @error('current_password') is-invalid @enderror" required>
                        @error('current_password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">New Password</label>
                        <input type="password" name="password"
                            class="form-control @error('password') is-invalid @enderror"
                            required placeholder="Minimum 6 characters">
                        @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Confirm New Password</label>
                        <input type="password" name="password_confirmation" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-outline-danger">
                        <i class="bi bi-key me-1"></i>Change Password
                    </button>
                </form>
            </div>
        </div>

        {{-- Role permissions summary --}}
        <div class="card mt-3">
            <div class="card-header py-3">
                <i class="bi bi-shield-check me-2"></i>Your Permissions
            </div>
            <div class="card-body">
                @php
                    $rolePerms = [
                        'super_admin' => ['Full system access', 'Manage all users & roles', 'Change permissions'],
                        'admin'       => ['Manage users', 'Full inventory & sales', 'Reports & settings'],
                        'manager'     => ['Products & inventory', 'Sales & billing', 'Reports & customers'],
                        'cashier'     => ['POS billing', 'Add stock', 'View sales history'],
                    ];
                    $perms = $rolePerms[auth()->user()->role] ?? [];
                @endphp
                <ul class="list-unstyled mb-0">
                    @foreach($perms as $perm)
                    <li class="py-1"><i class="bi bi-check-circle-fill text-success me-2"></i>{{ $perm }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>

</div>
@endsection
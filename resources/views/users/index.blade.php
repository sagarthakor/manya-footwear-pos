@extends('layouts.app')
@section('title', 'User Management')
@section('page-title', 'User Management')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center py-3">
        <span><i class="bi bi-people me-2"></i>System Users</span>
        <a href="{{ route('users.create') }}" class="btn btn-sm btn-danger">
            <i class="bi bi-person-plus"></i> Add User
        </a>
    </div>
    <div class="card-body p-0">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light">
                <tr>
                    <th>User</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $user)
                <tr class="{{ $user->isSuperAdmin() ? 'table-warning' : '' }}">
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold text-white"
                                style="width:36px;height:36px;font-size:.85rem;flex-shrink:0;background:
                                    {{ $user->role === 'super_admin' ? '#6f42c1' :
                                      ($user->role === 'admin' ? '#e74c3c' :
                                      ($user->role === 'manager' ? '#3498db' :
                                      ($user->role === 'cashier' ? '#27ae60' : '#e67e22'))) }}">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </div>
                            <div>
                                <div class="fw-semibold">{{ $user->name }}</div>
                                @if($user->id === auth()->id())
                                    <span class="badge bg-secondary" style="font-size:.65rem">You</span>
                                @endif
                                @if($user->isSuperAdmin())
                                    <span class="badge text-white" style="font-size:.65rem;background:#6f42c1">Protected</span>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td class="text-muted small">{{ $user->email }}</td>
                    <td>
                        @php
                            $roleConfig = match($user->role) {
                                'super_admin' => ['style' => 'background:#6f42c1', 'icon' => '⭐', 'label' => 'Super Admin'],
                                'admin'       => ['style' => 'background:#dc3545', 'icon' => '👑', 'label' => 'Admin'],
                                'manager'     => ['style' => 'background:#0d6efd', 'icon' => '🏪', 'label' => 'Manager'],
                                'cashier'     => ['style' => 'background:#198754', 'icon' => '💼', 'label' => 'Cashier'],
                                default       => ['style' => 'background:#e67e22', 'icon' => '🔧', 'label' => $user->role],
                            };
                        @endphp
                        <span class="badge rounded-pill text-white"
                            style="font-size:.8rem;padding:.4em .8em;{{ $roleConfig['style'] }}">
                            {{ $roleConfig['icon'] }} {{ $roleConfig['label'] }}
                        </span>
                    </td>
                    <td>
                        <span class="badge bg-{{ $user->is_active ? 'success' : 'danger' }}">
                            {{ $user->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td class="text-muted small">{{ $user->created_at->format('d M Y') }}</td>
                    <td>
                        <div class="btn-group btn-group-sm">
                            @if(!$user->isSuperAdmin() || auth()->user()->isSuperAdmin())
                            <a href="{{ route('users.edit', $user) }}" class="btn btn-outline-warning">
                                <i class="bi bi-pencil me-1"></i>Edit
                            </a>
                            @endif

                            @if(!$user->isSuperAdmin() && $user->id !== auth()->id())
                            <form method="POST" action="{{ route('users.destroy', $user) }}"
                                onsubmit="return confirm('Delete user {{ $user->name }}?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger">
                                    <i class="bi bi-trash me-1"></i>Delete
                                </button>
                            </form>
                            @elseif($user->isSuperAdmin())
                            <button class="btn btn-outline-secondary" disabled title="Super Admin cannot be deleted">
                                <i class="bi bi-shield-lock"></i>
                            </button>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<div class="card mt-3">
    <div class="card-header py-3"><i class="bi bi-shield-check me-2"></i>Role Permissions Overview</div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-3">
                <div class="border rounded p-3" style="border-color:#6f42c1!important">
                    <h6 class="fw-bold mb-2" style="color:#6f42c1">⭐ Super Admin</h6>
                    <ul class="list-unstyled small mb-0">
                        <li>✅ Full system access</li>
                        <li>✅ Manage all users</li>
                        <li>✅ Create Super Admins</li>
                        <li class="text-danger fw-semibold mt-1">🔒 Cannot be deleted</li>
                        <li class="text-danger fw-semibold">🔒 Cannot be deactivated</li>
                    </ul>
                </div>
            </div>
            <div class="col-md-3">
                <div class="border border-danger rounded p-3">
                    <h6 class="text-danger fw-bold mb-2">👑 Admin</h6>
                    <ul class="list-unstyled small mb-0">
                        <li>✅ Manage users</li>
                        <li>✅ Products & Categories</li>
                        <li>✅ Sales & Reports</li>
                        <li>✅ Stock Management</li>
                        <li>❌ Cannot create Super Admin</li>
                    </ul>
                </div>
            </div>
            <div class="col-md-3">
                <div class="border border-primary rounded p-3">
                    <h6 class="text-primary fw-bold mb-2">🏪 Manager</h6>
                    <ul class="list-unstyled small mb-0">
                        <li>✅ Products & Categories</li>
                        <li>✅ Sales & Billing</li>
                        <li>✅ Stock Management</li>
                        <li>✅ Reports & Customers</li>
                        <li>❌ Cannot manage users</li>
                    </ul>
                </div>
            </div>
            <div class="col-md-3">
                <div class="border border-success rounded p-3">
                    <h6 class="text-success fw-bold mb-2">💼 Cashier</h6>
                    <ul class="list-unstyled small mb-0">
                        <li>✅ POS Billing</li>
                        <li>✅ Add Stock</li>
                        <li>✅ View Sales History</li>
                        <li>❌ No product management</li>
                        <li>❌ No reports or users</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

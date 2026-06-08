@extends('layouts.app')
@section('title', 'Customers')
@section('page-title', 'Customers')

@section('content')
<div class="card">
    <div class="card-header py-3"><i class="bi bi-people me-2"></i>All Customers</div>
    <div class="card-body border-bottom">
        <form method="GET" class="row g-2">
            <div class="col-md-5">
                <input type="text" name="search" class="form-control form-control-sm"
                    value="{{ request('search') }}" placeholder="Search by name or mobile number...">
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-sm btn-outline-primary">Search</button>
                <a href="{{ route('customers.index') }}" class="btn btn-sm btn-outline-secondary ms-1">Reset</a>
            </div>
        </form>
    </div>
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Name</th>
                    <th>Mobile</th>
                    <th>Total Purchase</th>
                    <th>Visits</th>
                    <th>Registered</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($customers as $customer)
                <tr>
                    <td class="fw-semibold">{{ $customer->name }}</td>
                    <td>{{ $customer->mobile ?? '-' }}</td>
                    <td class="fw-bold text-success">&#8377;{{ number_format($customer->total_purchase, 0) }}</td>
                    <td><span class="badge bg-info">{{ $customer->visit_count }}</span></td>
                    <td class="text-muted small">{{ $customer->created_at->format('d M Y') }}</td>
                    <td>
                        <a href="{{ route('customers.show', $customer) }}" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-eye me-1"></i>View
                        </a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center text-muted py-5">No customers found</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($customers->hasPages())
    <div class="card-footer">{{ $customers->links() }}</div>
    @endif
</div>
@endsection

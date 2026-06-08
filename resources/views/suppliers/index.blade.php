@extends('layouts.app')
@section('title', 'Suppliers')
@section('page-title', 'Suppliers')

@section('content')
<div class="card">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <span><i class="bi bi-truck me-2"></i>Suppliers</span>
        <a href="{{ route('suppliers.create') }}" class="btn btn-sm btn-danger">
            <i class="bi bi-plus"></i> Add Supplier
        </a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Name</th>
                        <th>Company</th>
                        <th>Phone</th>
                        <th>GST No.</th>
                        <th class="text-end">Payable</th>
                        <th class="text-end">Paid</th>
                        <th class="text-end">Outstanding</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($suppliers as $supplier)
                    @php
                        $outstanding = $supplier->total_payable - $supplier->total_paid;
                    @endphp
                    <tr>
                        <td>
                            <a href="{{ route('suppliers.show', $supplier) }}" class="text-decoration-none fw-semibold">
                                {{ $supplier->name }}
                            </a>
                        </td>
                        <td class="text-muted">{{ $supplier->company ?? '—' }}</td>
                        <td>{{ $supplier->phone ?? '—' }}</td>
                        <td class="small text-muted">{{ $supplier->gst_number ?? '—' }}</td>
                        <td class="text-end">&#8377;{{ number_format($supplier->total_payable, 2) }}</td>
                        <td class="text-end">&#8377;{{ number_format($supplier->total_paid, 2) }}</td>
                        <td class="text-end fw-semibold {{ $outstanding > 0 ? 'text-danger' : 'text-success' }}">
                            &#8377;{{ number_format($outstanding, 2) }}
                        </td>
                        <td>
                            <span class="badge bg-{{ $supplier->is_active ? 'success' : 'danger' }}">
                                {{ $supplier->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td>
                            <a href="{{ route('suppliers.show', $supplier) }}" class="btn btn-sm btn-outline-info" title="View">
                                <i class="bi bi-eye"></i>
                            </a>
                            <a href="{{ route('suppliers.edit', $supplier) }}" class="btn btn-sm btn-outline-warning" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form method="POST" action="{{ route('suppliers.destroy', $supplier) }}" class="d-inline"
                                onsubmit="return confirm('Delete supplier \'{{ addslashes($supplier->name) }}\'?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center text-muted py-4">
                            <i class="bi bi-truck fs-3 d-block mb-2 opacity-50"></i>
                            No suppliers found. <a href="{{ route('suppliers.create') }}">Add your first supplier</a>.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if(isset($suppliers) && method_exists($suppliers, 'hasPages') && $suppliers->hasPages())
    <div class="card-footer d-flex justify-content-between align-items-center">
        <small class="text-muted">
            Showing {{ $suppliers->firstItem() }}–{{ $suppliers->lastItem() }} of {{ $suppliers->total() }} suppliers
        </small>
        {{ $suppliers->links() }}
    </div>
    @endif
</div>
@endsection

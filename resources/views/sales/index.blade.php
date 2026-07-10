@extends('layouts.app')
@section('title', 'Sales History')
@section('page-title', 'Sales History')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center py-3">
        <span><i class="bi bi-receipt me-2"></i>Sales History</span>
        <a href="{{ route('sales.create') }}" class="btn btn-sm btn-danger">
            <i class="bi bi-plus"></i> New Sale
        </a>
    </div>
    <div class="card-body border-bottom">
        <form method="GET" class="row g-2">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control form-control-sm"
                    value="{{ request('search') }}" placeholder="Invoice no, customer name or mobile...">
            </div>
            <div class="col-md-3">
                <input type="date" name="date" class="form-control form-control-sm"
                    value="{{ request('date', today()->format('Y-m-d')) }}">
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-sm btn-outline-primary">Filter</button>
                <a href="{{ route('sales.index') }}" class="btn btn-sm btn-outline-secondary ms-1">All</a>
                @module('report_daily')
                <a href="{{ route('reports.daily', ['date' => request('date', today()->format('Y-m-d'))]) }}"
                    class="btn btn-sm btn-outline-info ms-1">Daily Report</a>
                @endmodule
            </div>
        </form>
    </div>
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Invoice</th>
                    <th>Customer</th>
                    <th>Items</th>
                    <th>Subtotal</th>
                    <th>GST</th>
                    <th>Discount</th>
                    <th>Total</th>
                    <th>Payment</th>
                    <th>Status</th>
                    <th>Time</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($sales as $sale)
                <tr>
                    <td class="fw-semibold">
                        <a href="{{ route('sales.show', $sale) }}" class="text-decoration-none">
                            {{ $sale->invoice_number }}
                        </a>
                    </td>
                    <td>
                        {{ $sale->customer?->name ?? 'Walk-in' }}
                        @if($sale->customer?->mobile)
                        <div class="text-muted small">{{ $sale->customer->mobile }}</div>
                        @endif
                    </td>
                    <td><span class="badge bg-secondary">{{ $sale->items->sum('quantity') }} pcs</span></td>
                    <td>&#8377;{{ number_format($sale->subtotal, 0) }}</td>
                    <td class="text-warning">
                        {{ $sale->tax_amount > 0 ? '₹'.number_format($sale->tax_amount, 2) : '-' }}
                    </td>
                    <td class="text-success">
                        {{ $sale->discount_amount > 0 ? '-₹'.number_format($sale->discount_amount,0) : '-' }}
                    </td>
                    <td class="fw-bold">&#8377;{{ number_format($sale->total_amount, 0) }}</td>
                    <td>
                        <span class="badge bg-{{ $sale->payment_method === 'cash' ? 'success' : 'info' }}">
                            {{ strtoupper($sale->payment_method) }}
                        </span>
                    </td>
                    <td>
                        <span class="badge bg-{{ $sale->status === 'completed' ? 'success' : 'warning' }}">
                            {{ ucfirst($sale->status) }}
                        </span>
                    </td>
                    <td class="small text-muted">{{ $sale->created_at->format('d/m H:i') }}</td>
                    <td>
                        <a href="{{ route('sales.receipt', $sale) }}" target="_blank"
                            class="btn btn-sm btn-outline-dark">
                            <i class="bi bi-printer me-1"></i>Print
                        </a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="11" class="text-center text-muted py-5">No sales found</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($sales->hasPages())
    <div class="card-footer">{{ $sales->links() }}</div>
    @endif
</div>
@endsection

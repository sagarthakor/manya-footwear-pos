@extends('layouts.app')
@section('title', $customer->name)
@section('page-title', $customer->name)

@section('content')
<div class="row g-3">
    <div class="col-md-4">
        <div class="card">
            <div class="card-body text-center">
                <div class="rounded-circle bg-danger text-white d-flex align-items-center justify-content-center mx-auto mb-3"
                    style="width:70px;height:70px;font-size:1.8rem">
                    {{ strtoupper(substr($customer->name, 0, 1)) }}
                </div>
                <h5 class="fw-bold">{{ $customer->name }}</h5>
                <div class="text-muted">{{ $customer->mobile ?? 'No mobile number' }}</div>
                @if($customer->email)
                <div class="text-muted small">{{ $customer->email }}</div>
                @endif
            </div>
            <div class="card-footer">
                <div class="row text-center">
                    <div class="col-6 border-end">
                        <div class="fw-bold text-success">&#8377;{{ number_format($customer->total_purchase, 0) }}</div>
                        <small class="text-muted">Total Purchase</small>
                    </div>
                    <div class="col-6">
                        <div class="fw-bold">{{ $customer->visit_count }}</div>
                        <small class="text-muted">Total Visits</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-8">
        <div class="card">
            <div class="card-header py-3"><i class="bi bi-receipt me-2"></i>Purchase History</div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Invoice</th>
                            <th>Items</th>
                            <th>Total</th>
                            <th>Payment</th>
                            <th>Date</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($customer->sales as $sale)
                        <tr>
                            <td class="fw-semibold">{{ $sale->invoice_number }}</td>
                            <td>{{ $sale->items->sum('quantity') }} pcs</td>
                            <td class="fw-bold">&#8377;{{ number_format($sale->total_amount, 0) }}</td>
                            <td><span class="badge bg-success">{{ strtoupper($sale->payment_method) }}</span></td>
                            <td class="text-muted small">{{ $sale->created_at->format('d M Y') }}</td>
                            <td>
                                <a href="{{ route('sales.receipt', $sale) }}" target="_blank"
                                    class="btn btn-sm btn-outline-dark">
                                    <i class="bi bi-printer"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="text-center text-muted py-4">No purchase history</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

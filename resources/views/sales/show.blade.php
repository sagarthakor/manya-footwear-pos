@extends('layouts.app')
@section('title', $sale->invoice_number)
@section('page-title', 'Sale Details: ' . $sale->invoice_number)

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between py-3">
                <span><i class="bi bi-receipt me-2"></i>{{ $sale->invoice_number }}</span>
                <a href="{{ route('sales.receipt', $sale) }}" target="_blank" class="btn btn-sm btn-dark">
                    <i class="bi bi-printer me-1"></i>Print Receipt
                </a>
            </div>
            <div class="card-body p-0">
                <table class="table mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Product</th>
                            <th>Barcode</th>
                            <th>Size / Color</th>
                            <th class="text-center">Qty</th>
                            <th class="text-end">Rate</th>
                            <th class="text-end">Discount</th>
                            <th class="text-end">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($sale->items as $item)
                        <tr>
                            <td class="fw-semibold">{{ $item->product_name }}</td>
                            <td class="font-monospace small">{{ $item->product_barcode }}</td>
                            <td class="small">{{ $item->product_size ?? '-' }} / {{ $item->product_color ?? '-' }}</td>
                            <td class="text-center">{{ $item->quantity }}</td>
                            <td class="text-end">&#8377;{{ number_format($item->unit_price, 2) }}</td>
                            <td class="text-end text-success">
                                {{ $item->discount > 0 ? '-₹'.number_format($item->discount,2) : '-' }}
                            </td>
                            <td class="text-end fw-bold">&#8377;{{ number_format($item->total_price, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <td colspan="6" class="text-end">Subtotal:</td>
                            <td class="text-end">&#8377;{{ number_format($sale->subtotal,2) }}</td>
                        </tr>
                        @if($sale->discount_amount > 0)
                        <tr>
                            <td colspan="6" class="text-end text-success">Discount:</td>
                            <td class="text-end text-success">-&#8377;{{ number_format($sale->discount_amount,2) }}</td>
                        </tr>
                        @endif
                        <tr class="fw-bold">
                            <td colspan="6" class="text-end fs-6">TOTAL:</td>
                            <td class="text-end text-danger fs-6">&#8377;{{ number_format($sale->total_amount,2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-header py-3">Payment Information</div>
            <div class="card-body">
                <table class="table table-sm table-borderless mb-0">
                    <tr><td class="text-muted">Amount Paid:</td><td class="fw-bold">&#8377;{{ number_format($sale->paid_amount,2) }}</td></tr>
                    <tr><td class="text-muted">Payment Method:</td><td><span class="badge bg-success">{{ strtoupper($sale->payment_method) }}</span></td></tr>
                    <tr><td class="text-muted">Change Given:</td><td>&#8377;{{ number_format($sale->change_amount,2) }}</td></tr>
                    <tr><td class="text-muted">Status:</td><td><span class="badge bg-success">{{ ucfirst($sale->status) }}</span></td></tr>
                    <tr><td class="text-muted">Date &amp; Time:</td><td>{{ $sale->created_at->format('d M Y h:i A') }}</td></tr>
                    <tr><td class="text-muted">Cashier:</td><td>{{ $sale->user->name }}</td></tr>
                </table>
            </div>
        </div>
        @if($sale->customer)
        <div class="card mt-3">
            <div class="card-header py-3">Customer Details</div>
            <div class="card-body">
                <h6 class="mb-1">{{ $sale->customer->name }}</h6>
                <div class="text-muted">{{ $sale->customer->mobile }}</div>
                <hr>
                <div class="small text-muted">Total Purchases: &#8377;{{ number_format($sale->customer->total_purchase,0) }}</div>
                <div class="small text-muted">Total Visits: {{ $sale->customer->visit_count }}</div>
                <a href="{{ route('customers.show', $sale->customer) }}" class="btn btn-sm btn-outline-primary mt-2">
                    View Customer
                </a>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection

@extends('layouts.app')
@section('title', 'Daily Report')
@section('page-title', 'Daily Sales Report')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <form method="GET" class="d-flex gap-2 align-items-center flex-wrap">
        <input type="date" name="date" class="form-control form-control-sm" style="width:150px"
            value="{{ $date->format('Y-m-d') }}">
        <select name="payment_method" class="form-select form-select-sm" style="width:150px">
            <option value="">All Payments</option>
            <option value="cash"   {{ ($payment ?? '') == 'cash'   ? 'selected' : '' }}>💵 Cash</option>
            <option value="upi"    {{ ($payment ?? '') == 'upi'    ? 'selected' : '' }}>📱 UPI</option>
            <option value="card"   {{ ($payment ?? '') == 'card'   ? 'selected' : '' }}>💳 Card</option>
            <option value="others" {{ ($payment ?? '') == 'others' ? 'selected' : '' }}>🔄 Others</option>
        </select>
        <button type="submit" class="btn btn-sm btn-danger">
            <i class="bi bi-funnel me-1"></i>Filter
        </button>
        @if(request('payment_method'))
        <a href="{{ route('reports.daily', ['date' => $date->format('Y-m-d')]) }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-x me-1"></i>Clear
        </a>
        @endif
    </form>
    <button onclick="window.print()" class="btn btn-sm btn-dark no-print">
        <i class="bi bi-printer me-1"></i>Print
    </button>
</div>
@if($payment ?? false)
<div class="alert alert-info py-2 mb-3 d-flex align-items-center gap-2">
    <i class="bi bi-funnel-fill"></i>
    Filtered by: <strong>{{ strtoupper($payment) }}</strong> payments only
</div>
@endif

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="stat-card" style="background:linear-gradient(135deg,#e74c3c,#c0392b)">
            <div class="stat-value">&#8377;{{ number_format($totalRevenue, 0) }}</div>
            <div style="opacity:.85">Total Revenue</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card" style="background:linear-gradient(135deg,#3498db,#2980b9)">
            <div class="stat-value">{{ $totalOrders }}</div>
            <div style="opacity:.85">Total Orders</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card" style="background:linear-gradient(135deg,#27ae60,#229954)">
            <div class="stat-value">{{ $totalItems }}</div>
            <div style="opacity:.85">Items Sold</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card" style="background:linear-gradient(135deg,#9b59b6,#8e44ad)">
            <div class="stat-value">&#8377;{{ $totalOrders > 0 ? number_format($totalRevenue / $totalOrders, 0) : 0 }}</div>
            <div style="opacity:.85">Avg Bill Value</div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="stat-card" style="background:linear-gradient(135deg,#f39c12,#e67e22)">
            <div class="stat-value">&#8377;{{ number_format($totalGST, 2) }}</div>
            <div style="opacity:.85">Total GST Collected</div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header py-3">
                <i class="bi bi-calendar-check me-2"></i>Sales on {{ $date->format('d F Y') }}
            </div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Invoice</th>
                            <th>Customer</th>
                            <th>Items</th>
                            <th>GST</th>
                            <th>Total</th>
                            <th>Payment</th>
                            <th>Time</th>
                            <th class="no-print"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($sales as $sale)
                        <tr>
                            <td class="fw-semibold">{{ $sale->invoice_number }}</td>
                            <td>{{ $sale->customer?->name ?? 'Walk-in' }}</td>
                            <td>{{ $sale->items->sum('quantity') }} pcs</td>
                            <td class="text-warning">{{ $sale->tax_amount > 0 ? '₹'.number_format($sale->tax_amount, 2) : '—' }}</td>
                            <td class="fw-bold">&#8377;{{ number_format($sale->total_amount, 0) }}</td>
                            <td>
                                <span class="badge bg-{{ $sale->payment_method === 'cash' ? 'success' : 'info' }}">
                                    {{ strtoupper($sale->payment_method) }}
                                </span>
                            </td>
                            <td class="text-muted small">{{ $sale->created_at->format('h:i A') }}</td>
                            <td class="no-print">
                                <a href="{{ route('sales.receipt', $sale) }}" target="_blank"
                                    class="btn btn-sm btn-outline-dark">
                                    <i class="bi bi-printer"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="8" class="text-center text-muted py-4">No sales on this date</td></tr>
                        @endforelse
                    </tbody>
                    @if($sales->count())
                    <tfoot class="table-light fw-bold">
                        <tr>
                            <td colspan="3">Total ({{ $sales->count() }} bills)</td>
                            <td class="text-warning">&#8377;{{ number_format($totalGST, 2) }}</td>
                            <td>&#8377;{{ number_format($totalRevenue, 0) }}</td>
                            <td colspan="3"></td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-header py-3"><i class="bi bi-pie-chart me-2"></i>Payment Breakdown</div>
            <div class="card-body">
                @forelse($paymentBreakdown as $method => $data)
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="badge bg-{{ $method === 'cash' ? 'success' : 'info' }} me-2">
                        {{ strtoupper($method) }}
                    </span>
                    <div class="text-end">
                        <div class="fw-bold">&#8377;{{ number_format($data['amount'], 0) }}</div>
                        <small class="text-muted">{{ $data['count'] }} bill(s)</small>
                    </div>
                </div>
                @empty
                <div class="text-center text-muted py-3">No data available</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection

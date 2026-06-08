@extends('layouts.app')
@section('title', 'Monthly Report')
@section('page-title', 'Monthly Report')

@section('content')
{{-- Month Picker --}}
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('reports.monthly') }}" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label fw-semibold">Select Month</label>
                <input type="month" name="month" class="form-control" value="{{ request('month', now()->format('Y-m')) }}">
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">Payment Type</label>
                <select name="payment_method" class="form-select">
                    <option value="">All Payments</option>
                    <option value="cash"   {{ request('payment_method') == 'cash'   ? 'selected' : '' }}>💵 Cash</option>
                    <option value="upi"    {{ request('payment_method') == 'upi'    ? 'selected' : '' }}>📱 UPI</option>
                    <option value="card"   {{ request('payment_method') == 'card'   ? 'selected' : '' }}>💳 Card</option>
                    <option value="others" {{ request('payment_method') == 'others' ? 'selected' : '' }}>🔄 Others</option>
                </select>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-danger">
                    <i class="bi bi-bar-chart me-1"></i>Generate Report
                </button>
                @if(request('payment_method'))
                <a href="{{ route('reports.monthly', ['month' => request('month')]) }}" class="btn btn-outline-secondary ms-1">
                    <i class="bi bi-x"></i> Clear Filter
                </a>
                @endif
            </div>
        </form>
    </div>
</div>

@if(isset($monthlyData))
{{-- Summary Stats --}}
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="stat-card" style="background:linear-gradient(135deg,#27ae60,#229954)">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="small mb-1" style="opacity:.85">Total Revenue</div>
                    <div class="stat-value">&#8377;{{ number_format($monthlyData['revenue'], 0) }}</div>
                </div>
                <i class="bi bi-currency-rupee stat-icon"></i>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card" style="background:linear-gradient(135deg,#3498db,#2980b9)">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="small mb-1" style="opacity:.85">Total Orders</div>
                    <div class="stat-value">{{ number_format($monthlyData['orders']) }}</div>
                </div>
                <i class="bi bi-receipt stat-icon"></i>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card" style="background:linear-gradient(135deg,#e74c3c,#c0392b)">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="small mb-1" style="opacity:.85">Total Expenses</div>
                    <div class="stat-value">&#8377;{{ number_format($monthlyData['expenses'], 0) }}</div>
                </div>
                <i class="bi bi-wallet2 stat-icon"></i>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="stat-card" style="background:linear-gradient(135deg,#f39c12,#e67e22)">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="small mb-1" style="opacity:.85">Total GST Collected</div>
                    <div class="stat-value">&#8377;{{ number_format($monthlyData['gst'], 2) }}</div>
                </div>
                <i class="bi bi-receipt-cutoff stat-icon"></i>
            </div>
        </div>
    </div>
</div>

{{-- Daily Breakdown --}}
<div class="card">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <span>
            <i class="bi bi-table me-2"></i>Daily Breakdown —
            <span class="text-muted fw-normal">{{ \Carbon\Carbon::parse(request('month', now()->format('Y-m')))->format('F Y') }}</span>
        </span>
        @if($payment ?? false)
        <span class="badge bg-info text-dark">
            <i class="bi bi-funnel me-1"></i>{{ strtoupper($payment) }} only
        </span>
        @endif
    </div>
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Date</th>
                    <th class="text-center">Orders</th>
                    <th class="text-end">GST</th>
                    <th class="text-end">Revenue</th>
                    <th class="text-end">Avg Order Value</th>
                </tr>
            </thead>
            <tbody>
                @forelse($monthlyData['daily'] ?? [] as $day)
                <tr>
                    <td class="fw-semibold">
                        {{ \Carbon\Carbon::parse($day->date)->format('d M Y (D)') }}
                    </td>
                    <td class="text-center">
                        <span class="badge bg-{{ $day->orders > 0 ? 'primary' : 'secondary' }}">
                            {{ $day->orders }}
                        </span>
                    </td>
                    <td class="text-end text-warning">
                        {{ ($day->gst ?? 0) > 0 ? '₹'.number_format($day->gst, 2) : '—' }}
                    </td>
                    <td class="text-end fw-semibold {{ $day->revenue > 0 ? '' : 'text-muted' }}">
                        &#8377;{{ number_format($day->revenue, 2) }}
                    </td>
                    <td class="text-end text-muted">
                        &#8377;{{ $day->orders > 0 ? number_format($day->revenue / $day->orders, 2) : '0.00' }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center text-muted py-4">
                        <i class="bi bi-calendar3 fs-3 d-block mb-2 opacity-50"></i>
                        No sales data for this month
                    </td>
                </tr>
                @endforelse
            </tbody>
            @if(isset($monthlyData['daily']) && count($monthlyData['daily']) > 0)
            <tfoot class="table-light">
                <tr>
                    <td class="fw-bold">Total</td>
                    <td class="text-center fw-bold">{{ $monthlyData['orders'] }}</td>
                    <td class="text-end fw-bold text-warning">&#8377;{{ number_format($monthlyData['gst'], 2) }}</td>
                    <td class="text-end fw-bold">&#8377;{{ number_format($monthlyData['revenue'], 2) }}</td>
                    <td class="text-end fw-bold text-muted">
                        &#8377;{{ $monthlyData['orders'] > 0 ? number_format($monthlyData['revenue'] / $monthlyData['orders'], 2) : '0.00' }}
                    </td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
</div>
@else
<div class="card">
    <div class="card-body text-center py-5 text-muted">
        <i class="bi bi-calendar3 fs-1 d-block mb-3 opacity-50"></i>
        Select a month and click <strong>Generate Report</strong>.
    </div>
</div>
@endif
@endsection

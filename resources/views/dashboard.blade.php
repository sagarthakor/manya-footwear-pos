@extends('layouts.app')
@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@push('styles')
<style>
/* ── KPI Cards ─────────────────────────────────────────────── */
.kpi-card {
    border-radius: 16px; padding: 1.25rem 1.4rem;
    color: #fff; position: relative; overflow: hidden;
    box-shadow: 0 6px 24px rgba(0,0,0,.13);
    border: none; height: 100%;
}
.kpi-card .kpi-icon {
    position: absolute; right: 16px; top: 50%; transform: translateY(-50%);
    font-size: 3rem; opacity: .12; line-height: 1;
}
.kpi-card .kpi-val  { font-size: 1.55rem; font-weight: 800; line-height: 1.1; letter-spacing: -.5px; }
.kpi-card .kpi-lbl  { font-size: .72rem; opacity: .88; margin-top: 5px; font-weight: 600; letter-spacing: .4px; text-transform: uppercase; }
.kpi-card .kpi-sub  { font-size: .7rem; opacity: .65; margin-top: 3px; }
.kpi-card .kpi-badge {
    display: inline-block; background: rgba(255,255,255,.2);
    border-radius: 20px; padding: 1px 8px; font-size: .67rem; font-weight: 600;
    margin-top: 6px;
}

/* ── Section header ────────────────────────────────────────── */
.sec-hdr {
    font-size: .7rem; font-weight: 800; letter-spacing: 1.2px;
    text-transform: uppercase; color: #9ca3af; margin-bottom: .65rem;
    display: flex; align-items: center; gap: 6px;
}
.sec-hdr::before {
    content: ''; width: 3px; height: 12px;
    background: #e74c3c; border-radius: 2px;
}

/* ── Month snapshot cards ──────────────────────────────────── */
.snap-card {
    border-radius: 14px; padding: 1rem 1.2rem;
    background: #fff; border: 1px solid #eef0f7;
    box-shadow: 0 2px 10px rgba(0,0,0,.05); height: 100%;
}
.snap-card .snap-val { font-size: 1.35rem; font-weight: 800; line-height: 1.1; margin-top: 4px; }
.snap-card .snap-lbl { font-size: .72rem; color: #9ca3af; font-weight: 600; text-transform: uppercase; letter-spacing: .4px; }
.snap-card .snap-icon { font-size: 1.4rem; margin-bottom: 4px; }

/* ── Payment breakdown ─────────────────────────────────────── */
.pay-row { display: flex; justify-content: space-between; align-items: center; padding: .45rem .75rem; border-radius: 8px; }
.pay-row:hover { background: #f9fafb; }
.pay-dot { width: 9px; height: 9px; border-radius: 50%; flex-shrink: 0; }

/* ── Table ─────────────────────────────────────────────────── */
.sales-table td, .sales-table th { padding: .6rem .8rem; vertical-align: middle; }
.invoice-link { font-size: .8rem; font-weight: 700; color: #e74c3c; text-decoration: none; }
.invoice-link:hover { text-decoration: underline; }

/* ── Stock bar ─────────────────────────────────────────────── */
.stock-row { display: flex; align-items: center; padding: .5rem .75rem; border-radius: 8px; gap: .6rem; }
.stock-row:hover { background: #f9fafb; }
.stock-bar-wrap { flex: 1; height: 4px; background: #f0f2f8; border-radius: 4px; overflow: hidden; }
.stock-bar { height: 100%; border-radius: 4px; }

/* ── Rank badge ────────────────────────────────────────────── */
.rbadge { width: 22px; height: 22px; border-radius: 6px; background: #e74c3c; color: #fff; font-size: .65rem; font-weight: 800; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.rbadge.r2 { background: #6c757d; }
.rbadge.r3 { background: #fd7e14; }
.rbadge.r4, .rbadge.r5 { background: #dee2e6; color: #555; }

/* ── Cashier avatar ────────────────────────────────────────── */
.cav { width: 32px; height: 32px; border-radius: 9px; font-size: .8rem; font-weight: 700; display: flex; align-items: center; justify-content: center; flex-shrink: 0; color: #fff; }

/* ── Quick action btn ──────────────────────────────────────── */
.qa-btn { border-radius: 10px; font-size: .8rem; font-weight: 600; padding: .45rem .9rem; border: 1.5px solid transparent; }

/* ── Activity dot ──────────────────────────────────────────── */
.adot { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; margin-top: 5px; }

@media (max-width: 575px) {
    .kpi-card .kpi-val { font-size: 1.2rem; }
    .kpi-card { padding: 1rem 1.1rem; }
}
</style>
@endpush

@section('content')

{{-- ══════════════════════════════════════════════════════════ --}}
{{-- CASHIER DASHBOARD --}}
{{-- ══════════════════════════════════════════════════════════ --}}
@if(!auth()->user()->isManager())

{{-- Greeting bar --}}
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h6 class="mb-0 fw-bold">Welcome back, {{ auth()->user()->name }} 👋</h6>
        <div class="text-muted" style="font-size:.8rem">{{ now()->format('l, d F Y') }}</div>
    </div>
    <a href="{{ route('sales.create') }}" class="btn btn-danger btn-sm">
        <i class="bi bi-cart3 me-1"></i>New Sale
    </a>
</div>

@can('view sales')
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="kpi-card" style="background:linear-gradient(135deg,#e74c3c,#c0392b)">
            <i class="bi bi-currency-rupee kpi-icon"></i>
            <div class="kpi-val">&#8377;{{ number_format($myRevenue ?? 0, 0) }}</div>
            <div class="kpi-lbl">My Revenue Today</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-card" style="background:linear-gradient(135deg,#3498db,#2980b9)">
            <i class="bi bi-receipt kpi-icon"></i>
            <div class="kpi-val">{{ $myOrders ?? 0 }}</div>
            <div class="kpi-lbl">Bills Generated</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-card" style="background:linear-gradient(135deg,#27ae60,#229954)">
            <i class="bi bi-box-seam kpi-icon"></i>
            <div class="kpi-val">{{ $myItems ?? 0 }}</div>
            <div class="kpi-lbl">Items Sold</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-card" style="background:linear-gradient(135deg,#f39c12,#e67e22)">
            <i class="bi bi-arrow-return-left kpi-icon"></i>
            <div class="kpi-val">{{ $myReturns ?? 0 }}</div>
            <div class="kpi-lbl">Returns Today</div>
        </div>
    </div>
</div>
@endcan

<div class="row g-3">
    <div class="col-md-8">
        @can('view sales')
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center py-3">
                <span class="fw-semibold"><i class="bi bi-receipt me-2 text-danger"></i>My Recent Sales</span>
                <a href="{{ route('sales.create') }}" class="btn btn-sm btn-danger qa-btn"><i class="bi bi-plus me-1"></i>New Sale</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 sales-table">
                        <thead class="table-light">
                            <tr><th>Invoice</th><th>Customer</th><th>Items</th><th>Amount</th><th>Pay</th><th>Time</th></tr>
                        </thead>
                        <tbody>
                            @forelse($recentSales as $sale)
                            <tr>
                                <td><a href="{{ route('sales.show', $sale) }}" class="invoice-link">{{ $sale->invoice_number }}</a></td>
                                <td class="small">{{ $sale->customer?->name ?? 'Walk-in' }}</td>
                                <td><span class="badge bg-secondary rounded-pill">{{ $sale->items->sum('quantity') }}</span></td>
                                <td class="fw-bold text-danger small">&#8377;{{ number_format($sale->total_amount, 0) }}</td>
                                <td><span class="badge bg-{{ $sale->payment_method === 'cash' ? 'success' : 'info' }} rounded-pill">{{ strtoupper($sale->payment_method) }}</span></td>
                                <td class="text-muted small">{{ $sale->created_at->format('h:i A') }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="6" class="text-center text-muted py-5"><i class="bi bi-receipt fs-3 d-block mb-2 opacity-25"></i>No sales yet today</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endcan
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3">
                <span class="fw-semibold"><i class="bi bi-exclamation-triangle-fill text-warning me-2"></i>Stock Alerts
                @if(($outOfStockCount ?? 0) > 0)
                <span class="badge bg-danger ms-1">{{ $outOfStockCount }} OOS</span>
                @endif
                </span>
            </div>
            <div class="card-body p-2">
                @forelse($lowStockItems as $p)
                <div class="stock-row">
                    <div style="flex:1;min-width:0">
                        <div class="fw-semibold small text-truncate">{{ $p->name }}</div>
                        <div class="stock-bar-wrap mt-1">
                            @php $pct = $p->alert_quantity > 0 ? min(100, round($p->stock_quantity / $p->alert_quantity * 100)) : 100; @endphp
                            <div class="stock-bar" style="width:{{ $pct }}%;background:{{ $pct < 30 ? '#e74c3c' : '#f39c12' }}"></div>
                        </div>
                    </div>
                    <span class="badge {{ $p->stock_quantity == 0 ? 'bg-danger' : 'bg-warning text-dark' }}">{{ $p->stock_quantity }}</span>
                </div>
                @empty
                <div class="text-center text-muted py-4 small"><i class="bi bi-check-circle-fill text-success fs-4 d-block mb-1"></i>All items well-stocked</div>
                @endforelse
            </div>
        </div>
    </div>
</div>

@else
{{-- ══════════════════════════════════════════════════════════ --}}
{{-- ADMIN / MANAGER DASHBOARD --}}
{{-- ══════════════════════════════════════════════════════════ --}}

{{-- ── Top Bar ───────────────────────────────────────────────── --}}
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h6 class="mb-0 fw-bold">Good {{ now()->hour < 12 ? 'Morning' : (now()->hour < 17 ? 'Afternoon' : 'Evening') }}, {{ auth()->user()->name }} 👋</h6>
        <div class="text-muted" style="font-size:.8rem"><i class="bi bi-calendar3 me-1"></i>{{ now()->format('l, d F Y') }}</div>
    </div>
    <div class="d-flex gap-2 flex-wrap justify-content-end">
        <a href="{{ route('sales.create') }}" class="btn btn-danger qa-btn"><i class="bi bi-cart3 me-1"></i>New Sale</a>
        <a href="{{ route('stock.add') }}" class="btn btn-outline-success qa-btn"><i class="bi bi-plus-circle me-1"></i>Add Stock</a>
        <a href="{{ route('products.create') }}" class="btn btn-outline-primary qa-btn"><i class="bi bi-box me-1"></i>Add Product</a>
    </div>
</div>

{{-- ── TODAY'S KPI CARDS ─────────────────────────────────────── --}}
<div class="sec-hdr mb-3">Today's Performance</div>
<div class="row g-3 mb-4">

    @can('view sales')
    <div class="col-6 col-lg-2">
        <div class="kpi-card" style="background:linear-gradient(135deg,#e74c3c,#c0392b)">
            <i class="bi bi-graph-up kpi-icon"></i>
            <div class="kpi-val">&#8377;{{ number_format($todayRevenue, 0) }}</div>
            <div class="kpi-lbl">Total Sales</div>
            <div class="kpi-sub">{{ $todayOrders }} orders</div>
        </div>
    </div>

    <div class="col-6 col-lg-2">
        <div class="kpi-card" style="background:linear-gradient(135deg,#27ae60,#1e8449)">
            <i class="bi bi-cash-stack kpi-icon"></i>
            <div class="kpi-val">&#8377;{{ number_format($todayCashIn ?? 0, 0) }}</div>
            <div class="kpi-lbl">Cash Collected</div>
            <div class="kpi-sub">Cash payments</div>
        </div>
    </div>

    <div class="col-6 col-lg-2">
        <div class="kpi-card" style="background:linear-gradient(135deg,#3498db,#2471a3)">
            <i class="bi bi-phone kpi-icon"></i>
            <div class="kpi-val">&#8377;{{ number_format(($todayUpiIn ?? 0) + ($todayCardIn ?? 0), 0) }}</div>
            <div class="kpi-lbl">UPI / Card</div>
            <div class="kpi-sub">Digital payments</div>
        </div>
    </div>
    @endcan

    @module('expenses')
    @can('view expenses')
    <div class="col-6 col-lg-2">
        <div class="kpi-card" style="background:linear-gradient(135deg,{{ ($todayProfit ?? 0) >= 0 ? '#1abc9c,#148f77' : '#e74c3c,#c0392b' }})">
            <i class="bi bi-graph-up-arrow kpi-icon"></i>
            <div class="kpi-val">&#8377;{{ number_format(abs($todayProfit ?? 0), 0) }}</div>
            <div class="kpi-lbl">{{ ($todayProfit ?? 0) >= 0 ? "Today's Profit" : "Today's Loss" }}</div>
            <div class="kpi-sub">After expenses</div>
        </div>
    </div>
    @endcan
    @endmodule

    @module('purchases')
    @can('view purchases')
    <div class="col-6 col-lg-2">
        <div class="kpi-card" style="background:linear-gradient(135deg,#9b59b6,#7d3c98)">
            <i class="bi bi-truck kpi-icon"></i>
            <div class="kpi-val">&#8377;{{ number_format($todayPurchase ?? 0, 0) }}</div>
            <div class="kpi-lbl">Purchase</div>
            <div class="kpi-sub">GRN received today</div>
        </div>
    </div>
    @endcan
    @endmodule

    @module('sale_returns')
    @can('process sale returns')
    <div class="col-6 col-lg-2">
        <div class="kpi-card" style="background:linear-gradient(135deg,#f39c12,#d68910)">
            <i class="bi bi-arrow-return-left kpi-icon"></i>
            <div class="kpi-val">{{ $todayReturns ?? 0 }}</div>
            <div class="kpi-lbl">Returns</div>
            <div class="kpi-sub">Today's returns</div>
        </div>
    </div>
    @endcan
    @endmodule

</div>

{{-- ── MONTHLY SNAPSHOT ──────────────────────────────────────── --}}
<div class="sec-hdr mb-3">{{ now()->format('F') }} Snapshot</div>
<div class="row g-3 mb-4">

    @can('view sales')
    <div class="col-6 col-md-3 col-lg">
        <div class="snap-card">
            <div class="snap-icon text-danger"><i class="bi bi-bag-check-fill"></i></div>
            <div class="snap-lbl">Monthly Sales</div>
            <div class="snap-val text-danger">&#8377;{{ number_format($monthSales, 0) }}</div>
            <div class="text-muted mt-1" style="font-size:.7rem">{{ $monthOrders }} orders · Avg &#8377;{{ $monthOrders > 0 ? number_format($monthSales / $monthOrders, 0) : 0 }}</div>
        </div>
    </div>
    @endcan

    @module('purchases')
    @can('view purchases')
    <div class="col-6 col-md-3 col-lg">
        <div class="snap-card">
            <div class="snap-icon text-primary"><i class="bi bi-truck"></i></div>
            <div class="snap-lbl">Purchase</div>
            <div class="snap-val text-primary">&#8377;{{ number_format($monthPurchase, 0) }}</div>
            <div class="text-muted mt-1" style="font-size:.7rem">Total goods received</div>
        </div>
    </div>
    @endcan
    @endmodule

    @module('expenses')
    @can('view expenses')
    <div class="col-6 col-md-3 col-lg">
        <div class="snap-card">
            <div class="snap-icon text-warning"><i class="bi bi-wallet2"></i></div>
            <div class="snap-lbl">Expenses</div>
            <div class="snap-val text-warning">&#8377;{{ number_format($monthExpenses, 0) }}</div>
            <div class="text-muted mt-1" style="font-size:.7rem">Total this month</div>
        </div>
    </div>
    <div class="col-6 col-md-3 col-lg">
        <div class="snap-card">
            <div class="snap-icon {{ $monthProfit >= 0 ? 'text-success' : 'text-danger' }}"><i class="bi bi-graph-up-arrow"></i></div>
            <div class="snap-lbl">Net Profit</div>
            <div class="snap-val {{ $monthProfit >= 0 ? 'text-success' : 'text-danger' }}">
                {{ $monthProfit >= 0 ? '' : '-' }}&#8377;{{ number_format(abs($monthProfit), 0) }}
            </div>
            <div class="text-muted mt-1" style="font-size:.7rem">Sales - Purchase - Expenses</div>
        </div>
    </div>
    @endcan
    @endmodule

    @module('customers')
    @can('view customers')
    <div class="col-6 col-md-3 col-lg">
        <div class="snap-card">
            <div class="snap-icon text-info"><i class="bi bi-people-fill"></i></div>
            <div class="snap-lbl">Customers</div>
            <div class="snap-val text-info">{{ $totalCustomers ?? 0 }}</div>
            <div class="text-muted mt-1" style="font-size:.7rem">+{{ $todayNewCustomers ?? 0 }} today</div>
        </div>
    </div>
    @endcan
    @endmodule

</div>

{{-- ── MAIN CONTENT ROW ──────────────────────────────────────── --}}
<div class="row g-3 mb-4">

    {{-- Recent Sales --}}
    <div class="col-lg-8">
        @can('view sales')
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center py-3">
                <span class="fw-semibold"><i class="bi bi-clock-history me-2 text-danger"></i>Recent Sales</span>
                <a href="{{ route('sales.index') }}" class="small text-muted text-decoration-none">View all →</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 sales-table">
                        <thead class="table-light">
                            <tr>
                                <th>Invoice</th>
                                <th>Customer</th>
                                <th>Cashier</th>
                                <th>Amount</th>
                                <th>Pay</th>
                                <th>Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentSales as $sale)
                            <tr>
                                <td><a href="{{ route('sales.show', $sale) }}" class="invoice-link">{{ $sale->invoice_number }}</a></td>
                                <td class="small">{{ $sale->customer?->name ?? 'Walk-in' }}</td>
                                <td class="small text-muted">{{ $sale->user?->name ?? '—' }}</td>
                                <td class="fw-bold text-danger small">&#8377;{{ number_format($sale->total_amount, 0) }}</td>
                                <td>
                                    @php
                                        $pm = $sale->payment_method;
                                        $pmColor = $pm === 'cash' ? 'success' : ($pm === 'upi' ? 'info' : 'primary');
                                    @endphp
                                    <span class="badge bg-{{ $pmColor }} rounded-pill">{{ strtoupper($pm) }}</span>
                                </td>
                                <td class="text-muted small">{{ $sale->created_at->format('d/m h:i A') }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="6" class="text-center text-muted py-5">
                                <i class="bi bi-receipt fs-2 d-block mb-2 opacity-25"></i>No sales yet
                            </td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endcan
    </div>

    {{-- Right column --}}
    <div class="col-lg-4">

        @can('view sales')
        {{-- Payment Breakdown --}}
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white border-bottom py-3">
                <span class="fw-semibold"><i class="bi bi-pie-chart me-2 text-primary"></i>Today's Collections</span>
            </div>
            <div class="card-body py-2 px-1">
                @php
                    $payTotal = $todayRevenue ?: 1;
                    $cashPct  = round(($todayCashIn  ?? 0) / $payTotal * 100);
                    $upiPct   = round(($todayUpiIn   ?? 0) / $payTotal * 100);
                    $cardPct  = 100 - $cashPct - $upiPct;
                @endphp
                <div class="pay-row">
                    <div class="d-flex align-items-center gap-2">
                        <div class="pay-dot" style="background:#27ae60"></div>
                        <span class="small fw-semibold">Cash</span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="small text-muted">{{ $cashPct }}%</span>
                        <span class="small fw-bold text-success">&#8377;{{ number_format($todayCashIn ?? 0, 0) }}</span>
                    </div>
                </div>
                <div class="pay-row">
                    <div class="d-flex align-items-center gap-2">
                        <div class="pay-dot" style="background:#3498db"></div>
                        <span class="small fw-semibold">UPI</span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="small text-muted">{{ $upiPct }}%</span>
                        <span class="small fw-bold text-primary">&#8377;{{ number_format($todayUpiIn ?? 0, 0) }}</span>
                    </div>
                </div>
                <div class="pay-row">
                    <div class="d-flex align-items-center gap-2">
                        <div class="pay-dot" style="background:#9b59b6"></div>
                        <span class="small fw-semibold">Card / Other</span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="small text-muted">{{ max(0,$cardPct) }}%</span>
                        <span class="small fw-bold" style="color:#9b59b6">&#8377;{{ number_format($todayCardIn ?? 0, 0) }}</span>
                    </div>
                </div>
                <div class="mx-3 mt-2 mb-1" style="height:6px;background:#f0f2f8;border-radius:6px;overflow:hidden;display:flex">
                    <div style="width:{{ $cashPct }}%;background:#27ae60"></div>
                    <div style="width:{{ $upiPct }}%;background:#3498db"></div>
                    <div style="width:{{ max(0,$cardPct) }}%;background:#9b59b6"></div>
                </div>
            </div>
        </div>
        @endcan

        {{-- Stock Alerts --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center py-3">
                <span class="fw-semibold"><i class="bi bi-exclamation-triangle-fill text-warning me-2"></i>Stock Alerts</span>
                @if(($outOfStockCount ?? 0) > 0)
                <span class="badge bg-danger rounded-pill">{{ $outOfStockCount }} OOS</span>
                @endif
            </div>
            <div class="card-body p-2" style="max-height:240px;overflow-y:auto">
                @forelse($lowStockItems as $p)
                @php $pct = $p->alert_quantity > 0 ? min(100, round($p->stock_quantity / $p->alert_quantity * 100)) : 100; @endphp
                <div class="stock-row">
                    <div style="flex:1;min-width:0">
                        <div class="small fw-semibold text-truncate">{{ $p->name }}</div>
                        <div class="stock-bar-wrap mt-1">
                            <div class="stock-bar" style="width:{{ $pct }}%;background:{{ $pct < 30 ? '#e74c3c' : '#f39c12' }}"></div>
                        </div>
                    </div>
                    <span class="badge {{ $p->stock_quantity == 0 ? 'bg-danger' : 'bg-warning text-dark' }} ms-2">{{ $p->stock_quantity }}</span>
                </div>
                @empty
                <div class="text-center text-muted py-4 small">
                    <i class="bi bi-check-circle-fill text-success fs-3 d-block mb-1"></i>All items well-stocked
                </div>
                @endforelse
            </div>
            <div class="card-footer bg-white py-2 px-3">
                <a href="{{ route('stock.index') }}" class="small text-muted text-decoration-none">View stock history →</a>
            </div>
        </div>

    </div>
</div>

{{-- ── ANALYTICS ROW ─────────────────────────────────────────── --}}
@can('view reports')
<div class="row g-3 mb-4">

    {{-- Top Products --}}
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-bottom py-3">
                <span class="fw-semibold"><i class="bi bi-trophy-fill text-warning me-2"></i>Top Products <small class="text-muted fw-normal">(30 days)</small></span>
            </div>
            <div class="card-body p-2">
                @forelse($topProducts as $i => $p)
                <div class="d-flex align-items-center gap-2 py-2 {{ !$loop->last ? 'border-bottom' : '' }}">
                    <span class="rbadge r{{ $i+1 }}">{{ $i+1 }}</span>
                    <div style="flex:1;min-width:0">
                        <div class="small fw-semibold text-truncate">{{ $p->product_name }}</div>
                        <div style="font-size:.7rem;color:#9ca3af">{{ $p->qty }} pcs sold</div>
                    </div>
                    <div class="small fw-bold text-danger">&#8377;{{ number_format($p->revenue, 0) }}</div>
                </div>
                @empty
                <div class="text-center text-muted py-4 small">No sales data yet</div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Cashier Performance --}}
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-bottom py-3">
                <span class="fw-semibold"><i class="bi bi-person-badge me-2 text-primary"></i>Cashier Performance <small class="text-muted fw-normal">(this month)</small></span>
            </div>
            <div class="card-body p-2">
                @php $cavColors = ['#e74c3c','#3498db','#27ae60','#9b59b6','#f39c12']; @endphp
                @forelse($topCashiers as $i => $c)
                <div class="d-flex align-items-center gap-2 py-2 {{ !$loop->last ? 'border-bottom' : '' }}">
                    <div class="cav" style="background:{{ $cavColors[$i] ?? '#6c757d' }}">{{ strtoupper(substr($c->name,0,1)) }}</div>
                    <div style="flex:1;min-width:0">
                        <div class="small fw-semibold text-truncate">{{ $c->name }}</div>
                        <div style="font-size:.7rem;color:#9ca3af">{{ $c->orders }} orders</div>
                    </div>
                    <div class="small fw-bold text-danger">&#8377;{{ number_format($c->revenue, 0) }}</div>
                </div>
                @empty
                <div class="text-center text-muted py-4 small">No data this month</div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Inventory Overview --}}
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-bottom py-3">
                <span class="fw-semibold"><i class="bi bi-boxes me-2 text-success"></i>Inventory Overview</span>
            </div>
            <div class="card-body">
                <div class="row g-2 text-center mb-2">
                    <div class="col-6">
                        <div class="p-2 rounded" style="background:#fff5f5">
                            <div class="fw-bold text-danger" style="font-size:1.3rem">{{ $totalProducts ?? 0 }}</div>
                            <div style="font-size:.7rem;color:#9ca3af">Products</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-2 rounded" style="background:#f0f9ff">
                            <div class="fw-bold text-primary" style="font-size:1.3rem">{{ $totalBrands ?? 0 }}</div>
                            <div style="font-size:.7rem;color:#9ca3af">Brands</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-2 rounded" style="background:#f0fff4">
                            <div class="fw-bold text-success" style="font-size:1.3rem">{{ $totalStockItems ?? 0 }}</div>
                            <div style="font-size:.7rem;color:#9ca3af">Total Stock Units</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-2 rounded" style="background:#fff8f0">
                            <div class="fw-bold text-warning" style="font-size:1.3rem">{{ $outOfStockCount ?? 0 }}</div>
                            <div style="font-size:.7rem;color:#9ca3af">Out of Stock</div>
                        </div>
                    </div>
                </div>
                <div class="p-2 rounded text-center" style="background:linear-gradient(135deg,#fff5f5,#fff0f0)">
                    <div style="font-size:.7rem;color:#9ca3af;text-transform:uppercase;letter-spacing:.5px">Total Inventory Value</div>
                    <div class="fw-bold text-danger" style="font-size:1.2rem">&#8377;{{ number_format($totalStockValue ?? 0, 0) }}</div>
                </div>
            </div>
        </div>
    </div>

</div>
@endcan

{{-- ── BOTTOM ROW: Brands + Customers + Returns ─────────────── --}}
<div class="row g-3 mb-4">

    @can('view reports')
    {{-- Top Brands --}}
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-bottom py-3">
                <span class="fw-semibold"><i class="bi bi-stars me-2 text-warning"></i>Top Brands <small class="text-muted fw-normal">(30 days)</small></span>
            </div>
            <div class="card-body p-2">
                @forelse($topBrands ?? [] as $i => $b)
                <div class="d-flex align-items-center gap-2 py-2 {{ !$loop->last ? 'border-bottom' : '' }}">
                    <span class="rbadge r{{ $i+1 }}">{{ $i+1 }}</span>
                    <span class="small fw-semibold" style="flex:1">{{ $b->name }}</span>
                    <span class="small fw-bold text-danger">&#8377;{{ number_format($b->revenue, 0) }}</span>
                </div>
                @empty
                <div class="text-center text-muted py-4 small">No brand data yet</div>
                @endforelse
            </div>
        </div>
    </div>
    @endcan

    {{-- Top Customers --}}
    @module('customers')
    @can('view customers')
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-bottom py-3">
                <span class="fw-semibold"><i class="bi bi-people-fill me-2 text-info"></i>Top Customers</span>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-around text-center mb-3 pb-2 border-bottom">
                    <div>
                        <div class="fw-bold text-primary" style="font-size:1.4rem">{{ $totalCustomers ?? 0 }}</div>
                        <div style="font-size:.72rem;color:#9ca3af">Total</div>
                    </div>
                    <div>
                        <div class="fw-bold text-success" style="font-size:1.4rem">{{ $todayNewCustomers ?? 0 }}</div>
                        <div style="font-size:.72rem;color:#9ca3af">New Today</div>
                    </div>
                </div>
                @foreach(($topCustomers ?? collect())->take(5) as $c)
                <div class="d-flex justify-content-between align-items-center py-1 small {{ !$loop->last ? 'border-bottom' : '' }}">
                    <span class="text-truncate fw-semibold" style="max-width:130px">{{ $c->name }}</span>
                    <div class="text-end">
                        <div class="text-danger fw-bold">&#8377;{{ number_format($c->total, 0) }}</div>
                        <div style="font-size:.68rem;color:#9ca3af">{{ $c->orders }} orders</div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endcan
    @endmodule

    {{-- Monthly chart or Recent Returns --}}
    @php
        $showPurchasesChart = \App\Helpers\Module::isActive('purchases') && isset($monthlyChart) && auth()->user()->can('view purchases') && auth()->user()->can('view reports');
        $showRecentReturns  = \App\Helpers\Module::isActive('sale_returns') && auth()->user()->can('process sale returns');
    @endphp

    @if($showRecentReturns)
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-bottom py-3">
                <span class="fw-semibold"><i class="bi bi-arrow-return-left me-2 text-warning"></i>Recent Returns</span>
            </div>
            <div class="card-body p-2">
                @forelse($recentReturns ?? [] as $ret)
                <div class="d-flex gap-2 py-2 {{ !$loop->last ? 'border-bottom' : '' }}">
                    <div class="adot" style="background:#e74c3c;margin-top:5px"></div>
                    <div>
                        <div class="small fw-semibold">{{ $ret->return_number }}</div>
                        <div style="font-size:.72rem;color:#9ca3af">
                            {{ $ret->user?->name ?? '—' }} · &#8377;{{ number_format($ret->return_amount, 0) }}
                        </div>
                        <div style="font-size:.7rem;color:#bbb">{{ $ret->created_at->diffForHumans() }}</div>
                    </div>
                </div>
                @empty
                <div class="text-center text-muted py-4 small">No recent returns</div>
                @endforelse
            </div>
        </div>
    </div>
    @endif

</div>

{{-- Monthly Comparison Chart --}}
@if($showPurchasesChart)
<div class="row g-3 mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3">
                <span class="fw-semibold"><i class="bi bi-bar-chart-steps me-2 text-success"></i>Monthly Sales vs Purchase <small class="text-muted fw-normal">(6 months)</small></span>
            </div>
            <div class="card-body">
                <canvas id="monthlyChart" height="60"></canvas>
            </div>
        </div>
    </div>
</div>
@endif

@endif {{-- end admin/manager --}}
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
@if(isset($monthlyChart) && \App\Helpers\Module::isActive('purchases'))
new Chart(document.getElementById('monthlyChart'), {
    type: 'bar',
    data: {
        labels: {!! json_encode($monthlyChart['labels']) !!},
        datasets: [
            {
                label: 'Sales',
                data: {!! json_encode($monthlyChart['sales']) !!},
                backgroundColor: 'rgba(231,76,60,.8)',
                borderRadius: 6,
            },
            {
                label: 'Purchase',
                data: {!! json_encode($monthlyChart['purchases']) !!},
                backgroundColor: 'rgba(52,152,219,.8)',
                borderRadius: 6,
            }
        ]
    },
    options: {
        responsive: true,
        plugins: { legend: { labels: { font: { size: 11 } } } },
        scales: {
            y: {
                beginAtZero: true,
                grid: { color: '#f0f2f8' },
                ticks: { callback: v => '₹' + (v >= 1000 ? (v/1000).toFixed(0)+'k' : v), font: { size: 11 } }
            },
            x: { grid: { display: false }, ticks: { font: { size: 11 } } }
        }
    }
});
@endif
</script>
@endpush

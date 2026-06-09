@extends('layouts.app')
@section('title', 'Profit & Loss Report')
@section('page-title', 'Profit & Loss Report')

@section('content')
{{-- Date Range Filter --}}
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('reports.profit-loss') }}" class="row g-2 align-items-end">
            <div class="col-md-2">
                <label class="form-label fw-semibold">From Date</label>
                <input type="date" name="from_date" class="form-control" value="{{ request('from_date', now()->startOfMonth()->format('Y-m-d')) }}">
            </div>
            <div class="col-md-2">
                <label class="form-label fw-semibold">To Date</label>
                <input type="date" name="to_date" class="form-control" value="{{ request('to_date', now()->format('Y-m-d')) }}">
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
                <a href="{{ route('reports.profit-loss', ['from_date'=>request('from_date'),'to_date'=>request('to_date')]) }}"
                    class="btn btn-outline-secondary ms-1">
                    <i class="bi bi-x"></i> Clear
                </a>
                @endif
            </div>
        </form>
    </div>
</div>

@if(isset($report))
@if($payment ?? false)
<div class="alert alert-info py-2 mb-3 d-flex align-items-center gap-2">
    <i class="bi bi-funnel-fill"></i>
    Filtered by: <strong>{{ strtoupper($payment) }}</strong> payments only
    <span class="text-muted small ms-2">(Expenses not affected by payment filter)</span>
</div>
@endif
{{-- Summary Cards --}}
<div class="row g-3 mb-4">
    <div class="col-md-2-4 col" style="flex:0 0 16.66%;max-width:16.66%">
        <div class="stat-card" style="background:linear-gradient(135deg,#3498db,#2980b9)">
            <div class="small mb-1" style="opacity:.85">Revenue</div>
            <div class="stat-value fs-5">&#8377;{{ number_format($report['revenue'], 0) }}</div>
        </div>
    </div>
    <div class="col" style="flex:0 0 16.66%;max-width:16.66%">
        <div class="stat-card" style="background:linear-gradient(135deg,#f39c12,#e67e22)">
            <div class="small mb-1" style="opacity:.85">GST Collected</div>
            <div class="stat-value fs-5">&#8377;{{ number_format($report['gst'], 0) }}</div>
        </div>
    </div>
    @can('view purchase price')
    <div class="col" style="flex:0 0 16.66%;max-width:16.66%">
        <div class="stat-card" style="background:linear-gradient(135deg,#e67e22,#d35400)">
            <div class="small mb-1" style="opacity:.85">COGS</div>
            <div class="stat-value fs-5">&#8377;{{ number_format($report['cogs'], 0) }}</div>
        </div>
    </div>
    <div class="col" style="flex:0 0 16.66%;max-width:16.66%">
        <div class="stat-card" style="background:linear-gradient(135deg,#27ae60,#229954)">
            <div class="small mb-1" style="opacity:.85">Gross Profit</div>
            <div class="stat-value fs-5">&#8377;{{ number_format($report['gross_profit'], 0) }}</div>
        </div>
    </div>
    @endcan
    <div class="col" style="flex:0 0 16.66%;max-width:16.66%">
        <div class="stat-card" style="background:linear-gradient(135deg,#e74c3c,#c0392b)">
            <div class="small mb-1" style="opacity:.85">Expenses</div>
            <div class="stat-value fs-5">&#8377;{{ number_format($report['expenses'], 0) }}</div>
        </div>
    </div>
    <div class="col" style="flex:0 0 16.66%;max-width:16.66%">
        @php $netPositive = $report['net_profit'] >= 0; @endphp
        <div class="stat-card" style="background:linear-gradient(135deg,{{ $netPositive ? '#27ae60,#229954' : '#e74c3c,#c0392b' }})">
            <div class="small mb-1" style="opacity:.85">Net Profit</div>
            <div class="stat-value fs-5">&#8377;{{ number_format(abs($report['net_profit']), 0) }}
                @if(!$netPositive)<small style="font-size:.7em">(Loss)</small>@endif
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    {{-- P&L Summary Table --}}
    <div class="col-md-5">
        <div class="card">
            <div class="card-header py-3"><i class="bi bi-calculator me-2"></i>P&L Summary</div>
            <div class="card-body p-0">
                <table class="table mb-0">
                    <tbody>
                        <tr>
                            <td class="text-muted">Revenue (Sales)</td>
                            <td class="text-end fw-semibold">&#8377;{{ number_format($report['revenue'], 2) }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted ps-4">GST Collected</td>
                            <td class="text-end text-warning">&#8377;{{ number_format($report['gst'], 2) }}</td>
                        </tr>
                        @can('view purchase price')
                        <tr>
                            <td class="text-muted ps-4">Less: Cost of Goods Sold</td>
                            <td class="text-end text-danger">-&#8377;{{ number_format($report['cogs'], 2) }}</td>
                        </tr>
                        <tr class="table-light">
                            <td class="fw-semibold">Gross Profit</td>
                            <td class="text-end fw-semibold text-success">&#8377;{{ number_format($report['gross_profit'], 2) }}</td>
                        </tr>
                        @endcan
                        <tr>
                            <td class="text-muted ps-4">Less: Total Expenses</td>
                            <td class="text-end text-danger">-&#8377;{{ number_format($report['expenses'], 2) }}</td>
                        </tr>
                        <tr class="table-active">
                            <td class="fw-bold fs-6">Net Profit / Loss</td>
                            <td class="text-end fw-bold fs-6 {{ $netPositive ? 'text-success' : 'text-danger' }}">
                                {{ $netPositive ? '' : '-' }}&#8377;{{ number_format(abs($report['net_profit']), 2) }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Expense Breakdown --}}
    <div class="col-md-7">
        <div class="card">
            <div class="card-header py-3"><i class="bi bi-pie-chart me-2"></i>Expense Breakdown by Category</div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Category</th>
                            <th class="text-end">Amount</th>
                            <th class="text-end">% of Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($expenseBreakdown ?? [] as $item)
                        <tr>
                            <td>{{ $item->category_name ?? 'Uncategorized' }}</td>
                            <td class="text-end">&#8377;{{ number_format($item->total, 2) }}</td>
                            <td class="text-end text-muted">
                                @if($report['expenses'] > 0)
                                {{ number_format($item->total / $report['expenses'] * 100, 1) }}%
                                @else —
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="text-center text-muted py-3">No expenses in this period</td></tr>
                        @endforelse
                    </tbody>
                    @if(isset($expenseBreakdown) && $expenseBreakdown->count())
                    <tfoot class="table-light">
                        <tr>
                            <td class="fw-bold">Total</td>
                            <td class="text-end fw-bold">&#8377;{{ number_format($report['expenses'], 2) }}</td>
                            <td class="text-end fw-bold">100%</td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>
</div>
@else
<div class="card">
    <div class="card-body text-center py-5 text-muted">
        <i class="bi bi-bar-chart fs-1 d-block mb-3 opacity-50"></i>
        Select a date range and click <strong>Generate Report</strong> to view the Profit & Loss statement.
    </div>
</div>
@endif
@endsection

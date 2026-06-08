@extends('layouts.app')
@section('title', 'Cashier-Wise Report')
@section('page-title', 'Cashier-Wise Sales Report')

@section('content')
{{-- Date Picker --}}
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('reports.cashier-wise') }}" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label fw-semibold">Date</label>
                <input type="date" name="date" class="form-control" value="{{ request('date', now()->format('Y-m-d')) }}">
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
                    <i class="bi bi-person-check me-1"></i>Generate Report
                </button>
                @if(request('payment_method'))
                <a href="{{ route('reports.cashier-wise', ['date' => request('date')]) }}" class="btn btn-outline-secondary ms-1">
                    <i class="bi bi-x"></i> Clear
                </a>
                @endif
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header py-3">
        <i class="bi bi-people me-2"></i>
        Cashier Performance —
        <span class="text-muted fw-normal">{{ request('date') ? \Carbon\Carbon::parse(request('date'))->format('d M Y') : now()->format('d M Y') }}</span>
    </div>
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Cashier</th>
                    <th class="text-center">Bills</th>
                    <th class="text-end">GST Collected</th>
                    <th class="text-end">Revenue</th>
                    <th class="text-end">Discount Given</th>
                    <th class="text-end">Avg Bill Value</th>
                </tr>
            </thead>
            <tbody>
                @forelse($cashierData ?? [] as $row)
                <tr>
                    <td>
                        <div class="fw-semibold">{{ $row->cashier_name }}</div>
                        <div class="text-muted small">{{ $row->role ?? '' }}</div>
                    </td>
                    <td class="text-center">
                        <span class="badge bg-primary">{{ $row->bills_count }}</span>
                    </td>
                    <td class="text-end text-warning">&#8377;{{ number_format($row->total_gst ?? 0, 2) }}</td>
                    <td class="text-end fw-semibold">&#8377;{{ number_format($row->revenue, 2) }}</td>
                    <td class="text-end text-danger">&#8377;{{ number_format($row->total_discount ?? 0, 2) }}</td>
                    <td class="text-end">
                        &#8377;{{ $row->bills_count > 0 ? number_format($row->revenue / $row->bills_count, 2) : '0.00' }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center text-muted py-4">
                        <i class="bi bi-people fs-3 d-block mb-2 opacity-50"></i>
                        No sales data for the selected date
                    </td>
                </tr>
                @endforelse
            </tbody>
            @if(isset($cashierData) && $cashierData->count() > 0)
            <tfoot class="table-light">
                <tr>
                    <td class="fw-bold">Total</td>
                    <td class="text-center fw-bold">{{ $cashierData->sum('bills_count') }}</td>
                    <td class="text-end fw-bold text-warning">&#8377;{{ number_format($cashierData->sum('total_gst'), 2) }}</td>
                    <td class="text-end fw-bold">&#8377;{{ number_format($cashierData->sum('revenue'), 2) }}</td>
                    <td class="text-end fw-bold text-danger">&#8377;{{ number_format($cashierData->sum('total_discount'), 2) }}</td>
                    <td class="text-end fw-bold">
                        @php
                            $totalBills = $cashierData->sum('bills_count');
                            $totalRevenue = $cashierData->sum('revenue');
                        @endphp
                        &#8377;{{ $totalBills > 0 ? number_format($totalRevenue / $totalBills, 2) : '0.00' }}
                    </td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
</div>
@endsection

@extends('layouts.app')
@section('title', 'Stock Movement Report')
@section('page-title', 'Stock Movement Report')

@section('content')
{{-- Date Range Filter --}}
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('reports.stock-movement') }}" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label fw-semibold">From Date</label>
                <input type="date" name="from_date" class="form-control"
                    value="{{ request('from_date', now()->startOfMonth()->format('Y-m-d')) }}">
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">To Date</label>
                <input type="date" name="to_date" class="form-control"
                    value="{{ request('to_date', now()->format('Y-m-d')) }}">
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">Product / SKU (optional)</label>
                <input type="text" name="search" class="form-control form-control"
                    value="{{ request('search') }}" placeholder="Filter by name or SKU...">
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-danger">
                    <i class="bi bi-arrow-left-right me-1"></i>Generate Report
                </button>
                @if(request()->hasAny(['from_date','to_date','search']))
                <a href="{{ route('reports.stock-movement') }}" class="btn btn-outline-secondary ms-1">
                    <i class="bi bi-x"></i>
                </a>
                @endif
            </div>
        </form>
    </div>
</div>

@if(isset($stockData))
<div class="card">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <span>
            <i class="bi bi-box-seam me-2"></i>Stock Movement —
            <span class="text-muted fw-normal">
                {{ \Carbon\Carbon::parse(request('from_date', now()->startOfMonth()))->format('d M Y') }}
                to
                {{ \Carbon\Carbon::parse(request('to_date', now()))->format('d M Y') }}
            </span>
        </span>
        <small class="text-muted">{{ count($stockData) }} products</small>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Product</th>
                        <th>SKU</th>
                        <th class="text-center">Opening Stock</th>
                        <th class="text-center text-success">Stock IN</th>
                        <th class="text-center text-danger">Stock OUT</th>
                        <th class="text-center">Closing Stock</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($stockData as $row)
                    @php
                        $closing = $row->opening_stock + $row->stock_in - $row->stock_out;
                    @endphp
                    <tr>
                        <td>
                            <div class="fw-semibold">{{ $row->product_name }}</div>
                            @if(isset($row->category_name))
                            <div class="text-muted small">{{ $row->category_name }}</div>
                            @endif
                        </td>
                        <td class="text-muted small">{{ $row->sku }}</td>
                        <td class="text-center">{{ number_format($row->opening_stock) }}</td>
                        <td class="text-center fw-semibold text-success">
                            {{ $row->stock_in > 0 ? '+' . number_format($row->stock_in) : '—' }}
                        </td>
                        <td class="text-center fw-semibold text-danger">
                            {{ $row->stock_out > 0 ? '-' . number_format($row->stock_out) : '—' }}
                        </td>
                        <td class="text-center fw-bold">
                            <span class="badge bg-{{ $closing <= 0 ? 'danger' : ($closing <= 5 ? 'warning' : 'success') }} fs-6">
                                {{ number_format($closing) }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">
                            <i class="bi bi-box-seam fs-3 d-block mb-2 opacity-50"></i>
                            No stock movement data found for this period
                        </td>
                    </tr>
                    @endforelse
                </tbody>
                @if(count($stockData) > 0)
                <tfoot class="table-light">
                    <tr>
                        <td colspan="2" class="fw-bold">Total</td>
                        <td class="text-center fw-bold">{{ number_format(collect($stockData)->sum('opening_stock')) }}</td>
                        <td class="text-center fw-bold text-success">+{{ number_format(collect($stockData)->sum('stock_in')) }}</td>
                        <td class="text-center fw-bold text-danger">-{{ number_format(collect($stockData)->sum('stock_out')) }}</td>
                        <td class="text-center fw-bold">
                            {{ number_format(collect($stockData)->sum(fn($r) => $r->opening_stock + $r->stock_in - $r->stock_out)) }}
                        </td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>
</div>
@else
<div class="card">
    <div class="card-body text-center py-5 text-muted">
        <i class="bi bi-arrow-left-right fs-1 d-block mb-3 opacity-50"></i>
        Select a date range and click <strong>Generate Report</strong> to view stock movements.
    </div>
</div>
@endif
@endsection

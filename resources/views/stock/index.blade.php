@extends('layouts.app')
@section('title', 'Stock History')
@section('page-title', 'Stock History')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center py-3">
        <span><i class="bi bi-clock-history me-2"></i>Stock History</span>
        <a href="{{ route('stock.add') }}" class="btn btn-sm btn-success">
            <i class="bi bi-plus"></i> Add Stock
        </a>
    </div>
    <div class="card-body border-bottom">
        <form method="GET" class="row g-2">
            <div class="col-md-3">
                <select name="product_id" class="form-select form-select-sm">
                    <option value="">All Products</option>
                    @foreach($products as $p)
                    <option value="{{ $p->id }}" {{ request('product_id') == $p->id ? 'selected' : '' }}>
                        {{ $p->name }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="type" class="form-select form-select-sm">
                    <option value="">All Types</option>
                    <option value="in"         {{ request('type') === 'in'         ? 'selected' : '' }}>Stock In</option>
                    <option value="out"        {{ request('type') === 'out'        ? 'selected' : '' }}>Stock Out</option>
                    <option value="adjustment" {{ request('type') === 'adjustment' ? 'selected' : '' }}>Adjustment</option>
                </select>
            </div>
            <div class="col-md-3">
                <input type="date" name="date" class="form-control form-control-sm" value="{{ request('date') }}">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-sm btn-outline-primary">Filter</button>
                <a href="{{ route('stock.index') }}" class="btn btn-sm btn-outline-secondary ms-1">Reset</a>
            </div>
        </form>
    </div>
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Date / Time</th>
                    <th>Product</th>
                    <th>Type</th>
                    <th>Qty</th>
                    <th>Before</th>
                    <th>After</th>
                    <th>Reference</th>
                    <th>By</th>
                </tr>
            </thead>
            <tbody>
                @forelse($movements as $m)
                <tr>
                    <td class="small">{{ $m->created_at->format('d/m/Y H:i') }}</td>
                    <td>
                        <a href="{{ route('products.show', $m->product) }}" class="text-decoration-none">
                            {{ $m->product->name }}
                        </a>
                        <div class="text-muted" style="font-size:.75rem">{{ $m->product->barcode }}</div>
                    </td>
                    <td>
                        <span class="badge bg-{{ $m->type === 'in' ? 'success' : ($m->type === 'out' ? 'danger' : 'warning') }}">
                            {{ $m->type === 'in' ? 'IN' : ($m->type === 'out' ? 'OUT' : 'ADJUSTMENT') }}
                        </span>
                    </td>
                    <td class="fw-bold {{ $m->stock_after >= $m->stock_before ? 'text-success' : 'text-danger' }}">
                        {{ $m->stock_after >= $m->stock_before ? '+' : '' }}{{ $m->stock_after - $m->stock_before }}
                    </td>
                    <td class="text-muted">{{ $m->stock_before }}</td>
                    <td class="fw-bold">{{ $m->stock_after }}</td>
                    <td class="small text-muted">{{ $m->reference ?? $m->notes }}</td>
                    <td class="small">{{ $m->user->name }}</td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center text-muted py-5">No records found</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($movements->hasPages())
    <div class="card-footer">{{ $movements->links() }}</div>
    @endif
</div>
@endsection

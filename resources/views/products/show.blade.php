@extends('layouts.app')
@section('title', $product->name)
@section('page-title', $product->name)

@section('content')
<div class="row g-3">
    {{-- Left column: barcode + info --}}
    <div class="col-md-4">

        {{-- Barcode Card --}}
        <div class="card border-2" style="border-color:#e74c3c!important">
            <div class="card-header py-2 text-white text-center" style="background:#e74c3c">
                <i class="bi bi-upc me-2"></i><strong>Product Barcode</strong>
            </div>
            <div class="card-body text-center">
                @if($product->barcode)
                {{-- Large barcode image --}}
                <img src="{{ route('barcode.image', $product->barcode) }}"
                    alt="{{ $product->barcode }}"
                    class="img-fluid mb-2"
                    style="max-height:70px; image-rendering: pixelated;">
                <div class="font-monospace fw-bold" style="font-size:1rem;letter-spacing:2px">
                    {{ $product->barcode }}
                </div>
                <div class="text-muted small mt-1">Scan this barcode to add stock or sell</div>

                {{-- Barcode label mini preview --}}
                <div class="mt-3 p-2 border rounded" style="background:#fafafa;display:inline-block;font-family:Arial;font-size:8px;min-width:140px;">
                    <div style="font-weight:bold;text-transform:uppercase;font-size:7px;letter-spacing:1px;color:#555">Mayank Footware</div>
                    <div style="font-weight:bold;font-size:8px;margin:2px 0">{{ Str::limit($product->name, 22) }}</div>
                    @if($product->size || $product->color)
                    <div style="font-size:7px;color:#666">{{ $product->size ? 'Sz:'.$product->size : '' }}{{ $product->size && $product->color ? ' | ' : '' }}{{ $product->color ?? '' }}</div>
                    @endif
                    <div style="font-size:12px;font-weight:bold;color:#e74c3c;margin:2px 0">&#8377;{{ number_format($product->selling_price, 0) }}</div>
                    <img src="{{ route('barcode.image', $product->barcode) }}" style="height:22px;max-width:130px">
                    <div style="font-size:6.5px;font-family:monospace">{{ $product->barcode }}</div>
                </div>
                <div class="text-muted" style="font-size:.7rem;margin-top:4px">↑ Label preview (print & stick on product)</div>
                @else
                <div class="text-muted py-3">
                    <i class="bi bi-upc fs-2 d-block mb-2"></i>No barcode assigned
                </div>
                @endif
            </div>
            <div class="card-footer p-2">
                <a href="{{ route('products.barcode', $product) }}" class="btn btn-danger w-100">
                    <i class="bi bi-printer-fill me-2"></i>Print Barcode Labels
                </a>
            </div>
        </div>

        {{-- Product Info --}}
        <div class="card mt-3">
            <div class="card-header py-2"><i class="bi bi-info-circle me-2"></i>Product Info</div>
            <div class="card-body p-0">
                <table class="table table-sm table-borderless mb-0">
                    <tr class="border-bottom"><td class="text-muted ps-3">Name</td><td class="fw-semibold pe-3">{{ $product->name }}</td></tr>
                    <tr class="border-bottom"><td class="text-muted ps-3">Category</td><td class="pe-3"><span class="badge bg-secondary">{{ $product->category->name }}</span></td></tr>
                    <tr class="border-bottom"><td class="text-muted ps-3">SKU</td><td class="font-monospace small pe-3">{{ $product->sku }}</td></tr>
                    @if($product->brand)<tr class="border-bottom"><td class="text-muted ps-3">Brand</td><td class="pe-3">{{ $product->brand }}</td></tr>@endif
                    <tr class="border-bottom"><td class="text-muted ps-3">Size</td><td class="pe-3">{{ $product->size ?? '—' }}</td></tr>
                    <tr class="border-bottom"><td class="text-muted ps-3">Color</td><td class="pe-3">{{ $product->color ?? '—' }}</td></tr>
                    @can('view purchase price')
                    <tr class="border-bottom"><td class="text-muted ps-3">Cost</td><td class="pe-3">&#8377;{{ number_format($product->purchase_price, 0) }}</td></tr>
                    @endcan
                    <tr class="border-bottom"><td class="text-muted ps-3">Selling Price</td><td class="fw-bold text-danger pe-3">&#8377;{{ number_format($product->selling_price, 0) }}</td></tr>
                    @if($product->mrp)<tr class="border-bottom"><td class="text-muted ps-3">MRP</td><td class="pe-3">&#8377;{{ number_format($product->mrp, 0) }}</td></tr>@endif
                    <tr>
                        <td class="text-muted ps-3">Stock</td>
                        <td class="pe-3">
                            @if($product->isLowStock())
                                <span class="badge bg-danger fs-6">{{ $product->stock_quantity }} ⚠️ Low</span>
                            @else
                                <span class="badge bg-success fs-6">{{ $product->stock_quantity }} units</span>
                            @endif
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="d-grid gap-2 mt-3">
            <a href="{{ route('products.edit', $product) }}" class="btn btn-outline-warning">
                <i class="bi bi-pencil me-2"></i>Edit Product
            </a>
            <a href="{{ route('products.create', ['variant_of' => $product->id]) }}" class="btn btn-outline-primary">
                <i class="bi bi-copy me-2"></i>Add Variant (Size/Color)
            </a>
            <a href="{{ route('stock.add') }}" class="btn btn-outline-success">
                <i class="bi bi-plus-square me-2"></i>Add Stock via Barcode
            </a>
        </div>
    </div>

    {{-- Right column: stock movements --}}
    <div class="col-md-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center py-3">
                <span><i class="bi bi-arrow-left-right me-2"></i>Stock Movement History</span>
                <span class="badge bg-{{ $product->isLowStock() ? 'danger' : 'success' }} fs-6">
                    Current Stock: {{ $product->stock_quantity }}
                </span>
            </div>
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Date / Time</th>
                            <th>Type</th>
                            <th>Qty</th>
                            <th>Before</th>
                            <th>After</th>
                            <th>Notes / Reference</th>
                            <th>By</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($product->stockMovements as $movement)
                        <tr>
                            <td class="small">{{ $movement->created_at->format('d/m/Y H:i') }}</td>
                            <td>
                                <span class="badge bg-{{ $movement->type === 'in' ? 'success' : ($movement->type === 'out' ? 'danger' : 'warning') }}">
                                    {{ strtoupper($movement->type) }}
                                </span>
                            </td>
                            <td class="fw-bold {{ $movement->type === 'in' ? 'text-success' : 'text-danger' }}">
                                {{ $movement->type === 'in' ? '+' : '-' }}{{ $movement->quantity }}
                            </td>
                            <td class="text-muted">{{ $movement->stock_before }}</td>
                            <td class="fw-bold">{{ $movement->stock_after }}</td>
                            <td class="small text-muted">{{ $movement->notes ?? $movement->reference }}</td>
                            <td class="small">{{ $movement->user->name }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="7" class="text-center text-muted py-4">No stock movements recorded</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

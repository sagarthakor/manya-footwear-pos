@extends('layouts.app')
@section('title', 'Inventory Report')
@section('page-title', 'Inventory Report')

@section('content')

{{-- Filters --}}
<div class="card mb-4 no-print">
    <div class="card-body py-3">
        <form method="GET" action="{{ route('reports.inventory') }}" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small fw-semibold mb-1">Search</label>
                <input type="text" name="search" class="form-control form-control-sm"
                    placeholder="Name, SKU, Item Code, Barcode..."
                    value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-semibold mb-1">Category</label>
                <select name="category_id" class="form-select form-select-sm">
                    <option value="">All Categories</option>
                    @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>
                        {{ $cat->name }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-semibold mb-1">Brand</label>
                <select name="brand_id" class="form-select form-select-sm">
                    <option value="">All Brands</option>
                    @foreach($brands as $b)
                    <option value="{{ $b->id }}" {{ request('brand_id') == $b->id ? 'selected' : '' }}>
                        {{ $b->name }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-semibold mb-1">Stock Status</label>
                <select name="stock" class="form-select form-select-sm">
                    <option value="">All Stock</option>
                    <option value="in"  {{ request('stock') === 'in'  ? 'selected' : '' }}>In Stock</option>
                    <option value="low" {{ request('stock') === 'low' ? 'selected' : '' }}>Low Stock</option>
                    <option value="out" {{ request('stock') === 'out' ? 'selected' : '' }}>Out of Stock</option>
                </select>
            </div>
            <div class="col-auto d-flex gap-2">
                <button type="submit" class="btn btn-sm btn-primary">
                    <i class="bi bi-search me-1"></i>Filter
                </button>
                <a href="{{ route('reports.inventory') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-x me-1"></i>Reset
                </a>
                <button type="button" onclick="window.print()" class="btn btn-sm btn-dark">
                    <i class="bi bi-printer me-1"></i>Print
                </button>
            </div>
        </form>
        @if(request()->hasAny(['search','category_id','brand_id','stock']))
        <div class="mt-2">
            <small class="text-muted">
                <i class="bi bi-funnel-fill me-1 text-primary"></i>
                Showing <strong>{{ $products->count() }}</strong> filtered results
                @if(request('search')) — Search: <span class="badge bg-light text-dark border">{{ request('search') }}</span> @endif
                @if(request('category_id')) — Category: <span class="badge bg-secondary">{{ $categories->find(request('category_id'))?->name }}</span> @endif
                @if(request('brand_id')) — Brand: <span class="badge bg-secondary">{{ $brands->find(request('brand_id'))?->name }}</span> @endif
                @if(request('stock')) — Stock: <span class="badge bg-secondary">{{ ucfirst(request('stock')) }}</span> @endif
            </small>
        </div>
        @endif
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="stat-card" style="background:linear-gradient(135deg,#3498db,#2980b9)">
            <div class="stat-value">{{ $products->count() }}</div>
            <div style="opacity:.85">Total Products</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card" style="background:linear-gradient(135deg,#27ae60,#229954)">
            <div class="stat-value">&#8377;{{ number_format($totalValue, 0) }}</div>
            <div style="opacity:.85">Stock Value (Cost)</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card" style="background:linear-gradient(135deg,#e74c3c,#c0392b)">
            <div class="stat-value">{{ $lowStockProducts->count() }}</div>
            <div style="opacity:.85">Low Stock Items</div>
        </div>
    </div>
</div>

@if($lowStockProducts->count())
<div class="alert alert-warning mb-3">
    <i class="bi bi-exclamation-triangle-fill me-2"></i>
    <strong>{{ $lowStockProducts->count() }} product(s)</strong> are running low on stock:
    @foreach($lowStockProducts->take(5) as $p)
    <span class="badge bg-danger ms-1">{{ $p->name }} ({{ $p->stock_quantity }})</span>
    @endforeach
</div>
@endif

<div class="card">
    <div class="card-header py-3"><i class="bi bi-clipboard-data me-2"></i>Product-wise Stock</div>
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Product</th>
                    <th>SKU</th>
                    <th>Item Code</th>
                    <th>Category</th>
                    <th>Size / Color</th>
                    <th>Cost Price</th>
                    <th>Selling Price</th>
                    <th>GST %</th>
                    <th>Stock</th>
                    <th>Stock Value</th>
                    <th>Retail Value</th>
                </tr>
            </thead>
            <tbody>
                @php $prevCat = null; @endphp
                @foreach($products as $product)
                @if($prevCat !== $product->category->name)
                <tr class="table-secondary">
                    <td colspan="11" class="fw-bold small text-uppercase">{{ $product->category->name }}</td>
                </tr>
                @php $prevCat = $product->category->name; @endphp
                @endif
                <tr class="{{ $product->isLowStock() ? 'table-warning' : '' }}">
                    <td>
                        <div class="fw-semibold">{{ $product->name }}</div>
                        <small class="text-muted font-monospace" style="font-size:.68rem">{{ $product->barcode }}</small>
                    </td>
                    <td class="small font-monospace text-muted">{{ $product->sku ?? '—' }}</td>
                    <td class="small font-monospace text-muted">{{ $product->item_code ?? '—' }}</td>
                    <td class="small">{{ $product->category->name }}</td>
                    <td class="small">{{ $product->size ?? '-' }} / {{ $product->color ?? '-' }}</td>
                    <td>&#8377;{{ number_format($product->purchase_price, 0) }}</td>
                    <td>&#8377;{{ number_format($product->selling_price, 0) }}</td>
                    <td>
                        @if($product->tax_percent > 0)
                        <span class="badge bg-warning text-dark">{{ number_format($product->tax_percent, 0) }}%</span>
                        @else
                        <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td>
                        @if($product->isLowStock())
                            <span class="badge bg-danger">{{ $product->stock_quantity }} ⚠️</span>
                        @else
                            <span class="badge bg-success">{{ $product->stock_quantity }}</span>
                        @endif
                    </td>
                    <td>&#8377;{{ number_format($product->stock_quantity * $product->purchase_price, 0) }}</td>
                    <td>&#8377;{{ number_format($product->stock_quantity * $product->selling_price, 0) }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot class="table-light fw-bold">
                <tr>
                    <td colspan="8">Total ({{ $products->count() }} products)</td>
                    <td>{{ $products->sum('stock_quantity') }}</td>
                    <td>&#8377;{{ number_format($totalValue, 0) }}</td>
                    <td>&#8377;{{ number_format($totalRetailValue, 0) }}</td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
@endsection

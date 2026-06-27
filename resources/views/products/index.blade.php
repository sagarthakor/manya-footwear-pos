@extends('layouts.app')
@section('title', 'Products')
@section('page-title', 'Products')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center py-3">
        <span><i class="bi bi-box-seam me-2"></i>Product List</span>
        <div class="d-flex gap-2">
            <button id="bulkPrintBtn" class="btn btn-sm btn-outline-dark" style="display:none"
                onclick="printSelected()">
                <i class="bi bi-printer me-1"></i>Print Selected (<span id="selectedCount">0</span>)
            </button>
            <a href="{{ route('products.create') }}" class="btn btn-sm btn-danger">
                <i class="bi bi-plus-lg"></i> Add Product
            </a>
        </div>
    </div>
    <div class="card-body border-bottom">
        <form method="GET" class="row g-2">
            <div class="col-md-4">
                <input type="text" class="form-control form-control-sm" name="search"
                    value="{{ request('search') }}" placeholder="Search by name, SKU or barcode...">
            </div>
            <div class="col-md-3">
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
                <select name="stock" class="form-select form-select-sm">
                    <option value="">All Stock</option>
                    <option value="low" {{ request('stock') === 'low' ? 'selected' : '' }}>Low Stock</option>
                </select>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-sm btn-outline-primary me-1">
                    <i class="bi bi-search"></i> Search
                </button>
                <a href="{{ route('products.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th style="width:36px">
                            <input type="checkbox" id="selectAll" class="form-check-input" title="Select all">
                        </th>
                        <th>Product</th>
                        <th>Barcode</th>
                        <th>Category</th>
                        <th>Size / Color</th>
                        @can('view purchase price')<th>Cost (₹)</th>@endcan
                        <th>MRP (₹)</th>
                        <th>Selling (₹)</th>
                        <th>GST %</th>
                        <th>Stock</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $product)
                    <tr>
                        <td>
                            <input type="checkbox" class="form-check-input product-check"
                                value="{{ $product->id }}" onchange="updateBulkBtn()">
                        </td>

                        {{-- Product: Name + Brand + SKU + Item Code --}}
                        <td style="min-width:170px">
                            <div class="fw-semibold">{{ $product->name }}</div>
                            @if($product->brand)
                            <div style="font-size:.72rem;color:#6c757d">
                                <i class="bi bi-tag-fill me-1" style="color:#aaa"></i>{{ $product->brand }}
                            </div>
                            @endif
                            <div style="font-size:.7rem;color:#9ca3af" class="font-monospace">
                                SKU: {{ $product->sku }}
                            </div>
                            @if($product->item_code)
                            <div style="font-size:.7rem;color:#9ca3af" class="font-monospace">
                                Item: {{ $product->item_code }}
                            </div>
                            @endif
                        </td>

                        {{-- Barcode --}}
                        <td>
                            @if($product->barcode)
                                <img src="{{ route('barcode.image', $product->barcode) }}"
                                    alt="{{ $product->barcode }}" style="height:28px;display:block">
                                <small class="font-monospace text-muted" style="font-size:.65rem">
                                    {{ $product->barcode }}
                                </small>
                            @else
                                <span class="text-muted small">—</span>
                            @endif
                        </td>

                        <td><span class="badge bg-secondary">{{ $product->category->name }}</span></td>

                        <td class="small">
                            {{ $product->size ?? '—' }}
                            @if($product->color)
                            <span class="text-muted">/ {{ $product->color }}</span>
                            @endif
                        </td>

                        @can('view purchase price')
                        <td class="small">&#8377;{{ number_format($product->purchase_price, 0) }}</td>
                        @endcan

                        <td class="small">
                            @if($product->mrp)
                            &#8377;{{ number_format($product->mrp, 0) }}
                            @else
                            <span class="text-muted">—</span>
                            @endif
                        </td>

                        <td class="fw-semibold text-danger small">&#8377;{{ number_format($product->selling_price, 0) }}</td>

                        <td>
                            @if($product->tax_percent > 0)
                            <span class="badge bg-warning text-dark">{{ number_format($product->tax_percent, 0) }}%</span>
                            @else
                            <span class="text-muted small">0%</span>
                            @endif
                        </td>

                        {{-- Stock + Alert Qty --}}
                        <td>
                            @if($product->stock_quantity == 0)
                                <span class="badge bg-danger">0 ⚠</span>
                            @elseif($product->isLowStock())
                                <span class="badge bg-warning text-dark">{{ $product->stock_quantity }} ⚠</span>
                            @else
                                <span class="badge bg-success">{{ $product->stock_quantity }}</span>
                            @endif
                            @if($product->alert_quantity)
                            <div style="font-size:.65rem;color:#aaa">Alert: {{ $product->alert_quantity }}</div>
                            @endif
                        </td>

                        {{-- Status --}}
                        <td>
                            @if($product->is_active)
                            <span class="badge bg-success-subtle text-success border border-success-subtle">Active</span>
                            @else
                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle">Inactive</span>
                            @endif
                        </td>

                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('products.show', $product) }}"
                                    class="btn btn-outline-info">
                                    <i class="bi bi-eye me-1"></i>View
                                </a>
                                <a href="{{ route('products.barcode', $product) }}"
                                    class="btn btn-outline-dark">
                                    <i class="bi bi-upc me-1"></i>Barcode
                                </a>
                                <a href="{{ route('products.edit', $product) }}"
                                    class="btn btn-outline-warning">
                                    <i class="bi bi-pencil me-1"></i>Edit
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="{{ auth()->user()->can('view purchase price') ? 12 : 11 }}" class="text-center text-muted py-5">No products found</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($products->hasPages())
    <div class="card-footer">{{ $products->links() }}</div>
    @endif
</div>

{{-- Hidden bulk print form --}}
<form id="bulkBarcodeForm" action="{{ route('barcodes.generate') }}" method="POST" target="_blank">
    @csrf
    <div id="bulkProductInputs"></div>
</form>
@endsection

@push('scripts')
<script>
document.getElementById('selectAll').addEventListener('change', function() {
    document.querySelectorAll('.product-check').forEach(function(cb) {
        cb.checked = this.checked;
    }.bind(this));
    updateBulkBtn();
});

function updateBulkBtn() {
    var selected = document.querySelectorAll('.product-check:checked').length;
    var btn = document.getElementById('bulkPrintBtn');
    document.getElementById('selectedCount').textContent = selected;
    btn.style.display = selected > 0 ? 'inline-flex' : 'none';
}

function printSelected() {
    var ids = [];
    document.querySelectorAll('.product-check:checked').forEach(function(cb) {
        ids.push(cb.value);
    });
    if (ids.length === 0) return;

    var form = document.getElementById('bulkBarcodeForm');
    var inputs = document.getElementById('bulkProductInputs');
    inputs.innerHTML = '';
    ids.forEach(function(id) {
        var inp = document.createElement('input');
        inp.type = 'hidden';
        inp.name = 'product_ids[]';
        inp.value = id;
        inputs.appendChild(inp);
    });
    form.submit();
}
</script>
@endpush

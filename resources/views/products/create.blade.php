@extends('layouts.app')
@section('title', 'Add Product')
@section('page-title', 'Add New Product')

@php
    $variantRows = old('variants', [
        ['size' => '', 'color' => '', 'sku' => '', 'item_code' => '', 'barcode' => '', 'stock_quantity' => ''],
    ]);
@endphp

@section('content')
<div class="row justify-content-center">
    <div class="col-md-10">
        <div class="card">
            <div class="card-header py-3"><i class="bi bi-plus-square me-2"></i>Product Details</div>
            <div class="card-body">
                @if($variantOf)
                <div class="alert alert-info d-flex align-items-center">
                    <i class="bi bi-copy me-2"></i>
                    Adding a new size/color variant of <strong class="mx-1">{{ $variantOf->name }}</strong> —
                    name, category, brand and prices are pre-filled. Just set the new Size, Color (and SKU/Barcode if needed).
                </div>
                @endif
                <form action="{{ route('products.store') }}" method="POST">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label fw-semibold">Product Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                value="{{ old('name', $variantOf->name ?? '') }}" required placeholder="e.g. Nike Air Max 270">
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Category <span class="text-danger">*</span></label>
                            <select name="category_id" class="form-select @error('category_id') is-invalid @enderror" required>
                                <option value="">Select Category</option>
                                @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ old('category_id', $variantOf->category_id ?? null) == $cat->id ? 'selected' : '' }}>
                                    {{ $cat->name }}
                                </option>
                                @endforeach
                            </select>
                            @error('category_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Brand</label>
                            <select name="brand_id" class="form-select @error('brand_id') is-invalid @enderror">
                                <option value="">— Select Brand —</option>
                                @foreach($brands as $brand)
                                <option value="{{ $brand->id }}" {{ old('brand_id', $variantOf->brand_id ?? null) == $brand->id ? 'selected' : '' }}>
                                    {{ $brand->name }}
                                </option>
                                @endforeach
                            </select>
                            @error('brand_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        @can('view purchase price')
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Purchase Price (&#8377;) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">&#8377;</span>
                                <input type="number" name="purchase_price" class="form-control @error('purchase_price') is-invalid @enderror"
                                    value="{{ old('purchase_price', $variantOf->purchase_price ?? '') }}" required min="0" step="0.01">
                            </div>
                            @error('purchase_price')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        @endcan
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Selling Price (&#8377;) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">&#8377;</span>
                                <input type="number" name="selling_price" class="form-control @error('selling_price') is-invalid @enderror"
                                    value="{{ old('selling_price', $variantOf->selling_price ?? '') }}" required min="0" step="0.01">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">MRP (&#8377;)</label>
                            <div class="input-group">
                                <span class="input-group-text">&#8377;</span>
                                <input type="number" name="mrp" class="form-control @error('mrp') is-invalid @enderror"
                                    value="{{ old('mrp', $variantOf->mrp ?? '') }}" min="0" step="0.01">
                                @error('mrp')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">GST % <span class="text-danger">*</span></label>
                            <select name="tax_percent" class="form-select @error('tax_percent') is-invalid @enderror">
                                @foreach([0, 5, 12, 18, 28] as $rate)
                                <option value="{{ $rate }}" {{ old('tax_percent', $variantOf->tax_percent ?? 0) == $rate ? 'selected' : '' }}>
                                    {{ $rate }}%{{ $rate === 0 ? ' (No GST)' : '' }}
                                </option>
                                @endforeach
                            </select>
                            @error('tax_percent')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <input type="hidden" name="alert_quantity" value="0">
                        <div class="col-12">
                            <label class="form-label fw-semibold">Description</label>
                            <textarea name="description" class="form-control" rows="2"
                                placeholder="Optional product description...">{{ old('description', $variantOf->description ?? '') }}</textarea>
                        </div>

                        <div class="col-12">
                            <hr>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <label class="form-label fw-semibold mb-0">
                                    Variants (Size / Color) <span class="text-danger">*</span>
                                    <small class="text-muted fw-normal d-block">Add one row per size/color. SKU auto-generates if left blank.</small>
                                </label>
                                <button type="button" id="addVariantBtn" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-plus-lg me-1"></i>Add Another Variant
                                </button>
                            </div>
                            <div id="variantsContainer">
                                @foreach($variantRows as $i => $v)
                                <div class="row g-2 align-items-start variant-row border rounded p-2 mb-2 mx-0">
                                    <div class="col-md-2">
                                        <label class="form-label small mb-1">Size</label>
                                        <input type="text" name="variants[{{ $i }}][size]"
                                            class="form-control form-control-sm @error('variants.'.$i.'.size') is-invalid @enderror"
                                            value="{{ $v['size'] ?? '' }}" placeholder="e.g. 8">
                                        @error('variants.'.$i.'.size')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label small mb-1">Color</label>
                                        <input type="text" name="variants[{{ $i }}][color]"
                                            class="form-control form-control-sm @error('variants.'.$i.'.color') is-invalid @enderror"
                                            value="{{ $v['color'] ?? '' }}" placeholder="e.g. Black">
                                        @error('variants.'.$i.'.color')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label small mb-1">SKU <small class="text-muted">(auto)</small></label>
                                        <input type="text" name="variants[{{ $i }}][sku]"
                                            class="form-control form-control-sm @error('variants.'.$i.'.sku') is-invalid @enderror"
                                            value="{{ $v['sku'] ?? '' }}" placeholder="optional">
                                        @error('variants.'.$i.'.sku')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label small mb-1">Item Code</label>
                                        <input type="text" name="variants[{{ $i }}][item_code]"
                                            class="form-control form-control-sm @error('variants.'.$i.'.item_code') is-invalid @enderror"
                                            value="{{ $v['item_code'] ?? '' }}" placeholder="optional">
                                        @error('variants.'.$i.'.item_code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-md-1">
                                        <label class="form-label small mb-1">Barcode</label>
                                        <input type="text" name="variants[{{ $i }}][barcode]"
                                            class="form-control form-control-sm @error('variants.'.$i.'.barcode') is-invalid @enderror"
                                            value="{{ $v['barcode'] ?? '' }}" placeholder="optional">
                                        @error('variants.'.$i.'.barcode')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label small mb-1">Stock Qty</label>
                                        <input type="number" name="variants[{{ $i }}][stock_quantity]"
                                            class="form-control form-control-sm @error('variants.'.$i.'.stock_quantity') is-invalid @enderror"
                                            value="{{ $v['stock_quantity'] ?? '' }}" min="0" placeholder="0">
                                        @error('variants.'.$i.'.stock_quantity')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-md-1 pt-4 text-end">
                                        <button type="button" class="btn btn-sm btn-outline-danger remove-variant-btn" title="Remove this variant">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    <div class="mt-4 d-flex gap-2">
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-save me-1"></i>Save Product
                        </button>
                        <a href="{{ route('products.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function() {
    var container   = document.getElementById('variantsContainer');
    var addBtn       = document.getElementById('addVariantBtn');
    var variantIndex = {{ count($variantRows) }};

    function rowHtml(index) {
        return '' +
            '<div class="col-md-2">' +
                '<label class="form-label small mb-1">Size</label>' +
                '<input type="text" name="variants[' + index + '][size]" class="form-control form-control-sm" placeholder="e.g. 8">' +
            '</div>' +
            '<div class="col-md-2">' +
                '<label class="form-label small mb-1">Color</label>' +
                '<input type="text" name="variants[' + index + '][color]" class="form-control form-control-sm" placeholder="e.g. Black">' +
            '</div>' +
            '<div class="col-md-2">' +
                '<label class="form-label small mb-1">SKU <small class="text-muted">(auto)</small></label>' +
                '<input type="text" name="variants[' + index + '][sku]" class="form-control form-control-sm" placeholder="optional">' +
            '</div>' +
            '<div class="col-md-2">' +
                '<label class="form-label small mb-1">Item Code</label>' +
                '<input type="text" name="variants[' + index + '][item_code]" class="form-control form-control-sm" placeholder="optional">' +
            '</div>' +
            '<div class="col-md-1">' +
                '<label class="form-label small mb-1">Barcode</label>' +
                '<input type="text" name="variants[' + index + '][barcode]" class="form-control form-control-sm" placeholder="optional">' +
            '</div>' +
            '<div class="col-md-2">' +
                '<label class="form-label small mb-1">Stock Qty</label>' +
                '<input type="number" name="variants[' + index + '][stock_quantity]" class="form-control form-control-sm" min="0" placeholder="0">' +
            '</div>' +
            '<div class="col-md-1 pt-4 text-end">' +
                '<button type="button" class="btn btn-sm btn-outline-danger remove-variant-btn" title="Remove this variant">' +
                    '<i class="bi bi-trash"></i>' +
                '</button>' +
            '</div>';
    }

    function updateRemoveButtons() {
        var rows = container.querySelectorAll('.variant-row');
        rows.forEach(function(row) {
            var btn = row.querySelector('.remove-variant-btn');
            btn.style.display = rows.length > 1 ? '' : 'none';
        });
    }

    addBtn.addEventListener('click', function() {
        var row = document.createElement('div');
        row.className = 'row g-2 align-items-start variant-row border rounded p-2 mb-2 mx-0';
        row.innerHTML = rowHtml(variantIndex);
        container.appendChild(row);
        variantIndex++;
        updateRemoveButtons();
    });

    container.addEventListener('click', function(e) {
        var btn = e.target.closest('.remove-variant-btn');
        if (btn) {
            btn.closest('.variant-row').remove();
            updateRemoveButtons();
        }
    });

    updateRemoveButtons();
})();
</script>
@endpush

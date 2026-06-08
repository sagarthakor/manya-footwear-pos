@extends('layouts.app')
@section('title', 'Add Product')
@section('page-title', 'Add New Product')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header py-3"><i class="bi bi-plus-square me-2"></i>Product Details</div>
            <div class="card-body">
                <form action="{{ route('products.store') }}" method="POST">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label fw-semibold">Product Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                value="{{ old('name') }}" required placeholder="e.g. Nike Air Max 270">
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Category <span class="text-danger">*</span></label>
                            <select name="category_id" class="form-select @error('category_id') is-invalid @enderror" required>
                                <option value="">Select Category</option>
                                @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>
                                    {{ $cat->name }}
                                </option>
                                @endforeach
                            </select>
                            @error('category_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">SKU / Article No <span class="text-danger">*</span></label>
                            <input type="text" name="sku" class="form-control @error('sku') is-invalid @enderror"
                                value="{{ old('sku') }}" required placeholder="e.g. NK-AM270-001">
                            @error('sku')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Item Code</label>
                            <input type="text" name="item_code" class="form-control @error('item_code') is-invalid @enderror"
                                value="{{ old('item_code') }}" placeholder="Manufacturer item/article code">
                            @error('item_code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Barcode</label>
                            <input type="text" name="barcode" id="barcodeInput"
                                class="form-control @error('barcode') is-invalid @enderror"
                                value="{{ old('barcode') }}" placeholder="Leave blank to auto-generate">
                            @error('barcode')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <div class="form-text">If blank, a unique barcode will be generated automatically.</div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Brand</label>
                            <select name="brand_id" class="form-select @error('brand_id') is-invalid @enderror">
                                <option value="">— Select Brand —</option>
                                @foreach($brands as $brand)
                                <option value="{{ $brand->id }}" {{ old('brand_id') == $brand->id ? 'selected' : '' }}>
                                    {{ $brand->name }}
                                </option>
                                @endforeach
                            </select>
                            @error('brand_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-semibold">Size</label>
                            <input type="text" name="size" class="form-control"
                                value="{{ old('size') }}" placeholder="e.g. 8">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-semibold">Color</label>
                            <input type="text" name="color" class="form-control"
                                value="{{ old('color') }}" placeholder="e.g. Black">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Purchase Price (&#8377;) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">&#8377;</span>
                                <input type="number" name="purchase_price" class="form-control @error('purchase_price') is-invalid @enderror"
                                    value="{{ old('purchase_price') }}" required min="0" step="0.01">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Selling Price (&#8377;) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">&#8377;</span>
                                <input type="number" name="selling_price" class="form-control @error('selling_price') is-invalid @enderror"
                                    value="{{ old('selling_price') }}" required min="0" step="0.01">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">MRP (&#8377;)</label>
                            <div class="input-group">
                                <span class="input-group-text">&#8377;</span>
                                <input type="number" name="mrp" class="form-control"
                                    value="{{ old('mrp') }}" min="0" step="0.01">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">GST % <span class="text-danger">*</span></label>
                            <select name="tax_percent" class="form-select @error('tax_percent') is-invalid @enderror">
                                @foreach([0, 5, 12, 18, 28] as $rate)
                                <option value="{{ $rate }}" {{ old('tax_percent', 0) == $rate ? 'selected' : '' }}>
                                    {{ $rate }}%{{ $rate === 0 ? ' (No GST)' : '' }}
                                </option>
                                @endforeach
                            </select>
                            @error('tax_percent')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Low Stock Alert Qty <span class="text-danger">*</span></label>
                            <input type="number" name="alert_quantity" class="form-control"
                                value="{{ old('alert_quantity', 5) }}" required min="0">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Description</label>
                            <textarea name="description" class="form-control" rows="2"
                                placeholder="Optional product description...">{{ old('description') }}</textarea>
                        </div>
                    </div>
                    <div class="mt-4 d-flex gap-2">
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-save me-1"></i>Save & Generate Barcode
                        </button>
                        <a href="{{ route('products.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

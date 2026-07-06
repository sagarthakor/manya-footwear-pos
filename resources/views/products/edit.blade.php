@extends('layouts.app')
@section('title', 'Edit Product')
@section('page-title', 'Edit Product')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header py-3"><i class="bi bi-pencil me-2"></i>Edit: {{ $product->name }}</div>
            <div class="card-body">
                <form action="{{ route('products.update', $product) }}" method="POST">
                    @csrf @method('PUT')
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label fw-semibold">Product Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                value="{{ old('name', $product->name) }}" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Category <span class="text-danger">*</span></label>
                            <select name="category_id" class="form-select" required>
                                @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ old('category_id', $product->category_id) == $cat->id ? 'selected' : '' }}>
                                    {{ $cat->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Article Code (SKU) <small class="text-muted fw-normal">(optional, auto-generated if left blank)</small></label>
                            <input type="text" name="sku" class="form-control @error('sku') is-invalid @enderror"
                                value="{{ old('sku', $product->sku) }}">
                            @error('sku')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Item Code <small class="text-muted fw-normal">(optional)</small></label>
                            <input type="text" name="item_code" class="form-control @error('item_code') is-invalid @enderror"
                                value="{{ old('item_code', $product->item_code) }}"
                                placeholder="Secondary item/supplier code">
                            @error('item_code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Barcode</label>
                            <input type="text" name="barcode" class="form-control @error('barcode') is-invalid @enderror"
                                value="{{ old('barcode', $product->barcode) }}" id="editBarcodeInput">
                            @error('barcode')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            @if($product->barcode)
                            <div class="mt-1">
                                <img src="{{ route('barcode.image', $product->barcode) }}"
                                    alt="{{ $product->barcode }}" id="editBarcodeImg"
                                    style="height:28px">
                                <small class="font-monospace text-muted ms-1" style="font-size:.7rem">
                                    {{ $product->barcode }}
                                </small>
                            </div>
                            @endif
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Brand</label>
                            <select name="brand_id" class="form-select @error('brand_id') is-invalid @enderror">
                                <option value="">— Select Brand —</option>
                                @foreach($brands as $brand)
                                <option value="{{ $brand->id }}"
                                    {{ old('brand_id', $product->brand_id) == $brand->id ? 'selected' : '' }}>
                                    {{ $brand->name }}
                                </option>
                                @endforeach
                            </select>
                            @error('brand_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-semibold">Size</label>
                            <input type="text" name="size" class="form-control @error('size') is-invalid @enderror" value="{{ old('size', $product->size) }}">
                            @error('size')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-semibold">Color</label>
                            <input type="text" name="color" class="form-control" value="{{ old('color', $product->color) }}">
                        </div>
                        @can('view purchase price')
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Purchase Price (&#8377;) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">&#8377;</span>
                                <input type="number" name="purchase_price" class="form-control @error('purchase_price') is-invalid @enderror"
                                    value="{{ old('purchase_price', $product->purchase_price) }}" required min="0" step="0.01">
                            </div>
                            @error('purchase_price')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        @endcan
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Selling Price (&#8377;) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">&#8377;</span>
                                <input type="number" name="selling_price" class="form-control"
                                    value="{{ old('selling_price', $product->selling_price) }}" required min="0" step="0.01">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">MRP (&#8377;)</label>
                            <div class="input-group">
                                <span class="input-group-text">&#8377;</span>
                                <input type="number" name="mrp" class="form-control @error('mrp') is-invalid @enderror"
                                    value="{{ old('mrp', $product->mrp) }}" min="0" step="0.01">
                                @error('mrp')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">GST %</label>
                            <select name="tax_percent" class="form-select @error('tax_percent') is-invalid @enderror">
                                @foreach([0, 5, 12, 18, 28] as $rate)
                                <option value="{{ $rate }}" {{ old('tax_percent', $product->tax_percent) == $rate ? 'selected' : '' }}>
                                    {{ $rate }}%{{ $rate === 0 ? ' (No GST)' : '' }}
                                </option>
                                @endforeach
                            </select>
                            @error('tax_percent')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <input type="hidden" name="alert_quantity" value="0">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Status</label>
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1"
                                    {{ old('is_active', $product->is_active) ? 'checked' : '' }}>
                                <label class="form-check-label">Active</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Description</label>
                            <textarea name="description" class="form-control" rows="2">{{ old('description', $product->description) }}</textarea>
                        </div>
                    </div>
                    <div class="mt-4 d-flex gap-2">
                        <button type="submit" class="btn btn-danger"><i class="bi bi-save me-1"></i>Update</button>
                        <a href="{{ route('products.show', $product) }}" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
var editBarcodeInput = document.getElementById('editBarcodeInput');
var editBarcodeImg   = document.getElementById('editBarcodeImg');

if (editBarcodeInput && editBarcodeImg) {
    editBarcodeInput.addEventListener('input', function () {
        var val = this.value.trim();
        if (val.length > 3) {
            editBarcodeImg.src = '/barcode/image/' + encodeURIComponent(val);
            editBarcodeImg.style.display = '';
        } else {
            editBarcodeImg.style.display = 'none';
        }
    });
}
</script>
@endpush

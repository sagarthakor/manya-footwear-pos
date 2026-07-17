@extends('layouts.app')
@section('title', 'Barcode Label — ' . $product->name)
@section('page-title', 'Barcode Label — ' . $product->name)

@push('styles')
<style>
    .barcode-preview {
        background: #fff;
        border: 2px dashed #dee2e6;
        border-radius: 12px;
        padding: 2rem;
        text-align: center;
    }
    .label-card {
        display: inline-block;
        width: 60mm;
        border: 1px solid #333;
        padding: 4mm;
        text-align: center;
        font-family: Arial, sans-serif;
        background: #fff;
        border-radius: 4px;
        box-shadow: 0 2px 8px rgba(0,0,0,.15);
    }
    .label-card .shop { font-size: 9px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; color: #555; }
    .label-card .pname { font-size: 9px; font-weight: bold; margin: 2px 0; max-width: 52mm; overflow: hidden; white-space: nowrap; text-overflow: ellipsis; }
    .label-card .pmeta { font-size: 8px; color: #666; margin-bottom: 2px; }
    .label-card .pprice { font-size: 13px; font-weight: bold; color: #e74c3c; margin: 2px 0; }
    .label-card .bimg { max-width: 52mm; height: 20mm; }
    .label-card .bnum { font-size: 7.5px; font-family: 'Courier New', monospace; color: #444; margin-top: 2px; letter-spacing: 1px; }

    .qty-selector { display: flex; align-items: center; gap: 10px; justify-content: center; }
    .qty-selector button { width: 36px; height: 36px; border-radius: 50%; border: 2px solid #dc3545; background: #fff; color: #dc3545; font-size: 1.2rem; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all .2s; }
    .qty-selector button:hover { background: #dc3545; color: #fff; }
    .qty-selector input { width: 70px; text-align: center; font-size: 1.3rem; font-weight: bold; border: 2px solid #dee2e6; border-radius: 8px; padding: 4px; }

    @media print {
        body * { visibility: hidden; }
        #printArea, #printArea * { visibility: visible; }
        #printArea { position: fixed; top: 0; left: 0; width: 100%; }
        .print-label {
            display: block;
            width: 60mm; height: 29mm; box-sizing: border-box; border: none; padding: 1.5mm 2mm;
            text-align: center; font-family: Arial, sans-serif; overflow: hidden;
            page-break-inside: avoid; page-break-after: always; margin: 0 auto;
        }
        .print-label:last-child { page-break-after: auto; }
        .print-label .shop  { font-size: 2mm; line-height: 1; font-weight: bold; text-transform: uppercase; letter-spacing: 0.3mm; }
        .print-label .pname { font-size: 1.8mm; line-height: 1; font-weight: bold; max-width: 56mm; overflow: hidden; white-space: nowrap; text-overflow: ellipsis; margin: 0.3mm 0; }
        .print-label .pmeta { font-size: 1.6mm; line-height: 1; color: #444; }
        .print-label .pprice { font-size: 2.6mm; line-height: 1; font-weight: bold; margin: 0.3mm 0; }
        .print-label .bimg  { max-width: 56mm; height: 11mm; display: block; margin: 0 auto; }
        .print-label .bnum  { font-size: 1.6mm; line-height: 1; font-family: 'Courier New'; letter-spacing: 0.2mm; margin-top: 0.3mm; }
        @page { size: 60mm 30mm; margin: 0; }
    }
</style>
@endpush

@section('content')
@if(session('success'))
<div class="alert alert-success alert-dismissible fade show">
    <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<div class="row g-4">
    {{-- Left: Product info + controls --}}
    <div class="col-md-5">
        <div class="card">
            <div class="card-header py-3">
                <i class="bi bi-box-seam me-2"></i>Product Details
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless mb-0">
                    <tr><td class="text-muted" style="width:40%">Product Name</td><td class="fw-bold">{{ $product->name }}</td></tr>
                    <tr><td class="text-muted">SKU</td><td class="font-monospace">{{ $product->sku }}</td></tr>
                    <tr><td class="text-muted">Category</td><td>{{ $product->category->name }}</td></tr>
                    @if($product->brand)<tr><td class="text-muted">Brand</td><td>{{ $product->brand }}</td></tr>@endif
                    @if($product->size)<tr><td class="text-muted">Size</td><td>{{ $product->size }}</td></tr>@endif
                    @if($product->color)<tr><td class="text-muted">Color</td><td>{{ $product->color }}</td></tr>@endif
                    <tr><td class="text-muted">Selling Price</td><td class="fw-bold text-danger fs-5">&#8377;{{ number_format($product->selling_price, 0) }}</td></tr>
                    <tr>
                        <td class="text-muted">Barcode</td>
                        <td>
                            @if($product->barcode)
                                <span class="font-monospace fw-bold">{{ $product->barcode }}</span>
                                <span class="badge bg-success ms-2">Auto-Generated</span>
                            @else
                                <span class="badge bg-warning text-dark">Not Assigned</span>
                            @endif
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header py-3">
                <i class="bi bi-printer me-2"></i>Print Settings
            </div>
            <div class="card-body text-center">
                <p class="text-muted mb-3">How many labels do you want to print?</p>
                <div class="qty-selector mb-3">
                    <button onclick="changeQty(-1)">−</button>
                    <input type="number" id="labelQty" value="1" min="1" max="100">
                    <button onclick="changeQty(1)">+</button>
                </div>
                <div class="d-grid gap-2">
                    <button onclick="printLabels()" class="btn btn-danger btn-lg" @if(!$product->barcode) disabled @endif>
                        <i class="bi bi-printer-fill me-2"></i>Print Barcode Labels
                    </button>
                    <a href="{{ route('products.show', $product) }}" class="btn btn-outline-secondary">
                        <i class="bi bi-eye me-1"></i>View Product
                    </a>
                    <a href="{{ route('products.index') }}" class="btn btn-outline-primary">
                        <i class="bi bi-arrow-left me-1"></i>Back to Products
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Right: Barcode preview + print area --}}
    <div class="col-md-7">
        <div class="card">
            <div class="card-header py-3">
                <i class="bi bi-upc me-2"></i>Barcode Label Preview
                <small class="text-muted ms-2">(60mm × 30mm — Standard label size)</small>
            </div>
            <div class="card-body barcode-preview">
                <p class="text-muted small mb-3">
                    <i class="bi bi-info-circle me-1"></i>
                    This is what the label looks like. Print and stick on the product.
                </p>

                @if($product->barcode)
                {{-- Single preview --}}
                <div class="label-card">
                    <div class="shop">Mayank Footware</div>
                    <div class="pname">{{ $product->name }}</div>
                    @if($product->size || $product->color)
                    <div class="pmeta">
                        {{ $product->size ? 'Size: '.$product->size : '' }}
                        {{ $product->size && $product->color ? ' | ' : '' }}
                        {{ $product->color ?? '' }}
                    </div>
                    @endif
                    <div class="pprice">&#8377;{{ number_format($product->selling_price, 0) }}</div>
                    <img class="bimg"
                        src="{{ route('barcode.image', $product->barcode) }}"
                        alt="{{ $product->barcode }}">
                    <div class="bnum">{{ $product->barcode }}</div>
                </div>

                <div class="mt-3 text-muted small">
                    <i class="bi bi-lightbulb me-1 text-warning"></i>
                    <strong>Instructions:</strong> Click "Print Barcode Labels", set copies, then cut and stick each label on the product.
                </div>
                @else
                <div class="alert alert-warning mb-0">
                    <i class="bi bi-exclamation-triangle me-1"></i>
                    This product doesn't have a barcode yet, so no label can be printed.
                    <a href="{{ route('barcodes.assign') }}" class="alert-link">Assign a barcode</a> to this product first.
                </div>
                @endif
            </div>
        </div>

        {{-- How it works info --}}
        <div class="card mt-3">
            <div class="card-header py-3"><i class="bi bi-diagram-3 me-2"></i>How the Barcode System Works</div>
            <div class="card-body">
                <div class="row g-2 text-center">
                    <div class="col-3">
                        <div class="p-2 bg-light rounded">
                            <div style="font-size:1.8rem">📦</div>
                            <div class="small fw-semibold mt-1">1. Add Product</div>
                            <div style="font-size:.7rem;color:#6c757d">Barcode auto-generates</div>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="p-2 bg-light rounded">
                            <div style="font-size:1.8rem">🖨️</div>
                            <div class="small fw-semibold mt-1">2. Print Label</div>
                            <div style="font-size:.7rem;color:#6c757d">Print & stick on product</div>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="p-2 bg-light rounded">
                            <div style="font-size:1.8rem">📥</div>
                            <div class="small fw-semibold mt-1">3. Add Stock</div>
                            <div style="font-size:.7rem;color:#6c757d">Scan barcode → add qty</div>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="p-2 bg-light rounded">
                            <div style="font-size:1.8rem">🛒</div>
                            <div class="small fw-semibold mt-1">4. Sell</div>
                            <div style="font-size:.7rem;color:#6c757d">Scan → bill ready</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Hidden print area --}}
<div id="printArea" style="display:none"></div>
@endsection

@push('scripts')
<script>
var product = {
    name:          {!! json_encode($product->name) !!},
    barcode:       {!! json_encode($product->barcode) !!},
    barcodeImg:    {!! json_encode($barcodeImage) !!},
    selling_price: {{ (float) $product->selling_price }},
    size:          {!! json_encode($product->size) !!},
    color:         {!! json_encode($product->color) !!}
};

function changeQty(delta) {
    var input = document.getElementById('labelQty');
    var val = parseInt(input.value) + delta;
    if (val < 1) val = 1;
    if (val > 100) val = 100;
    input.value = val;
}

function printLabels() {
    if (!product.barcode) return;
    var qty      = parseInt(document.getElementById('labelQty').value) || 1;
    var printArea = document.getElementById('printArea');
    printArea.style.display = 'block';

    var detail = '';
    if (product.size)              detail += 'Size: ' + product.size;
    if (product.size && product.color) detail += ' | ';
    if (product.color)             detail += product.color;

    var priceInt = Math.round(product.selling_price);
    var name = product.name.length > 26 ? product.name.substring(0, 26) + '...' : product.name;

    var labels = '';
    for (var i = 0; i < qty; i++) {
        labels +=
            '<div class="print-label">' +
            '<div class="shop">Mayank Footware</div>' +
            '<div class="pname">' + name + '</div>' +
            (detail ? '<div class="pmeta">' + detail + '</div>' : '') +
            '<div class="pprice">&#8377;' + priceInt + '</div>' +
            '<img class="bimg" src="' + product.barcodeImg + '" alt="barcode">' +
            '<div class="bnum">' + product.barcode + '</div>' +
            '</div>';
    }
    printArea.innerHTML = labels;

    setTimeout(function () { window.print(); }, 300);
    setTimeout(function () { printArea.style.display = 'none'; }, 2000);
}
</script>
@endpush

@extends('layouts.app')
@section('title', 'Scan to Assign Barcode')
@section('page-title', 'Scan to Assign Barcode')

@section('content')
<div class="row g-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header py-3">
                <i class="bi bi-upc-scan me-2"></i>Assign Barcode to Product
            </div>
            <div class="card-body">

                @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                @endif
                @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                @endif

                <form method="POST" action="{{ route('barcodes.assign.store') }}" id="assignForm">
                    @csrf

                    <div class="mb-4">
                        <label class="form-label fw-semibold">
                            <i class="bi bi-upc-scan me-1"></i>Barcode
                            <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-upc"></i></span>
                            <input type="text" name="barcode" id="barcodeInput"
                                class="form-control form-control-lg @error('barcode') is-invalid @enderror"
                                value="{{ old('barcode', $prefill) }}"
                                placeholder="Scan or type barcode…"
                                autocomplete="off" autofocus>
                            @error('barcode')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-text">Focus here and scan with barcode scanner, or type manually.</div>
                        <div id="barcodePreview" class="mt-2" style="{{ old('barcode', $prefill) ? '' : 'display:none' }}">
                            <img id="barcodeImg" src="{{ old('barcode', $prefill) ? route('barcode.image', old('barcode', $prefill)) : '' }}"
                                alt="barcode" style="height:40px">
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold">
                            Product <span class="text-danger">*</span>
                        </label>
                        <select name="product_id" id="productSelect"
                            class="form-select @error('product_id') is-invalid @enderror" required>
                            <option value="">— Select Product —</option>
                            @foreach($products as $product)
                            <option value="{{ $product->id }}"
                                {{ old('product_id') == $product->id ? 'selected' : '' }}>
                                {{ $product->name }}
                                @if($product->sku) ({{ $product->sku }}) @endif
                            </option>
                            @endforeach
                        </select>
                        @error('product_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-link-45deg me-1"></i>Assign Barcode
                        </button>
                        <button type="button" class="btn btn-outline-secondary" onclick="resetForm()">
                            <i class="bi bi-arrow-counterclockwise"></i> Clear
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card">
            <div class="card-header py-3">
                <i class="bi bi-clock-history me-2"></i>Recent Assignments
            </div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Barcode</th>
                            <th>Product</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recent as $item)
                        <tr>
                            <td class="font-monospace small">{{ $item->barcode }}</td>
                            <td class="small fw-semibold">{{ $item->product->name }}</td>
                            <td>
                                <span class="badge bg-{{ $item->status_color }}">
                                    {{ ucfirst($item->status) }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="text-center text-muted py-3">No assignments yet</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
var barcodeInput = document.getElementById('barcodeInput');
var preview      = document.getElementById('barcodePreview');
var previewImg   = document.getElementById('barcodeImg');

barcodeInput.addEventListener('input', function () {
    var val = this.value.trim();
    if (val.length > 3) {
        previewImg.src = '/barcode/image/' + encodeURIComponent(val);
        preview.style.display = '';
    } else {
        preview.style.display = 'none';
    }
});

// Auto-submit on scan (scanner sends Enter)
barcodeInput.addEventListener('keydown', function (e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        var productSel = document.getElementById('productSelect');
        if (productSel.value) {
            document.getElementById('assignForm').submit();
        } else {
            productSel.focus();
        }
    }
});

function resetForm() {
    barcodeInput.value = '';
    document.getElementById('productSelect').value = '';
    preview.style.display = 'none';
    barcodeInput.focus();
}

// Focus barcode input after success
@if(session('success'))
setTimeout(() => barcodeInput.focus(), 100);
@endif
</script>
@endpush
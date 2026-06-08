@extends('layouts.app')
@section('title', 'Bulk Generate Barcodes')
@section('page-title', 'Bulk Generate Barcodes')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header py-3">
                <i class="bi bi-upc me-2"></i>Generate New Barcodes
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('barcodes.bulk-generate.store') }}">
                    @csrf

                    <div class="mb-4">
                        <label class="form-label fw-semibold">Quantity <span class="text-danger">*</span></label>
                        <input type="number" name="quantity" class="form-control @error('quantity') is-invalid @enderror"
                            value="{{ old('quantity', 10) }}" min="1" max="500" required>
                        @error('quantity')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Maximum 500 per batch.</div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold">Barcode Prefix</label>
                        <input type="text" name="prefix" id="prefixInput"
                            class="form-control @error('prefix') is-invalid @enderror"
                            value="{{ old('prefix', 'MF') }}" maxlength="6" placeholder="MF"
                            oninput="updatePreview()">
                        @error('prefix')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Alphanumeric, max 6 characters. Default: <strong>MF</strong></div>
                    </div>

                    <div class="mb-4 p-3 bg-light rounded border">
                        <div class="small text-muted mb-1">Preview format:</div>
                        <div class="font-monospace fw-bold" id="previewText">MF20260603XXXXXXX</div>
                        <div class="mt-2">
                            <img id="previewImg" src="{{ route('barcode.image', 'MF20260603XXXXXXX') }}"
                                alt="preview" style="height:36px" onerror="this.style.display='none'">
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-plus-lg me-1"></i>Generate Barcodes
                        </button>
                        <a href="{{ route('barcodes.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function updatePreview() {
    var prefix = document.getElementById('prefixInput').value.toUpperCase() || 'MF';
    var today  = '{{ date("Ymd") }}';
    var sample = prefix + today + '0001234';
    document.getElementById('previewText').textContent = sample;
    document.getElementById('previewImg').src = '/barcode/image/' + encodeURIComponent(sample);
}
</script>
@endpush
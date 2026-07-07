@extends('layouts.app')
@section('title', 'Adjust Stock')
@section('page-title', 'Adjust Stock')

@push('styles')
<style>
    .search-zone {
        background: linear-gradient(135deg,#6c3483,#a569bd);
        border-radius: 14px; color: #fff; padding: 2rem; text-align: center;
    }
    #stockSearchInput {
        font-size: 1.2rem; text-align: center;
        background: rgba(255,255,255,.18); border: 2px solid rgba(255,255,255,.45);
        color: #fff; border-radius: 8px; letter-spacing: 1px;
    }
    #stockSearchInput::placeholder { color: rgba(255,255,255,.6); font-size: .95rem; letter-spacing: 0; }
    #stockSearchInput:focus { background: rgba(255,255,255,.28); border-color: #fff; box-shadow: none; color: #fff; }
    .search-badge { background: rgba(255,255,255,.15); border-radius: 20px; padding: 3px 12px; font-size: .78rem; margin: 3px; display:inline-block; }
    .product-found-card { border: 2px solid #6c3483; border-radius: 12px; padding: 1.5rem; background: #fff; }
    .result-item {
        cursor: pointer; padding: .7rem 1rem; border-bottom: 1px solid #eee; transition: background .15s;
    }
    .result-item:hover { background: #f4ecf7; }
    .result-item:last-child { border-bottom: none; }
</style>
@endpush

@section('content')
<div class="row justify-content-center">
    <div class="col-md-7">

        {{-- Unified Search Box --}}
        <div class="search-zone mb-3">
            <i class="bi bi-sliders" style="font-size:2.2rem;opacity:.85"></i>
            <h5 class="mt-2 mb-1">Adjust Stock</h5>
            <p class="mb-3" style="opacity:.75;font-size:.9rem">
                <span class="search-badge"><i class="bi bi-upc-scan me-1"></i>Barcode</span>
                <span class="search-badge"><i class="bi bi-tag me-1"></i>Article No</span>
                <span class="search-badge"><i class="bi bi-hash me-1"></i>Item Code</span>
                <span class="search-badge"><i class="bi bi-search me-1"></i>Product Name</span>
            </p>
            <input type="text" id="stockSearchInput" class="form-control form-control-lg"
                placeholder="Scan barcode / Article No / Item Code / Product Name..."
                autocomplete="off" autofocus>
            <small class="mt-2 d-block" style="opacity:.7">Barcode scan karo ya Article No / naam type karo</small>
        </div>

        {{-- Search Results Dropdown --}}
        <div id="searchResultsBox" class="mb-3" style="display:none">
            <div class="card shadow">
                <div class="card-header py-2 px-3 bg-light">
                    <small class="text-muted fw-semibold">Products Found — click to select</small>
                </div>
                <div id="searchResultsList"></div>
            </div>
        </div>

        {{-- Product Found / Adjust Stock Form --}}
        <div id="productFoundSection" class="product-found-card mb-3" style="display:none">
            <div class="d-flex align-items-center gap-3 mb-3">
                <img id="productBarcodeImg" src="" alt="barcode" style="height:52px;display:none">
                <div>
                    <h6 class="mb-0 fw-bold" id="productFoundName">-</h6>
                    <small class="text-muted" id="productFoundMeta">-</small><br>
                    <span class="badge bg-secondary mt-1" id="currentStockBadge">Stock: -</span>
                </div>
            </div>

            <form action="{{ route('stock.adjust.process') }}" method="POST">
                @csrf
                <input type="hidden" name="product_id" id="hiddenProductIdInput">
                <div class="row g-3">
                    <div class="col-md-5">
                        <label class="form-label fw-semibold">New Stock Quantity <span class="text-danger">*</span></label>
                        <input type="number" name="new_quantity" id="newQtyInput"
                            class="form-control form-control-lg text-center fw-bold"
                            min="0" required>
                        <small class="text-muted">Current: <span id="currentQtyText">-</span></small>
                    </div>
                    <div class="col-md-7">
                        <label class="form-label fw-semibold">Reason / Notes <span class="text-danger">*</span></label>
                        <input type="text" name="notes" class="form-control" placeholder="e.g. Physical count mismatch, damaged goods" required>
                    </div>
                </div>
                <div class="mt-3 d-flex gap-2">
                    <button type="submit" class="btn btn-lg flex-grow-1 text-white" style="background:#6c3483">
                        <i class="bi bi-check-circle-fill me-2"></i>Save Adjustment
                    </button>
                    <button type="button" class="btn btn-outline-secondary btn-lg" id="clearBtn" title="Clear / Search Again">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
            </form>
        </div>

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
        @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ $errors->first() }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif
        <div id="alertBox"></div>
    </div>
</div>
@endsection

@push('scripts')
<script>
var searchInput  = document.getElementById('stockSearchInput');
var resultsBox   = document.getElementById('searchResultsBox');
var resultsList  = document.getElementById('searchResultsList');
var productSection = document.getElementById('productFoundSection');
var alertBox     = document.getElementById('alertBox');
var searchTimeout;

// ----- Input handler -----
searchInput.addEventListener('keydown', function(e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        clearTimeout(searchTimeout);
        doSearch(this.value.trim());
    }
});

searchInput.addEventListener('input', function() {
    clearTimeout(searchTimeout);
    var val = this.value.trim();
    if (val.length < 2) {
        hideResults(); hideAlert(); return;
    }
    searchTimeout = setTimeout(function() { doSearch(val); }, 400);
});

// ----- Main search function -----
function doSearch(q) {
    if (!q) return;
    hideAlert();

    // Try exact barcode first (pure digits, 8+ chars)
    if (/^\d{8,}$/.test(q)) {
        fetch('/api/stock/barcode?barcode=' + encodeURIComponent(q))
            .then(function(res) { return res.ok ? res.json() : null; })
            .then(function(p) {
                if (p && p.id) { showProduct(p); }
                else { searchByName(q); }
            })
            .catch(function() { searchByName(q); });
    } else {
        searchByName(q);
    }
}

// ----- Name / item code search -----
function searchByName(q) {
    fetch('/api/products/search-pos?q=' + encodeURIComponent(q))
        .then(function(res) { return res.json(); })
        .then(function(products) {
            if (!Array.isArray(products) || products.length === 0) {
                showAlert('danger', 'No product found for: <strong>' + escHtml(q) + '</strong>');
                hideResults(); return;
            }
            if (products.length === 1) {
                showProduct(products[0]); return;
            }
            showResultsList(products);
        })
        .catch(function() {
            showAlert('danger', 'Search error. Please try again.');
        });
}

// ----- Show product form -----
function showProduct(p) {
    hideResults();
    productSection.style.display = 'block';
    document.getElementById('productFoundName').textContent = p.name;
    document.getElementById('productFoundMeta').textContent =
        'Article No: ' + (p.sku || '—') +
        (p.item_code ? '  |  Item Code: ' + p.item_code : '') +
        (p.size  ? '  |  Size: ' + p.size  : '') +
        (p.color ? '  |  ' + p.color : '');
    document.getElementById('currentStockBadge').textContent = 'Current Stock: ' + p.stock_quantity;
    document.getElementById('currentQtyText').textContent = p.stock_quantity;
    document.getElementById('hiddenProductIdInput').value = p.id;
    var newQtyInput = document.getElementById('newQtyInput');
    newQtyInput.value = p.stock_quantity;
    var barcodeImg = document.getElementById('productBarcodeImg');
    if (p.barcode) {
        barcodeImg.src = '/barcode/image/' + p.barcode;
        barcodeImg.style.display = '';
    } else {
        barcodeImg.style.display = 'none';
    }
    searchInput.disabled = true;
    newQtyInput.focus();
    newQtyInput.select();
}

// ----- Show multiple results -----
function showResultsList(products) {
    hideAlert();
    resultsList.innerHTML = '';
    products.forEach(function(p) {
        var div = document.createElement('div');
        div.className = 'result-item';
        div.innerHTML =
            '<div class="fw-semibold">' + escHtml(p.name) + '</div>' +
            '<small class="text-muted">' +
                '<span class="me-2">Article: <strong>' + escHtml(p.sku || '—') + '</strong></span>' +
                (p.item_code ? '<span class="me-2">Item: ' + escHtml(p.item_code) + '</span>' : '') +
                (p.size  ? '<span class="me-2">Size: ' + escHtml(p.size) + '</span>' : '') +
                (p.color ? '<span class="me-2">' + escHtml(p.color) + '</span>' : '') +
                '<span class="text-success fw-semibold">Stock: ' + p.stock_quantity + '</span>' +
            '</small>';
        div.addEventListener('click', function() { showProduct(p); });
        resultsList.appendChild(div);
    });
    resultsBox.style.display = 'block';
    productSection.style.display = 'none';
}

// ----- Clear / reset -----
document.getElementById('clearBtn').addEventListener('click', resetPage);

function resetPage() {
    searchInput.disabled = false;
    searchInput.value = '';
    productSection.style.display = 'none';
    hideResults(); hideAlert();
    searchInput.focus();
}

function hideResults() { resultsBox.style.display = 'none'; resultsList.innerHTML = ''; }
function hideAlert()   { alertBox.innerHTML = ''; }

function showAlert(type, html) {
    alertBox.innerHTML = '<div class="alert alert-' + type + '"><i class="bi bi-exclamation-triangle me-2"></i>' + html + '</div>';
}

function escHtml(str) {
    if (!str) return '';
    return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

searchInput.focus();
</script>
@endpush

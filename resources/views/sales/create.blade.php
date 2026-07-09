@extends('layouts.app')
@section('title', 'POS - New Sale')
@section('page-title', 'POS — New Sale')

@push('styles')
<style>
    /* ── Desktop POS layout ── */
    .pos-container {
        display: grid;
        grid-template-columns: 1fr 370px;
        gap: 1rem;
        height: calc(100vh - 100px);
        min-height: 500px;
    }
    .pos-left { overflow-y: auto; display: flex; flex-direction: column; gap: .75rem; }
    .pos-right {
        display: flex; flex-direction: column;
        background: #fff; border-radius: 14px;
        box-shadow: 0 2px 16px rgba(0,0,0,.08); overflow: hidden;
    }
    .pos-header {
        background: linear-gradient(135deg,#1a1f36,#2c3a5a);
        color: #fff; padding: .85rem 1rem;
        display: flex; align-items: center; justify-content: space-between;
        flex-shrink: 0;
    }
    .pos-header h6 { margin: 0; font-size: .95rem; font-weight: 600; }
    .cart-items { flex: 1; overflow-y: auto; padding: .5rem; min-height: 0; }
    .cart-item {
        background: #f8fafc; border: 1px solid #edf2f7;
        border-radius: 10px; padding: 10px 12px; margin-bottom: 6px;
        transition: border-color .15s;
    }
    .cart-item:hover { border-color: #e74c3c; }
    .cart-item .item-name { font-weight: 600; font-size: .88rem; color: #1a1f36; }
    .cart-item .item-meta { font-size: .73rem; color: #6b7280; margin-top: 1px; }
    .qty-btn {
        width: 26px; height: 26px; padding: 0;
        border-radius: 50%; display: flex; align-items: center; justify-content: center;
        font-size: .8rem;
    }
    .pos-total {
        background: #f8fafc; border-top: 2px solid #edf2f7;
        padding: .85rem 1rem; flex-shrink: 0;
    }
    .scan-box {
        background: #fff; border-radius: 12px; padding: 1rem;
        box-shadow: 0 2px 12px rgba(0,0,0,.06);
    }
    .product-card {
        background: #fff; border-radius: 10px; padding: 12px;
        border: 1.5px solid #edf2f7; cursor: pointer; transition: all .18s;
    }
    .product-card:hover { border-color: #e74c3c; box-shadow: 0 3px 12px rgba(231,76,60,.15); transform: translateY(-1px); }
    .product-card .price { color: #e74c3c; font-weight: 700; font-size: 1rem; }
    #scanInput { font-size: 1rem; }
    .empty-cart { text-align: center; padding: 2.5rem 1rem; color: #94a3b8; }

    /* ── Mobile cart badge ── */
    .cart-fab {
        display: none;
        position: fixed; bottom: 1.2rem; right: 1.2rem;
        z-index: 1030;
        background: #e74c3c; color: #fff;
        border: none; border-radius: 50%;
        width: 56px; height: 56px;
        font-size: 1.4rem;
        box-shadow: 0 4px 18px rgba(231,76,60,.45);
        align-items: center; justify-content: center;
        cursor: pointer;
    }
    .cart-count-badge {
        position: absolute; top: 2px; right: 2px;
        background: #fff; color: #e74c3c;
        border-radius: 50%; width: 20px; height: 20px;
        font-size: .65rem; font-weight: 700;
        display: flex; align-items: center; justify-content: center;
    }

    /* ── Mobile: stack layout, slide-in cart ── */
    @media (max-width: 991.98px) {
        .pos-container {
            grid-template-columns: 1fr;
            height: auto;
        }
        .pos-right {
            position: fixed; inset: 0; z-index: 1060;
            border-radius: 0;
            transform: translateX(100%);
            transition: transform .28s cubic-bezier(.4,0,.2,1);
        }
        .pos-right.cart-open { transform: translateX(0); }
        .cart-fab { display: flex; }
        .pos-right .pos-header { padding-top: calc(.85rem + env(safe-area-inset-top)); }
    }
</style>
@endpush

@section('content')
<div class="pos-container">
    {{-- LEFT: Scan & Search --}}
    <div class="pos-left">
        <div class="scan-box">
            <div class="row g-2 align-items-center">
                <div class="col-md-8">
                    <label class="form-label fw-semibold mb-1">
                        <i class="bi bi-upc-scan me-1 text-danger"></i>Barcode Scan / Search
                    </label>
                    <input type="text" id="scanInput" class="form-control form-control-lg"
                        placeholder="Scan barcode / Article No / Product Name..."
                        autocomplete="off" autofocus>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold mb-1">Status</label>
                    <div id="searchInfo" class="text-muted small">Ready to scan...</div>
                </div>
            </div>
        </div>

        <div id="productGrid" class="row g-2">
            <div class="col-12 text-center text-muted py-5">
                <i class="bi bi-upc-scan" style="font-size:3rem;opacity:.3"></i>
                <p class="mt-2">Scan a barcode or type product name to search</p>
            </div>
        </div>
    </div>

    {{-- RIGHT: Cart & Billing --}}
    <div class="pos-right" id="posCart">
        <div class="pos-header">
            <div>
                <h6><i class="bi bi-cart3 me-2"></i>Cart / Bill</h6>
                <small style="opacity:.75;font-size:.75rem">Scan products to add to cart</small>
            </div>
            <button class="btn btn-sm btn-outline-light d-lg-none" onclick="closeCart()" style="border-radius:8px">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        {{-- Customer --}}
        <div class="p-3 border-bottom" style="background:#f8fafc">
            <div class="row g-2">
                <div class="col-7">
                    <input type="tel" id="customerMobile" class="form-control form-control-sm"
                        placeholder="Mobile Number" maxlength="10"
                        inputmode="numeric" pattern="[0-9]*" autocomplete="tel">
                </div>
                <div class="col-5">
                    <input type="text" id="customerName" class="form-control form-control-sm"
                        placeholder="Customer Name">
                </div>
            </div>
            <div id="customerInfo" class="mt-1" style="font-size:.75rem;color:#64748b"></div>
        </div>

        {{-- Cart Items --}}
        <div class="cart-items" id="cartItems">
            <div class="empty-cart" id="emptyCart">
                <i class="bi bi-cart-x" style="font-size:2.5rem;opacity:.3"></i>
                <p class="mt-2 mb-0">Cart is empty</p>
                <small>Scan products to add</small>
            </div>
        </div>

        {{-- Totals --}}
        <div class="pos-total">
            <div class="d-flex justify-content-between mb-1">
                <span class="text-muted">Subtotal:</span>
                <span id="subtotalDisplay">&#8377;0.00</span>
            </div>
            <div class="d-flex justify-content-between mb-1" id="gstRow" style="display:none!important">
                <span class="text-muted">GST:</span>
                <span id="gstDisplay" class="text-warning">&#8377;0.00</span>
            </div>
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-muted">Discount (&#8377;):</span>
                <div style="width:100px">
                    <input type="number" id="discountInput" class="form-control form-control-sm text-end"
                        value="0" min="0" step="0.01">
                </div>
            </div>
            <div class="d-flex justify-content-between fw-bold mb-3" style="font-size:1.2rem">
                <span>TOTAL:</span>
                <span id="totalDisplay" class="text-danger">&#8377;0.00</span>
            </div>

            <div class="row g-2 mb-2">
                <div class="col-6">
                    <select id="paymentMethod" class="form-select form-select-sm">
                        <option value="cash">Cash</option>
                        <option value="upi">UPI</option>
                        <option value="card">Card</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div class="col-6">
                    <input type="number" id="paidAmount" class="form-control form-control-sm"
                        placeholder="Paid Amount" min="0" step="0.01">
                </div>
            </div>
            <div id="changeDisplay" class="text-center mb-2 fw-semibold" style="display:none"></div>

            <div class="d-grid gap-2">
                <button id="completeSaleBtn" class="btn btn-danger btn-lg fw-bold" disabled>
                    <i class="bi bi-check-circle me-2"></i>Complete Sale
                </button>
                <button id="clearCartBtn" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-trash me-1"></i>Clear Cart
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Mobile cart FAB --}}
<button class="cart-fab d-lg-none" id="cartFab" onclick="openCart()">
    <i class="bi bi-cart3"></i>
    <span class="cart-count-badge" id="cartBadge" style="display:none">0</span>
</button>
@endsection

@push('scripts')
<script>
var cart = [];
var scanTimeout;
var totalAmount = 0;

var scanInput    = document.getElementById('scanInput');
var cartItemsEl  = document.getElementById('cartItems');
var emptyCart    = document.getElementById('emptyCart');
var productGrid  = document.getElementById('productGrid');

scanInput.addEventListener('input', function() {
    clearTimeout(scanTimeout);
    var val = this.value.trim();
    if (!val) {
        productGrid.innerHTML = '<div class="col-12 text-center text-muted py-5"><i class="bi bi-upc-scan" style="font-size:3rem;opacity:.3"></i><p class="mt-2">Scan a barcode or type product name to search</p></div>';
        return;
    }
    scanTimeout = setTimeout(function() { searchProduct(val, false); }, 400);
});

scanInput.addEventListener('keydown', function(e) {
    if (e.key === 'Enter') {
        clearTimeout(scanTimeout);
        var val = this.value.trim();
        if (val) searchProduct(val, true);
    }
});

function searchProduct(query, isEnter) {
    document.getElementById('searchInfo').innerHTML = '<span class="text-warning">Searching...</span>';
    fetch('/api/products/search-pos?q=' + encodeURIComponent(query))
        .then(function(res) { return res.json(); })
        .then(function(products) {
            if (!products.length) {
                document.getElementById('searchInfo').innerHTML = '<span class="text-danger">Not found</span>';
                productGrid.innerHTML = '<div class="col-12"><div class="alert alert-warning"><i class="bi bi-search me-2"></i>Product not found: <strong>' + query + '</strong></div></div>';
                return;
            }

            // Exact barcode or article no match + Enter or scanner → add to cart directly
            var exactMatch = products.find(function(p) {
                return p.barcode === query || p.sku === query || p.item_code === query;
            });
            if (exactMatch && (isEnter || query.length >= 8)) {
                addToCart(exactMatch);
                scanInput.value = '';
                document.getElementById('searchInfo').innerHTML = '<span class="text-success"><i class="bi bi-check-circle"></i> Added!</span>';
                setTimeout(function() { document.getElementById('searchInfo').textContent = 'Ready to scan...'; }, 1500);
                return;
            }

            // Single result + Enter → add directly
            if (products.length === 1 && isEnter) {
                addToCart(products[0]);
                scanInput.value = '';
                document.getElementById('searchInfo').innerHTML = '<span class="text-success"><i class="bi bi-check-circle"></i> Added!</span>';
                setTimeout(function() { document.getElementById('searchInfo').textContent = 'Ready to scan...'; }, 1500);
                return;
            }

            // Show product grid
            showProductCards(products);
            document.getElementById('searchInfo').innerHTML = '<span class="text-success">' + products.length + ' result(s)</span>';
        })
        .catch(function() {
            document.getElementById('searchInfo').innerHTML = '<span class="text-danger">Error</span>';
        });
}

function makeProductCard(product) {
    var stockColor = product.stock_quantity <= product.alert_quantity ? 'danger' : 'success';
    var detail = '';
    if (product.size) detail += 'Size: ' + product.size;
    if (product.size && product.color) detail += ' | ';
    if (product.color) detail += product.color;

    return '<div class="col-md-4">' +
        '<div class="product-card" onclick="addToCartFromGrid(' + product.id + ')">' +
        '<div class="fw-semibold">' + product.name + '</div>' +
        (detail ? '<div class="text-muted small">' + detail + '</div>' : '') +
        '<div class="text-muted small font-monospace">' + (product.item_code ? 'Item: ' + product.item_code + '  ' : '') + (product.barcode || '') + '</div>' +
        '<div class="d-flex justify-content-between align-items-center mt-2">' +
        '<div>' +
        '<span class="price">&#8377;' + parseFloat(product.selling_price).toFixed(0) + '</span>' +
        (product.mrp && parseFloat(product.mrp) > parseFloat(product.selling_price)
            ? ' <small><s class="text-muted">&#8377;' + parseFloat(product.mrp).toFixed(0) + '</s> <span class="text-success fw-semibold">' + Math.round((1 - parseFloat(product.selling_price)/parseFloat(product.mrp))*100) + '% OFF</span></small>'
            : '') +
        '</div>' +
        '<span class="badge bg-' + stockColor + '">Stock: ' + product.stock_quantity + '</span>' +
        '</div>' +
        '</div></div>';
}

function showProductCards(products) {
    window._searchProducts = {};
    var html = '';
    products.forEach(function(p) {
        window._searchProducts[p.id] = p;
        html += makeProductCard(p);
    });
    productGrid.innerHTML = html;
}

function addToCartFromGrid(id) {
    var product = window._searchProducts && window._searchProducts[id];
    if (product) {
        addToCart(product);
        scanInput.value = '';
        productGrid.innerHTML = '<div class="col-12 text-center text-muted py-5"><i class="bi bi-upc-scan" style="font-size:3rem;opacity:.3"></i><p class="mt-2">Scan a barcode or type product name to search</p></div>';
        document.getElementById('searchInfo').innerHTML = '<span class="text-success"><i class="bi bi-check-circle"></i> Added!</span>';
        setTimeout(function() { document.getElementById('searchInfo').textContent = 'Ready to scan...'; }, 1500);
    }
}

function addToCart(product) {
    if (product.stock_quantity <= 0) {
        alert(product.name + ' is out of stock!');
        return;
    }
    var existing = cart.find(function(i) { return i.product_id === product.id; });
    if (existing) {
        if (existing.quantity >= product.stock_quantity) {
            alert('Max available stock: ' + product.stock_quantity);
            return;
        }
        existing.quantity++;
        existing.total_price = existing.quantity * existing.unit_price - existing.discount;
    } else {
        cart.push({
            product_id:  product.id,
            name:        product.name,
            barcode:     product.barcode,
            size:        product.size,
            color:       product.color,
            mrp:         parseFloat(product.mrp || 0),
            quantity:    1,
            unit_price:  parseFloat(product.selling_price),
            tax_percent: parseFloat(product.tax_percent || 0),
            discount:    0,
            tax_amount:  parseFloat(product.selling_price) * parseFloat(product.tax_percent || 0) / 100,
            total_price: parseFloat(product.selling_price) * (1 + parseFloat(product.tax_percent || 0) / 100),
            max_stock:   product.stock_quantity
        });
    }
    renderCart();
}

function openCart() {
    document.getElementById('posCart').classList.add('cart-open');
    document.body.style.overflow = 'hidden';
}
function closeCart() {
    document.getElementById('posCart').classList.remove('cart-open');
    document.body.style.overflow = '';
}

function renderCart() {
    // Update FAB badge
    var badge = document.getElementById('cartBadge');
    var total = cart.reduce(function(s, i) { return s + i.quantity; }, 0);
    if (total > 0) { badge.textContent = total; badge.style.display = 'flex'; }
    else { badge.style.display = 'none'; }

    if (cart.length === 0) {
        cartItemsEl.innerHTML = '';
        cartItemsEl.appendChild(emptyCart);
        emptyCart.style.display = 'block';
        updateTotals();
        return;
    }
    emptyCart.style.display = 'none';
    var html = '';
    cart.forEach(function(item, idx) {
        var meta = '';
        if (item.size) meta += 'Size: ' + item.size;
        if (item.size && item.color) meta += ' | ';
        if (item.color) meta += item.color;

        var gstLabel = item.tax_percent > 0 ? ' | GST ' + item.tax_percent + '%' : '';
        var mrpLabel = (item.mrp && item.mrp > item.unit_price)
            ? ' <s class="text-muted">&#8377;' + item.mrp.toFixed(0) + '</s> <span class="text-success" style="font-size:.7rem">' + Math.round((1 - item.unit_price/item.mrp)*100) + '% OFF</span>'
            : '';
        html +=
            '<div class="cart-item">' +
            '<div class="d-flex justify-content-between align-items-start">' +
            '<div style="flex:1"><div class="item-name">' + item.name + '</div>' +
            (meta ? '<div class="item-meta">' + meta + ' | &#8377;' + item.unit_price.toFixed(2) + mrpLabel + gstLabel + '</div>' : '<div class="item-meta">&#8377;' + item.unit_price.toFixed(2) + mrpLabel + gstLabel + '</div>') +
            '</div>' +
            '<button class="btn btn-sm btn-link text-danger p-0 ms-2" onclick="removeFromCart(' + idx + ')"><i class="bi bi-x-lg"></i></button>' +
            '</div>' +
            '<div class="d-flex align-items-center justify-content-between mt-2">' +
            '<div class="d-flex align-items-center gap-2">' +
            '<button class="btn btn-outline-secondary qty-btn" onclick="changeQty(' + idx + ',-1)"><i class="bi bi-dash"></i></button>' +
            '<span class="fw-bold">' + item.quantity + '</span>' +
            '<button class="btn btn-outline-secondary qty-btn" onclick="changeQty(' + idx + ',1)"><i class="bi bi-plus"></i></button>' +
            '</div>' +
            '<div class="fw-bold text-danger">&#8377;' + item.total_price.toFixed(2) + '</div>' +
            '</div></div>';
    });
    cartItemsEl.innerHTML = html;
    updateTotals();
}

function changeQty(idx, delta) {
    var item = cart[idx];
    var newQty = item.quantity + delta;
    if (newQty <= 0) { removeFromCart(idx); return; }
    if (newQty > item.max_stock) { alert('Max available stock: ' + item.max_stock); return; }
    item.quantity    = newQty;
    var base         = newQty * item.unit_price - item.discount;
    item.tax_amount  = parseFloat((base * item.tax_percent / 100).toFixed(2));
    item.total_price = base + item.tax_amount;
    renderCart();
}

function removeFromCart(idx) {
    cart.splice(idx, 1);
    renderCart();
}

function updateTotals() {
    var baseTotal = cart.reduce(function(s, i) { return s + (i.quantity * i.unit_price - i.discount); }, 0);
    var totalGst  = cart.reduce(function(s, i) { return s + i.tax_amount; }, 0);
    var discount  = parseFloat(document.getElementById('discountInput').value) || 0;
    totalAmount   = baseTotal + totalGst - discount;

    document.getElementById('subtotalDisplay').innerHTML = '&#8377;' + baseTotal.toFixed(2);

    var gstRow = document.getElementById('gstRow');
    if (totalGst > 0) {
        gstRow.style.display = 'flex';
        document.getElementById('gstDisplay').innerHTML = '&#8377;' + totalGst.toFixed(2);
    } else {
        gstRow.style.display = 'none';
    }

    document.getElementById('totalDisplay').innerHTML = '&#8377;' + totalAmount.toFixed(2);
    document.getElementById('paidAmount').value = totalAmount.toFixed(2);
    updateChange();
    document.getElementById('completeSaleBtn').disabled = cart.length === 0;
}

function updateChange() {
    var paid = parseFloat(document.getElementById('paidAmount').value) || 0;
    var change = paid - totalAmount;
    var el = document.getElementById('changeDisplay');
    if (paid > 0 && change >= 0) {
        el.style.display = 'block';
        el.innerHTML = 'Change to return: <strong>&#8377;' + change.toFixed(2) + '</strong>';
        el.className = 'text-center mb-2 fw-semibold ' + (change > 0 ? 'text-success' : 'text-muted');
    } else {
        el.style.display = 'none';
    }
}

document.getElementById('discountInput').addEventListener('input', updateTotals);
document.getElementById('paidAmount').addEventListener('input', updateChange);

document.getElementById('customerMobile').addEventListener('input', function() {
    var cleaned = this.value.replace(/\D/g, '');
    if (this.value !== cleaned) this.value = cleaned;
});

document.getElementById('customerMobile').addEventListener('blur', function() {
    var mobile = this.value.trim();
    if (mobile.length > 0 && mobile.length < 10) {
        this.classList.add('is-invalid');
        this.title = 'Mobile number must be 10 digits';
        return;
    }
    this.classList.remove('is-invalid');
    if (mobile.length >= 10) {
        fetch('/api/customers/search?q=' + mobile)
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.length > 0) {
                    var c = data[0];
                    document.getElementById('customerName').value = c.name;
                    document.getElementById('customerInfo').textContent =
                        'Customer found: ' + c.name + ' | Visits: ' + c.visit_count + ' | Total: &#8377;' + parseFloat(c.total_purchase).toFixed(0);
                }
            });
    }
});

document.getElementById('clearCartBtn').addEventListener('click', function() {
    if (cart.length && confirm('Clear all items from cart?')) {
        cart = [];
        renderCart();
        scanInput.focus();
    }
});

document.getElementById('completeSaleBtn').addEventListener('click', function() {
    var mobileField = document.getElementById('customerMobile');
    var mobileVal   = mobileField.value.trim();
    if (mobileVal.length > 0 && !/^\d{10}$/.test(mobileVal)) {
        mobileField.classList.add('is-invalid');
        mobileField.focus();
        alert('Mobile number must be exactly 10 digits.');
        return;
    }
    var paid = parseFloat(document.getElementById('paidAmount').value) || 0;
    if (paid < totalAmount) { alert('Paid amount is less than total!'); return; }

    this.disabled = true;
    this.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Processing...';

    var payload = {
        items: cart.map(function(i) {
            return { product_id: i.product_id, quantity: i.quantity, unit_price: i.unit_price, discount: i.discount };
        }),
        customer_mobile: document.getElementById('customerMobile').value,
        customer_name:   document.getElementById('customerName').value,
        discount_amount: parseFloat(document.getElementById('discountInput').value) || 0,
        paid_amount:     paid,
        payment_method:  document.getElementById('paymentMethod').value
    };

    var self = this;
    fetch('/sales', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify(payload)
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) {
            window.open(data.print_url, '_blank');
            cart = [];
            renderCart();
            document.getElementById('customerMobile').value = '';
            document.getElementById('customerName').value   = '';
            document.getElementById('customerInfo').textContent = '';
            document.getElementById('discountInput').value  = 0;
            scanInput.focus();
        } else {
            alert('Error: ' + (data.message || 'Something went wrong'));
        }
    })
    .catch(function() { alert('Network error!'); })
    .finally(function() {
        self.disabled = false;
        self.innerHTML = '<i class="bi bi-check-circle me-2"></i>Complete Sale';
    });
});

scanInput.focus();
</script>
@endpush

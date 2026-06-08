@extends('layouts.app')
@section('title','New Purchase Order')
@section('page-title','New Purchase Order')
@push('styles')
<style>
.items-table th,.items-table td{padding:.5rem .6rem;vertical-align:middle}
.remove-row{cursor:pointer}
</style>
@endpush
@section('content')
<form action="{{ route('purchase-orders.store') }}" method="POST" id="poForm">
@csrf
<div class="row g-3">
<div class="col-md-8">
<div class="card">
  <div class="card-header py-3">Order Items</div>
  <div class="card-body">
    <table class="table items-table" id="itemsTable">
      <thead class="table-light"><tr><th>Product</th><th>Qty</th><th>Unit Price</th><th>Tax%</th><th>Total</th><th></th></tr></thead>
      <tbody id="itemsBody">
        <tr class="item-row">
          <td><select name="items[0][product_id]" class="form-select form-select-sm product-select" required>
            <option value="">Select Product</option>
            @foreach($products as $p)<option value="{{ $p->id }}" data-price="{{ $p->purchase_price }}">{{ $p->name }} ({{ $p->sku }}{{ $p->size?' Sz:'.$p->size:'' }})</option>
            @endforeach
          </select></td>
          <td><input type="number" name="items[0][quantity]" class="form-control form-control-sm qty-input" value="1" min="1" required style="width:70px"></td>
          <td><input type="number" name="items[0][unit_price]" class="form-control form-control-sm price-input" value="0" min="0" step="0.01" required style="width:100px"></td>
          <td><input type="number" name="items[0][tax_percent]" class="form-control form-control-sm tax-input" value="0" min="0" max="100" step="0.01" style="width:70px"></td>
          <td class="line-total fw-bold">&#8377;0.00</td>
          <td><i class="bi bi-trash text-danger remove-row" onclick="removeRow(this)"></i></td>
        </tr>
      </tbody>
    </table>
    <button type="button" class="btn btn-sm btn-outline-primary" onclick="addRow()"><i class="bi bi-plus"></i> Add Row</button>
  </div>
</div>
</div>
<div class="col-md-4">
<div class="card mb-3">
  <div class="card-header py-3">Order Details</div>
  <div class="card-body">
    <div class="mb-3"><label class="form-label fw-semibold">Supplier *</label>
      <select name="supplier_id" class="form-select" required>
        <option value="">Select Supplier</option>
        @foreach($suppliers as $s)<option value="{{ $s->id }}">{{ $s->name }}</option>@endforeach
      </select></div>
    <div class="mb-3"><label class="form-label fw-semibold">Order Date *</label>
      <input type="date" name="order_date" class="form-control" value="{{ today()->format('Y-m-d') }}" required></div>
    <div class="mb-3"><label class="form-label fw-semibold">Expected Date</label>
      <input type="date" name="expected_date" class="form-control"></div>
    <div class="mb-3"><label class="form-label fw-semibold">Notes</label>
      <textarea name="notes" class="form-control" rows="2"></textarea></div>
  </div>
</div>
<div class="card">
  <div class="card-body">
    <div class="d-flex justify-content-between mb-1"><span>Subtotal:</span><span id="subtotal">&#8377;0.00</span></div>
    <div class="d-flex justify-content-between mb-1"><span>Tax:</span><span id="taxTotal">&#8377;0.00</span></div>
    <hr>
    <div class="d-flex justify-content-between fw-bold fs-5"><span>Total:</span><span id="grandTotal" class="text-danger">&#8377;0.00</span></div>
    <div class="d-grid mt-3"><button type="submit" class="btn btn-danger btn-lg"><i class="bi bi-save me-2"></i>Create Purchase Order</button></div>
    <a href="{{ route('purchase-orders.index') }}" class="btn btn-outline-secondary w-100 mt-2">Cancel</a>
  </div>
</div>
</div>
</div>
</form>
@endsection
@push('scripts')
<script>
var rowCount = 1;
var products = {};
@foreach($products as $p)
products[{{ $p->id }}] = { price: {{ (float)$p->purchase_price }} };
@endforeach

function addRow(){
  var t=document.getElementById('itemsBody');
  var r=t.querySelector('.item-row').cloneNode(true);
  var selects=r.querySelectorAll('select,input');
  selects.forEach(function(el){
    el.name=el.name.replace(/\[\d+\]/,'['+rowCount+']');
    if(el.type==='number'&&el.classList.contains('qty-input'))el.value=1;
    if(el.type==='number'&&el.classList.contains('price-input'))el.value=0;
    if(el.type==='number'&&el.classList.contains('tax-input'))el.value=0;
  });
  r.querySelector('.line-total').textContent='₹0.00';
  t.appendChild(r);
  attachEvents(r);
  rowCount++;
  calcTotals();
}

function removeRow(btn){
  var rows=document.querySelectorAll('.item-row');
  if(rows.length>1){btn.closest('tr').remove();calcTotals();}
}

function attachEvents(row){
  row.querySelectorAll('.product-select,.qty-input,.price-input,.tax-input').forEach(function(el){
    el.addEventListener('change',function(){updateRow(row);});
    el.addEventListener('input',function(){updateRow(row);});
  });
  row.querySelector('.product-select').addEventListener('change',function(){
    var pid=this.value;
    if(pid&&products[pid]){row.querySelector('.price-input').value=products[pid].price;}
    updateRow(row);
  });
}

function updateRow(row){
  var qty=parseFloat(row.querySelector('.qty-input').value)||0;
  var price=parseFloat(row.querySelector('.price-input').value)||0;
  var tax=parseFloat(row.querySelector('.tax-input').value)||0;
  var line=qty*price;
  row.querySelector('.line-total').textContent='₹'+line.toFixed(2);
  calcTotals();
}

function calcTotals(){
  var sub=0,taxSum=0;
  document.querySelectorAll('.item-row').forEach(function(row){
    var qty=parseFloat(row.querySelector('.qty-input').value)||0;
    var price=parseFloat(row.querySelector('.price-input').value)||0;
    var tax=parseFloat(row.querySelector('.tax-input').value)||0;
    var line=qty*price;
    sub+=line;taxSum+=line*tax/100;
  });
  document.getElementById('subtotal').textContent='₹'+sub.toFixed(2);
  document.getElementById('taxTotal').textContent='₹'+taxSum.toFixed(2);
  document.getElementById('grandTotal').textContent='₹'+(sub+taxSum).toFixed(2);
}

document.querySelectorAll('.item-row').forEach(function(r){attachEvents(r);});
</script>
@endpush

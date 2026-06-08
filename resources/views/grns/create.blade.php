@extends('layouts.app')
@section('title','New GRN')
@section('page-title','New Goods Received Note')
@section('content')
<form action="{{ route('grns.store') }}" method="POST">
@csrf
<div class="row g-3">
<div class="col-md-8">
<div class="card">
  <div class="card-header py-3">Items Received</div>
  <div class="card-body">
    <table class="table" id="grnTable">
      <thead class="table-light"><tr><th>Product</th><th>Qty</th><th>Unit Price</th><th>Tax%</th><th>Total</th><th></th></tr></thead>
      <tbody id="grnBody">
        @if($purchaseOrder)
          @foreach($purchaseOrder->items as $i=>$item)
          <tr class="grn-row">
            <td>
              <select name="items[{{ $i }}][product_id]" class="form-select form-select-sm grn-product" required>
                @foreach($products as $p)
                <option value="{{ $p->id }}" {{ $p->id==$item->product_id?'selected':'' }}>{{ $p->name }} ({{ $p->sku }})</option>
                @endforeach
              </select>
            </td>
            <td><input type="number" name="items[{{ $i }}][quantity]" class="form-control form-control-sm grn-qty" value="{{ $item->ordered_quantity - $item->received_quantity }}" min="1" style="width:70px" required></td>
            <td><input type="number" name="items[{{ $i }}][unit_price]" class="form-control form-control-sm grn-price" value="{{ $item->unit_price }}" min="0" step="0.01" style="width:100px" required></td>
            <td><input type="number" name="items[{{ $i }}][tax_percent]" class="form-control form-control-sm grn-tax" value="{{ $item->tax_percent }}" min="0" max="100" step="0.01" style="width:70px"></td>
            <td class="grn-line fw-bold">&#8377;{{ number_format(($item->ordered_quantity - $item->received_quantity) * $item->unit_price, 2) }}</td>
            <td><i class="bi bi-trash text-danger" style="cursor:pointer" onclick="removeGrnRow(this)"></i></td>
          </tr>
          @endforeach
        @else
          <tr class="grn-row">
            <td><select name="items[0][product_id]" class="form-select form-select-sm grn-product" required><option value="">Select Product</option>@foreach($products as $p)<option value="{{ $p->id }}" data-price="{{ $p->purchase_price }}">{{ $p->name }} ({{ $p->sku }})</option>@endforeach</select></td>
            <td><input type="number" name="items[0][quantity]" class="form-control form-control-sm grn-qty" value="1" min="1" style="width:70px" required></td>
            <td><input type="number" name="items[0][unit_price]" class="form-control form-control-sm grn-price" value="0" step="0.01" style="width:100px" required></td>
            <td><input type="number" name="items[0][tax_percent]" class="form-control form-control-sm grn-tax" value="0" style="width:70px"></td>
            <td class="grn-line fw-bold">&#8377;0.00</td>
            <td><i class="bi bi-trash text-danger" style="cursor:pointer" onclick="removeGrnRow(this)"></i></td>
          </tr>
        @endif
      </tbody>
    </table>
    <button type="button" class="btn btn-sm btn-outline-primary" onclick="addGrnRow()"><i class="bi bi-plus"></i> Add Row</button>
  </div>
</div>
</div>
<div class="col-md-4">
<div class="card mb-3">
  <div class="card-header py-3">Receipt Details</div>
  <div class="card-body">
    @if($purchaseOrder)<input type="hidden" name="purchase_order_id" value="{{ $purchaseOrder->id }}"><div class="alert alert-info py-2 small"><i class="bi bi-link me-1"></i>Linked to PO: {{ $purchaseOrder->po_number }}</div>@endif
    <div class="mb-2"><label class="form-label fw-semibold">Supplier *</label>
      <select name="supplier_id" class="form-select" required>
        <option value="">Select</option>
        @foreach($suppliers as $s)<option value="{{ $s->id }}" {{ ($purchaseOrder && $purchaseOrder->supplier_id==$s->id)?'selected':'' }}>{{ $s->name }}</option>@endforeach
      </select></div>
    <div class="mb-2"><label class="form-label fw-semibold">Received Date *</label><input type="date" name="received_date" class="form-control" value="{{ today()->format('Y-m-d') }}" required></div>
    <div class="mb-2"><label class="form-label fw-semibold">Supplier Invoice No</label><input type="text" name="invoice_number" class="form-control" placeholder="Supplier's bill number"></div>
    <div class="mb-2"><label class="form-label fw-semibold">Notes</label><textarea name="notes" class="form-control" rows="2"></textarea></div>
  </div>
</div>
<div class="card">
  <div class="card-body">
    <div class="d-flex justify-content-between mb-1"><span>Subtotal:</span><span id="grnSubtotal">&#8377;0.00</span></div>
    <div class="d-flex justify-content-between mb-2"><span>Tax:</span><span id="grnTax">&#8377;0.00</span></div>
    <div class="d-flex justify-content-between fw-bold fs-6 mb-3"><span>Total:</span><span id="grnTotal" class="text-danger">&#8377;0.00</span></div>
    <label class="form-label fw-semibold">Amount Paid Now</label>
    <input type="number" name="paid_amount" id="grnPaid" class="form-control mb-1" value="0" min="0" step="0.01">
    <div class="text-muted small" id="grnPayStatus">Payment Status: Pending</div>
    <div class="d-grid mt-3"><button type="submit" class="btn btn-danger btn-lg"><i class="bi bi-check-circle me-2"></i>Receive & Update Stock</button></div>
    <a href="{{ route('grns.index') }}" class="btn btn-outline-secondary w-100 mt-2">Cancel</a>
  </div>
</div>
</div>
</div>
</form>
@endsection
@push('scripts')
<script>
var grnRowCount = {{ $purchaseOrder ? $purchaseOrder->items->count() : 1 }};
var allProducts = {};
@foreach($products as $p) allProducts[{{ $p->id }}]={{ (float)$p->purchase_price }}; @endforeach

document.querySelectorAll('.grn-row').forEach(function(r){attachGrnEvents(r);});
calcGrnTotals();

function addGrnRow(){
  var t=document.getElementById('grnBody');
  var opts='<option value="">Select Product</option>';
  for(var id in allProducts){opts+='<option value="'+id+'" data-price="'+allProducts[id]+'">Product '+id+'</option>';}
  var row=document.createElement('tr');row.className='grn-row';
  row.innerHTML='<td><select name="items['+grnRowCount+'][product_id]" class="form-select form-select-sm grn-product" required><option value="">Select Product</option>'+
    @json($products->map(fn($p)=>['id'=>$p->id,'label'=>$p->name.' ('.$p->sku.')','price'=>(float)$p->purchase_price]))->reduce(function($c,$p){ return $c.'<option value="'.$p['id'].'" data-price="'.$p['price'].'">'.$p['label'].'</option>'; },'') +
    '</select></td><td><input type="number" name="items['+grnRowCount+'][quantity]" class="form-control form-control-sm grn-qty" value="1" min="1" style="width:70px" required></td><td><input type="number" name="items['+grnRowCount+'][unit_price]" class="form-control form-control-sm grn-price" value="0" step="0.01" style="width:100px" required></td><td><input type="number" name="items['+grnRowCount+'][tax_percent]" class="form-control form-control-sm grn-tax" value="0" style="width:70px"></td><td class="grn-line fw-bold">&#8377;0.00</td><td><i class="bi bi-trash text-danger" style="cursor:pointer" onclick="removeGrnRow(this)"></i></td>';
  t.appendChild(row);attachGrnEvents(row);grnRowCount++;calcGrnTotals();
}

function removeGrnRow(btn){
  var rows=document.querySelectorAll('.grn-row');
  if(rows.length>1){btn.closest('tr').remove();calcGrnTotals();}
}

function attachGrnEvents(row){
  row.querySelectorAll('input,select').forEach(function(el){
    el.addEventListener('change',function(){updateGrnRow(row);});
    el.addEventListener('input',function(){updateGrnRow(row);});
  });
  var ps=row.querySelector('.grn-product');
  if(ps)ps.addEventListener('change',function(){
    var opt=this.options[this.selectedIndex];
    var price=opt.dataset.price||allProducts[this.value]||0;
    row.querySelector('.grn-price').value=price;
    updateGrnRow(row);
  });
}

function updateGrnRow(row){
  var qty=parseFloat(row.querySelector('.grn-qty').value)||0;
  var price=parseFloat(row.querySelector('.grn-price').value)||0;
  row.querySelector('.grn-line').textContent='₹'+(qty*price).toFixed(2);
  calcGrnTotals();
}

function calcGrnTotals(){
  var sub=0,tax=0;
  document.querySelectorAll('.grn-row').forEach(function(r){
    var qty=parseFloat(r.querySelector('.grn-qty').value)||0;
    var price=parseFloat(r.querySelector('.grn-price').value)||0;
    var taxPct=parseFloat(r.querySelector('.grn-tax').value)||0;
    var line=qty*price;sub+=line;tax+=line*taxPct/100;
  });
  var total=sub+tax;
  document.getElementById('grnSubtotal').textContent='₹'+sub.toFixed(2);
  document.getElementById('grnTax').textContent='₹'+tax.toFixed(2);
  document.getElementById('grnTotal').textContent='₹'+total.toFixed(2);
  var paid=parseFloat(document.getElementById('grnPaid').value)||0;
  var status=paid>=total?'Paid':(paid>0?'Partial':'Pending');
  document.getElementById('grnPayStatus').textContent='Payment Status: '+status;
}

document.getElementById('grnPaid').addEventListener('input',calcGrnTotals);
</script>
@endpush

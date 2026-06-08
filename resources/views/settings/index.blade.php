@extends('layouts.app')
@section('title','Settings')
@section('page-title','System Settings')
@section('content')
<form action="{{ route('settings.update') }}" method="POST">
@csrf
<div class="row">
<div class="col-md-3">
  <div class="list-group" id="settingsTabs" role="tablist">
    <a class="list-group-item list-group-item-action active" id="t1" data-bs-toggle="list" href="#business">
      <i class="bi bi-shop me-2"></i>Business Info</a>
    <a class="list-group-item list-group-item-action" id="t2" data-bs-toggle="list" href="#pos">
      <i class="bi bi-cart me-2"></i>POS Settings</a>
    <a class="list-group-item list-group-item-action" id="t3" data-bs-toggle="list" href="#receipt">
      <i class="bi bi-receipt me-2"></i>Receipt</a>
    <a class="list-group-item list-group-item-action" id="t4" data-bs-toggle="list" href="#barcode">
      <i class="bi bi-upc me-2"></i>Barcode Label</a>
    <a class="list-group-item list-group-item-action" id="t5" data-bs-toggle="list" href="#loyalty">
      <i class="bi bi-gift me-2"></i>Loyalty Points</a>
  </div>
</div>
<div class="col-md-9">
  <div class="tab-content">
    <div class="tab-pane fade show active" id="business">
      <div class="card">
        <div class="card-header py-3">Business Information</div>
        <div class="card-body">
          <div class="mb-3"><label class="form-label fw-semibold">Business Name</label><input type="text" name="business_name" class="form-control" value="{{ old('business_name', $settingValues->get('business_name','')) }}"></div>
          <div class="mb-3"><label class="form-label fw-semibold">Address</label><textarea name="business_address" class="form-control" rows="2">{{ old('business_address', $settingValues->get('business_address','')) }}</textarea></div>
          <div class="mb-3"><label class="form-label fw-semibold">Phone</label><input type="text" name="business_phone" class="form-control" value="{{ old('business_phone', $settingValues->get('business_phone','')) }}"></div>
          <div class="mb-3"><label class="form-label fw-semibold">GST Number</label><input type="text" name="business_gst" class="form-control" value="{{ old('business_gst', $settingValues->get('business_gst','')) }}"></div>
          <div class="mb-3"><label class="form-label fw-semibold">Currency Symbol</label><input type="text" name="currency_symbol" class="form-control" value="{{ old('currency_symbol', $settingValues->get('currency_symbol','₹')) }}" style="max-width:100px"></div>
        </div>
      </div>
    </div>
    <div class="tab-pane fade" id="pos">
      <div class="card">
        <div class="card-header py-3">POS Settings</div>
        <div class="card-body">
          <div class="mb-3"><label class="form-label fw-semibold">Default Tax/GST %</label><div class="input-group" style="max-width:200px"><input type="number" name="tax_percent" class="form-control" value="{{ old('tax_percent', $settingValues->get('tax_percent','0')) }}" min="0" max="100" step="0.01"><span class="input-group-text">%</span></div><small class="text-muted">Applied on new products by default</small></div>
          <div class="mb-3"><label class="form-label fw-semibold">Low Stock Alert Quantity</label><input type="number" name="low_stock_alert_qty" class="form-control" value="{{ old('low_stock_alert_qty', $settingValues->get('low_stock_alert_qty','5')) }}" min="0" style="max-width:150px"></div>
        </div>
      </div>
    </div>
    <div class="tab-pane fade" id="receipt">
      <div class="card">
        <div class="card-header py-3">Receipt Settings</div>
        <div class="card-body">
          <div class="mb-3"><label class="form-label fw-semibold">Receipt Footer Message</label><input type="text" name="receipt_footer" class="form-control" value="{{ old('receipt_footer', $settingValues->get('receipt_footer','')) }}" placeholder="Thank You! Come Again!"></div>
          <div class="mb-3"><label class="form-label fw-semibold">Exchange Policy</label><textarea name="receipt_exchange_policy" class="form-control" rows="2">{{ old('receipt_exchange_policy', $settingValues->get('receipt_exchange_policy','')) }}</textarea></div>
        </div>
      </div>
    </div>
    <div class="tab-pane fade" id="barcode">
      <div class="card">
        <div class="card-header py-3">Barcode Label Settings</div>
        <div class="card-body">
          <div class="row g-3">
            <div class="col-md-4"><label class="form-label fw-semibold">Label Width (mm)</label><input type="number" name="barcode_label_width" class="form-control" value="{{ old('barcode_label_width', $settingValues->get('barcode_label_width','60')) }}" min="30" max="100"></div>
            <div class="col-md-4"><label class="form-label fw-semibold">Label Height (mm)</label><input type="number" name="barcode_label_height" class="form-control" value="{{ old('barcode_label_height', $settingValues->get('barcode_label_height','30')) }}" min="20" max="60"></div>
          </div>
          <small class="text-muted mt-2 d-block">Current: {{ $settingValues->get('barcode_label_width','60') }}mm × {{ $settingValues->get('barcode_label_height','30') }}mm</small>
        </div>
      </div>
    </div>
    <div class="tab-pane fade" id="loyalty">
      <div class="card">
        <div class="card-header py-3">Loyalty Points Settings</div>
        <div class="card-body">
          <div class="mb-3"><label class="form-label fw-semibold">Points Earned per ₹1 Spent</label><input type="number" name="loyalty_points_per_rupee" class="form-control" value="{{ old('loyalty_points_per_rupee', $settingValues->get('loyalty_points_per_rupee','1')) }}" min="0" step="0.1" style="max-width:150px"><small class="text-muted">e.g. 1 = earn 1 point per ₹1 spent</small></div>
          <div class="mb-3"><label class="form-label fw-semibold">Points needed for ₹1 Discount</label><input type="number" name="points_to_rupee" class="form-control" value="{{ old('points_to_rupee', $settingValues->get('points_to_rupee','100')) }}" min="1" style="max-width:150px"><small class="text-muted">e.g. 100 = 100 points = ₹1 discount</small></div>
        </div>
      </div>
    </div>
  </div>
  <div class="d-flex justify-content-end mt-3">
    <button type="submit" class="btn btn-danger btn-lg"><i class="bi bi-save me-2"></i>Save Settings</button>
  </div>
</div>
</div>
</form>
@endsection

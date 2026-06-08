@extends('layouts.app')
@section('title','Purchase Orders')
@section('page-title','Purchase Orders')
@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center py-3">
        <span><i class="bi bi-file-earmark-text me-2"></i>Purchase Orders</span>
        <a href="{{ route('purchase-orders.create') }}" class="btn btn-sm btn-danger"><i class="bi bi-plus"></i> New PO</a>
    </div>
    <div class="card-body border-bottom">
        <form method="GET" class="row g-2">
            <div class="col-md-3">
                <select name="supplier_id" class="form-select form-select-sm">
                    <option value="">All Suppliers</option>
                    @foreach($suppliers as $s)<option value="{{ $s->id }}" {{ request('supplier_id')==$s->id?'selected':'' }}>{{ $s->name }}</option>@endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="status" class="form-select form-select-sm">
                    <option value="">All Status</option>
                    <option value="draft" {{ request('status')=='draft'?'selected':'' }}>Draft</option>
                    <option value="ordered" {{ request('status')=='ordered'?'selected':'' }}>Ordered</option>
                    <option value="partially_received" {{ request('status')=='partially_received'?'selected':'' }}>Partially Received</option>
                    <option value="received" {{ request('status')=='received'?'selected':'' }}>Received</option>
                    <option value="cancelled" {{ request('status')=='cancelled'?'selected':'' }}>Cancelled</option>
                </select>
            </div>
            <div class="col-md-2"><input type="date" name="date" class="form-control form-control-sm" value="{{ request('date') }}"></div>
            <div class="col-md-2"><button class="btn btn-sm btn-outline-primary">Filter</button> <a href="{{ route('purchase-orders.index') }}" class="btn btn-sm btn-outline-secondary ms-1">Reset</a></div>
        </form>
    </div>
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead class="table-light"><tr><th>PO Number</th><th>Supplier</th><th>Date</th><th>Total</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
                @forelse($orders as $po)
                <tr>
                    <td class="fw-semibold">{{ $po->po_number }}</td>
                    <td>{{ $po->supplier->name }}</td>
                    <td>{{ $po->order_date->format('d M Y') }}</td>
                    <td>&#8377;{{ number_format($po->total_amount,0) }}</td>
                    <td>
                        @php $colors=['draft'=>'secondary','ordered'=>'primary','partially_received'=>'warning','received'=>'success','cancelled'=>'danger'] @endphp
                        <span class="badge bg-{{ $colors[$po->status]??'secondary' }}">{{ ucfirst(str_replace('_',' ',$po->status)) }}</span>
                    </td>
                    <td>
                        <a href="{{ route('purchase-orders.show',$po) }}" class="btn btn-sm btn-outline-info"><i class="bi bi-eye"></i></a>
                        @if(in_array($po->status,['ordered','partially_received']))
                        <a href="{{ route('grns.create',['po_id'=>$po->id]) }}" class="btn btn-sm btn-outline-success" title="Receive Stock"><i class="bi bi-truck"></i></a>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center text-muted py-4">No purchase orders found</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($orders->hasPages())<div class="card-footer">{{ $orders->links() }}</div>@endif
</div>
@endsection

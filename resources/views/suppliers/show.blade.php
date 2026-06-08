@extends('layouts.app')
@section('title', 'Supplier: ' . $supplier->name)
@section('page-title', 'Supplier Details')

@section('content')
<div class="row g-4">
    {{-- Left Column: Info + Stats --}}
    <div class="col-md-4">
        <div class="card mb-3">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <span><i class="bi bi-person-vcard me-2"></i>Supplier Info</span>
                <a href="{{ route('suppliers.edit', $supplier) }}" class="btn btn-sm btn-outline-warning">
                    <i class="bi bi-pencil"></i> Edit
                </a>
            </div>
            <div class="card-body">
                <h5 class="fw-bold mb-1">{{ $supplier->name }}</h5>
                @if($supplier->company)
                <p class="text-muted mb-3">{{ $supplier->company }}</p>
                @endif
                <ul class="list-unstyled mb-0">
                    @if($supplier->phone)
                    <li class="mb-2">
                        <i class="bi bi-telephone text-muted me-2"></i>
                        <a href="tel:{{ $supplier->phone }}" class="text-decoration-none">{{ $supplier->phone }}</a>
                    </li>
                    @endif
                    @if($supplier->email)
                    <li class="mb-2">
                        <i class="bi bi-envelope text-muted me-2"></i>
                        <a href="mailto:{{ $supplier->email }}" class="text-decoration-none">{{ $supplier->email }}</a>
                    </li>
                    @endif
                    @if($supplier->gst_number)
                    <li class="mb-2">
                        <i class="bi bi-file-earmark-text text-muted me-2"></i>
                        GST: <span class="fw-semibold">{{ $supplier->gst_number }}</span>
                    </li>
                    @endif
                    @if($supplier->address)
                    <li class="mb-2">
                        <i class="bi bi-geo-alt text-muted me-2"></i>
                        {{ $supplier->address }}
                    </li>
                    @endif
                    <li>
                        <i class="bi bi-circle-fill text-muted me-2" style="font-size:.5rem;vertical-align:middle"></i>
                        Status:
                        <span class="badge bg-{{ $supplier->is_active ? 'success' : 'danger' }} ms-1">
                            {{ $supplier->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </li>
                </ul>
            </div>
        </div>

        {{-- Financial Stats --}}
        @php
            $outstanding = $supplier->total_payable - $supplier->total_paid;
        @endphp
        <div class="row g-2">
            <div class="col-12">
                <div class="stat-card" style="background:linear-gradient(135deg,#3498db,#2980b9)">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="small mb-1" style="opacity:.85">Total Payable</div>
                            <div class="fs-5 fw-bold">&#8377;{{ number_format($supplier->total_payable, 2) }}</div>
                        </div>
                        <i class="bi bi-arrow-up-circle stat-icon"></i>
                    </div>
                </div>
            </div>
            <div class="col-12">
                <div class="stat-card" style="background:linear-gradient(135deg,#27ae60,#229954)">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="small mb-1" style="opacity:.85">Total Paid</div>
                            <div class="fs-5 fw-bold">&#8377;{{ number_format($supplier->total_paid, 2) }}</div>
                        </div>
                        <i class="bi bi-check-circle stat-icon"></i>
                    </div>
                </div>
            </div>
            <div class="col-12">
                <div class="stat-card" style="background:linear-gradient(135deg,{{ $outstanding > 0 ? '#e74c3c,#c0392b' : '#6c757d,#495057' }})">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="small mb-1" style="opacity:.85">Outstanding</div>
                            <div class="fs-5 fw-bold">&#8377;{{ number_format($outstanding, 2) }}</div>
                        </div>
                        <i class="bi bi-exclamation-circle stat-icon"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Right Column: POs + GRNs --}}
    <div class="col-md-8">
        <div class="card mb-3">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <span><i class="bi bi-file-earmark-check me-2"></i>Recent Purchase Orders</span>
                <a href="{{ route('purchase-orders.create') }}?supplier_id={{ $supplier->id }}" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-plus"></i> New PO
                </a>
            </div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>PO Number</th>
                            <th>Date</th>
                            <th class="text-end">Total</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($supplier->purchaseOrders->take(10) as $po)
                        <tr>
                            <td class="fw-semibold">{{ $po->po_number }}</td>
                            <td class="text-muted small">{{ $po->order_date->format('d M Y') }}</td>
                            <td class="text-end">&#8377;{{ number_format($po->grand_total, 2) }}</td>
                            <td>
                                @php
                                    $poColors = ['draft'=>'secondary','ordered'=>'primary','partially_received'=>'warning','received'=>'success','cancelled'=>'danger'];
                                @endphp
                                <span class="badge bg-{{ $poColors[$po->status] ?? 'secondary' }}">
                                    {{ ucfirst(str_replace('_', ' ', $po->status)) }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('purchase-orders.show', $po) }}" class="btn btn-sm btn-outline-info">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center text-muted py-3">No purchase orders yet</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card-header py-3">
                <i class="bi bi-box-arrow-in-down me-2"></i>Recent GRNs
            </div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>GRN Number</th>
                            <th>Date</th>
                            <th>Invoice No.</th>
                            <th class="text-end">Total</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($supplier->grns->take(10) as $grn)
                        <tr>
                            <td class="fw-semibold">{{ $grn->grn_number }}</td>
                            <td class="text-muted small">{{ $grn->received_date->format('d M Y') }}</td>
                            <td class="text-muted small">{{ $grn->invoice_number ?? '—' }}</td>
                            <td class="text-end">&#8377;{{ number_format($grn->grand_total, 2) }}</td>
                            <td>
                                <a href="{{ route('grns.show', $grn) }}" class="btn btn-sm btn-outline-info">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center text-muted py-3">No GRNs yet</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

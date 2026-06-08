@extends('layouts.app')
@section('title', 'Barcode Management')
@section('page-title', 'Barcode Management')

@section('content')

{{-- Stats --}}
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="stat-card" style="background:linear-gradient(135deg,#6c757d,#495057)">
            <div class="stat-icon"><i class="bi bi-upc"></i></div>
            <div class="stat-value">{{ number_format($stats['total']) }}</div>
            <div>Total Barcodes</div>
        </div>
    </div>
    <div class="col-md-3">
        <a href="{{ request()->fullUrlWithQuery(['status' => 'unused']) }}" class="text-decoration-none">
        <div class="stat-card" style="background:linear-gradient(135deg,#868e96,#6c757d)">
            <div class="stat-icon"><i class="bi bi-circle"></i></div>
            <div class="stat-value">{{ number_format($stats['unused']) }}</div>
            <div>Unused</div>
        </div>
        </a>
    </div>
    <div class="col-md-3">
        <a href="{{ request()->fullUrlWithQuery(['status' => 'assigned']) }}" class="text-decoration-none">
        <div class="stat-card" style="background:linear-gradient(135deg,#339af0,#1971c2)">
            <div class="stat-icon"><i class="bi bi-link-45deg"></i></div>
            <div class="stat-value">{{ number_format($stats['assigned']) }}</div>
            <div>Assigned</div>
        </div>
        </a>
    </div>
    <div class="col-md-3">
        <a href="{{ request()->fullUrlWithQuery(['status' => 'active']) }}" class="text-decoration-none">
        <div class="stat-card" style="background:linear-gradient(135deg,#40c057,#2f9e44)">
            <div class="stat-icon"><i class="bi bi-check-circle-fill"></i></div>
            <div class="stat-value">{{ number_format($stats['active']) }}</div>
            <div>Active (Sold)</div>
        </div>
        </a>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center py-3">
        <span><i class="bi bi-upc-scan me-2"></i>Barcode List</span>
        <div class="d-flex gap-2">
            <button id="bulkPrintBtn" class="btn btn-sm btn-outline-dark d-none" onclick="printSelected()">
                <i class="bi bi-printer me-1"></i>Print Selected (<span id="selCount">0</span>)
            </button>
            <a href="{{ route('barcodes.assign') }}" class="btn btn-sm btn-outline-primary">
                <i class="bi bi-upc-scan me-1"></i>Scan to Assign
            </a>
            <a href="{{ route('barcodes.bulk-generate') }}" class="btn btn-sm btn-danger">
                <i class="bi bi-plus-lg me-1"></i>Bulk Generate
            </a>
        </div>
    </div>

    {{-- Filters --}}
    <div class="card-body border-bottom">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control form-control-sm"
                    placeholder="Search barcode…" value="{{ request('search') }}">
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select form-select-sm">
                    <option value="">All Statuses</option>
                    <option value="unused"   {{ request('status') === 'unused'   ? 'selected' : '' }}>Unused</option>
                    <option value="assigned" {{ request('status') === 'assigned' ? 'selected' : '' }}>Assigned</option>
                    <option value="active"   {{ request('status') === 'active'   ? 'selected' : '' }}>Active</option>
                </select>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-sm btn-outline-primary me-1">
                    <i class="bi bi-search"></i> Filter
                </button>
                <a href="{{ route('barcodes.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th style="width:40px">
                            <input type="checkbox" id="selectAll" class="form-check-input">
                        </th>
                        <th>Barcode</th>
                        <th>Barcode Image</th>
                        <th>Product</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($barcodes as $item)
                    <tr>
                        <td>
                            <input type="checkbox" class="form-check-input bc-check" value="{{ $item->id }}"
                                onchange="updateBulkBtn()">
                        </td>
                        <td class="font-monospace fw-semibold" style="font-size:.85rem">{{ $item->barcode }}</td>
                        <td>
                            <img src="{{ route('barcode.image', $item->barcode) }}"
                                alt="{{ $item->barcode }}" style="height:28px">
                        </td>
                        <td>
                            @if($item->product_id)
                                <span class="fw-semibold">{{ $item->product->name }}</span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-{{ $item->status_color }}">
                                {{ ucfirst($item->status) }}
                            </span>
                        </td>
                        <td class="small text-muted">{{ $item->created_at->format('d M Y') }}</td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                @if($item->status !== 'active')
                                <a href="{{ route('barcodes.assign') }}?barcode={{ $item->barcode }}"
                                    class="btn btn-outline-primary">
                                    <i class="bi bi-link-45deg me-1"></i>Assign
                                </a>
                                @endif
                                <a href="{{ route('barcode.image', $item->barcode) }}" target="_blank"
                                    class="btn btn-outline-secondary">
                                    <i class="bi bi-eye me-1"></i>View
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-5">
                            No barcodes found.
                            <a href="{{ route('barcodes.bulk-generate') }}">Generate some now →</a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($barcodes->hasPages())
    <div class="card-footer">{{ $barcodes->links() }}</div>
    @endif
</div>

{{-- Hidden form for bulk print --}}
<form id="printForm" action="{{ route('barcodes.print-labels') }}" method="POST" target="_blank">
    @csrf
    <div id="printInputs"></div>
</form>

@endsection

@push('scripts')
<script>
document.getElementById('selectAll').addEventListener('change', function () {
    document.querySelectorAll('.bc-check').forEach(cb => cb.checked = this.checked);
    updateBulkBtn();
});

function updateBulkBtn() {
    var n = document.querySelectorAll('.bc-check:checked').length;
    document.getElementById('selCount').textContent = n;
    document.getElementById('bulkPrintBtn').classList.toggle('d-none', n === 0);
}

function printSelected() {
    var ids = [...document.querySelectorAll('.bc-check:checked')].map(c => c.value);
    if (!ids.length) return;
    var inputs = document.getElementById('printInputs');
    inputs.innerHTML = ids.map(id =>
        `<input type="hidden" name="barcode_ids[]" value="${id}">`
    ).join('');
    document.getElementById('printForm').submit();
}
</script>
@endpush
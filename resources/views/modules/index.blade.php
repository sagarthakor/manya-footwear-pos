@extends('layouts.app')
@section('title', 'Module Management')
@section('page-title', 'Module Management')

@push('styles')
<style>
    .module-card {
        border: 2px solid #eaecf0;
        border-radius: 14px;
        padding: 1.25rem 1.4rem;
        transition: border-color .2s, box-shadow .2s;
        background: #fff;
    }
    .module-card.active  { border-color: #27ae60; }
    .module-card.inactive{ border-color: #e0e4ef; opacity: .75; }
    .module-card.active:hover  { box-shadow: 0 4px 18px rgba(39,174,96,.12); }
    .module-card.inactive:hover{ box-shadow: 0 4px 18px rgba(0,0,0,.07); opacity: 1; }

    .module-icon {
        width: 46px; height: 46px; border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.3rem; flex-shrink: 0;
    }
    .module-icon.active  { background: #eafaf1; color: #27ae60; }
    .module-icon.inactive{ background: #f4f6fb; color: #9ca3af; }

    .toggle-switch {
        position: relative; width: 52px; height: 28px; flex-shrink: 0;
    }
    .toggle-switch input { opacity: 0; width: 0; height: 0; }
    .toggle-slider {
        position: absolute; cursor: pointer; inset: 0;
        background: #e0e4ef; border-radius: 28px;
        transition: background .25s;
    }
    .toggle-slider:before {
        position: absolute; content: '';
        width: 22px; height: 22px; left: 3px; bottom: 3px;
        background: #fff; border-radius: 50%;
        box-shadow: 0 1px 4px rgba(0,0,0,.2);
        transition: transform .25s;
    }
    .toggle-switch input:checked + .toggle-slider { background: #27ae60; }
    .toggle-switch input:checked + .toggle-slider:before { transform: translateX(24px); }

    .status-pill {
        font-size: .72rem; font-weight: 600; padding: .2rem .65rem;
        border-radius: 20px;
    }
    .status-pill.on  { background: #eafaf1; color: #27ae60; }
    .status-pill.off { background: #fdf2f2; color: #e74c3c; }

    .section-heading {
        font-size: .78rem; font-weight: 700; letter-spacing: 1px;
        text-transform: uppercase; color: #6b7280;
        padding-bottom: .5rem;
        border-bottom: 2px solid #f0f2f8;
        margin-bottom: 1rem;
        display: flex; align-items: center; gap: .5rem;
    }
</style>
@endpush

@section('content')

<div class="alert alert-info d-flex align-items-start gap-2 mb-4">
    <i class="bi bi-info-circle-fill fs-5 mt-1 flex-shrink-0"></i>
    <div>
        <strong>Module Management</strong> — Toggle karo koi bhi module ON/OFF. OFF karne pe wo module ka sidebar menu, routes aur features sab chhup jayenge.
        Changes <strong>turant effect</strong> lete hain — page refresh karo change dekhne ke liye.
    </div>
</div>

@php
    $grouped = [];
    foreach ($definitions as $key => $info) {
        $grouped[$info['group'] ?? 'Core'][$key] = $info;
    }
    $groupIcons = [
        'Inventory' => 'bi-box-seam-fill',
        'Sales'     => 'bi-receipt',
        'Finance'   => 'bi-cash-coin',
        'Reports'   => 'bi-bar-chart-line-fill',
    ];
@endphp

@foreach($grouped as $groupName => $groupDefs)
<div class="mb-4">
    <div class="section-heading">
        <i class="bi {{ $groupIcons[$groupName] ?? 'bi-grid' }}"></i>
        {{ $groupName }} Modules
    </div>
    <div class="row g-3">
        @foreach($groupDefs as $key => $info)
        @php $isActive = $status[$key] ?? true; @endphp
        <div class="col-md-6 col-lg-4">
            <div class="module-card {{ $isActive ? 'active' : 'inactive' }}" id="card-{{ $key }}">
                <div class="d-flex align-items-center gap-3">
                    <div class="module-icon {{ $isActive ? 'active' : 'inactive' }}" id="icon-{{ $key }}">
                        <i class="bi {{ $info['icon'] }}"></i>
                    </div>
                    <div style="flex:1;min-width:0">
                        <div class="fw-semibold">{{ $info['label'] }}</div>
                        <div class="text-muted small">{{ $info['desc'] }}</div>
                    </div>
                    <label class="toggle-switch mb-0">
                        <input type="checkbox" {{ $isActive ? 'checked' : '' }}
                            onchange="toggleModule('{{ $key }}', this)">
                        <span class="toggle-slider"></span>
                    </label>
                </div>
                <div class="mt-3 d-flex align-items-center justify-content-between">
                    <span class="status-pill {{ $isActive ? 'on' : 'off' }}" id="pill-{{ $key }}">
                        {{ $isActive ? '✓ Active' : '✗ Disabled' }}
                    </span>
                    <small class="text-muted" id="note-{{ $key }}">
                        {{ $isActive ? 'Visible to all users' : 'Hidden from all users' }}
                    </small>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endforeach

<div class="card mt-2">
    <div class="card-header py-3"><i class="bi bi-table me-2"></i>Module Status Summary</div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Module</th>
                        <th>Group</th>
                        <th>Status</th>
                        <th>Visible To</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($definitions as $key => $info)
                    @php $on = $status[$key] ?? true; @endphp
                    <tr>
                        <td><i class="bi {{ $info['icon'] }} me-2 text-muted"></i>{{ $info['label'] }}</td>
                        <td><span class="badge bg-secondary">{{ $info['group'] ?? 'Core' }}</span></td>
                        <td>
                            <span class="badge bg-{{ $on ? 'success' : 'secondary' }}">
                                {{ $on ? 'ON' : 'OFF' }}
                            </span>
                        </td>
                        <td class="text-muted small">{{ $on ? 'Admin, Manager, Cashier (as per role)' : 'Hidden for all users' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function toggleModule(key, checkbox) {
    checkbox.disabled = true;

    fetch('{{ route('modules.toggle') }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: JSON.stringify({ module: key })
    })
    .then(r => r.json())
    .then(function(data) {
        checkbox.disabled = false;
        var active = data.active;

        var card = document.getElementById('card-' + key);
        var icon = document.getElementById('icon-' + key);
        var pill = document.getElementById('pill-' + key);
        var note = document.getElementById('note-' + key);

        card.className = 'module-card ' + (active ? 'active' : 'inactive');
        icon.className = 'module-icon ' + (active ? 'active' : 'inactive');
        pill.className = 'status-pill ' + (active ? 'on' : 'off');
        pill.textContent = active ? '✓ Active' : '✗ Disabled';
        note.textContent = active ? 'Visible to all users' : 'Hidden from all users';
    })
    .catch(function() {
        checkbox.disabled = false;
        checkbox.checked = !checkbox.checked;
        alert('Error updating module. Please try again.');
    });
}
</script>
@endpush

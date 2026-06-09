<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Mayank Footware POS')</title>
    <link rel="icon" type="image/png" href="/images/favicon.png">
    <link rel="shortcut icon" href="/images/favicon.png">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        :root {
            --sidebar-w: 260px;
            --sidebar-bg: #0f0f0f;
            --sidebar-hover: rgba(200,160,20,.12);
            --sidebar-active: rgba(180,140,10,.85);
            --accent: #b89010;
            --accent2: #8a6a08;
            --gold: #c8a014;
            --gold-light: #e8c040;
            --gold-dark: #8a6a08;
            --topbar-h: 60px;
            --body-bg: #f5f3ee;
            --card-radius: 12px;
        }

        * { box-sizing: border-box; }
        body {
            font-family: 'Inter', 'Segoe UI', sans-serif;
            background: var(--body-bg);
            margin: 0;
            color: #1a1f36;
        }

        /* ─── Sidebar ─────────────────────────────────────── */
        .sidebar {
            width: var(--sidebar-w);
            background: var(--sidebar-bg);
            position: fixed;
            top: 0; left: 0;
            height: 100vh;
            z-index: 1050;
            display: flex;
            flex-direction: column;
            transition: transform .28s cubic-bezier(.4,0,.2,1);
        }
        .sidebar-brand {
            padding: .75rem 1rem;
            background: #000;
            border-bottom: 1px solid rgba(200,160,20,.3);
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }
        .sidebar-brand img.sidebar-logo {
            height: 72px;
            width: auto;
            max-width: 180px;
            display: block;
            border-radius: 4px;
            filter: brightness(1.15) contrast(1.1);
        }
        .sidebar-close {
            display: none;
            margin-left: auto;
            background: none; border: none;
            color: rgba(255,255,255,.5); font-size: 1.3rem; cursor: pointer; padding: 0;
        }

        .sidebar-nav {
            flex: 1;
            overflow-y: auto;
            padding: .5rem 0 1.5rem;
            scrollbar-width: thin;
            scrollbar-color: rgba(255,255,255,.12) transparent;
        }
        .sidebar-nav::-webkit-scrollbar { width: 4px; }
        .sidebar-nav::-webkit-scrollbar-thumb { background: rgba(255,255,255,.12); border-radius: 4px; }

        .nav-section {
            font-size: .67rem; font-weight: 600; letter-spacing: 1.2px;
            text-transform: uppercase;
            color: rgba(200,160,20,.45);
            padding: 1.1rem 1.3rem .35rem;
        }
        .nav-link {
            display: flex; align-items: center; gap: 10px;
            color: rgba(255,255,255,.6);
            padding: .6rem 1rem;
            margin: 1px .6rem;
            border-radius: 8px;
            font-size: .855rem; font-weight: 400;
            text-decoration: none;
            transition: background .18s, color .18s;
            position: relative;
        }
        .nav-link i { font-size: 1rem; width: 18px; text-align: center; flex-shrink: 0; }
        .nav-link:hover {
            background: rgba(200,160,20,.12);
            color: #e8c040;
        }
        .nav-link.active {
            background: linear-gradient(90deg, rgba(200,160,20,.25), rgba(200,160,20,.1));
            color: #e8c040;
            font-weight: 600;
        }
        .nav-link.active::before {
            content: '';
            position: absolute; left: 0; top: 20%; bottom: 20%;
            width: 3px; border-radius: 0 3px 3px 0;
            background: #c8a014; margin-left: -.6rem;
        }

        /* ─── Overlay (mobile) ───────────────────────────── */
        .sidebar-overlay {
            display: none;
            position: fixed; inset: 0;
            background: rgba(0,0,0,.5);
            z-index: 1040;
            backdrop-filter: blur(2px);
        }
        .sidebar-overlay.show { display: block; }

        /* ─── Main Content ───────────────────────────────── */
        .main-wrap {
            margin-left: var(--sidebar-w);
            min-height: 100vh;
            display: flex; flex-direction: column;
            transition: margin-left .28s cubic-bezier(.4,0,.2,1);
        }

        /* ─── Topbar ─────────────────────────────────────── */
        .topbar {
            height: var(--topbar-h);
            background: #fff;
            border-bottom: 2px solid rgba(200,160,20,.3);
            padding: 0 1.5rem;
            display: flex; align-items: center;
            justify-content: space-between;
            position: sticky; top: 0; z-index: 999;
            box-shadow: 0 2px 12px rgba(180,140,10,.08);
            flex-shrink: 0;
        }
        .topbar-left { display: flex; align-items: center; gap: .75rem; }
        .hamburger {
            display: none;
            background: none; border: none;
            font-size: 1.35rem; color: #555; cursor: pointer; padding: 4px 6px;
            border-radius: 6px; line-height: 1;
        }
        .hamburger:hover { background: #f4f6fb; }
        .page-title {
            font-size: .98rem; font-weight: 600; color: #1a1f36; margin: 0;
        }
        .topbar-right { display: flex; align-items: center; gap: .75rem; }
        .date-badge {
            background: #fdf9ee; border: 1px solid rgba(200,160,20,.3);
            border-radius: 20px; padding: .3rem .9rem;
            font-size: .75rem; font-weight: 500; color: #7a6010;
        }
        .user-btn {
            background: #fdf9ee; border: 1px solid rgba(200,160,20,.3);
            border-radius: 8px; padding: .38rem .85rem;
            font-size: .82rem; font-weight: 500; color: #333;
            display: flex; align-items: center; gap: 6px; cursor: pointer;
        }
        .user-btn:hover { background: #f5edcc; border-color: #c8a014; }
        .role-dot {
            display: inline-block; width: 8px; height: 8px;
            border-radius: 50%;
        }

        /* ─── Page Content ───────────────────────────────── */
        .page-content { padding: 1.4rem 1.5rem; flex: 1; }

        /* ─── Alert ──────────────────────────────────────── */
        .alert { border: none; border-radius: 10px; font-size: .875rem; }

        /* ─── Cards ──────────────────────────────────────── */
        .card {
            border: none;
            border-radius: var(--card-radius);
            box-shadow: 0 1px 10px rgba(0,0,0,.07);
        }
        .card-header {
            background: #fff;
            border-bottom: 1px solid #f0ead8;
            border-radius: var(--card-radius) var(--card-radius) 0 0 !important;
            font-weight: 600; font-size: .9rem; color: #1a1a0a;
            border-top: 2px solid rgba(200,160,20,.4);
        }
        .card-footer {
            background: #fafbfd;
            border-top: 1px solid #f0f2f8;
            border-radius: 0 0 var(--card-radius) var(--card-radius) !important;
        }

        /* ─── Stat Cards ─────────────────────────────────── */
        .stat-card {
            border-radius: 14px; padding: 1.25rem 1.4rem;
            color: #fff; position: relative; overflow: hidden;
            border: none; box-shadow: 0 4px 20px rgba(0,0,0,.12);
        }
        .stat-card::after {
            content: '';
            position: absolute; right: -15px; top: -15px;
            width: 90px; height: 90px;
            background: rgba(255,255,255,.1);
            border-radius: 50%;
        }
        .stat-card .stat-icon { font-size: 1.8rem; opacity: .85; margin-bottom: .4rem; }
        .stat-card .stat-value { font-size: 1.7rem; font-weight: 700; line-height: 1; }
        .stat-card .stat-label { font-size: .8rem; opacity: .85; margin-top: .3rem; }

        /* ─── Tables ─────────────────────────────────────── */
        .table { font-size: .87rem; }
        .table th {
            font-size: .73rem; text-transform: uppercase;
            letter-spacing: .5px; color: #6b7280;
            font-weight: 600; border-bottom: 1px solid #f0f2f8 !important;
        }
        .table td { border-color: #f0f2f8; vertical-align: middle; }
        .table-hover tbody tr:hover { background: #fdf9ee; }

        /* ─── Buttons ────────────────────────────────────── */
        .btn-danger {
            background: linear-gradient(135deg, #c8a014, #a07810);
            border-color: #a07810; color: #000; font-weight: 600;
        }
        .btn-danger:hover {
            background: linear-gradient(135deg, #dbb520, #b88a10);
            border-color: #8a6a08; color: #000;
        }
        .btn-danger:focus { box-shadow: 0 0 0 3px rgba(200,160,20,.35); }
        .btn { border-radius: 7px; font-size: .87rem; font-weight: 500; }
        .btn-sm { font-size: .8rem; }

        /* ─── Form Controls ──────────────────────────────── */
        .form-control, .form-select {
            border-radius: 8px; border-color: #e0e4ef;
            font-size: .87rem;
        }
        .form-control:focus, .form-select:focus {
            border-color: #c8a014;
            box-shadow: 0 0 0 3px rgba(200,160,20,.15);
        }
        .form-label { font-size: .85rem; font-weight: 500; color: #374151; margin-bottom: .4rem; }

        /* ─── Badges ─────────────────────────────────────── */
        .badge { font-weight: 500; border-radius: 6px; font-size: .72rem; }

        /* ─── Print ──────────────────────────────────────── */
        @media print { .no-print { display: none !important; } }

        /* ─── Mobile Responsive ──────────────────────────── */
        @media (max-width: 991.98px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.open { transform: translateX(0); }
            .sidebar-close { display: block; }
            .main-wrap { margin-left: 0; }
            .hamburger { display: flex; }
            .page-content { padding: 1rem; }
            .topbar { padding: 0 1rem; }
            .date-badge { display: none; }
        }

        @media (max-width: 575.98px) {
            .page-title { font-size: .88rem; }
            .user-btn .user-name { display: none; }
        }
    </style>
    @stack('styles')
</head>
<body>

{{-- Mobile overlay --}}
<div class="sidebar-overlay no-print" id="sidebarOverlay" onclick="closeSidebar()"></div>

{{-- Sidebar --}}
<aside class="sidebar no-print" id="sidebar">
    <div class="sidebar-brand">
        <img src="/images/logo.jpeg" alt="Manya Footwear" class="sidebar-logo">
        <button class="sidebar-close" onclick="closeSidebar()" style="position:absolute;right:10px;top:50%;transform:translateY(-50%)"><i class="bi bi-x-lg"></i></button>
    </div>

    <nav class="sidebar-nav">

        <div class="nav-section">Main</div>
        <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <i class="bi bi-speedometer2"></i> Dashboard
        </a>
        <a href="{{ route('sales.create') }}" class="nav-link {{ request()->routeIs('sales.create') ? 'active' : '' }}">
            <i class="bi bi-cart3"></i> New Sale / POS
        </a>
        <a href="{{ route('guide') }}" class="nav-link {{ request()->routeIs('guide') ? 'active' : '' }}">
            <i class="bi bi-book-fill"></i> POS Guide
        </a>

        {{-- ── Inventory Section ─────────────────────────────────── --}}
        @php
            $user = auth()->user();
            $canProducts   = $user->can('view products')   && \App\Helpers\Module::isActive('products');
            $canBrands     = $user->can('manage brands')   && \App\Helpers\Module::isActive('brands');
            $canCategories = $user->can('manage categories') && \App\Helpers\Module::isActive('categories');
            $canBarcodes   = $user->can('print barcodes')  && \App\Helpers\Module::isActive('barcodes');
            $canAddStock   = \App\Helpers\Module::isActive('add_stock');
            $canStockHist  = \App\Helpers\Module::isActive('stock_movements');
            $anyInventory  = $canProducts || $canBrands || $canCategories || $canBarcodes || $canAddStock || $canStockHist;
        @endphp
        @if($anyInventory)
        <div class="nav-section">Inventory</div>
        @endif
        @if($canProducts)
        <a href="{{ route('products.index') }}" class="nav-link {{ request()->routeIs('products.*') ? 'active' : '' }}">
            <i class="bi bi-box-seam-fill"></i> Products
        </a>
        @endif
        @if($canBrands)
        <a href="{{ route('brands.index') }}" class="nav-link {{ request()->routeIs('brands.*') ? 'active' : '' }}">
            <i class="bi bi-stars"></i> Brands
        </a>
        @endif
        @if($canCategories)
        <a href="{{ route('categories.index') }}" class="nav-link {{ request()->routeIs('categories.*') ? 'active' : '' }}">
            <i class="bi bi-tags-fill"></i> Categories
        </a>
        @endif
        @if($canBarcodes)
        <a href="{{ route('barcodes.index') }}" class="nav-link {{ request()->routeIs('barcodes.*') ? 'active' : '' }}">
            <i class="bi bi-upc-scan"></i> Barcodes
        </a>
        @endif
        @if($canAddStock)
        <a href="{{ route('stock.add') }}" class="nav-link {{ request()->routeIs('stock.add') ? 'active' : '' }}">
            <i class="bi bi-plus-square-fill"></i> Add Stock
        </a>
        @endif
        @if($canStockHist)
        <a href="{{ route('stock.index') }}" class="nav-link {{ request()->routeIs('stock.index') ? 'active' : '' }}">
            <i class="bi bi-clock-history"></i> Stock History
        </a>
        @endif

        {{-- ── Sales Section ──────────────────────────────────────── --}}
        @php
            $canSalesHist   = \App\Helpers\Module::isActive('sales_history') && $user->can('view sales');
            $canSaleReturns = $user->can('process sale returns') && \App\Helpers\Module::isActive('sale_returns');
            $canCustomers   = $user->can('view customers')       && \App\Helpers\Module::isActive('customers');
            $anySales       = $canSalesHist || $canSaleReturns || $canCustomers;
        @endphp
        @if($anySales)
        <div class="nav-section">Sales</div>
        @endif
        @if($canSalesHist)
        <a href="{{ route('sales.index') }}" class="nav-link {{ request()->routeIs('sales.index') ? 'active' : '' }}">
            <i class="bi bi-receipt"></i> Sales History
        </a>
        @endif
        @if($canSaleReturns)
        <a href="{{ route('sale-returns.index') }}" class="nav-link {{ request()->routeIs('sale-returns.*') ? 'active' : '' }}">
            <i class="bi bi-arrow-return-left"></i> Sale Returns
        </a>
        @endif
        @if($canCustomers)
        <a href="{{ route('customers.index') }}" class="nav-link {{ request()->routeIs('customers.*') ? 'active' : '' }}">
            <i class="bi bi-people-fill"></i> Customers
        </a>
        @endif

        {{-- ── Purchases Section ──────────────────────────────────── --}}
        @if($user->can('view purchases') && \App\Helpers\Module::isActive('purchases'))
        <div class="nav-section">Purchases</div>
        <a href="{{ route('suppliers.index') }}" class="nav-link {{ request()->routeIs('suppliers.*') ? 'active' : '' }}">
            <i class="bi bi-building-fill"></i> Suppliers
        </a>
        <a href="{{ route('purchase-orders.index') }}" class="nav-link {{ request()->routeIs('purchase-orders.*') ? 'active' : '' }}">
            <i class="bi bi-file-earmark-text-fill"></i> Purchase Orders
        </a>
        <a href="{{ route('grns.index') }}" class="nav-link {{ request()->routeIs('grns.*') ? 'active' : '' }}">
            <i class="bi bi-truck"></i> GRN
        </a>
        @endif

        {{-- ── Finance Section ────────────────────────────────────── --}}
        @if($user->can('view expenses') && \App\Helpers\Module::isActive('expenses'))
        <div class="nav-section">Finance</div>
        <a href="{{ route('expenses.index') }}" class="nav-link {{ request()->routeIs('expenses.*') ? 'active' : '' }}">
            <i class="bi bi-cash-coin"></i> Expenses
        </a>
        @endif

        {{-- ── Reports Section ─────────────────────────────────────── --}}
        @if($user->can('view reports'))
        @php
            $anyReport = \App\Helpers\Module::isActive('report_daily')
                      || \App\Helpers\Module::isActive('report_monthly')
                      || \App\Helpers\Module::isActive('report_profit_loss')
                      || \App\Helpers\Module::isActive('report_gst')
                      || \App\Helpers\Module::isActive('report_inventory')
                      || \App\Helpers\Module::isActive('report_cashier')
                      || \App\Helpers\Module::isActive('report_stock_movement');
        @endphp
        @if($anyReport)
        <div class="nav-section">Reports</div>
        @endif
        @module('report_daily')
        <a href="{{ route('reports.daily') }}" class="nav-link {{ request()->routeIs('reports.daily') ? 'active' : '' }}">
            <i class="bi bi-calendar-check-fill"></i> Daily Report
        </a>
        @endmodule
        @module('report_monthly')
        <a href="{{ route('reports.monthly') }}" class="nav-link {{ request()->routeIs('reports.monthly') ? 'active' : '' }}">
            <i class="bi bi-calendar-month"></i> Monthly Report
        </a>
        @endmodule
        @module('report_profit_loss')
        <a href="{{ route('reports.profit-loss') }}" class="nav-link {{ request()->routeIs('reports.profit-loss') ? 'active' : '' }}">
            <i class="bi bi-graph-up-arrow"></i> Profit & Loss
        </a>
        @endmodule
        @module('report_gst')
        <a href="{{ route('reports.gst') }}" class="nav-link {{ request()->routeIs('reports.gst') ? 'active' : '' }}">
            <i class="bi bi-percent"></i> GST Report
        </a>
        @endmodule
        @module('report_inventory')
        <a href="{{ route('reports.inventory') }}" class="nav-link {{ request()->routeIs('reports.inventory') ? 'active' : '' }}">
            <i class="bi bi-clipboard-data-fill"></i> Inventory Report
        </a>
        @endmodule
        @module('report_cashier')
        <a href="{{ route('reports.cashier-wise') }}" class="nav-link {{ request()->routeIs('reports.cashier-wise') ? 'active' : '' }}">
            <i class="bi bi-person-badge"></i> Cashier Report
        </a>
        @endmodule
        @module('report_stock_movement')
        <a href="{{ route('reports.stock-movement') }}" class="nav-link {{ request()->routeIs('reports.stock-movement') ? 'active' : '' }}">
            <i class="bi bi-bar-chart-steps"></i> Stock History Report
        </a>
        @endmodule
        @endif

        @if(auth()->user()->isAdmin())
        <div class="nav-section">Administration</div>
        <a href="{{ route('users.index') }}" class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}">
            <i class="bi bi-shield-lock-fill"></i> Users
        </a>
        @if(auth()->user()->isSuperAdmin())
        <a href="{{ route('roles.index') }}" class="nav-link {{ request()->routeIs('roles.*') ? 'active' : '' }}">
            <i class="bi bi-shield-plus"></i> Custom Roles
        </a>
        <a href="{{ route('permissions.index') }}" class="nav-link {{ request()->routeIs('permissions.*') ? 'active' : '' }}">
            <i class="bi bi-key-fill"></i> Permissions
        </a>
        {{-- <a href="{{ route('modules.index') }}" class="nav-link {{ request()->routeIs('modules.*') ? 'active' : '' }}">
            <i class="bi bi-toggles"></i> Modules
        </a> --}}
        @endif
        <a href="{{ route('settings.index') }}" class="nav-link {{ request()->routeIs('settings.*') ? 'active' : '' }}">
            <i class="bi bi-gear-fill"></i> Settings
        </a>
        @endif

        <div class="nav-section">Account</div>
        <a href="{{ route('profile.edit') }}" class="nav-link {{ request()->routeIs('profile.*') ? 'active' : '' }}">
            <i class="bi bi-person-circle"></i> My Profile
        </a>

    </nav>
</aside>

{{-- Main content --}}
<div class="main-wrap">

    {{-- Topbar --}}
    <header class="topbar no-print">
        <div class="topbar-left">
            <button class="hamburger" onclick="openSidebar()" aria-label="Menu">
                <i class="bi bi-list"></i>
            </button>
            <h1 class="page-title">@yield('page-title', 'Dashboard')</h1>
        </div>
        <div class="topbar-right">
            <div class="date-badge">
                <i class="bi bi-calendar3 me-1"></i>{{ now()->format('d M Y') }}
            </div>
            <div class="dropdown">
                <button class="user-btn dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                    @php
                        $roleColor = match(auth()->user()->role) {
                            'super_admin' => '#6f42c1',
                            'admin'       => '#e74c3c',
                            'manager'     => '#0d6efd',
                            default       => '#198754',
                        };
                    @endphp
                    <span class="role-dot" style="background:{{ $roleColor }}"></span>
                    <span class="user-name">{{ auth()->user()->name }}</span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0" style="border-radius:10px;min-width:180px">
                    <li class="px-3 py-2 border-bottom">
                        <div class="fw-semibold small">{{ auth()->user()->name }}</div>
                        <div class="text-muted" style="font-size:.75rem">{{ auth()->user()->role_label }}</div>
                    </li>
                    <li>
                        <a class="dropdown-item small py-2" href="{{ route('profile.edit') }}">
                            <i class="bi bi-person-circle me-2 text-muted"></i>My Profile
                        </a>
                    </li>
                    <li><hr class="dropdown-divider my-1"></li>
                    <li>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="dropdown-item small py-2 text-danger">
                                <i class="bi bi-box-arrow-right me-2"></i>Logout
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </header>

    {{-- Page content --}}
    <main class="page-content">
        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2 mb-3">
            <i class="bi bi-check-circle-fill fs-5"></i>
            <span>{{ session('success') }}</span>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
        </div>
        @endif
        @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center gap-2 mb-3">
            <i class="bi bi-exclamation-triangle-fill fs-5"></i>
            <span>{{ session('error') }}</span>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
        </div>
        @endif
        @yield('content')
    </main>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
function openSidebar() {
    document.getElementById('sidebar').classList.add('open');
    document.getElementById('sidebarOverlay').classList.add('show');
    document.body.style.overflow = 'hidden';
}
function closeSidebar() {
    document.getElementById('sidebar').classList.remove('open');
    document.getElementById('sidebarOverlay').classList.remove('show');
    document.body.style.overflow = '';
}
// Close sidebar on nav-link click (mobile)
document.querySelectorAll('.sidebar .nav-link').forEach(function(link) {
    link.addEventListener('click', function() {
        if (window.innerWidth < 992) closeSidebar();
    });
});
</script>
@stack('scripts')
</body>
</html>
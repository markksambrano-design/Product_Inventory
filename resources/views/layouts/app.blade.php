<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>@yield('title', 'Product Inventory')</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <style>
        :root { --sidebar:#0b1424; --sidebar-soft:#111e32; --sidebar-line:rgba(148,163,184,.12); --accent:#536dfe; }
        body { background:#f4f7fb; color:#172033; }
        .app-row { min-height:100vh; flex-wrap:nowrap; }

        .sidebar {
            position: sticky;
            top: 0;
            align-self: flex-start;
            width: 245px;
            flex: 0 0 245px;
            height: 100vh;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            background: radial-gradient(circle at 30% 0, rgba(61,82,170,.16), transparent 25%), var(--sidebar);
            border-right: 1px solid var(--sidebar-line);
            box-shadow: 10px 0 35px rgba(15,23,42,.08);
        }

        .sidebar a {
            color: #aebbd0;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 0 10px;
            padding: 7px 11px;
            border: 1px solid transparent;
            border-radius: 8px;
            font-size: .8rem;
            transition: .18s ease;
        }

        .sidebar a:hover { color:#fff; background:rgba(255,255,255,.055); transform:translateX(2px); }
        .sidebar a.active { color:#fff; background:linear-gradient(100deg,rgba(83,109,254,.28),rgba(70,86,202,.12)); border-color:rgba(111,132,255,.25); box-shadow:inset 3px 0 #7084ff,0 6px 18px rgba(0,0,0,.1); }
        .sidebar a i { width:18px; text-align:center; font-size:.92rem; color:#8494b1; transition:.18s; }
        .sidebar a:hover i,.sidebar a.active i { color:#8495ff; }
        .sidebar-brand { display:flex; align-items:center; gap:10px; padding:14px 16px 12px; border-bottom:1px solid var(--sidebar-line); }
        .brand-icon { width:34px; height:34px; display:grid; place-items:center; border-radius:10px; color:#fff; background:linear-gradient(145deg,#7a4df6,#3566ff); box-shadow:0 7px 18px rgba(65,83,255,.28); }
        .brand-copy strong { display:block; color:#fff; font-size:.88rem; line-height:1.1; }.brand-copy small{color:#71809b;font-size:.57rem;letter-spacing:.07em;text-transform:uppercase}
        .nav-scroll { flex:1; padding:5px 0 7px; overflow-y:auto; scrollbar-width:thin; scrollbar-color:#293750 transparent; }
        .nav-label { padding:8px 21px 2px; color:#586780; font-size:.56rem; font-weight:700; letter-spacing:.11em; text-transform:uppercase; }
        .nav-badge { margin-left:auto; min-width:18px; height:18px; display:grid; place-items:center; border-radius:6px; font-size:.6rem; background:#ef4444; color:#fff; }

        .content {
            min-width: 0;
            flex: 1 1 auto;
            padding: 0 30px 30px;
        }

        .top-header { position:sticky; z-index:40; top:0; min-height:92px; margin:0 -30px 28px; padding:18px 30px; display:flex; align-items:center; justify-content:space-between; gap:20px; background:rgba(255,255,255,.94); border-bottom:1px solid #e5eaf1; box-shadow:0 5px 20px rgba(15,23,42,.055); backdrop-filter:blur(14px); -webkit-backdrop-filter:blur(14px); }
        .page-heading { min-width:0; display:flex; align-items:center; gap:12px; }
        .page-heading-icon { width:44px; height:44px; flex:0 0 44px; display:grid; place-items:center; border:1px solid rgba(83,109,254,.16); border-radius:13px; color:#fff; background:linear-gradient(145deg,#7650f5,#4167ff); box-shadow:0 8px 20px rgba(78,94,242,.2); font-size:1.05rem; }
        .page-heading-copy { min-width:0; }
        .page-kicker { margin-bottom:1px; color:#6574dd; font-size:.61rem; font-weight:750; letter-spacing:.12em; text-transform:uppercase; }
        .page-heading h1 { margin:0; color:#15213a; font-size:1.45rem; line-height:1.1; font-weight:760; letter-spacing:-.025em; }
        .page-heading-copy > p { display:none; }
        .page-description { margin:4px 0 0; color:#7b8799; font-size:.76rem; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
        .header-actions { display:flex;align-items:center;gap:11px; }
        .date-chip,.header-icon { height:40px;display:flex;align-items:center;gap:8px;border:1px solid #e2e8f0;border-radius:10px;background:#f8fafc;color:#64748b;font-size:.76rem; }
        .date-chip{padding:0 13px}.header-icon{position:relative;width:40px;justify-content:center;font-size:1rem}.header-icon .dot{position:absolute;right:8px;top:7px;width:7px;height:7px;border:2px solid #fff;border-radius:50%;background:#ef4444}
        .header-user { display:flex;align-items:center;gap:9px;padding-left:4px; }.header-avatar{width:40px;height:40px;display:grid;place-items:center;border-radius:11px;color:#fff;background:linear-gradient(145deg,#7650f5,#4167ff);font-size:.82rem;font-weight:700;box-shadow:0 7px 17px rgba(78,94,242,.2)}.header-user strong{display:block;color:#24324a;font-size:.78rem}.header-user small{display:block;color:#8a95a6;font-size:.66rem}
        .user-dropdown { position:relative; }.user-dropdown summary{list-style:none;cursor:pointer;border-radius:11px;padding:4px 7px;transition:.18s}.user-dropdown summary::-webkit-details-marker{display:none}.user-dropdown summary:hover,.user-dropdown[open] summary{background:#f1f5f9}.user-dropdown .chevron{color:#94a3b8;font-size:.72rem;transition:.18s}.user-dropdown[open] .chevron{transform:rotate(180deg)}
        .user-menu { position:absolute; z-index:50; right:0; top:calc(100% + 9px); width:205px; padding:7px; border:1px solid #e2e8f0; border-radius:12px; background:#fff; box-shadow:0 18px 45px rgba(15,23,42,.16); }.user-menu::before{content:"";position:absolute;right:19px;top:-5px;width:10px;height:10px;border-left:1px solid #e2e8f0;border-top:1px solid #e2e8f0;background:#fff;transform:rotate(45deg)}.user-menu a,.user-menu button{position:relative;width:100%;display:flex;align-items:center;gap:10px;padding:9px 10px;border:0;border-radius:8px;color:#475569;background:transparent;text-decoration:none;font-size:.78rem;text-align:left}.user-menu a:hover{color:#3f51d7;background:#f1f5ff}.user-menu button:hover{color:#dc2626;background:#fff1f2}.user-menu hr{margin:5px 4px;border-color:#edf0f4;opacity:1}

        .card {
            border: none;
            border-radius: 12px;
        }

        @media print {
            .sidebar, .btn, form { display: none !important; }
            .content { width: 100% !important; padding: 0 !important; }
            body { background: white; }
            .card { box-shadow: none !important; border: 1px solid #ddd; }
        }
        @media (max-width: 767.98px) {
            .app-row { flex-wrap:wrap; }
            .sidebar { position:relative; top:auto; width:100%; height:auto; flex:0 0 100%; min-height:auto; overflow:visible; }
            .nav-scroll { max-height:55vh; }
            .content { width:100%; padding:20px 14px; }
            .top-header { margin:-20px -14px 20px;padding:14px;min-height:78px}.date-chip,.header-user .header-user-copy{display:none}.page-heading{gap:9px}.page-heading-icon{width:38px;height:38px;flex-basis:38px;border-radius:11px}.page-heading h1{font-size:1.2rem}.page-kicker{font-size:.55rem}.page-description{max-width:48vw}
        }
    </style>
</head>

<body>

<div class="container-fluid">
    <div class="row app-row">

        <aside class="sidebar p-0">

            <div class="sidebar-brand"><span class="brand-icon"><i class="bi bi-box-seam fs-5"></i></span><span class="brand-copy"><strong>Product Inventory</strong><small>Management System</small></span></div>
            <nav class="nav-scroll">
            <div class="nav-label">Overview</div>

            <a
                href="{{ route('dashboard') }}"
                class="{{ request()->routeIs('dashboard') ? 'active' : '' }}"
            >
                <i class="bi bi-grid-1x2-fill"></i><span>Dashboard</span>
                @if($inventoryAlertCount > 0)<span class="nav-badge">{{ $inventoryAlertCount }}</span>@endif
            </a>

            <div class="nav-label">Inventory</div>

            <a
                href="{{ route('products.index') }}"
                class="{{ request()->routeIs('products.*') ? 'active' : '' }}"
            >
                <i class="bi bi-box-seam"></i><span>Products</span>
            </a>

            @if(auth()->user()->role === 'admin')
                <a
                    href="{{ route('categories.index') }}"
                    class="{{ request()->routeIs('categories.*') ? 'active' : '' }}"
                >
                    <i class="bi bi-tags"></i><span>Categories</span>
                </a>

                <a
                    href="{{ route('suppliers.index') }}"
                    class="{{ request()->routeIs('suppliers.*') ? 'active' : '' }}"
                >
                    <i class="bi bi-truck"></i><span>Suppliers</span>
                </a>
            @endif

            <a
                href="{{ route('inventory.index') }}"
                class="{{ request()->routeIs('inventory.*') ? 'active' : '' }}"
            >
                <i class="bi bi-arrow-left-right"></i><span>Inventory</span>
            </a>

            <a
                href="{{ route('batch-inventory.index') }}"
                class="{{ request()->routeIs('batch-inventory.*') ? 'active' : '' }}"
            >
                <i class="bi bi-boxes"></i><span>Batch Inventory</span>
            </a>

            <a href="{{ route('barcodes.index') }}" class="{{ request()->routeIs('barcodes.*') ? 'active' : '' }}"><i class="bi bi-qr-code-scan"></i><span>Barcode Labels</span></a>

            <div class="nav-label">Stock Operations</div>


            <a href="{{ route('purchase-orders.index') }}" class="{{ request()->routeIs('purchase-orders.*') ? 'active' : '' }}"><i class="bi bi-cart-check"></i><span>Purchase Orders</span></a>

            <a
                href="{{ route('stock-in.index') }}"
                class="{{ request()->routeIs('stock-in.*') ? 'active' : '' }}"
            >
                <i class="bi bi-box-arrow-in-down"></i><span>Stock In</span>
            </a>

            <a
                href="{{ route('stock-out.index') }}"
                class="{{ request()->routeIs('stock-out.*') ? 'active' : '' }}"
            >
                <i class="bi bi-box-arrow-up"></i><span>Stock Out</span>
            </a>

            <a
                href="{{ route('stock-adjustments.index') }}"
                class="{{ request()->routeIs('stock-adjustments.*') ? 'active' : '' }}"
            >
                <i class="bi bi-sliders"></i><span>Stock Adjustments</span>
            </a>

            <a href="{{ route('inventory-counts.index') }}" class="{{ request()->routeIs('inventory-counts.*') ? 'active' : '' }}">
                <i class="bi bi-clipboard-check"></i><span>Physical Counts</span>
            </a>

            <div class="nav-label">Monitoring</div>

            <a
                href="{{ route('expiration.index') }}"
                class="{{ request()->routeIs('expiration.*') ? 'active' : '' }}"
            >
                <i class="bi bi-calendar2-week"></i><span>Expiration Monitoring</span>
            </a>

            <a
                href="{{ route('expired-disposals.index') }}"
                class="{{ request()->routeIs('expired-disposals.*') ? 'active' : '' }}"
            >
                <i class="bi bi-trash3"></i><span>Expired Disposal</span>
            </a>

            @if(auth()->user()->role === 'admin')
                <div class="nav-label">Administration</div>
                <a
                    href="{{ route('reports.index') }}"
                    class="{{ request()->routeIs('reports.*') ? 'active' : '' }}"
                >
                    <i class="bi bi-bar-chart-line"></i><span>Reports</span>
                </a>

                <a
                    href="{{ route('users.index') }}"
                    class="{{ request()->routeIs('users.*') ? 'active' : '' }}"
                >
                    <i class="bi bi-people"></i><span>Users</span>
                </a>

                <a
                    href="{{ route('activity-logs.index') }}"
                    class="{{ request()->routeIs('activity-logs.*') ? 'active' : '' }}"
                >
                    <i class="bi bi-clock-history"></i><span>Activity Logs</span>
                </a>
            @endif
            </nav>

        </aside>

        <main class="content">

            <header class="top-header">
                <div class="page-heading">
                    <span class="page-heading-icon"><i class="bi bi-grid-1x2-fill"></i></span>
                    <div class="page-heading-copy">
                    <div class="page-kicker">Management System</div>
                    <h1>Product Inventory</h1>
                    <div class="page-description">Track, manage, and monitor inventory operations.</div>
                    <p><span class="crumb">Inventory</span> / @yield('title', 'Dashboard') Â· @yield('page_description', 'Manage and monitor inventory operations.')</p>
                    </div>
                </div>
                <div class="header-actions">
                    <div class="date-chip"><i class="bi bi-calendar3"></i>{{ now()->format('M d, Y') }}</div>
                    <a href="{{ route('notifications.index') }}" class="header-icon text-decoration-none" title="Inventory alerts"><i class="bi bi-bell"></i>@if($inventoryAlertCount > 0)<span class="dot"></span>@endif</a>
                    <details class="user-dropdown">
                        <summary class="header-user"><span class="header-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span><span class="header-user-copy"><strong>{{ auth()->user()->name }}</strong><small>{{ ucfirst(auth()->user()->role) }}</small></span><i class="bi bi-chevron-down chevron"></i></summary>
                        <div class="user-menu">
                            <a href="{{ route('settings.edit') }}"><i class="bi bi-gear"></i> Account Settings</a>
                            @if(auth()->user()->role === 'admin')
                                <a href="{{ route('security.index') }}"><i class="bi bi-shield-lock"></i> Login Security</a>
                                <a href="{{ route('products.bulk.form') }}"><i class="bi bi-file-earmark-spreadsheet"></i> Bulk Product Tools</a>
                                <a href="{{ route('backups.index') }}"><i class="bi bi-database-check"></i> Backup &amp; Restore</a>
                                <a href="{{ route('backups.download') }}"><i class="bi bi-cloud-arrow-down"></i> Download Backup</a>
                            @endif
                            <hr>
                            <form action="{{ route('logout') }}" method="POST">@csrf<button type="submit"><i class="bi bi-box-arrow-right"></i> Logout</button></form>
                        </div>
                    </details>
                </div>
            </header>

            @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>
            @endif

            @yield('content')

        </main>

    </div>
</div>

</body>
</html>

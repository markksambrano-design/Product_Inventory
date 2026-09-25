@extends('layouts.app')

@section('title', 'Reports')

@section('content')

<style>
    .reports-page{--report-accent:#536dfe}.report-hero{position:relative;overflow:hidden;margin-bottom:20px;padding:22px 24px;border:1px solid #e1e8f2;border-radius:16px;background:linear-gradient(120deg,#fff,#f7f9ff 65%,#eef2ff);box-shadow:0 10px 30px rgba(30,42,70,.06)}.report-hero::after{content:"";position:absolute;right:130px;top:-80px;width:210px;height:210px;border-radius:50%;background:radial-gradient(circle,rgba(83,109,254,.15),transparent 68%)}.report-hero h2{margin:0 0 5px;color:#15213a;font-size:1.7rem;font-weight:780;letter-spacing:-.03em}.report-hero p{color:#758298!important;font-size:.84rem}.report-hero .btn{position:relative;z-index:2;padding:9px 14px;border-color:#dbe2ed;border-radius:10px;color:#46546b;background:#fff;box-shadow:0 5px 14px rgba(25,39,70,.06);font-size:.75rem}.report-hero .btn::before{content:"\F501";margin-right:7px;font-family:"bootstrap-icons"}
    .reports-page .card{overflow:hidden;border:1px solid #e5eaf2;border-radius:15px!important;box-shadow:0 8px 25px rgba(25,39,70,.055)!important}.export-strip .card-body{display:flex;align-items:center;flex-wrap:wrap;gap:8px;padding:14px 17px}.export-strip strong{display:flex;align-items:center;gap:7px;margin-right:5px!important;color:#536079;font-size:.75rem}.export-strip strong::before{content:"\F30A";font-family:"bootstrap-icons"}.export-strip .btn{margin:0!important;padding:6px 10px;border-color:#dce3ee;border-radius:8px;color:#3e5b87;background:#f8fafc;font-size:.69rem}.export-strip .btn:hover{border-color:#536dfe;color:#fff;background:#536dfe}
    .summary-card{--tone:#536dfe;position:relative;overflow:hidden}.summary-card::before{content:"";position:absolute;left:0;top:17px;bottom:17px;width:3px;border-radius:0 5px 5px 0;background:var(--tone)}.summary-card .card-body{padding:17px 18px}.summary-card p{margin-bottom:6px!important;color:#7c8798!important;font-size:.72rem;font-weight:650}.summary-card h3{margin:0;color:#202b3d!important;font-size:1.48rem;font-weight:780;letter-spacing:-.035em}.summary-card i{float:right;width:35px;height:35px;display:grid;place-items:center;border-radius:10px;color:var(--tone);background:color-mix(in srgb,var(--tone),#fff 89%)}
    .filter-panel .card-body{padding:16px}.filter-panel .form-label{margin-bottom:6px;color:#59667a;font-size:.72rem;font-weight:700}.filter-panel .form-control{height:42px;border-color:#e0e6ef;border-radius:9px;font-size:.76rem}.filter-panel .btn{height:42px;padding:0 15px;border-radius:9px;font-size:.73rem}.filter-panel .btn-dark{border-color:#536dfe;background:#536dfe}.filter-title{display:flex;align-items:center;gap:8px;margin-bottom:12px;color:#344158;font-size:.8rem;font-weight:750}.filter-title i{color:#536dfe}
    .report-table-card .card-header{padding:14px 17px;border-bottom-color:#edf0f5!important;background:linear-gradient(#fff,#fbfcfe)!important}.report-table-card .card-header h5{display:flex;align-items:center;gap:9px;color:#29364d;font-size:.92rem;font-weight:750}.report-table-card .card-header h5::before{content:"\F4D0";width:30px;height:30px;display:grid;place-items:center;border-radius:9px;color:#536dfe;background:#eef1ff;font-family:"bootstrap-icons"}.report-table-card .card-body{padding:7px 16px 13px}.report-table-card table{margin:0}.report-table-card thead th{padding:11px 8px;color:#8792a4;border-bottom-color:#e8edf4;font-size:.66rem;font-weight:750;letter-spacing:.05em;text-transform:uppercase}.report-table-card tbody td{padding:11px 8px;color:#465267;border-bottom-color:#edf1f5;font-size:.75rem}.report-table-card tbody tr:last-child td{border-bottom:0}.report-table-card tbody tr:hover{background:#fafbff}.report-table-card .badge{padding:5px 8px;border-radius:7px;font-size:.63rem}
    @media print{.report-hero .btn,.export-strip,.filter-panel{display:none!important}.report-hero{padding:0;border:0;box-shadow:none}.reports-page .card{box-shadow:none!important}}
</style>

<div class="reports-page">

<div class="report-hero">
    <div class="d-flex justify-content-between align-items-start"><div><h2>Reports</h2><p class="text-muted mb-0">View inventory and stock transaction reports.</p></div><button onclick="window.print()" class="btn btn-outline-dark">Print Report</button></div>
</div>

<div class="card export-strip mb-4"><div class="card-body"><strong class="me-3">Export CSV / Excel</strong>
@foreach(['products' => 'Products', 'stock_in' => 'Stock In', 'stock_out' => 'Stock Out', 'expired' => 'Expired', 'movements' => 'Movements', 'valuation' => 'Valuation', 'profit' => 'Profit & Margin', 'suppliers' => 'Suppliers'] as $key => $label)
<a class="btn btn-sm btn-outline-success me-1" href="{{ route('reports.export', ['type' => $key, 'date_from' => $dateFrom, 'date_to' => $dateTo]) }}">{{ $label }}</a>
@endforeach
</div></div>

<div class="row g-4 mb-4">
    <div class="col-md-4"><div class="card summary-card h-100" style="--tone:#7c3aed"><div class="card-body"><p class="text-muted mb-2">Near Expiry (30 days)</p><h3>{{ $nearExpiryCount }}</h3></div></div></div>
    <div class="col-md-4"><div class="card summary-card h-100" style="--tone:#0f766e"><div class="card-body"><p class="text-muted mb-2">Inventory Value</p><h3>₱{{ number_format((float) $inventoryValue, 2) }}</h3></div></div></div>
    <div class="col-md-4"><div class="card summary-card h-100" style="--tone:#15803d"><div class="card-body"><p class="text-muted mb-2">Recorded Sales</p><h3>₱{{ number_format((float) $profitSummary, 2) }}</h3></div></div></div>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-4 col-lg">
        <div class="card summary-card h-100" style="--tone:#536dfe"><i class="bi bi-box-seam position-absolute top-0 end-0 mt-3 me-3"></i>
            <div class="card-body">
                <p class="text-muted mb-2">Total Products</p>
                <h3>{{ $totalProducts }}</h3>
            </div>
        </div>
    </div>

    <div class="col-md-4 col-lg">
        <div class="card summary-card h-100" style="--tone:#0ea5a8"><i class="bi bi-boxes position-absolute top-0 end-0 mt-3 me-3"></i>
            <div class="card-body">
                <p class="text-muted mb-2">Total Stock</p>
                <h3>{{ number_format($totalStock, 2) }}</h3>
            </div>
        </div>
    </div>

    <div class="col-md-4 col-lg">
        <div class="card summary-card h-100" style="--tone:#f0a500"><i class="bi bi-graph-down-arrow position-absolute top-0 end-0 mt-3 me-3"></i>
            <div class="card-body">
                <p class="text-muted mb-2">Low Stock</p>
                <h3 class="text-warning">{{ $lowStockCount }}</h3>
            </div>
        </div>
    </div>

    <div class="col-md-4 col-lg">
        <div class="card summary-card h-100" style="--tone:#ef4444"><i class="bi bi-x-circle position-absolute top-0 end-0 mt-3 me-3"></i>
            <div class="card-body">
                <p class="text-muted mb-2">Out of Stock</p>
                <h3 class="text-danger">{{ $outOfStockCount }}</h3>
            </div>
        </div>
    </div>

    <div class="col-md-4 col-lg">
        <div class="card summary-card h-100" style="--tone:#dc3545"><i class="bi bi-calendar-x position-absolute top-0 end-0 mt-3 me-3"></i>
            <div class="card-body">
                <p class="text-muted mb-2">Expired</p>
                <h3 class="text-danger">{{ $expiredCount }}</h3>
            </div>
        </div>
    </div>
</div>

<div class="card filter-panel mb-4">
    <div class="card-body">
        <div class="filter-title"><i class="bi bi-funnel"></i>Filter transaction period</div>
        <form action="{{ route('reports.index') }}" method="GET">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Date From</label>
                    <input
                        type="date"
                        name="date_from"
                        class="form-control"
                        value="{{ $dateFrom }}"
                    >
                </div>

                <div class="col-md-4">
                    <label class="form-label">Date To</label>
                    <input
                        type="date"
                        name="date_to"
                        class="form-control"
                        value="{{ $dateTo }}"
                    >
                </div>

                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-dark me-2">Filter</button>
                    <a href="{{ route('reports.index') }}" class="btn btn-outline-secondary">
                        Reset
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card report-table-card mb-4">
    <div class="card-header bg-white">
        <h5 class="mb-0">Recent Stock In</h5>
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Product</th>
                        <th>Supplier</th>
                        <th>Quantity</th>
                        <th>Reference</th>
                    </tr>
                </thead>

                <tbody>
                @forelse($stockIns as $stockIn)
                    <tr>
                        <td>{{ $stockIn->stock_in_date?->format('M d, Y') }}</td>
                        <td>{{ $stockIn->product->product_name ?? 'N/A' }}</td>
                        <td>{{ $stockIn->supplier->supplier_name ?? '-' }}</td>
                        <td>
                            <span class="text-success fw-bold">
                                +{{ $stockIn->quantity }}
                            </span>
                        </td>
                        <td>{{ $stockIn->reference_number ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted">
                            No stock in records.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="card report-table-card mb-4">
    <div class="card-header bg-white">
        <h5 class="mb-0">Recent Stock Out</h5>
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Product</th>
                        <th>Quantity</th>
                        <th>Reason</th>
                        <th>Reference</th>
                    </tr>
                </thead>

                <tbody>
                @forelse($stockOuts as $stockOut)
                    <tr>
                        <td>{{ $stockOut->stock_out_date?->format('M d, Y') }}</td>
                        <td>{{ $stockOut->product->product_name ?? 'N/A' }}</td>
                        <td>
                            <span class="text-danger fw-bold">
                                -{{ $stockOut->quantity }}
                            </span>
                        </td>
                        <td>{{ $stockOut->reason ?? '-' }}</td>
                        <td>{{ $stockOut->reference_number ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted">
                            No stock out records.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="card report-table-card">
    <div class="card-header bg-white">
        <h5 class="mb-0">Low Stock Report</h5>
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Category</th>
                        <th>Current Stock</th>
                        <th>Minimum Stock</th>
                        <th>Status</th>
                    </tr>
                </thead>

                <tbody>
                @forelse($lowStockProducts as $product)
                    <tr>
                        <td>{{ $product->product_name }}</td>
                        <td>{{ $product->category_label }}</td>
                        <td>
                            {{ $product->quantity }}
                            {{ $product->unit }}
                        </td>
                        <td>{{ $product->minimum_stock }}</td>
                        <td>
                            @if($product->quantity <= 0)
                                <span class="badge bg-danger">Out of Stock</span>
                            @else
                                <span class="badge bg-warning text-dark">Low Stock</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted">
                            No low stock products.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

</div>
@endsection

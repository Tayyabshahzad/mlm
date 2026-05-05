@extends('demo.layout.app')
@section('title', 'My Saving ROI Report')

@section('content')
<div class="content d-flex flex-column flex-column-fluid" id="kt_content">

    {{-- Subheader --}}
    <div class="py-2 subheader py-lg-6 subheader-solid" id="kt_subheader">
        <div class="flex-wrap container-fluid d-flex align-items-center justify-content-between flex-sm-nowrap">
            <div class="d-flex align-items-baseline flex-wrap mr-5">
                <h5 class="text-dark font-weight-bold my-1 mr-5">My Saving ROI Report</h5>
                <ul class="breadcrumb breadcrumb-transparent breadcrumb-dot font-weight-bold font-size-sm p-0 my-2">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-muted">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('wallets.saving.roi') }}" class="text-muted">Savings ROI</a></li>
                    <li class="breadcrumb-item"><a href="#" class="text-muted">Report</a></li>
                </ul>
            </div>
        </div>
    </div>

    <div class="flex-column-fluid">
        <div class="container">

            {{-- Filters --}}
            <div class="card card-custom gutter-b">
                <div class="card-body py-4">
                    <form method="GET" action="{{ route('saving.roi.report') }}">
                        <div class="row align-items-end">
                            <div class="col-md-4">
                                <label class="font-weight-bold font-size-sm">From Date</label>
                                <input type="date" name="start_date" class="form-control form-control-sm form-control-solid"
                                    value="{{ request('start_date', $startDate->format('Y-m-d')) }}">
                            </div>
                            <div class="col-md-4">
                                <label class="font-weight-bold font-size-sm">To Date</label>
                                <input type="date" name="end_date" class="form-control form-control-sm form-control-solid"
                                    value="{{ request('end_date', $endDate->format('Y-m-d')) }}">
                            </div>
                            <div class="col-md-4 d-flex align-items-end mt-3 mt-md-0">
                                <button type="submit" class="btn btn-primary btn-sm font-weight-bold mr-2">
                                    <i class="la la-search"></i> Apply
                                </button>
                                <a href="{{ route('saving.roi.report') }}" class="btn btn-light btn-sm mr-2">Reset</a>
                                <a href="{{ route('saving.roi.report.export', request()->query()) }}"
                                   class="btn btn-success btn-sm font-weight-bold">
                                    <i class="la la-download"></i> Download
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Summary Cards --}}
            <div class="row gutter-b">
                <div class="col-md-6">
                    <div class="card card-custom bg-success text-white">
                        <div class="card-body py-5 text-center">
                            <div style="font-size:1.8rem;font-weight:700;">${{ number_format($totalAllTime, 2) }}</div>
                            <div class="font-size-sm mt-1">Total ROI Earned (All-Time)</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card card-custom bg-primary text-white">
                        <div class="card-body py-5 text-center">
                            <div style="font-size:1.8rem;font-weight:700;">${{ number_format($totalRoi, 2) }}</div>
                            <div class="font-size-sm mt-1">ROI in Selected Period</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Chart --}}
            <div class="card card-custom gutter-b">
                <div class="card-header border-0 py-4">
                    <h3 class="card-title font-weight-bolder text-dark">Daily ROI Chart</h3>
                </div>
                <div class="card-body pt-0">
                    <div id="userRoiChart" style="min-height:280px;"></div>
                </div>
            </div>

            {{-- Entries Table --}}
            <div class="card card-custom gutter-b">
                <div class="card-header border-0 py-4">
                    <h3 class="card-title font-weight-bolder text-dark">ROI History</h3>
                </div>
                <div class="card-body pt-0">
                    @if($entries->isEmpty())
                        <p class="text-muted">No ROI payments found for the selected period.</p>
                    @else
                    <div class="table-responsive">
                        <table class="table table-hover table-head-custom table-vertical-center">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Amount</th>
                                    <th>Description</th>
                                    <th>Date & Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($entries as $i => $entry)
                                <tr>
                                    <td>{{ ($entries->currentPage() - 1) * $entries->perPage() + $i + 1 }}</td>
                                    <td class="font-weight-bold text-success">${{ number_format($entry->balance, 2) }}</td>
                                    <td class="text-muted">{{ $entry->description ?? 'Daily saving account ROI' }}</td>
                                    <td>{{ $entry->created_at->format('d M Y H:i') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    {{ $entries->links('pagination::bootstrap-5') }}
                    @endif
                </div>
            </div>

        </div>
    </div>
</div>

@section('page_js')
<script>
(function () {
    const dates  = @json($chartDates);
    const totals = @json($chartTotals);

    if (!dates.length) {
        document.getElementById('userRoiChart').innerHTML =
            '<p class="text-muted text-center pt-10">No data for selected period.</p>';
        return;
    }

    const options = {
        series: [{ name: 'ROI ($)', data: totals }],
        chart: {
            type: 'bar',
            height: 280,
            fontFamily: "'Poppins', sans-serif",
            background: 'transparent',
            toolbar: { show: true, tools: { download: true, zoom: true, reset: true } },
            animations: { enabled: true, easing: 'easeinout', speed: 600 },
        },
        colors: ['#3699FF'],
        plotOptions: {
            bar: { columnWidth: '50%', borderRadius: 5, dataLabels: { position: 'top' } }
        },
        dataLabels: {
            enabled: true,
            formatter: v => '$' + v.toFixed(2),
            offsetY: -18,
            style: { fontSize: '11px', colors: ['#304758'] }
        },
        xaxis: {
            categories: dates,
            labels: { rotate: -45, style: { fontSize: '11px' } }
        },
        yaxis: {
            labels: { formatter: v => '$' + v.toFixed(2) }
        },
        tooltip: {
            y: { formatter: v => '$' + v.toFixed(2) }
        },
        grid: { borderColor: '#f1f1f1' },
    };

    new ApexCharts(document.getElementById('userRoiChart'), options).render();
})();
</script>
@endsection
@endsection

@extends('demo.layout.app')
@section('title', 'Digital Cards')

@section('content')
<div class="content d-flex flex-column flex-column-fluid" id="kt_content">

    <div class="py-2 subheader py-lg-6 subheader-solid" id="kt_subheader">
        <div class="flex-wrap container-fluid d-flex align-items-center justify-content-between flex-sm-nowrap">
            <div class="d-flex align-items-baseline">
                <h5 class="my-1 mr-5 text-dark font-weight-bold">Digital Cards</h5>
                <ul class="p-0 my-2 breadcrumb breadcrumb-transparent breadcrumb-dot font-weight-bold font-size-sm">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-muted">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="#" class="text-muted">Digital Cards</a></li>
                </ul>
            </div>
            <div>
                <a href="{{ route('admin.cards.failed') }}" class="btn btn-light-danger btn-sm font-weight-bold mr-2">
                    Failed Attempts <span class="badge badge-danger ml-1">{{ $failedLast7 }}</span>
                </a>
                <form method="POST" action="{{ route('admin.cards.run-expiry') }}" class="d-inline">
                    @csrf
                    <button class="btn btn-light-dark btn-sm font-weight-bold" onclick="return confirm('Expire all unused cards past their expiry date and refund the generators?')">Run Expiry Now</button>
                </form>
            </div>
        </div>
    </div>

    <div class="flex-column-fluid">
        <div class="container-fluid">

            <div class="row">
                @foreach(['available' => ['Available', 'primary'], 'shared' => ['Shared', 'info'], 'reserved' => ['Pending Review', 'warning'], 'used' => ['Used', 'success'], 'expired' => ['Expired', 'danger'], 'cancelled' => ['Cancelled', 'dark']] as $key => [$label, $color])
                    <div class="col-6 col-md-4 col-xl-2">
                        <a href="{{ route('admin.cards.index', ['status' => $key]) }}" class="card card-custom bg-light-{{ $color }} gutter-b d-block">
                            <div class="card-body py-5">
                                <span class="text-{{ $color }} font-weight-bold d-block">{{ $label }}</span>
                                <span class="font-weight-bolder font-size-h4 text-dark">{{ $summary[$key]->total ?? 0 }}</span>
                                <span class="text-muted small d-block">${{ number_format($summary[$key]->amount ?? 0, 2) }}</span>
                            </div>
                        </a>
                    </div>
                @endforeach
            </div>

            <div class="card card-custom gutter-b">
                <div class="card-body">
                    <form method="GET" class="row">
                        <div class="col-md-3 form-group">
                            <label class="small font-weight-bold">Card ID / number</label>
                            <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Card ID or last 4+ digits">
                        </div>
                        <div class="col-md-2 form-group">
                            <label class="small font-weight-bold">Generator</label>
                            <input type="text" name="generator" value="{{ request('generator') }}" class="form-control" placeholder="Name / username">
                        </div>
                        <div class="col-md-2 form-group">
                            <label class="small font-weight-bold">Recipient</label>
                            <input type="text" name="recipient" value="{{ request('recipient') }}" class="form-control" placeholder="Name / username">
                        </div>
                        <div class="col-md-1 form-group">
                            <label class="small font-weight-bold">Status</label>
                            <select name="status" class="form-control">
                                <option value="">All</option>
                                @foreach(\App\Models\DigitalCard::STATUSES as $s)
                                    <option value="{{ $s }}" @selected(request('status') === $s)>{{ ucfirst($s) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-1 form-group">
                            <label class="small font-weight-bold">From</label>
                            <input type="date" name="from" value="{{ request('from') }}" class="form-control">
                        </div>
                        <div class="col-md-1 form-group">
                            <label class="small font-weight-bold">To</label>
                            <input type="date" name="to" value="{{ request('to') }}" class="form-control">
                        </div>
                        <div class="col-md-2 form-group d-flex align-items-end">
                            <button class="btn btn-primary mr-2">Filter</button>
                            <a href="{{ route('admin.cards.index') }}" class="btn btn-light">Reset</a>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-hover table-head-custom table-vertical-center">
                            <thead>
                                <tr>
                                    <th>Card</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Generator</th>
                                    <th>Recipient</th>
                                    <th>Instalment</th>
                                    <th>Generated</th>
                                    <th>Expires</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($cards as $card)
                                    <tr>
                                        <td class="text-nowrap">
                                            <span class="font-weight-bold">{{ $card->maskedNumber() }}</span><br>
                                            <span class="text-muted small">{{ $card->reference }}</span>
                                        </td>
                                        <td class="font-weight-bolder">${{ number_format($card->amount, 2) }}</td>
                                        <td><span class="badge {{ $card->statusBadgeClass() }}">{{ $card->statusLabel() }}</span></td>
                                        <td>{{ $card->generator?->name }}<br><span class="text-muted small">@ {{ $card->generator?->username }}</span></td>
                                        <td>
                                            @if($card->recipient)
                                                {{ $card->recipient->name }}<br><span class="text-muted small">@ {{ $card->recipient->username }}</span>
                                            @else <span class="text-muted">—</span> @endif
                                        </td>
                                        <td>{{ $card->instalment ? '#' . $card->instalment->instalment_number : '—' }}</td>
                                        <td class="text-nowrap small">{{ $card->created_at->format('d M Y H:i') }}</td>
                                        <td class="text-nowrap small">{{ $card->expires_at->format('d M Y') }}</td>
                                        <td><a href="{{ route('admin.cards.show', $card) }}" class="btn btn-sm btn-light-primary">Details</a></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="9" class="text-center text-muted py-6">No cards found.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">{{ $cards->links() }}</div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection

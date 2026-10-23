@extends('demo.layout.app')
@section('title', 'My Digital Cards')

@section('content')
<div class="content d-flex flex-column flex-column-fluid" id="kt_content">

    <div class="py-2 subheader py-lg-6 subheader-solid" id="kt_subheader">
        <div class="flex-wrap container-fluid d-flex align-items-center justify-content-between flex-sm-nowrap">
            <div class="d-flex align-items-baseline">
                <h5 class="my-1 mr-5 text-dark font-weight-bold">My Digital Cards</h5>
                <ul class="p-0 my-2 breadcrumb breadcrumb-transparent breadcrumb-dot font-weight-bold font-size-sm">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-muted">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="#" class="text-muted">Digital Cards</a></li>
                </ul>
            </div>
            <a href="{{ route('cards.received') }}" class="btn btn-light-primary btn-sm font-weight-bold">Received Cards</a>
        </div>
    </div>

    <div class="flex-column-fluid">
        <div class="container">

            <div class="row">
                {{-- Generate --}}
                <div class="col-lg-5">
                    <div class="card card-custom gutter-b">
                        <div class="card-header border-0 pt-5">
                            <h3 class="card-title font-weight-bolder text-dark">Generate a Card</h3>
                        </div>
                        <div class="card-body pt-2">
                            <div class="d-flex align-items-center bg-light-primary rounded p-4 mb-5">
                                <div class="flex-grow-1">
                                    <span class="text-muted d-block">Online wallet balance</span>
                                    <span class="font-weight-bolder font-size-h3 text-primary">${{ number_format($balance, 2) }}</span>
                                </div>
                            </div>

                            @if(!$enabled)
                                <div class="alert alert-light-warning">Card generation is currently disabled.</div>
                            @else
                                <p class="text-muted">
                                    Cards are generated from your <strong>Online Wallet</strong>. The card is valid for {{ \App\Models\DigitalCard::validityLabel() }}
                                    and can be assigned to yourself or to one eligible downline member on a saving instalment plan.
                                </p>
                                <a href="{{ route('wallets.online') }}" class="btn btn-dark btn-block font-weight-bold">
                                    Go to Online Wallet to Generate a Card
                                </a>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Summary --}}
                <div class="col-lg-7">
                    <div class="row">
                        @foreach(['available' => ['Available', 'primary'], 'shared' => ['Shared', 'info'], 'reserved' => ['Pending Review', 'warning'], 'used' => ['Used', 'success'], 'expired' => ['Expired', 'danger'], 'cancelled' => ['Cancelled', 'dark']] as $key => [$label, $color])
                            <div class="col-6 col-md-4">
                                <div class="card card-custom bg-light-{{ $color }} gutter-b">
                                    <div class="card-body py-5">
                                        <span class="text-{{ $color }} font-weight-bold d-block">{{ $label }}</span>
                                        <span class="font-weight-bolder font-size-h4">{{ $stats[$key]->total ?? 0 }}</span>
                                        <span class="text-muted small d-block">${{ number_format($stats[$key]->amount ?? 0, 2) }}</span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Generated cards list --}}
            <div class="card card-custom gutter-b">
                <div class="card-header border-0 py-5">
                    <h3 class="card-title font-weight-bolder text-dark">Cards I Generated</h3>
                </div>
                <div class="card-body pt-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-head-custom table-vertical-center">
                            <thead>
                                <tr>
                                    <th>Card</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Recipient</th>
                                    <th>Generated</th>
                                    <th>Expires</th>
                                    <th class="text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($generated as $card)
                                    <tr>
                                        <td class="font-weight-bold text-nowrap">{{ $card->maskedNumber() }}</td>
                                        <td class="font-weight-bolder">${{ number_format($card->amount, 2) }}</td>
                                        <td><span class="badge {{ $card->statusBadgeClass() }}">{{ $card->statusLabel() }}</span></td>
                                        <td>
                                            @if($card->recipient)
                                                {{ $card->recipient->name }}<br>
                                                <span class="text-muted small">@ {{ $card->recipient->username }} · {{ $card->shared_at?->format('d M Y H:i') }}</span>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td class="text-nowrap">{{ $card->created_at->format('d M Y H:i') }}</td>
                                        <td class="text-nowrap">{{ $card->expires_at->format('d M Y') }}</td>
                                        <td class="text-right text-nowrap">
                                            <a href="{{ route('cards.show', $card) }}" class="btn btn-sm btn-light-primary">
                                                {{ $card->status === 'available' ? 'View / Share' : 'View' }}
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="7" class="text-center text-muted py-6">You have not generated any cards yet.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">{{ $generated->links() }}</div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection

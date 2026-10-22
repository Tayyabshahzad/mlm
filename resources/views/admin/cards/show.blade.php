@extends('demo.layout.app')
@section('title', 'Digital Card Details')

@section('content')
<div class="content d-flex flex-column flex-column-fluid" id="kt_content">

    <div class="py-2 subheader py-lg-6 subheader-solid" id="kt_subheader">
        <div class="flex-wrap container-fluid d-flex align-items-center justify-content-between flex-sm-nowrap">
            <div class="d-flex align-items-baseline">
                <h5 class="my-1 mr-5 text-dark font-weight-bold">Card {{ $card->maskedNumber() }}</h5>
                <ul class="p-0 my-2 breadcrumb breadcrumb-transparent breadcrumb-dot font-weight-bold font-size-sm">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-muted">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.cards.index') }}" class="text-muted">Digital Cards</a></li>
                    <li class="breadcrumb-item"><a href="#" class="text-muted">Details</a></li>
                </ul>
            </div>
        </div>
    </div>

    <div class="flex-column-fluid">
        <div class="container">
            <div class="row">
                <div class="col-xl-6">
                    <div class="card card-custom gutter-b">
                        <div class="card-body text-center">
                            <img src="{{ route('cards.image', $card) }}" alt="Digital card" style="width:100%;max-width:560px;border-radius:16px;box-shadow:0 10px 26px rgba(0,0,0,.25)">
                            <p class="text-muted small mt-3 mb-0">PIN hidden for administrators.</p>
                        </div>
                    </div>

                    @if($pinLock)
                        <div class="card card-custom gutter-b border border-warning">
                            <div class="card-header border-0 pt-5">
                                <h3 class="card-title font-weight-bolder text-warning">PIN Locked</h3>
                            </div>
                            <div class="card-body pt-2">
                                <p class="mb-4">
                                    <strong>{{ $card->recipient?->name }}</strong> (@ {{ $card->recipient?->username }}) entered a wrong PIN
                                    <strong>{{ $pinLock['attempts'] }}</strong> time(s). The card is locked until
                                    <strong>{{ $pinLock['unlocks_at']->format('d M Y H:i') }}</strong>
                                    ({{ $pinLock['unlocks_at']->diffForHumans() }}).
                                </p>
                                <form method="POST" action="{{ route('admin.cards.reset-pin-lock', $card) }}">
                                    @csrf
                                    <div class="form-group">
                                        <label>Reason (optional, recorded in the audit log)</label>
                                        <input type="text" name="reason" maxlength="500" class="form-control" placeholder="e.g. Member verified by phone">
                                    </div>
                                    <button class="btn btn-warning font-weight-bold" onclick="return confirm('Reset the PIN lock for this card?')">
                                        Reset PIN Lock
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endif

                    @if(in_array($card->status, ['available', 'shared'], true))
                        <div class="card card-custom gutter-b border border-danger">
                            <div class="card-header border-0 pt-5"><h3 class="card-title font-weight-bolder text-danger">Cancel Card</h3></div>
                            <div class="card-body pt-2">
                                <form method="POST" action="{{ route('admin.cards.cancel', $card) }}">
                                    @csrf
                                    <div class="form-group">
                                        <label>Reason (recorded in the audit log)</label>
                                        <textarea name="reason" rows="2" class="form-control" required maxlength="500"></textarea>
                                    </div>
                                    <button class="btn btn-danger" onclick="return confirm('Cancel this card and refund ${{ number_format($card->amount, 2) }} to {{ $card->generator->username }}?')">
                                        Cancel &amp; Refund Generator
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endif
                </div>

                <div class="col-xl-6">
                    <div class="card card-custom gutter-b">
                        <div class="card-header border-0 pt-5">
                            <h3 class="card-title font-weight-bolder">Card Record</h3>
                            <div class="card-toolbar"><span class="badge {{ $card->statusBadgeClass() }} px-4 py-2">{{ $card->statusLabel() }}</span></div>
                        </div>
                        <div class="card-body pt-2">
                            <table class="table table-sm">
                                <tr><td class="text-muted" width="40%">Card ID</td><td class="small">{{ $card->reference }}</td></tr>
                                <tr><td class="text-muted">Card number</td><td>{{ $card->formattedNumber() }}</td></tr>
                                <tr><td class="text-muted">Amount</td><td class="font-weight-bolder">${{ number_format($card->amount, 2) }}</td></tr>
                                <tr><td class="text-muted">Generated by</td><td>{{ $card->generator->name }} (@ {{ $card->generator->username }})<br><span class="small text-muted">{{ $card->created_at->format('d M Y H:i:s') }}</span></td></tr>
                                <tr><td class="text-muted">Wallet debit</td>
                                    <td>
                                        @if($card->generationWallet)
                                            #{{ $card->generationWallet->id }} · ${{ number_format(abs($card->generationWallet->balance), 2) }} · {{ $card->generationWallet->created_at->format('d M Y H:i') }}
                                        @else — @endif
                                    </td></tr>
                                <tr><td class="text-muted">Recipient</td>
                                    <td>
                                        @if($card->recipient)
                                            {{ $card->recipient->name }} (@ {{ $card->recipient->username }})<br>
                                            <span class="small text-muted">Shared by {{ $card->sharer?->username }} on {{ $card->shared_at?->format('d M Y H:i:s') }} via {{ $card->share_method }}</span>
                                        @else — @endif
                                    </td></tr>
                                <tr><td class="text-muted">Instalment</td>
                                    <td>
                                        @if($card->instalment)
                                            <a href="{{ route('admin.saving.show', $card->instalment->user) }}">#{{ $card->instalment->instalment_number }} — {{ $card->instalment->user->username }}</a>
                                            ({{ $card->instalment->status }})
                                        @else — @endif
                                    </td></tr>
                                <tr><td class="text-muted">Reserved / Used</td><td>{{ $card->reserved_at?->format('d M Y H:i') ?? '—' }} / {{ $card->used_at?->format('d M Y H:i') ?? '—' }}</td></tr>
                                <tr><td class="text-muted">Expires</td><td>{{ $card->expires_at->format('d M Y H:i') }}</td></tr>
                                @if($card->cancelled_at)
                                    <tr><td class="text-muted">Cancelled</td><td>{{ $card->cancelled_at->format('d M Y H:i') }} by {{ $card->canceller?->username }}<br><span class="small">{{ $card->cancel_reason }}</span></td></tr>
                                @endif
                                @if($card->refundWallet)
                                    <tr><td class="text-muted">Refund</td><td>Wallet #{{ $card->refundWallet->id }} · ${{ number_format($card->refundWallet->balance, 2) }} · {{ $card->refundWallet->created_at->format('d M Y H:i') }}</td></tr>
                                @endif
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card card-custom gutter-b">
                <div class="card-header border-0 py-5">
                    <h3 class="card-title font-weight-bolder text-dark">Complete Audit Trail</h3>
                </div>
                <div class="card-body pt-0">
                    @include('cards._history', ['history' => $history, 'showIp' => true])
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@extends('demo.layout.app')
@section('title', 'Received Cards')

@section('content')
<div class="content d-flex flex-column flex-column-fluid" id="kt_content">

    <div class="py-2 subheader py-lg-6 subheader-solid" id="kt_subheader">
        <div class="flex-wrap container-fluid d-flex align-items-center justify-content-between flex-sm-nowrap">
            <div class="d-flex align-items-baseline">
                <h5 class="my-1 mr-5 text-dark font-weight-bold">Received Cards</h5>
                <ul class="p-0 my-2 breadcrumb breadcrumb-transparent breadcrumb-dot font-weight-bold font-size-sm">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-muted">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('cards.index') }}" class="text-muted">Digital Cards</a></li>
                    <li class="breadcrumb-item"><a href="#" class="text-muted">Received</a></li>
                </ul>
            </div>
            <a href="{{ route('cards.index') }}" class="btn btn-light-primary btn-sm font-weight-bold">My Cards</a>
        </div>
    </div>

    <div class="flex-column-fluid">
        <div class="container">

            @foreach($cards as $card)
                @if(in_array($card->reference, $unreadRefs, true))
                    <div class="alert alert-custom alert-light-success fade show mb-4" role="alert">
                        <div class="alert-text">
                            You have received a new card from <strong>{{ $card->sharer?->name ?? $card->generator?->name }}</strong>
                            — ${{ number_format($card->amount, 2) }}.
                            <a href="{{ route('cards.show', $card) }}" class="font-weight-bold ml-2">Open card →</a>
                        </div>
                    </div>
                @endif
            @endforeach

            <div class="card card-custom gutter-b">
                <div class="card-header border-0 py-5">
                    <h3 class="card-title font-weight-bolder text-dark">Cards Assigned to Me</h3>
                </div>
                <div class="card-body pt-0">
                    <p class="text-muted">
                        These cards are assigned to your account only. Open a card and use it to pay your next saving instalment.
                        The card amount must exactly match the instalment total.
                    </p>
                    <div class="table-responsive">
                        <table class="table table-hover table-head-custom table-vertical-center">
                            <thead>
                                <tr>
                                    <th>Card ID</th>
                                    <th>From</th>
                                    <th>Amount</th>
                                    <th>Received</th>
                                    <th>Expires</th>
                                    <th>Status</th>
                                    <th class="text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($cards as $card)
                                    <tr>
                                        <td class="text-nowrap">
                                            <span class="font-weight-bold">{{ $card->maskedNumber() }}</span>
                                            @if(in_array($card->reference, $unreadRefs, true))
                                                <span class="badge badge-success ml-1">New</span>
                                            @endif
                                        </td>
                                        <td>
                                            {{ $card->sharer?->name ?? $card->generator?->name }}<br>
                                            <span class="text-muted small">@ {{ $card->sharer?->username ?? $card->generator?->username }}</span>
                                        </td>
                                        <td class="font-weight-bolder">${{ number_format($card->amount, 2) }}</td>
                                        <td class="text-nowrap">{{ $card->shared_at?->format('d M Y H:i') }}</td>
                                        <td class="text-nowrap">{{ $card->expires_at->format('d M Y') }}</td>
                                        <td>
                                            <span class="badge {{ $card->statusBadgeClass() }}">
                                                {{ $card->isUsable() ? 'Available to use' : $card->statusLabel() }}
                                            </span>
                                            @if($card->instalment)
                                                <div class="small text-muted mt-1">Instalment #{{ $card->instalment->instalment_number }}</div>
                                            @endif
                                        </td>
                                        <td class="text-right text-nowrap">
                                            <a href="{{ route('cards.show', $card) }}" class="btn btn-sm {{ $card->isUsable() ? 'btn-primary' : 'btn-light-primary' }}">
                                                {{ $card->isUsable() ? 'Open & Use' : 'View' }}
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="7" class="text-center text-muted py-6">You have not received any cards yet.</td></tr>
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

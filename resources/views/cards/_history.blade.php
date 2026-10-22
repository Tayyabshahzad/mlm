{{-- Chronological card history. Expects $history (collection of DigitalCardEvent) and optional $showIp. --}}
@php
    $eventColors = [
        'generated' => 'primary', 'shared' => 'info', 'email_sent' => 'info', 'downloaded' => 'secondary',
        'payment_submitted' => 'warning', 'payment_confirmed' => 'success', 'payment_rejected' => 'danger',
        'payment_reversed' => 'danger', 'verification_failed' => 'danger', 'expired' => 'dark',
        'cancelled' => 'dark', 'refunded' => 'primary', 'pin_lock_reset' => 'warning',
    ];
@endphp
<div class="table-responsive">
    <table class="table table-sm table-vertical-center">
        <thead>
            <tr class="text-muted">
                <th style="width:160px">Date &amp; Time</th>
                <th>Event</th>
                <th>By</th>
                <th>Details</th>
                @if(!empty($showIp))<th>IP</th>@endif
            </tr>
        </thead>
        <tbody>
            @forelse($history as $ev)
                <tr>
                    <td class="text-nowrap small">{{ $ev->created_at->format('d M Y H:i:s') }}</td>
                    <td>
                        <span class="label label-inline label-light-{{ $eventColors[$ev->event] ?? 'secondary' }} font-weight-bold">{{ $ev->label() }}</span>
                    </td>
                    <td class="small">
                        @if($ev->actor)
                            {{ $ev->actor->name }} <span class="text-muted">@ {{ $ev->actor->username }}</span>
                        @else
                            <span class="text-muted">System</span>
                        @endif
                    </td>
                    <td class="small">
                        {{ $ev->description }}
                        @if($ev->toUser && $ev->event === 'shared')
                            <br><span class="text-muted">Recipient:</span> {{ $ev->toUser->name }} (@ {{ $ev->toUser->username }})
                            · <span class="text-muted">Method:</span> {{ ucfirst($ev->share_method) }}
                        @endif
                        @if($ev->instalment)
                            <br><span class="text-muted">Instalment:</span> #{{ $ev->instalment->instalment_number }}
                        @endif
                        @if($ev->wallet_id)
                            <br><span class="text-muted">Wallet entry:</span> #{{ $ev->wallet_id }}
                        @endif
                        @if($ev->status_from || $ev->status_to)
                            <br><span class="text-muted">Status:</span> {{ $ev->status_from ?? '—' }} → {{ $ev->status_to ?? '—' }}
                        @endif
                    </td>
                    @if(!empty($showIp))<td class="small text-muted">{{ $ev->ip_address ?? '—' }}</td>@endif
                </tr>
            @empty
                <tr><td colspan="5" class="text-center text-muted py-4">No history recorded.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

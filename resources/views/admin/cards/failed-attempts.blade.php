@extends('demo.layout.app')
@section('title', 'Card Failed Attempts')

@section('content')
<div class="content d-flex flex-column flex-column-fluid" id="kt_content">

    <div class="py-2 subheader py-lg-6 subheader-solid" id="kt_subheader">
        <div class="flex-wrap container-fluid d-flex align-items-center justify-content-between flex-sm-nowrap">
            <div class="d-flex align-items-baseline">
                <h5 class="my-1 mr-5 text-dark font-weight-bold">Failed Verification / Payment Attempts</h5>
                <ul class="p-0 my-2 breadcrumb breadcrumb-transparent breadcrumb-dot font-weight-bold font-size-sm">
                    <li class="breadcrumb-item"><a href="{{ route('admin.cards.index') }}" class="text-muted">Digital Cards</a></li>
                    <li class="breadcrumb-item"><a href="#" class="text-muted">Failed Attempts</a></li>
                </ul>
            </div>
        </div>
    </div>

    <div class="flex-column-fluid">
        <div class="container">
            <div class="card card-custom gutter-b">
                <div class="card-body">
                    <form method="GET" class="form-inline mb-5">
                        <input type="text" name="user" value="{{ request('user') }}" class="form-control mr-2" placeholder="Filter by username">
                        <button class="btn btn-primary mr-2">Filter</button>
                        <a href="{{ route('admin.cards.failed') }}" class="btn btn-light">Reset</a>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-hover table-head-custom table-vertical-center">
                            <thead>
                                <tr>
                                    <th>When</th>
                                    <th>User</th>
                                    <th>Card</th>
                                    <th>Reason</th>
                                    <th>Message</th>
                                    <th>IP</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($events as $ev)
                                    <tr>
                                        <td class="text-nowrap small">{{ $ev->created_at->format('d M Y H:i:s') }}</td>
                                        <td>{{ $ev->actor?->name }}<br><span class="text-muted small">@ {{ $ev->actor?->username }}</span></td>
                                        <td>
                                            @if($ev->card)
                                                <a href="{{ route('admin.cards.show', $ev->card) }}">{{ $ev->card->maskedNumber() }}</a>
                                                @if($ev->card->recipient_id && (int) $ev->card->recipient_id !== (int) $ev->actor_id)
                                                    <br><span class="badge badge-light-danger">Not the assigned recipient</span>
                                                @endif
                                            @else
                                                <span class="text-muted small">Unknown: {{ $ev->meta['reference'] ?? '—' }}</span>
                                            @endif
                                        </td>
                                        <td><span class="badge badge-light-danger">{{ $ev->meta['reason'] ?? '—' }}</span></td>
                                        <td class="small">{{ $ev->description }}</td>
                                        <td class="small text-muted">{{ $ev->ip_address ?? '—' }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="text-center text-muted py-6">No failed attempts recorded.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">{{ $events->links() }}</div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

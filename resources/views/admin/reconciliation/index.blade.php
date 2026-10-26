@extends('demo.layout.app')
@section('title', 'Bank Reconciliation')

@section('content')
<div class="content d-flex flex-column flex-column-fluid" id="kt_content">

    <div class="py-2 subheader py-lg-6 subheader-solid" id="kt_subheader">
        <div class="flex-wrap container-fluid d-flex align-items-center justify-content-between flex-sm-nowrap">
            <div class="d-flex align-items-baseline">
                <h5 class="my-1 mr-5 text-dark font-weight-bold">Bank Reconciliation</h5>
                <ul class="p-0 my-2 breadcrumb breadcrumb-transparent breadcrumb-dot font-weight-bold font-size-sm">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-muted">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="#" class="text-muted">Bank Reconciliation</a></li>
                </ul>
            </div>
            @role('admin')
                <a href="{{ route('admin.office-admins.index') }}" class="btn btn-light-primary btn-sm font-weight-bold">Manage Staff Roles</a>
            @endrole
        </div>
    </div>

    <div class="flex-column-fluid">
        <div class="container-fluid">

            {{-- Daily summary --}}
            <div class="row">
                <div class="col-6 col-md-3">
                    <a href="{{ route('reconciliation.index', ['tab' => 'submitted']) }}" class="card card-custom bg-light-warning gutter-b d-block">
                        <div class="card-body py-5">
                            <span class="text-warning font-weight-bold d-block">Awaiting Verification</span>
                            <span class="font-weight-bolder font-size-h3 text-dark">{{ $stats['awaiting'] }}</span>
                        </div>
                    </a>
                </div>
                <div class="col-6 col-md-3">
                    <a href="{{ route('reconciliation.index', ['tab' => 'due', 'due_until' => now()->toDateString()]) }}" class="card card-custom bg-light-danger gutter-b d-block">
                        <div class="card-body py-5">
                            <span class="text-danger font-weight-bold d-block">Due / Overdue (unpaid)</span>
                            <span class="font-weight-bolder font-size-h3 text-dark">{{ $stats['due_today'] }}</span>
                        </div>
                    </a>
                </div>
                <div class="col-6 col-md-3">
                    <a href="{{ route('reconciliation.index', ['tab' => 'reconciled', 'from' => now()->toDateString()]) }}" class="card card-custom bg-light-success gutter-b d-block">
                        <div class="card-body py-5">
                            <span class="text-success font-weight-bold d-block">Verified Today</span>
                            <span class="font-weight-bolder font-size-h3 text-dark">{{ $stats['verified_today'] }}</span>
                            <span class="text-muted small d-block">${{ number_format($stats['amount_today'], 2) }}</span>
                        </div>
                    </a>
                </div>
                <div class="col-6 col-md-3">
                    <a href="{{ route('reconciliation.index', ['tab' => 'audit']) }}" class="card card-custom bg-light-dark gutter-b d-block">
                        <div class="card-body py-5">
                            <span class="text-dark font-weight-bold d-block">Blocked Attempts Today</span>
                            <span class="font-weight-bolder font-size-h3 text-dark">{{ $stats['blocked_today'] }}</span>
                            <span class="text-muted small d-block">duplicates, wrong amounts…</span>
                        </div>
                    </a>
                </div>
            </div>

            <div class="card card-custom gutter-b">
                <div class="card-header card-header-tabs-line">
                    <div class="card-toolbar">
                        <ul class="nav nav-tabs nav-bold nav-tabs-line">
                            <li class="nav-item"><a class="nav-link {{ $tab === 'submitted' ? 'active' : '' }}" href="{{ route('reconciliation.index', ['tab' => 'submitted']) }}">Awaiting Verification <span class="badge badge-warning ml-1">{{ $stats['awaiting'] }}</span></a></li>
                            <li class="nav-item"><a class="nav-link {{ $tab === 'due' ? 'active' : '' }}" href="{{ route('reconciliation.index', ['tab' => 'due']) }}">Due / Unpaid</a></li>
                            <li class="nav-item"><a class="nav-link {{ $tab === 'reconciled' ? 'active' : '' }}" href="{{ route('reconciliation.index', ['tab' => 'reconciled']) }}">Reconciled Payments</a></li>
                            <li class="nav-item"><a class="nav-link {{ $tab === 'audit' ? 'active' : '' }}" href="{{ route('reconciliation.index', ['tab' => 'audit']) }}">Audit Log</a></li>
                        </ul>
                    </div>
                </div>
                <div class="card-body">

                    {{-- Filters --}}
                    <form method="GET" class="form-inline mb-6">
                        <input type="hidden" name="tab" value="{{ $tab }}">
                        <input type="text" name="search" value="{{ $search }}" class="form-control mr-2 mb-2" style="min-width:260px"
                               placeholder="{{ in_array($tab, ['submitted', 'reconciled']) ? 'Name, username, phone or bank ref' : 'Name, username or phone' }}">
                        @if($tab === 'due')
                            <label class="mr-2 mb-2 small font-weight-bold">Due until</label>
                            <input type="date" name="due_until" value="{{ request('due_until', now()->addDays(7)->toDateString()) }}" class="form-control mr-2 mb-2">
                        @endif
                        @if($tab === 'reconciled')
                            <label class="mr-2 mb-2 small font-weight-bold">Bank date</label>
                            <input type="date" name="from" value="{{ request('from') }}" class="form-control mr-1 mb-2">
                            <input type="date" name="to" value="{{ request('to') }}" class="form-control mr-2 mb-2">
                        @endif
                        <button class="btn btn-primary mr-2 mb-2">Search</button>
                        <a href="{{ route('reconciliation.index', ['tab' => $tab]) }}" class="btn btn-light mb-2">Reset</a>
                    </form>

                    <div class="table-responsive">
                        @if($tab === 'submitted')
                            <p class="text-muted small">Members who submitted a bank payment. Find each one in today's bank statement, then <strong>Verify</strong> (paid) or <strong>Reject</strong> (not received). Digital-card payments are not listed here.</p>
                            <table class="table table-hover table-head-custom table-vertical-center">
                                <thead><tr>
                                    <th>Member</th><th>Instalment</th><th>Amount Due</th><th>Member Submitted</th><th>Member's Txn ID</th><th>Method</th><th>Submitted</th><th>Proof</th><th class="text-right">Actions</th>
                                </tr></thead>
                                <tbody>
                                @forelse($rows as $inst)
                                    <tr>
                                        <td><span class="font-weight-bold">{{ $inst->user?->name }}</span><br><span class="text-muted small">@ {{ $inst->user?->username }} · {{ $inst->user?->phone_number }}</span></td>
                                        <td>#{{ $inst->instalment_number }}<br><span class="text-muted small">due {{ $inst->due_date->format('d M Y') }}</span></td>
                                        <td class="font-weight-bolder">${{ number_format($inst->required_total, 2) }}</td>
                                        <td class="{{ round((float) $inst->submitted_amount, 2) === round($inst->required_total, 2) ? 'text-success' : 'text-danger' }} font-weight-bold">${{ number_format((float) $inst->submitted_amount, 2) }}</td>
                                        <td class="small">{{ $inst->transaction_id ?? '—' }}</td>
                                        <td class="small">{{ ucfirst(str_replace('_', ' ', $inst->payment_method ?? '—')) }}</td>
                                        <td class="small text-nowrap">{{ $inst->submitted_at?->format('d M Y H:i') }}</td>
                                        <td>
                                            @if($inst->proofScreenshot())
                                                <a href="{{ $inst->proofScreenshot() }}" target="_blank" class="btn btn-xs btn-light-primary">View</a>
                                            @else <span class="text-muted">—</span> @endif
                                        </td>
                                        <td class="text-right text-nowrap">
                                            <button type="button" class="btn btn-sm btn-success js-open-pay" data-mode="verify"
                                                    data-action="{{ route('reconciliation.verify', $inst) }}"
                                                    data-member="{{ $inst->user?->name }} (@ {{ $inst->user?->username }})"
                                                    data-instalment="#{{ $inst->instalment_number }}"
                                                    data-amount="{{ number_format($inst->required_total, 2, '.', '') }}"
                                                    data-reference="{{ $inst->transaction_id }}">Verify</button>
                                            <button type="button" class="btn btn-sm btn-light-danger js-open-reject"
                                                    data-action="{{ route('reconciliation.reject', $inst) }}"
                                                    data-member="{{ $inst->user?->name }} (@ {{ $inst->user?->username }})"
                                                    data-instalment="#{{ $inst->instalment_number }}">Reject</button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="9" class="text-center text-muted py-6">No submissions awaiting verification.</td></tr>
                                @endforelse
                                </tbody>
                            </table>

                        @elseif($tab === 'due')
                            <p class="text-muted small">Unpaid instalments. If you find a member's payment in the bank but they did not submit it in the portal (e.g. cash / branch deposit), use <strong>Record Payment</strong>.</p>
                            <table class="table table-hover table-head-custom table-vertical-center">
                                <thead><tr><th>Member</th><th>Instalment</th><th>Due Date</th><th>Amount Due</th><th>Status</th><th class="text-right">Action</th></tr></thead>
                                <tbody>
                                @forelse($rows as $inst)
                                    <tr>
                                        <td><span class="font-weight-bold">{{ $inst->user?->name }}</span><br><span class="text-muted small">@ {{ $inst->user?->username }} · {{ $inst->user?->phone_number }}</span></td>
                                        <td>#{{ $inst->instalment_number }}</td>
                                        <td class="text-nowrap">{{ $inst->due_date->format('d M Y') }}</td>
                                        <td class="font-weight-bolder">${{ number_format($inst->required_total, 2) }}</td>
                                        <td>
                                            @if($inst->isOverdue() || $inst->status === 'missed')
                                                <span class="badge badge-light-danger">Overdue</span>
                                            @else
                                                <span class="badge badge-light-secondary">Pending</span>
                                            @endif
                                        </td>
                                        <td class="text-right">
                                            <button type="button" class="btn btn-sm btn-primary js-open-pay" data-mode="record"
                                                    data-action="{{ route('reconciliation.record', $inst) }}"
                                                    data-member="{{ $inst->user?->name }} (@ {{ $inst->user?->username }})"
                                                    data-instalment="#{{ $inst->instalment_number }}"
                                                    data-amount="{{ number_format($inst->required_total, 2, '.', '') }}"
                                                    data-reference="">Record Payment</button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="text-center text-muted py-6">No unpaid instalments in this range.</td></tr>
                                @endforelse
                                </tbody>
                            </table>

                        @elseif($tab === 'reconciled')
                            <table class="table table-hover table-head-custom table-vertical-center">
                                <thead><tr><th>Bank Date</th><th>Bank Ref</th><th>Member</th><th>Instalment</th><th>Amount</th><th>Source</th><th>Verified By</th><th>Status</th></tr></thead>
                                <tbody>
                                @forelse($rows as $rec)
                                    <tr>
                                        <td class="text-nowrap">{{ $rec->payment_date->format('d M Y') }}</td>
                                        <td class="font-weight-bold">{{ $rec->bank_reference }}
                                            @if($rec->bank_name)<br><span class="text-muted small">{{ $rec->bank_name }}</span>@endif
                                            @if($rec->user_submitted_reference && \App\Models\BankPaymentReconciliation::normaliseReference($rec->user_submitted_reference) !== \App\Models\BankPaymentReconciliation::normaliseReference($rec->bank_reference))
                                                <br><span class="text-muted small">member typed: {{ $rec->user_submitted_reference }}</span>
                                            @endif
                                        </td>
                                        <td>{{ $rec->user?->name }}<br><span class="text-muted small">@ {{ $rec->user?->username }}</span></td>
                                        <td>#{{ $rec->instalment?->instalment_number }}</td>
                                        <td class="font-weight-bolder">${{ number_format($rec->amount, 2) }}</td>
                                        <td class="small">{{ $rec->source === 'office_entry' ? 'Recorded by office' : 'Member submission' }}</td>
                                        <td class="small">{{ $rec->verifier?->name }}<br><span class="text-muted">{{ $rec->verified_at->format('d M Y H:i') }}</span></td>
                                        <td>
                                            @if($rec->status === 'verified')
                                                <span class="badge badge-light-success">Verified</span>
                                            @else
                                                <span class="badge badge-light-danger">Reversed</span>
                                                <div class="small text-muted">{{ $rec->reversed_at?->format('d M Y') }} {{ $rec->reversal_reason }}</div>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="8" class="text-center text-muted py-6">No reconciled payments found.</td></tr>
                                @endforelse
                                </tbody>
                            </table>

                        @else
                            <table class="table table-sm table-hover table-vertical-center">
                                <thead><tr class="text-muted"><th>When</th><th>Action</th><th>By</th><th>Member</th><th>Details</th><th>IP</th></tr></thead>
                                <tbody>
                                @forelse($rows as $log)
                                    <tr>
                                        <td class="text-nowrap small">{{ $log->created_at->format('d M Y H:i:s') }}</td>
                                        <td><span class="label label-inline font-weight-bold {{ $log->action === 'verification_blocked' ? 'label-light-danger' : ($log->action === 'bank_not_received' || $log->action === 'reconciliation_reversed' ? 'label-light-warning' : 'label-light-success') }}">{{ $log->label() }}</span></td>
                                        <td class="small">{{ $log->actor?->name ?? 'System' }}</td>
                                        <td class="small">{{ $log->member?->name }} <span class="text-muted">@ {{ $log->member?->username }}</span></td>
                                        <td class="small">{{ $log->description }}</td>
                                        <td class="small text-muted">{{ $log->ip_address ?? '—' }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="text-center text-muted py-6">No audit entries.</td></tr>
                                @endforelse
                                </tbody>
                            </table>
                        @endif
                    </div>
                    <div class="mt-4">{{ $rows->links() }}</div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Verify / Record payment modal --}}
<div class="modal fade" id="payModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form method="POST" id="payForm" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="payTitle">Verify Bank Payment</h5>
                    <button type="button" class="close" data-dismiss="modal"><i aria-hidden="true" class="ki ki-close"></i></button>
                </div>
                <div class="modal-body">
                    <div class="bg-light-primary rounded p-4 mb-5">
                        <div><span class="text-muted">Member:</span> <strong id="payMember"></strong></div>
                        <div><span class="text-muted">Instalment:</span> <strong id="payInstalment"></strong></div>
                        <div><span class="text-muted">Exact amount due:</span> <strong class="text-primary">$<span id="payDue"></span></strong></div>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold">Bank Transaction Reference <span class="text-danger">*</span></label>
                        <input type="text" name="bank_reference" id="payReference" maxlength="100" class="form-control" required placeholder="Exactly as shown on the bank statement">
                        <small class="text-muted">Each bank transaction can be used for only one instalment.</small>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-6">
                            <label class="font-weight-bold">Amount Received ($) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" min="0.01" name="amount" id="payAmount" class="form-control" required>
                        </div>
                        <div class="form-group col-6">
                            <label class="font-weight-bold">Bank Payment Date <span class="text-danger">*</span></label>
                            <input type="date" name="payment_date" class="form-control" required max="{{ now()->toDateString() }}" value="{{ now()->toDateString() }}">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold">Bank / Account</label>
                        <input type="text" name="bank_name" maxlength="100" class="form-control" placeholder="e.g. Meezan Bank – company account">
                    </div>
                    <div class="form-group" id="proofGroup" style="display:none">
                        <label class="font-weight-bold">Bank Statement / Slip (optional)</label>
                        <input type="file" name="proof" accept="image/*,application/pdf" class="form-control-file">
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold">Notes</label>
                        <textarea name="notes" rows="2" maxlength="500" class="form-control"></textarea>
                    </div>
                    <label class="checkbox checkbox-success">
                        <input type="checkbox" name="confirm_verified" value="1" required><span></span>
                        &nbsp;I have checked the bank statement and this payment has been received.
                    </label>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success font-weight-bold" id="paySubmit">Verify &amp; Mark Paid</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Reject modal --}}
<div class="modal fade" id="rejectModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form method="POST" id="rejectForm">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Reject — Not Received in Bank</h5>
                    <button type="button" class="close" data-dismiss="modal"><i aria-hidden="true" class="ki ki-close"></i></button>
                </div>
                <div class="modal-body">
                    <p><span class="text-muted">Member:</span> <strong id="rejectMember"></strong> · <span class="text-muted">Instalment:</span> <strong id="rejectInstalment"></strong></p>
                    <label class="font-weight-bold">Reason <span class="text-danger">*</span></label>
                    <textarea name="reason" rows="3" maxlength="500" class="form-control" required placeholder="e.g. No matching credit in bank statement of 23 Oct"></textarea>
                    <small class="text-muted">The instalment goes back to unpaid and the member sees this reason.</small>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger font-weight-bold">Reject</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('page_js')
<script>
$(function () {
    $(document).on('click', '.js-open-pay', function () {
        var b = $(this), record = b.data('mode') === 'record';
        $('#payForm').attr('action', b.data('action'))[0].reset();
        $('#payTitle').text(record ? 'Record Payment from Bank Statement' : 'Verify Bank Payment');
        $('#paySubmit').text(record ? 'Record & Mark Paid' : 'Verify & Mark Paid');
        $('#proofGroup').toggle(record);
        $('#payMember').text(b.data('member'));
        $('#payInstalment').text(b.data('instalment'));
        $('#payDue').text(b.data('amount'));
        $('#payAmount').val('');
        $('#payReference').val(b.data('reference') || '');
        $('#payModal').modal('show');
    });
    $(document).on('click', '.js-open-reject', function () {
        var b = $(this);
        $('#rejectForm').attr('action', b.data('action'))[0].reset();
        $('#rejectMember').text(b.data('member'));
        $('#rejectInstalment').text(b.data('instalment'));
        $('#rejectModal').modal('show');
    });
    $('#payForm').on('submit', function () { $('#paySubmit').prop('disabled', true); });
});
</script>
@endsection

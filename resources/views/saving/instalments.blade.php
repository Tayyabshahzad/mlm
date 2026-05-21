@extends('demo.layout.app')
@section('title', 'My Saving Plan')

@section('content')
<div class="content d-flex flex-column flex-column-fluid" id="kt_content">

    {{-- Subheader --}}
    <div class="py-2 subheader py-lg-6 subheader-solid" id="kt_subheader">
        <div class="flex-wrap container-fluid d-flex align-items-center justify-content-between flex-sm-nowrap">
            <div class="flex-wrap mr-1 d-flex align-items-center">
                <div class="flex-wrap mr-5 d-flex align-items-baseline">
                    <h5 class="my-1 mr-5 text-dark font-weight-bold">Saving Plan — Instalment Tracker</h5>
                    <ul class="p-0 my-2 breadcrumb breadcrumb-transparent breadcrumb-dot font-weight-bold font-size-sm">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-muted">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="#" class="text-muted">Saving Plan</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="flex-column-fluid">
        <div class="container">

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                    {{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                    {{ session('error') }}
                    <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                </div>
            @endif

            {{-- Summary Cards --}}
            <div class="row mb-6">
                <div class="col-md-3">
                    <div class="card card-custom bg-primary text-white">
                        <div class="card-body py-5 text-center">
                            <div style="font-size:1.6rem; font-weight:700;">${{ number_format($paid_amount, 2) }}</div>
                            <div class="font-size-sm mt-1">Total Deposited</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card card-custom bg-warning text-white">
                        <div class="card-body py-5 text-center">
                            <div style="font-size:1.6rem; font-weight:700;">${{ number_format($remaining, 2) }}</div>
                            <div class="font-size-sm mt-1">Remaining</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card card-custom bg-success text-white">
                        <div class="card-body py-5 text-center">
                            <div style="font-size:1.6rem; font-weight:700;">{{ $paid_count }} / {{ $total_count }}</div>
                            <div class="font-size-sm mt-1">Instalments Paid</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card card-custom {{ $plan_complete ? 'bg-success' : 'bg-info' }} text-white">
                        <div class="card-body py-5 text-center">
                            @if($plan_complete)
                                <div style="font-size:1.6rem; font-weight:700;">&#10003; Done</div>
                                <div class="font-size-sm mt-1">Plan Complete!</div>
                            @elseif($next_due)
                                <div style="font-size:1.1rem; font-weight:700;">
                                    #{{ $next_due->instalment_number }}<br>
                                    {{ $next_due->due_date->format('d M Y') }}
                                </div>
                                <div class="font-size-sm mt-1">Next Due Date</div>
                            @else
                                <div style="font-size:1rem;">—</div>
                                <div class="font-size-sm mt-1">No pending</div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Registration Payment Breakdown --}}
            <div class="card card-custom gutter-b border-left-primary">
                <div class="card-header border-0 py-4">
                    <h3 class="card-title font-weight-bolder text-dark">Registration Payment Summary</h3>
                </div>
                <div class="card-body pt-0">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="d-flex align-items-center mb-3">
                                <span class="svg-icon svg-icon-primary mr-3">
                                    <i class="fas fa-money-bill-wave text-primary" style="font-size:1.4rem;"></i>
                                </span>
                                <div>
                                    <div class="text-muted font-size-sm">Amount Paid at Registration</div>
                                    <div class="font-weight-bolder font-size-h5">${{ number_format($paidAtReg, 2) }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="d-flex align-items-center mb-3">
                                <span class="svg-icon svg-icon-warning mr-3">
                                    <i class="fas fa-receipt text-warning" style="font-size:1.4rem;"></i>
                                </span>
                                <div>
                                    <div class="text-muted font-size-sm">Registration Fee</div>
                                    <div class="font-weight-bolder font-size-h5">${{ number_format($regFee, 2) }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="d-flex align-items-center mb-3">
                                <span class="svg-icon svg-icon-success mr-3">
                                    <i class="fas fa-piggy-bank text-success" style="font-size:1.4rem;"></i>
                                </span>
                                <div>
                                    <div class="text-muted font-size-sm">Deposited Toward Instalment #1</div>
                                    <div class="font-weight-bolder font-size-h5">${{ number_format($partialDeposit, 2) }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if(!auth()->user()->saving_registration_completed)
                        <div class="alert alert-warning mt-2 mb-0">
                            <strong>Registration Incomplete.</strong>
                            You paid ${{ number_format($paidAtReg, 2) }} at registration (${{ number_format($regFee, 2) }} fee + ${{ number_format($partialDeposit, 2) }} toward deposit).
                            You still need to deposit <strong>${{ number_format($inst1Remaining, 2) }}</strong> to complete Instalment #1 and activate your account.
                        </div>
                    @else
                        <div class="alert alert-success mt-2 mb-0">
                            <strong>Registration Complete.</strong> Your saving account is fully activated.
                        </div>
                    @endif
                </div>
            </div>

            {{-- Early payment notice --}}
            @if($next_due && $next_due->status === 'pending' && $next_due->instalment_number > 1 && \Carbon\Carbon::today()->lt($next_due->due_date))
                <div class="alert alert-info mb-6">
                    <i class="fas fa-info-circle mr-1"></i>
                    <strong>Early Payment:</strong> Instalment #{{ $next_due->instalment_number }} is scheduled for
                    <strong>{{ $next_due->due_date->format('d M Y') }}</strong>.
                    You can pay now — ROI for this instalment will begin from its scheduled date.
                </div>
            @endif

            {{-- Submit Next Instalment — shown for all pending instalments --}}
            @if($next_due && $next_due->status === 'pending')
                @php
                    $instUser     = auth()->user();
                    $baseAmt      = $next_due->amount;
                    $adbFee       = $instUser->adb_option  ? round($baseAmt * 0.003, 2) : 0;
                    $fispFee      = $instUser->fisp_option ? round($baseAmt * 0.004, 2) : 0;
                    $totalPayable = round($baseAmt + $adbFee + $fispFee, 2);
                    // For instalment #1 partial logic
                    $defaultSubmit = ($next_due->instalment_number === 1 && $inst1Remaining > 0)
                        ? round($inst1Remaining + $adbFee + $fispFee, 2)
                        : $totalPayable;
                @endphp
                <div class="card card-custom gutter-b">
                    <div class="card-header border-0 py-5">
                        <h3 class="card-title font-weight-bolder text-dark">
                            Pay Instalment #{{ $next_due->instalment_number }}
                            <span class="badge badge-light-primary ml-2">${{ number_format($totalPayable, 2) }}</span>
                            @if($instUser->adb_option || $instUser->fisp_option)
                                <span class="badge badge-light-info ml-1" style="font-size:.75rem;">incl. ADB/FISP</span>
                            @endif
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <table class="table table-sm table-borderless">
                                    <tr><td class="text-muted" width="180">Due Date</td><td class="font-weight-bold">{{ $next_due->due_date->format('d M Y') }}</td></tr>
                                    @if($next_due->instalment_number === 1 && $partialDeposit > 0)
                                        <tr><td class="text-muted">Paid at Registration</td><td class="font-weight-bold text-success">${{ number_format($partialDeposit, 2) }}</td></tr>
                                        <tr><td class="text-muted">Base Remaining</td><td class="font-weight-bold">${{ number_format($inst1Remaining, 2) }}</td></tr>
                                    @else
                                        <tr><td class="text-muted">Base Instalment</td><td class="font-weight-bold">${{ number_format($baseAmt, 2) }}</td></tr>
                                    @endif
                                    @if($instUser->adb_option)
                                        <tr><td class="text-muted">+ ADB Charge (0.3%)</td><td class="text-danger font-weight-bold">+${{ number_format($adbFee, 2) }}</td></tr>
                                    @endif
                                    @if($instUser->fisp_option)
                                        <tr><td class="text-muted">+ FISP Charge (0.4%)</td><td class="text-warning font-weight-bold">+${{ number_format($fispFee, 2) }}</td></tr>
                                    @endif
                                    <tr style="border-top:2px solid #eee;">
                                        <td class="font-weight-bolder">Total to Pay</td>
                                        <td class="font-weight-bolder text-primary font-size-h6">${{ number_format($totalPayable, 2) }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Status</td>
                                        <td>
                                            @if($next_due->isOverdue())
                                                <span class="badge badge-light-danger">Overdue</span>
                                            @else
                                                <span class="badge badge-light-warning">Pending</span>
                                            @endif
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        <form method="POST" action="{{ route('saving.user.submit') }}" enctype="multipart/form-data">
                            @csrf
                            <div class="row">
                                <div class="col-md-4 mb-4">
                                    <label class="font-weight-bold">Payment Method</label>
                                    <select name="payment_method" id="inst_payment_method" class="form-control" required onchange="toggleInstBankField(this.value)">
                                        <option value="">Select method</option>
                                        <option value="bank" {{ old('payment_method') == 'bank' ? 'selected' : '' }}>Bank Transfer</option>
                                        <option value="usdt" {{ old('payment_method') == 'usdt' ? 'selected' : '' }}>USDT</option>
                                        <option value="cash_slip" {{ old('payment_method') == 'cash_slip' ? 'selected' : '' }}>Cash Slip</option>
                                    </select>
                                    @error('payment_method')<div class="text-danger small">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-4 mb-4" id="inst_bank_field" style="{{ old('payment_method') === 'bank' ? '' : 'display:none;' }}">
                                    <label class="font-weight-bold">Bank Name</label>
                                    <select name="bank_name" id="inst_bank_name" class="form-control">
                                        <option value="">Select bank</option>
                                        <option value="Allied Bank Limited (ABL)" {{ old('bank_name') == 'Allied Bank Limited (ABL)' ? 'selected' : '' }}>Allied Bank Limited (ABL)</option>
                                        <option value="Al Baraka Bank Pakistan Limited" {{ old('bank_name') == 'Al Baraka Bank Pakistan Limited' ? 'selected' : '' }}>Al Baraka Bank Pakistan Limited</option>
                                        <option value="Askari Bank Limited" {{ old('bank_name') == 'Askari Bank Limited' ? 'selected' : '' }}>Askari Bank Limited</option>
                                        <option value="Bank Alfalah Limited" {{ old('bank_name') == 'Bank Alfalah Limited' ? 'selected' : '' }}>Bank Alfalah Limited</option>
                                        <option value="Bank Al-Habib Limited" {{ old('bank_name') == 'Bank Al-Habib Limited' ? 'selected' : '' }}>Bank Al-Habib Limited</option>
                                        <option value="Bank Islami Pakistan Limited" {{ old('bank_name') == 'Bank Islami Pakistan Limited' ? 'selected' : '' }}>Bank Islami Pakistan Limited</option>
                                        <option value="Bank of Khyber (BOK)" {{ old('bank_name') == 'Bank of Khyber (BOK)' ? 'selected' : '' }}>Bank of Khyber (BOK)</option>
                                        <option value="Bank of Punjab (BOP)" {{ old('bank_name') == 'Bank of Punjab (BOP)' ? 'selected' : '' }}>Bank of Punjab (BOP)</option>
                                        <option value="Dubai Islamic Bank Pakistan Limited" {{ old('bank_name') == 'Dubai Islamic Bank Pakistan Limited' ? 'selected' : '' }}>Dubai Islamic Bank Pakistan Limited</option>
                                        <option value="Faysal Bank Limited" {{ old('bank_name') == 'Faysal Bank Limited' ? 'selected' : '' }}>Faysal Bank Limited</option>
                                        <option value="First Women Bank Limited" {{ old('bank_name') == 'First Women Bank Limited' ? 'selected' : '' }}>First Women Bank Limited</option>
                                        <option value="Habib Bank Limited (HBL)" {{ old('bank_name') == 'Habib Bank Limited (HBL)' ? 'selected' : '' }}>Habib Bank Limited (HBL)</option>
                                        <option value="Habib Metropolitan Bank Limited" {{ old('bank_name') == 'Habib Metropolitan Bank Limited' ? 'selected' : '' }}>Habib Metropolitan Bank Limited</option>
                                        <option value="JS Bank Limited" {{ old('bank_name') == 'JS Bank Limited' ? 'selected' : '' }}>JS Bank Limited</option>
                                        <option value="MCB Bank Limited" {{ old('bank_name') == 'MCB Bank Limited' ? 'selected' : '' }}>MCB Bank Limited</option>
                                        <option value="Meezan Bank Limited" {{ old('bank_name') == 'Meezan Bank Limited' ? 'selected' : '' }}>Meezan Bank Limited</option>
                                        <option value="National Bank of Pakistan (NBP)" {{ old('bank_name') == 'National Bank of Pakistan (NBP)' ? 'selected' : '' }}>National Bank of Pakistan (NBP)</option>
                                        <option value="Sindh Bank Limited" {{ old('bank_name') == 'Sindh Bank Limited' ? 'selected' : '' }}>Sindh Bank Limited</option>
                                        <option value="Soneri Bank Limited" {{ old('bank_name') == 'Soneri Bank Limited' ? 'selected' : '' }}>Soneri Bank Limited</option>
                                        <option value="Standard Chartered Bank Pakistan" {{ old('bank_name') == 'Standard Chartered Bank Pakistan' ? 'selected' : '' }}>Standard Chartered Bank Pakistan</option>
                                        <option value="United Bank Limited (UBL)" {{ old('bank_name') == 'United Bank Limited (UBL)' ? 'selected' : '' }}>United Bank Limited (UBL)</option>
                                        <option value="Zarai Taraqiati Bank Limited (ZTBL)" {{ old('bank_name') == 'Zarai Taraqiati Bank Limited (ZTBL)' ? 'selected' : '' }}>Zarai Taraqiati Bank Limited (ZTBL)</option>
                                        <option value="Easypaisa" {{ old('bank_name') == 'Easypaisa' ? 'selected' : '' }}>Easypaisa</option>
                                        <option value="Jazzcash" {{ old('bank_name') == 'Jazzcash' ? 'selected' : '' }}>Jazzcash</option>
                                        <option value="Other" {{ old('bank_name') == 'Other' ? 'selected' : '' }}>Other</option>
                                    </select>
                                    <div id="inst_other_bank_div" class="mt-2" style="{{ old('bank_name') === 'Other' ? '' : 'display:none;' }}">
                                        <input type="text" name="bank_name_other" id="inst_other_bank_input" class="form-control" placeholder="Enter bank name" value="{{ old('bank_name_other') }}">
                                    </div>
                                    @error('bank_name')<div class="text-danger small">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-4 mb-4">
                                    <label class="font-weight-bold">Transaction ID</label>
                                    <input type="text" name="transaction_id" class="form-control" placeholder="Your transaction ID" required value="{{ old('transaction_id') }}">
                                    @error('transaction_id')<div class="text-danger small">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-4 mb-4">
                                    <label class="font-weight-bold">
                                        Amount Submitted ($)
                                        <small class="text-muted">min: ${{ number_format($totalPayable, 2) }}</small>
                                    </label>
                                    <input type="number" step="0.01" name="submitted_amount" class="form-control"
                                           placeholder="{{ number_format($defaultSubmit, 2) }}"
                                           min="{{ $totalPayable }}"
                                           value="{{ old('submitted_amount', $defaultSubmit) }}" required>
                                    @error('submitted_amount')<div class="text-danger small">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-12 mb-4">
                                    <label class="font-weight-bold">Payment Proof Screenshot</label>
                                    <input type="file" name="proof" class="form-control-file" accept="image/*" required>
                                    @error('proof')<div class="text-danger small">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary">Submit Payment Proof</button>
                        </form>
                    </div>
                </div>
            @elseif($next_due && $next_due->status === 'submitted')
                <div class="alert alert-info mb-6">
                    <strong>Instalment #{{ $next_due->instalment_number }} submitted</strong> on {{ $next_due->submitted_at?->format('d M Y H:i') }}.
                    Awaiting admin confirmation.
                </div>
            @endif

            {{-- Full Instalment History --}}
            <div class="card card-custom gutter-b">
                <div class="card-header border-0 py-5">
                    <h3 class="card-title font-weight-bolder text-dark">Instalment Schedule (25 Months)</h3>
                </div>
                <div class="card-body pt-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-head-custom table-vertical-center">
                            @php
                                $user       = auth()->user();
                                $hasOptions = $user->adb_option || $user->fisp_option;
                            @endphp
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Due Date</th>
                                    <th>Base Amount</th>
                                    @if($hasOptions)
                                        @if($user->adb_option)<th class="text-danger">ADB Charge</th>@endif
                                        @if($user->fisp_option)<th class="text-warning">FISP Charge</th>@endif
                                        <th class="text-primary">Total Payable</th>
                                    @endif
                                    <th>Status</th>
                                    <th>Paid On</th>
                                    <th>Deposited</th>
                                    <th>Notes</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($instalments as $inst)
                                @php
                                    $adbCharge  = $user->adb_option  ? round($inst->amount * 0.003, 2) : 0;
                                    $fispCharge = $user->fisp_option ? round($inst->amount * 0.004, 2) : 0;
                                    $totalPayable = $inst->amount + $adbCharge + $fispCharge;
                                @endphp
                                <tr>
                                    <td><strong>{{ $inst->instalment_number }}</strong></td>
                                    <td>{{ $inst->due_date->format('d M Y') }}</td>
                                    <td>${{ number_format($inst->amount, 2) }}</td>
                                    @if($hasOptions)
                                        @if($user->adb_option)
                                            <td class="text-danger font-weight-bold">+${{ number_format($adbCharge, 2) }}</td>
                                        @endif
                                        @if($user->fisp_option)
                                            <td class="text-warning font-weight-bold">+${{ number_format($fispCharge, 2) }}</td>
                                        @endif
                                        <td><strong class="text-primary">${{ number_format($totalPayable, 2) }}</strong></td>
                                    @endif
                                    <td>
                                        @switch($inst->status)
                                            @case('confirmed')
                                                <span class="badge badge-light-success">Confirmed</span>
                                                @if($inst->is_late) <span class="badge badge-light-warning ml-1">Late</span> @endif
                                                @break
                                            @case('submitted')
                                                <span class="badge badge-light-info">Awaiting Confirmation</span>
                                                @break
                                            @case('missed')
                                                <span class="badge badge-light-danger">Missed</span>
                                                @break
                                            @default
                                                @if($inst->isOverdue())
                                                    <span class="badge badge-light-danger">Overdue</span>
                                                @else
                                                    <span class="badge badge-light-secondary">Pending</span>
                                                @endif
                                        @endswitch
                                    </td>
                                    <td>{{ $inst->confirmed_at?->format('d M Y') ?? '—' }}</td>
                                    <td>
                                        @if($inst->deposited_at)
                                            {{ $inst->deposited_at->format('d M Y') }}
                                        @elseif($inst->deposit_deferred && $inst->next_cycle_date)
                                            <span class="text-warning">Deferred to {{ $inst->next_cycle_date->format('d M Y') }}</span>
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td class="text-muted small">{{ $inst->notes ?? '' }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

@section('page_js')
<script>
function toggleInstBankField(method) {
    var bankField = document.getElementById('inst_bank_field');
    var bankSelect = document.getElementById('inst_bank_name');
    if (method === 'bank') {
        bankField.style.display = '';
        bankSelect.required = true;
    } else {
        bankField.style.display = 'none';
        bankSelect.required = false;
    }
}

document.addEventListener('DOMContentLoaded', function() {
    var bankSelect  = document.getElementById('inst_bank_name');
    var otherDiv    = document.getElementById('inst_other_bank_div');
    var otherInput  = document.getElementById('inst_other_bank_input');

    if (bankSelect) {
        bankSelect.addEventListener('change', function() {
            if (this.value === 'Other') {
                otherDiv.style.display = '';
                otherInput.required = true;
            } else {
                otherDiv.style.display = 'none';
                otherInput.required = false;
            }
        });
    }

    // On submit: if "Other" chosen, overwrite the bank select value with typed text
    var form = document.querySelector('form[action="{{ route('saving.user.submit') }}"]');
    if (form) {
        form.addEventListener('submit', function() {
            if (bankSelect && bankSelect.value === 'Other' && otherInput && otherInput.value.trim()) {
                bankSelect.value = otherInput.value.trim();
            }
        });
    }
});
</script>
@endsection


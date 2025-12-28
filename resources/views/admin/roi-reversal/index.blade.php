@extends('demo.layout.app')
@section('title', 'ROI Reversal Management')
@section('content')
<div class="content d-flex flex-column flex-column-fluid" id="kt_content">
    <div class="subheader py-2 py-lg-6 subheader-solid" id="kt_subheader">
        <div class="container-fluid d-flex align-items-center justify-content-between flex-wrap flex-sm-nowrap">
            <div class="d-flex align-items-center flex-wrap mr-1">
                <div class="d-flex align-items-baseline flex-wrap mr-5">
                    <h5 class="text-dark font-weight-bold my-1 mr-5">ROI Reversal Tool</h5>
                    <ul class="breadcrumb breadcrumb-transparent breadcrumb-dot font-weight-bold p-0 my-2 font-size-sm">
                        <li class="breadcrumb-item">
                            <a href="{{ route('dashboard') }}" class="text-muted">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="{{ route('setting.basic') }}" class="text-muted">Settings</a>
                        </li>
                        <li class="breadcrumb-item text-muted">ROI Reversal</li>
                    </ul>
                </div>
            </div>
            <div class="d-flex align-items-center">
                <a href="{{ route('admin.roi-settings.index') }}" class="btn btn-light-primary font-weight-bolder">
                    <i class="la la-arrow-left"></i> Back to ROI Settings
                </a>
            </div>
        </div>
    </div>

    <div class="d-flex flex-column-fluid">
        <div class="container-fluid">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <div class="alert-icon"><i class="flaticon2-check-mark"></i></div>
                    <div class="alert-text">{{ session('success') }}</div>
                    <button type="button" class="close" data-dismiss="alert">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <div class="alert-icon"><i class="flaticon-warning"></i></div>
                    <div class="alert-text">{{ session('error') }}</div>
                    <button type="button" class="close" data-dismiss="alert">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            <!-- Warning Notice -->
            <div class="alert alert-custom alert-danger fade show mb-5" role="alert">
                <div class="alert-icon"><i class="flaticon-warning"></i></div>
                <div class="alert-text">
                    <strong>Caution:</strong> This tool affects user wallet balances. Always preview before executing. All actions are logged and auditable.
                </div>
            </div>

            <!-- Recent ROI Statistics -->
            <div class="card card-custom gutter-b">
                <div class="card-header">
                    <div class="card-title">
                        <h3 class="card-label">Recent ROI Distribution Statistics</h3>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach($stats as $days => $data)
                        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6">
                            <div class="card card-custom bg-light-{{ $loop->index == 0 ? 'primary' : ($loop->index == 1 ? 'success' : ($loop->index == 2 ? 'warning' : 'info')) }} gutter-b">
                                <div class="card-body">
                                    <h6 class="text-muted font-weight-bold">Last {{ $days }} Day{{ $days > 1 ? 's' : '' }}</h6>
                                    <div class="mt-3">
                                        <small class="text-muted">Standard:</small>
                                        <div class="font-weight-bolder font-size-h6">${{ number_format($data['standard'], 2) }}</div>
                                    </div>
                                    <div class="mt-2">
                                        <small class="text-muted">VIP:</small>
                                        <div class="font-weight-bolder font-size-h6">${{ number_format($data['vip'], 2) }}</div>
                                    </div>
                                    <div class="mt-2 pt-2 border-top">
                                        <small class="text-muted">Total:</small>
                                        <div class="font-weight-boldest font-size-h5 text-primary">${{ number_format($data['total'], 2) }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Reversal Configuration Form -->
                <div class="col-lg-6">
                    <div class="card card-custom gutter-b">
                        <div class="card-header">
                            <div class="card-title">
                                <h3 class="card-label">Configure Reversal</h3>
                            </div>
                        </div>
                        <div class="card-body">
                            <form id="reversalForm">
                                @csrf
                                <div class="form-group">
                                    <label>Reversal Percentage <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" id="percentage" name="percentage"
                                               value="83.16" step="0.01" min="0" max="100" required>
                                        <div class="input-group-append">
                                            <span class="input-group-text">%</span>
                                        </div>
                                    </div>
                                    <span class="form-text text-muted">Enter the percentage of ROI to reverse (e.g., 83.16)</span>
                                </div>

                                <div class="form-group">
                                    <label>Target Plan <span class="text-danger">*</span></label>
                                    <select class="form-control" id="plan" name="plan" required>
                                        <option value="standard" selected>Standard Plan Only</option>
                                        <option value="vip">VIP Plan Only</option>
                                        <option value="all">All Plans</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label>Days to Look Back <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="days" name="days"
                                           value="2" min="1" max="30" required>
                                    <span class="form-text text-muted">How many days back to reverse ROI (default: 2)</span>
                                </div>

                                <div class="form-group">
                                    <div class="checkbox-inline">
                                        <label class="checkbox">
                                            <input type="checkbox" id="include_profit_share" name="include_profit_share" checked>
                                            <span></span>
                                            Also reverse profit sharing
                                        </label>
                                    </div>
                                    <span class="form-text text-muted">Check this to also reverse profit share distributions proportionally</span>
                                </div>

                                <div class="form-group">
                                    <label>Reason for Reversal <span class="text-danger">*</span></label>
                                    <textarea class="form-control" id="reason" name="reason" rows="3"
                                              maxlength="500" required
                                              placeholder="Explain why this reversal is being performed..."></textarea>
                                    <span class="form-text text-muted">This will be logged for audit purposes</span>
                                </div>

                                <div class="separator separator-dashed my-5"></div>

                                <div class="d-flex justify-content-between">
                                    <button type="button" class="btn btn-primary font-weight-bold" onclick="previewReversal()">
                                        <i class="la la-eye"></i> Preview Reversal
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Preview Panel -->
                <div class="col-lg-6">
                    <div class="card card-custom gutter-b" id="previewPanel" style="display: none;">
                        <div class="card-header bg-warning">
                            <div class="card-title">
                                <h3 class="card-label text-white">
                                    <i class="flaticon-eye text-white"></i> Reversal Preview
                                </h3>
                            </div>
                        </div>
                        <div class="card-body">
                            <!-- ROI Preview -->
                            <div class="mb-5">
                                <h5 class="font-weight-bold mb-3">ROI Reversal Impact</h5>
                                <div class="row">
                                    <div class="col-6">
                                        <small class="text-muted">Entries Affected:</small>
                                        <div class="font-weight-bolder font-size-h6" id="roi_total_entries">-</div>
                                    </div>
                                    <div class="col-6">
                                        <small class="text-muted">Users Affected:</small>
                                        <div class="font-weight-bolder font-size-h6" id="roi_total_users">-</div>
                                    </div>
                                </div>
                                <div class="separator separator-dashed my-3"></div>
                                <div class="mb-2">
                                    <small class="text-muted">Original Total:</small>
                                    <div class="font-weight-bolder font-size-h6" id="roi_original_total">-</div>
                                </div>
                                <div class="mb-2">
                                    <small class="text-muted">Amount to Reverse:</small>
                                    <div class="font-weight-bolder font-size-h5 text-danger" id="roi_reversal_amount">-</div>
                                </div>
                                <div>
                                    <small class="text-muted">Remaining Total:</small>
                                    <div class="font-weight-bolder font-size-h6 text-success" id="roi_remaining_total">-</div>
                                </div>
                            </div>

                            <!-- Profit Share Preview -->
                            <div class="mb-5" id="profitSharePreview" style="display: none;">
                                <h5 class="font-weight-bold mb-3">Profit Share Reversal Impact</h5>
                                <div class="row">
                                    <div class="col-6">
                                        <small class="text-muted">Entries Affected:</small>
                                        <div class="font-weight-bolder font-size-h6" id="ps_total_entries">-</div>
                                    </div>
                                    <div class="col-6">
                                        <small class="text-muted">Users Affected:</small>
                                        <div class="font-weight-bolder font-size-h6" id="ps_total_users">-</div>
                                    </div>
                                </div>
                                <div class="separator separator-dashed my-3"></div>
                                <div class="mb-2">
                                    <small class="text-muted">Amount to Reverse:</small>
                                    <div class="font-weight-bolder font-size-h5 text-danger" id="ps_reversal_amount">-</div>
                                </div>
                            </div>

                            <!-- Top Affected Users -->
                            <div class="mb-5" id="topUsersSection" style="display: none;">
                                <h5 class="font-weight-bold mb-3">Top 10 Affected Users</h5>
                                <div class="table-responsive">
                                    <table class="table table-sm table-hover" id="topUsersTable">
                                        <thead>
                                            <tr>
                                                <th>User</th>
                                                <th>Plan</th>
                                                <th class="text-right">Reversal</th>
                                            </tr>
                                        </thead>
                                        <tbody id="topUsersBody">
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Execute Button -->
                            <div class="alert alert-danger mb-3">
                                <strong>⚠️ Warning:</strong> This action cannot be undone. Please verify all details before proceeding.
                            </div>
                            <button type="button" class="btn btn-danger btn-lg btn-block" onclick="executeReversal()">
                                <i class="la la-exclamation-triangle"></i> Execute Reversal
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Reversals History -->
            @if(count($recentReversals) > 0)
            <div class="card card-custom gutter-b">
                <div class="card-header">
                    <div class="card-title">
                        <h3 class="card-label">Recent Reversal History</h3>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-vertical-center">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>User</th>
                                    <th>Plan</th>
                                    <th>Type</th>
                                    <th class="text-right">Amount</th>
                                    <th>Description</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentReversals as $reversal)
                                <tr>
                                    <td>{{ $reversal['created_at']->format('M d, Y H:i') }}</td>
                                    <td>
                                        <div class="font-weight-bold">{{ $reversal['user_name'] }}</div>
                                        <small class="text-muted">@</small>{{ $reversal['username'] }}
                                    </td>
                                    <td>
                                        <span class="label label-{{ $reversal['user_plan'] == 'vip' ? 'warning' : 'info' }} label-inline font-weight-bolder">
                                            {{ strtoupper($reversal['user_plan']) }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="label label-danger label-inline">
                                            {{ str_replace('_', ' ', $reversal['type']) }}
                                        </span>
                                    </td>
                                    <td class="text-right text-danger font-weight-bold">
                                        ${{ number_format(abs($reversal['amount']), 2) }}
                                    </td>
                                    <td><small>{{ $reversal['description'] }}</small></td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
let previewData = null;

function previewReversal() {
    const btn = event.target;
    const originalHtml = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm mr-2"></span>Loading...';

    const formData = {
        percentage: document.getElementById('percentage').value,
        plan: document.getElementById('plan').value,
        days: document.getElementById('days').value,
        include_profit_share: document.getElementById('include_profit_share').checked
    };

    fetch('{{ route('admin.roi-reversal.preview') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
        },
        body: JSON.stringify(formData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            previewData = data.preview;
            displayPreview(data.preview);
            document.getElementById('previewPanel').style.display = 'block';

            // Scroll to preview
            document.getElementById('previewPanel').scrollIntoView({ behavior: 'smooth' });
        } else {
            Swal.fire('Error', data.message || 'Failed to generate preview', 'error');
        }
    })
    .catch(error => {
        Swal.fire('Error', 'An error occurred: ' + error.message, 'error');
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = originalHtml;
    });
}

function displayPreview(preview) {
    // ROI Preview
    document.getElementById('roi_total_entries').textContent = preview.roi.total_entries.toLocaleString();
    document.getElementById('roi_total_users').textContent = preview.roi.total_users.toLocaleString();
    document.getElementById('roi_original_total').textContent = '$' + preview.roi.original_total.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
    document.getElementById('roi_reversal_amount').textContent = '$' + preview.roi.reversal_amount.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
    document.getElementById('roi_remaining_total').textContent = '$' + preview.roi.remaining_total.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');

    // Profit Share Preview
    if (preview.profit_share) {
        document.getElementById('profitSharePreview').style.display = 'block';
        document.getElementById('ps_total_entries').textContent = preview.profit_share.total_entries.toLocaleString();
        document.getElementById('ps_total_users').textContent = preview.profit_share.total_users.toLocaleString();
        document.getElementById('ps_reversal_amount').textContent = '$' + preview.profit_share.reversal_amount.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
    } else {
        document.getElementById('profitSharePreview').style.display = 'none';
    }

    // Top Users
    if (preview.top_affected_users && preview.top_affected_users.length > 0) {
        document.getElementById('topUsersSection').style.display = 'block';
        const tbody = document.getElementById('topUsersBody');
        tbody.innerHTML = '';

        preview.top_affected_users.slice(0, 10).forEach(user => {
            const row = `
                <tr>
                    <td>
                        <div class="font-weight-bold">${user.name}</div>
                        <small class="text-muted">@${user.username}</small>
                    </td>
                    <td>
                        <span class="label label-${user.plan === 'vip' ? 'warning' : 'info'} label-inline">
                            ${user.plan.toUpperCase()}
                        </span>
                    </td>
                    <td class="text-right text-danger font-weight-bold">
                        $${user.reversal.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,')}
                    </td>
                </tr>
            `;
            tbody.innerHTML += row;
        });
    }
}

function executeReversal() {
    if (!previewData) {
        Swal.fire('Error', 'Please preview the reversal first', 'error');
        return;
    }

    const reason = document.getElementById('reason').value.trim();
    if (!reason) {
        Swal.fire('Error', 'Please provide a reason for this reversal', 'error');
        return;
    }

    Swal.fire({
        title: 'Are you absolutely sure?',
        html: `This will reverse ROI for <strong>${previewData.roi.total_users}</strong> users.<br>
               Total amount: <strong class="text-danger">$${previewData.roi.reversal_amount.toFixed(2)}</strong><br><br>
               <span class="text-danger font-weight-bold">This action CANNOT be undone!</span>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, execute reversal!',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            performReversal();
        }
    });
}

function performReversal() {
    const formData = {
        percentage: document.getElementById('percentage').value,
        plan: document.getElementById('plan').value,
        days: document.getElementById('days').value,
        include_profit_share: document.getElementById('include_profit_share').checked,
        reason: document.getElementById('reason').value
    };

    Swal.fire({
        title: 'Processing...',
        html: 'Executing reversal. Please wait...',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    fetch('{{ route('admin.roi-reversal.execute') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
        },
        body: JSON.stringify(formData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            let resultHtml = `
                <h5>Reversal Completed Successfully!</h5>
                <div class="text-left mt-4">
                    <strong>ROI Reversal:</strong><br>
                    - Entries Affected: ${data.results.roi.total_affected.toLocaleString()}<br>
                    - Amount Reversed: $${data.results.roi.total_reversed.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,')}<br>
            `;

            if (data.results.profit_share) {
                resultHtml += `
                    <br><strong>Profit Share Reversal:</strong><br>
                    - Entries Affected: ${data.results.profit_share.total_affected.toLocaleString()}<br>
                    - Amount Reversed: $${data.results.profit_share.total_reversed.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,')}
                `;
            }
            resultHtml += '</div>';

            Swal.fire({
                title: 'Success!',
                html: resultHtml,
                icon: 'success'
            }).then(() => {
                window.location.reload();
            });
        } else {
            Swal.fire('Error', data.message || 'Failed to execute reversal', 'error');
        }
    })
    .catch(error => {
        Swal.fire('Error', 'An error occurred: ' + error.message, 'error');
    });
}
</script>
@endpush
@endsection

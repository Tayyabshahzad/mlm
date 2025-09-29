@extends('demo.layout.app')
@section('title', 'ROI Monitoring')
@section('custom_css')
    <style>
        :root {
            --primary-color: #4361ee;
            --secondary-color: #3f37c9;
            --accent-color: #4cc9f0;
            --success-color: #4ad66d;
            --warning-color: #f8961e;
            --danger-color: #f94144;
            --light-color: #f8f9fa;
            --dark-color: #212529;
            --card-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            --transition: all 0.3s ease;
        }

        /* Modern Card Styling */
        .modern-card {
            border: none;
            border-radius: 12px;
            box-shadow: var(--card-shadow);
            transition: var(--transition);
            overflow: hidden;
            background: white;
            margin-bottom: 24px;
        }

        .modern-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
        }

        .modern-card-header {
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            padding: 20px 25px;
            background: transparent;
        }

        .modern-card-title {
            font-weight: 600;
            color: var(--dark-color);
            font-size: 1.1rem;
            margin: 0;
        }

        /* Wallet Cards */
        .wallet-card {
            border-radius: 12px;
            color: white;
            padding: 20px;
            position: relative;
            overflow: hidden;
            margin-bottom: 20px;
            height: 130px;
            transition: var(--transition);
        }

        .wallet-card:hover {
            transform: translateY(-3px);
        }

        .wallet-card::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200px;
            height: 200px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
        }

        .wallet-card .wallet-title {
            font-size: 0.9rem;
            font-weight: 500;
            margin-bottom: 5px;
            opacity: 0.9;
        }

        .wallet-card .wallet-amount {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 15px;
        }

        /* Progress Circles */
        .progress-circle-container {
            display: flex;
            justify-content: center;
            margin: 20px 0;
        }

        .progress-circle {
            position: relative;
            width: 180px;
            height: 180px;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            background: conic-gradient(var(--primary-color) 0%, var(--primary-color) var(--progress), #e9ecef var(--progress));
        }

        .progress-circle .inner-circle {
            position: absolute;
            width: 150px;
            height: 150px;
            border-radius: 50%;
            background: white;
            display: flex;
            justify-content: center;
            align-items: center;
            box-shadow: inset 0 0 10px rgba(0, 0, 0, 0.05);
        }

        .progress-circle .percentage {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--dark-color);
        }

        /* Reward Levels */
        .reward-level {
            position: relative;
            padding: 15px 0;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }

        .reward-level:last-child {
            border-bottom: none;
        }

        .reward-progress {
            height: 12px;
            border-radius: 6px;
            margin: 10px 0;
        }

        .reward-badge {
            position: absolute;
            right: 45px;
            top: 50%;
            transform: translateY(-50%);
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: white;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        /* Announcement Banner */
        .announcement-banner {
            background: linear-gradient(135deg, #4361ee, #4cc9f0);
            color: white;
            border-radius: 12px;
            padding: 25px;
            margin: 30px 0;
            position: relative;
            overflow: hidden;
        }

        .announcement-banner::after {
            content: '';
            position: absolute;
            top: -50px;
            right: -50px;
            width: 200px;
            height: 200px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
        }

        .announcement-icon {
            font-size: 2.5rem;
            margin-right: 15px;
            animation: bounce 2s infinite;
        }

        .announcement-title {
            font-weight: 700;
            font-size: 1.3rem;
            margin-bottom: 10px;
        }

        .announcement-text {
            opacity: 0.9;
            margin-bottom: 15px;
        }

        .announcement-link {
            color: white;
            text-decoration: underline;
            font-weight: 500;
            transition: var(--transition);
        }

        .announcement-link:hover {
            color: rgba(255, 255, 255, 0.8);
        }

        @keyframes bounce {

            0%,
            100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-10px);
            }
        }

        /* Investment Progress Cards */
        .investment-card {
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 20px;
            position: relative;
            overflow: hidden;
            background: white;
            box-shadow: var(--card-shadow);
        }

        .investment-card::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 100px;
            height: 100px;
            background-size: contain;
            background-repeat: no-repeat;
            opacity: 0.1;
        }

        .investment-title {
            font-weight: 700;
            font-size: 1.2rem;
            margin-bottom: 15px;
            color: var(--dark-color);
        }

        .progress-label {
            font-weight: 600;
            color: var(--dark-color);
        }

        .progress-value {
            font-weight: 700;
        }

        /* Responsive Adjustments */
        @media (max-width: 768px) {
            .wallet-card {
                height: auto;
                padding: 15px;
            }

            .wallet-card .wallet-amount {
                font-size: 1.5rem;
            }

            .progress-circle {
                width: 150px;
                height: 150px;
            }

            .progress-circle .inner-circle {
                width: 120px;
                height: 120px;
            }
        }

        .target-section {
            display: block !important;
            animation: fadeIn 0.3s ease-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
@endsection
@section('content')
    <!--begin::Content-->
    <div class="content d-flex flex-column flex-column-fluid" id="kt_content">
        <!--begin::Subheader-->
        <div class="py-2 subheader py-lg-6 subheader-solid" id="kt_subheader">
            <div class="flex-wrap container-fluid d-flex align-items-center justify-content-between flex-sm-nowrap">
                <!--begin::Info-->
                <div class="flex-wrap mr-1 d-flex align-items-center">
                    <!--begin::Mobile Toggle-->
                    <button class="mr-4 burger-icon burger-icon-left d-inline-block d-lg-none"
                        id="kt_subheader_mobile_toggle">
                        <span></span>
                    </button>
                    <div class="flex-wrap mr-5 d-flex align-items-baseline">
                        <h5 class="my-1 mr-5 text-dark font-weight-bold">ROI Monitoring </h5>
                        <ul class="p-0 my-2 breadcrumb breadcrumb-transparent breadcrumb-dot font-weight-bold font-size-sm">
                            <li class="breadcrumb-item">
                                <a href="{{ route('dashboard') }}" class="text-muted">Dashboard</a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="" class="text-muted">ROI Monitoring</a>
                            </li>
                        </ul>
                        <!--end::Breadcrumb-->
                    </div>
                </div>

            </div>
        </div>
        <div class="content d-flex flex-column flex-column-fluid" id="kt_content">
            <!--begin::Entry-->
            <div class="d-flex flex-column-fluid">
                <!--begin::Container-->
                <div class="container-fluid">
                    <div class="flex-row d-flex">
                        <div class="flex-row-fluid ml-lg-8">
                            <div class="card card-custom gutter-b">
                                <div class="p-10 card-body row">
                                    <div class="col-md-6 col-lg-4">
                                        <div class="wallet-card"
                                            style="background: linear-gradient(135deg, #4361ee, #3a0ca3);">
                                            <div class="wallet-title">Total Users</div>
                                            <div class="wallet-amount">{{ number_format($summary['total_users']) }} </div>
                                            <div class="progress" style="height: 4px; background: rgba(255,255,255,0.2);">
                                                <div class="bg-white progress-bar" style="width: 60%"></div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6 col-lg-4">
                                        <div class="wallet-card"
                                            style="background: linear-gradient(135deg, #7209b7, #560bad);">
                                            <div class="wallet-title">2X Completed</div>
                                            <div class="wallet-amount">{{ number_format($summary['completed_users']) }}
                                            </div>
                                            <div class="progress" style="height: 4px; background: rgba(255,255,255,0.2);">
                                                <div class="bg-white progress-bar" style="width: 45%"></div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6 col-lg-4">
                                        <div class="wallet-card"
                                            style="background: linear-gradient(135deg, #f72585, #b5179e);">
                                            <div class="wallet-title">Active</div>
                                            <div class="wallet-amount">{{ number_format($summary['active_users']) }}</div>
                                            <div class="progress" style="height: 4px; background: rgba(255,255,255,0.2);">
                                                <div class="bg-white progress-bar" style="width: 30%"></div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6 col-lg-4">
                                        <div class="wallet-card"
                                            style="background: linear-gradient(135deg, #4ab3f8, #e96629);">
                                            <div class="wallet-title">Stopped</div>
                                            <div class="wallet-amount">{{ number_format($summary['stopped_users']) }}</div>
                                            <div class="progress" style="height: 4px; background: rgba(255,255,255,0.2);">
                                                <div class="bg-white progress-bar" style="width: 30%"></div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6 col-lg-4">
                                        <div class="wallet-card"
                                            style="background: linear-gradient(135deg, #0757ec, #07df61);">
                                            <div class="wallet-title">Expired</div>
                                            <div class="wallet-amount">{{ number_format($summary['expired_users']) }}</div>
                                            <div class="progress" style="height: 4px; background: rgba(255,255,255,0.2);">
                                                <div class="bg-white progress-bar" style="width: 30%"></div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6 col-lg-4">
                                        <div class="wallet-card"
                                            style="background: linear-gradient(135deg, #f52525, #03f3e7);">
                                            <div class="wallet-title">Completion Rate</div>
                                            <div class="wallet-amount">{{ number_format($summary['completion_rate'], 1) }}%
                                            </div>
                                            <div class="progress" style="height: 4px; background: rgba(255,255,255,0.2);">
                                                <div class="bg-white progress-bar" style="width: 30%"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-4 card">
                                <div class="card-body">
                                    <div class="row align-items-center">
                                        <div class="col-md-6">
                                            <ul class="nav nav-tabs filter-tabs">
                                                <li class="nav-item">
                                                    <a class="nav-link {{ $filter === 'all' ? 'active' : '' }}"
                                                        href="?filter=all&search={{ $search }}">
                                                        All Users
                                                    </a>
                                                </li>
                                                <li class="nav-item">
                                                    <a class="nav-link {{ $filter === 'completed' ? 'active' : '' }}"
                                                        href="?filter=completed&search={{ $search }}">
                                                        2X Completed
                                                    </a>
                                                </li>
                                                <li class="nav-item">
                                                    <a class="nav-link {{ $filter === 'active' ? 'active' : '' }}"
                                                        href="?filter=active&search={{ $search }}">
                                                        Active
                                                    </a>
                                                </li>
                                                <li class="nav-item">
                                                    <a class="nav-link {{ $filter === 'stopped' ? 'active' : '' }}"
                                                        href="?filter=stopped&search={{ $search }}">
                                                        Stopped
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="row">
                                                <div class="col-8">
                                                    <form method="GET" class="d-flex">
                                                        <input type="hidden" name="filter"
                                                            value="{{ $filter }}">
                                                        <input type="text" name="search" class="form-control"
                                                            placeholder="Search by name, email, or ID..."
                                                            value="{{ $search }}">
                                                        <button type="submit" class="btn btn-primary ms-2">
                                                            <i class="fas fa-search"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                                <div class="col-4">
                                                    <a href="#" class="btn btn-success">
                                                        <i class="fas fa-download"></i> Export
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card">
                                <div class="p-0 card-body">
                                    <div class="table-responsive">
                                        <table class="table mb-0 table-hover">
                                            <thead class="table table-dark">
                                                <tr> 
                                                    <th>User Info</th>
                                                    <th>Investment</th>
                                                    <th>ROI Progress</th>
                                                    <th>Status</th>
                                                    <th>Completion</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($paginatedUsers as $user)
                                                    <tr data-user-id="{{ $user->id }}"
                                                        data-investment="{{ number_format($user->roi_stats['invested_amount'], 2) }}"
                                                        data-two-x-limit="{{ number_format($user->roi_stats['two_x_limit'], 2) }}"
                                                        data-total-earned="{{ number_format($user->roi_stats['total_roi_paid'], 2) }}"
                                                        data-remaining="{{ number_format($user->roi_stats['remaining_amount'], 2) }}"> 
                                                        <td>
                                                            <div>
                                                                <strong>{{ $user->name }}</strong>
                                                                <br>
                                                                <small class="text-muted">Username: {{ $user->username }}</small>
                                                                <br>
                                                                <small class="text-muted">{{ $user->email }}</small>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <div>
                                                                <strong>${{ number_format($user->roi_stats['invested_amount']) }}</strong>
                                                                <br>
                                                                <small class="text-muted">
                                                                    2X Limit:
                                                                    ${{ number_format($user->roi_stats['two_x_limit']) }}
                                                                </small>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <div>
                                                                <strong>${{ number_format($user->roi_stats['total_roi_paid']) }}</strong>
                                                                <br>
                                                                <small class="text-success">
                                                                    Direct:
                                                                    ${{ number_format($user->roi_stats['direct_roi_paid']) }}
                                                                </small>
                                                                <br>
                                                                <small class="text-info">
                                                                    Commission:
                                                                    ${{ number_format($user->roi_stats['commission_earned']) }}
                                                                </small>
                                                                <br>
                                                                <small class="text-primary">
                                                                    Remaining:
                                                                    ${{ number_format($user->roi_stats['remaining_amount']) }}
                                                                </small>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            @php
                                                                $statusClass =
                                                                    [
                                                                        'completed' => 'bg-success',
                                                                        'active' => 'bg-primary',
                                                                        'stopped' => 'bg-warning',
                                                                        'expired' => 'bg-danger',
                                                                    ][$user->completion_status] ?? 'bg-secondary';
                                                            @endphp
                                                            <span class="badge {{ $statusClass }} status-badge">
                                                                {{ ucfirst($user->completion_status) }}
                                                            </span>
                                                            @if ($user->roi_stats['last_payment_date'])
                                                                <br>
                                                                <small class="text-muted">
                                                                    Last: {{ $user->roi_stats['last_payment_date'] }}
                                                                </small>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            <div class="completion-bar">
                                                                <div class="completion-fill bg-{{ $user->roi_stats['completion_percentage'] >= 100 ? 'success' : 'primary' }}"
                                                                    style="width: {{ min(100, $user->roi_stats['completion_percentage']) }}%">
                                                                </div>
                                                            </div>
                                                            <small
                                                                class="text-muted">{{ number_format($user->roi_stats['completion_percentage'], 1) }}%</small>
                                                        </td>
                                                        <td>
                                                            <div class="btn-group btn-group-sm">
                                                                @if ($user->completion_status === 'active')
                                                                    <button type="button" 
                                                                        class="btn btn-outline-warning btn-sm" 
                                                                        title="Stop ROI"
                                                                        onclick="showStopModal({{ $user->id }}, '{{ addslashes($user->name) }}')">
                                                                        <i class="fas fa-stop"></i>
                                                                    </button>
                                                                @elseif($user->completion_status === 'stopped')
                                                                    <button type="button" 
                                                                        class="btn btn-outline-success btn-sm" 
                                                                        title="Reactivate ROI"
                                                                        onclick="showReactivateModal({{ $user->id }}, '{{ addslashes($user->name) }}')">
                                                                        <i class="fas fa-play"></i>
                                                                    </button>
                                                                @endif
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="7" class="py-4 text-center">
                                                            <i class="mb-3 fas fa-inbox fa-3x text-muted"></i>
                                                            <br>
                                                            <span class="text-muted">No users found with the current
                                                                filters</span>
                                                        </td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <div class="flex-wrap mt-5 d-flex justify-content-center align-items-center">
                                <div class="flex-wrap py-2 mr-3 d-flex">
                                    @if ($paginatedUsers->onFirstPage())
                                        <a href="#" class="my-1 mr-2 btn btn-icon btn-sm btn-light-primary disabled"><i class="ki ki-bold-double-arrow-back icon-xs"></i></a>
                                        <a href="#" class="my-1 mr-2 btn btn-icon btn-sm btn-light-primary disabled"><i class="ki ki-bold-arrow-back icon-xs"></i></a>
                                    @else
                                        <a href="{{ $paginatedUsers->url(1) }}" class="my-1 mr-2 btn btn-icon btn-sm btn-light-primary"><i class="ki ki-bold-double-arrow-back icon-xs"></i></a>
                                        <a href="{{ $paginatedUsers->previousPageUrl() }}" class="my-1 mr-2 btn btn-icon btn-sm btn-light-primary"><i class="ki ki-bold-arrow-back icon-xs"></i></a>
                                    @endif
                                    
                                    @foreach ($paginatedUsers->getUrlRange(max(1, $paginatedUsers->currentPage() - 2), min($paginatedUsers->lastPage(), $paginatedUsers->currentPage() + 2)) as $page => $url)
                                        <a href="{{ $url }}" class="btn btn-icon btn-sm border-0 btn-hover-primary mr-2 my-1 {{ $page == $paginatedUsers->currentPage() ? 'active' : '' }}">
                                            {{ $page }}
                                        </a>
                                    @endforeach 
                                    @if ($paginatedUsers->hasMorePages())
                                        <a href="{{ $paginatedUsers->nextPageUrl() }}" class="my-1 mr-2 btn btn-icon btn-sm btn-light-primary"><i class="ki ki-bold-arrow-next icon-xs"></i></a>
                                        <a href="{{ $paginatedUsers->url($paginatedUsers->lastPage()) }}" class="my-1 mr-2 btn btn-icon btn-sm btn-light-primary"><i class="ki ki-bold-double-arrow-next icon-xs"></i></a>
                                    @else
                                        <a href="#" class="my-1 mr-2 btn btn-icon btn-sm btn-light-primary disabled"><i class="ki ki-bold-arrow-next icon-xs"></i></a>
                                        <a href="#" class="my-1 mr-2 btn btn-icon btn-sm btn-light-primary disabled"><i class="ki ki-bold-double-arrow-next icon-xs"></i></a>
                                    @endif
                                </div>
                            </div> 
                        </div>
                        <!--end::Layout-->
                    </div>
                    <!--end::Page Layout-->
                </div>
                <!--end::Container-->
            </div>
            <!--end::Entry-->
        </div>
    </div>


     <!-- Stop ROI Modal -->
<div class="modal fade" id="stopRoiModal" tabindex="-1" aria-labelledby="stopRoiModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="stopRoiForm" method="POST">
                @csrf
                <div class="modal-header bg-light-warning text-dark">
                    <h5 class="modal-title" id="stopRoiModalLabel">
                        <i class="fas fa-stop me-2 text-danger"></i> Stop ROI Account
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning" role="alert">
                        <i class="fas fa-exclamation-triangle me-2 text-danger"></i>
                        You are about to stop ROI for user: <strong id="stopUserName"></strong>
                    </div>

                    <div class="mb-3">
                        <label for="stopReason" class="form-label">Reason for stopping ROI:<span class="text-danger">*</span></label>
                        <select class="form-select form-control" id="stopReason" name="reason" required>
                            <option value="" selected disabled >Select a reason...</option>
                            <option value="manual_admin_stop">Manual Admin Stop</option>
                            <option value="policy_violation">Policy Violation</option>
                            <option value="suspicious_activity">Suspicious Activity</option>
                            <option value="user_request">User Request</option>
                            <option value="system_maintenance">System Maintenance</option>
                            <option value="other">Other</option>
                        </select>
                    </div>

                    <div class="mb-3" id="customReasonDiv" style="display: none;">
                        <label for="customReason" class="form-label">Custom Reason:</label>
                        <textarea class="form-control" id="customReason" name="custom_reason" rows="3" placeholder="Please specify the reason..."></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="stopMessage" class="form-label">Additional Message (Optional):</label>
                        <textarea class="form-control" id="stopMessage" name="message" rows="3" placeholder="Add any additional notes or message for this action..."></textarea>
                    </div>

                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="confirmStop" required>
                        <label class="form-check-label" for="confirmStop">
                            I confirm that I want to stop ROI for this user
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>Cancel
                    </button>
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-stop me-2"></i>Stop ROI
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Reactivate ROI Modal -->
<div class="modal fade" id="reactivateRoiModal" tabindex="-1" aria-labelledby="reactivateRoiModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="reactivateRoiForm" method="POST">
                @csrf
                <div class="modal-header bg-light-success text-dark">
                    <h5 class="modal-title" id="reactivateRoiModalLabel">
                        <i class="fas fa-play me-2 text-success"></i> Reactivate ROI Account
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info" role="alert">
                        <i class="fas fa-info-circle me-2"></i>
                        You are about to reactivate ROI for user: <strong id="reactivateUserName"></strong>
                    </div>

                    <div class="alert alert-warning" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Important:</strong> ROI can only be reactivated if the user has not reached their 2X limit. The system will automatically validate this before reactivation.
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Current Status:</label>
                        <ul class="list-unstyled">
                            <li><strong>Investment:</strong> $<span id="reactivateInvestment">0.00</span></li>
                            <li><strong>2X Limit:</strong> $<span id="reactivate2XLimit">0.00</span></li>
                            <li><strong>Total Earned:</strong> $<span id="reactivateTotalEarned">0.00</span></li>
                            <li><strong>Remaining:</strong> $<span id="reactivateRemaining">0.00</span></li>
                        </ul>
                    </div>

                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="confirmReactivate" required>
                        <label class="form-check-label" for="confirmReactivate">
                            I confirm that I want to reactivate ROI for this user
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>Cancel
                    </button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-play me-2"></i>Reactivate ROI
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
 



@endsection
@section('page_js')
    <script>
    function showStopModal(userId, userName) {
        document.getElementById('stopUserName').textContent = userName;
        const form = document.getElementById('stopRoiForm');
        form.action = `/users/roi/stop/${userId}`;
        const modal = new bootstrap.Modal(document.getElementById('stopRoiModal'));
        modal.show();
    }

    function showReactivateModal(userId, userName) {
        document.getElementById('reactivateUserName').textContent = userName;
        const form = document.getElementById('reactivateRoiForm');
        form.action = `/users/roi/reactivate/${userId}`;

        // Find user data from the table
        const userRow = document.querySelector(`tr[data-user-id="${userId}"]`);
        if (userRow) {
            const investment = userRow.dataset.investment || '0.00';
            const twoXLimit = userRow.dataset.twoXLimit || '0.00';
            const totalEarned = userRow.dataset.totalEarned || '0.00';
            const remaining = userRow.dataset.remaining || '0.00';

            document.getElementById('reactivateInvestment').textContent = investment;
            document.getElementById('reactivate2XLimit').textContent = twoXLimit;
            document.getElementById('reactivateTotalEarned').textContent = totalEarned;
            document.getElementById('reactivateRemaining').textContent = remaining;
        }

        const modal = new bootstrap.Modal(document.getElementById('reactivateRoiModal'));
        modal.show();
    }

    // Show/hide custom reason
    document.addEventListener('DOMContentLoaded', function () {
        const reasonSelect = document.getElementById('stopReason');
        const customDiv = document.getElementById('customReasonDiv');

        reasonSelect.addEventListener('change', function () {
            customDiv.style.display = this.value === 'other' ? 'block' : 'none';
        });
    });
</script>

@endsection

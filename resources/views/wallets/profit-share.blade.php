@extends('demo.layout.app')
@section('title','Profit Share')
@section('content')
 <!--begin::Content-->
 <div class="content d-flex flex-column flex-column-fluid" id="kt_content">
    <!--begin::Subheader-->
    <div class="py-2 subheader py-lg-6 subheader-solid" id="kt_subheader">
        <div class="flex-wrap container-fluid d-flex align-items-center justify-content-between flex-sm-nowrap">
            <!--begin::Info-->
            <div class="flex-wrap mr-1 d-flex align-items-center"> 
                <!--begin::Page Heading-->
                <div class="flex-wrap mr-5 d-flex align-items-baseline">
                    <!--begin::Page Title-->
                    <h5 class="my-1 mr-5 text-dark font-weight-bold">Profit Share Wallet </h5>
                    <!--end::Page Title-->
                    <!--begin::Breadcrumb-->
                    <ul class="p-0 my-2 breadcrumb breadcrumb-transparent breadcrumb-dot font-weight-bold font-size-sm">
                        <li class="breadcrumb-item">
                            <a href="{{  route('dashboard') }}" class="text-muted">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="#" class="text-muted">Wallets</a>
                        </li>   

                        <li class="breadcrumb-item">
                            <a href="" class="text-muted">Profit Share</a>
                        </li>
                    </ul> 
                </div> 
            </div>
             
        </div>
    </div> 
    <!--end::Subheader-->
    <!--begin::Entry-->
   <div class="container"> 
            <!-- Filter Card -->
            <div class="card card-custom gutter-b">
                <div class="card-header">
                    <div class="card-title">
                        <h3 class="card-label">Filters</h3>
                    </div>
                    <div class="card-toolbar">
                        <button type="button" class="btn btn-sm btn-light-primary" data-toggle="collapse" data-target="#filterForm">
                            <i class="fas fa-filter"></i> Toggle Filters
                        </button>
                    </div>
                </div>
                <div class="collapse {{ request()->hasAny(['date_from', 'date_to', 'level', 'search', 'min_percentage', 'max_percentage', 'min_balance', 'max_balance']) ? 'show' : '' }}" id="filterForm">
                    <div class="card-body">
                        <form method="GET" action="{{ route('wallets.profit.share') }}" id="filterFormData">
                            <div class="row">
                                <!-- Date Range -->
                                <div class="mb-3 col-md-3">
                                    <label class="form-label">Date From</label>
                                    <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                                </div>
                                <div class="mb-3 col-md-3">
                                    <label class="form-label">Date To</label>
                                    <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                                </div>
                                
                                <!-- Level Filter -->
                                <div class="mb-3 col-md-3">
                                    <label class="form-label">Level</label>
                                    <select name="level" class="form-control">
                                        <option value="all">All Levels</option>
                                        @foreach($levels as $level)
                                            <option value="{{ $level }}" {{ request('level') == $level ? 'selected' : '' }}>
                                                Level {{ $level }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                
                                <!-- Username Search -->
                                <div class="mb-3 col-md-3">
                                    <label class="form-label">Search Username</label>
                                    <input type="text" name="search" class="form-control" placeholder="Enter username" value="{{ request('search') }}">
                                </div>
                            </div>
                            
                            <div class="row">
                                <!-- Percentage Range -->
                                <div class="mb-3 col-md-3">
                                    <label class="form-label">Min Percentage</label>
                                    <input type="number" name="min_percentage" class="form-control" step="0.01" placeholder="0.00" value="{{ request('min_percentage') }}">
                                </div>
                                <div class="mb-3 col-md-3">
                                    <label class="form-label">Max Percentage</label>
                                    <input type="number" name="max_percentage" class="form-control" step="0.01" placeholder="100.00" value="{{ request('max_percentage') }}">
                                </div>
                                
                                <!-- Balance Range -->
                                <div class="mb-3 col-md-3">
                                    <label class="form-label">Min Balance</label>
                                    <input type="number" name="min_balance" class="form-control" step="0.01" placeholder="0.00" value="{{ request('min_balance') }}">
                                </div>
                                <div class="mb-3 col-md-3">
                                    <label class="form-label">Max Balance</label>
                                    <input type="number" name="max_balance" class="form-control" step="0.01" placeholder="0.00" value="{{ request('max_balance') }}">
                                </div>
                            </div>
                            
                            <div class="row">
                                <!-- Sort Options -->
                                <div class="mb-3 col-md-3">
                                    <label class="form-label">Sort By</label>
                                    <select name="sort_by" class="form-control">
                                        <option value="created_at" {{ request('sort_by') == 'created_at' ? 'selected' : '' }}>Date</option>
                                        <option value="level" {{ request('sort_by') == 'level' ? 'selected' : '' }}>Level</option>
                                        <option value="percentage" {{ request('sort_by') == 'percentage' ? 'selected' : '' }}>Percentage</option>
                                        <option value="balance" {{ request('sort_by') == 'balance' ? 'selected' : '' }}>Balance</option>
                                    </select>
                                </div>
                                <div class="mb-3 col-md-3">
                                    <label class="form-label">Sort Order</label>
                                    <select name="sort_order" class="form-control">
                                        <option value="desc" {{ request('sort_order') == 'desc' ? 'selected' : '' }}>Descending</option>
                                        <option value="asc" {{ request('sort_order') == 'asc' ? 'selected' : '' }}>Ascending</option>
                                    </select>
                                </div>
                                
                                <!-- Action Buttons -->
                                <div class="mb-3 col-md-6 d-flex align-items-end">
                                    <button type="submit" class="mr-2 btn btn-primary">
                                        <i class="fas fa-search"></i> Apply Filters
                                    </button>
                                    <a href="{{ route('wallets.profit.share') }}" class="btn btn-secondary">
                                        <i class="fas fa-times"></i> Clear Filters
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
            
    <div class=" flex-column-fluid"> 
        <div class="container"> 
            <div class="card card-custom gutter-b">
                <!--begin::Header-->
                <div class="row">
                    <!-- Total Earning Card -->
                    <div class="mb-4 col-md-6">
                        <div class="border-0 shadow-lg card rounded-3 h-100">
                            <div class="text-center card-body d-flex flex-column align-items-center justify-content-center">
                                <div class="p-3 mb-3 bg-primary bg-opacity-10 rounded-circle">
                                    <i class="fas fa-chart-line fa-2x text-primary"></i>
                                </div>
                                <h6 class="text-muted text-uppercase fw-bold">Total Earning</h6>
                                <h2 class="mt-2 fw-bold text-primary">
                                    ${{ number_format($totalEarning, 2) }}
                                </h2>
                                <p class="mb-0 small text-muted">All-time profit share earnings</p>
                            </div>
                        </div>
                    </div>

                    <!-- Current Balance Card -->
                    <div class="mb-4 col-md-6">
                        <div class="border-0 shadow-lg card rounded-3 h-100">
                            <div class="text-center card-body d-flex flex-column align-items-center justify-content-center">
                                <div class="p-3 mb-3 bg-success bg-opacity-10 rounded-circle">
                                    <i class="fas fa-wallet fa-2x text-success"></i>
                                </div>
                                <h6 class="text-muted text-uppercase fw-bold">Current Balance</h6>
                                <h2 class="mt-2 fw-bold text-success">
                                    ${{ number_format($currentBalance, 2) }}
                                </h2>
                                <p class="mb-0 small text-muted">Available balance in wallet</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="px-6 mt-3 mb-3 d-flex justify-content-between align-items-center">
                    @php
                        $blockedWallets = json_decode($setting->blocked_wallets ?? '{}', true);
                    @endphp
                    @if (!($blockedWallets['profit_share'] ?? false))
                        <a href="#" data-toggle="modal" data-target="#WithdrawModel" class="px-4 btn btn-info rounded-sm-pill">Transfer to Online Wallet</a>
                    @endif
                    <a href="{{ route('show.transaction.history') }}" class="px-4 btn btn-outline-primary rounded-sm-pill">Show Transaction History</a>
                </div>
                <!--end::Header-->
                <!--begin::Body-->
                <div class="py-0 card-body">
                    <!--begin::Table-->
                    <div class="table-responsive">
                        <table class="table table-head-custom table-vertical-center" id="kt_advance_table_widget_4">
                            <thead>
                                <tr class="text-left"> 
                                    <th class="pl-0" style="">S#</th>

                                    <th style="min-width: 110px">User Name</th>
                                    <th style="min-width: 110px">Level</th>
                                    <th style="min-width: 110px">Percentage</th>
                                    <th style="min-width: 110px">PV</th> 
                                    <th style="min-width: 120px">Date</th> 
                                </tr>
                            </thead>
                            <tbody>


                                

                                @foreach($wallets as $profit)
                                <tr class="pl-0">
                                    <td>
                                        <span href="#" class="text-dark-75 font-weight-bolder d-block font-size-sm">{{ $loop->iteration }}</span>
                                    </td>  
                                    <td>
                                        <a class="text-dark-75 font-weight-bolder d-block font-size-sm">{{ $profit->form->username }}</a> 
                                    </td>   
                                    <td>
                                        <span class="text-dark-75 font-weight-bolder d-block font-size-sm">{{ $profit->level }} </span> 
                                    </td> 
                                    <td>
                                        <span class="text-dark-75 font-weight-bolder d-block font-size-sm">{{ $profit->percentage }}</span> 
                                    </td> 
                                    <td>
                                        <span class="text-dark-75 font-weight-bolder d-block font-size-sm">{{ $profit->balance }}</span> 
                                    </td> 
                                    <td>
                                        <span class="text-dark-75 font-weight-bolder d-block font-size-sm">{{ $profit->created_at->format('d-m-Y') }}</span> 
                                    </td> 
                                </tr>  
                                @endforeach



                               
                            </tbody>
                        </table>
                    </div>
                    <!--end::Table-->
                </div>
                <!--end::Body-->
            </div>
            <!--end::Advance Table Widget 10-->
        </div>
        
    </div>
    <!--end::Entry-->
</div>
 

@if (!($blockedWallets['profit_share'] ?? false)) 
    @include("wallets.transfer_modal", ['wallet' => 'profit_share'])
@endif  
  
@endsection
@section('page_js')
    <script>
         var avatar = new KTImageInput('kt_profile_avatar');  

    </script>
    
@endsection
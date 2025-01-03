@extends('demo.layout.app')
@section('title','Online')
@section('content')
 <!--begin::Content-->
 <div class="content d-flex flex-column flex-column-fluid" id="kt_content">
    <!--begin::Subheader-->
    <div class="subheader py-2 py-lg-6 subheader-solid" id="kt_subheader">
        <div class="container-fluid d-flex align-items-center justify-content-between flex-wrap flex-sm-nowrap">
            <!--begin::Info-->
            <div class="d-flex align-items-center flex-wrap mr-1"> 
                <!--begin::Page Heading-->
                <div class="d-flex align-items-baseline flex-wrap mr-5">
                    <!--begin::Page Title-->
                    <h5 class="text-dark font-weight-bold my-1 mr-5">Online  Wallet </h5>
                    <!--end::Page Title-->
                    <!--begin::Breadcrumb-->
                    <ul class="breadcrumb breadcrumb-transparent breadcrumb-dot font-weight-bold p-0 my-2 font-size-sm">
                        <li class="breadcrumb-item">
                            <a href="{{  route('dashboard') }}" class="text-muted">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="#" class="text-muted">Wallets</a>
                        </li>   

                        <li class="breadcrumb-item">
                            <a href="" class="text-muted">Online Wallet</a>
                        </li>
                    </ul> 
                </div> 
            </div>
             
        </div>
    </div> 
    <!--end::Subheader-->
    <!--begin::Entry-->
    <div class="  flex-column-fluid"> 
        <div class="container"> 


            <div class="col-lg-12 col-xxl-12 order-1 order-xxl-2">  
                <div class="card card-custom card-stretch gutter-b"> 
                    <div class="card-header border-0"> 
                        <h3 class="card-title font-weight-bolder text-dark">Account Balance Details</h3> 
                    </div> 
                    <div class="card-body pt-0"> 
                        <div class="mb-10">
                            <!--begin::Section-->
                            <div class="d-flex align-items-center">
                                <!--begin::Symbol-->
                                <div class="symbol symbol-45 symbol-light mr-5">
                                    <span class="symbol-label">
                                        <span class="navi-icon">
                                            <i class="flaticon2-graph-1 text-warning"></i>
                                        </span>
                                    </span>
                                </div> 
                                <div class="d-flex flex-column flex-grow-1">
                                    <a href="#" class="font-weight-bold text-dark-75 text-hover-primary font-size-lg mb-1">Total Balance</a>
                                    <span class=" font-weight-bold">{{ $onlineWallets->sum('balance') }} PV</span>
                                </div> 
                                <!--begin::Text-->
                                <div class="symbol symbol-45 symbol-light mr-5">
                                    <span class="symbol-label">
                                        <span class="navi-icon">
                                            <i class=" flaticon-calendar-3 text-warning"></i>
                                        </span>
                                    </span>
                                </div> 
                                <div class="d-flex flex-column flex-grow-1">
                                    <a href="#" class="font-weight-bold text-dark-75 text-hover-primary font-size-lg mb-1">Last Transaction Date</a>
                                    <span class="font-weight-bold">
                                        {{ $onlineWallets->sortByDesc('created_at')->first()->created_at ?? 'No records found' }}  
                                    </span>
                                </div> 
                            </div> 
                        </div>  
                    </div> 
                </div> 

                <div class="card card-custom card-stretch gutter-b"> 
                    <div class="card-header border-0">
                        <h3 class="card-title font-weight-bolder text-dark">Funds Transfer</h3> 
                    </div>
                    <div class="card-body pt-0"> 
                        <div class="mb-10">
                            <!--begin::Section-->
                            <div class="d-flex align-items-center">
                                <a href="#" data-toggle="modal" data-target="#WithdrawModel" class="mr-3 rounded-0 btn btn-info font-weight-bolder font-size-sm">Bank Transfer</a>

                                <a href="{{ route('show.transaction.history') }}"   class="rounded-0 btn btn-primary font-weight-bolder font-size-sm">Show Transaction History</a> 
                            </div> 
                        </div>  
                    </div> 
                </div> 
            </div> 
        </div>
        
    </div>
    <!--end::Entry-->
</div>
  
<!--end::Content-->
@endsection
@section('page_js')
    <script>
         var avatar = new KTImageInput('kt_profile_avatar');  

    </script>
    
@endsection
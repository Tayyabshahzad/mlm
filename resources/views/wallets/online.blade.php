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
                                
                                @if($walletSum >= 700)
                                    <div class="d-flex align-items-center">
                                        <a href="#" disabled  class="disabled mr-3 rounded-0 btn btn-info font-weight-bolder font-size-sm">Create Withdrawal Request</a>
                                        <a href="#" disabled   class="disabled mr-3 rounded-0 btn btn-primary font-weight-bolder font-size-sm">Transfer to Member </a>    
                                    </div>
                                @else 
                                <div class="d-flex align-items-center">
                                    <a href="#"   data-toggle="modal"   data-target="#WithdrawModel"  class=" mr-3 rounded-0 btn btn-info font-weight-bolder font-size-sm">Create Withdrawal Request</a>
                                    <a href="#"   data-toggle="modal"    data-target="#WithdrawModelTransfer"  class=" mr-3 rounded-0 btn btn-primary font-weight-bolder font-size-sm">Transfer to Member </a>    
                                </div> 
                                @endif

                                    

                              
                            </div> 

                            <div class="d-flex align-items-center mt-10">
                                <!--begin::Symbol-->
                                <div class="symbol symbol-45 symbol-light mr-5">
                                    <span class="symbol-label">
                                        <span class="navi-icon">
                                            <i class="flaticon2-graph-1 text-warning"></i>
                                        </span>
                                    </span>
                                </div> 
                                <div class="d-flex flex-column flex-grow-1">
                                    <a href="#" class="font-weight-bold text-dark-75 text-hover-primary font-size-lg mb-1">Total Earned PV</a>
                                    <span class=" font-weight-bold">{{ $walletSum }} PV</span>
                                </div> 
                                @if($walletSum >= 700)
                                    <div class="d-flex align-items-center">
                                        <a href="#" data-toggle="modal" data-target="#WithdrawModel" class="mr-3 rounded-0 btn btn-warning font-weight-bolder font-size-sm">Buy Product </a> 
                                    </div>  
                                @endif

                               

                            </div> 
                        </div>  
                    </div> 
                </div> 

                <div class="card card-custom card-stretch gutter-b"> 
                    <div class="card-header border-0">
                        <h3 class="card-title font-weight-bolder text-dark">Withdrawal Requests</h3> 
                    </div>
                    <div class="card-body pt-0"> 
                        <div class="mb-10">
                            <div class="table-responsive">
                                <table class="table table-head-custom table-vertical-center" id="kt_advance_table_widget_4">
                                    <thead>
                                        <tr class="text-left"> 
                                            <th class="pl-0" style="">S#</th>  
                                            <th style="min-width: 110px">Amount</th>
                                            <th style="min-width: 110px">Status</th> 
                                            <th style="min-width: 120px">Date</th> 
                                            <th style="min-width: 120px">Action</th> 
                                        </tr>
                                    </thead>
                                    <tbody>  
                                        @foreach($withDrawsRequests as $withDrawsRequest)
                                            <tr class="pl-0">
                                                <td>
                                                    <span href="#" class="text-dark-75 font-weight-bolder d-block font-size-sm">{{ $loop->iteration }}</span>
                                                </td>  
                                                <td>
                                                    <a class="text-dark-75 font-weight-bolder d-block font-size-sm">{{ $withDrawsRequest->amount    }}</a> 
                                                </td>   
                                                <td>
                                                    <span class="text-dark-75 font-weight-bolder d-block font-size-sm text-warning ">{{ ucfirst($withDrawsRequest->status) }} </span>  
                                                </td> 
                                                <td>
                                                    <span class="text-dark-75 font-weight-bolder d-block font-size-sm">{{ $withDrawsRequest->created_at->format('d-m-Y') }}</span> 
                                                </td> 
                                                <td>
                                                @if($withDrawsRequest->status == 'pending' )
                                                    <button 
                                                        type="button"
                                                        class="rounded-0 btn btn-sm btn-light-danger WithdrawDelete"
                                                        data-toggle="modal" 
                                                        data-target="#WithdrawDelete"
                                                        data-id="{{ $withDrawsRequest->id }}">
                                                    Delete Request
                                                    </button>  
                                                    @else
                                                        ----------
                                                    @endif
                                                </td> 
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
        
    </div>
    <!--end::Entry-->
</div>

<div class="modal fade" id="WithdrawModel" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="{{ route('withdrawals.store') }}" method="POST">
                @csrf
                <input type="hidden" name="wallet_type" value="online" required>
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Withdrawal</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <i aria-hidden="true" class="ki ki-close"></i>
                    </button>
                </div>
                <div class="modal-body"> 
                    <div class="form-group row"> 
                        <div class="col-lg-12 col-xl-12">
                            <label for="" class="font-weight-bold mr-2">
                                Amount <span class="text-danger">*</span>
                            </label>
                            <input type="number" class="form-control form-control-sm form-control-solid mb-2" 
                             name="amount" min="0.01" step="0.01"
                             required 
                             max="{{ $onlineWallets->sum('balance') }}"
                             placeholder="Enter Amount"
                             >  
                        </div>  

                        <div class="col-lg-12 col-xl-12">
                            <label for="" class="font-weight-bold mr-2">
                                Description 
                            </label>
                            <textarea name="target_account_details" id="target_account_details" class="form-control form-control-sm form-control-solid mb-2" required></textarea>
                            
                        </div>  
                    </div>  
                </div>
                <div class="modal-footer">
                    <button type="button" class="rounded-0 btn btn-light-primary btn-sm" data-dismiss="modal">Close</button>
                    <button type="submit" class="rounded-0 btn btn-primary btn-sm">Withdrawal </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="WithdrawModelTransfer" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="{{ route('withdrawals.member.transfer') }}" method="POST">
                @csrf
                <input type="hidden" name="wallet_type" value="online" required>
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Member Transfer</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <i aria-hidden="true" class="ki ki-close"></i>
                    </button>
                </div>
                <div class="modal-body"> 
                    <div class="form-group row"> 
                        <div class="col-lg-12 col-xl-12">
                            <label for="" class="font-weight-bold mr-2">
                                Username / Email Address <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control form-control-sm form-control-solid mb-2" 
                             name="source"   
                             required 
                             max="{{ $onlineWallets->sum('balance') }}"
                             placeholder="Enter Amount"
                             >  
                        </div>  

                        <div class="col-lg-12 col-xl-12">
                            <label for="" class="font-weight-bold mr-2">
                                Amount <span class="text-danger">*</span>
                            </label>
                            <input type="number" class="form-control form-control-sm form-control-solid mb-2" 
                             name="amount" min="0.01" step="0.01"
                             required 
                             max="{{ $onlineWallets->sum('balance') }}"
                             placeholder="Enter Amount"
                             >  
                        </div>  

                        <div class="col-lg-12 col-xl-12">
                            <label for="" class="font-weight-bold mr-2">
                                Description 
                            </label>
                            <textarea name="target_account_details" id="target_account_details" class="form-control form-control-sm form-control-solid mb-2" required></textarea>
                            
                        </div>  
                    </div>  
                </div>
                <div class="modal-footer">
                    <button type="button" class="rounded-0 btn btn-light-primary btn-sm" data-dismiss="modal">Close</button>
                    <button type="submit" class="rounded-0 btn btn-primary btn-sm">Withdrawal </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="WithdrawDelete" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="{{ route('withdrawals.delete') }}" method="POST">
                @csrf
                <input type=" " name="request_id" id="request-id" required>
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Delete Withdrawal Request</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <i aria-hidden="true" class="ki ki-close"></i>
                    </button>
                </div>
                <div class=" "> 
                    <div class="form-group row">  
                        <p class="text-danger-75 font-weight-bolder  p-12  pb-1 text-danger">
                           Are You Sure to Delete Withdrawal Request ? 
                        </p> 
                    </div>  
                </div>
                <div class="modal-footer">
                    <button type="button" class="rounded-0 btn btn-light-primary btn-sm" data-dismiss="modal">Close</button>
                    <button type="submit" class="rounded-0 btn btn-danger btn-sm">Delete Request </button>
                </div>
            </form>
        </div>
    </div>
</div>
  
<!--end::Content-->
@endsection
@section('page_js')
    <script>
         var avatar = new KTImageInput('kt_profile_avatar');  
         $(document).ready(function() {
            $('.WithdrawDelete').on('click', function() {
                var requestId = $(this).data('id');
                $('#request-id').val(requestId);
            });
        });

    </script>
    
@endsection
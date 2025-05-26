@extends('demo.layout.app')
@section('title','Online')
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
                    <h5 class="my-1 mr-5 text-dark font-weight-bold">Online  Wallet </h5>
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
                            <a href="" class="text-muted">Online Wallet</a>
                        </li>
                    </ul> 
                </div> 
            </div>
             
        </div>
    </div> 
    <!--end::Subheader-->
    <!--begin::Entry-->
    <div class=" flex-column-fluid"> 
        <div class="container"> 


            <div class="order-1 col-lg-12 col-xxl-12 order-xxl-2"> 
                @if(!Auth::user()->freez_wallet )   
                @if(!(float) Auth::user()->negative_pv > 0)
                        <div class="card card-custom card-stretch gutter-b ">  
                            <div class="border-0 card-header">  
                                @php
                                    $blockedWallets = json_decode($setting->blocked_wallets ?? '{}', true); 
                                @endphp
                                @if (!($blockedWallets['online'] ?? false))  
                                    @if($setting->withdraw_block == false)
                                        @if($walletSum >= 700)
                                            <div class="pt-5 align-items-center justify-content-center" >
                                                <a href="#" disabled  class="mb-5 mr-3 disabled rounded-0 btn btn-info font-weight-bolder font-size-sm">Withdrawal Request</a>
                                                <a href="#" disabled   class="mb-5 mr-3 disabled rounded-0 btn btn-primary font-weight-bolder font-size-sm">Transfer to Member </a>    
                                            </div>
                                        @else 
                                            <div class="pt-5 align-items-center justify-content-center">
                                                    <a href="#"   data-toggle="modal"   data-target="#WithdrawModel"  class="mb-5 mr-3 rounded-0 btn btn-info font-weight-bolder font-size-sm">Create Withdrawal Request</a>
                                                    <a href="#"   data-toggle="modal"    data-target="#WithdrawModelTransfer"  class="mb-5 mr-3 rounded-0 btn btn-primary font-weight-bolder font-size-sm">Member Transfer </a> 
                                            </div> 
                                        @endif
                                    @else
                                @endif 
                                @endif

                                <div class="pt-5 align-items-center justify-content-center" > 
                                    <a href="#"  data-toggle="modal"   data-target="#TopUpAccount"  class="mb-5 mr-3 rounded-0 btn btn-success font-weight-bolder font-size-sm">TopUp Your Account</a>    
                                </div>

                            </div> 
                        </div>
                    @endif
                @endif
               
               
               
                <div class="card card-custom card-stretch gutter-b"> 
                    <div class="border-0 card-header"> 
                        <h3 class="card-title font-weight-bolder text-dark">Account Balance Details</h3> 
                    </div> 


                    <div class="pt-0 card-body"> 
                        <div class="mb-10">
                            <!--begin::Section-->
                            <div class="d-flex align-items-center">
                                <!--begin::Symbol-->
                                <div class="mr-5 symbol symbol-45 symbol-light">
                                    <span class="symbol-label">
                                        <span class="navi-icon">
                                            <i class="flaticon2-graph-1 text-warning"></i>
                                        </span>
                                    </span>
                                </div> 
                                <div class="d-flex flex-column flex-grow-1">
                                    <a href="#" class="mb-1 font-weight-bold text-dark-75 text-hover-primary font-size-lg">Total Balance</a>
                                     <span class=" font-weight-bold">${{ $onlineWallets->sum('balance') }}</span>
                                </div>  
                                <div class="mr-5 symbol symbol-45 symbol-light">
                                    <span class="symbol-label">
                                        <span class="navi-icon">
                                            <i class=" flaticon-calendar-3 text-warning"></i>
                                        </span>
                                    </span>
                                </div> 
                                <div class="d-flex flex-column flex-grow-1">
                                    <a href="#" class="mb-1 font-weight-bold text-dark-75 text-hover-primary font-size-lg">Last Transaction Date</a>
                                    <a href="{{ route('show.transaction.history') }}">View Transaction History</a>
                                    <span class="font-weight-bold">
                                        {{ $onlineWallets->sortByDesc('created_at')->first()->created_at ?? 'No records found' }}  
                                    </span>
                                </div>   


                                <div class="d-flex flex-column flex-grow-1 text-danger">
                                    <a href="#" class="mb-1 font-weight-bold text-dark-75 text-hover-primary font-size-lg">
                                        Negative Points
                                    </a>
                                     @if(Auth::user()->negative_pv > 0)
                                        <small>
                                            <button class="mt-1 mb-3 btn btn-sm btn-danger no-radius rounded-0" data-toggle="modal" data-target="#clearNegativeModal">
                                                Clear Points Now
                                            </button>
                                        </small>
                                    @endif

                                    <span class="font-weight-bold">
                                        -${{ round(Auth::user()->negative_pv,2) }}
                                    </span>
                                </div>   
                            </div> 

                            <div class="mt-10 d-flex align-items-center">
                                <!--begin::Symbol-->
                                <div class="mr-5 symbol symbol-45 symbol-light">
                                    <span class="symbol-label">
                                        <span class="navi-icon">
                                            <i class="flaticon2-graph-1 text-warning"></i>
                                        </span>
                                    </span>
                                </div> 
                                <div class="d-flex flex-column flex-grow-1">
                                    <a href="#" class="mb-1 font-weight-bold text-dark-75 text-hover-primary font-size-lg">Total Earned</a>
                                    <span class=" font-weight-bold">${{ $walletSum }}</span>
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
                    <div class="border-0 card-header">
                        <h3 class="card-title font-weight-bolder text-dark">Withdrawal Requests</h3> 
                    </div>
                    <div class="pt-0 card-body"> 
                        <div class="mb-10">
                            <div class="table-responsive">
                                <table class="table table-head-custom table-vertical-center" id="kt_advance_table_widget_4">
                                    <thead>
                                        <tr class="text-left"> 
                                            <th class="pl-0" style="">S#</th>  
                                            <th style="min-width: 110px">Amount</th>
                                            <th style="min-width: 110px">Status</th> 
                                            <th style="min-width: 120px">Request Type</th>
                                            <th style="min-width: 120px">Fee <small class="text-danger">(In case Bank)</small></th> 
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
                                                    <span class="text-dark-75 font-weight-bolder d-block font-size-sm text-warning ">{{ ucfirst($withDrawsRequest->request_type) }} </span>  
                                                </td> 


                                                <td>
                                                    <span class="text-dark-75 font-weight-bolder d-block font-size-sm text-warning ">{{ ucfirst($withDrawsRequest->transfer_fee) }} </span>  
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
                    <p><strong>Available Balance: {{ $onlineWallets->sum('balance') }} </strong></p>
                    <div class="form-group row"> 
                        <div class="col-lg-12 col-xl-12">
                            <label for="" class="mr-2 font-weight-bold">
                                Amount <span class="text-danger">*</span>
                            </label>
                            <input type="number" class="mb-2 form-control form-control-sm form-control-solid" 
                             name="amount" min="15" step="0.01"
                             required 
                             placeholder="Enter Amount"
                             >  
                             <small class="text-danger">Minimum Transfer Amount: $15</small> 
                        </div>  
                        
                        <div class="col-lg-12 col-xl-12">
                            <label for="" class="mr-2 font-weight-bold">
                                Description 
                            </label>
                            <textarea name="target_account_details" id="target_account_details" class="mb-2 form-control form-control-sm form-control-solid" required></textarea>
                        </div>  
                        <div class="col-lg-12 col-xl-12">
                            <label for="" class="mb-10 mr-2 font-weight-bold ">
                                Choose Withdraw Option <span class="text-danger"> *</span>
                            </label>
                            <div>
                                <input type="radio" value="bank" class="" name="withdrawal_option"> Bank
                                <input type="radio" value="usdt" name="withdrawal_option"> USDT
                                <input type="radio" value="cash" name="withdrawal_option"> Cash
                            </div>
                            
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
                <input type="hidden" name="wallet_type" value="member_transfer" required>
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Member Transfer</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <i aria-hidden="true" class="ki ki-close"></i>
                    </button>
                </div>
                <div class="modal-body"> 
                    <div class="form-group row"> 
                        <div class="col-lg-12 col-xl-12">
                            <label for="" class="mr-2 font-weight-bold">
                                Username / Email Address <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="mb-2 form-control form-control-sm form-control-solid" 
                             name="member_account"   
                             required  
                             placeholder="Enter Member Account email or password"
                             >  
                        </div>  

                        <div class="col-lg-12 col-xl-12">
                            <label for="" class="mr-2 font-weight-bold">
                                Amount <span class="text-danger">*</span>
                            </label>
                            <input type="number" class="mb-2 form-control form-control-sm form-control-solid" 
                             name="amount" min="5" step="0.01"
                             required 
                             max="{{ $onlineWallets->sum('balance') }}"
                             placeholder="Enter Amount"
                             > 
                             <small class="text-danger">Minimum Transfer Amount: 5PV</small> 
                        </div>  

                        <div class="col-lg-12 col-xl-12">
                            <label for="" class="mr-2 font-weight-bold">
                                Description 
                            </label>
                            <textarea name="description" id="description" class="mb-2 form-control form-control-sm form-control-solid"></textarea>
                            
                        </div>  
                    </div>  
                </div>
                <div class="modal-footer">
                    <button type="button" class="rounded-0 btn btn-light-primary btn-sm" data-dismiss="modal">Close</button>
                    <button type="submit" class="rounded-0 btn btn-primary btn-sm">Transfer </button>
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
                <input type="hidden" name="request_id" id="request-id" required>
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Delete Withdrawal Request</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <i aria-hidden="true" class="ki ki-close"></i>
                    </button>
                </div>
                <div class=""> 
                    <div class="form-group row">  
                        <p class="p-12 pb-1 text-danger-75 font-weight-bolder text-danger">
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

<div class="modal fade" id="TopUpAccount" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="{{ route('account.topups.process') }}" method="POST">
                @csrf
                <input type="hidden" name="wallet_type" value="online" required>
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">TopUp Your Account</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <i aria-hidden="true" class="ki ki-close"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <p><strong>Available Balance: {{ $onlineWallets->sum('balance') }} </strong></p>
                    <div class="form-group row">
                        <div class="col-lg-12 col-xl-12">
                            <label for="" class="mr-2 font-weight-bold">
                                Amount <span class="text-danger">*</span>
                            </label>
                            <input type="number" class="mb-2 form-control form-control-sm form-control-solid"
                             name="topUp_amount" min="5" max="{{ $onlineWallets->sum('balance') }}" step="0.01"
                             required
                             placeholder="Enter Amount"
                             >
                        </div>

                        <div class="col-lg-12 col-xl-12">
                            <label for="" class="mr-2 font-weight-bold">
                                Description
                            </label>
                            <textarea name="topUp_description"
                          class="mb-2 form-control form-control-sm form-control-solid"
                            required></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="rounded-0 btn btn-light-primary btn-sm" data-dismiss="modal">Close</button>
                    <button type="submit" class="rounded-0 btn btn-primary btn-sm">TopUp </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="clearNegativeModal" tabindex="-1" role="dialog" aria-labelledby="clearNegativeModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <form action="{{ route('clear.negative.points') }}" method="POST">
          @csrf
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="clearNegativeModalLabel">Clear Negative Points</h5>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <div class="modal-body">
                You currently have <strong>{{ Auth::user()->negative_pv }}</strong> negative points.<br>
                On clicking <strong>Submit</strong>, this amount will be deducted from your online wallet.
            </div>
            <div class="modal-footer">
              <button type="submit" class="btn btn-success btn-sm rounded-0">Yes, Clear Points</button>
              <button type="button" class="btn btn-secondary rounded-0" data-dismiss="modal">Cancel</button>
            </div>
          </div>
      </form>
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
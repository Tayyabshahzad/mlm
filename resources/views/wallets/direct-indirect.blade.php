@extends('demo.layout.app')
@section('title','Direct/Indirect')
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
                    <h5 class="text-dark font-weight-bold my-1 mr-5">Direct/Indirect Wallet </h5>
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
                            <a href="" class="text-muted">Direct/Indirect</a>
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
            <div class="card card-custom gutter-b">
                <!--begin::Header-->
                <div class="card-header border-0 py-5">
                    <h3 class="card-title align-items-start flex-column">
                        <span class="card-label font-weight-bolder text-dark">Total Balance : {{ $wallets->sum('balance') }} PV</span> 
                    </h3>
                    <div class="card-toolbar">
                        <a href="#" data-toggle="modal" data-target="#WithdrawModel" class="mr-3 rounded-0 btn btn-info font-weight-bolder font-size-sm">Transfer to Online Wallet</a>

                        <a href="{{ route('show.transaction.history') }}"   class="rounded-0 btn btn-primary font-weight-bolder font-size-sm">Show Transaction History</a>
                    </div>
                </div>
                <!--end::Header-->
                <!--begin::Body-->
                <div class="card-body py-0">
                    <!--begin::Table-->
                    <div class="table-responsive">
                        <table class="table table-head-custom table-vertical-center" id="kt_advance_table_widget_4">
                            <thead>
                                <tr class="text-left"> 
                                    <th class="pl-0" style="">S#</th>

                                    <th style="min-width: 110px">User Name</th>
                                    <th style="min-width: 110px">Commission</th>
                                    <th style="min-width: 110px">PV</th>
                                    <th style="min-width: 110px">Commission Type</th>
                                    <th style="min-width: 110px">Level</th> 
                                    <th style="min-width: 120px">Date</th> 
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($wallets as $wallet)
                                <tr class="pl-0">
                                    <td>
                                        <span href="#" class="text-dark-75 font-weight-bolder d-block font-size-sm">{{ $loop->iteration }}</span>
                                    </td>  
                                    <td>
                                        <a class="text-dark-75 font-weight-bolder d-block font-size-sm">{{ $wallet->form->username }}</a> 
                                    </td>  
                                    <td>
                                        <span class="text-dark-75 font-weight-bolder d-block font-size-sm"> 
                                           @if($wallet->commission_type == 'direct') {{ $wallet->direct_balance }} @else  {{ $wallet->indirect_balance }}  @endif%
                                        </span> 
                                    </td>
                                    <td>
                                        <span class="text-dark-75 font-weight-bolder d-block font-size-sm"> 
                                            @if($wallet->commission_type == 'direct') {{ $wallet->direct_balance }} @else  {{ $wallet->indirect_balance }}  @endif PV
                                         </span> 
                                    </td>
                                    <td>   <span class="text-dark-75 font-weight-bolder d-block font-size-sm"> {{ ucfirst($wallet->commission_type) }} </span>    </td>
                                    <td>   <span class="text-dark-75 font-weight-bolder d-block font-size-sm">{{ ucfirst($wallet->level) }}</span>    </td>
                                    <td> 
                                        {{ $wallet->created_at }}
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
 
<div class="modal fade" id="WithdrawModel" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="{{ route('wallet.transfer.to.online') }}" method="POST">
                @csrf
                <input type="hidden" name="wallet_type" value="direct_indirect" required>
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Transfer to Online Wallet</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <i aria-hidden="true" class="ki ki-close"></i>
                    </button>
                </div>
                <div class="modal-body">
                <p class="text-center text-danger">
                    5% Will charge on every transaction 
                </p>
                    <div class="form-group row"> 
                        <div class="col-lg-12 col-xl-12">
                            <label for="" class="font-weight-bold mr-2">
                                Transfer Amount
                            </label>
                            <input type="number" class="form-control form-control-sm form-control-solid mb-2" 
                             name="amount" min="7" step="0.01"
                             required
                             max="{{ $wallets->sum('balance') }}"
                             placeholder="Enter Amount">  
                             <strong class="text-danger">Available Balance : {{ $wallets->sum('balance') }}</strong>
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
 

 
<!--end::Content-->
@endsection
@section('page_js')
    <script>
         var avatar = new KTImageInput('kt_profile_avatar');  

    </script>
    
@endsection
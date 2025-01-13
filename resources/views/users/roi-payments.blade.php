@extends('demo.layout.app')
@section('title','ROI')
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
                    <h5 class="text-dark font-weight-bold my-1 mr-5">Return on Investment </h5>
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
                            <a href="" class="text-muted">ROI</a>
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
                       
                    </h3>
                    <div class="card-toolbar">
                        <a  href="{{ route('run-schedule') }}"   class="mr-3 rounded-0 btn btn-primary font-weight-bolder font-size-sm">Generate ROI</a> 
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
                                    <th style="min-width: 110px">Username</th>
                                    <th style="min-width: 110px">Amount</th>  
                                    <th style="min-width: 120px">Percentage</th>
                                    <th style="min-width: 120px">Amount Remaining</th>   
                                    <th style="min-width: 120px">Start Date</th> 
                                    <th style="min-width: 120px">End Date</th> 
                                </tr>
                            </thead>
                            <tbody> 
                                @foreach ($payments as $payment )
                                    <tr class="text-left"> 
                                        <td class="pl-0" style="">{{ $loop->iteration }}</td>
                                        <td style="min-width: 110px">{{ $payment->user->name }}</td>
                                        <td style="min-width: 110px">{{ $payment->amount }} </td>  
                                        <td style="min-width: 120px">{{ $payment->percentage }}</td>  
                                        <td style="min-width: 120px">{{ $payment->user->current_pv_balance - $payment->user->roi_wallet_balance  }}</td>  
                                        <td style="min-width: 120px">{{ $payment->user->roi_start_date ? $payment->user->roi_start_date : '--' }}</td> 
                                        <td style="min-width: 120px">{{ $payment->user->roi_end_date ?  $payment->user->roi_end_date : '--' }}</td> 
                                    </tr>
                                @endforeach
                            </tbody>
                            @if(!$payments>0)
                                <tfoot class="text-center">
                                    <th colspan="7" class="p-5 text-danger">No Roi Payments Found</th>
                                </tfoot>
                            @endif
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
            <form action="{{ route('submit.roi.payments') }}" method="POST">
                @csrf
                
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Make an ROI</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <i aria-hidden="true" class="ki ki-close"></i>
                    </button>
                </div>
                <div class="modal-body"> 
                    <div class="form-group row"> 

                        <div class="col-lg-12 col-xl-12">
                            <label for="" class="font-weight-bold mr-2">
                                Select Member
                            </label>
                            <select class="form-control form-control-sm form-control-solid mb-2" 
                             name="user_id">
                                <option disabled selected value=""> Select Member </option>
                                @foreach ($users as $user )
                                <option  value="{{ $user->id }}"> {{ $user->name}} </option>
                                @endforeach
                             </select
                             >  
                        </div>  


                        <div class="col-lg-12 col-xl-12">
                            <label for="" class="font-weight-bold mr-2">
                                Transfer Amount
                            </label>
                            <input type="number" class="form-control form-control-sm form-control-solid mb-2" 
                             name="commission_percentage" min="0.01" step="0.01"
                             required
                             
                             placeholder="Commission Percentage"
                             >  
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
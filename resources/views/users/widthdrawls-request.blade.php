@extends('demo.layout.app')
@section('title','Withdrawal Requests')
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
                    <h5 class="text-dark font-weight-bold my-1 mr-5">Withdrawal Requests</h5>
                    <!--end::Page Title-->
                    <!--begin::Breadcrumb-->
                    <ul class="breadcrumb breadcrumb-transparent breadcrumb-dot font-weight-bold p-0 my-2 font-size-sm">
                        <li class="breadcrumb-item">
                            <a href="{{  route('dashboard') }}" class="text-muted">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="#" class="text-muted">User Info</a>
                        </li>   

                        <li class="breadcrumb-item">
                            <a href="" class="text-muted">Withdrawal Requests</a>
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
                        <span class="card-label font-weight-bolder text-dark">Total Requests : {{ $withDrawsRequests->count() }}</span> 
                    </h3>
                    <div class="card-toolbar">
                        <a href="#" data-toggle="modal" data-target="#WithdrawModel" class="mr-3 rounded-0 btn btn-info font-weight-bolder font-size-sm">Total Approved: {{ $withDrawsRequests->where('status','approved')->count() }}</a> 
                        <a href="{{ route('show.transaction.history') }}"   class="mr-3 rounded-0 btn btn-warning font-weight-bolder font-size-sm">Total Pending: {{ $withDrawsRequests->where('status','pending')->count() }}</a>
                        <a href="{{ route('show.transaction.history') }}"   class="rounded-0 btn btn-danger font-weight-bolder font-size-sm">Total Rejected: {{ $withDrawsRequests->where('status','rejected')->count() }}</a>
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
                                    <th style="min-width: 120px">Status</th>
                                    <th style="min-width: 120px">Request Type</th>  
                                    <th style="min-width: 120px">Date</th> 
                                    <th style="min-width: 120px">Details</th> 
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($withDrawsRequests as $requests )
                                    <tr class="text-left"> 
                                        <td class="pl-0" style="">{{ $loop->iteration }}</td>
                                        <td style="min-width: 110px">{{ $requests->user->username }}</td>
                                        <td style="min-width: 110px">{{ $requests->amount }} </td>  
                                        <td style="min-width: 120px"> <span class="btn @if($requests->status == 'pending')btn-outline-warning @elseif($requests->status == 'rejected') btn-outline-danger @else btn-outline-success @endif btn-sm">{{ ucfirst($requests->status) }}</span></td>  
                                        <td style="min-width: 120px">{{  ucwords($requests->request_type)  }}</td>   
                                        <td style="min-width: 120px">{{ $requests->created_at  }}</td>   
                                        <td style="min-width: 120px"> <button  data-id="{{ $requests->id }}" data-toggle="modal" data-target="#WithdrawModel" 
                                            class="view-details-btn btn btn-sm btn-outline-info"> <i class="far fa-eye"></i> </button> </td>   
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
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content md ">
            <form action="{{ route('withdraw.request.update') }}" method="POST" enctype="multipart/form-data">
                @csrf
                 
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Withdrawal Request</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <i aria-hidden="true" class="ki ki-close"></i>
                    </button>
                </div>
                <div class="modal-body" id="modal-content">
                    
                    
                </div>
                <div class="modal-footer">
                    <button type="button" class="rounded-0 btn btn-light-primary btn-sm" data-dismiss="modal">Close</button>
                    <button type="submit" class="rounded-0 btn btn-danger btn-sm">Update </button>
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
        $('.view-details-btn').on('click', function() {
            var requestId = $(this).data('id');
            var url = "{{ route('withdraw.request.details', ':id') }}".replace(':id', requestId);

            // Clear the modal content
            $('#modal-content').html('<p>Loading...</p>');

            // Fetch the details
            $.ajax({
                url: url,
                method: 'GET',
                success: function(response) {
                    // Populate the modal with the response data
                    $('#request_status').val(response.status)
                     
                    var content = `
                        <input type="hidden" name="request_id" value="${response.id}"/>
                        <table  class="table table-head-custom table-vertical-center "> 
                            <tr>
                                <th> Username </th>
                                <th> Amount </th>
                                <th> Status </th>
                                <th> Request Type </th>
                                <th> Transfer Fee </th>
                                <th> Created At </th>
                            </tr>
                            <tr class="bg-warning">    
                                <td>  ${response.username} </td>
                                <td>  ${response.amount} </td>
                                <td>  ${response.status} </td>
                                <td>  ${response.request_type} </td>
                                <td>  ${response.transfer_fee} </td>
                                <td>  ${response.created_at} </td>
                            </tr>
                        </table>

                        <div class="row">
                            <!-- Left Column (6 Columns) -->
                            <div class="col-lg-8">
                                <table class="table table-bordered ">
                                    <tr>
                                        <th colspan="4" class="text-center"> Bank Details </th>  
                                    </tr> 
                                    <tr>
                                        <th colspan="2"> Bank Name </th>  
                                        <td colspan="2">  ${response.bank_name} </td>    
                                    </tr>
                                
                                    <tr>
                                        <th colspan="2"> Account Title </th>  
                                        <td colspan="2">  ${response.account_title} </td> 
                                    </tr>
                                    <tr>
                                        <th colspan="2"> Account Number </th>  
                                        <td colspan="2">  ${response.ibn_number} </td>    
                                    </tr>

                                    <tr>
                                        <th colspan="2"> USDT Address </th>  
                                        <td colspan="2">  ${response.account_number} </td> 
                                    </tr>
                                    <tr>
                                        <th colspan="2"> Update Status </th>  
                                        <td colspan="2">
                                            <select class='form-control' name='status' required id='request_status'>
                                                <option value="" selected disabled>Update Request Status</option> 
                                                <option value="approved">Approved</option>
                                                 <option value="rejected">Reject</option> 
                                            </select> 
                                        </td> 
                                    </tr> 

                                    <tr>
                                        <th colspan="2"> Screenshot </th>  
                                        <td colspan="2">
                                            <input type="file" name="screenshot"/> 
                                        </td> 
                                    </tr> 
                                </table>
                                 <table class="table table-bordered">
                                    <tr>
                                        <th  class="text-center"> Current USD Rate </th>  
                                    </tr>  
                                     <tr>
                                        <td  class="text-center"> ${response.current_usd} </td>  
                                    </tr>  
                                </table>
                            </div>

                            <!-- Right Column (6 Columns) -->
                            <div class="col-lg-4">
                                <table class="table table-bordered">
                                    <tr>
                                        <th colspan="2" class="text-center"> Payable Amount </th>  
                                    </tr> 
                                    <tr>
                                        <td class="text-center text-danger " colspan="2">  <b> ${response.payable_amount} PKR</b> </td>  
                                    </tr>
                                    <tr>
                                        <th colspan="2" class="text-center"> Transaction Screenshot </th>  
                                    </tr>
                                    <tr>
                                        <td colspan="2" class="text-center">  
                                            <img src="${response.withdraw_screenshot}" class="img img-thumbnail" width="400"/>  
                                        </td>  
                                    </tr>
                                </table>
                            </div> 

                        </div>




                                
                        
                    `;
                    $('#modal-content').html(content);
                },
                error: function(xhr) {
                    $('#modal-content').html('<p class="text-danger">Error fetching details.</p>');
                }
            });
        });
    });

    </script>
    
@endsection
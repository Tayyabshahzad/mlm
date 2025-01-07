@extends('demo.layout.app')
@section('title','Members')
@section('content')
 <!--begin::Content-->
 <div class="content d-flex flex-column flex-column-fluid" id="kt_content">
    <!--begin::Subheader-->

    
    <div class="subheader py-2 py-lg-6 subheader-solid" id="kt_subheader">
        <div class="container-fluid d-flex align-items-center justify-content-between flex-wrap flex-sm-nowrap">
            <!--begin::Info-->
            <div class="d-flex align-items-center flex-wrap mr-1">
                <!--begin::Mobile Toggle-->
                <button class="burger-icon burger-icon-left mr-4 d-inline-block d-lg-none" id="kt_subheader_mobile_toggle">
                    <span></span>
                </button> 
                <div class="d-flex align-items-baseline flex-wrap mr-5"> 
                    <h5 class="text-dark font-weight-bold my-1 mr-5">Members </h5>
                    <!--end::Page Title-->
                    <!--begin::Breadcrumb-->
                    <ul class="breadcrumb breadcrumb-transparent breadcrumb-dot font-weight-bold p-0 my-2 font-size-sm">
                        <li class="breadcrumb-item">
                            <a href="{{  route('dashboard') }}" class="text-muted">Dashboard</a>
                        </li> 
                        <li class="breadcrumb-item">
                            <a href="" class="text-muted">Members</a>
                        </li>
                    </ul>
                    <!--end::Breadcrumb-->
                </div>


                

                
                <!--end::Page Heading-->
            </div>
             
        </div>
    </div>
    <div class="content d-flex flex-column flex-column-fluid" id="kt_content">
        <!--begin::Entry-->
        <div class="d-flex flex-column-fluid">
            <!--begin::Container-->
            <div class="container-fluid">
                <!--begin::Page Layout-->
                <div class="d-flex flex-row">
                    <div class="flex-row-fluid ml-lg-8"> 
                        <div class="card card-custom gutter-b">
                            <div class="card-body p-0"> 
                                <div class="row justify-content-center py-8 px-8 py-md-10 px-md-0">
                                    <div class="col-md-10">
                                        <div class="table-responsive">
                                            <table class="table">
                                                <thead>
                                                    <tr> 
                                                        <th class="text-left font-weight-bold text-muted text-uppercase">S#</th>
                                                        <th class="text-left font-weight-bold text-muted text-uppercase">Username</th>
                                                        <th class="text-left font-weight-bold text-muted text-uppercase">Email Address</th>
                                                        <th class="text-left pr-0 font-weight-bold text-muted text-uppercase">PV Balance</th>
                                                        <th class="text-left pr-0 font-weight-bold text-muted text-uppercase">Details</th>
                                                        <th class="text-left pr-0 font-weight-bold text-muted text-uppercase">Status</th>
                                                        <th class="text-left pr-0 font-weight-bold text-muted text-uppercase">Joining Date</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($teamMembers as $teamMember)
                                                    <tr class="font-weight-boldest"> 
                                                        <td class="border-0    pt-7  "> 
                                                            {{ $loop->iteration }}
                                                        </td>

                                                        <td class="border-0 pl-0 pt-7 d-flex align-items-center">
                                                        <!--begin::Symbol-->
                                                        <div class="symbol symbol-40 flex-shrink-0 mr-4 bg-light">
                                                            <div class="symbol-label" 
                                                            style="background-image: url('{{asset($teamMember->getFirstMediaUrl('user_profile_images')) }}')"></div>
                                                        </div>
                                                         {{ $teamMember->username }}
                                                        </td>
                                                        <td class="text-left pt-7 align-middle">{{ $teamMember->email }}</td>
                                                        <td class="text-left pt-7 align-middle">{{ $teamMember->current_pv_balance }}</td>
                                                      
                                                        <td class="text-primary pr-0 pt-7 text-left align-middle">
                                                            <button type="button" 
                                                                data-toggle="modal"
                                                                data-target="#userDetails"
                                                                data-id="{{ $teamMember->id }}"
                                                                class="btn btn-sm btn-outline-primary btn-outline-info user-details-btn"> 
                                                                <i     class="flaticon-medical "></i>
                                                            </button>    
                                                        </td>

                                                        <td class="text-primary pr-0 pt-7 text-left align-middle">
                                                            <button type="button" 
                                                            @if(! $teamMember->can_login == 1)
                                                                data-toggle="modal"
                                                                data-target="#changeStatus"
                                                            @endif
                                                            data-id="{{ $teamMember->id }}"
                                                            class="radius btn btn-sm {{  $teamMember->can_login == 1 ? 'btn-outline-primary disabled' : 'btn-outline-danger' }}"> 
                                                            {{ $teamMember->can_login == 1 ? 'Activated ' : 'Active' }} 
                                                        </button>
                                                        </td>

                                                        <td class="text-left pt-7 align-middle">{{ $teamMember->created_at->diffForHumans()  }}</td>

                                                        
                                                    </tr>
                                                    @endforeach 
                                                    
                                                </tbody>
                                            </table>
                                            <div class="d-flex justify-content-between align-items-center flex-wrap">
                                                <div class="d-flex flex-wrap py-2 mr-3">
                                                    {{-- Previous Page Link --}}
                                                    @if ($teamMembers->onFirstPage())
                                                        <a href="#" class="btn btn-icon btn-sm btn-light-primary mr-2 my-1 disabled"><i class="ki ki-bold-double-arrow-back icon-xs"></i></a>
                                                        <a href="#" class="btn btn-icon btn-sm btn-light-primary mr-2 my-1 disabled"><i class="ki ki-bold-arrow-back icon-xs"></i></a>
                                                    @else
                                                        <a href="{{ $teamMembers->url(1) }}" class="btn btn-icon btn-sm btn-light-primary mr-2 my-1"><i class="ki ki-bold-double-arrow-back icon-xs"></i></a>
                                                        <a href="{{ $teamMembers->previousPageUrl() }}" class="btn btn-icon btn-sm btn-light-primary mr-2 my-1"><i class="ki ki-bold-arrow-back icon-xs"></i></a>
                                                    @endif
                                             
                                                    @foreach ($teamMembers->getUrlRange(max(1, $teamMembers->currentPage() - 2), min($teamMembers->lastPage(), $teamMembers->currentPage() + 2)) as $page => $url)
                                                        <a href="{{ $url }}" class="btn btn-icon btn-sm border-0 btn-hover-primary mr-2 my-1 {{ $page == $teamMembers->currentPage() ? 'active' : '' }}">
                                                            {{ $page }}
                                                        </a>
                                                    @endforeach 
                                                    @if ($teamMembers->hasMorePages())
                                                        <a href="{{ $teamMembers->nextPageUrl() }}" class="btn btn-icon btn-sm btn-light-primary mr-2 my-1"><i class="ki ki-bold-arrow-next icon-xs"></i></a>
                                                        <a href="{{ $teamMembers->url($teamMembers->lastPage()) }}" class="btn btn-icon btn-sm btn-light-primary mr-2 my-1"><i class="ki ki-bold-double-arrow-next icon-xs"></i></a>
                                                    @else
                                                        <a href="#" class="btn btn-icon btn-sm btn-light-primary mr-2 my-1 disabled"><i class="ki ki-bold-arrow-next icon-xs"></i></a>
                                                        <a href="#" class="btn btn-icon btn-sm btn-light-primary mr-2 my-1 disabled"><i class="ki ki-bold-double-arrow-next icon-xs"></i></a>
                                                    @endif
                                                </div>
                                            </div>
                                            
                                        </div>
                                    </div>
                                </div> 
                            </div>
                        </div>
                        <!--end::Card-->
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

<div class="modal fade" id="changeStatus" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">  Change Member Status </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <i aria-hidden="true" class="ki ki-close"></i>
                </button>
            </div>
            <form action="{{ route('users.status.update') }}" method="POST">
                @method('put')
                @csrf
                <div class="modal-body">
                    <div class="form-group row"> 
                        <div class="col-lg-12 col-xl-12">
                        <p>Are you sure to update your member status ? </p> 
                        </div>  
                    </div> 
                    <input type="hidden" name="member_id" id="member_id"> 
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light-primary font-weight-bold" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary font-weight-bold">Update</button>
                </div>
            </form>
        </div>
    </div>
</div> 
<div class="modal fade" id="userDetails" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">  Member Details </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <i aria-hidden="true" class="ki ki-close"></i>
                </button>
            </div> 
                <div class="modal-body">
                    <div id="loading-spinner" style="display: none; text-align: center;">
                        <p>Loading...</p>
                    </div>
                    <div id="user-details-content">
                        
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light-primary font-weight-bold" data-dismiss="modal">Close</button>
                  
                </div>
            
        </div>
    </div>
</div>
 
@endsection
@section('page_js')
   
<script>
    $(document).ready(function() {
$('[data-target="#changeStatus"]').click(function() {
    var id = $(this).data('id');
    $('#member_id').val(id);
    $('#statusForm').attr('action', '/team/members/status/' + id + '/update');
});
});
    

$(document).ready(function () {
// When modal is triggered
$('.user-details-btn').on('click', function () {
    var memberId = $(this).data('id');
    $('#member_id').val(memberId); // Set hidden input value
    $('#user-details-content').html(''); // Clear previous content
    $('#loading-spinner').show(); // Show loader 
    $.ajax({
        url: '/users/details',  
        method: 'GET',
        data: { id: memberId },
        success: function (response) {
            $('#loading-spinner').hide(); // Hide loader
            if (response.success) {
                var userDetails = `
                    <table class='table table-bordered'>
                        <tr> <th> Name </th> <td> ${response.data.name}</td> </tr>
                        <tr> <th> Email </th> <td> ${response.data.email}</td> </tr>
                        <tr> <th> Joined </th> <td> ${response.data.created_at}</td> </tr>
                        <tr> <th> Status </th> <td> ${response.data.status}</td> </tr> 
                         <tr> <th> Transaction Id </th> <td> ${response.data.transaction_id}</td> </tr> 
                    </table>
                `;
                if (response.data.amount_proof) {
                    userDetails += `

                    <table class='table table-bordered'>
                        <tr> <th> Amount Proof </th>   </tr>
                        <tr> <th> <img src="${response.data.amount_proof}" alt="Amount Proof Image" style="max-width: 100%; height: auto;" /> </th>  </tr> 
                    </table>
 
                    `;
                } else {
                    userDetails += `<p><strong>Amount Proof:</strong> Not Available</p>`;
                }
                $('#user-details-content').html(userDetails);
            } else {
                $('#user-details-content').html('<p>Error fetching user details.</p>');
            }
        },
        error: function () {
            $('#loading-spinner').hide(); // Hide loader
            $('#user-details-content').html('<p>Unable to fetch user details.</p>');
        }
    });
});
}); 

</script>
    
@endsection
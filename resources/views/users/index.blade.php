@extends('demo.layout.app')
@section('title','Members')
@section('custom_css')
    <style>
        .table td, .table th{
            padding:2px!important;
            vertical-align: center
        }
    </style>
@endsection
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

                                        <div class="btn-group" role="group" aria-label="Basic example">

                                            
                                            <button type="button" class="rounded-0 mr-3 mb-3 btn btn-primary">Total Members : {{ $totalMembers }}</button>
                                            <button type="button" class="rounded-0 mr-3 mb-3 btn btn-success">Active Members : {{ $totalActiveMembers }}</button>
                                            <button type="button" class="rounded-0 mr-3 mb-3 btn btn-warning">Inactive Members : {{ $totalInActiveMembers }}</button>
                                            <button type="button" class="rounded-0 mr-3 mb-3 btn btn-danger">Blocked Members : {{ $totalBlockedMembers }}</button>
                                            <button type="button" class="rounded-0 mr-3 mb-3 btn btn-info">Freeze Accounts : {{ $totalfreezeMembers }}</button>
                                            <button type="button" class="rounded-0 mr-3 mb-3 btn btn-success" data-toggle="modal" data-target="#downloadModal">
                                                Download Contact Details
                                            </button>
                                            
                                        </div>
 
                                        <form method="GET" action="{{ route('users.index') }}" class="mb-4">
                                            <div class="input-group">
                                                <input 
                                                    type="text" 
                                                    name="search" 
                                                    class="form-control rounded-0" 
                                                    placeholder="Search by username, name, or email" 
                                                    value="{{ old('search', $search ?? '') }}">
                                                <div class="input-group-append">
                                                    <button type="submit" class="mr-3 btn-sm rounded-0 btn btn-info">Search</button>
                                                    <a href="{{ route('users.index') }}" class="  rounded-0 btn btn-success">Clear</a>
                                                </div>
                                            </div>
                                        </form>
                                        <div class="table-responsive-sm">
                                            <table class="table" style="padding:0!important">
                                                <thead>
                                                    <tr> 
                                                        <th class=" text-uppercase">S#</th>
                                                        <th class=" text-uppercase">Username</th>
                                                        <th class=" text-uppercase">Name</th>
                                                        <th class=" text-uppercase">Email Address</th>  
                                                        <th class=" text-uppercase">Payment Method</th>
                                                        <th class=" text-uppercase">Actions</th> 
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($teamMembers as $teamMember)
                                                    <tr class="pa-0 
                                                    @if($teamMember->blocked) text-danger @endif
                                                    @if($teamMember->can_login) text-success @endif
                                                    @if(!$teamMember->can_login) text-warning @endif
                                                    @if($teamMember->freez_wallet) text-info @endif"> 
                                                        <td class=" align-middle">{{ $loop->iteration }}</td>
                                                        <td class=" align-middle">{{ $teamMember->username }}</td>
                                                        <td class=" align-middle">{{ $teamMember->name }}</td>
                                                        <td class=" align-middle">{{ $teamMember->email }}</td> 
                                                        <td class=" align-middle">{{ ucfirst($teamMember->payment_method) }}</td> 
                                                        <td class="text-primary  align-middle"> 
                                                        <div class="dropdown">
                                                            <button class="btn btn-sm btn-outline-info rounded-0 dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                               More Actions
                                                            </button>
                                                            <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                                                <a class="dropdown-item text-info user-details-btn"   data-id="{{ $teamMember->id }}" data-target="#userDetails" data-toggle="modal" href="#">
                                                                    Info
                                                                </a>
                                                                <a class="dropdown-item text-warning" 
                                                                    @if(! $teamMember->can_login == 1)
                                                                        data-toggle="modal"
                                                                        data-target="#changeStatus"
                                                                    @endif
                                                                        data-id="{{ $teamMember->id }}"

                                                                href="#"> {{ $teamMember->can_login == 1 ? 'Activated ' : 'Active' }} </a>
                                                                <a class="dropdown-item text-primary" href="{{ route('user.info', $teamMember->id) }}">Details</a>
                                                                <a class="dropdown-item text-danger"
                                                                    data-toggle="modal"
                                                                    data-target="#deleteUser"
                                                                    data-id="{{ $teamMember->id }}"
                                                                href="#">Delete</a>
                                                            </div>
                                                        </div>

                                                            
                                                             
                                                        </td> 
                                                    </tr>
                                                    @endforeach 
                                                </tbody>
                                            </table>
                                            
                                            <!-- Pagination Controls (no changes needed here) -->
                                            <div class="d-flex justify-content-center align-items-center flex-wrap mt-5">
                                                <div class="d-flex flex-wrap py-2 mr-3">
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
            <form action="{{ route('users.status.update') }}"  id="updateUserForm" method="POST">
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
                    <button type="button" class="btn btn-light-primary font-weight-bold rounded-0" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary font-weight-bold rounded-0"  id="updateUser">Update</button>
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
<div class="modal fade" id="deleteUser" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabelDelete" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabelDelete">  Delete User </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <i aria-hidden="true" class="ki ki-close"></i>
                </button>
            </div>
            <form action="{{ route('user.delete') }}"  id="deleteUserForm" method="POST">
                
                @csrf
                <div class="modal-body"> 
                    <input type="hidden" name="delete_id" id="delete_id"> 
                    <p class="text-center text-danger">Are you sure you want to delete this member? <br>This action cannot be undone. </p>  
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light-primary btn-sm font-weight-bold rounded-0" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-danger btn-sm font-weight-bold rounded-0"  id="deleteUser">Delete</button>
                </div>
            </form>
        </div>
    </div>
</div>  

<!-- Modal for Date Selection -->
<div class="modal fade" id="downloadModal" tabindex="-1" aria-labelledby="downloadModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Select Date Range</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('download.contacts') }}" method="GET">
                <div class="modal-body">
                    <label for="start_date">Start Date</label>
                    <input type="date" name="start_date" id="start_date" class="form-control" required>

                    <label for="end_date" class="mt-3">End Date</label>
                    <input type="date" name="end_date" id="end_date" class="form-control" required>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Download</button>
                </div>
            </form>
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


   $(document).ready(function() {
        $('[data-target="#deleteUser"]').click(function() { 
            var id = $(this).data('id');
            $('#delete_id').val(id);  
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
                        <tr> <th> Payment Method </th> <td> ${response.data.payment_method}</td> </tr> 
                        <tr> <th> Activation Code </th> <td> ${response.data.activationCode.code}</td> </tr> 
                        <tr> <th> Code Generated By</th> <td> ${response.data.activationCode.generated_by}</td> </tr> 
                        
                    </table>
                `;
                if (response.data.amount_proof) {
                    userDetails += `

                    <table class='table table-bordered'>
                        <tr> <th> Amount Proof </th>   </tr>
                        <tr> <th> <img src="${response.data.amount_proof}" alt="Amount Proof Image"  class="img img-thumbnail"style="max-width: 100%; height: auto;" /> </th>  </tr> 
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


<script>
    $(document).ready(function () {
        $('#updateUser').on('click', function () { 
            $(this).prop('disabled', true); 
            $(this).text('Updating...');
            $('#updateUserForm').submit();
        });

        
    });
</script>


    
@endsection
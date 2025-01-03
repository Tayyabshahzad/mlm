@extends('demo.layout.app')
@section('title','Team Members')
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
                    <h5 class="text-dark font-weight-bold my-1 mr-5">Team Members </h5>
                    <!--end::Page Title-->
                    <!--begin::Breadcrumb-->
                    <ul class="breadcrumb breadcrumb-transparent breadcrumb-dot font-weight-bold p-0 my-2 font-size-sm">
                        <li class="breadcrumb-item">
                            <a href="{{  route('dashboard') }}" class="text-muted">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="#" class="text-muted">Team</a>
                        </li>   

                        <li class="breadcrumb-item">
                            <a href="" class="text-muted">Team Members</a>
                        </li>
                    </ul> 
                </div> 
            </div>
             
        </div>
    </div>

    
    <!--end::Subheader-->
    <!--begin::Entry-->
    <div class="  sflex-column-fluid"> 
        <div class="container-fluid"> 
            <div class="card card-custom gutter-b"> 
                <div class="card-body py-2 ">
                    <!--begin::Table-->
                    <div class="table-responsive">
                        <table class="table table-head-custom table-vertical-center" id="kt_advance_table_widget_4">
                            <thead>
                                <tr class="text-left"> 
                                    <th class="pl-0" style="">S#</th>
                                    <th style="min-width: 110px">Name</th>
                                    <th style="min-width: 110px"> <span class="text-info">Email</span>  </span> </th>
                                    <th style="min-width: 110px"> <span class="text-info">PV Balance</span>  </span> </th>
                                    <th style="min-width: 120px">Joining Date</th> 
                                    <th style="min-width: 120px">Detail</th> 
                                    <th style="min-width: 120px">Status</th> 
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($teamMembers as $teamMember)
                                <tr> 
                                    <td class="pl-0">
                                        <a href="#" class="text-dark-75 font-weight-bolder text-hover-primary font-size-lg">{{ $loop->iteration }}</a>
                                    </td>
                                    <td>
                                        <span class="text-dark-75 font-weight-bolder d-block font-size-lg"> {{ $teamMember->name }} </span> 
                                    </td>
                                    <td>
                                        <span class="text-info font-weight-bolder d-block font-size-lg">{{ $teamMember->email }}</span> 
                                    </td>
                                    <td>
                                        <span class="text-dark-75 font-weight-bolder d-block font-size-lg">100</span> 
                                    </td> 
                                    <td>
                                        <span class="text-dark-75 font-weight-bolder d-block font-size-lg">{{ $teamMember->created_at }}</span> 
                                    </td> 

                                    <td>
                                         

                                         <button type="button" 
                                            data-toggle="modal"
                                            data-target="#userDetails"
                                            data-id="{{ $teamMember->id }}"
                                            class="btn btn-sm btn-outline-primary btn-outline-info user-details-btn"> 
                                            <i     class="flaticon-medical "></i>
                                        </button>


                                    </td> 
                                    <td>
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

<div class="modal fade" id="changeStatus" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">  Change Member Status </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <i aria-hidden="true" class="ki ki-close"></i>
                </button>
            </div>
            <form action="{{ route('genealogy.team.member.status.update') }}" method="POST">
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
                        <!-- Content will be populated dynamically -->
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light-primary font-weight-bold" data-dismiss="modal">Close</button>
                  
                </div>
            
        </div>
    </div>
</div>
 
<!--end::Content-->
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
            url: '/genealogy/team/members/details',  
            method: 'GET',
            data: { id: memberId },
            success: function (response) {
                $('#loading-spinner').hide(); // Hide loader
                if (response.success) {
                    var userDetails = `
                        <p><strong>Name:</strong> ${response.data.name}</p>
                        <p><strong>Email:</strong> ${response.data.email}</p>
                        <p><strong>Joined:</strong> ${response.data.created_at}</p>
                        <p><strong>Status:</strong> ${response.data.status}</p>
                    `;
                    if (response.data.amount_proof) {
                        userDetails += `
                            <p><strong>Amount Proof:</strong></p>
                            <img src="${response.data.amount_proof}" alt="Amount Proof Image" style="max-width: 100%; height: auto;" />
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
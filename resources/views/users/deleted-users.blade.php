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
                            <a href="" class="text-muted">Deleted user</a>
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
                                        <div class="table-responsive-sm">
                                            <table class="table" style="padding:0!important">
                                                <thead>
                                                    <tr> 
                                                        <th class=" text-uppercase">S#</th>
                                                        <th class=" text-uppercase">Username</th>
                                                        <th class=" text-uppercase">Name</th>
                                                        <th class=" text-uppercase">Email Address</th> 
                                                        <th class=" text-uppercase">Actions</th> 
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($teamMembers as $teamMember)
                                                    <tr class="pa-0  text-danger"> 
                                                        <td class=" align-middle">{{ $loop->iteration }}</td>
                                                        <td class=" align-middle">{{ $teamMember->username }}</td>
                                                        <td class=" align-middle">{{ $teamMember->name }}</td>
                                                        <td class=" align-middle">{{ $teamMember->email }}</td> 
                                                        <td class="text-primary  align-middle">
                                                            
                                                        <div class="dropdown">
                                                            <button class="btn btn-sm btn-outline-info rounded-0 dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                               More Actions
                                                            </button>
                                                            <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">  
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

@endsection
@section('page_js') 
    <script> 
    $(document).ready(function() {
            $('[data-target="#deleteUser"]').click(function() { 
                var id = $(this).data('id');
                $('#delete_id').val(id);  
            });
    });
    </script> 
@endsection
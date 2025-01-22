@extends('demo.layout.app')
@section('title','Product')
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
                    <h5 class="text-dark font-weight-bold my-1 mr-5">  Report </h5>
                    <!--end::Page Title-->
                    <!--begin::Breadcrumb-->
                    <ul class="breadcrumb breadcrumb-transparent breadcrumb-dot font-weight-bold p-0 my-2 font-size-sm">
                        <li class="breadcrumb-item">
                            <a href="{{  route('dashboard') }}" class="text-muted">Dashboard</a>
                        </li> 
                        <li class="breadcrumb-item">
                            <a href="" class="text-muted">Reports</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="" class="text-muted">Genealogy Report</a>
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
                                                <form action="{{ route('report.genealogy.tree') }}" method="get">
                                                    <thead>
                                                        <tr> 
                                                            <th class="text-left font-weight-bold text-muted text-uppercase">
                                                                <div class="form-group">
                                                                <select name="user" id="" class="form-control" required>
                                                                        <option value="" disabled selected> Select User</option>
                                                                        @foreach ($users as $user )
                                                                            <option value="{{ $user->id }}">  {{ $user->name }} </option>
                                                                        @endforeach
                                                                </select>
                                                                </div>
                                                            </th> 
                                                            <th class="text-left font-weight-bold text-muted text-uppercase">
                                                                <div class="form-group">
                                                                <button class="btn btn-sm btn-info "> Generate Report </button>
                                                                </div>
                                                            </th> 
                                                        </tr>
                                                    </thead>
                                                </form>
                                                 
                                            </table> 
                                            <div class="col-md-10 text-right">
                                                <a href="{{ route('report.genealogy.tree.download',7) }}" class="btn btn-sm btn-info rounded-0">
                                                    Downlaod Data
                                                </a >
                                            </div>
                                            <table class="table">
                                                 
                                                    <thead>
                                                        <tr> 
                                                            <th class="text-left font-weight-bold text-muted text-uppercase">
                                                                S#
                                                           </th> 
                                                            <th class="text-left font-weight-bold text-muted text-uppercase">
                                                                 Username
                                                            </th> 
                                                            <th class="text-left font-weight-bold text-muted text-uppercase">
                                                                Name
                                                            </th> 
                                                            <th class="text-left font-weight-bold text-muted text-uppercase">
                                                                Level
                                                            </th> 
                                                             
                                                        </tr>
                                                    </thead>

                                                    <tbody>
                                                        @foreach ($data as $member) 
                                                        <tr> 
                                                            <th class="text-left font-weight-bold   text-uppercase">
                                                                {{ $loop->iteration }}
                                                            </th> 
                                                            <th class="text-left font-weight-bold   text-uppercase">
                                                                 {{ $member->username }}
                                                            </th> 
                                                            <th class="text-left font-weight-bold   text-uppercase">
                                                                {{ $member->name }}
                                                            </th> 
                                                            <th class="text-left font-weight-bold   text-uppercase">
                                                                {{ $member->level }}
                                                            </th> 
                                                            
                                                           
                                                        </tr>
                                                        @endforeach
                                                    </tbody>
                                                 
                                                 
                                            </table> 

                                           
                                            
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
    
 

</script>
    
@endsection
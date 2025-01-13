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
                    <h5 class="text-dark font-weight-bold my-1 mr-5">Rental Percentage </h5>
                    <!--end::Page Title-->
                    <!--begin::Breadcrumb-->
                    <ul class="breadcrumb breadcrumb-transparent breadcrumb-dot font-weight-bold p-0 my-2 font-size-sm">
                        <li class="breadcrumb-item">
                            <a href="{{  route('dashboard') }}" class="text-muted">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="#" class="text-muted">Rental</a>
                        </li>    
                        <li class="breadcrumb-item">
                            <a href="" class="text-muted">Rental Percentage</a>
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
               
            
                <div class="card-header border-0 py-5">
                    <h3 class="card-title align-items-start flex-column">
                       
                    </h3>
                    <div class="card-toolbar">
                        {{-- <a href="{{ route('run-schedule') }}" data-toggle="modal" data-target="#WithdrawModel" class="mr-3 rounded-0 btn btn-primary font-weight-bolder font-size-sm">Add Week</a> 
                    </div> --}}
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
                                    <th style="min-width: 110px">Percentage</th>  
                                    <th style="min-width: 120px">Last Updated</th>  
                                    <th style="min-width: 120px">Percentage</th> 
                                    {{-- <th style="min-width: 120px">Action</th>  --}}
                                </tr>
                            </thead>
                            <tbody> 
                                @foreach ($weeks as $week )
                                    <tr class="text-left"> 
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $week->week_name }}</td>
                                        <td>{{ $week->percentage }}%</td>
                                        <td>{{ $week->updated_at->format('d M Y H:m:i') }}</td>
                                        <td>
                                            <form action="{{ route('rental.percentage.update', $week->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('PUT')
                                                <input type="number" name="percentage" class="rounded-0 form-control d-inline" style="width: 140px;" value="{{ $week->percentage }}" step="0.01" required>
                                                <button type="submit" class="rounded-0 btn btn-success btn-sm">Update</button>
                                            </form>
                                        </td> 
                                        {{-- <td>
                                            <form action="{{ route('rental.percentage.delete', $week->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE') 
                                                <button type="submit" class="rounded-0 btn btn-danger btn-sm">Delete</button>
                                            </form>
                                        </td>  --}}
                                @endforeach
                            </tbody>
                            @if(!$weeks>0)
                                <tfoot class="text-center">
                                    <th colspan="7" class="p-5 text-danger">Rental Percentage</th>
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
{{--  
<div class="modal fade" id="WithdrawModel" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="{{ route('add.rental.percentage') }}" method="POST">
                @csrf 
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Add Week</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <i aria-hidden="true" class="ki ki-close"></i>
                    </button>
                </div>
                <div class="modal-body"> 
                    <div class="form-group row">  
                        <div class="col-lg-12 col-xl-12">
                            <label for="" class="font-weight-bold mr-2">
                                Week Name
                            </label>
                            <input type="text" class="form-control form-control-sm form-control-solid mb-2" 
                             name="week_name"
                             required 
                             placeholder="Week Name"
                             >  
                        </div>  

                        <div class="col-lg-12 col-xl-12">
                            <label for="" class="font-weight-bold mr-2">
                                Percentage
                            </label>
                            <input type="number" class="form-control form-control-sm form-control-solid mb-2" 
                             name="percentage"
                             required 
                             placeholder="Percentage"
                             >  
                        </div>  
                    </div>  
                </div>
                <div class="modal-footer">
                    <button type="button" class="rounded-0 btn btn-light-primary btn-sm" data-dismiss="modal">Close</button>
                    <button type="submit" class="rounded-0 btn btn-primary btn-sm">Add </button>
                </div>
            </form>
        </div>
    </div>
</div>
  --}}

 
<!--end::Content-->
@endsection
@section('page_js')
    <script>
         var avatar = new KTImageInput('kt_profile_avatar');  

    </script>
    
@endsection
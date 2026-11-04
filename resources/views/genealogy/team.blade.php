@extends('demo.layout.app')
@section('title','Genealogy Team')
@section('custom_css')
    @include('genealogy.partials.org-chart-css')
@endsection

@section('content')
<!--begin::Content----------------------->
<div class="content d-flex flex-column flex-column-fluid" id="kt_content">
    <div class="subheader py-2 py-lg-6 subheader-solid" id="kt_subheader">
        <div class="container-fluid d-flex align-items-center justify-content-between flex-wrap flex-sm-nowrap">
            <div class="d-flex align-items-center flex-wrap mr-1">
                <div class="d-flex align-items-baseline flex-wrap mr-5">
                    <h5 class="text-dark font-weight-bold my-1 mr-5">Genealogy</h5>
                    <ul class="breadcrumb breadcrumb-transparent breadcrumb-dot font-weight-bold p-0 my-2 font-size-sm">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-muted">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="#" class="text-muted">Genealogy</a></li>
                        <li class="breadcrumb-item"><a href="" class="text-muted">My Team</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="flex-column-fluid">
        <div class="container-fluid">
            @include('genealogy.partials.org-chart-body', ['ocTeamLabel' => 'team'])
        </div>
    </div>
</div>

<!--end::Content-->
@endsection

@section('page_js')
    @include('genealogy.partials.org-chart-js', ['ocTree' => 'standard'])
@endsection

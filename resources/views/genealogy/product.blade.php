@extends('demo.layout.app')
@section('title','Genealogy')
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
                    <h5 class="text-dark font-weight-bold my-1 mr-5">Products </h5>
                    <!--end::Page Title-->
                    <!--begin::Breadcrumb-->
                    <ul class="breadcrumb breadcrumb-transparent breadcrumb-dot font-weight-bold p-0 my-2 font-size-sm">
                        <li class="breadcrumb-item">
                            <a href="{{  route('dashboard') }}" class="text-muted">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="#" class="text-muted">Genealogy</a>
                        </li>   

                        <li class="breadcrumb-item">
                            <a href="" class="text-muted">Products</a>
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


            <div class="col-lg-12 col-xxl-12 order-1 order-xxl-2">  
                <div class="card card-custom card-stretch gutter-b">
                    <div class="card-body">
                        <!--begin::Engage Widget 15-->
                        <div class="card card-custom mb-12">
                            <div class="card-body rounded p-0 d-flex" style="background-color:#DAF0FD;">
                                <div class="d-flex flex-column flex-lg-row-auto w-auto w-lg-350px w-xl-450px w-xxl-500px p-10 p-md-20">
                                    <h1 class="font-weight-bolder text-dark">Search Goods</h1>
                                    <div class="font-size-h4 mb-8">Get Amazing Gadgets</div>
                                    <!--begin::Form-->
                                    <form class="d-flex flex-center py-2 px-6 bg-white rounded">
                                        <span class="svg-icon svg-icon-lg svg-icon-primary">
                                            <!--begin::Svg Icon | path:assets/media/svg/icons/General/Search.svg-->
                                            <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px" height="24px" viewBox="0 0 24 24" version="1.1">
                                                <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                                    <rect x="0" y="0" width="24" height="24"></rect>
                                                    <path d="M14.2928932,16.7071068 C13.9023689,16.3165825 13.9023689,15.6834175 14.2928932,15.2928932 C14.6834175,14.9023689 15.3165825,14.9023689 15.7071068,15.2928932 L19.7071068,19.2928932 C20.0976311,19.6834175 20.0976311,20.3165825 19.7071068,20.7071068 C19.3165825,21.0976311 18.6834175,21.0976311 18.2928932,20.7071068 L14.2928932,16.7071068 Z" fill="#000000" fill-rule="nonzero" opacity="0.3"></path>
                                                    <path d="M11,16 C13.7614237,16 16,13.7614237 16,11 C16,8.23857625 13.7614237,6 11,6 C8.23857625,6 6,8.23857625 6,11 C6,13.7614237 8.23857625,16 11,16 Z M11,18 C7.13400675,18 4,14.8659932 4,11 C4,7.13400675 7.13400675,4 11,4 C14.8659932,4 18,7.13400675 18,11 C18,14.8659932 14.8659932,18 11,18 Z" fill="#000000" fill-rule="nonzero"></path>
                                                </g>
                                            </svg>
                                            <!--end::Svg Icon-->
                                        </span>
                                        <input type="text" class="form-control border-0 font-weight-bold pl-2" placeholder="Search Goods">
                                    </form>
                                    <!--end::Form-->
                                </div>
                                <div class="d-none d-md-flex flex-row-fluid bgi-no-repeat bgi-position-y-center bgi-position-x-left bgi-size-cover" style="background-image: url(assets/media/svg/illustrations/progress.svg);"></div>
                            </div>
                        </div>
                        <!--end::Engage Widget 15-->
                        <!--begin::Section-->
                        <div class="mb-11">
                            <!--begin::Heading-->
                            <div class="d-flex justify-content-between align-items-center mb-7">
                                <h2 class="font-weight-bolder text-dark font-size-h3 mb-0">Products</h2> 
                            </div> 
                            <div class="row"> 
                                @foreach($products as $product)
                                    <div class="mb-11 col-md-4 col-xxl-4 col-lg-12">
                                        <!--begin::Card-->
                                        <div class="card card-custom card-shadowless">
                                            <div class="card-body p-0">
                                                <!--begin::Image-->
                                                <div class="overlay">
                                                    <div class="overlay-wrapper rounded bg-light text-center">
                                                        <img src="{{asset($product->getFirstMediaUrl('product_cover')) }}" alt="" class="mw-100 w-200px">
                                                    </div>
                                                    <div class="overlay-layer"> 
                                                        <a href="#" class="btn font-weight-bolder btn-sm btn-light-primary">Purchase</a>
                                                    </div>
                                                </div>
                                                <!--end::Image-->
                                                <!--begin::Details-->
                                                <div class="text-center mt-5 mb-md-0 mb-lg-5 mb-md-0 mb-lg-5 mb-lg-0 mb-5 d-flex flex-column">
                                                    <a href="#" class="font-size-h5 font-weight-bolder text-dark-75 text-hover-primary mb-1">{{ $product->name }} </a>
                                                    <span class="font-size-lg"> {{ $product->price }} PV</span>
                                                </div>
                                                <!--end::Details-->
                                            </div>
                                        </div>
                                        <!--end::Card-->
                                    </div> 
                                @endforeach
                            </div>
                            <!--end::Products-->
                        </div>
                        
                    </div>
                </div>
            </div> 
        </div>
        
    </div>
    <!--end::Entry-->
</div>

 
  
<!--end::Content-->
@endsection
@section('page_js')
    <script>
         var avatar = new KTImageInput('kt_profile_avatar');  
         $(document).ready(function() {
            $('.WithdrawDelete').on('click', function() {
                var requestId = $(this).data('id');
                $('#request-id').val(requestId);
            });
        });

    </script>
    
@endsection
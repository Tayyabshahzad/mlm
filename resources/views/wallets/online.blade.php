@extends('demo.layout.app')
@section('title','Online Wallet')
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
                    <h5 class="text-dark font-weight-bold my-1 mr-5">Online Wallet </h5>
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
                            <a href="" class="text-muted">Online Wallet</a>
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
                        <span class="card-label font-weight-bolder text-dark">Total Balance : 800 PV</span> 
                    </h3>
                    <div class="card-toolbar">
                        <a href="#" data-toggle="modal" data-target="#WithdrawModel" class="btn btn-info font-weight-bolder font-size-sm">Withdraw Request</a>
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
                                    <th style="min-width: 110px">Wallet Name</th>
                                    <th style="min-width: 110px"> <span class="text-info">Amount PV</span>  </span> </th>
                                    <th style="min-width: 120px">Date</th> 
                                </tr>
                            </thead>
                            <tbody>
                                <tr> 
                                    <td class="pl-0">
                                        <a href="#" class="text-dark-75 font-weight-bolder text-hover-primary font-size-lg">1</a>
                                    </td>
                                    <td>
                                        <span class="text-dark-75 font-weight-bolder d-block font-size-lg">Direct / InDirect</span> 
                                    </td>
                                    <td>
                                        <span class="text-info font-weight-bolder d-block font-size-lg">100 PV</span> 
                                    </td>
                                    <td>
                                        <span class="text-dark-75 font-weight-bolder d-block font-size-lg">05/28/2020</span> 
                                    </td> 
                                </tr> 

                                <tr> 
                                    <td class="pl-0">
                                        <a href="#" class="text-dark-75 font-weight-bolder text-hover-primary font-size-lg">2</a>
                                    </td>
                                    <td>
                                        <span class="text-dark-75 font-weight-bolder d-block font-size-lg">ROI</span> 
                                    </td>
                                    <td>
                                        <span class="text-info font-weight-bolder d-block font-size-lg">50  PV</span> 
                                    </td>
                                    <td>
                                        <span class="text-dark-75 font-weight-bolder d-block font-size-lg">05/28/2020</span> 
                                    </td> 
                                </tr> 

                                <tr> 
                                    <td class="pl-0">
                                        <a href="#" class="text-dark-75 font-weight-bolder text-hover-primary font-size-lg">3</a>
                                    </td>
                                    <td>
                                        <span class="text-dark-75 font-weight-bolder d-block font-size-lg">Profit Share</span> 
                                    </td>
                                    <td>
                                        <span class="text-info font-weight-bolder d-block font-size-lg">120 PV</span> 
                                    </td>
                                    <td>
                                        <span class="text-dark-75 font-weight-bolder d-block font-size-lg">05/28/2020</span> 
                                    </td> 
                                </tr> 

                                <tr> 
                                    <td class="pl-0">
                                        <a href="#" class="text-dark-75 font-weight-bolder text-hover-primary font-size-lg">4</a>
                                    </td>
                                    <td>
                                        <span class="text-dark-75 font-weight-bolder d-block font-size-lg">Reward</span> 
                                    </td>
                                    <td>
                                        <span class="text-info font-weight-bolder d-block font-size-lg">110 PV</span> 
                                    </td>
                                    <td>
                                        <span class="text-dark-75 font-weight-bolder d-block font-size-lg">05/28/2020</span> 
                                    </td> 
                                </tr> 

                                <tr> 
                                    <td class="pl-0">
                                        <a href="#" class="text-dark-75 font-weight-bolder text-hover-primary font-size-lg">5</a>
                                    </td>
                                    <td>
                                        <span class="text-dark-75 font-weight-bolder d-block font-size-lg">Rank</span> 
                                    </td>
                                    <td>
                                        <span class="text-info font-weight-bolder d-block font-size-lg">30 PV</span> 
                                    </td>
                                    <td>
                                        <span class="text-dark-75 font-weight-bolder d-block font-size-lg">05/28/2020</span> 
                                    </td> 
                                </tr> 


                                <tr> 
                                    <td class="pl-0">
                                        <a href="#" class="text-dark-75 font-weight-bolder text-hover-primary font-size-lg">6</a>
                                    </td>
                                    <td>
                                        <span class="text-dark-75 font-weight-bolder d-block font-size-lg">Rank</span> 
                                    </td>
                                    <td>
                                        <span class="text-info font-weight-bolder d-block font-size-lg">5 PV</span> 
                                    </td>
                                    <td>
                                        <span class="text-dark-75 font-weight-bolder d-block font-size-lg">05/28/2020</span> 
                                    </td> 
                                </tr> 


                                <tr> 
                                    <td class="pl-0">
                                        <a href="#" class="text-dark-75 font-weight-bolder text-hover-primary font-size-lg">7</a>
                                    </td>
                                    <td>
                                        <span class="text-dark-75 font-weight-bolder d-block font-size-lg">Profit Share</span> 
                                    </td>
                                    <td>
                                        <span class="text-info font-weight-bolder d-block font-size-lg">100 PV</span> 
                                    </td>
                                    <td>
                                        <span class="text-dark-75 font-weight-bolder d-block font-size-lg">05/28/2020</span> 
                                    </td> 
                                </tr> 


                                <tr class="bg-light-danger"> 
                                    <td class="pl-0">
                                        <a href="#" class="text-dark-75 font-weight-bolder text-hover-primary font-size-lg">8</a>
                                    </td>
                                    <td>
                                        <span class="text-dark-75 font-weight-bolder d-block font-size-lg">Withdraw (Bank)</span> 
                                    </td>
                                    <td>
                                        <span class="text-info font-weight-bolder d-block font-size-lg">100 PV</span> 
                                    </td>
                                    <td>
                                        <span class="text-dark-75 font-weight-bolder d-block font-size-lg">01/29/2025</span> 
                                    </td> 
                                </tr> 


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
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Withdrawal Request</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <i aria-hidden="true" class="ki ki-close"></i>
                </button>
            </div>
            <div class="modal-body">
              <p class="text-center text-danger">
                Withdrawals are paid within 72 hours. Min. Withdrawal PV : 04
              </p>
                <div class="form-group row"> 
                    <div class="col-lg-12 col-xl-12">
                        <label for="" class="font-weight-bold mr-2">
                              Withdraw Amount
                        </label>
                        <input type="text" class="form-control form-control-sm form-control-solid mb-2" name="current_password" placeholder="Withdraw Amount" required="" value=""> 
                    </div> 

                    <div class="col-lg-12 col-xl-12">
                        <label for="" class="font-weight-bold mr-2">
                            Withdraw To
                        </label>
                        <select name="card_expiry_month" class="form-control   e"  >
                            <option value="bank" > Bank </option>
                            <option value="bank" > USDT </option>
                            <option value="bank" > Buy Package </option>
                            
                        </select>
                    </div>  
                </div>


                 
              

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light-primary font-weight-bold" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary font-weight-bold">Withdraw</button>
            </div>
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
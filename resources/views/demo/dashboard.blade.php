@extends('demo.layout.app')
@section('content')
<div class="content d-flex flex-column flex-column-fluid" id="kt_content">
    <!--begin::Subheader-->
    <div class="subheader py-2 py-lg-4 subheader-solid" id="kt_subheader">
        <div class="container-fluid d-flex align-items-center justify-content-between flex-wrap flex-sm-nowrap">
            <!--begin::Info-->
            <div class="d-flex align-items-center flex-wrap mr-2">
                <!--begin::Page Title-->
                <h5 class="text-dark font-weight-bold mt-2 mb-2 mr-5">Dashboard</h5> 
                <div class="subheader-separator subheader-separator-ver mt-2 mb-2 mr-4 bg-gray-200"></div>
                <span class="text-muted font-weight-bold mr-4">Level 1 </span>
                <div class="kt-widget__content">
                    <div class="kt-widget__section">
                        <a href="#" class="kt-widget__username">
                            
                            <i class="flaticon2-correct kt-font-success"></i>
                        </a>
                        
                    </div>
                    
                </div> 
                <div class="subheader-separator subheader-separator-ver mt-2 mb-2 mr-4 bg-gray-200 ml-2"></div>
                <span class="text-muted font-weight-bold mr-4">PV Balance </span>
                <div class="kt-widget__content">
                    <div class="kt-widget__section">
                        <strong> {{ Auth::user()->current_pv_balance }} </strong> 
                    </div> 
                </div> 
            </div> 
        </div>
    </div>
    <!--end::Subheader-->
    <!--begin::Entry-->
    <div class="d-flex flex-column-fluid"> 
        <div class="container">  
            <div class="row"> 
                <div class="col-xl-4"> 
                    <div class="card card-custom gutter-b card-stretch"> 
                        <div class="card-header border-0 pt-5">
                            <div class="card-title">
                                <div class="card-label">
                                    <div class="font-weight-bolder">Weekly Sales Stats</div>
                                    <div class="font-size-sm text-muted mt-2">0 PV</div>
                                </div>
                            </div> 
                        </div>
                        <!--end::Header-->
                        <!--begin::Body-->
                        <div class="card-body d-flex flex-column px-0">

                           
                            <!--begin::Chart-->
                            <div id="kt_tiles_widget_1_chart" data-color="info" style="height: 125px"></div>
                            <!--end::Chart-->
                            <!--begin::Items-->

                          

                            <div class="flex-grow-1 card-spacer-x">
                                <h3 class="  font-weight-bolder my-7">Leaderboard</h3>
                                <!--begin::Item-->

                                @foreach ($data['team_size']  as $team ) 
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <div class="d-flex align-items-center mr-2">
                                            <div class="symbol symbol-50 symbol-light mr-3 flex-shrink-0">
                                                <div class="symbol-label">
                                                    <img src="{{asset($team->getFirstMediaUrl('user_profile_images')) }}" alt="" class="h-50" />
                                                </div>
                                            </div>
                                            <div>
                                                <a href="#" class="font-size-h6 text-dark-75 text-hover-primary font-weight-bolder"> {{ ucfirst($team->name) }}</a>
                                                <div class="font-size-sm text-muted font-weight-bold mt-1">Direct</div>
                                            </div>
                                        </div>
                                        <div class="label label-light label-inline font-weight-bold text-dark-50 py-4 px-3 font-size-base">
                                            {{ $team->team->count() }}
                                        </div>
                                    </div> 
                                @endforeach
                                <!--end::Item-->
 

                                
                                <!--end::Item-->
                            </div>
                            <!--end::Items-->
                        </div>
                        <!--end::Body-->
                    </div>
                    <!--end::Tiles Widget 1-->
                </div>
                <div class="col-xl-8">
                    <div class="row"> 
                        <div class="col-xl-6"> 
                            <div class="card card-custom bgi-no-repeat bgi-no-repeat bgi-size-cover gutter-b" style="height: 130px; background-image: url(assets/media/bg/bg-8.jpg)">
                                <!--begin::Body-->
                                <div class="card-body d-flex flex-column p-0">
                                    <!--begin::Stats-->
                                    <div class="flex-grow-1 card-spacer-x pt-6">
                                        <div class="text-inverse-danger font-weight-bold">Online Wallet</div>
                                        <div class="text-inverse-danger font-weight-bolder font-size-h3"> {{ $data['online_wallet'] }} </div>
                                    </div>
                                    <!--end::Stats-->
                                    <!--begin::Chart-->
                                    <div id="kt_tiles_widget_2_chart" class="card-rounded-bottom" style="height: 50px"></div>
                                    <!--end::Chart-->
                                </div>
                                <!--end::Body-->
                            </div>
                            <!--end::Tiles Widget 5-->
                        </div>


                        <div class="col-xl-6"> 
                            <div class="card card-custom bgi-no-repeat bgi-no-repeat bgi-size-cover gutter-b" style="height: 130px; background-image: url(assets/media/bg/bg-5.jpg)">
                                <!--begin::Body-->
                                <div class="card-body d-flex flex-column p-0">
                                    <!--begin::Stats-->
                                    <div class="flex-grow-1 card-spacer-x pt-6">
                                        <div class="text-inverse-danger font-weight-bold">Direct/Indirect Wallet</div>
                                        <div class="text-inverse-danger font-weight-bolder font-size-h3"> {{ $data['direct_indirect'] }}</div>
                                    </div>
                                    <!--end::Stats-->
                                    <!--begin::Chart-->
                                    <div id="kt_tiles_widget_2_chart" class="card-rounded-bottom" style="height: 50px"></div>
                                    <!--end::Chart-->
                                </div>
                                <!--end::Body-->
                            </div>
                            <!--end::Tiles Widget 5-->
                        </div>

                        <div class="col-xl-6"> 
                            <div class="card card-custom bgi-no-repeat bgi-no-repeat bgi-size-cover gutter-b" style="height: 130px; background-image: url(assets/media/bg/bg-1.jpg)">
                                <!--begin::Body-->
                                <div class="card-body d-flex flex-column p-0">
                                    <!--begin::Stats-->
                                    <div class="flex-grow-1 card-spacer-x pt-6">
                                        <div class="text-inverse-danger font-weight-bold">Reward Wallet</div>
                                        <div class="text-inverse-danger font-weight-bolder font-size-h3">{{ $data['reward'] }}</div>
                                    </div>
                                    <!--end::Stats-->
                                    <!--begin::Chart-->
                                    <div id="kt_tiles_widget_2_chart" class="card-rounded-bottom" style="height: 50px"></div>
                                    <!--end::Chart-->
                                </div>
                                <!--end::Body-->
                            </div>
                            <!--end::Tiles Widget 5-->
                        </div>

                        <div class="col-xl-6"> 
                            <div class="card card-custom bgi-no-repeat bgi-no-repeat bgi-size-cover gutter-b" style="height: 130px; background-image: url(assets/media/bg/bg-4.jpg)">
                                <!--begin::Body-->
                                <div class="card-body d-flex flex-column p-0">
                                    <!--begin::Stats-->
                                    <div class="flex-grow-1 card-spacer-x pt-6">
                                        <div class="text-inverse-danger font-weight-bold">ROI</div>
                                        <div class="text-inverse-danger font-weight-bolder font-size-h3">{{ $data['roi'] }}</div>
                                    </div>
                                    <!--end::Stats-->
                                    <!--begin::Chart-->
                                    <div id="kt_tiles_widget_2_chart" class="card-rounded-bottom" style="height: 50px"></div>
                                    <!--end::Chart-->
                                </div>
                                <!--end::Body-->
                            </div>
                            <!--end::Tiles Widget 5-->
                        </div>


                        <div class="col-xl-6"> 
                            <div class="card card-custom bgi-no-repeat bgi-no-repeat bgi-size-cover gutter-b" style="height: 130px; background-image: url(assets/media/bg/bg-2.jpg)">
                                <!--begin::Body-->
                                <div class="card-body d-flex flex-column p-0">
                                    <!--begin::Stats-->
                                    <div class="flex-grow-1 card-spacer-x pt-6">
                                        <div class="text-inverse-danger font-weight-bold">Profit Share</div>
                                        <div class="text-inverse-danger font-weight-bolder font-size-h3">{{ $data['profit_share'] }}</div>
                                    </div>
                                    <!--end::Stats-->
                                    <!--begin::Chart-->
                                    <div id="kt_tiles_widget_2_chart" class="card-rounded-bottom" style="height: 50px"></div>
                                    <!--end::Chart-->
                                </div>
                                <!--end::Body-->
                            </div>
                            <!--end::Tiles Widget 5-->
                        </div> 


                        <div class="col-xl-6"> 
                            <div class="card card-custom bgi-no-repeat bgi-no-repeat bgi-size-cover gutter-b" style="height: 130px; background-image: url(assets/media/bg/bg-6.jpg)">
                                <!--begin::Body-->
                                <div class="card-body d-flex flex-column p-0">
                                    <!--begin::Stats-->
                                    <div class="flex-grow-1 card-spacer-x pt-6">
                                        <div class="text-inverse-danger font-weight-bold">Rank</div>
                                        <div class="text-inverse-danger font-weight-bolder font-size-h3">{{ $data['rank'] }}</div>
                                    </div>
                                    <!--end::Stats-->
                                    <!--begin::Chart-->
                                    <div id="kt_tiles_widget_2_chart" class="card-rounded-bottom" style="height: 50px"></div>
                                    <!--end::Chart-->
                                </div>
                                <!--end::Body-->
                            </div>
                            <!--end::Tiles Widget 5-->
                        </div>  
 
                        <div class="col-xl-12"> 
                            <div class="card card-custom bgi-no-repeat bgi-no-repeat bgi-size-cover gutter-b" style="height: 130px; background-image: url(assets/media/bg/bg-10.jpg)">
                                <!--begin::Body-->
                                <div class="card-body d-flex flex-column p-0">
                                    <!--begin::Stats-->
                                    <div class="flex-grow-1 card-spacer-x pt-6">
                                        <div class="text-inverse-danger font-weight-bold">Total Team Size</div>
                                        <div class="text-inverse-danger font-weight-bolder font-size-h3"> {{ $data['totalTeam'] }} </div>
                                    </div>
                                    <!--end::Stats-->
                                    <!--begin::Chart-->
                                    <div id="kt_tiles_widget_2_chart" class="card-rounded-bottom" style="height: 50px"></div>
                                    <!--end::Chart-->
                                </div>
                                <!--end::Body-->
                            </div>
                            <!--end::Tiles Widget 5-->
                        </div>  

                    </div>
                    <!--begin::Mixed Widget 20-->
                    <div class="card card-custom bgi-no-repeat gutter-b" style="height: 175px; background-color: #4AB58E; background-position: calc(100% + 1rem) bottom; background-size: 25% auto; background-image: url(assets/media/svg/humans/custom-1.svg)">
                        <!--begin::Body-->
                        <div class="card-body d-flex align-items-center">
                            <div class="py-2">
                                <h3 class="text-white font-weight-bolder mb-3">Total Earning: {{ number_format($data['total_earning'],2)  }} PV</h3>
                                <p class="text-white font-size-lg">
                                    Overview of All Your Wallets
                                </p>
                            </div>
                        </div>
                        <!--end::Body-->
                    </div>
                    <!--end::Mixed Widget 20-->
                </div>
                 
            </div>
            <div class="row">
                <div class="col-lg-12">
                    <div class="card card-custom card-stretch gutter-b">
                        <div class="card-body" style=""> 
                            <div class="card card-custom  " style="background-color: rgb(149, 238, 233)">
                                <div class="card-body rounded-0 p-0 d-flex" style="background-image: url({{ asset('assets/custom-images/announcement.svg') }});background-size:70px;background-repeat:no-repeat;background-position:bottom left">
                                    <div class="d-flex flex-column flex-lg-row-auto w-auto  p-10 p-md-20">
                                        <h1 class="font-weight-bolder text-dark">10 PV Registration fee will apply starting February 10, 2025. </h1>  
                                    </div> 
                                </div>
                            </div> 
                        </div>
                    </div>
                </div> 
                <div class="col-lg-12">
                    <!--begin::Card-->
                    <div class="card card-custom gutter-b">
                        <div class="card-header">
                            <div class="card-title">
                                <h3 class="card-label">Business Summary</h3>
                            </div>
                        </div>
                        <div class="card-body">  
                            <div id="chart_3"></div> 
                        </div>
                    </div>
                    <!--end::Card-->
                </div>

                
            </div> 

            <div class="card card-custom gutter-b wave  wave-animated-info wave-info" 
              style="background-position: right top; background-size: 30% auto; background-image: url(assets/media/svg/shapes/abstract-3.svg);background-size:cover">
                <div class="card-body">
                    <!--begin::Details-->
                    <div class="d-flex mb-9"> 
                        <div class="flex-grow-1">
                            <!--begin::Title-->
                            <div class="d-flex justify-content-between flex-wrap mt-1">
                                <div class="d-flex mr-3">
                                    <a href="#" class="text-dark-75 text-hover-primary font-size-h4 font-weight-bold mr-3">
                                       <strong> Personal Investment {{ $data['initial_investment'] }} PV <span>2X</span> </strong>  
                                    </a>
                                    <a href="#">
                                        <i class="flaticon2-correct text-success font-size-h5"></i>
                                    </a>
                                </div> 
                            </div>
                            <!--end::Title-->
                            <!--begin::Content-->
                            <div class="d-flex flex-wrap justify-content-between mt-1">
                                <div class="d-flex flex-column flex-grow-1 pr-8">
                                    <div class="d-flex flex-wrap mb-4">
                                         
                                    </div>
                                    
                                </div>
                                <div class="d-flex align-items-center w-25 flex-fill float-right mt-lg-12 mt-8">
                                    <span class="font-weight-bold text-dark-75">Progress</span>
                                    <div class="progress progress-xs mx-3 w-100">
                                        <div class="progress-bar bg-danger" role="progressbar" 
                                         style="width:{{ $data['total_roi_earned_pv'] }}%;" 
                                         aria-valuenow="0" aria-valuemin="0" aria-valuemax="200"></div>
                                    </div>
                                    <span class="font-weight-bolder text-dark">{{ $data['total_roi_earned_pv'] }}%</span>
                                </div>
                            </div>
                            <!--end::Content-->
                        </div>
                        <!--end::Info-->
                    </div>
                    <!--end::Details-->
                    <div class="separator separator-solid"></div>
                    <!--begin::Items-->
                    <div class="d-flex align-items-center flex-wrap mt-8 ">
                        <!--begin::Item-->
                        <div class="d-flex align-items-center flex-lg-fill mr-5 mb-2">
                            <span class="mr-4">
                                <i class="flaticon-piggy-bank display-4 text-muted font-weight-bold"></i>
                            </span>
                            <div class="d-flex flex-column text-dark-75">
                                <span class="font-weight-bolder font-size-sm">Total Earned</span>
                                <span class="font-weight-bolder font-size-h5">
                                <span class="text-dark-50 font-weight-bold"></span>{{ $data['total_roi_earned_pv'] }} PV</span>
                            </div>
                        </div>
                        <!--end::Item-->
                        <!--begin::Item-->
                        <div class="d-flex align-items-center flex-lg-fill mr-5 mb-2">
                            <span class="mr-4">
                                <i class="flaticon-confetti display-4 text-muted font-weight-bold"></i>
                            </span>
                            <div class="d-flex flex-column text-dark-75">
                                <span class="font-weight-bolder font-size-sm">Earned This Month</span>
                                <span class="font-weight-bolder font-size-h5">
                                <span class="text-dark-50 font-weight-bold"></span>{{ $data['total_roi_earned_pv'] }} PV</span>
                            </div>
                        </div>
                        <!--end::Item-->
                        <!--begin::Item-->
                        <div class="d-flex align-items-center flex-lg-fill mr-5 mb-2">
                            <span class="mr-4">
                                <i class="flaticon-pie-chart display-4 text-muted font-weight-bold"></i>
                            </span>
                            <div class="d-flex flex-column text-dark-75">
                                <span class="font-weight-bolder font-size-sm">Remaining</span>
                                <span class="font-weight-bolder font-size-h5">
                                <span class="text-dark-50 font-weight-bold"></span>{{  200 - $data['total_roi_earned_pv']      }} PV</span>
                            </div>
                        </div> 
                    </div>
                    <!--begin::Items-->
                </div>
            </div>


            


            <div class="card card-custom gutter-b wave  wave-animated wave-info" 
            style="background-position: right top; background-size: 30% auto; background-image: url(assets/media/svg/shapes/abstract-4.svg);background-size:cover">
              <div class="card-body">
                  <!--begin::Details-->
                  <div class="d-flex mb-9"> 
                      <div class="flex-grow-1">
                          <!--begin::Title-->
                          <div class="d-flex justify-content-between flex-wrap mt-1">
                              <div class="d-flex mr-3">
                                  <a href="#" class="text-dark-75 text-hover-primary font-size-h4 font-weight-bold mr-3">
                                    Personal Investment Cap 700 PV <span>7X</span> 
                                  </a>
                                  <a href="#">
                                      <i class="flaticon2-correct text-success font-size-h5"></i>
                                  </a>
                              </div> 
                          </div>
                          <!--end::Title-->
                          <!--begin::Content-->
                          <div class="d-flex flex-wrap justify-content-between mt-1">
                              <div class="d-flex flex-column flex-grow-1 pr-8">
                                  <div class="d-flex flex-wrap mb-4">
                                       
                                  </div>
                                  
                              </div>
                              <div class="d-flex align-items-center w-25 flex-fill float-right mt-lg-12 mt-8">
                                  <span class="font-weight-bold text-dark-75">Progress</span>
                                  <div class="progress progress-xs mx-3 w-100">
                                      <div class="progress-bar bg-info" role="progressbar" style="width: 1%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                                  </div>
                                  <span class="font-weight-bolder text-dark">0%</span>
                              </div>
                          </div>
                          <!--end::Content-->
                      </div>
                      <!--end::Info-->
                  </div>
                  <!--end::Details-->
                  <div class="separator separator-solid"  ></div>
                  <!--begin::Items-->
                  <div class="d-flex align-items-center flex-wrap mt-8 ">
                      <!--begin::Item-->
                      <div class="d-flex align-items-center flex-lg-fill mr-5 mb-2">
                          <span class="mr-4">
                              <i class="flaticon-piggy-bank display-4 text-muted font-weight-bold"></i>
                          </span>
                          <div class="d-flex flex-column text-dark-75">
                              <span class="font-weight-bolder font-size-sm">Total Earned</span>
                              <span class="font-weight-bolder font-size-h5">
                              <span class="text-dark-50 font-weight-bold"></span>{{ Auth::user()->roi_wallet_balance }} PV</span>
                          </div>
                      </div>
                      <!--end::Item-->
                      <!--begin::Item-->
                      <div class="d-flex align-items-center flex-lg-fill mr-5 mb-2">
                          <span class="mr-4">
                              <i class="flaticon-confetti display-4 text-muted font-weight-bold"></i>
                          </span>
                          <div class="d-flex flex-column text-dark-75">
                              <span class="font-weight-bolder font-size-sm">Earned This Month</span>
                              <span class="font-weight-bolder font-size-h5">
                              <span class="text-dark-50 font-weight-bold"></span>{{ Auth::user()->roi_wallet_balance }} PV</span>
                          </div>
                      </div>
                      <!--end::Item-->
                      <!--begin::Item-->
                      <div class="d-flex align-items-center flex-lg-fill mr-5 mb-2">
                          <span class="mr-4">
                              <i class="flaticon-pie-chart display-4 text-muted font-weight-bold"></i>
                          </span>
                          <div class="d-flex flex-column text-dark-75">
                              <span class="font-weight-bolder font-size-sm">Remaining</span>
                              <span class="font-weight-bolder font-size-h5">
                              <span class="text-dark-50 font-weight-bold"></span>{{ Auth::user()->roi_wallet_balance }} PV</span>
                          </div>
                      </div> 
                  </div>
                  <!--begin::Items-->
              </div>
          </div>

         
          <div class="row">
           
            
          
            <div class="col-lg-6 " id="reward-target-wrapper"> 
                <div class="card card-custom card-stretch gutter-b"> 
                    <div class="card-header border-0 pt-5">
                        <h3 class="card-title font-weight-bolder">Reward Target</h3>
                        <div class="card-toolbar"> 
                        </div>
                    </div> 
                    <div class="card-body d-flex flex-column">
                        <div class="flex-grow-1">
                            <div id="kt_mixed_widget_18_chart" style="height: 300px"></div>
                        </div>
                        <div class="pt-5">
                            <p class="text-center font-weight-normal font-size-lg pb-7">Notes: Click to get more details for your upcoming rewards
                         </p>
                            <button type="" id="generateRewardTargetReport" class="btn btn-success btn-shadow-hover font-weight-bolder w-100 py-3">View Reward Target</button>
                        </div>
                    </div>  
                </div> 
            </div> 

            <div class="col-lg-6" id="rank-target-wrapper"> 
                <div class="card card-custom card-stretch gutter-b"> 
                    <div class="card-header border-0 pt-5">
                        <h3 class="card-title font-weight-bolder">Rank Target </h3>
                        <div class="card-toolbar"> 
                        </div>
                    </div> 
                    <div class="card-body d-flex flex-column">
                        <div class="flex-grow-1">
                            <div id="kt_mixed_widget_14_chart" style="height: 300px"></div>
                        </div>
                        <div class="pt-5">
                            <p class="text-center font-weight-normal font-size-lg pb-7">Notes: Click to get more details for your upcoming Targets
                         </p>
                            <button type="" id="generateRankTargetReport" class="btn btn-success btn-shadow-hover font-weight-bolder w-100 py-3">View Rank Target</button>
                        </div>
                    </div>  
                </div> 
            </div> 


            <div class="col-lg-12 d-none" id="reward-target-details"> 
                <div class="card card-custom card-stretch gutter-b"> 
                    <div class="card-header border-0 py-5">
                        <h3 class="card-title align-items-start flex-column">
                            <span class="card-label font-weight-bolder text-dark">Reward Target Details</span>
        
                        </h3> 
                    </div>
                    <div class="card-body pt-0 pb-3">
                        <div class="tab-content">
                            <ol class="display-5">
                                <li>
                                    <div class="progress mb-10">
                                        <div class="progress-bar bg-success" role="progressbar" 
                                             style="width: {{ ($data['levelCount'][1] / 3) * 100 }}%;" 
                                             aria-valuenow="{{ $data['levelCount'][1] }}" 
                                             aria-valuemin="0" 
                                             aria-valuemax="3"> 
                                            {{ $data['levelCount'][1] }} /  3
                                        </div>
                                    </div>
                                </li>
                                <li>
                                    <div class="progress mb-10">
                                        <div class="progress-bar bg-warning" role="progressbar" 
                                             style="width: {{ ($data['levelCount'][2] / 5) * 100 }}%;" 
                                             aria-valuenow="{{ $data['levelCount'][2] }}" 
                                             aria-valuemin="0" 
                                             aria-valuemax="5"> 
                                            {{ $data['levelCount'][2] }}  / 5
                                        </div>
                                    </div>
                                </li>
                                <li>
                                    <div class="progress mb-10">
                                        <div class="progress-bar bg-success" role="progressbar" 
                                             style="width: {{ ($data['levelCount'][3] / 7) * 100 }}%;" 
                                             aria-valuenow="{{ $data['levelCount'][3] }}" 
                                             aria-valuemin="0" 
                                             aria-valuemax="7"> 
                                            {{ $data['levelCount'][3] }}  / 7
                                        </div>
                                    </div>
                                </li>
                                <li>
                                    <div class="progress mb-10">
                                        <div class="progress-bar bg-warning" role="progressbar" 
                                             style="width: {{ ($data['levelCount'][4] / 9) * 100 }}%;" 
                                             aria-valuenow="{{ $data['levelCount'][4] }}" 
                                             aria-valuemin="0" 
                                             aria-valuemax="9"> 
                                            {{ $data['levelCount'][4] }}  / 9
                                        </div>
                                    </div>
                                </li>
                                <li>
                                    <div class="progress mb-10">
                                        <div class="progress-bar bg-danger" role="progressbar" 
                                             style="width: {{ ($data['levelCount'][5] / 11) * 100 }}%;" 
                                             aria-valuenow="{{ $data['levelCount'][5] }}" 
                                             aria-valuemin="0" 
                                             aria-valuemax="11"> 
                                            {{ $data['levelCount'][5] }}  / 11
                                        </div>
                                    </div>
                                </li>
                                <li>
                                    <div class="progress mb-10">
                                        <div class="progress-bar bg-info" role="progressbar" 
                                             style="width: {{ ($data['levelCount'][6] / 13) * 100 }}%;" 
                                             aria-valuenow="{{ $data['levelCount'][6] }}" 
                                             aria-valuemin="0" 
                                             aria-valuemax="13"> 
                                            {{ $data['levelCount'][6] }}  / 13
                                        </div>
                                    </div>
                                </li>
                                <li>
                                    <div class="progress mb-10">
                                        <div class="progress-bar bg-success" role="progressbar" 
                                             style="width: {{ ($data['levelCount'][7] / 15) * 100 }}%;" 
                                             aria-valuenow="{{ $data['levelCount'][7] }}" 
                                             aria-valuemin="0" 
                                             aria-valuemax="15"> 
                                            {{ $data['levelCount'][7] }}  / 15
                                        </div>
                                    </div>
                                </li>
                            </ol>
                        </div>
                    </div>
                    
                </div> 
            </div> 
              {{-- rank details --}}
            
            <div class="col-lg-12 d-none" id="rank-target-details"> 
                <div class="card card-custom card-stretch gutter-b"> 
                    <div class="card-header border-0 py-5">
                        <h3 class="card-title align-items-start flex-column">
                            <span class="card-label font-weight-bolder text-dark">Rank Target Details</span> 
                        </h3> 
                    </div>
                    <!--end::Header-->
                    <!--begin::Body-->
                    <div class="card-body pt-0 pb-3 ">
                        <div class="tab-content">
                             <ol class="display-5">
                                <li>
                                    <div class="progress mt-20 mb-10">
                                        <div class="progress-bar bg-primary" role="progressbar" 
                                        style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="10"> 0 </div>
                                    </div>
                                </li>
                                <li>
                                    <div class="progress mb-10">
                                        <div class="progress-bar bg-primary" role="progressbar" 
                                        style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="10"> 0 </div>
                                    </div>
                                </li>
                                <li>
                                    <div class="progress mb-10">
                                        <div class="progress-bar bg-primary" role="progressbar" 
                                        style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="10"> 0 </div>
                                    </div>
                                </li>
                                <li>
                                    <div class="progress mb-10">
                                        <div class="progress-bar bg-primary" role="progressbar" 
                                        style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="10"> 0 </div>
                                    </div>
                                </li>
                                <li>
                                    <div class="progress mb-10">
                                        <div class="progress-bar bg-primary" role="progressbar" 
                                        style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="10"> 0 </div>
                                    </div>
                                </li>
                                <li>
                                    <div class="progress mb-10">
                                        <div class="progress-bar bg-primary" role="progressbar" 
                                        style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="10"> 0 </div>
                                    </div>
                                </li>
                                <li>
                                    <div class="progress mb-10">
                                        <div class="progress-bar bg-primary" role="progressbar" 
                                        style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="10"> 0 </div>
                                    </div>
                                </li>
                             </ol>
                            
                           
                        </div>
                    </div>
                    <!--end::Body-->
                </div> 
            </div> 

       
        </div> 
    </div>
    <!--end::Entry-->
</div>
@endsection
@section('page_js')
    <script src="{{ asset('assets/js/pages/features/charts/apexcharts.js') }}"></script>
    <script>
        $(document).ready(function () {

            $('#generateRewardTargetReport').on('click', function () { 
                $('#reward-target-details').removeClass('d-none'); 
                $('#rank-target-details').addClass('d-none').removeClass('d-block'); 
            });

            // Handle Rank Target button click
            $('#generateRankTargetReport').on('click', function () {
                $('#rank-target-details').removeClass('d-none'); 
                $('#reward-target-details').addClass('d-none'); 
            });

           
        });
    </script>
@endsection
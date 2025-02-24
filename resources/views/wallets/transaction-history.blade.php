@extends('demo.layout.app')
@section('title','Transaction History')
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
                    <h5 class="text-dark font-weight-bold my-1 mr-5">Transaction History </h5>
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
                            <a href="" class="text-muted">Transaction History</a>
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
                        <span class="card-label font-weight-bolder text-dark">Transaction History</span> 
                    </h3>
                    <div class="card-toolbar">
                          
                    </div>
                </div>
                
                <div class="card-body py-0">
                    <!--begin::Table-->
                    <div class="table-responsive py-4">
                        <table class="table table-head-custom table-vertical-center pa-3" id="kt_advance_table_widget_4">
                            <thead>
                                <tr class="text-left"> 
                                    <th class="pl-0" style="">S#</th>

                                    <th style="min-width: 110px">Date</th>
                                    <th style="min-width: 110px">From Wallet</th>
                                    <th style="min-width: 110px">To Wallet</th>
                                    <th style="min-width: 110px">Amount</th>
                                    <th style="min-width: 110px">Charge</th> 
                                    <th style="min-width: 120px">Final Transferred</th>
                                    <th style="min-width: 120px">Description</th>  
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($transactions  as $transaction)
                                    <tr class="pl-0">
                                        <td>
                                            <span href="#" class="text-dark-75 font-weight-bolder d-block font-size-sm">{{ $loop->iteration }}</span>
                                        </td>  
                                        <td>
                                            <a class="text-dark-75 font-weight-bolder d-block font-size-sm"> {{ $transaction->created_at->format('Y-m-d H:i') }} </a> 
                                        </td>  
                                        <td>
                                            <a class="text-dark-75 font-weight-bolder d-block font-size-sm"> {{ $transaction->from_wallet_type }} </a> 
                                        </td>  
                                        <td>
                                            <span class="text-dark-75 font-weight-bolder d-block font-size-sm"> 
                                                {{ $transaction->to_wallet_type }}
                                            </span> 
                                        </td>
                                        <td>
                                            <span class="text-dark-75 font-weight-bolder d-block font-size-sm"> 
                                                {{ $transaction->amount  }}
                                            </span> 
                                        </td>
                                        <td>   <span class="text-dark-75 font-weight-bolder d-block font-size-sm"> {{ ucfirst($transaction->charge ) }} </span>    </td>
                                        <td>   <span class="text-dark-75 font-weight-bolder d-block font-size-sm">{{ ucfirst($transaction->final_amount ) }}</span>    </td>
                                        <td>
                                            <span class="text-dark-75 font-weight-bolder d-block font-size-sm" 
                                                  data-desc="{{ $transaction->description }}" 
                                                  data-toggle="modal" 
                                                  data-target="#description_trans"> 
                                                <i class="fa fa-eye"></i> 
                                            </span>
                                        </td>
                                        
                                        
                                    </tr>   
                                @endforeach
                                @if(!$transactions->count() >0)
                                    <tfoot>
                                        <tr class="text-center text-danger">
                                            <th colspan="7"  > No Transaction History Found </th>
                                        </tr>
                                    </tfoot>
                                @endif
                            </tbody>
                        </table>
                        <div class="d-flex justify-content-center align-items-center flex-wrap mt-5">
                            <div class="d-flex flex-wrap py-2 mr-3">
                                @if ($transactions->onFirstPage())
                                    <a href="#" class="btn btn-icon btn-sm btn-light-primary mr-2 my-1 disabled"><i class="ki ki-bold-double-arrow-back icon-xs"></i></a>
                                    <a href="#" class="btn btn-icon btn-sm btn-light-primary mr-2 my-1 disabled"><i class="ki ki-bold-arrow-back icon-xs"></i></a>
                                @else
                                    <a href="{{ $transactions->url(1) }}" class="btn btn-icon btn-sm btn-light-primary mr-2 my-1"><i class="ki ki-bold-double-arrow-back icon-xs"></i></a>
                                    <a href="{{ $transactions->previousPageUrl() }}" class="btn btn-icon btn-sm btn-light-primary mr-2 my-1"><i class="ki ki-bold-arrow-back icon-xs"></i></a>
                                @endif
                                
                                @foreach ($transactions->getUrlRange(max(1, $transactions->currentPage() - 2), min($transactions->lastPage(), $transactions->currentPage() + 2)) as $page => $url)
                                    <a href="{{ $url }}" class="btn btn-icon btn-sm border-0 btn-hover-primary mr-2 my-1 {{ $page == $transactions->currentPage() ? 'active' : '' }}">
                                        {{ $page }}
                                    </a>
                                @endforeach 
                                @if ($transactions->hasMorePages())
                                    <a href="{{ $transactions->nextPageUrl() }}" class="btn btn-icon btn-sm btn-light-primary mr-2 my-1"><i class="ki ki-bold-arrow-next icon-xs"></i></a>
                                    <a href="{{ $transactions->url($transactions->lastPage()) }}" class="btn btn-icon btn-sm btn-light-primary mr-2 my-1"><i class="ki ki-bold-double-arrow-next icon-xs"></i></a>
                                @else
                                    <a href="#" class="btn btn-icon btn-sm btn-light-primary mr-2 my-1 disabled"><i class="ki ki-bold-arrow-next icon-xs"></i></a>
                                    <a href="#" class="btn btn-icon btn-sm btn-light-primary mr-2 my-1 disabled"><i class="ki ki-bold-double-arrow-next icon-xs"></i></a>
                                @endif
                            </div>
                        </div>
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
 
<div class="modal fade" id="description_trans" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="{{ route('wallet.transfer.to.online') }}" method="POST">
                @csrf
                <div class="modal-header text-right">
                   
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <i aria-hidden="true" class="ki ki-close"></i>
                    </button>
                </div>
                <div class="modal-body ">
                    <p class="font-weight-bold" id="transactionDescription"></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="rounded-0 btn btn-light-primary btn-sm" data-dismiss="modal">Close</button> 
                </div>
            </form>
        </div>
    </div>
</div>


 

 
<!--end::Content-->
@endsection
@section('page_js')
    <script>
         var avatar = new KTImageInput('kt_profile_avatar');  
        
    </script>
    <script>
        $(document).ready(function() {
            $('#description_trans').on('show.bs.modal', function(event) {
                var button = $(event.relatedTarget); // Get the button that triggered the modal
                var description = button.data('desc'); // Extract info from data-* attributes 
                $('#transactionDescription').text(description); // Inject into modal
            });
        });
    </script>
        
    
@endsection
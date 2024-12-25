@extends('demo.layout.app')
@section('content')
<div class="content d-flex flex-column flex-column-fluid" id="kt_content"> 
    <div class="d-flex flex-column-fluid"> 
        <div class="container"> 
            <div class="row"> 
                <div class="col-lg-12 col-xxl-12">
                    <!--begin::List Widget 9-->
                    <div class=" ">
                        <!--begin::Header--> 

                        <div class="col-lg-12 p-6">
                            <!--begin::Callout-->
                            <div class="card card-custom mb-2 bg-diagonal bg-diagonal-light-primary">
                                <div class="card-body">
                                    <div class="d-flex align-items-center justify-content-between p-4">
                                        <!--begin::Content-->
                                        <div class="d-flex flex-column mr-5">
                                            <a href="#" class="h4 text-dark text-hover-primary mb-5">
                                                 Your Request is Under Review

                                             </a>
                                            <p class="text-dark-50">
                                            <b>What Happens Next?</b>
                                            <ol>
                                                <li class="mb-5">
                                                    Review Process:
                                                    <ul>
                                                        <li>Our experts are verifying the details you submitted for accuracy and completeness.</li>
                                                        <li>This process ensures that all information adheres to our policies and guidelines.</li>
                                                    </ul>
                                                </li>

                                                <li class="mb-5">
                                                    Estimated Time frame:
                                                    <ul>
                                                        <li>Reviews are typically completed within 24 hours, though complex cases may take slightly longer.</li>
                                                        <li>Rest assured, we aim to complete the review as quickly as possible while maintaining thoroughness.</li>
                                                    </ul>
                                                </li>


                                                <li class="mb-5">
                                                    Approval Notification:
                                                    <ul>
                                                        <li>Once your request is approved, you will receive a confirmation via email or through your account dashboard.</li>
                                                        <li>If further action is needed, we will also notify you promptly.</li>
                                                    </ul>
                                                </li>


                                                <li class="mb-5">
                                                    What You Can Do in the Meantime?
                                                    <ul>
                                                        <li>
                                                           
                                                           <b>Stay Updated</b> : Ensure your contact information is up to date to receive timely notifications
                                                        
                                                        </li>
                                                        <li>
                                                            <b>Reach Out for Support</b>: If you have urgent questions or concerns, our support team is here to help.
                                                        </li>
                                                    </ul>
                                                </li> 
                                            </ol>    
                                            We value your patience and understanding as we complete this important process. Thank you for choosing us, and we look forward to serving you better!


                                            </p>
                                        </div>
                                        <!--end::Content-->
                                        <!--begin::Button-->
                                        <div class="ml-6 flex-shrink-0">
                                            <a href="#" data-toggle="modal" data-target="#kt_chat_modal" class="btn font-weight-bolder text-uppercase font-size-lg btn-primary py-3 px-6">Contact Us</a>
                                        </div>
                                        <!--end::Button-->
                                    </div>
                                </div>
                            </div>
                            <!--end::Callout-->
                        </div>
                    </div>
                    <!--end: List Widget 9-->
                </div> 
            </div> 
        </div> 
    </div> 
</div>
@endsection
@section('page_js')
    <script src="{{ asset('assets/js/pages/features/charts/apexcharts.js') }}"></script>
    <script>
        $(document).ready(function () {
            $("#generateReport").on("click", function () {
                // Change the class of the first div
                $("#div1").removeClass("col-lg-12").addClass("col-lg-4");

                // Show the second div and adjust its column size
                $("#div2").removeClass("d-none").addClass("col-lg-8");
            });
        });
    </script>
@endsection
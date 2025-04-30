@extends('demo.layout.app')
@section('title', 'Account TopUp')
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
                        <h5 class="text-dark font-weight-bold my-1 mr-5">Account TopUp </h5> 
                        <ul class="breadcrumb breadcrumb-transparent breadcrumb-dot font-weight-bold p-0 my-2 font-size-sm">
                            <li class="breadcrumb-item">
                                <a href="{{ route('dashboard') }}" class="text-muted">Dashboard</a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="#" class="text-muted">Users</a>
                            </li> 
                            <li class="breadcrumb-item">
                                <a href="" class="text-muted">Account TopUp</a>
                            </li>
                        </ul>
                    </div>
                </div> 
            </div>
        </div> 
        <div class="flex-column-fluid">
            <div class="container">
                <div class="card card-custom gutter-b"> 
                    <div class="card-header border-0 py-5 justify-content-right">
                        <h3 class="card-title "> 
                            <button type="button" 
                            data-toggle="modal" data-target="#account-top-up"
                            class="btn btn-danger btn-sm font-weight-bold rounded-0">Account TopUp</button>  
                        </h3> 
                    </div> 
                    <div class="card-body py-0"> 
                        <div class="table-responsive">
                            <table class="table table-head-custom table-vertical-center" id="kt_advance_table_widget_4">
                                <thead>
                                    <tr class=" ">
                                        <th class=" "> S#</th>
                                        <th class=" "> Amount</th>
                                        <th class=" "> Type</th>
                                        <th class=" "> TopUp For</th> 
                                        <th class=" "> Date</th>  
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($investments as $investment )
                                        <tr class=" "> 
                                            <td>{{ $loop->iteration }}</td>
                                            <th>{{ $investment->amount }}</th>
                                            <th>{{ $investment->type }}</th>
                                            <td>{{ $investment->user->name }}</td> 
                                            <td>{{ $investment->created_at  }}</td>  
                                        </tr>
                                    @endforeach
                                </tbody> 
                            </table> 
                        </div>
                        <!--begin::Pagination-->
                        <div class="d-flex justify-content-between align-items-center flex-wrap">
                            <div class="d-flex flex-wrap py-2 mr-3">
                                
                            </div>
                        </div> 
                    </div> 
                </div> 
            </div> 
        </div> 
    </div>  

    <div class="modal fade" id="account-top-up" tabindex="-1" role="dialog"     aria-labelledby="account-top-up" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="account-top-up">  Generate Activation Code </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <i aria-hidden="true" class="ki ki-close"></i>
                    </button>
                </div> 
                    <div class="modal-body"> 
                        <form id="topupForm">
                            @csrf
                            <div class="form-group">
                                <label for="user_id">Select User</label>
                                <select name="user_id" id="user_id" class="form-control" required>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                                    @endforeach
                                </select>
                            </div>
                        
                            <div class="form-group">
                                <label for="amount">Top-Up Amount</label>
                                <input type="number" name="amount" id="amount" class="form-control" required min="1">
                            </div>
                        </form>
                        
                    </div>
                    <div class="modal-footer">
                        <button id="generateTopUP" class="btn-sm btn btn-primary rounded-0">Account Topup</button>
                        <button type="button" class="btn btn-light-primary btn-sm font-weight-bold rounded-0" data-dismiss="modal">Close</button>
                       
                    </div> 
            </div>
        </div>
    </div> 

@endsection
@section('page_js') 
<script>
    $('#generateTopUP').click(function(e) {
        e.preventDefault();

        let formData = {
            _token: '{{ csrf_token() }}',
            user_id: $('#user_id').val(),
            amount: $('#amount').val()
        };

        $.ajax({
            url: '{{ route("user.topup.store") }}',
            type: 'POST',
            data: formData,
            success: function(response) { 
                toastr.success(response.message);
                $('#account-top-up').modal('hide');
                $('#topupForm')[0].reset();
            },
            error: function(xhr) {
                toastr.error(xhr.responseJSON.message);
               
            }
        });
    });
</script>

@endsection

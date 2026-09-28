@extends('demo.layout.app')
@section('title', 'KYC Verification')
@section('content')
<div class="content d-flex flex-column flex-column-fluid" id="kt_content">
    <div class="py-2 subheader py-lg-6 subheader-solid" id="kt_subheader">
        <div class="flex-wrap container-fluid d-flex align-items-center justify-content-between flex-sm-nowrap">
            <div class="flex-wrap mr-1 d-flex align-items-center">
                <div class="flex-wrap mr-5 d-flex align-items-baseline">
                    <h5 class="my-1 mr-5 text-dark font-weight-bold">KYC Verification</h5>
                    <ul class="p-0 my-2 breadcrumb breadcrumb-transparent breadcrumb-dot font-weight-bold font-size-sm">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-muted">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="#" class="text-muted">KYC</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="flex-column-fluid">
        <div class="container" style="max-width:680px;">

            {{-- Status badge --}}
            <div class="mb-5 mt-6">
                @if($user->kyc_status === 'rejected')
                    <div class="alert alert-danger d-flex align-items-center">
                        <i class="fas fa-times-circle fa-lg mr-3"></i>
                        <div>
                            <strong>KYC Rejected.</strong>
                            @if($user->kyc_rejection_reason)
                                Reason: {{ $user->kyc_rejection_reason }}
                            @endif
                            <br><small>Please re-upload your CNIC photos below.</small>
                        </div>
                    </div>
                @elseif($user->kyc_status === 'submitted')
                    <div class="alert alert-info d-flex align-items-center">
                        <i class="fas fa-clock fa-lg mr-3"></i>
                        <div>
                            <strong>Documents Submitted.</strong> Your KYC is under review by admin.
                            Submitted: {{ $user->kyc_submitted_at ? \Carbon\Carbon::parse($user->kyc_submitted_at)->format('d M Y, h:i A') : '—' }}
                        </div>
                    </div>
                @else
                    <div class="alert alert-warning d-flex align-items-center">
                        <i class="fas fa-exclamation-triangle fa-lg mr-3"></i>
                        <div>
                            <strong>KYC Required.</strong> Please upload your CNIC (front and back) to complete verification.
                        </div>
                    </div>
                @endif
            </div>

            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            {{-- Only show upload form when pending or rejected --}}
            @if(in_array($user->kyc_status, ['pending', 'rejected']))
            <div class="card card-custom gutter-b">
                <div class="card-header border-0">
                    <h3 class="card-title font-weight-bolder text-dark">
                        <i class="fas fa-id-card text-primary mr-2"></i> Upload CNIC Photos
                    </h3>
                </div>
                <div class="card-body pt-0">
                    <form action="{{ route('kyc.submit') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        {{-- Front --}}
                        <div class="mb-6">
                            <label class="font-weight-bold font-size-sm d-block mb-2">
                                CNIC Front <span class="text-danger">*</span>
                            </label>
                            <label class="d-block" style="cursor:pointer;border:2px dashed #e2e8f0;border-radius:10px;padding:1.5rem;text-align:center;background:#fafafa;" id="front-label">
                                <input type="file" name="cnic_front" id="cnic_front" accept="image/*" class="d-none" onchange="previewImg(this,'front-preview','front-label-text')" required />
                                <i class="fas fa-id-card fa-2x text-muted mb-2 d-block"></i>
                                <span id="front-label-text" style="font-size:.85rem;color:#64748b;">Click to upload front side of CNIC</span>
                            </label>
                            <div id="front-preview" class="mt-3 d-none text-center">
                                <img src="" alt="Front Preview" style="max-width:100%;max-height:200px;border-radius:8px;border:1px solid #e2e8f0;" />
                            </div>
                            @error('cnic_front') <div class="text-danger font-size-sm mt-1">{{ $message }}</div> @enderror
                        </div>

                        {{-- Back --}}
                        <div class="mb-6">
                            <label class="font-weight-bold font-size-sm d-block mb-2">
                                CNIC Back <span class="text-danger">*</span>
                            </label>
                            <label class="d-block" style="cursor:pointer;border:2px dashed #e2e8f0;border-radius:10px;padding:1.5rem;text-align:center;background:#fafafa;" id="back-label">
                                <input type="file" name="cnic_back" id="cnic_back" accept="image/*" class="d-none" onchange="previewImg(this,'back-preview','back-label-text')" required />
                                <i class="fas fa-id-card fa-2x text-muted mb-2 d-block"></i>
                                <span id="back-label-text" style="font-size:.85rem;color:#64748b;">Click to upload back side of CNIC</span>
                            </label>
                            <div id="back-preview" class="mt-3 d-none text-center">
                                <img src="" alt="Back Preview" style="max-width:100%;max-height:200px;border-radius:8px;border:1px solid #e2e8f0;" />
                            </div>
                            @error('cnic_back') <div class="text-danger font-size-sm mt-1">{{ $message }}</div> @enderror
                        </div>

                        <div class="text-muted font-size-sm mb-4">
                            <i class="fas fa-info-circle mr-1"></i>
                            Accepted formats: JPG, JPEG, PNG. Max size: 4MB per image.
                        </div>

                        <button type="submit" class="btn btn-primary font-weight-bold rounded-0 px-8">
                            <i class="fas fa-upload mr-2"></i> Submit KYC Documents
                        </button>
                    </form>
                </div>
            </div>
            @endif

            {{-- Show submitted images (read-only) when status = submitted --}}
            @if($user->kyc_status === 'submitted' && ($frontUrl || $backUrl))
            <div class="card card-custom gutter-b">
                <div class="card-header border-0">
                    <h3 class="card-title font-weight-bolder text-dark">Submitted Documents</h3>
                </div>
                <div class="card-body pt-0">
                    <div class="row">
                        @if($frontUrl)
                        <div class="col-md-6 mb-4">
                            <p class="font-weight-bold font-size-sm mb-2">CNIC Front</p>
                            <img src="{{ $frontUrl }}" alt="CNIC Front" style="width:100%;border-radius:8px;border:1px solid #e2e8f0;" />
                        </div>
                        @endif
                        @if($backUrl)
                        <div class="col-md-6 mb-4">
                            <p class="font-weight-bold font-size-sm mb-2">CNIC Back</p>
                            <img src="{{ $backUrl }}" alt="CNIC Back" style="width:100%;border-radius:8px;border:1px solid #e2e8f0;" />
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            @endif

        </div>
    </div>
</div>
@endsection
@section('page_js')
<script>
function previewImg(input, previewId, labelId) {
    var preview = document.getElementById(previewId);
    var label   = document.getElementById(labelId);
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            preview.querySelector('img').src = e.target.result;
            preview.classList.remove('d-none');
            if (label) label.textContent = input.files[0].name;
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
@endsection

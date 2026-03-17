@extends('demo.layout.app')
@section('title', 'Change Member Parent')
@section('custom_css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    .select2-container--default .select2-selection--single {
        height: 42px;
        border: 1px solid #e4e6ef;
        border-radius: 0.42rem;
        padding: 6px 12px;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 28px;
        color: #3f4254;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 40px;
    }
    .select2-container { width: 100% !important; }
    .warning-box {
        background: #fff3cd;
        border: 1px solid #ffc107;
        border-radius: 0.42rem;
        padding: 14px 18px;
    }
</style>
@endsection

@section('content')
<div class="content d-flex flex-column flex-column-fluid" id="kt_content">

    {{-- Subheader --}}
    <div class="subheader py-2 py-lg-6 subheader-solid" id="kt_subheader">
        <div class="container-fluid d-flex align-items-center justify-content-between flex-wrap flex-sm-nowrap">
            <div class="d-flex align-items-center flex-wrap mr-2">
                <h5 class="text-dark font-weight-bold mt-2 mb-2 mr-5">Change Member Parent</h5>
                <ul class="breadcrumb breadcrumb-transparent breadcrumb-dot font-weight-bold p-0 my-2 font-size-sm">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-muted">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('users.index') }}" class="text-muted">Members</a></li>
                    <li class="breadcrumb-item"><a href="#" class="text-muted">Change Parent</a></li>
                </ul>
            </div>
        </div>
    </div>

    <div class="d-flex flex-column-fluid">
        <div class="container-fluid">
            <div class="row justify-content-center mt-6">
                <div class="col-lg-7">

                    {{-- Alerts --}}
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <strong>Success!</strong> {{ session('success') }}
                            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                        </div>
                    @endif
                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <strong>Error!</strong> {{ session('error') }}
                            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                        </div>
                    @endif

                    {{-- Warning notice --}}
                    <div class="warning-box mb-6">
                        <strong>Important:</strong> Changing a member's parent will rebuild their entire referral tree.
                        All <u>future</u> commissions will flow to the new parent's upline.
                        Historical records are not affected.
                    </div>

                    {{-- Form Card --}}
                    <div class="card card-custom">
                        <div class="card-header">
                            <div class="card-title">
                                <h3 class="card-label">Move Member to New Parent</h3>
                            </div>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('admin.change.parent.process') }}" method="POST" id="changeParentForm">
                                @csrf

                                {{-- Member to move --}}
                                <div class="form-group">
                                    <label class="font-weight-bold">Member to Move <span class="text-danger">*</span></label>
                                    <select name="user_id" id="user_id" class="form-control select2-user" required>
                                        <option value="">-- Search and select member --</option>
                                        @foreach($users as $u)
                                            <option value="{{ $u->id }}" {{ old('user_id') == $u->id ? 'selected' : '' }}>
                                                {{ $u->username }} — {{ $u->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('user_id')<span class="text-danger small">{{ $message }}</span>@enderror
                                </div>

                                {{-- New parent --}}
                                <div class="form-group">
                                    <label class="font-weight-bold">New Parent <span class="text-danger">*</span></label>
                                    <select name="new_parent_id" id="new_parent_id" class="form-control select2-user" required>
                                        <option value="">-- Search and select new parent --</option>
                                        @foreach($users as $u)
                                            <option value="{{ $u->id }}" {{ old('new_parent_id') == $u->id ? 'selected' : '' }}>
                                                {{ $u->username }} — {{ $u->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('new_parent_id')<span class="text-danger small">{{ $message }}</span>@enderror
                                </div>

                                {{-- Preview panel (filled via JS) --}}
                                <div id="previewPanel" class="alert alert-info d-none mt-2 mb-4">
                                    Moving <strong id="previewUser">—</strong>
                                    under <strong id="previewParent">—</strong>
                                </div>

                                <div class="d-flex justify-content-end">
                                    <a href="{{ route('users.index') }}" class="btn btn-secondary mr-3">Cancel</a>
                                    <button type="submit" class="btn btn-danger" id="submitBtn" disabled>
                                        Confirm Change
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@section('custom_js')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function () {

    $('#user_id').select2({ placeholder: 'Type to search...', allowClear: true });
    $('#new_parent_id').select2({ placeholder: 'Type to search...', allowClear: true });

    function updatePreview() {
        var userId   = $('#user_id').val();
        var parentId = $('#new_parent_id').val();
        var userText   = $('#user_id option:selected').text().trim();
        var parentText = $('#new_parent_id option:selected').text().trim();

        if (userId && parentId) {
            $('#previewUser').text(userText);
            $('#previewParent').text(parentText);
            $('#previewPanel').removeClass('d-none');
            $('#submitBtn').prop('disabled', false);
        } else {
            $('#previewPanel').addClass('d-none');
            $('#submitBtn').prop('disabled', true);
        }
    }

    $('#user_id').on('select2:select select2:unselect change', updatePreview);
    $('#new_parent_id').on('select2:select select2:unselect change', updatePreview);

    // Confirm before submit
    $('#changeParentForm').on('submit', function (e) {
        var userText   = $('#user_id option:selected').text().trim();
        var parentText = $('#new_parent_id option:selected').text().trim();
        if (!confirm('Are you sure you want to move\n"' + userText + '"\nunder\n"' + parentText + '"?\n\nThis will rebuild their entire referral tree.')) {
            e.preventDefault();
        }
    });
});
</script>
@endsection

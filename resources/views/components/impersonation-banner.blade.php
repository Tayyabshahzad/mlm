@if(session()->has('impersonating_user_id'))
    @php
        $impersonatedUser = \App\Models\User::find(session('impersonating_user_id'));
        $originalAdmin = \App\Models\User::find(session('original_admin_id'));
    @endphp

    <div class="impersonation-banner alert alert-warning alert-dismissible position-fixed w-100 m-0 rounded-0 shadow-lg"
         style="top: 0; left: 0; z-index: 9999; border: none;">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-user-secret fa-2x me-3 text-warning"></i>
                        <div>
                            <h6 class="mb-1 fw-bold">
                                🎭 ADMIN IMPERSONATION ACTIVE
                            </h6>
                            <small>
                                You ({{ $originalAdmin->name ?? 'Admin' }}) are logged in as
                                <strong>{{ $impersonatedUser->name ?? 'Unknown User' }}</strong>
                                (ID: {{ session('impersonating_user_id') }})
                            </small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 text-end">
                    <form action="{{ route('admin.impersonation.stop') }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-danger btn-sm me-2">
                            <i class="fas fa-stop"></i> Stop Impersonation
                        </button>
                    </form>
                    <a href="{{ route('admin.impersonation.index') }}" class="btn btn-outline-dark btn-sm">
                        <i class="fas fa-users"></i> Switch User
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Add padding to body to account for fixed banner -->
    <style>
        body {
            padding-top: 80px !important;
        }

        .impersonation-banner {
            background: linear-gradient(90deg, #fff3cd 0%, #ffeaa7 100%) !important;
            border-left: 5px solid #ff9800 !important;
            animation: pulse-glow 2s infinite;
        }

        @keyframes pulse-glow {
            0% { box-shadow: 0 0 5px rgba(255, 152, 0, 0.5); }
            50% { box-shadow: 0 0 20px rgba(255, 152, 0, 0.8); }
            100% { box-shadow: 0 0 5px rgba(255, 152, 0, 0.5); }
        }

        .impersonation-banner .btn-danger {
            animation: pulse 1.5s infinite;
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
    </style>

    <!-- Auto-hide after 10 seconds (optional) -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Auto minimize banner after 10 seconds
            setTimeout(function() {
                const banner = document.querySelector('.impersonation-banner');
                if (banner) {
                    banner.style.transform = 'translateY(-50px)';
                    banner.style.transition = 'transform 0.3s ease';

                    // Show a small indicator
                    const indicator = document.createElement('div');
                    indicator.innerHTML = '🎭 Impersonating';
                    indicator.className = 'position-fixed bg-warning text-dark px-3 py-1 rounded-bottom shadow';
                    indicator.style.cssText = 'top: 0; right: 20px; z-index: 9998; font-size: 12px; cursor: pointer;';
                    indicator.onclick = function() {
                        banner.style.transform = 'translateY(0)';
                        this.remove();
                    };
                    document.body.appendChild(indicator);
                }
            }, 10000);
        });
    </script>
@endif
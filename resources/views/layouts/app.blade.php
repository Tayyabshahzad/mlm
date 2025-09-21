<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'MLM System') }} - @yield('title', 'Admin Panel')</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">

    @stack('styles')

    <style>
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .content-wrapper {
            min-height: 100vh;
        }
        .navbar-brand {
            font-weight: bold;
        }
    </style>
</head>
<body>
    <!-- Impersonation Banner -->
    @include('components.impersonation-banner')

    <div class="d-flex">
        <!-- Sidebar -->
        <nav class="sidebar text-white p-3" style="width: 250px;">
            <div class="sidebar-brand mb-4">
                <h4><i class="fas fa-chart-line me-2"></i>MLM Admin</h4>
            </div>

            <ul class="nav flex-column">
                <li class="nav-item mb-2">
                    <a href="{{ route('dashboard') }}" class="nav-link text-white">
                        <i class="fas fa-tachometer-alt me-2"></i> Dashboard
                    </a>
                </li>

                @if(auth()->user()->hasRole('admin') || auth()->user()->hasRole('super-admin'))
                    <li class="nav-item mb-2">
                        <a href="{{ route('admin.impersonation.index') }}" class="nav-link text-white">
                            <i class="fas fa-user-secret me-2"></i> User Impersonation
                        </a>
                    </li>
                @endif

                <li class="nav-item mb-2">
                    <a href="{{ route('users.index') }}" class="nav-link text-white">
                        <i class="fas fa-users me-2"></i> Users
                    </a>
                </li>

                <li class="nav-item mb-2">
                    <a href="{{ route('wallets.online') }}" class="nav-link text-white">
                        <i class="fas fa-wallet me-2"></i> Wallets
                    </a>
                </li>

                <li class="nav-item mb-2">
                    <a href="{{ route('genealogy.team') }}" class="nav-link text-white">
                        <i class="fas fa-sitemap me-2"></i> Genealogy
                    </a>
                </li>

                <li class="nav-item mb-2">
                    <a href="{{ route('report.genealogy.tree') }}" class="nav-link text-white">
                        <i class="fas fa-chart-line me-2"></i> Reports
                    </a>
                </li>

                <hr class="my-3">

                <li class="nav-item mb-2">
                    <a href="{{ route('profile.edit') }}" class="nav-link text-white">
                        <i class="fas fa-user-cog me-2"></i> Profile
                    </a>
                </li>

                <li class="nav-item">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="nav-link text-white border-0 bg-transparent w-100 text-start">
                            <i class="fas fa-sign-out-alt me-2"></i> Logout
                        </button>
                    </form>
                </li>
            </ul>
        </nav>

        <!-- Main Content -->
        <div class="flex-grow-1">
            <!-- Top Navigation -->
            <nav class="navbar navbar-expand-lg navbar-light bg-light border-bottom">
                <div class="container-fluid">
                    <span class="navbar-text">
                        Welcome, <strong>{{ auth()->user()->name }}</strong>
                    </span>

                    <div class="navbar-nav ms-auto">
                        @if(session()->has('impersonating_user_id'))
                            <span class="navbar-text text-warning me-3">
                                <i class="fas fa-exclamation-triangle"></i>
                                <strong>Impersonating User</strong>
                            </span>
                        @endif

                        <span class="navbar-text">
                            <i class="fas fa-clock me-1"></i>
                            {{ now()->format('M d, Y - h:i A') }}
                        </span>
                    </div>
                </div>
            </nav>

            <!-- Page Content -->
            <div class="content-wrapper p-4">
                <!-- Flash Messages -->
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i>
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if(session('warning'))
                    <div class="alert alert-warning alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        {{ session('warning') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        <strong>Please fix the following errors:</strong>
                        <ul class="mb-0 mt-2">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @yield('content')
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    @stack('scripts')
</body>
</html>
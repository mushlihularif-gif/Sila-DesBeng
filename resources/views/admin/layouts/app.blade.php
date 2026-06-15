<!DOCTYPE html>
<html lang="{{ str_replace('_','-',app()->getLocale()) }}" class="light-style layout-menu-fixed">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>{{ $title ?? 'SidesBeng Admin' }}</title>
    <link rel="icon" type="image/png" href="{{ asset('Admin/img/favicon/logoisewa.png') }}" />
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@400;500;600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="{{ asset('Admin/vendor/fonts/boxicons.css') }}" />
    <link rel="stylesheet" href="{{ asset('Admin/vendor/css/core.css') }}" />
    <link rel="stylesheet" href="{{ asset('Admin/vendor/css/theme-default.css') }}" />
    <link rel="stylesheet" href="{{ asset('Admin/css/demo.css') }}" />
    <link rel="stylesheet" href="{{ asset('Admin/vendor/libs/perfect-scrollbar/perfect-scrollbar.css') }}" />
    @stack('styles')
</head>
<body>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            @include('admin.partials.sidebar')
            <div class="layout-page">
                @include('admin.partials.navbar')
                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y">
                        @yield('content')
                    </div>
                    @include('admin.partials.footer')
                </div>
            </div>
        </div>
        <div class="layout-overlay layout-menu-toggle"></div>
    </div>

    <script src="{{ asset('Admin/vendor/libs/jquery/jquery.js') }}"></script>
    <script src="{{ asset('Admin/vendor/libs/popper/popper.js') }}"></script>
    <script src="{{ asset('Admin/vendor/js/bootstrap.js') }}"></script>
    <script src="{{ asset('Admin/vendor/js/menu.js') }}"></script>
    <script src="{{ asset('Admin/vendor/libs/perfect-scrollbar/perfect-scrollbar.js') }}"></script>

    <!-- Notifikasi Dinamis -->
    <div id="dynamic-notifications" class="position-fixed top-0 end-0 p-3" style="z-index: 1060; width: 380px;"></div>

    @stack('scripts')
</body>
</html>
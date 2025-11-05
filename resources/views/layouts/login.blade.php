<!DOCTYPE html>
<!--
Template Name: Tinker - HTML Admin Dashboard Template
Author: Left4code
Website: http://www.left4code.com/
Contact: muhammadrizki@left4code.com
Purchase: https://themeforest.net/user/left4code/portfolio
Renew Support: https://themeforest.net/user/left4code/portfolio
License: You must have a valid license purchased only from themeforest(the above link) in order to legally use the theme for your project.
-->
<html lang="en" class="light">
    <!-- BEGIN: Head -->
    <head>
        <meta charset="utf-8">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <link href="{{ asset('dist/images/logo.svg') }}" rel="shortcut icon">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="Tinker admin is super flexible, powerful, clean & modern responsive tailwind admin template with unlimited possibilities.">
        <meta name="keywords" content="admin template, Tinker Admin Template, dashboard template, flat admin template, responsive admin template, web app">
        <meta name="author" content="LEFT4CODE">
        @yield('head')
        <!-- BEGIN: CSS Assets-->
        <link rel="stylesheet" href="{{ asset('dist/css/app.css') }}" />
        @stack('styles')
        <!-- END: CSS Assets-->
    </head>
    <!-- END: Head -->
    <body class="login">
        @yield('content')
        
        <!-- BEGIN: JS Assets-->
        <script src="{{ asset('dist/js/app.js') }}"></script>
        <!-- Fallback for axios if not loaded by app.js -->
        <script>
            (function() {
                if (typeof window.axios === 'undefined') {
                    var script = document.createElement('script');
                    script.src = 'https://cdn.jsdelivr.net/npm/axios@0.25.0/dist/axios.min.js';
                    script.onload = function() {
                        // Set CSRF token after axios loads
                        var token = document.head.querySelector('meta[name="csrf-token"]');
                        if (token && window.axios) {
                            window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
                        }
                    };
                    document.head.appendChild(script);
                }
            })();
        </script>
        @stack('scripts')
        @yield('script')
        <!-- END: JS Assets-->
    </body>
</html>


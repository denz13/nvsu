@extends('layouts.' . $layout)

@section('head')
    <title>Login - Tinker - Tailwind HTML Admin Template</title>
@endsection

@section('content')
    <div class="container sm:px-10">
        <div class="block xl:grid grid-cols-2 gap-4">
            <!-- BEGIN: Login Info -->
            <div class="hidden xl:flex flex-col min-h-screen">
                <a href="" class="-intro-x flex items-center pt-5">
                    @php
                        $loginTopLogo = \App\Models\system_settings::where('key', 'login_logo_top')->orderBy('id','desc')->first();
                        $logoTopPath = $loginTopLogo && $loginTopLogo->description ? $loginTopLogo->description : null;
                        $loginTopSrc = $logoTopPath ? (preg_match('/^(https?:\\/\\/|\\/)/', $logoTopPath) ? $logoTopPath : asset($logoTopPath)) : asset('build/assets/images/logo.svg');
                        $loginTopTextRec = \App\Models\system_settings::where('key', 'login_logo_top_text')->orderBy('id','desc')->first();
                        $loginTopText = $loginTopTextRec && $loginTopTextRec->description ? $loginTopTextRec->description : 'Tinker';
                        $loginCenterRec = \App\Models\system_settings::where('key', 'login_logo_center')->orderBy('id','desc')->first();
                        $loginCenterPath = $loginCenterRec && $loginCenterRec->description ? $loginCenterRec->description : null;
                        $loginCenterSrc = $loginCenterPath ? (preg_match('/^(https?:\\/\\/|\\/)/', $loginCenterPath) ? $loginCenterPath : asset($loginCenterPath)) : asset('build/assets/images/illustration.svg');
                        $loginCenterTextRec = \App\Models\system_settings::where('key', 'login_logo_center_text')->orderBy('id','desc')->first();
                        $loginCenterText = $loginCenterTextRec && $loginCenterTextRec->description ? $loginCenterTextRec->description : 'A few more clicks to <br> sign in to your account.';
                    @endphp
                    <img alt="Icewall Tailwind HTML Admin Template" class="w-6" src="{{ $loginTopSrc }}">
                    <span class="text-white text-lg ml-3">
                        {{ $loginTopText }}
                    </span>
                </a>
                <div class="my-auto">
                    <img alt="Icewall Tailwind HTML Admin Template" class="-intro-x w-1/2 -mt-16" src="{{ $loginCenterSrc }}">
                    <div class="-intro-x text-white font-medium text-4xl leading-tight mt-10">{!! $loginCenterText !!}</div>
                </div>
            </div>
            <!-- END: Login Info -->
            <!-- BEGIN: Login Form -->
            <div class="h-screen xl:h-auto flex py-5 xl:py-0 my-10 xl:my-0">
                <div class="my-auto mx-auto xl:ml-20 bg-white dark:bg-darkmode-600 xl:bg-transparent px-5 sm:px-8 py-8 xl:p-0 rounded-md shadow-md xl:shadow-none w-full sm:w-3/4 lg:w-2/4 xl:w-auto">
                    <h2 class="intro-x font-bold text-2xl xl:text-3xl text-center xl:text-left">Sign In</h2>
                    <div class="intro-x mt-2 text-slate-400 xl:hidden text-center">A few more clicks to sign in to your account. Manage all your e-commerce accounts in one place</div>
                    <div class="intro-x mt-8">
                        <form id="login-form">
                            <input id="email" type="text" class="intro-x login__input form-control py-3 px-4 block" placeholder="Email or ID Number" value="midone@left4code.com">
                            <div id="error-email" class="login__input-error text-danger mt-2"></div>
                            <input id="password" type="password" class="intro-x login__input form-control py-3 px-4 block mt-4" placeholder="Password" value="password">
                            <div id="error-password" class="login__input-error text-danger mt-2"></div>
                        </form>
                    </div>
                    <div class="intro-x flex text-slate-600 dark:text-slate-500 text-xs sm:text-sm mt-4">
                        <div class="flex items-center mr-auto">
                            <input id="remember-me" type="checkbox" class="form-check-input border mr-2">
                            <label class="cursor-pointer select-none" for="remember-me">Remember me</label>
                        </div>
                        <a href="">Forgot Password?</a>
                    </div>
                    <div class="intro-x mt-5 xl:mt-8 text-center xl:text-left">
                        <button id="btn-login" class="btn btn-primary py-3 px-4 w-full xl:w-32 xl:mr-3 align-top">Login</button>
                        <!-- <button class="btn btn-outline-secondary py-3 px-4 w-full xl:w-32 mt-3 xl:mt-0 align-top">Register</button> -->
                    </div>
                    <!-- <div class="intro-x mt-10 xl:mt-24 text-slate-600 dark:text-slate-500 text-center xl:text-left">
                        By signin up, you agree to our <a class="text-primary dark:text-slate-200" href="">Terms and Conditions</a> & <a class="text-primary dark:text-slate-200" href="">Privacy Policy</a>
                    </div> -->
                </div>
            </div>
            <!-- END: Login Form -->
        </div>
    </div>
@endsection

@section('script')
    <script>
        (function () {
            // Wait for axios to be available
            function waitForAxios(callback) {
                if (typeof window.axios !== 'undefined') {
                    callback();
                } else {
                    setTimeout(function() {
                        waitForAxios(callback);
                    }, 50);
                }
            }

            waitForAxios(function() {
                const axiosInstance = window.axios;
                
                async function login() {
                    // Reset state
                    $('#login-form').find('.login__input').removeClass('border-danger')
                    $('#login-form').find('.login__input-error').html('')

                    // Post form
                    let email = $('#email').val()
                    let password = $('#password').val()

                    // Loading state
                    $('#btn-login').html('<i data-loading-icon="oval" data-color="white" class="w-5 h-5 mx-auto"></i>')
                    tailwind.svgLoader()
                    await helper.delay(1500)

                    // Set CSRF token if not already set
                    if (axiosInstance.defaults.headers.common['X-CSRF-TOKEN'] === undefined) {
                        let token = document.head.querySelector('meta[name="csrf-token"]');
                        if (token) {
                            axiosInstance.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
                        }
                    }

                    axiosInstance.post(`login`, {
                        email: email,
                        password: password
                    }).then(res => {
                        console.log('Login response:', res && res.data ? res.data : res);
                        try {
                            const redirectUrl = (res && res.data && res.data.redirect) ? res.data.redirect : '/';
                            if (!redirectUrl) {
                                console.warn('No redirect URL in response, defaulting to /.');
                            }
                            location.href = redirectUrl || '/';
                        } catch (e) {
                            console.error('Redirect handling failed:', e);
                            location.href = '/';
                        }
                    }).catch(err => {
                        console.error('Login error:', err && err.response ? err.response : err);
                        $('#btn-login').html('Login')
                        if (err.response.data.message != 'Wrong email or password.') {
                            for (const [key, val] of Object.entries(err.response.data.errors)) {
                                $(`#${key}`).addClass('border-danger')
                                $(`#error-${key}`).html(val)
                            }
                        } else {
                            $(`#password`).addClass('border-danger')
                            $(`#error-password`).html(err.response.data.message)
                        }
                    })
                }

                $('#login-form').on('keyup', function(e) {
                    if (e.keyCode === 13) {
                        login()
                    }
                })

                $('#btn-login').on('click', function() {
                    login()
                })
            });
        })()
    </script>
@endsection

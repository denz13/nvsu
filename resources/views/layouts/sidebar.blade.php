@php
    $ssLogo = \App\Models\system_settings::where('key', 'sidebar_top_logo')->orderBy('id', 'desc')->first();
    $ssLogoPath = $ssLogo && $ssLogo->description ? $ssLogo->description : null;
    $logoSrc = $ssLogoPath ? (preg_match('/^(https?:\\/\\/|\\/)/', $ssLogoPath) ? $ssLogoPath : asset($ssLogoPath)) : asset('dist/images/logo.svg');
    $ssText = \App\Models\system_settings::where('key', 'sidebar_top_text')->orderBy('id', 'desc')->first();
    $logoText = $ssText && $ssText->description ? $ssText->description : 'Tinker';
@endphp
<nav class="side-nav">
                <a href="" class="intro-x flex items-center pl-5 pt-4 mt-3">
                    <img alt="Midone - HTML Admin Template" class="w-12" src="{{ $logoSrc }}">
                    <span class="hidden xl:block text-white text-lg ml-3"> {{ $logoText }} </span> 
                </a>
                <div class="side-nav__devider my-6"></div>
                <ul>
                    <li>
                        <a href="{{ route('dashboard.dashboard') }}" class="side-menu {{ request()->routeIs('dashboard.*') ? 'side-menu--active' : '' }}">
                            <div class="side-menu__icon"> <i data-lucide="home"></i> </div>
                            <div class="side-menu__title">
                                Dashboard 
                                <!-- <div class="side-menu__sub-icon transform rotate-180"> <i data-lucide="chevron-down"></i> </div> -->
                            </div>
                        </a>
                        <!-- <ul class="side-menu__sub-open">
                            <li>
                                <a href="side-menu-light-dashboard-overview-1.html" class="side-menu">
                                    <div class="side-menu__icon"> <i data-lucide="activity"></i> </div>
                                    <div class="side-menu__title"> Overview 1 </div>
                                </a>
                            </li>
                            <li>
                                <a href="side-menu-light-dashboard-overview-2.html" class="side-menu">
                                    <div class="side-menu__icon"> <i data-lucide="activity"></i> </div>
                                    <div class="side-menu__title"> Overview 2 </div>
                                </a>
                            </li>
                            <li>
                                <a href="index.html" class="side-menu side-menu--active">
                                    <div class="side-menu__icon"> <i data-lucide="activity"></i> </div>
                                    <div class="side-menu__title"> Overview 3 </div>
                                </a>
                            </li>
                            <li>
                                <a href="side-menu-light-dashboard-overview-4.html" class="side-menu">
                                    <div class="side-menu__icon"> <i data-lucide="activity"></i> </div>
                                    <div class="side-menu__title"> Overview 4 </div>
                                </a>
                            </li>
                        </ul> -->
                    </li>
                    <!-- <li>
                        <a href="javascript:;" class="side-menu">
                            <div class="side-menu__icon"> <i data-lucide="box"></i> </div>
                            <div class="side-menu__title">
                                Menu Layout 
                                <div class="side-menu__sub-icon "> <i data-lucide="chevron-down"></i> </div>
                            </div>
                        </a>
                        <ul class="">
                            <li>
                                <a href="side-menu-light-dashboard-overview-1.html" class="side-menu">
                                    <div class="side-menu__icon"> <i data-lucide="activity"></i> </div>
                                    <div class="side-menu__title"> Side Menu </div>
                                </a>
                            </li>
                            <li>
                                <a href="simple-menu-light-dashboard-overview-1.html" class="side-menu">
                                    <div class="side-menu__icon"> <i data-lucide="activity"></i> </div>
                                    <div class="side-menu__title"> Simple Menu </div>
                                </a>
                            </li>
                            <li>
                                <a href="top-menu-light-dashboard-overview-1.html" class="side-menu">
                                    <div class="side-menu__icon"> <i data-lucide="activity"></i> </div>
                                    <div class="side-menu__title"> Top Menu </div>
                                </a>
                            </li>
                        </ul>
                    </li>
                    <li>
                        <a href="javascript:;" class="side-menu">
                            <div class="side-menu__icon"> <i data-lucide="shopping-bag"></i> </div>
                            <div class="side-menu__title">
                                E-Commerce 
                                <div class="side-menu__sub-icon "> <i data-lucide="chevron-down"></i> </div>
                            </div>
                        </a>
                        <ul class="">
                            <li>
                                <a href="side-menu-light-categories.html" class="side-menu">
                                    <div class="side-menu__icon"> <i data-lucide="activity"></i> </div>
                                    <div class="side-menu__title"> Categories </div>
                                </a>
                            </li>
                            <li>
                                <a href="side-menu-light-add-product.html" class="side-menu">
                                    <div class="side-menu__icon"> <i data-lucide="activity"></i> </div>
                                    <div class="side-menu__title"> Add Product </div>
                                </a>
                            </li>
                            <li>
                                <a href="javascript:;" class="side-menu">
                                    <div class="side-menu__icon"> <i data-lucide="activity"></i> </div>
                                    <div class="side-menu__title">
                                        Products 
                                        <div class="side-menu__sub-icon "> <i data-lucide="chevron-down"></i> </div>
                                    </div>
                                </a>
                                <ul class="">
                                    <li>
                                        <a href="side-menu-light-product-list.html" class="side-menu">
                                            <div class="side-menu__icon"> <i data-lucide="zap"></i> </div>
                                            <div class="side-menu__title">Product List</div>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="side-menu-light-product-grid.html" class="side-menu">
                                            <div class="side-menu__icon"> <i data-lucide="zap"></i> </div>
                                            <div class="side-menu__title">Product Grid</div>
                                        </a>
                                    </li>
                                </ul>
                            </li>
                            <li>
                                <a href="javascript:;" class="side-menu">
                                    <div class="side-menu__icon"> <i data-lucide="activity"></i> </div>
                                    <div class="side-menu__title">
                                        Transactions 
                                        <div class="side-menu__sub-icon "> <i data-lucide="chevron-down"></i> </div>
                                    </div>
                                </a>
                                <ul class="">
                                    <li>
                                        <a href="side-menu-light-transaction-list.html" class="side-menu">
                                            <div class="side-menu__icon"> <i data-lucide="zap"></i> </div>
                                            <div class="side-menu__title">Transaction List</div>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="side-menu-light-transaction-detail.html" class="side-menu">
                                            <div class="side-menu__icon"> <i data-lucide="zap"></i> </div>
                                            <div class="side-menu__title">Transaction Detail</div>
                                        </a>
                                    </li>
                                </ul>
                            </li>
                            <li>
                                <a href="javascript:;" class="side-menu">
                                    <div class="side-menu__icon"> <i data-lucide="activity"></i> </div>
                                    <div class="side-menu__title">
                                        Sellers 
                                        <div class="side-menu__sub-icon "> <i data-lucide="chevron-down"></i> </div>
                                    </div>
                                </a>
                                <ul class="">
                                    <li>
                                        <a href="side-menu-light-seller-list.html" class="side-menu">
                                            <div class="side-menu__icon"> <i data-lucide="zap"></i> </div>
                                            <div class="side-menu__title">Seller List</div>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="side-menu-light-seller-detail.html" class="side-menu">
                                            <div class="side-menu__icon"> <i data-lucide="zap"></i> </div>
                                            <div class="side-menu__title">Seller Detail</div>
                                        </a>
                                    </li>
                                </ul>
                            </li>
                            <li>
                                <a href="side-menu-light-reviews.html" class="side-menu">
                                    <div class="side-menu__icon"> <i data-lucide="activity"></i> </div>
                                    <div class="side-menu__title"> Reviews </div>
                                </a>
                            </li>
                        </ul>
                    </li> -->
                    <!-- <li>
                        <a href="side-menu-light-inbox.html" class="side-menu">
                            <div class="side-menu__icon"> <i data-lucide="inbox"></i> </div>
                            <div class="side-menu__title"> Inbox </div>
                        </a>
                    </li>
                    <li>
                        <a href="side-menu-light-file-manager.html" class="side-menu">
                            <div class="side-menu__icon"> <i data-lucide="hard-drive"></i> </div>
                            <div class="side-menu__title"> File Manager </div>
                        </a>
                    </li>
                    <li>
                        <a href="side-menu-light-point-of-sale.html" class="side-menu">
                            <div class="side-menu__icon"> <i data-lucide="credit-card"></i> </div>
                            <div class="side-menu__title"> Point of Sale </div>
                        </a>
                    </li> -->
                    @hasPermission('Chat')
                    <li>
                        <a href="{{ route('chat.chat') }}" class="side-menu {{ request()->routeIs('chat.*') ? 'side-menu--active' : '' }}">
                            <div class="side-menu__icon"> <i data-lucide="message-square"></i> </div>
                            <div class="side-menu__title"> Chat </div>
                        </a>
                    </li>
                    @endhasPermission
                    @hasPermission('Announcement')
                    <li>
                        <a href="{{ route('announcement.announcement') }}" class="side-menu {{ request()->routeIs('announcement.*') ? 'side-menu--active' : '' }}">
                            <div class="side-menu__icon"> <i data-lucide="file-text"></i> </div>
                            <div class="side-menu__title"> Announcement </div>
                        </a>
                    </li>
                    @endhasPermission
                    @hasPermission('Calendar')
                    <li>
                        <a href="{{ route('calendar.calendar') }}" class="side-menu {{ request()->routeIs('calendar.*') ? 'side-menu--active' : '' }}">
                            <div class="side-menu__icon"> <i data-lucide="calendar"></i> </div>
                            <div class="side-menu__title"> Calendar </div>
                        </a>
                    </li>
                    @endhasPermission
                    <li class="side-nav__devider my-6"></li>
                    @hasPermission('Information')
                    <li>
                        <a href="javascript:;" class="side-menu {{ request()->routeIs('college.*') || request()->routeIs('program.*') || request()->routeIs('organization.*') || request()->routeIs('semester.*') ? 'side-menu--active side-menu--open' : '' }}">
                            <div class="side-menu__icon"> <i data-lucide="edit"></i> </div>
                            <div class="side-menu__title">
                                Information 
                                <div class="side-menu__sub-icon {{ request()->routeIs('college.*') || request()->routeIs('program.*') || request()->routeIs('organization.*') || request()->routeIs('semester.*') ? 'transform rotate-180' : '' }}"> <i data-lucide="chevron-down"></i> </div>
                            </div>
                        </a>
                        <ul class="{{ request()->routeIs('college.*') || request()->routeIs('program.*') || request()->routeIs('organization.*') || request()->routeIs('semester.*') ? 'side-menu__sub-open' : '' }}">
                            <li>
                                <a href="{{ route('college.add-college') }}" class="side-menu {{ request()->routeIs('college.*') ? 'side-menu--active' : '' }}">
                                    <div class="side-menu__icon"> <i data-lucide="activity"></i> </div>
                                    <div class="side-menu__title"> College </div>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('program.add-program') }}" class="side-menu {{ request()->routeIs('program.*') ? 'side-menu--active' : '' }}">
                                    <div class="side-menu__icon"> <i data-lucide="activity"></i> </div>
                                    <div class="side-menu__title"> Program </div>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('organization.add-organization') }}" class="side-menu {{ request()->routeIs('organization.*') ? 'side-menu--active' : '' }}">
                                    <div class="side-menu__icon"> <i data-lucide="activity"></i> </div>
                                    <div class="side-menu__title"> Organization </div>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('semester.add-semester') }}" class="side-menu {{ request()->routeIs('semester.*') ? 'side-menu--active' : '' }}">
                                    <div class="side-menu__icon"> <i data-lucide="activity"></i> </div>
                                    <div class="side-menu__title"> Semester & SY </div>
                                </a>
                            </li>
                        </ul>
                    </li>
                    @endhasPermission
                    @hasPermission('Events')
                    <li>
                        <a href="javascript:;" class="side-menu {{ request()->routeIs('events.*') ? 'side-menu--active side-menu--open' : '' }}">
                            <div class="side-menu__icon"> <i data-lucide="calendar"></i> </div>
                            <div class="side-menu__title">
                                Events 
                                <div class="side-menu__sub-icon {{ request()->routeIs('events.*') ? 'transform rotate-180' : '' }}"> <i data-lucide="chevron-down"></i> </div>
                            </div>
                        </a>
                        <ul class="{{ request()->routeIs('events.*') ? 'side-menu__sub-open' : '' }}">
                            <li>
                                <a href="{{ route('events.add-event') }}" class="side-menu {{ request()->routeIs('events.*') ? 'side-menu--active' : '' }}">
                                    <div class="side-menu__icon"> <i data-lucide="activity"></i> </div>
                                    <div class="side-menu__title"> Add Event </div>
                                </a>
                            </li>
                        </ul>
                    </li>
                    @endhasPermission
                    @hasPermission('User Management')
                    <li>
                        <a href="javascript:;" class="side-menu {{ request()->routeIs('students.*') ? 'side-menu--active side-menu--open' : '' }}">
                            <div class="side-menu__icon"> <i data-lucide="trello"></i> </div>
                            <div class="side-menu__title">
                                Users Management 
                                <div class="side-menu__sub-icon {{ request()->routeIs('students.*') ? 'transform rotate-180' : '' }}"> <i data-lucide="chevron-down"></i> </div>
                            </div>
                        </a>
                        <ul class="{{ request()->routeIs('students.*') ? 'side-menu__sub-open' : '' }}">
                            <li>
                                <a href="{{ route('students.add-students') }}" class="side-menu {{ request()->routeIs('students.*') ? 'side-menu--active' : '' }}">
                                    <div class="side-menu__icon"> <i data-lucide="activity"></i> </div>
                                    <div class="side-menu__title"> Students </div>
                                </a>
                            </li>
                        </ul>
                    </li>
                    @endhasPermission
                    @hasPermission('Scanner')
                    <li>
                        <a href="javascript:;" class="side-menu {{ request()->routeIs('scanner.*') ? 'side-menu--active side-menu--open' : '' }}">
                            <div class="side-menu__icon"> <i data-lucide="layout"></i> </div>
                            <div class="side-menu__title">
                                Scanner 
                                <div class="side-menu__sub-icon {{ request()->routeIs('scanner.*') ? 'transform rotate-180' : '' }}"> <i data-lucide="chevron-down"></i> </div>
                            </div>
                        </a>
                        <ul class="{{ request()->routeIs('scanner.*') ? 'side-menu__sub-open' : '' }}">
                            <li>
                                <a href="{{ route('scanner.scanner') }}" class="side-menu {{ request()->routeIs('scanner.*') ? 'side-menu--active' : '' }}">
                                    <div class="side-menu__icon"> <i data-lucide="activity"></i> </div>
                                    <div class="side-menu__title"> Scan Barcode </div>
                                </a>
                            </li>
                        </ul>
                    </li>
                    @endhasPermission
                    @hasPermission('Attendance Management')
                    <li>
                        <a href="javascript:;" class="side-menu {{ request()->routeIs('attendance.*') || request()->routeIs('myattendance.*') || request()->routeIs('listpaymentrequest.*') ? 'side-menu--active side-menu--open' : '' }}">
                            <div class="side-menu__icon"> <i data-lucide="layout"></i> </div>
                            <div class="side-menu__title">
                                Attendance Management
                                <div class="side-menu__sub-icon {{ request()->routeIs('attendance.*') || request()->routeIs('myattendance.*') || request()->routeIs('listpaymentrequest.*') ? 'transform rotate-180' : '' }}"> <i data-lucide="chevron-down"></i> </div>
                            </div>
                        </a>
                        <ul class="{{ request()->routeIs('attendance.*') || request()->routeIs('myattendance.*') || request()->routeIs('listpaymentrequest.*') ? 'side-menu__sub-open' : '' }}">
                            <li>
                                <a href="{{ route('attendance.attendance') }}" class="side-menu {{ request()->routeIs('attendance.*') ? 'side-menu--active' : '' }}">
                                    <div class="side-menu__icon"> <i data-lucide="activity"></i> </div>
                                    <div class="side-menu__title"> Attendance </div>
                                </a>
                            </li>
                            @if(auth(guard: 'students')->check())
                            <li>
                                <a href="{{ route('myattendance.myAttendance') }}" class="side-menu {{ request()->routeIs('myattendance.*') ? 'side-menu--active' : '' }}">
                                    <div class="side-menu__icon"> <i data-lucide="activity"></i> </div>
                                    <div class="side-menu__title"> My Attendance </div>
                                </a>
                            </li>
                            @endif
                            <li>
                                <a href="{{ route('listpaymentrequest.list') }}" class="side-menu {{ request()->routeIs('listpaymentrequest.*') ? 'side-menu--active' : '' }}">
                                    <div class="side-menu__icon"> <i data-lucide="activity"></i> </div>
                                    <div class="side-menu__title"> List Payment Request </div>
                                </a>
                            </li>
                            
                        </ul>
                    </li>
                    @endhasPermission
                    <li class="side-nav__devider my-6"></li>
                    @hasPermission('Settings')
                    <li>
                        <a href="javascript:;" class="side-menu {{ request()->routeIs('systemsettings.*') || request()->routeIs('permission.*') ? 'side-menu--active side-menu--open' : '' }}">
                            <div class="side-menu__icon"> <i data-lucide="inbox"></i> </div>
                            <div class="side-menu__title">
                                Settings 
                                <div class="side-menu__sub-icon {{ request()->routeIs('systemsettings.*') || request()->routeIs('permission.*') ? 'transform rotate-180' : '' }}"> <i data-lucide="chevron-down"></i> </div>
                            </div>
                        </a>
                        <ul class="{{ request()->routeIs('systemsettings.*') || request()->routeIs('permission.*') ? 'side-menu__sub-open' : '' }}">
                            <li>
                                <a href="{{ route('systemsettings.add-systemsettings') }}" class="side-menu {{ request()->routeIs('systemsettings.*') ? 'side-menu--active' : '' }}">
                                    <div class="side-menu__icon"> <i data-lucide="activity"></i> </div>
                                    <div class="side-menu__title">
                                        System Settings </div>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('permission.permission') }}" class="side-menu {{ request()->routeIs('permission.*') ? 'side-menu--active' : '' }}">
                                    <div class="side-menu__icon"> <i data-lucide="activity"></i> </div>
                                    <div class="side-menu__title"> Permission Settings</div>
                                </a>
                            </li>
                            
                        </ul>
                    </li>
                    @endif
                    
                   
                </ul>
            </nav>
@extends('layouts.master')

@section('subcontent')
    <div class="intro-y col-span-12 flex flex-wrap sm:flex-nowrap items-center mt-2 col-gap-6">
                    <h2 class="text-lg font-medium mr-auto">
                        Profile
                    </h2>
                </div>
                <!-- BEGIN: Profile Info -->
                <div class="intro-y box px-5 pt-5 mt-5 col-span-12">
                    @php
                        $userName = $currentUser ? ($userType === 'student' ? $currentUser->student_name : $currentUser->name) : 'Guest';
                        $userRole = $userType === 'student' 
                            ? ($currentUser && $currentUser->college ? $currentUser->college->college_name : 'Student')
                            : ($currentUser ? 'Admin' : 'Guest');
                        $photoUrl = asset('dist/images/profile-5.jpg');
                        $defaultPhoto = asset('dist/images/profile-5.jpg');
                        
                        // Set photo URL based on user type
                        if ($currentUser && $currentUser->photo) {
                            // Remove 'storage/' prefix if exists to avoid duplication
                            $photoPath = str_replace('storage/', '', $currentUser->photo);
                            $photoUrl = asset('storage/' . $photoPath);
                        }
                        
                        // Contact info
                        $emailOrId = $currentUser ? ($userType === 'student' ? $currentUser->id_number : $currentUser->email) : '';
                        $program = $currentUser && $userType === 'student' && $currentUser->program ? $currentUser->program->program_name : '';
                        $organization = $currentUser && $userType === 'student' && $currentUser->organization ? $currentUser->organization->organization_name : '';
                        $address = $currentUser && $userType === 'student' ? ($currentUser->address ?? '') : '';
                        $yearLevel = $currentUser && $userType === 'student' ? ($currentUser->year_level ?? '') : '';
                    @endphp
                    <div class="flex flex-col lg:flex-row border-b border-slate-200/60 dark:border-darkmode-400 pb-5 -mx-5">
                        <div class="flex flex-1 px-5 items-center justify-center lg:justify-start">
                            <div class="w-20 h-20 sm:w-24 sm:h-24 flex-none lg:w-32 lg:h-32 image-fit relative">
                                <img alt="{{ $userName }}" id="profile-photo-display" class="rounded-full" src="{{ $photoUrl }}" onerror="this.src='{{ $defaultPhoto }}'">
                                <input type="file" id="profile-photo-input" accept="image/*" style="display: none;">
                                <div class="absolute mb-1 mr-1 flex items-center justify-center bottom-0 right-0 bg-primary rounded-full p-2 cursor-pointer" id="camera-icon-btn" title="Change Photo">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" icon-name="camera" class="lucide lucide-camera w-4 h-4 text-white" data-lucide="camera">
                                        <path d="M14.5 4h-5L7 7H4a2 2 0 00-2 2v9a2 2 0 002 2h16a2 2 0 002-2V9a2 2 0 00-2-2h-3l-2.5-3z"></path>
                                        <circle cx="12" cy="13" r="3"></circle>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-5">
                                <div class="w-24 sm:w-40 truncate sm:whitespace-normal font-medium text-lg">{{ $userName }}</div>
                                <div class="text-slate-500">{{ $userRole }}</div>
                                @if($userType === 'student' && $program)
                                <div class="text-slate-400 text-sm mt-1">{{ $program }}</div>
                                @endif
                            </div>
                        </div>
                        <div class="mt-6 lg:mt-0 flex-1 px-5 border-l border-r border-slate-200/60 dark:border-darkmode-400 border-t lg:border-t-0 pt-5 lg:pt-0">
                            <div class="font-medium text-center lg:text-left lg:mt-3">Contact Details</div>
                            <div class="flex flex-col justify-center items-center lg:items-start mt-4">
                                @if($emailOrId)
                                <div class="truncate sm:whitespace-normal flex items-center"> 
                                    <i data-lucide="{{ $userType === 'student' ? 'hash' : 'mail' }}" class="w-4 h-4 mr-2"></i> 
                                    {{ $userType === 'student' ? 'ID: ' . $emailOrId : $emailOrId }} 
                                </div>
                                @endif
                                @if($userType === 'student' && $address)
                                <div class="truncate sm:whitespace-normal flex items-center mt-3"> 
                                    <i data-lucide="map-pin" class="w-4 h-4 mr-2"></i> 
                                    {{ $address }} 
                                </div>
                                @endif
                                @if($userType === 'student' && $organization)
                                <div class="truncate sm:whitespace-normal flex items-center mt-3"> 
                                    <i data-lucide="users" class="w-4 h-4 mr-2"></i> 
                                    {{ $organization }} 
                                </div>
                                @endif
                                @if($userType === 'student' && $yearLevel)
                                <div class="truncate sm:whitespace-normal flex items-center mt-3"> 
                                    <i data-lucide="calendar" class="w-4 h-4 mr-2"></i> 
                                    Year Level: {{ $yearLevel }} 
                                </div>
                                @endif
                            </div>
                        </div>
                        <div class="mt-6 lg:mt-0 flex-1 px-5 border-t lg:border-0 border-slate-200/60 dark:border-darkmode-400 pt-5 lg:pt-0">
                            @if($userType === 'student')
                            <div class="font-medium text-center lg:text-left lg:mt-5">Student Information</div>
                            <div class="flex flex-col justify-center items-center lg:items-start mt-4">
                                @if($currentUser && $currentUser->college)
                                <div class="text-center lg:text-left"> 
                                    <div class="text-xs text-slate-500">College</div>
                                    <div class="font-medium">{{ $currentUser->college->college_name }}</div>
                                </div>
                                @endif
                                @if($program)
                                <div class="text-center lg:text-left mt-3"> 
                                    <div class="text-xs text-slate-500">Program</div>
                                    <div class="font-medium">{{ $program }}</div>
                                </div>
                                @endif
                                @if($organization)
                                <div class="text-center lg:text-left mt-3"> 
                                    <div class="text-xs text-slate-500">Organization</div>
                                    <div class="font-medium">{{ $organization }}</div>
                                </div>
                                @endif
                            </div>
                            @else
                            <div class="font-medium text-center lg:text-left lg:mt-5">Account Information</div>
                            <div class="flex flex-col justify-center items-center lg:items-start mt-4">
                                <div class="text-center lg:text-left"> 
                                    <div class="text-xs text-slate-500">Role</div>
                                    <div class="font-medium">Administrator</div>
                                </div>
                                @if($emailOrId)
                                <div class="text-center lg:text-left mt-3"> 
                                    <div class="text-xs text-slate-500">Email</div>
                                    <div class="font-medium">{{ $emailOrId }}</div>
                                </div>
                                @endif
                            </div>
                            @endif
                        </div>
                    </div>
                    <ul class="nav nav-link-tabs flex-col sm:flex-row justify-center lg:justify-start text-center" role="tablist" >
                        <li id="account-and-profile-tab" class="nav-item" role="presentation"> <a href="javascript:;" class="nav-link py-4" data-tw-target="#account-and-profile" aria-selected="false" role="tab" > Account & Profile </a> </li>
                    </ul>
                </div>
                <!-- END: Profile Info -->
                <div class="intro-y tab-content mt-5 col-span-12">
                    <div id="dashboard" class="tab-pane active" role="tabpanel" aria-labelledby="dashboard-tab">
                        <div class="grid grid-cols-12 gap-6">
                          
                            <!-- BEGIN: Update Profile -->
                            <div class="intro-y box col-span-12 lg:col-span-6">
                                <div class="flex items-center px-5 py-5 sm:py-3 border-b border-slate-200/60 dark:border-darkmode-400">
                                    <h2 class="font-medium text-base mr-auto">
                                        Update Profile
                                    </h2>
                                </div>
                                <div class="p-5">
                                    <form id="update-profile-form">
                                        @if($userType === 'student')
                                        <div class="mb-4">
                                            <label class="form-label">Student Name <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="profile-student-name" name="student_name" value="{{ $currentUser ? $currentUser->student_name : '' }}" required>
                                        </div>
                                        <div class="mb-4">
                                            <label class="form-label">Address</label>
                                            <textarea class="form-control" id="profile-address" name="address" rows="3">{{ $currentUser ? ($currentUser->address ?? '') : '' }}</textarea>
                                        </div>
                                        <div class="mb-4">
                                            <label class="form-label">Year Level</label>
                                            <input type="text" class="form-control" id="profile-year-level" name="year_level" value="{{ $currentUser ? ($currentUser->year_level ?? '') : '' }}">
                                        </div>
                                        @else
                                        <div class="mb-4">
                                            <label class="form-label">Name <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="profile-name" name="name" value="{{ $currentUser ? $currentUser->name : '' }}" required>
                                        </div>
                                        <div class="mb-4">
                                            <label class="form-label">Email <span class="text-danger">*</span></label>
                                            <input type="email" class="form-control" id="profile-email" name="email" value="{{ $currentUser ? $currentUser->email : '' }}" required>
                                        </div>
                                        <div class="mb-4">
                                            <label class="form-label">Gender <span class="text-danger">*</span></label>
                                            <select class="form-control" id="profile-gender" name="gender" required>
                                                <option value="">Select Gender</option>
                                                <option value="male" {{ $currentUser && $currentUser->gender === 'male' ? 'selected' : '' }}>Male</option>
                                                <option value="female" {{ $currentUser && $currentUser->gender === 'female' ? 'selected' : '' }}>Female</option>
                                                <option value="other" {{ $currentUser && $currentUser->gender === 'other' ? 'selected' : '' }}>Other</option>
                                            </select>
                                        </div>
                                        @endif
                                        <div class="mt-5">
                                            <button type="submit" class="btn btn-primary w-full sm:w-auto" id="update-profile-btn">
                                                <i data-lucide="save" class="w-4 h-4 mr-2"></i> Update Profile
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            <!-- END: Update Profile -->
                            <!-- BEGIN: Change Password -->
                            <div class="intro-y box col-span-12 lg:col-span-6">
                            <div class="flex items-center px-5 py-5 sm:py-3 border-b border-slate-200/60 dark:border-darkmode-400">
                                    <h2 class="font-medium text-base mr-auto">
                                        Change Password
                                    </h2>
                                </div>
                                <div class="p-5">
                                    <form id="change-password-form">
                                        <div class="mb-4">
                                            <label class="form-label">Current Password <span class="text-danger">*</span></label>
                                            <input type="password" class="form-control" id="current-password" name="current_password" required>
                                        </div>
                                        <div class="mb-4">
                                            <label class="form-label">New Password <span class="text-danger">*</span></label>
                                            <input type="password" class="form-control" id="new-password" name="new_password" required minlength="6">
                                            <div class="text-xs text-slate-500 mt-1">Minimum 6 characters</div>
                                        </div>
                                        <div class="mb-4">
                                            <label class="form-label">Confirm New Password <span class="text-danger">*</span></label>
                                            <input type="password" class="form-control" id="confirm-password" name="confirm_password" required minlength="6">
                                        </div>
                                        <div class="mt-5">
                                            <button type="submit" class="btn btn-primary w-full sm:w-auto" id="change-password-btn">
                                                <i data-lucide="key" class="w-4 h-4 mr-2"></i> Change Password
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            <!-- END: Change Password -->
                        </div>
                    </div>
                </div>
            </div>
            <!-- END: Content -->
        </div>

        <!-- BEGIN: Photo Upload Confirmation Modal -->
        <div id="photo-upload-modal" class="modal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-md">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="font-medium text-base mr-auto">Confirm Photo Update</h2>
                        <a href="javascript:;" data-tw-dismiss="modal">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-x w-8 h-8 text-slate-400" data-lucide="x">
                                <path d="M18 6L6 18"></path>
                                <path d="M6 6l12 12"></path>
                            </svg>
                        </a>
                    </div>
                    <div class="modal-body p-6">
                        <div class="text-center">
                            <div class="mb-4">
                                <div class="w-32 h-32 mx-auto image-fit relative">
                                    <img id="photo-preview" class="rounded-full border-2 border-slate-200" src="" alt="Preview" style="object-fit: cover;">
                                </div>
                            </div>
                            <p class="text-slate-600">Are you sure you want to update your profile photo?</p>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" data-tw-dismiss="modal" class="btn btn-secondary w-20 mr-2">Cancel</button>
                        <button type="button" id="confirm-photo-upload-btn" class="btn btn-primary w-32">
                            <i data-lucide="check" class="w-4 h-4 mr-2"></i> Update Photo
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <!-- END: Photo Upload Confirmation Modal -->
    @endsection
    @push('scripts')
    <script src="{{ asset('js/profile/profile.js') }}?v={{ time() }}"></script>
    @endpush
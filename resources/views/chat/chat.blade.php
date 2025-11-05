@extends('layouts.master')

@section('subcontent')

<div class="col-span-12 lg:col-span-4 2xl:col-span-3">
    <h2 class="text-lg font-medium mr-auto">
        Chat
    </h2>
    <div class="intro-y pr-1">
        <div class="box p-2">
            <ul class="nav nav-pills" role="tablist">
                <li id="chats-tab" class="nav-item flex-1" role="presentation">
                    <button class="nav-link w-full py-2 active" data-tw-toggle="pill" data-tw-target="#chats" type="button" role="tab" aria-controls="chats" aria-selected="true"> Chats </button>
                </li>

            </ul>
        </div>
    </div>
    <div class="tab-content">
        <div id="chats" class="tab-pane active" role="tabpanel" aria-labelledby="chats-tab">
                <div class="pr-1">
                    <div class="box px-5 pt-5 pb-5 lg:pb-0 mt-5">
                        <div class="relative text-slate-500">
                            <input type="text" id="chat-search-users" class="form-control py-3 px-4 border-transparent bg-slate-100 pr-10" placeholder="Search for messages or users...">
                            <i class="w-4 h-4 hidden sm:absolute my-auto inset-y-0 mr-3 right-0" data-lucide="search"></i>
                        </div>
                        <div class="overflow-x-auto scrollbar-hidden chat__users-list">
                            <div class="flex mt-5">
                                @if(isset($allUsers) && count($allUsers) > 0)
                                    @foreach($allUsers as $user)
                                        <a href="javascript:;" class="chat__user-item w-10 mr-4 cursor-pointer" data-user-id="{{ $user['id'] }}" data-user-type="{{ $user['type'] }}">
                                            <div class="w-10 h-10 flex-none image-fit rounded-full">
                                                <img alt="{{ $user['name'] }}" class="rounded-full" src="{{ $user['photo'] }}">
                                                <div class="w-3 h-3 bg-success absolute right-0 bottom-0 rounded-full border-2 border-white dark:border-darkmode-600"></div>
                                            </div>
                                            <div class="text-xs text-slate-500 truncate text-center mt-2">{{ $user['name'] }}</div>
                                        </a>
                                    @endforeach
                                @else
                                    <div class="text-center text-slate-500 w-full py-5">No users available</div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            <div class="chat__chat-list overflow-y-auto scrollbar-hidden pr-1 pt-1 mt-4" style="max-height: 600px;">
                <!-- Conversations will be loaded dynamically via JavaScript -->
                <div class="loading-conversations text-center text-slate-500 p-5">
                    <i data-lucide="message-circle" class="w-8 h-8 mx-auto mb-2"></i>
                    <p>Loading conversations...</p>
                </div>
            </div>
        </div>
        
    </div>
</div>
<div class="intro-y col-span-12 lg:col-span-8 2xl:col-span-9">
    <div class="chat__box box" style="height: 782px; display: flex; flex-direction: column;">
        <!-- BEGIN: Chat Active -->
        <div class="chat__active hidden h-full flex flex-col" style="height: 100%; max-height: 782px;">
            <div class="flex flex-col sm:flex-row border-b border-slate-200/60 dark:border-darkmode-400 px-5 py-4 flex-none">
                <div class="flex items-center">
                    <div class="w-10 h-10 sm:w-12 sm:h-12 flex-none image-fit relative chat__header-avatar">
                        <img alt="Avatar" class="rounded-full" src="{{ asset('dist/images/profile-5.jpg') }}" onerror="this.src='{{ asset('dist/images/profile-5.jpg') }}';">
                    </div>
                    <div class="ml-3 mr-auto">
                        <div class="font-medium text-base chat__header-name">User</div>
                        <div class="text-slate-500 text-xs sm:text-sm">Online <span class="mx-1">•</span> <span class="chat__header-status">Active</span></div>
                    </div>
                </div>
               
            </div>
            <div class="overflow-y-scroll scrollbar-hidden px-5 pt-5 flex-1" style="min-height: 0; max-height: calc(782px - 200px);">
                <div class="chat__messages-container">
                    <!-- Messages will be loaded dynamically via JavaScript -->
                </div>
            </div>
            <div class="pt-4 pb-10 sm:py-4 flex items-center border-t border-slate-200/60 dark:border-darkmode-400 flex-none">
                <textarea class="chat__box__input form-control dark:bg-darkmode-600 h-16 resize-none border-transparent px-5 py-3 shadow-none focus:border-transparent focus:ring-0" rows="1" placeholder="Type your message..."></textarea>
               
                <a href="javascript:;" class="chat__send-button w-8 h-8 sm:w-10 sm:h-10 block bg-primary text-white rounded-full flex-none flex items-center justify-center mr-5 cursor-pointer"> <i data-lucide="send" class="w-4 h-4"></i> </a>
            </div>
        </div>
        <!-- END: Chat Active -->
        <!-- BEGIN: Chat Default -->
        <div class="chat__default h-full flex items-center mt-12 text-center" style="height: 782px; max-height: 782px;">
            <div class="mx-auto text-center text-center">
                <div class="w-16 h-16 flex-none image-fit rounded-full overflow-hidden mx-auto">
                    <img alt="Chat" src="{{ asset('dist/images/profile-5.jpg') }}" onerror="this.src='{{ asset('dist/images/profile-5.jpg') }}';">
                </div>
                <div class="mt-3">
                    <div class="font-medium">Hey, {{ $currentUser['name'] ?? 'User' }}!</div>
                    <div class="text-slate-500 mt-1">Please select a chat to start messaging.</div>
                </div>
            </div>
        </div>
        <!-- END: Chat Default -->
    </div>
</div>
<!-- END: Chat Content -->
</div>
</div>
@endsection
@push('scripts')
<!-- Load jQuery for chat functionality -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<style>
/* Ensure float-left and float-right work for chat messages */
.chat__messages-container {
    width: 100%;
    overflow: hidden;
}
.chat__box__text-box {
    clear: both;
    width: auto;
    max-width: 70%;
}
.chat__box__text-box.float-left {
    float: left !important;
    clear: both !important;
    justify-content: flex-start;
}
.chat__box__text-box.float-right {
    float: right !important;
    clear: both !important;
    justify-content: flex-end;
}
.clear-both {
    clear: both !important;
    height: 0;
    overflow: hidden;
    width: 100%;
}
/* Force float to work even with flex */
.chat__messages-container > .chat__box__text-box.float-left {
    float: left !important;
    width: auto !important;
}
.chat__messages-container > .chat__box__text-box.float-right {
    float: right !important;
    width: auto !important;
}
</style>
<script>
    // Set current user ID and type for JavaScript
    window.currentUserId = {{ $currentUser['id'] ?? 0 }};
    window.currentUserType = '{{ $currentUser['type'] ?? 'user' }}';
    window.debugChat = true; // Enable debug logging
    
    console.log('Current user ID set:', window.currentUserId);
    console.log('Current user type set:', window.currentUserType);
    
    // Ensure jQuery is available globally
    if (typeof window.jQuery === 'undefined') {
        window.jQuery = jQuery;
        window.$ = jQuery;
    }
</script>
<script src="{{ asset('js/chat/chat.js') }}?v={{ time() }}"></script>
@endpush

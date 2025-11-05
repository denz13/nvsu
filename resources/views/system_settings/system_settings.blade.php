@extends('layouts.master')

@section('subcontent')
    <div class="intro-y col-span-12 flex flex-wrap sm:flex-nowrap items-center mt-12">
        <h2 class="text-lg font-medium mr-auto">System Settings</h2>
        
    </div>
    <div class="intro-y col-span-12 flex flex-wrap sm:flex-nowrap items-center mt-2">
        <div class="w-full sm:w-auto mt-3 sm:mt-0 sm:ml-auto md:ml-0">
            <div class="w-56 relative text-slate-500">
                <form id="ss-search-form" method="GET" action="{{ url()->current() }}">
                    <input type="text" name="q" id="ss-search-input" class="form-control w-56 box pr-10" placeholder="Search settings..." value="{{ request('q') }}">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" icon-name="search" class="lucide lucide-search w-4 h-4 absolute my-auto inset-y-0 mr-3 right-0" data-lucide="search"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg> 
                </form>
            </div>
        </div>
    </div>
    <!-- BEGIN: Data List -->
    <div class="intro-y col-span-12 overflow-auto lg:overflow-visible">
        <table class="table table-report -mt-2">
            <thead>
                <tr>
                    <th class="whitespace-nowrap">KEY</th>
                    <th class="whitespace-nowrap">TYPE</th>
                    <th class="whitespace-nowrap">DESCRIPTION</th>
                    <th class="text-center whitespace-nowrap">STATUS</th>
                    <th class="text-center whitespace-nowrap">ACTIONS</th>
                </tr>
            </thead>
            <tbody id="ss-table-body">
                @forelse($systemSettings as $setting)
                <tr class="intro-x ss-row">
                    <td class="whitespace-nowrap ss-key" data-value="{{ strtolower($setting->key) }}">
                        <a href="javascript:;" class="font-medium whitespace-nowrap">{{ $setting->key }}</a>
                    </td>
                    <td class="whitespace-nowrap ss-type" data-value="{{ strtolower($setting->type) }}">{{ $setting->type }}</td>
                    <td class="whitespace-nowrap ss-description" data-value="{{ strtolower($setting->description) }}">
                        @if(strtolower($setting->type) === 'image' && $setting->description)
                            @php
                                $src = preg_match('/^(https?:\/\/|\/)/', $setting->description) ? $setting->description : asset($setting->description);
                            @endphp
                            <img src="/{{ ltrim($setting->description,'/') }}" onerror="this.onerror=null;this.src='{{ $src }}'" alt="{{ $setting->key }}" class="h-10 w-auto rounded" />
                        @else
                            {{ $setting->description }}
                        @endif
                    </td>
                    <td class="w-40">
                        @php $isActive = strtolower($setting->status) === 'active'; @endphp
                        <div class="flex items-center justify-center {{ $isActive ? 'text-success' : 'text-danger' }}">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" icon-name="check-square" data-lucide="check-square" class="lucide lucide-check-square w-4 h-4 mr-2"><polyline points="9 11 12 14 22 4"></polyline><path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"></path></svg>
                            {{ $isActive ? 'Active' : 'Inactive' }}
                        </div>
                    </td>
                    <td class="table-report__action w-56">
                        <div class="flex justify-center items-center">
                            <a class="flex items-center ss-update" href="javascript:;" data-tw-toggle="modal" data-tw-target="#ss-update-modal"
                               data-id="{{ $setting->id }}"
                               data-key="{{ $setting->key }}"
                               data-type="{{ strtolower($setting->type) }}"
                               data-description="{{ $setting->description }}">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-edit w-4 h-4 mr-1"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4Z"/></svg>
                                Update
                            </a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center py-8 text-slate-500">No system settings found.</td>
                </tr>
                @endforelse
                
            </tbody>
        </table>
    </div>
    <!-- END: Data List -->

    <!-- Update Modal -->
<div id="ss-update-modal" class="modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="font-medium text-base mr-auto">Update Setting</h2>
                <a href="javascript:;" data-tw-dismiss="modal">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-x w-8 h-8 text-slate-400" data-lucide="x"><path d="M18 6L6 18"></path><path d="M6 6l12 12"></path></svg>
                </a>
            </div>
            <div class="modal-body p-6">
                <form id="ss-update-form">
                    <input type="hidden" id="ss-id" name="id">
                    <input type="hidden" id="ss-type" name="type">
                    <div class="mb-4">
                        <label class="form-label">Key</label>
                        <input type="text" id="ss-key" class="form-control" disabled>
                    </div>
                    <div id="ss-dynamic-field"></div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary w-20 mr-1" data-tw-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary w-24" id="ss-save-btn">Save</button>
            </div>
        </div>
    </div>
    </div>
    
</div>

<!-- BEGIN: Toast Component -->
@include('components.toast')
<!-- END: Toast Component -->

@endsection

@push('scripts')

<script src="{{ asset('js/system_settings/system_settings.js') }}?v={{ time() }}"></script>
@endpush

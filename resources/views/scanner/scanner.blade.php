@extends('layouts.master')

@section('subcontent')
<div class="col-span-12 lg:col-span-3 2xl:col-span-2">
    <h2 class="intro-y text-lg font-medium mr-auto mt-10">
        Scanner
    </h2>
    <!-- BEGIN: Scanner Menu -->
    <div class="intro-y box bg-primary p-5 mt-10">
        <div class="text-white mb-4">
            <label for="deviceScanner" class="form-label text-white">Scan Barcode</label>
            <input 
                id="deviceScanner" 
                type="text" 
                class="form-control w-full mt-2 text-slate-800" 
                placeholder="Scan or type barcode..."
                autofocus
                autocomplete="off"
            >
        </div>
        <div class="grid grid-cols-2 gap-2 mt-3">
            <button 
                id="clearScanner" 
                type="button" 
                class="btn text-slate-600 dark:text-slate-300 bg-white dark:bg-darkmode-300 dark:border-darkmode-300"
            > 
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-refresh-cw w-4 h-4 mr-2" data-lucide="refresh-cw">
                    <path d="M3 12a9 9 0 0 1 9-9 9.75 9.75 0 0 1 6.74 2.74L21 8"></path>
                    <path d="M21 3v5h-5"></path>
                    <path d="M21 12a9 9 0 0 1-9 9 9.75 9.75 0 0 1-6.74-2.74L3 16"></path>
                    <path d="M8 16H3v5"></path>
                </svg> 
                Clear 
            </button>
        </div>
        <div class="border-t border-white/10 dark:border-darkmode-400 mt-6 pt-6 text-white">
            <div class="mb-4">
                <label for="eventSelect" class="form-label text-white text-sm font-medium">Select Event</label>
                <select id="eventSelect" class="form-control w-full mt-2 text-slate-800">
                    @if($events && count($events) > 0)
                        @foreach($events as $event)
                            <option value="{{ $event->id }}" {{ $loop->first ? 'selected' : '' }}>
                                {{ $event->event_name }}
                            </option>
                        @endforeach
                    @else
                        <option value="">No active events</option>
                    @endif
                </select>
            </div>
            <div class="mt-4 p-3 bg-white/5 rounded text-xs text-white/80">
                <p class="font-medium mb-1">Barcode Format:</p>
                <p>ID Number + Name (5 chars)</p>
                <p class="mt-2 text-white/60">Example: 2024-001JOHN</p>
                <p class="mt-1 text-white/60 text-xxs">(Short format for easy scanning)</p>
            </div>
        </div>
    </div>
    <!-- END: Scanner Menu -->
</div>
<div class="col-span-12 lg:col-span-9 2xl:col-span-10">
    <!-- BEGIN: Scanner Header -->
    <div class="intro-y flex flex-col sm:flex-row items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">Student Scanner</h2>
        <div class="w-full sm:w-auto flex mt-4 sm:mt-0">
            <button class="btn btn-primary shadow-md mr-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-scan-line w-4 h-4 mr-2" data-lucide="scan-line">
                    <path d="M3 7V5a2 2 0 0 1 2-2h2"></path>
                    <path d="M17 3h2a2 2 0 0 1 2 2v2"></path>
                    <path d="M21 17v2a2 2 0 0 1-2 2h-2"></path>
                    <path d="M7 21H5a2 2 0 0 1-2-2v-2"></path>
                    <path d="M7 12h10"></path>
                </svg>
                Scan Now
            </button>
        </div>
    </div>
    <!-- END: Scanner Header -->
    
    <!-- BEGIN: Search Results -->
    <div class="intro-y grid grid-cols-12 gap-6 mt-5" id="search-results">
        <div class="col-span-12 text-center py-12 text-slate-500">
            <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-scan-barcode mx-auto mb-4 text-slate-300">
                <path d="M3 7V5a2 2 0 0 1 2-2h2"></path>
                <path d="M17 3h2a2 2 0 0 1 2 2v2"></path>
                <path d="M21 17v2a2 2 0 0 1-2 2h-2"></path>
                <path d="M7 21H5a2 2 0 0 1-2-2v-2"></path>
                <path d="M7 8h8"></path>
                <path d="M7 12h10"></path>
                <path d="M7 16h6"></path>
            </svg>
            <h5 class="text-lg font-medium mb-2">Ready to Scan</h5>
            <p>Scan a student barcode or enter it manually to view information</p>
        </div>
    </div>
    <!-- END: Search Results -->
</div>

<!-- BEGIN: Attendance Selection Modal -->
<div id="attendanceSelectionModal" class="modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="font-medium text-base mr-auto">Select Attendance Type</h2>
                <a href="javascript:;" data-tw-dismiss="modal">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-x w-8 h-8 text-slate-400">
                        <path d="M18 6L6 18"></path>
                        <path d="M6 6l12 12"></path>
                    </svg>
                </a>
            </div>
            <div class="modal-body p-6">
                <div id="attendanceModalContent">
                    <!-- Student info and selection -->
                    <div class="flex items-center mb-6 pb-6 border-b border-slate-200">
                        <div class="w-20 h-20 rounded-full overflow-hidden mr-4">
                            <img id="modal-student-photo" src="/dist/images/preview-7.jpg" alt="Student Photo" class="w-full h-full object-cover">
                        </div>
                        <div>
                            <h3 id="modal-student-name" class="font-medium text-lg">Student Name</h3>
                            <p id="modal-student-id" class="text-slate-500 text-sm">ID: </p>
                            <p id="modal-student-college" class="text-slate-400 text-xs mt-1">College</p>
                        </div>
                    </div>
                    
                    <!-- Existing attendance records -->
                    <div id="existing-attendance-info" class="mb-6">
                        <!-- Will be populated dynamically -->
                    </div>
                    
                    <!-- Late penalty info -->
                    <div id="late-penalty-info" class="mb-6 p-4 bg-yellow-50 border border-yellow-200 rounded hidden">
                        <p class="text-sm text-yellow-800 font-medium">⚠️ Late Penalty Warning</p>
                        <p class="text-xs text-yellow-700 mt-1">A penalty fee will be charged if you proceed.</p>
                    </div>
                    
                    <!-- Selection buttons -->
                    <div class="grid grid-cols-2 gap-4 mt-6">
                        <button type="button" id="btn-time-in" class="btn btn-lg btn-primary py-4">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-6 h-6 mx-auto mb-2">
                                <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"></path>
                                <circle cx="12" cy="7" r="4"></circle>
                            </svg>
                            <div class="font-medium text-base">Time In</div>
                            <div class="text-xs opacity-70 mt-1">Record arrival</div>
                        </button>
                        <button type="button" id="btn-time-out" class="btn btn-lg btn-success py-4">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-6 h-6 mx-auto mb-2">
                                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                                <polyline points="16 17 21 12 16 7"></polyline>
                                <line x1="21" y1="12" x2="9" y2="12"></line>
                            </svg>
                            <div class="font-medium text-base">Time Out</div>
                            <div class="text-xs opacity-70 mt-1">Record departure</div>
                        </button>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" data-tw-dismiss="modal" class="btn btn-secondary w-20 mr-1">Cancel</button>
            </div>
        </div>
    </div>
</div>
<!-- END: Attendance Selection Modal -->

<!-- BEGIN: Student Details Modal -->
<div id="studentDetailsModal" class="modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="font-medium text-base mr-auto">Student Details</h2>
                <a href="javascript:;" data-tw-dismiss="modal">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-x w-8 h-8 text-slate-400" data-lucide="x">
                        <path d="M18 6L6 18"></path>
                        <path d="M6 6l12 12"></path>
                    </svg>
                </a>
            </div>
            <div class="modal-body p-6">
                <div id="studentModalContent">
                    <!-- Content will be loaded dynamically via JavaScript -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" data-tw-dismiss="modal" class="btn btn-secondary w-20 mr-1">Close</button>
            </div>
        </div>
    </div>
</div>
<!-- END: Student Details Modal -->

<!-- BEGIN: Toast Component -->
@include('components.toast')
<!-- END: Toast Component -->

@endsection

@push('scripts')
<script src="{{ asset('js/scanner/scanner.js') }}?v={{ time() }}"></script>
@endpush

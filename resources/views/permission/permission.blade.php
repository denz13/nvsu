@extends('layouts.master')

@section('subcontent')
<h2 class="intro-y text-lg font-medium mt-10">
    Permission Settings
</h2>
<div class="intro-y col-span-12 flex flex-wrap sm:flex-nowrap items-center mt-2">
    <button class="btn btn-primary shadow-md mr-2" data-tw-toggle="modal" data-tw-target="#add-permission-modal">Add New Permission</button>

    <div class="hidden md:block mx-auto text-slate-500">Showing {{ count($permissionSettings) }} entries</div>
    <div class="w-full sm:w-auto mt-3 sm:mt-0 sm:ml-auto md:ml-0">
        <div class="w-56 relative text-slate-500">
            <input type="text" id="search-permission" class="form-control w-56 box pr-10" placeholder="Search...">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" icon-name="search" class="lucide lucide-search w-4 h-4 absolute my-auto inset-y-0 mr-3 right-0" data-lucide="search">
                <circle cx="11" cy="11" r="8"></circle>
                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
            </svg>
        </div>
    </div>
</div>
<!-- BEGIN: HTML Table Data -->
<div class="intro-y col-span-12 overflow-auto lg:overflow-visible">
    <table class="table table-report -mt-2" style="width: 100%;">
        <thead>
            <tr>
                <th class="whitespace-nowrap" style="width: 20%;">USER/STUDENT</th>
                <th class="text-center whitespace-nowrap" style="width: 15%;">TYPE</th>
                <th class="text-center whitespace-nowrap" style="width: 30%;">MODULES</th>
                <th class="text-center whitespace-nowrap" style="width: 15%;">STATUS</th>
                <th class="text-center whitespace-nowrap" style="width: 20%;">ACTIONS</th>
            </tr>
        </thead>
        <tbody id="permission-table-body">
            @forelse($permissionSettings as $permission)
            <tr class="intro-x permission-row" data-name="{{ strtolower($permission['user_name']) }}" data-type="{{ strtolower($permission['user_type']) }}">
                <td>
                    <a href="" class="font-medium whitespace-nowrap">{{ $permission['user_name'] }}</a>
                    <div class="text-slate-500 text-xs mt-0.5">{{ $permission['user_email'] }}</div>
                </td>
                <td class="text-center">
                    <span class="px-2 py-1 rounded-full text-xs {{ $permission['user_type'] === 'User' ? 'bg-primary/10 text-primary' : 'bg-success/10 text-success' }}">
                        {{ $permission['user_type'] }}
                    </span>
                </td>
                <td class="text-center">
                    @if(count($permission['modules']) > 0)
                        <div class="flex flex-wrap justify-center gap-1">
                            @foreach(array_slice($permission['modules'], 0, 3) as $module)
                                <span class="px-2 py-1 rounded text-xs bg-slate-100 dark:bg-darkmode-400">{{ $module }}</span>
                            @endforeach
                            @if(count($permission['modules']) > 3)
                                <span class="px-2 py-1 rounded text-xs bg-slate-100 dark:bg-darkmode-400">+{{ count($permission['modules']) - 3 }} more</span>
                            @endif
                        </div>
                    @else
                        <span class="text-slate-400 text-xs">No modules assigned</span>
                    @endif
                </td>
                <td class="w-40">
                    @if($permission['status'] === 'active')
                    <div class="flex items-center justify-center text-success"> 
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" icon-name="check-square" data-lucide="check-square" class="lucide lucide-check-square w-4 h-4 mr-2">
                            <polyline points="9 11 12 14 22 4"></polyline>
                            <path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"></path>
                        </svg> Active 
                    </div>
                    @else
                    <div class="flex items-center justify-center text-danger"> 
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" icon-name="check-square" data-lucide="check-square" class="lucide lucide-check-square w-4 h-4 mr-2">
                            <polyline points="9 11 12 14 22 4"></polyline>
                            <path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"></path>
                        </svg> Inactive 
                    </div>
                    @endif
                </td>
                <td class="table-report__action">
                    <div class="flex justify-center items-center">
                        <a class="flex items-center mr-3" href="javascript:;" onclick="editPermission('{{ $permission['id'] }}')" data-id="{{ $permission['id'] }}"> 
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" icon-name="check-square" data-lucide="check-square" class="lucide lucide-check-square w-4 h-4 mr-1">
                                <polyline points="9 11 12 14 22 4"></polyline>
                                <path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"></path>
                            </svg> Edit 
                        </a>
                        <a class="flex items-center text-danger" href="javascript:;" onclick="confirmDelete('{{ $permission['id'] }}', '{{ $permission['user_name'] }}')" data-id="{{ $permission['id'] }}"> 
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" icon-name="trash-2" data-lucide="trash-2" class="lucide lucide-trash-2 w-4 h-4 mr-1">
                                <polyline points="3 6 5 6 21 6"></polyline>
                                <path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"></path>
                                <line x1="10" y1="11" x2="10" y2="17"></line>
                                <line x1="14" y1="11" x2="14" y2="17"></line>
                            </svg> Delete 
                        </a>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="text-center py-5">
                    <div class="text-slate-500">No permission settings found</div>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
<!-- END: HTML Table Data -->

<!-- BEGIN: Toast Component -->
@include('components.toast')
<!-- END: Toast Component -->

<!-- BEGIN: Add Permission Modal -->
@php
    $addPermissionBody = '<form id="add-permission-form">
    <div class="grid grid-cols-1 gap-4">
        <div>
            <label class="form-label">User Type <span class="text-danger">*</span></label>
            <select class="form-control" name="user_type" id="user-type" required>
                <option value="">Select User Type</option>
                <option value="user">User</option>
                <option value="student">Student</option>
            </select>
        </div>
        <div>
            <label class="form-label">User/Student <span class="text-danger">*</span></label>
            <select class="form-control" name="user_id" id="user-id" required>
                <option value="">Select User/Student</option>
            </select>
        </div>
        <div>
            <label class="form-label">Status <span class="text-danger">*</span></label>
            <div class="flex items-center mt-2">
                <input type="radio" name="status" id="status-active" class="form-check-input" value="active" checked>
                <label for="status-active" class="ml-2 mr-5">Active</label>
                <input type="radio" name="status" id="status-inactive" class="form-check-input" value="inactive">
                <label for="status-inactive" class="ml-2">Inactive</label>
            </div>
        </div>
        <div>
            <label class="form-label">Modules</label>
            <div class="max-h-48 overflow-y-auto border rounded p-3" id="modules-container">';
    foreach($modules as $module) {
        $addPermissionBody .= '
                <div class="flex items-center mb-2">
                    <input type="checkbox" name="modules[]" id="module-'.$module->id.'" class="form-check-input" value="'.$module->id.'">
                    <label for="module-'.$module->id.'" class="ml-2">'.$module->module.'</label>
                </div>';
    }
    $addPermissionBody .= '
            </div>
        </div>
    </div>
</form>';
@endphp
@include('components.modal', [
'modalId' => 'add-permission-modal',
'size' => 'lg',
'title' => 'Add New Permission',
'body' => $addPermissionBody,
'footer' => '
<button type="button" class="btn btn-secondary" data-tw-dismiss="modal">Cancel</button>
<button type="button" class="btn btn-primary" id="save-permission-btn">
    <i data-lucide="save" class="w-4 h-4 mr-2"></i> Save Permission
</button>
',
'showButton' => false
])
<!-- END: Add Permission Modal -->

<!-- BEGIN: Edit Permission Modal -->
@php
    $editPermissionBody = '<form id="edit-permission-form">
    <input type="hidden" name="id" id="edit-permission-id">
    <div class="grid grid-cols-1 gap-4">
        <div>
            <label class="form-label">User Type <span class="text-danger">*</span></label>
            <select class="form-control" name="user_type" id="edit-user-type" required disabled>
                <option value="user">User</option>
                <option value="student">Student</option>
            </select>
        </div>
        <div>
            <label class="form-label">User/Student <span class="text-danger">*</span></label>
            <select class="form-control" name="user_id" id="edit-user-id" required disabled>
                <option value="">Select User/Student</option>
            </select>
        </div>
        <div>
            <label class="form-label">Status <span class="text-danger">*</span></label>
            <div class="flex items-center mt-2">
                <input type="radio" name="status" id="edit-status-active" class="form-check-input" value="active">
                <label for="edit-status-active" class="ml-2 mr-5">Active</label>
                <input type="radio" name="status" id="edit-status-inactive" class="form-check-input" value="inactive">
                <label for="edit-status-inactive" class="ml-2">Inactive</label>
            </div>
        </div>
        <div>
            <label class="form-label">Modules</label>
            <div class="max-h-48 overflow-y-auto border rounded p-3" id="edit-modules-container">';
    foreach($modules as $module) {
        $editPermissionBody .= '
                <div class="flex items-center mb-2">
                    <input type="checkbox" name="modules[]" id="edit-module-'.$module->id.'" class="form-check-input edit-module-checkbox" value="'.$module->id.'">
                    <label for="edit-module-'.$module->id.'" class="ml-2">'.$module->module.'</label>
                </div>';
    }
    $editPermissionBody .= '
            </div>
        </div>
    </div>
</form>';
@endphp
@include('components.modal', [
'modalId' => 'edit-permission-modal',
'size' => 'lg',
'title' => 'Edit Permission',
'body' => $editPermissionBody,
'footer' => '
<button type="button" class="btn btn-secondary" data-tw-dismiss="modal">Cancel</button>
<button type="button" class="btn btn-primary" id="update-permission-btn">
    <i data-lucide="save" class="w-4 h-4 mr-2"></i> Update Permission
</button>
',
'showButton' => false
])
<!-- END: Edit Permission Modal -->

<!-- BEGIN: Delete Confirmation Modal -->
@include('components.modal', [
'modalId' => 'delete-confirmation-modal',
'size' => 'sm',
'title' => 'Delete Confirmation',
'body' => '
<div class="p-5 text-center">
    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" icon-name="alert-triangle" data-lucide="alert-triangle" class="lucide lucide-alert-triangle w-16 h-16 text-danger mx-auto mt-3">
        <path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"></path>
        <path d="M12 9v4"></path>
        <path d="M12 17h.01"></path>
    </svg>
    <div class="text-slate-500 mt-4">Are you sure you want to delete this permission setting?</div>
    <div class="text-danger font-medium mt-2" id="delete-permission-name"></div>
</div>
',
'footer' => '
<button type="button" class="btn btn-secondary mr-2" data-tw-dismiss="modal">Cancel</button>
<button type="button" class="btn btn-danger" id="confirm-delete-btn">
    <i data-lucide="trash-2" class="w-4 h-4 mr-2"></i> Delete Permission
</button>
',
'showButton' => false
])
<!-- END: Delete Confirmation Modal -->
@endsection
@push('scripts')
<script>
    // Pass data to JavaScript
    window.permissionUsers = @json($users);
    window.permissionStudents = @json($studentsList);
</script>
<script src="{{ asset('js/permission/permission.js') }}?v={{ time() }}"></script>
@endpush

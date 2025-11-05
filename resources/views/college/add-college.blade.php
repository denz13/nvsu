@extends('layouts.master')

@section('subcontent')
<h2 class="intro-y text-lg font-medium mt-10">
    College
</h2>
<div class="intro-y col-span-12 flex flex-wrap sm:flex-nowrap items-center mt-2">
    <button class="btn btn-primary shadow-md mr-2" data-tw-toggle="modal" data-tw-target="#add-product-modal">Add New College</button>

    <div class="hidden md:block mx-auto text-slate-500">Showing {{ $colleges->firstItem() ?? 0 }} to {{ $colleges->lastItem() ?? 0 }} of {{ $colleges->total() }} entries</div>
    <div class="w-full sm:w-auto mt-3 sm:mt-0 sm:ml-auto md:ml-0">
        <div class="w-56 relative text-slate-500">
            <input type="text" class="form-control w-56 box pr-10" placeholder="Search...">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" icon-name="search" class="lucide lucide-search w-4 h-4 absolute my-auto inset-y-0 mr-3 right-0" data-lucide="search">
                <circle cx="11" cy="11" r="8"></circle>
                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
            </svg>
        </div>
    </div>
</div>
<!-- BEGIN: Users Layout -->
@foreach($colleges as $college)
<div class="intro-y col-span-12 md:col-span-6 lg:col-span-4 xl:col-span-3">
    <div class="box">
        <div class="p-5">
            <div class="h-40 2xl:h-56 image-fit rounded-md overflow-hidden before:block before:absolute before:w-full before:h-full before:top-0 before:left-0 before:z-10 before:bg-gradient-to-t before:from-black before:to-black/10">
                <img alt="{{ $college->college_name }}" class="rounded-md" src="{{ $college->photo ? asset($college->photo) : asset('dist/images/preview-10.jpg') }}">
                @if($college->status === 'active')
                <span class="absolute top-0 bg-success/80 text-white text-xs m-5 px-2 py-1 rounded z-10">Active</span>
                @else
                <span class="absolute top-0 bg-danger/80 text-white text-xs m-5 px-2 py-1 rounded z-10">Inactive</span>
                @endif
                <div class="absolute bottom-0 text-white px-5 pb-6 z-10"> <a href="" class="block font-medium text-base">{{ $college->college_name }}</a> <span class="text-white/90 text-xs mt-3">College</span> </div>
            </div>
            <div class="text-slate-600 dark:text-slate-500 mt-5">
                <div class="flex items-center"> <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" icon-name="check-square" data-lucide="check-square" class="lucide lucide-check-square w-4 h-4 mr-2">
                        <polyline points="9 11 12 14 22 4"></polyline>
                        <path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"></path>
                    </svg> Status: <span class="font-medium">{{ ucfirst($college->status) }}</span> </div>
            </div>
        </div>
        <div class="flex justify-center lg:justify-end items-center p-5 border-t border-slate-200/60 dark:border-darkmode-400">
            <a class="flex items-center mr-auto" href="javascript:;" onclick="editCollege('{{ $college->id }}')" data-id="{{ $college->id }}"> <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" icon-name="edit" data-lucide="edit" class="lucide lucide-edit w-4 h-4 mr-1">
                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                </svg> Edit </a>
            <a class="flex items-center text-danger" href="javascript:;" onclick="confirmDelete('{{ $college->id }}', '{{ $college->college_name }}')" data-id="{{ $college->id }}"> <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" icon-name="trash-2" data-lucide="trash-2" class="lucide lucide-trash-2 w-4 h-4 mr-1">
                    <polyline points="3 6 5 6 21 6"></polyline>
                    <path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"></path>
                    <line x1="10" y1="11" x2="10" y2="17"></line>
                    <line x1="14" y1="11" x2="14" y2="17"></line>
                </svg> Delete </a>
        </div>
    </div>
</div>
@endforeach

<!-- END: Users Layout -->
<!-- BEGIN: Pagination -->
{{ $colleges->links('components.pagination') }}
<!-- END: Pagination -->

<!-- BEGIN: Toast Component -->
@include('components.toast')
<!-- END: Toast Component -->

<!-- BEGIN: Add Product Modal -->
@include('components.modal', [
'modalId' => 'add-product-modal',
'size' => 'lg',
'title' => 'Add New College',
'body' => '
<form id="add-product-form" enctype="multipart/form-data">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="md:col-span-2">
            <label class="form-label">College Name <span class="text-danger">*</span></label>
            <input type="text" class="form-control" name="college_name" placeholder="Enter college name" required>
        </div>
        <div class="md:col-span-2">
            <label class="form-label">Photo</label>
            <input type="file" class="form-control" name="photo" accept="image/*">
            <div class="text-slate-500 text-xs mt-1">Supported formats: JPG, PNG, GIF</div>
        </div>
        <div class="md:col-span-2">
            <label class="form-label">Status <span class="text-danger">*</span></label>
            <div class="flex items-center mt-2">
                <input type="radio" name="status" id="status-active" class="form-check-input" value="active" checked>
                <label for="status-active" class="ml-2 mr-5">Active</label>
                <input type="radio" name="status" id="status-inactive" class="form-check-input" value="inactive">
                <label for="status-inactive" class="ml-2">Inactive</label>
            </div>
        </div>
    </div>
</form>
',
'footer' => '
<button type="button" class="btn btn-secondary" data-tw-dismiss="modal">Cancel</button>
<button type="button" class="btn btn-primary" id="save-product-btn">
    <i data-lucide="save" class="w-4 h-4 mr-2"></i> Save College
</button>
',
'showButton' => false
])
<!-- END: Add Product Modal -->

<!-- BEGIN: Edit Product Modal -->
@include('components.modal', [
'modalId' => 'edit-product-modal',
'size' => 'lg',
'title' => 'Edit College',
'body' => '
<form id="edit-product-form" enctype="multipart/form-data">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="md:col-span-2">
            <label class="form-label">College Name <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="edit-college-name" name="college_name" placeholder="Enter college name" required>
        </div>
        <div class="md:col-span-2">
            <label class="form-label">Photo</label>
            <input type="file" class="form-control" name="photo" accept="image/*">
            <div class="text-slate-500 text-xs mt-1">Supported formats: JPG, PNG, GIF</div>
            <div id="current-photo" class="mt-2"></div>
        </div>
        <div class="md:col-span-2">
            <label class="form-label">Status <span class="text-danger">*</span></label>
            <div class="flex items-center mt-2">
                <input type="radio" name="status" id="edit-status-active" class="form-check-input" value="active">
                <label for="edit-status-active" class="ml-2 mr-5">Active</label>
                <input type="radio" name="status" id="edit-status-inactive" class="form-check-input" value="inactive">
                <label for="edit-status-inactive" class="ml-2">Inactive</label>
            </div>
        </div>
    </div>
</form>
',
'footer' => '
<button type="button" class="btn btn-secondary" data-tw-dismiss="modal">Cancel</button>
<button type="button" class="btn btn-primary" id="update-product-btn">
    <i data-lucide="save" class="w-4 h-4 mr-2"></i> Update College
</button>
',
'showButton' => false
])
<!-- END: Edit Product Modal -->

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
    <div class="text-slate-500 mt-4">Are you sure you want to delete this college?</div>
    <div class="text-danger font-medium mt-2" id="delete-college-name"></div>
</div>
',
'footer' => '
<button type="button" class="btn btn-secondary mr-2" data-tw-dismiss="modal">Cancel</button>
<button type="button" class="btn btn-danger" id="confirm-delete-btn">
    <i data-lucide="trash-2" class="w-4 h-4 mr-2"></i> Delete College
</button>
',
'showButton' => false
])
<!-- END: Delete Confirmation Modal -->
@endsection
@push('scripts')
<script src="{{ asset('js/college/college.js') }}?v={{ time() }}"></script>
@endpush

@extends('layouts.master')

@section('subcontent')
<h2 class="intro-y text-lg font-medium mt-10">
    Students
</h2>
<div class="intro-y col-span-12 flex flex-wrap sm:flex-nowrap items-center mt-2">
    <button class="btn btn-primary shadow-md mr-2" data-tw-toggle="modal" data-tw-target="#add-product-modal">Add New Student</button>

    <div class="hidden md:block mx-auto text-slate-500">Showing {{ $students->firstItem() ?? 0 }} to {{ $students->lastItem() ?? 0 }} of {{ $students->total() }} entries</div>
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
<!-- BEGIN: Data List -->
<div class="intro-y col-span-12 overflow-auto lg:overflow-visible">
    <table class="table table-report -mt-2" style="width: 100%;">
        <thead>
            <tr>
                <th class="whitespace-nowrap">ID NUMBER</th>
                <th class="whitespace-nowrap">STUDENT NAME</th>
                <th class="text-center whitespace-nowrap">COLLEGE</th>
                <th class="text-center whitespace-nowrap">PROGRAM</th>
                <th class="text-center whitespace-nowrap">YEAR LEVEL</th>
                <th class="text-center whitespace-nowrap">STATUS</th>
                <th class="text-center whitespace-nowrap">ACTIONS</th>
            </tr>
        </thead>
        <tbody>
            @foreach($students as $student)
            <tr class="intro-x">
                <td>
                    <a href="" class="font-medium whitespace-nowrap">{{ $student->id_number }}</a>
                </td>
                <td>
                    <div class="flex items-center">
                        <div class="w-10 h-10 image-fit zoom-in">
                            <img alt="{{ $student->student_name }}" class="rounded-full" src="{{ $student->photo ? asset($student->photo) : asset('dist/images/preview-10.jpg') }}">
                        </div>
                        <div class="ml-4">
                            <a href="" class="font-medium whitespace-nowrap">{{ $student->student_name }}</a>
                            <div class="text-slate-500 text-xs whitespace-nowrap">{{ $student->address }}</div>
                        </div>
                    </div>
                </td>
                <td class="text-center">
                    <div class="text-xs whitespace-nowrap">{{ $student->college ? $student->college->college_name : 'N/A' }}</div>
                </td>
                <td class="text-center">
                    <div class="text-xs whitespace-nowrap">{{ $student->program ? $student->program->program_name : 'N/A' }}</div>
                </td>
                <td class="text-center">{{ $student->year_level }}</td>
                <td class="w-40">
                    @if($student->status === 'active')
                    <div class="flex items-center justify-center text-success"> <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" icon-name="check-square" data-lucide="check-square" class="lucide lucide-check-square w-4 h-4 mr-2"><polyline points="9 11 12 14 22 4"></polyline><path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"></path></svg> Active </div>
                    @else
                    <div class="flex items-center justify-center text-danger"> <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" icon-name="check-square" data-lucide="check-square" class="lucide lucide-check-square w-4 h-4 mr-2"><polyline points="9 11 12 14 22 4"></polyline><path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"></path></svg> Inactive </div>
                    @endif
                </td>
                <td class="table-report__action w-56">
                    <div class="flex justify-center items-center">
                        <a class="flex items-center mr-3" href="javascript:;" onclick="editStudent('{{ $student->id }}')" data-id="{{ $student->id }}"> <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" icon-name="edit" data-lucide="edit" class="lucide lucide-edit w-4 h-4 mr-1"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg> Edit </a>
                        <a class="flex items-center mr-3 text-success" href="javascript:;" onclick="generateBarcodeForStudent('{{ $student->id }}', '{{ $student->student_name }}')" data-id="{{ $student->id }}"> <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" icon-name="scan" data-lucide="scan" class="lucide lucide-scan w-4 h-4 mr-1"><path d="M3 7V5a2 2 0 0 1 2-2h2"></path><path d="M17 3h2a2 2 0 0 1 2 2v2"></path><path d="M21 17v2a2 2 0 0 1-2 2h-2"></path><path d="M7 21H5a2 2 0 0 1-2-2v-2"></path><line x1="7" y1="12" x2="17" y2="12"></line></svg> Barcode </a>
                        <a class="flex items-center text-danger" href="javascript:;" onclick="confirmDelete('{{ $student->id }}', '{{ $student->student_name }}')" data-id="{{ $student->id }}"> <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" icon-name="trash-2" data-lucide="trash-2" class="lucide lucide-trash-2 w-4 h-4 mr-1"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg> Delete </a>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
<!-- END: Data List -->
<!-- BEGIN: Pagination -->
{{ $students->links('components.pagination') }}
<!-- END: Pagination -->

<!-- BEGIN: Toast Component -->
@include('components.toast')
<!-- END: Toast Component -->

<!-- BEGIN: Add Product Modal -->
<div id="add-product-modal" class="modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="font-medium text-base mr-auto">Add New Student</h3>
                <a href="javascript:;" data-tw-dismiss="modal"> <i data-lucide="x" class="w-8 h-8 text-slate-400"></i> </a>
            </div>
            <div class="modal-body">
                <form id="add-product-form" enctype="multipart/form-data">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="md:col-span-2">
                            <label class="form-label">ID Number <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="id_number" placeholder="Enter ID number" required>
                        </div>
                        <div class="md:col-span-2">
                            <label class="form-label">Student Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="student_name" placeholder="Enter student name" required>
                        </div>
                        <div class="md:col-span-2">
                            <label class="form-label">Address</label>
                            <input type="text" class="form-control" name="address" placeholder="Enter address">
                        </div>
                        <div class="md:col-span-2">
                            <label class="form-label">Year Level <span class="text-danger">*</span></label>
                            <select class="form-control" name="year_level" required>
                                <option value="">Select Year Level</option>
                                <option value="1st Year">1st Year</option>
                                <option value="2nd Year">2nd Year</option>
                                <option value="3rd Year">3rd Year</option>
                                <option value="4th Year">4th Year</option>
                            </select>
                        </div>
                        <div class="md:col-span-2">
                            <label class="form-label">College <span class="text-danger">*</span></label>
                            <select class="form-control" name="college_id" required>
                                <option value="">Select College</option>
                                @foreach($colleges as $college)
                                <option value="{{ $college->id }}">{{ $college->college_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="md:col-span-2">
                            <label class="form-label">Program <span class="text-danger">*</span></label>
                            <select class="form-control" name="program_id" required>
                                <option value="">Select Program</option>
                                @foreach($programs as $program)
                                <option value="{{ $program->id }}">{{ $program->program_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="md:col-span-2">
                            <label class="form-label">Organization</label>
                            <select class="form-control" name="organization_id">
                                <option value="">N/A</option>
                                @foreach($organizations as $organization)
                                <option value="{{ $organization->id }}">{{ $organization->organization_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="md:col-span-2">
                            <label class="form-label">Photo</label>
                            <input type="file" class="form-control" name="photo" accept="image/*">
                            <div class="text-slate-500 text-xs mt-1">Supported formats: JPG, PNG, GIF</div>
                        </div>
                        <div class="md:col-span-2">
                            <label class="form-label">Password (Optional)</label>
                            <input type="password" class="form-control" name="password" placeholder="Leave blank for default password">
                            <div class="text-slate-500 text-xs mt-1">Default: default123</div>
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
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-tw-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="save-product-btn">
                    <i data-lucide="save" class="w-4 h-4 mr-2"></i> Save Student
                </button>
            </div>
        </div>
    </div>
</div>
<!-- END: Add Product Modal -->

<!-- BEGIN: Edit Product Modal -->
<div id="edit-product-modal" class="modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="font-medium text-base mr-auto">Edit Student</h3>
                <a href="javascript:;" data-tw-dismiss="modal"> <i data-lucide="x" class="w-8 h-8 text-slate-400"></i> </a>
            </div>
            <div class="modal-body">
                <form id="edit-product-form" enctype="multipart/form-data">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="md:col-span-2">
                            <label class="form-label">ID Number <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="edit-id-number" name="id_number" placeholder="Enter ID number" required>
                        </div>
                        <div class="md:col-span-2">
                            <label class="form-label">Student Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="edit-student-name" name="student_name" placeholder="Enter student name" required>
                        </div>
                        <div class="md:col-span-2">
                            <label class="form-label">Address</label>
                            <input type="text" class="form-control" id="edit-address" name="address" placeholder="Enter address">
                        </div>
                        <div class="md:col-span-2">
                            <label class="form-label">Year Level <span class="text-danger">*</span></label>
                            <select class="form-control" id="edit-year-level" name="year_level" required>
                                <option value="">Select Year Level</option>
                                <option value="1st Year">1st Year</option>
                                <option value="2nd Year">2nd Year</option>
                                <option value="3rd Year">3rd Year</option>
                                <option value="4th Year">4th Year</option>
                            </select>
                        </div>
                        <div class="md:col-span-2">
                            <label class="form-label">College <span class="text-danger">*</span></label>
                            <select class="form-control" id="edit-college-id" name="college_id" required>
                                <option value="">Select College</option>
                                @foreach($colleges as $college)
                                <option value="{{ $college->id }}">{{ $college->college_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="md:col-span-2">
                            <label class="form-label">Program <span class="text-danger">*</span></label>
                            <select class="form-control" id="edit-program-id" name="program_id" required>
                                <option value="">Select Program</option>
                                @foreach($programs as $program)
                                <option value="{{ $program->id }}">{{ $program->program_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="md:col-span-2">
                            <label class="form-label">Organization</label>
                            <select class="form-control" id="edit-organization-id" name="organization_id">
                                <option value="">N/A</option>
                                @foreach($organizations as $organization)
                                <option value="{{ $organization->id }}">{{ $organization->organization_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="md:col-span-2">
                            <label class="form-label">Photo</label>
                            <input type="file" class="form-control" name="photo" accept="image/*">
                            <div class="text-slate-500 text-xs mt-1">Supported formats: JPG, PNG, GIF</div>
                            <div id="current-photo" class="mt-2"></div>
                        </div>
                        <div class="md:col-span-2">
                            <label class="form-label">Password (Optional)</label>
                            <input type="password" class="form-control" name="password" placeholder="Leave blank to keep current password">
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
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-tw-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="update-product-btn">
                    <i data-lucide="save" class="w-4 h-4 mr-2"></i> Update Student
                </button>
            </div>
        </div>
    </div>
</div>
<!-- END: Edit Product Modal -->

<!-- BEGIN: Generate Barcode Modal -->
<div id="generate-barcode-modal" class="modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="font-medium text-base mr-auto">Generate Barcode</h3>
                <a href="javascript:;" data-tw-dismiss="modal"> <i data-lucide="x" class="w-8 h-8 text-slate-400"></i> </a>
            </div>
            <div class="modal-body">
                <div class="p-5 text-center">
                    <div class="text-slate-500 mt-4">Current Barcode</div>
                    <div class="text-success font-medium text-lg mt-2 mb-4" id="current-barcode-display">N/A</div>
                    
                    <!-- Current Barcode Image -->
                    <div id="current-barcode-image-container" class="mb-4"></div>
                    <div id="current-barcode-info" class="text-slate-500 text-sm mb-6"></div>
                    
                    <div class="text-slate-500 mt-4">New Barcode</div>
                    <div class="text-primary font-medium text-lg mt-2 mb-4" id="new-barcode-display">Click Generate to create a new barcode</div>
                    
                    <!-- New Barcode Image -->
                    <div id="new-barcode-image-container" class="mb-4"></div>
                    <div id="new-barcode-info" class="text-slate-500 text-sm mb-4"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary mr-2" data-tw-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="confirm-generate-barcode-btn">
                    <i data-lucide="scan" class="w-4 h-4 mr-2"></i> Generate New Barcode
                </button>
            </div>
        </div>
    </div>
</div>
<!-- END: Generate Barcode Modal -->

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
    <div class="text-slate-500 mt-4">Are you sure you want to delete this student?</div>
    <div class="text-danger font-medium mt-2" id="delete-student-name"></div>
</div>
',
'footer' => '
<button type="button" class="btn btn-secondary mr-2" data-tw-dismiss="modal">Cancel</button>
<button type="button" class="btn btn-danger" id="confirm-delete-btn">
    <i data-lucide="trash-2" class="w-4 h-4 mr-2"></i> Delete Student
</button>
',
'showButton' => false
])
<!-- END: Delete Confirmation Modal -->
@endsection
@push('scripts')
<script src="{{ asset('js/students/students.js') }}?v={{ time() }}"></script>
@endpush

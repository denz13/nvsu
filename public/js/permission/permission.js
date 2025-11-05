document.addEventListener('DOMContentLoaded', function() {
    // Get all elements
    const addPermissionForm = document.getElementById('add-permission-form');
    const editPermissionForm = document.getElementById('edit-permission-form');
    const savePermissionBtn = document.getElementById('save-permission-btn');
    const updatePermissionBtn = document.getElementById('update-permission-btn');
    const confirmDeleteBtn = document.getElementById('confirm-delete-btn');
    const userTypeSelect = document.getElementById('user-type');
    const userIdSelect = document.getElementById('user-id');
    const editUserTypeSelect = document.getElementById('edit-user-type');
    const editUserIdSelect = document.getElementById('edit-user-id');
    const searchInput = document.getElementById('search-permission');
    
    // Get users and students data from the page
    const users = window.permissionUsers || [];
    const studentsList = window.permissionStudents || [];
    
    // Delete confirmation
    let deletePermissionId = null;
    
    // User type change handler for add modal
    if (userTypeSelect && userIdSelect) {
        userTypeSelect.addEventListener('change', function() {
            updateUserDropdown(userIdSelect, this.value);
        });
    }
    
    // Function to update user dropdown based on user type
    function updateUserDropdown(dropdown, userType) {
        if (!dropdown) return;
        
        // Clear existing options
        dropdown.innerHTML = '<option value="">Select User/Student</option>';
        
        if (userType === 'user') {
            users.forEach(user => {
                const option = document.createElement('option');
                option.value = user.id;
                option.textContent = `${user.name} (${user.email})`;
                dropdown.appendChild(option);
            });
        } else if (userType === 'student') {
            studentsList.forEach(student => {
                const option = document.createElement('option');
                option.value = student.id;
                option.textContent = `${student.student_name} (${student.id_number})`;
                dropdown.appendChild(option);
            });
        }
    }
    
    // Search functionality
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const rows = document.querySelectorAll('.permission-row');
            
            rows.forEach(row => {
                const name = row.getAttribute('data-name');
                const type = row.getAttribute('data-type');
                
                if (name.includes(searchTerm) || type.includes(searchTerm)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    }
    
    // Save Permission
    if (savePermissionBtn && addPermissionForm) {
        savePermissionBtn.addEventListener('click', function() {
            if (addPermissionForm.checkValidity()) {
                const formData = new FormData(addPermissionForm);
                
                // Get and validate form values
                const userType = formData.get('user_type');
                const userId = formData.get('user_id');
                const status = formData.get('status');
                
                // Validation
                if (!userType || userType === '') {
                    showError('Error!', 'Please select a user type.');
                    return;
                }
                
                if (!userId || userId === '') {
                    showError('Error!', 'Please select a user/student.');
                    return;
                }
                
                // Get selected modules
                const selectedModules = [];
                const moduleCheckboxes = addPermissionForm.querySelectorAll('input[name="modules[]"]:checked');
                moduleCheckboxes.forEach(checkbox => {
                    selectedModules.push(checkbox.value);
                });
                
                // Create FormData with modules array
                const submitData = new FormData();
                submitData.append('user_type', userType);
                submitData.append('user_id', userId);
                submitData.append('status', status || 'active');
                selectedModules.forEach(moduleId => {
                    submitData.append('modules[]', moduleId);
                });
                
                // Debug log
                console.log('Submitting data:', {
                    user_type: userType,
                    user_id: userId,
                    status: status,
                    modules: selectedModules
                });
                
                savePermissionBtn.disabled = true;
                savePermissionBtn.innerHTML = '<i data-lucide="loader" class="w-4 h-4 mr-2 animate-spin"></i> Saving...';
                
                fetch('/permission/store', {
                    method: 'POST',
                    body: submitData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                })
                .then(async response => {
                    const contentType = response.headers.get('content-type');
                    if (!response.ok) {
                        if (contentType && contentType.includes('application/json')) {
                            const errorData = await response.json();
                            console.error('Error response:', errorData);
                            
                            // Extract validation errors
                            let errorMessage = errorData.message || 'Failed to save permission settings.';
                            if (errorData.errors) {
                                const errorMessages = [];
                                Object.keys(errorData.errors).forEach(key => {
                                    errorMessages.push(errorData.errors[key][0]);
                                });
                                errorMessage = errorMessages.join('<br>');
                            }
                            
                            showError('Error!', errorMessage);
                            throw new Error(errorMessage);
                        } else {
                            const text = await response.text();
                            console.error('Error response:', text);
                            showError('Error!', 'Failed to save permission settings. Please try again.');
                            throw new Error('Server error');
                        }
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        showSuccess('Success!', data.message);
                        addPermissionForm.reset();
                        if (userIdSelect) userIdSelect.innerHTML = '<option value="">Select User/Student</option>';
                        const modal = document.getElementById('add-permission-modal');
                        if (modal) {
                            const tailwindModal = tailwind.Modal.getInstance(modal);
                            if (tailwindModal) tailwindModal.hide();
                        }
                        setTimeout(() => {
                            window.location.reload();
                        }, 1500);
                    } else {
                        showError('Error!', data.message || 'Failed to save permission settings.');
                    }
                })
                .catch(error => {
                    // Error already shown in the response handler
                    console.error('Error:', error);
                })
                .finally(() => {
                    savePermissionBtn.disabled = false;
                    savePermissionBtn.innerHTML = '<i data-lucide="save" class="w-4 h-4 mr-2"></i> Save Permission';
                });
            } else {
                showError('Error!', 'Please fill in all required fields.');
                addPermissionForm.reportValidity();
            }
        });
    }
    
    // Update Permission
    if (updatePermissionBtn && editPermissionForm) {
        updatePermissionBtn.addEventListener('click', function() {
            if (editPermissionForm.checkValidity()) {
                const permissionId = document.getElementById('edit-permission-id')?.value;
                if (!permissionId) {
                    showError('Error!', 'Permission ID is missing.');
                    return;
                }
                
                const formData = new FormData(editPermissionForm);
                
                // Get and validate form values
                const userType = formData.get('user_type') || document.getElementById('edit-user-type')?.value;
                const userId = formData.get('user_id') || document.getElementById('edit-user-id')?.value;
                const status = formData.get('status');
                
                // Validation
                if (!userType || userType === '') {
                    showError('Error!', 'User type is missing.');
                    return;
                }
                
                if (!userId || userId === '') {
                    showError('Error!', 'User/Student is missing.');
                    return;
                }
                
                // Get selected modules
                const selectedModules = [];
                const moduleCheckboxes = editPermissionForm.querySelectorAll('input[name="modules[]"]:checked');
                moduleCheckboxes.forEach(checkbox => {
                    selectedModules.push(checkbox.value);
                });
                
                // Create FormData with modules array
                const submitData = new FormData();
                submitData.append('user_type', userType);
                submitData.append('user_id', userId);
                submitData.append('status', status || 'active');
                selectedModules.forEach(moduleId => {
                    submitData.append('modules[]', moduleId);
                });
                
                // Debug log
                console.log('Updating permission:', {
                    id: permissionId,
                    user_type: userType,
                    user_id: userId,
                    status: status,
                    modules: selectedModules
                });
                
                updatePermissionBtn.disabled = true;
                updatePermissionBtn.innerHTML = '<i data-lucide="loader" class="w-4 h-4 mr-2 animate-spin"></i> Updating...';
                
                fetch(`/permission/update/${permissionId}`, {
                    method: 'POST',
                    body: submitData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                        'X-HTTP-Method-Override': 'PUT'
                    }
                })
                .then(async response => {
                    const contentType = response.headers.get('content-type');
                    if (!response.ok) {
                        if (contentType && contentType.includes('application/json')) {
                            const errorData = await response.json();
                            console.error('Error response:', errorData);
                            
                            // Extract validation errors
                            let errorMessage = errorData.message || 'Failed to update permission settings.';
                            if (errorData.errors) {
                                const errorMessages = [];
                                Object.keys(errorData.errors).forEach(key => {
                                    errorMessages.push(errorData.errors[key][0]);
                                });
                                errorMessage = errorMessages.join('<br>');
                            }
                            
                            showError('Error!', errorMessage);
                            throw new Error(errorMessage);
                        } else {
                            const text = await response.text();
                            console.error('Error response:', text);
                            showError('Error!', 'Failed to update permission settings. Please try again.');
                            throw new Error('Server error');
                        }
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        showSuccess('Success!', data.message);
                        const modal = document.getElementById('edit-permission-modal');
                        if (modal) {
                            const tailwindModal = tailwind.Modal.getInstance(modal);
                            if (tailwindModal) tailwindModal.hide();
                        }
                        setTimeout(() => {
                            window.location.reload();
                        }, 1500);
                    } else {
                        showError('Error!', data.message || 'Failed to update permission settings.');
                    }
                })
                .catch(error => {
                    // Error already shown in the response handler
                    console.error('Error:', error);
                })
                .finally(() => {
                    updatePermissionBtn.disabled = false;
                    updatePermissionBtn.innerHTML = '<i data-lucide="save" class="w-4 h-4 mr-2"></i> Update Permission';
                });
            } else {
                showError('Error!', 'Please fill in all required fields.');
                editPermissionForm.reportValidity();
            }
        });
    }
    
    // Delete Permission
    if (confirmDeleteBtn) {
        confirmDeleteBtn.addEventListener('click', function() {
            if (deletePermissionId) {
                confirmDeleteBtn.disabled = true;
                confirmDeleteBtn.innerHTML = '<i data-lucide="loader" class="w-4 h-4 mr-2 animate-spin"></i> Deleting...';
                
                fetch(`/permission/delete/${deletePermissionId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                })
                .then(async response => {
                    if (!response.ok) {
                        const text = await response.text();
                        console.error('Error response:', text);
                        throw new Error('Server error');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        showSuccess('Success!', data.message);
                        const modal = document.getElementById('delete-confirmation-modal');
                        if (modal) {
                            const tailwindModal = tailwind.Modal.getInstance(modal);
                            if (tailwindModal) tailwindModal.hide();
                        }
                        setTimeout(() => {
                            window.location.reload();
                        }, 1500);
                    } else {
                        showError('Error!', data.message || 'Failed to delete permission settings.');
                    }
                })
                .catch(error => {
                    showError('Error!', 'Failed to delete permission settings. Please try again.');
                    console.error('Error:', error);
                })
                .finally(() => {
                    confirmDeleteBtn.disabled = false;
                    confirmDeleteBtn.innerHTML = '<i data-lucide="trash-2" class="w-4 h-4 mr-2"></i> Delete Permission';
                });
            }
        });
    }
});

// Edit Permission Function
window.editPermission = function(permissionId) {
    fetch(`/permission/edit/${permissionId}`, {
        method: 'GET',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        }
    })
    .then(async response => {
        if (!response.ok) {
            const text = await response.text();
            console.error('Error response:', text);
            throw new Error('Server error');
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            const permission = data.data;
            
            // Show edit modal
            const modalTrigger = document.createElement('div');
            modalTrigger.style.display = 'none';
            modalTrigger.setAttribute('data-tw-toggle', 'modal');
            modalTrigger.setAttribute('data-tw-target', '#edit-permission-modal');
            document.body.appendChild(modalTrigger);
            modalTrigger.click();
            setTimeout(() => document.body.removeChild(modalTrigger), 100);
            
            // Wait for modal to show
            const checkModal = setInterval(() => {
                const modalElement = document.getElementById('edit-permission-modal');
                if (modalElement && modalElement.classList.contains('show')) {
                    clearInterval(checkModal);
                    
                    setTimeout(() => {
                        console.log('Permission data:', permission);
                        console.log('Modules:', permission.modules);
                        
                        // Fill form data
                        const editPermissionId = document.getElementById('edit-permission-id');
                        const editUserType = document.getElementById('edit-user-type');
                        const editUserId = document.getElementById('edit-user-id');
                        
                        if (editPermissionId) editPermissionId.value = permission.id;
                        if (editUserType) editUserType.value = permission.user_type;
                        if (editUserId) editUserId.value = permission.user_id;
                        
                        // Set status radio
                        const editStatusActive = document.getElementById('edit-status-active');
                        const editStatusInactive = document.getElementById('edit-status-inactive');
                        if (editStatusActive && editStatusInactive) {
                            if (permission.status === 'active') {
                                editStatusActive.checked = true;
                                editStatusInactive.checked = false;
                            } else {
                                editStatusActive.checked = false;
                                editStatusInactive.checked = true;
                            }
                        }
                        
                        // Update user dropdown
                        const editUserTypeSelect = document.getElementById('edit-user-type');
                        const editUserIdSelect = document.getElementById('edit-user-id');
                        const users = window.permissionUsers || [];
                        const studentsList = window.permissionStudents || [];
                        
                        if (editUserIdSelect) {
                            editUserIdSelect.innerHTML = '<option value="">Select User/Student</option>';
                            if (permission.user_type === 'user') {
                                users.forEach(user => {
                                    const option = document.createElement('option');
                                    option.value = user.id;
                                    option.textContent = `${user.name} (${user.email})`;
                                    option.selected = user.id == permission.user_id;
                                    editUserIdSelect.appendChild(option);
                                });
                            } else {
                                studentsList.forEach(student => {
                                    const option = document.createElement('option');
                                    option.value = student.id;
                                    option.textContent = `${student.student_name} (${student.id_number})`;
                                    option.selected = student.id == permission.user_id;
                                    editUserIdSelect.appendChild(option);
                                });
                            }
                        }
                        
                        // Set module checkboxes - ensure modules is an array
                        const modulesArray = Array.isArray(permission.modules) ? permission.modules : [];
                        console.log('Modules array:', modulesArray);
                        
                        // First, uncheck all checkboxes
                        document.querySelectorAll('.edit-module-checkbox').forEach(checkbox => {
                            checkbox.checked = false;
                        });
                        
                        // Then check the ones that should be selected
                        document.querySelectorAll('.edit-module-checkbox').forEach(checkbox => {
                            const checkboxValue = parseInt(checkbox.value);
                            // Compare both as integers
                            if (modulesArray.some(moduleId => parseInt(moduleId) === checkboxValue)) {
                                checkbox.checked = true;
                                console.log('Checked module:', checkboxValue);
                            }
                        });
                    }, 200);
                }
            }, 100);
        } else {
            showError('Error!', data.message || 'Failed to load permission settings.');
        }
    })
    .catch(error => {
        showError('Error!', 'Failed to load permission settings. Please try again.');
        console.error('Error:', error);
    });
};

// Confirm Delete Function
window.confirmDelete = function(permissionId, permissionName) {
    window.deletePermissionId = permissionId;
    document.getElementById('delete-permission-name').textContent = permissionName;
    
    // Show delete modal
    const modalTrigger = document.createElement('div');
    modalTrigger.style.display = 'none';
    modalTrigger.setAttribute('data-tw-toggle', 'modal');
    modalTrigger.setAttribute('data-tw-target', '#delete-confirmation-modal');
    document.body.appendChild(modalTrigger);
    modalTrigger.click();
    setTimeout(() => document.body.removeChild(modalTrigger), 100);
};


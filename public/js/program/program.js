// Program Management JavaScript

document.addEventListener('DOMContentLoaded', function() {
    // Reset modal when "Add New Program" button is clicked
    const addBtn = document.querySelector('[data-tw-target="#add-product-modal"]');
    if (addBtn) {
        addBtn.addEventListener('click', function() {
            const saveBtn = document.getElementById('save-product-btn');
            saveBtn.innerHTML = '<i data-lucide="save" class="w-4 h-4 mr-2"></i> Save Program';
            document.getElementById('add-product-form').reset();
        });
    }
    
    // Handle Save Product Button
    const saveProductBtn = document.getElementById('save-product-btn');
    const addProductForm = document.getElementById('add-product-form');
    
    if (saveProductBtn) {
        saveProductBtn.addEventListener('click', function() {
            // Validate form
            if (addProductForm.checkValidity()) {
                // Get form data
                const formData = new FormData(addProductForm);
                
                // Log form data for debugging
                console.log('Program Data:');
                for (let [key, value] of formData.entries()) {
                    console.log(key, ':', value);
                }
                
                // Show loading state
                saveProductBtn.disabled = true;
                saveProductBtn.innerHTML = '<i data-lucide="loader" class="w-4 h-4 mr-2 animate-spin"></i> Saving...';
                
                // Make AJAX call to save program
                fetch('/program/store', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showSuccess('Success!', data.message);
                        // Reset form and close modal
                        addProductForm.reset();
                        const modal = document.getElementById('add-product-modal');
                        if (modal) {
                            modal.classList.remove('show');
                            document.body.classList.remove('modal-open');
                        }
                        // Reload page to refresh list
                        setTimeout(() => {
                            window.location.reload();
                        }, 1500);
                    } else {
                        showError('Error!', data.message || 'Failed to add program.');
                    }
                })
                .catch(error => {
                    showError('Error!', 'Failed to add program. Please try again.');
                    console.error('Error:', error);
                })
                .finally(() => {
                    saveProductBtn.disabled = false;
                    saveProductBtn.innerHTML = '<i data-lucide="save" class="w-4 h-4 mr-2"></i> Save Program';
                });
                
            } else {
                // Show validation error
                showError('Error!', 'Please fill in all required fields.');
                addProductForm.reportValidity();
            }
        });
    }
    
    // Handle Delete Confirmation Modal (if you have delete buttons)
    const deleteButtons = document.querySelectorAll('[data-tw-target="#delete-confirmation-modal"]');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function() {
            // You can add confirmation logic here
            console.log('Delete button clicked');
        });
    });
});

// Edit Program Function
window.editProgram = function(programId) {
    console.log('Edit button clicked for program ID:', programId);
    
    fetch(`/program/edit/${programId}`, {
        method: 'GET',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        console.log('Response data:', data);
        
        if (data.success) {
            const program = data.data;
            console.log('Program data:', program);
            console.log('College ID:', program.college_id);
            
            // Trigger Tailwind modal system using the same method as Add button
            const modalTrigger = document.createElement('div');
            modalTrigger.style.display = 'none';
            modalTrigger.setAttribute('data-tw-toggle', 'modal');
            modalTrigger.setAttribute('data-tw-target', '#edit-product-modal');
            document.body.appendChild(modalTrigger);
            
            // Click the trigger to show modal
            modalTrigger.click();
            
            // Clean up
            setTimeout(() => {
                document.body.removeChild(modalTrigger);
            }, 100);
            
            // Wait for modal to show before filling data
            const checkModal = setInterval(() => {
                const modalElement = document.getElementById('edit-product-modal');
                if (modalElement && modalElement.classList.contains('show')) {
                    clearInterval(checkModal);
                    
                    // Fill edit form with existing data after modal is shown
                    setTimeout(() => {
                        // Set college selection
                        const collegeSelect = document.getElementById('edit-college-id');
                        if (collegeSelect && program.college_id) {
                            collegeSelect.value = program.college_id;
                        }
                        
                        const nameInput = document.getElementById('edit-program-name');
                        if (nameInput) {
                            nameInput.value = program.program_name;
                        }
                        
                        // Set radio button based on status
                        if (program.status === 'active') {
                            const activeRadio = document.getElementById('edit-status-active');
                            if (activeRadio) activeRadio.checked = true;
                        } else {
                            const inactiveRadio = document.getElementById('edit-status-inactive');
                            if (inactiveRadio) inactiveRadio.checked = true;
                        }
                        
                        // Show current photo if exists
                        const currentPhotoDiv = document.getElementById('current-photo');
                        if (currentPhotoDiv && program.photo) {
                            currentPhotoDiv.innerHTML = `
                                <div class="mt-2">
                                    <label class="text-sm text-slate-500">Current Photo:</label>
                                    <img src="${program.photo.startsWith('http') ? program.photo : '/' + program.photo}" alt="Current photo" class="mt-1 rounded w-32 h-32 object-cover">
                                </div>
                            `;
                        } else if (currentPhotoDiv) {
                            currentPhotoDiv.innerHTML = '';
                        }
                        
                        // Set program ID for update button
                        const updateBtn = document.getElementById('update-product-btn');
                        if (updateBtn) {
                            updateBtn.setAttribute('data-program-id', programId);
                        }
                    }, 100);
                }
            }, 50);
        } else {
            showError('Error!', 'Failed to load program data.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showError('Error!', 'Failed to load program data.');
    });
};

// Update Program Function
window.updateProgram = function(programId) {
    const form = document.getElementById('edit-product-form');
    const formData = new FormData(form);
    
    // Add method spoofing for PUT request
    formData.append('_method', 'PUT');
    
    const updateBtn = document.getElementById('update-product-btn');
    updateBtn.disabled = true;
    updateBtn.innerHTML = '<i data-lucide="loader" class="w-4 h-4 mr-2 animate-spin"></i> Updating...';
    
    fetch(`/program/update/${programId}`, {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showSuccess('Success!', data.message);
            
            // Close modal
            const modal = document.getElementById('edit-product-modal');
            modal.classList.remove('show');
            modal.setAttribute('style', 'display: none;');
            document.body.classList.remove('modal-open');
            
            // Remove backdrop
            const backdrop = document.querySelector('.modal-backdrop');
            if (backdrop) {
                backdrop.remove();
            }
            
            // Reset form
            form.reset();
            
            // Reload page
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            showError('Error!', data.message || 'Failed to update program.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showError('Error!', 'Failed to update program.');
    })
    .finally(() => {
        updateBtn.disabled = false;
        updateBtn.innerHTML = '<i data-lucide="save" class="w-4 h-4 mr-2"></i> Update Program';
    });
};

// Add event listener for update button
document.addEventListener('DOMContentLoaded', function() {
    const updateBtn = document.getElementById('update-product-btn');
    if (updateBtn) {
        updateBtn.addEventListener('click', function() {
            const programId = this.getAttribute('data-program-id');
            if (programId) {
                updateProgram(programId);
            }
        });
    }
});

// Show Delete Confirmation Modal
window.confirmDelete = function(programId, programName) {
    // Set program name in modal
    const nameElement = document.getElementById('delete-program-name');
    if (nameElement) {
        nameElement.textContent = programName;
    }
    
    // Set program ID for confirm button
    const confirmBtn = document.getElementById('confirm-delete-btn');
    if (confirmBtn) {
        confirmBtn.setAttribute('data-program-id', programId);
    }
    
    // Trigger Tailwind modal system
    const modalTrigger = document.createElement('div');
    modalTrigger.style.display = 'none';
    modalTrigger.setAttribute('data-tw-toggle', 'modal');
    modalTrigger.setAttribute('data-tw-target', '#delete-confirmation-modal');
    document.body.appendChild(modalTrigger);
    
    // Click the trigger to show modal
    modalTrigger.click();
    
    // Clean up
    setTimeout(() => {
        document.body.removeChild(modalTrigger);
    }, 100);
};

// Delete Program Function
window.deleteProgram = function(programId) {
    fetch(`/program/delete/${programId}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showSuccess('Success!', data.message);
            
            // Close modal
            const modal = document.getElementById('delete-confirmation-modal');
            if (modal) {
                modal.classList.remove('show');
                modal.setAttribute('style', 'display: none;');
                document.body.classList.remove('modal-open');
            }
            
            // Remove backdrop
            const backdrop = document.querySelector('.modal-backdrop');
            if (backdrop) {
                backdrop.remove();
            }
            
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            showError('Error!', data.message || 'Failed to delete program.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showError('Error!', 'Failed to delete program.');
    });
};

// Add event listener for confirm delete button
document.addEventListener('DOMContentLoaded', function() {
    const confirmDeleteBtn = document.getElementById('confirm-delete-btn');
    if (confirmDeleteBtn) {
        confirmDeleteBtn.addEventListener('click', function() {
            const programId = this.getAttribute('data-program-id');
            if (programId) {
                deleteProgram(programId);
            }
        });
    }
});

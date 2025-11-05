// Organization Management JavaScript

document.addEventListener('DOMContentLoaded', function() {
    // Reset modal when "Add New Organization" button is clicked
    const addBtn = document.querySelector('[data-tw-target="#add-product-modal"]');
    if (addBtn) {
        addBtn.addEventListener('click', function() {
            const saveBtn = document.getElementById('save-product-btn');
            saveBtn.innerHTML = '<i data-lucide="save" class="w-4 h-4 mr-2"></i> Save Organization';
            document.getElementById('add-product-form').reset();
        });
    }
    
    // Handle Save Organization Button
    const saveOrganizationBtn = document.getElementById('save-product-btn');
    const addOrganizationForm = document.getElementById('add-product-form');
    
    if (saveOrganizationBtn) {
        saveOrganizationBtn.addEventListener('click', function() {
            // Validate form
            if (addOrganizationForm.checkValidity()) {
                // Get form data
                const formData = new FormData(addOrganizationForm);
                
                // Log form data for debugging
                console.log('Organization Data:');
                for (let [key, value] of formData.entries()) {
                    console.log(key, ':', value);
                }
                
                // Show loading state
                saveOrganizationBtn.disabled = true;
                saveOrganizationBtn.innerHTML = '<i data-lucide="loader" class="w-4 h-4 mr-2 animate-spin"></i> Saving...';
                
                // Make AJAX call to save organization
                fetch('/organization/store', {
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
                        addOrganizationForm.reset();
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
                        showError('Error!', data.message || 'Failed to add organization.');
                    }
                })
                .catch(error => {
                    showError('Error!', 'Failed to add organization. Please try again.');
                    console.error('Error:', error);
                })
                .finally(() => {
                    saveOrganizationBtn.disabled = false;
                    saveOrganizationBtn.innerHTML = '<i data-lucide="save" class="w-4 h-4 mr-2"></i> Save Organization';
                });
                
            } else {
                // Show validation error
                showError('Error!', 'Please fill in all required fields.');
                addOrganizationForm.reportValidity();
            }
        });
    }
});

// Edit Organization Function
window.editOrganization = function(organizationId) {
    console.log('Edit button clicked for organization ID:', organizationId);
    
    fetch(`/organization/edit/${organizationId}`, {
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
            const organization = data.data;
            
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
                        const nameInput = document.getElementById('edit-organization-name');
                        if (nameInput) {
                            nameInput.value = organization.organization_name;
                        }
                        
                        const descriptionInput = document.getElementById('edit-organization-description');
                        if (descriptionInput) {
                            descriptionInput.value = organization.organization_description || '';
                        }
                        
                        // Set radio button based on status
                        if (organization.status === 'active') {
                            const activeRadio = document.getElementById('edit-status-active');
                            if (activeRadio) activeRadio.checked = true;
                        } else {
                            const inactiveRadio = document.getElementById('edit-status-inactive');
                            if (inactiveRadio) inactiveRadio.checked = true;
                        }
                        
                        // Show current photo if exists
                        const currentPhotoDiv = document.getElementById('current-photo');
                        if (currentPhotoDiv && organization.photo) {
                            currentPhotoDiv.innerHTML = `
                                <div class="mt-2">
                                    <label class="text-sm text-slate-500">Current Photo:</label>
                                    <img src="${organization.photo.startsWith('http') ? organization.photo : '/' + organization.photo}" alt="Current photo" class="mt-1 rounded w-32 h-32 object-cover">
                                </div>
                            `;
                        } else if (currentPhotoDiv) {
                            currentPhotoDiv.innerHTML = '';
                        }
                        
                        // Set organization ID for update button
                        const updateBtn = document.getElementById('update-product-btn');
                        if (updateBtn) {
                            updateBtn.setAttribute('data-organization-id', organizationId);
                        }
                    }, 100);
                }
            }, 50);
        } else {
            showError('Error!', 'Failed to load organization data.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showError('Error!', 'Failed to load organization data.');
    });
};

// Update Organization Function
window.updateOrganization = function(organizationId) {
    const form = document.getElementById('edit-product-form');
    const formData = new FormData(form);
    
    // Add method spoofing for PUT request
    formData.append('_method', 'PUT');
    
    const updateBtn = document.getElementById('update-product-btn');
    updateBtn.disabled = true;
    updateBtn.innerHTML = '<i data-lucide="loader" class="w-4 h-4 mr-2 animate-spin"></i> Updating...';
    
    fetch(`/organization/update/${organizationId}`, {
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
            showError('Error!', data.message || 'Failed to update organization.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showError('Error!', 'Failed to update organization.');
    })
    .finally(() => {
        updateBtn.disabled = false;
        updateBtn.innerHTML = '<i data-lucide="save" class="w-4 h-4 mr-2"></i> Update Organization';
    });
};

// Add event listener for update button
document.addEventListener('DOMContentLoaded', function() {
    const updateBtn = document.getElementById('update-product-btn');
    if (updateBtn) {
        updateBtn.addEventListener('click', function() {
            const organizationId = this.getAttribute('data-organization-id');
            if (organizationId) {
                updateOrganization(organizationId);
            }
        });
    }
});

// Show Delete Confirmation Modal
window.confirmDelete = function(organizationId, organizationName) {
    // Set organization name in modal
    const nameElement = document.getElementById('delete-organization-name');
    if (nameElement) {
        nameElement.textContent = organizationName;
    }
    
    // Set organization ID for confirm button
    const confirmBtn = document.getElementById('confirm-delete-btn');
    if (confirmBtn) {
        confirmBtn.setAttribute('data-organization-id', organizationId);
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

// Delete Organization Function
window.deleteOrganization = function(organizationId) {
    fetch(`/organization/delete/${organizationId}`, {
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
            showError('Error!', data.message || 'Failed to delete organization.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showError('Error!', 'Failed to delete organization.');
    });
};

// Add event listener for confirm delete button
document.addEventListener('DOMContentLoaded', function() {
    const confirmDeleteBtn = document.getElementById('confirm-delete-btn');
    if (confirmDeleteBtn) {
        confirmDeleteBtn.addEventListener('click', function() {
            const organizationId = this.getAttribute('data-organization-id');
            if (organizationId) {
                deleteOrganization(organizationId);
            }
        });
    }
});


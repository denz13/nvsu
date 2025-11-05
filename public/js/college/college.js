// College Management JavaScript

document.addEventListener('DOMContentLoaded', function() {
    // Reset modal when "Add New College" button is clicked
    const addBtn = document.querySelector('[data-tw-target="#add-product-modal"]');
    if (addBtn) {
        addBtn.addEventListener('click', function() {
            const saveBtn = document.getElementById('save-product-btn');
            saveBtn.innerHTML = '<i data-lucide="save" class="w-4 h-4 mr-2"></i> Save College';
            document.getElementById('add-product-form').reset();
        });
    }
    
    // Handle Save College Button
    const saveCollegeBtn = document.getElementById('save-product-btn');
    const addCollegeForm = document.getElementById('add-product-form');
    
    if (saveCollegeBtn) {
        saveCollegeBtn.addEventListener('click', function() {
            // Validate form
            if (addCollegeForm.checkValidity()) {
                // Get form data
                const formData = new FormData(addCollegeForm);
                
                // Log form data for debugging
                console.log('College Data:');
                for (let [key, value] of formData.entries()) {
                    console.log(key, ':', value);
                }
                
                // Show loading state
                saveCollegeBtn.disabled = true;
                saveCollegeBtn.innerHTML = '<i data-lucide="loader" class="w-4 h-4 mr-2 animate-spin"></i> Saving...';
                
                // Make AJAX call to save college
                fetch('/college/store', {
                    method: 'POST',
                    body: formData,
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
                        // Reset form and close modal
                        addCollegeForm.reset();
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
                        showError('Error!', data.message || 'Failed to add college.');
                    }
                })
                .catch(error => {
                    showError('Error!', 'Failed to add college. Please try again.');
                    console.error('Error:', error);
                })
                .finally(() => {
                    saveCollegeBtn.disabled = false;
                    saveCollegeBtn.innerHTML = '<i data-lucide="save" class="w-4 h-4 mr-2"></i> Save College';
                });
                
            } else {
                // Show validation error
                showError('Error!', 'Please fill in all required fields.');
                addCollegeForm.reportValidity();
            }
        });
    }
});

// Edit College Function
window.editCollege = function(collegeId) {
    console.log('Edit button clicked for college ID:', collegeId);
    
    fetch(`/college/edit/${collegeId}`, {
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
            const college = data.data;
            
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
                        const nameInput = document.getElementById('edit-college-name');
                        if (nameInput) {
                            nameInput.value = college.college_name;
                        }
                        
                        // Set radio button based on status
                        if (college.status === 'active') {
                            const activeRadio = document.getElementById('edit-status-active');
                            if (activeRadio) activeRadio.checked = true;
                        } else {
                            const inactiveRadio = document.getElementById('edit-status-inactive');
                            if (inactiveRadio) inactiveRadio.checked = true;
                        }
                        
                        // Show current photo if exists
                        const currentPhotoDiv = document.getElementById('current-photo');
                        if (currentPhotoDiv && college.photo) {
                            currentPhotoDiv.innerHTML = `
                                <div class="mt-2">
                                    <label class="text-sm text-slate-500">Current Photo:</label>
                                    <img src="${college.photo.startsWith('http') ? college.photo : '/' + college.photo}" alt="Current photo" class="mt-1 rounded w-32 h-32 object-cover">
                                </div>
                            `;
                        } else if (currentPhotoDiv) {
                            currentPhotoDiv.innerHTML = '';
                        }
                        
                        // Set college ID for update button
                        const updateBtn = document.getElementById('update-product-btn');
                        if (updateBtn) {
                            updateBtn.setAttribute('data-college-id', collegeId);
                        }
                    }, 100);
                }
            }, 50);
        } else {
            showError('Error!', 'Failed to load college data.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showError('Error!', 'Failed to load college data.');
    });
};

// Update College Function
window.updateCollege = function(collegeId) {
    const form = document.getElementById('edit-product-form');
    const formData = new FormData(form);
    
    // Add method spoofing for PUT request
    formData.append('_method', 'PUT');
    
    const updateBtn = document.getElementById('update-product-btn');
    updateBtn.disabled = true;
    updateBtn.innerHTML = '<i data-lucide="loader" class="w-4 h-4 mr-2 animate-spin"></i> Updating...';
    
    fetch(`/college/update/${collegeId}`, {
        method: 'POST',
        body: formData,
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
            showError('Error!', data.message || 'Failed to update college.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showError('Error!', 'Failed to update college.');
    })
    .finally(() => {
        updateBtn.disabled = false;
        updateBtn.innerHTML = '<i data-lucide="save" class="w-4 h-4 mr-2"></i> Update College';
    });
};

// Add event listener for update button
document.addEventListener('DOMContentLoaded', function() {
    const updateBtn = document.getElementById('update-product-btn');
    if (updateBtn) {
        updateBtn.addEventListener('click', function() {
            const collegeId = this.getAttribute('data-college-id');
            if (collegeId) {
                updateCollege(collegeId);
            }
        });
    }
});

// Show Delete Confirmation Modal
window.confirmDelete = function(collegeId, collegeName) {
    // Set college name in modal
    const nameElement = document.getElementById('delete-college-name');
    if (nameElement) {
        nameElement.textContent = collegeName;
    }
    
    // Set college ID for confirm button
    const confirmBtn = document.getElementById('confirm-delete-btn');
    if (confirmBtn) {
        confirmBtn.setAttribute('data-college-id', collegeId);
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

// Delete College Function
window.deleteCollege = function(collegeId) {
    fetch(`/college/delete/${collegeId}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
            'Content-Type': 'application/json'
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
            showError('Error!', data.message || 'Failed to delete college.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showError('Error!', 'Failed to delete college.');
    });
};

// Add event listener for confirm delete button
document.addEventListener('DOMContentLoaded', function() {
    const confirmDeleteBtn = document.getElementById('confirm-delete-btn');
    if (confirmDeleteBtn) {
        confirmDeleteBtn.addEventListener('click', function() {
            const collegeId = this.getAttribute('data-college-id');
            if (collegeId) {
                deleteCollege(collegeId);
            }
        });
    }
});


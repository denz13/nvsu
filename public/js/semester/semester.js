// Semester Management JavaScript

document.addEventListener('DOMContentLoaded', function() {
    // Reset modal when "Add New Semester" button is clicked
    const addBtn = document.querySelector('[data-tw-target="#add-product-modal"]');
    if (addBtn) {
        addBtn.addEventListener('click', function() {
            const saveBtn = document.getElementById('save-product-btn');
            saveBtn.innerHTML = '<i data-lucide="save" class="w-4 h-4 mr-2"></i> Save Semester';
            document.getElementById('add-product-form').reset();
        });
    }
    
    // Handle Save Semester Button
    const saveSemesterBtn = document.getElementById('save-product-btn');
    const addSemesterForm = document.getElementById('add-product-form');
    
    if (saveSemesterBtn) {
        saveSemesterBtn.addEventListener('click', function() {
            // Validate form
            if (addSemesterForm.checkValidity()) {
                // Get form data
                const formData = new FormData(addSemesterForm);
                
                // Log form data for debugging
                console.log('Semester Data:');
                for (let [key, value] of formData.entries()) {
                    console.log(key, ':', value);
                }
                
                // Show loading state
                saveSemesterBtn.disabled = true;
                saveSemesterBtn.innerHTML = '<i data-lucide="loader" class="w-4 h-4 mr-2 animate-spin"></i> Saving...';
                
                // Make AJAX call to save semester
                fetch('/semester/store', {
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
                        addSemesterForm.reset();
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
                        showError('Error!', data.message || 'Failed to add semester.');
                    }
                })
                .catch(error => {
                    showError('Error!', 'Failed to add semester. Please try again.');
                    console.error('Error:', error);
                })
                .finally(() => {
                    saveSemesterBtn.disabled = false;
                    saveSemesterBtn.innerHTML = '<i data-lucide="save" class="w-4 h-4 mr-2"></i> Save Semester';
                });
                
            } else {
                // Show validation error
                showError('Error!', 'Please fill in all required fields.');
                addSemesterForm.reportValidity();
            }
        });
    }
});

// Edit Semester Function
window.editSemester = function(semesterId) {
    console.log('Edit button clicked for semester ID:', semesterId);
    
    fetch(`/semester/edit/${semesterId}`, {
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
            const semester = data.data;
            
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
                        const schoolYearInput = document.getElementById('edit-school-year');
                        if (schoolYearInput) {
                            schoolYearInput.value = semester.school_year;
                        }
                        
                        const semesterInput = document.getElementById('edit-semester');
                        if (semesterInput) {
                            semesterInput.value = semester.semester;
                        }
                        
                        // Set radio button based on status
                        if (semester.status === 'active') {
                            const activeRadio = document.getElementById('edit-status-active');
                            if (activeRadio) activeRadio.checked = true;
                        } else {
                            const inactiveRadio = document.getElementById('edit-status-inactive');
                            if (inactiveRadio) inactiveRadio.checked = true;
                        }
                        
                        // Set semester ID for update button
                        const updateBtn = document.getElementById('update-product-btn');
                        if (updateBtn) {
                            updateBtn.setAttribute('data-semester-id', semesterId);
                        }
                    }, 100);
                }
            }, 50);
        } else {
            showError('Error!', 'Failed to load semester data.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showError('Error!', 'Failed to load semester data.');
    });
};

// Update Semester Function
window.updateSemester = function(semesterId) {
    const form = document.getElementById('edit-product-form');
    const formData = new FormData(form);
    
    // Add method spoofing for PUT request
    formData.append('_method', 'PUT');
    
    const updateBtn = document.getElementById('update-product-btn');
    updateBtn.disabled = true;
    updateBtn.innerHTML = '<i data-lucide="loader" class="w-4 h-4 mr-2 animate-spin"></i> Updating...';
    
    fetch(`/semester/update/${semesterId}`, {
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
            showError('Error!', data.message || 'Failed to update semester.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showError('Error!', 'Failed to update semester.');
    })
    .finally(() => {
        updateBtn.disabled = false;
        updateBtn.innerHTML = '<i data-lucide="save" class="w-4 h-4 mr-2"></i> Update Semester';
    });
};

// Add event listener for update button
document.addEventListener('DOMContentLoaded', function() {
    const updateBtn = document.getElementById('update-product-btn');
    if (updateBtn) {
        updateBtn.addEventListener('click', function() {
            const semesterId = this.getAttribute('data-semester-id');
            if (semesterId) {
                updateSemester(semesterId);
            }
        });
    }
});

// Show Delete Confirmation Modal
window.confirmDelete = function(semesterId, semesterName) {
    // Set semester name in modal
    const nameElement = document.getElementById('delete-semester-name');
    if (nameElement) {
        nameElement.textContent = semesterName;
    }
    
    // Set semester ID for confirm button
    const confirmBtn = document.getElementById('confirm-delete-btn');
    if (confirmBtn) {
        confirmBtn.setAttribute('data-semester-id', semesterId);
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

// Delete Semester Function
window.deleteSemester = function(semesterId) {
    fetch(`/semester/delete/${semesterId}`, {
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
            showError('Error!', data.message || 'Failed to delete semester.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showError('Error!', 'Failed to delete semester.');
    });
};

// Add event listener for confirm delete button
document.addEventListener('DOMContentLoaded', function() {
    const confirmDeleteBtn = document.getElementById('confirm-delete-btn');
    if (confirmDeleteBtn) {
        confirmDeleteBtn.addEventListener('click', function() {
            const semesterId = this.getAttribute('data-semester-id');
            if (semesterId) {
                deleteSemester(semesterId);
            }
        });
    }
});


// Announcement Management JavaScript

document.addEventListener('DOMContentLoaded', function() {
    // Reset modal when "Add New Announcement" button is clicked
    const addBtn = document.querySelector('[data-tw-target="#add-product-modal"]');
    if (addBtn) {
        addBtn.addEventListener('click', function() {
            const saveBtn = document.getElementById('save-product-btn');
            saveBtn.innerHTML = '<i data-lucide="save" class="w-4 h-4 mr-2"></i> Save Announcement';
            document.getElementById('add-product-form').reset();
        });
    }
    
    // Handle Save Announcement Button
    const saveAnnouncementBtn = document.getElementById('save-product-btn');
    const addAnnouncementForm = document.getElementById('add-product-form');
    
    if (saveAnnouncementBtn) {
        saveAnnouncementBtn.addEventListener('click', function() {
            // Validate form
            if (addAnnouncementForm.checkValidity()) {
                // Get form data
                const formData = new FormData(addAnnouncementForm);
                
                // Log form data for debugging
                console.log('Announcement Data:');
                for (let [key, value] of formData.entries()) {
                    console.log(key, ':', value);
                }
                
                // Show loading state
                saveAnnouncementBtn.disabled = true;
                saveAnnouncementBtn.innerHTML = '<i data-lucide="loader" class="w-4 h-4 mr-2 animate-spin"></i> Saving...';
                
                // Make AJAX call to save announcement
                fetch('/announcement/store', {
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
                        addAnnouncementForm.reset();
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
                        showError('Error!', data.message || 'Failed to add announcement.');
                    }
                })
                .catch(error => {
                    showError('Error!', 'Failed to add announcement. Please try again.');
                    console.error('Error:', error);
                })
                .finally(() => {
                    saveAnnouncementBtn.disabled = false;
                    saveAnnouncementBtn.innerHTML = '<i data-lucide="save" class="w-4 h-4 mr-2"></i> Save Announcement';
                });
                
            } else {
                // Show validation error
                showError('Error!', 'Please fill in all required fields.');
                addAnnouncementForm.reportValidity();
            }
        });
    }
    
    // Handle Update Announcement Button
    const updateAnnouncementBtn = document.getElementById('update-product-btn');
    const editAnnouncementForm = document.getElementById('edit-product-form');
    
    if (updateAnnouncementBtn) {
        updateAnnouncementBtn.addEventListener('click', function() {
            const announcementId = updateAnnouncementBtn.dataset.id;
            
            if (!announcementId) {
                showError('Error!', 'Announcement ID is missing.');
                return;
            }
            
            // Validate form
            if (editAnnouncementForm.checkValidity()) {
                // Get form data
                const formData = new FormData(editAnnouncementForm);
                
                // Log form data for debugging
                console.log('Update Announcement Data:');
                for (let [key, value] of formData.entries()) {
                    console.log(key, ':', value);
                }
                
                // Show loading state
                updateAnnouncementBtn.disabled = true;
                updateAnnouncementBtn.innerHTML = '<i data-lucide="loader" class="w-4 h-4 mr-2 animate-spin"></i> Updating...';
                
                // Make AJAX call to update announcement
                fetch(`/announcement/update/${announcementId}`, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                        'X-HTTP-Method-Override': 'PUT'
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
                        if (modal) {
                            modal.classList.remove('show');
                            document.body.classList.remove('modal-open');
                        }
                        // Reload page to refresh list
                        setTimeout(() => {
                            window.location.reload();
                        }, 1500);
                    } else {
                        showError('Error!', data.message || 'Failed to update announcement.');
                    }
                })
                .catch(error => {
                    showError('Error!', 'Failed to update announcement. Please try again.');
                    console.error('Error:', error);
                })
                .finally(() => {
                    updateAnnouncementBtn.disabled = false;
                    updateAnnouncementBtn.innerHTML = '<i data-lucide="save" class="w-4 h-4 mr-2"></i> Update Announcement';
                });
                
            } else {
                // Show validation error
                showError('Error!', 'Please fill in all required fields.');
                editAnnouncementForm.reportValidity();
            }
        });
    }
    
    // Handle Delete Confirmation Button
    const confirmDeleteBtn = document.getElementById('confirm-delete-btn');
    if (confirmDeleteBtn) {
        confirmDeleteBtn.addEventListener('click', function() {
            const announcementId = confirmDeleteBtn.dataset.id;
            
            if (!announcementId) {
                showError('Error!', 'Announcement ID is missing.');
                return;
            }
            
            // Show loading state
            confirmDeleteBtn.disabled = true;
            confirmDeleteBtn.innerHTML = '<i data-lucide="loader" class="w-4 h-4 mr-2 animate-spin"></i> Deleting...';
            
            // Make AJAX call to delete announcement
            fetch(`/announcement/delete/${announcementId}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                    'X-HTTP-Method-Override': 'DELETE'
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
                        document.body.classList.remove('modal-open');
                    }
                    // Reload page to refresh list
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                } else {
                    showError('Error!', data.message || 'Failed to delete announcement.');
                }
            })
            .catch(error => {
                showError('Error!', 'Failed to delete announcement. Please try again.');
                console.error('Error:', error);
            })
            .finally(() => {
                confirmDeleteBtn.disabled = false;
                confirmDeleteBtn.innerHTML = '<i data-lucide="trash-2" class="w-4 h-4 mr-2"></i> Delete Announcement';
            });
        });
    }
});

// Edit Announcement Function
window.editAnnouncement = function(announcementId) {
    console.log('Edit button clicked for announcement ID:', announcementId);
    
    fetch(`/announcement/edit/${announcementId}`, {
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
            const announcement = data.data;
            
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
                        const titleInput = document.getElementById('edit-title');
                        if (titleInput) {
                            titleInput.value = announcement.title || '';
                        }
                        
                        const descriptionInput = document.getElementById('edit-description');
                        if (descriptionInput) {
                            descriptionInput.value = announcement.description || '';
                        }
                        
                        // Set radio button based on status
                        if (announcement.status === 'active') {
                            const activeRadio = document.getElementById('edit-status-active');
                            if (activeRadio) activeRadio.checked = true;
                            const inactiveRadio = document.getElementById('edit-status-inactive');
                            if (inactiveRadio) inactiveRadio.checked = false;
                        } else {
                            const activeRadio = document.getElementById('edit-status-active');
                            if (activeRadio) activeRadio.checked = false;
                            const inactiveRadio = document.getElementById('edit-status-inactive');
                            if (inactiveRadio) inactiveRadio.checked = true;
                        }
                        
                        // Set announcement ID for update button
                        const updateBtn = document.getElementById('update-product-btn');
                        if (updateBtn) {
                            updateBtn.setAttribute('data-id', announcementId);
                        }
                    }, 100);
                }
            }, 50);
        } else {
            showError('Error!', data.message || 'Failed to load announcement details.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showError('Error!', 'Failed to load announcement details. Please try again.');
    });
};

// Confirm Delete Function
window.confirmDelete = function(announcementId, announcementTitle) {
    console.log('Delete button clicked for announcement ID:', announcementId);
    
    // Set announcement title in delete modal
    const deleteTitleElement = document.getElementById('delete-announcement-title');
    if (deleteTitleElement) {
        deleteTitleElement.textContent = announcementTitle || 'Unknown';
    }
    
    // Set announcement ID for delete button
    const confirmDeleteBtn = document.getElementById('confirm-delete-btn');
    if (confirmDeleteBtn) {
        confirmDeleteBtn.dataset.id = announcementId;
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


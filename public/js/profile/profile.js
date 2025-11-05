// Profile JavaScript

let selectedPhotoFile = null;

document.addEventListener('DOMContentLoaded', function() {
    // Handle Update Profile Form
    const updateProfileForm = document.getElementById('update-profile-form');
    const updateProfileBtn = document.getElementById('update-profile-btn');
    
    if (updateProfileForm && updateProfileBtn) {
        updateProfileForm.addEventListener('submit', function(e) {
            e.preventDefault();
            updateProfile();
        });
    }
    
    // Handle Change Password Form
    const changePasswordForm = document.getElementById('change-password-form');
    const changePasswordBtn = document.getElementById('change-password-btn');
    
    if (changePasswordForm && changePasswordBtn) {
        changePasswordForm.addEventListener('submit', function(e) {
            e.preventDefault();
            changePassword();
        });
    }
    
    // Handle Camera Icon Click
    const cameraIconBtn = document.getElementById('camera-icon-btn');
    const photoInput = document.getElementById('profile-photo-input');
    
    if (cameraIconBtn && photoInput) {
        cameraIconBtn.addEventListener('click', function() {
            photoInput.click();
        });
        
        photoInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                // Validate file type
                if (!file.type.startsWith('image/')) {
                    showProfileToast('Please select a valid image file', 'error');
                    return;
                }
                
                // Validate file size (2MB max)
                if (file.size > 2048 * 1024) {
                    showProfileToast('Image size must be less than 2MB', 'error');
                    return;
                }
                
                selectedPhotoFile = file;
                showPhotoPreview(file);
            }
        });
    }
    
    // Handle Confirm Photo Upload Button
    const confirmPhotoUploadBtn = document.getElementById('confirm-photo-upload-btn');
    if (confirmPhotoUploadBtn) {
        confirmPhotoUploadBtn.addEventListener('click', function() {
            if (selectedPhotoFile) {
                uploadPhoto(selectedPhotoFile);
            }
        });
    }
});

// Update Profile
function updateProfile() {
    const form = document.getElementById('update-profile-form');
    const btn = document.getElementById('update-profile-btn');
    
    if (!form || !btn) {
        console.error('Form elements not found!');
        return;
    }
    
    // Validate form
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }
    
    // Disable button during request
    btn.disabled = true;
    const originalBtnText = btn.innerHTML;
    btn.innerHTML = '<div class="animate-spin rounded-full h-4 w-4 border-b-2 border-white mx-auto"></div>';
    
    // Collect form data
    const formData = new FormData(form);
    
    fetch('/profile/update', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showProfileToast('Profile updated successfully!', 'success');
            // Reload page after 1 second to reflect changes
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            showProfileToast(data.message || 'Failed to update profile', 'error');
            if (data.errors) {
                console.error('Validation errors:', data.errors);
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showProfileToast('An error occurred while updating profile', 'error');
    })
    .finally(() => {
        // Re-enable button
        btn.disabled = false;
        btn.innerHTML = originalBtnText;
    });
}

// Change Password
function changePassword() {
    const form = document.getElementById('change-password-form');
    const btn = document.getElementById('change-password-btn');
    
    if (!form || !btn) {
        console.error('Form elements not found!');
        return;
    }
    
    // Validate form
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }
    
    // Check if new password and confirm password match
    const newPassword = document.getElementById('new-password').value;
    const confirmPassword = document.getElementById('confirm-password').value;
    
    if (newPassword !== confirmPassword) {
        showProfileToast('New password and confirm password do not match', 'error');
        return;
    }
    
    // Check minimum length
    if (newPassword.length < 6) {
        showProfileToast('New password must be at least 6 characters', 'error');
        return;
    }
    
    // Disable button during request
    btn.disabled = true;
    const originalBtnText = btn.innerHTML;
    btn.innerHTML = '<div class="animate-spin rounded-full h-4 w-4 border-b-2 border-white mx-auto"></div>';
    
    // Collect form data
    const formData = new FormData(form);
    
    fetch('/profile/change-password', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showProfileToast('Password changed successfully!', 'success');
            // Clear form after 1 second
            setTimeout(() => {
                form.reset();
            }, 1000);
        } else {
            showProfileToast(data.message || 'Failed to change password', 'error');
            if (data.errors) {
                console.error('Validation errors:', data.errors);
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showProfileToast('An error occurred while changing password', 'error');
    })
    .finally(() => {
        // Re-enable button
        btn.disabled = false;
        btn.innerHTML = originalBtnText;
    });
}

// Show Photo Preview Modal
function showPhotoPreview(file) {
    const modal = document.getElementById('photo-upload-modal');
    const previewImg = document.getElementById('photo-preview');
    
    if (!modal || !previewImg) {
        console.error('Modal elements not found!');
        return;
    }
    
    // Create preview URL
    const reader = new FileReader();
    reader.onload = function(e) {
        previewImg.src = e.target.result;
        
        // Show modal
        if (typeof tailwind !== 'undefined' && tailwind.Modal) {
            const modalInstance = tailwind.Modal.getOrCreateInstance(modal);
            modalInstance.show();
        }
    };
    reader.readAsDataURL(file);
}

// Upload Photo
function uploadPhoto(file) {
    const btn = document.getElementById('confirm-photo-upload-btn');
    
    if (!btn) {
        console.error('Button not found!');
        return;
    }
    
    // Disable button during request
    btn.disabled = true;
    const originalBtnText = btn.innerHTML;
    btn.innerHTML = '<div class="animate-spin rounded-full h-4 w-4 border-b-2 border-white mx-auto"></div>';
    
    // Create FormData
    const formData = new FormData();
    formData.append('photo', file);
    
    // Log for debugging
    console.log('Uploading photo:', file.name, file.type, file.size);
    
    fetch('/profile/update-photo', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            'Accept': 'application/json'
            // Note: Don't set Content-Type for FormData - browser sets it automatically with boundary
        },
        body: formData
    })
    .then(response => {
        return response.json().then(data => ({ status: response.status, data }));
    })
    .then(({ status, data }) => {
        console.log('Response status:', status);
        console.log('Response data:', data);
        
        if (data.success) {
            showProfileToast('Profile photo updated successfully!', 'success');
            
            // Update displayed photo
            const profilePhotoDisplay = document.getElementById('profile-photo-display');
            if (profilePhotoDisplay && data.photo_url) {
                profilePhotoDisplay.src = data.photo_url;
            }
            
            // Close modal
            const modal = document.getElementById('photo-upload-modal');
            if (modal && typeof tailwind !== 'undefined' && tailwind.Modal) {
                const modalInstance = tailwind.Modal.getOrCreateInstance(modal);
                modalInstance.hide();
            }
            
            // Clear selected file
            selectedPhotoFile = null;
            const photoInput = document.getElementById('profile-photo-input');
            if (photoInput) {
                photoInput.value = '';
            }
            
            // Reload page after 1 second to reflect changes
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            // Show validation errors if available
            let errorMessage = data.message || 'Failed to update photo';
            if (data.errors) {
                console.error('Validation errors:', data.errors);
                // Get first error message
                const firstError = Object.values(data.errors)[0];
                if (Array.isArray(firstError) && firstError.length > 0) {
                    errorMessage = firstError[0];
                } else if (typeof firstError === 'string') {
                    errorMessage = firstError;
                }
            }
            showProfileToast(errorMessage, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showProfileToast('An error occurred while updating photo', 'error');
    })
    .finally(() => {
        // Re-enable button
        btn.disabled = false;
        btn.innerHTML = originalBtnText;
    });
}

// Toast notification helper
function showProfileToast(message, type = 'success') {
    if (typeof window.showToast === 'function') {
        // window.showToast expects (type, title, message)
        const toastType = type === 'success' ? 'success' : 'error';
        const title = type === 'success' ? 'Success' : 'Error';
        window.showToast(toastType, title, message);
    } else {
        alert(message);
    }
}

// Student Management JavaScript

document.addEventListener('DOMContentLoaded', function() {
    // Reset modal when "Add New Student" button is clicked
    const addBtn = document.querySelector('[data-tw-target="#add-product-modal"]');
    if (addBtn) {
        addBtn.addEventListener('click', function() {
            const saveBtn = document.getElementById('save-product-btn');
            saveBtn.innerHTML = '<i data-lucide="save" class="w-4 h-4 mr-2"></i> Save Student';
            document.getElementById('add-product-form').reset();
        });
    }
    
    // Handle Save Student Button
    const saveStudentBtn = document.getElementById('save-product-btn');
    const addStudentForm = document.getElementById('add-product-form');
    
    if (saveStudentBtn) {
        saveStudentBtn.addEventListener('click', function() {
            if (addStudentForm.checkValidity()) {
                const formData = new FormData(addStudentForm);
                
                saveStudentBtn.disabled = true;
                saveStudentBtn.innerHTML = '<i data-lucide="loader" class="w-4 h-4 mr-2 animate-spin"></i> Saving...';
                
                fetch('/students/store', {
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
                        addStudentForm.reset();
                        const modal = document.getElementById('add-product-modal');
                        if (modal) {
                            modal.classList.remove('show');
                            document.body.classList.remove('modal-open');
                        }
                        setTimeout(() => {
                            window.location.reload();
                        }, 1500);
                    } else {
                        showError('Error!', data.message || 'Failed to add student.');
                    }
                })
                .catch(error => {
                    showError('Error!', 'Failed to add student. Please try again.');
                    console.error('Error:', error);
                })
                .finally(() => {
                    saveStudentBtn.disabled = false;
                    saveStudentBtn.innerHTML = '<i data-lucide="save" class="w-4 h-4 mr-2"></i> Save Student';
                });
            } else {
                showError('Error!', 'Please fill in all required fields.');
                addStudentForm.reportValidity();
            }
        });
    }
});

// Edit Student Function
window.editStudent = function(studentId) {
    fetch(`/students/edit/${studentId}`, {
        method: 'GET',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const student = data.data;
            
            const modalTrigger = document.createElement('div');
            modalTrigger.style.display = 'none';
            modalTrigger.setAttribute('data-tw-toggle', 'modal');
            modalTrigger.setAttribute('data-tw-target', '#edit-product-modal');
            document.body.appendChild(modalTrigger);
            modalTrigger.click();
            setTimeout(() => {
                document.body.removeChild(modalTrigger);
            }, 100);
            
            const checkModal = setInterval(() => {
                const modalElement = document.getElementById('edit-product-modal');
                if (modalElement && modalElement.classList.contains('show')) {
                    clearInterval(checkModal);
                    setTimeout(() => {
                        // Set dropdown values
                        if (document.getElementById('edit-college-id')) {
                            document.getElementById('edit-college-id').value = student.college_id;
                        }
                        if (document.getElementById('edit-program-id')) {
                            document.getElementById('edit-program-id').value = student.program_id;
                        }
                        if (document.getElementById('edit-organization-id')) {
                            document.getElementById('edit-organization-id').value = student.organization_id || '';
                        }
                        
                        // Set text inputs
                        document.getElementById('edit-id-number').value = student.id_number;
                        document.getElementById('edit-student-name').value = student.student_name;
                        document.getElementById('edit-address').value = student.address || '';
                        document.getElementById('edit-year-level').value = student.year_level;
                        
                        // Set status
                        if (student.status === 'active') {
                            document.getElementById('edit-status-active').checked = true;
                        } else {
                            document.getElementById('edit-status-inactive').checked = true;
                        }
                        
                        // Show current photo
                        const currentPhotoDiv = document.getElementById('current-photo');
                        if (currentPhotoDiv && student.photo) {
                            currentPhotoDiv.innerHTML = `
                                <div class="mt-2">
                                    <label class="text-sm text-slate-500">Current Photo:</label>
                                    <img src="${student.photo.startsWith('http') ? student.photo : '/' + student.photo}" alt="Current photo" class="mt-1 rounded w-32 h-32 object-cover">
                                </div>
                            `;
                        } else if (currentPhotoDiv) {
                            currentPhotoDiv.innerHTML = '';
                        }
                        
                        document.getElementById('update-product-btn').setAttribute('data-student-id', studentId);
                    }, 100);
                }
            }, 50);
        } else {
            showError('Error!', 'Failed to load student data.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showError('Error!', 'Failed to load student data.');
    });
};

// Update Student Function
window.updateStudent = function(studentId) {
    const form = document.getElementById('edit-product-form');
    const formData = new FormData(form);
    formData.append('_method', 'PUT');
    
    const updateBtn = document.getElementById('update-product-btn');
    updateBtn.disabled = true;
    updateBtn.innerHTML = '<i data-lucide="loader" class="w-4 h-4 mr-2 animate-spin"></i> Updating...';
    
    fetch(`/students/update/${studentId}`, {
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
            const modal = document.getElementById('edit-product-modal');
            modal.classList.remove('show');
            modal.setAttribute('style', 'display: none;');
            document.body.classList.remove('modal-open');
            const backdrop = document.querySelector('.modal-backdrop');
            if (backdrop) {
                backdrop.remove();
            }
            form.reset();
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            showError('Error!', data.message || 'Failed to update student.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showError('Error!', 'Failed to update student.');
    })
    .finally(() => {
        updateBtn.disabled = false;
        updateBtn.innerHTML = '<i data-lucide="save" class="w-4 h-4 mr-2"></i> Update Student';
    });
};

document.addEventListener('DOMContentLoaded', function() {
    const updateBtn = document.getElementById('update-product-btn');
    if (updateBtn) {
        updateBtn.addEventListener('click', function() {
            const studentId = this.getAttribute('data-student-id');
            if (studentId) {
                updateStudent(studentId);
            }
        });
    }
});

// Show Delete Confirmation Modal
window.confirmDelete = function(studentId, studentName) {
    const nameElement = document.getElementById('delete-student-name');
    if (nameElement) {
        nameElement.textContent = studentName;
    }
    const confirmBtn = document.getElementById('confirm-delete-btn');
    if (confirmBtn) {
        confirmBtn.setAttribute('data-student-id', studentId);
    }
    const modalTrigger = document.createElement('div');
    modalTrigger.style.display = 'none';
    modalTrigger.setAttribute('data-tw-toggle', 'modal');
    modalTrigger.setAttribute('data-tw-target', '#delete-confirmation-modal');
    document.body.appendChild(modalTrigger);
    modalTrigger.click();
    setTimeout(() => {
        document.body.removeChild(modalTrigger);
    }, 100);
};

// Delete Student Function
window.deleteStudent = function(studentId) {
    fetch(`/students/delete/${studentId}`, {
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
            const modal = document.getElementById('delete-confirmation-modal');
            if (modal) {
                modal.classList.remove('show');
                modal.setAttribute('style', 'display: none;');
                document.body.classList.remove('modal-open');
            }
            const backdrop = document.querySelector('.modal-backdrop');
            if (backdrop) {
                backdrop.remove();
            }
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            showError('Error!', data.message || 'Failed to delete student.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showError('Error!', 'Failed to delete student.');
    });
};

document.addEventListener('DOMContentLoaded', function() {
    const confirmDeleteBtn = document.getElementById('confirm-delete-btn');
    if (confirmDeleteBtn) {
        confirmDeleteBtn.addEventListener('click', function() {
            const studentId = this.getAttribute('data-student-id');
            if (studentId) {
                deleteStudent(studentId);
            }
        });
    }
});

// Dynamic program filtering based on college - Disabled for now
// You can implement this later with an API endpoint

// Generate Barcode for Student Function - Show Modal
window.generateBarcodeForStudent = function(studentId, studentName) {
    // Fetch student data to get current barcode
    fetch(`/students/edit/${studentId}`, {
        method: 'GET',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const student = data.data;
            const currentBarcode = student.barcode || 'N/A';
            
            // Set current barcode
            const currentBarcodeDisplay = document.getElementById('current-barcode-display');
            if (currentBarcodeDisplay) {
                currentBarcodeDisplay.textContent = currentBarcode;
            }
            
            // Show current barcode image
            const currentBarcodeImageContainer = document.getElementById('current-barcode-image-container');
            if (currentBarcodeImageContainer && currentBarcode !== 'N/A') {
                // Sanitize barcode for Code128 (supports all ASCII, remove only control characters)
                const sanitizedBarcode = currentBarcode.toString().replace(/[\x00-\x1F\x7F]/g, '');
                
                // Create canvas element for barcode
                const canvasId = 'current-barcode-canvas-' + Date.now();
                currentBarcodeImageContainer.innerHTML = `
                    <canvas id="${canvasId}" style="max-width: 100%; height: auto;"></canvas>
                    <div class="text-center mt-2 text-xs text-slate-500">${sanitizedBarcode}</div>
                `;
                
                // Generate barcode using JsBarcode
                try {
                    JsBarcode(`#${canvasId}`, sanitizedBarcode, {
                        format: "CODE128",
                        width: 2,
                        height: 50,
                        displayValue: false,
                        background: "#ffffff",
                        lineColor: "#000000"
                    });
                } catch (error) {
                    console.error('Error generating barcode:', error);
                    currentBarcodeImageContainer.innerHTML = `<div class="text-red-500 text-sm">Error generating barcode: ${sanitizedBarcode}</div>`;
                }
            } else if (currentBarcodeImageContainer) {
                currentBarcodeImageContainer.innerHTML = '';
            }
            
            // Show current barcode info
            const currentBarcodeInfo = document.getElementById('current-barcode-info');
            if (currentBarcodeInfo && currentBarcode !== 'N/A') {
                currentBarcodeInfo.innerHTML = `ID: ${student.id_number} | Name: ${student.student_name.substring(0, 10)} | Year: ${student.year_level}`;
            } else if (currentBarcodeInfo) {
                currentBarcodeInfo.innerHTML = '';
            }
            
            // Reset new barcode display
            const newBarcodeDisplay = document.getElementById('new-barcode-display');
            if (newBarcodeDisplay) {
                newBarcodeDisplay.textContent = 'Click Generate to create a new barcode';
            }
            
            // Clear new barcode image
            const newBarcodeImageContainer = document.getElementById('new-barcode-image-container');
            if (newBarcodeImageContainer) {
                newBarcodeImageContainer.innerHTML = '';
            }
            
            // Clear new barcode info
            const newBarcodeInfo = document.getElementById('new-barcode-info');
            if (newBarcodeInfo) {
                newBarcodeInfo.innerHTML = '';
            }
            
            // Store student ID for the generate button
            const confirmBtn = document.getElementById('confirm-generate-barcode-btn');
            if (confirmBtn) {
                confirmBtn.setAttribute('data-student-id', studentId);
            }
            
            // Trigger modal
            const modalTrigger = document.createElement('div');
            modalTrigger.style.display = 'none';
            modalTrigger.setAttribute('data-tw-toggle', 'modal');
            modalTrigger.setAttribute('data-tw-target', '#generate-barcode-modal');
            document.body.appendChild(modalTrigger);
            modalTrigger.click();
            setTimeout(() => {
                document.body.removeChild(modalTrigger);
            }, 100);
        } else {
            showError('Error!', 'Failed to load student data.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showError('Error!', 'Failed to load student data.');
    });
};

// Confirm Generate Barcode Function
window.confirmGenerateBarcode = function(studentId) {
    // Get student data to create barcode from id_number, student_name, and year_level
    fetch(`/students/edit/${studentId}`, {
        method: 'GET',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const student = data.data;
            
            // Function to sanitize data for Code128 (supports all ASCII characters)
            function sanitizeForCode128(str) {
                if (!str) return '';
                // Code128 supports all ASCII characters (0-127), just remove control characters and invalid chars
                // Remove null bytes, control characters, and keep everything else
                let sanitized = str.toString().replace(/[\x00-\x1F\x7F]/g, ''); // Remove control characters
                return sanitized;
            }
            
            // Create barcode format - use only ID Number + Student Name (first 5 chars, no spaces)
            // Format: ID_NUMBER + FIRST_5_CHARS_OF_NAME (e.g., "2024-001JOHN" or "2024001JOHN")
            const idNumber = sanitizeForCode128(student.id_number || '');
            const studentName = sanitizeForCode128((student.student_name || '').substring(0, 5).replace(/\s+/g, '')); // First 5 chars of name, no spaces
            
            // Combine into barcode format (ID + Name only)
            const barcode = idNumber + studentName;
            
            // Show new barcode in modal
            const newBarcodeDisplay = document.getElementById('new-barcode-display');
            if (newBarcodeDisplay) {
                newBarcodeDisplay.textContent = barcode;
            }
            
            // Show new barcode image
            const newBarcodeImageContainer = document.getElementById('new-barcode-image-container');
            if (newBarcodeImageContainer) {
                // Create canvas element for barcode
                const canvasId = 'new-barcode-canvas-' + Date.now();
                newBarcodeImageContainer.innerHTML = `
                    <canvas id="${canvasId}" style="max-width: 100%; height: auto;"></canvas>
                    <div class="text-center mt-2 text-xs text-slate-500">${barcode}</div>
                `;
                
                // Generate barcode using JsBarcode
                try {
                    JsBarcode(`#${canvasId}`, barcode, {
                        format: "CODE39",
                        width: 2,
                        height: 50,
                        displayValue: false,
                        background: "#ffffff",
                        lineColor: "#000000"
                    });
                } catch (error) {
                    console.error('Error generating barcode:', error);
                    newBarcodeImageContainer.innerHTML = `<div class="text-red-500 text-sm">Error generating barcode: ${barcode}</div>`;
                }
            }
            
            // Show new barcode info
            const newBarcodeInfo = document.getElementById('new-barcode-info');
            if (newBarcodeInfo) {
                const idNumber = student.id_number || '';
                const studentNameShort = (student.student_name || '').substring(0, 10);
                const yearLevel = student.year_level || '';
                newBarcodeInfo.innerHTML = `ID: ${idNumber} | Name: ${studentNameShort} | Year: ${yearLevel}`;
            }
            
            // Update barcode for this student
            fetch(`/students/update-barcode/${studentId}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    barcode: barcode
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showSuccess('Success!', 'Barcode generated successfully!');
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                } else {
                    showError('Error!', data.message || 'Failed to generate barcode.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showError('Error!', 'Failed to generate barcode.');
            });
        } else {
            showError('Error!', 'Failed to load student data.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showError('Error!', 'Failed to load student data.');
    });
};

// Add event listener for confirm generate barcode button
document.addEventListener('DOMContentLoaded', function() {
    const confirmGenerateBtn = document.getElementById('confirm-generate-barcode-btn');
    if (confirmGenerateBtn) {
        confirmGenerateBtn.addEventListener('click', function() {
            const studentId = this.getAttribute('data-student-id');
            if (studentId) {
                confirmGenerateBarcode(studentId);
            }
        });
    }
});


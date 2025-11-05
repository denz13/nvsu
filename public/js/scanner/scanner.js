// Student Barcode Scanner JavaScript

let deviceScanner = null; // Make it accessible globally for refocusing

document.addEventListener('DOMContentLoaded', function() {
    deviceScanner = document.getElementById('deviceScanner');
    const clearScannerBtn = document.getElementById('clearScanner');
    let lastScannedCode = '';
    let lastScannedTime = 0;
    let scanTimeout = null;
    let scanBuffer = '';
    const SCAN_DELAY = 100; // Minimum delay between scans in milliseconds
    let inputCheckInterval = null;
    let lastInputValue = '';

    // No-op debug function (logs disabled)
    function debugLog() {}
    
    // Debug panel removed

    // Enhanced focus handling
    function refocusScannerInput() {
        setTimeout(() => {
            if (deviceScanner) {
                deviceScanner.focus();
                debugLog('Scanner auto-refocused');
            }
        }, 200);
    }

    // Start aggressive input monitoring for physical scanners
    function startInputMonitoring() {
        debugLog('Starting input monitoring');
        
        if (inputCheckInterval) {
            clearInterval(inputCheckInterval);
        }

        inputCheckInterval = setInterval(() => {
            if (!deviceScanner) return;
            
            const currentValue = deviceScanner.value;

            if (currentValue !== lastInputValue && currentValue.trim() !== '') {
                debugLog('Input monitoring detected change', {
                    oldValue: lastInputValue,
                    newValue: currentValue,
                    length: currentValue.length
                });

                processBarcodeScan(currentValue);
                deviceScanner.value = '';
                lastInputValue = '';

                clearInterval(inputCheckInterval);
                setTimeout(() => {
                    startInputMonitoring();
                }, 500);
            } else {
                lastInputValue = currentValue;
            }
        }, 50); // Check every 50ms
    }

    // Stop input monitoring
    function stopInputMonitoring() {
        if (inputCheckInterval) {
            clearInterval(inputCheckInterval);
            inputCheckInterval = null;
        }
    }

    // Process barcode scan with enhanced error handling
    function processBarcodeScan(barcode) {
        const currentTime = new Date().getTime();

        // Clean the barcode
        const originalBarcode = barcode;
        const trimmedBarcode = barcode.trim();
        const cleanedBarcode = trimmedBarcode.replace(/[\r\n\t]/g, '').replace(/\s+/g, '');

        debugLog('Processing barcode', {
            original: `"${originalBarcode}"`,
            cleaned: `"${cleanedBarcode}"`
        });

        // Prevent duplicate scans - stricter check
        if (cleanedBarcode === lastScannedCode && (currentTime - lastScannedTime) < SCAN_DELAY) {
            debugLog('Duplicate scan prevented (same code too soon)');
            return;
        }
        
        // Prevent if already searching
        if (isSearching) {
            debugLog('Duplicate scan prevented (search in progress)');
            return;
        }

        if (!cleanedBarcode || cleanedBarcode.length < 3) {
            debugLog('Empty or too short barcode after cleaning', cleanedBarcode);
            return;
        }

        debugLog('Valid barcode detected', cleanedBarcode);
        lastScannedCode = cleanedBarcode;
        lastScannedTime = currentTime;

        // Search for student
        searchStudent(cleanedBarcode);

        // Clear input after processing
        setTimeout(() => {
            if (deviceScanner) {
                deviceScanner.value = '';
                lastScannedCode = '';
                refocusScannerInput();
            }
        }, 1500);
    }

    // Debug panel removed
    
    // Handle barcode scan
    if (deviceScanner) {
        debugLog('DeviceScanner element found and ready');
        debugLog('Scanner input value', { currentValue: deviceScanner.value, placeholder: deviceScanner.placeholder });
        
        // Focus on input when page loads
        deviceScanner.focus();
        debugLog('Scanner input focused on page load');
        
        // Verify focus
        setTimeout(() => {
            if (document.activeElement === deviceScanner) {
                debugLog('✓ Scanner input is focused', { isFocused: true });
            } else {
                debugLog('✗ Scanner input is NOT focused', { isFocused: false, activeElement: document.activeElement?.id || 'unknown' });
            }
        }, 100);

        // Handle keypress (Enter key from physical scanners)
        deviceScanner.addEventListener('keypress', function(e) {
            debugLog('Keypress event', { key: e.key, keyCode: e.keyCode, value: this.value });
            
            if (e.key === 'Enter') {
                e.preventDefault();
                const barcode = this.value.trim();
                debugLog('✓ ENTER key detected', { barcode: barcode, length: barcode.length });
                
                if (barcode) {
                    debugLog('Processing barcode from Enter key...');
                    processBarcodeScan(barcode);
                } else {
                    debugLog('✗ Empty barcode on Enter key');
                }
            }
        });

        // Handle keydown for faster response
        deviceScanner.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.keyCode === 13) {
                e.preventDefault();
                const barcode = this.value.trim();
                if (barcode) {
                    processBarcodeScan(barcode);
                }
            }
        });

        // Handle input event
        deviceScanner.addEventListener('input', function(e) {
            scanBuffer = this.value;
            debugLog('Input event detected', { 
                value: scanBuffer, 
                length: scanBuffer.length,
                trimmed: scanBuffer.trim(),
                isEmpty: scanBuffer.trim() === ''
            });
            
            // Clear previous timeout
            if (scanTimeout) {
                clearTimeout(scanTimeout);
            }

            // For scanners that type fast without Enter, wait for pause
            if (scanBuffer.trim().length > 5) {
                debugLog('Input length > 5, setting timeout for auto-process...');
                scanTimeout = setTimeout(() => {
                    if (scanBuffer.trim() && scanBuffer.trim() !== lastScannedCode) {
                        debugLog('✓ Auto-processing after input delay', scanBuffer);
                        processBarcodeScan(scanBuffer);
                    } else {
                        debugLog('✗ Skipping auto-process', { 
                            isEmpty: !scanBuffer.trim(), 
                            isDuplicate: scanBuffer.trim() === lastScannedCode 
                        });
                    }
                }, 300);
            }
        });

        // Handle paste events (some scanners use paste)
        deviceScanner.addEventListener('paste', function(e) {
            e.preventDefault();
            const pastedData = (e.clipboardData || window.clipboardData).getData('text');
            debugLog('Paste event detected', pastedData);
            if (pastedData.trim()) {
                processBarcodeScan(pastedData);
            }
            this.value = '';
        });

        // Handle change event
        deviceScanner.addEventListener('change', function(e) {
            if (e.target.value.trim()) {
                debugLog('Change event detected', e.target.value);
                processBarcodeScan(e.target.value);
                e.target.value = '';
            }
        });

        // Focus event - start monitoring
        deviceScanner.addEventListener('focus', function() {
            debugLog('Scanner focused - starting monitoring');
            this.value = '';
            scanBuffer = '';
            lastInputValue = '';
            startInputMonitoring();
            
            // Visual feedback
            this.style.borderColor = '#10b981';
            this.style.boxShadow = '0 0 0 3px rgba(16, 185, 129, 0.1)';
        });

        // Blur event - stop monitoring
        deviceScanner.addEventListener('blur', function() {
            stopInputMonitoring();
            this.style.borderColor = '';
            this.style.boxShadow = '';
            debugLog('Scanner lost focus - stopped monitoring');
        });

        // Initialize monitoring
        startInputMonitoring();
    }

    // Refocus scanner when clicking anywhere EXCEPT interactive controls (e.g., event dropdowns, modals)
    document.addEventListener('click', function(e) {
        if (!deviceScanner) return;
        const isInsideScanner = e.target === deviceScanner || deviceScanner.contains(e.target);
        const isEventSelect = !!e.target.closest('#eventSelect');
        const isTomSelect = !!e.target.closest('.tom-select') || !!e.target.closest('.ts-dropdown') || !!e.target.closest('.ts-control');
        const isModal = !!e.target.closest('.modal');
        const isDropdown = !!e.target.closest('.dropdown') || !!e.target.closest('[data-tw-toggle="dropdown"]');
        const isFormControl = !!e.target.closest('select, input, textarea, button, label');
        const shouldSkip = isInsideScanner || isEventSelect || isTomSelect || isModal || isDropdown || isFormControl;
        if (!shouldSkip) {
            deviceScanner.focus();
        }
    });

    // Auto-focus when page loads
    window.addEventListener('load', function() {
        if (deviceScanner) {
            deviceScanner.focus();
            debugLog('✓ Page loaded - Scanner auto-focused');
            
            // Show ready status
            const searchResults = document.getElementById('search-results');
            if (searchResults) {
                searchResults.innerHTML = `
                    <div class="col-span-12 text-center py-12">
                        <div class="inline-flex items-center px-4 py-2 bg-green-100 text-green-800 rounded-lg mb-4">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-2">
                                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                <polyline points="22 4 12 14.01 9 11.01"></polyline>
                            </svg>
                            <span class="font-medium">Scanner Ready - Try typing or scanning now!</span>
                        </div>
                        <p class="text-slate-500">Check browser console (F12) for debug logs</p>
                        <p class="text-slate-400 text-xs mt-2">Or check the debug panel below the scanner input</p>
                    </div>
                `;
            }
        } else {
            debugLog('✗ ERROR: deviceScanner not found on page load');
        }
    });

    // Auto-focus when tab becomes visible
    document.addEventListener('visibilitychange', function() {
        if (!document.hidden && deviceScanner) {
            setTimeout(() => {
                deviceScanner.focus();
                debugLog('Scanner auto-focused on tab visible');
            }, 100);
        }
    });
    
    // Clear scanner
    if (clearScannerBtn) {
        clearScannerBtn.addEventListener('click', function() {
            if (deviceScanner) {
                deviceScanner.value = '';
                deviceScanner.focus();
            }
            stopInputMonitoring();
            startInputMonitoring();
            clearResults();
            debugLog('✓ Scanner cleared and reset');
        });
    }
    
    // Test helper removed
    
    // Log that scanner is ready
    debugLog('=== Scanner initialized successfully ===');
});

// Search Student Function - Prevent duplicate requests
let isSearching = false;
let lastSearchedBarcode = '';
let lastSearchTime = 0;
const SEARCH_COOLDOWN = 2000; // 2 seconds cooldown between searches

function searchStudent(barcode) {
    const searchResults = document.getElementById('search-results');
    
    // Clean the barcode - remove any extra whitespace, newlines, tabs, and special characters from scanner
    barcode = barcode.toString().trim();
    barcode = barcode.replace(/[\r\n\t]/g, ''); // Remove newlines, carriage returns, tabs
    barcode = barcode.replace(/\s+/g, ''); // Remove all whitespace
    barcode = barcode.replace(/[^\w\-]/g, ''); // Remove special chars except alphanumeric and hyphen
    
    if (!barcode) {
        showError('Error!', 'Please enter a valid barcode.');
        return;
    }
    
    // Prevent duplicate searches
    const currentTime = Date.now();
    if (isSearching) {
        debugLog('⚠ Search already in progress, skipping duplicate request');
        return;
    }
    
    if (barcode === lastSearchedBarcode && (currentTime - lastSearchTime) < SEARCH_COOLDOWN) {
        debugLog('⚠ Duplicate barcode scan prevented', {
            barcode: barcode,
            timeSinceLastSearch: currentTime - lastSearchTime
        });
        return;
    }
    
    // Set searching flag
    isSearching = true;
    lastSearchedBarcode = barcode;
    lastSearchTime = currentTime;
    
    // Show loading state
    searchResults.innerHTML = `
        <div class="col-span-12 text-center py-12">
            <div class="flex justify-center mb-4">
                <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary"></div>
            </div>
            <h5 class="text-lg font-medium mb-2">Searching...</h5>
            <p class="text-slate-500">Looking for student with barcode: <strong>${barcode}</strong></p>
        </div>
    `;
    
    // Get selected event ID
    const eventSelect = document.getElementById('eventSelect');
    const eventId = eventSelect ? eventSelect.value : null;
    
    // Search via AJAX
    fetch('/scanner/search', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            barcode: barcode,
            event_id: eventId
        })
    })
    .then(async response => {
        const text = await response.text();
        let data;
        try {
            data = JSON.parse(text);
        } catch (e) {
            console.error('JSON parse error:', e);
            console.error('Response text:', text);
            throw new Error('Invalid JSON response from server: ' + text.substring(0, 200));
        }
        return { status: response.status, data: data };
    })
    .then(result => {
        if (result.data.success && result.data.student) {
            displayStudent(result.data.student);
            
            // Show attendance message if available
            let successMessage = `Student found: ${result.data.student.student_name}`;
            if (result.data.attendance) {
                if (result.data.attendance.success) {
                    const attendance = result.data.attendance;
                    successMessage += `\n${attendance.workstate_text}: ${attendance.log_time}`;
                    showSuccess('Success!', successMessage);
                } else {
                    // Attendance save failed
                    showError('Attendance Error!', `Student found but failed to save attendance: ${result.data.attendance.message || 'Unknown error'}`);
                    console.error('Attendance save error:', result.data.attendance);
                }
            } else {
                showSuccess('Success!', successMessage);
            }
            
            // Reset searching flag
            isSearching = false;
            
            // Refocus scanner for next scan
            setTimeout(() => {
                if (deviceScanner) {
                    deviceScanner.focus();
                }
            }, 500);
        } else {
            // Show debug info if available
            let errorMessage = result.data.message || 'No student found with this barcode.';
            
            if (result.data.debug && result.data.debug.sample_barcodes) {
                console.log('Debug Info:', result.data.debug);
                debugLog('Not Found Debug', result.data.debug);
                
                // Show more helpful error message
                errorMessage += '\n\nSample barcodes in database:';
                result.data.debug.sample_barcodes.forEach(sample => {
                    console.log(`  - ${sample.barcode} (ID: ${sample.id_number}, Name: ${sample.name})`);
                });
            }
            
            showNotFound(barcode);
            showError('Not Found!', errorMessage);
            
            // Reset searching flag
            isSearching = false;
            
            // Refocus scanner even after error
            setTimeout(() => {
                if (deviceScanner) {
                    deviceScanner.focus();
                }
            }, 500);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotFound(barcode);
        
        let errorMessage = 'Failed to search student. Please try again.';
        if (error.response && error.response.status === 500) {
            errorMessage = 'Server error occurred. Please check the console for details and contact support if the issue persists.';
            console.error('500 Error Details:', error);
        }
        
        showError('Error!', errorMessage);
        
        // Reset searching flag
        isSearching = false;
    })
    .finally(() => {
        // Reset searching flag after a delay to prevent immediate duplicate
        setTimeout(() => {
            isSearching = false;
        }, 1000);
    });
}

// Display Student Function
function displayStudent(student) {
    const searchResults = document.getElementById('search-results');
    
    // Sanitize barcode for Code128 (supports all ASCII, remove only control characters)
    const sanitizedBarcode = student.barcode ? 
        student.barcode.toString().replace(/[\x00-\x1F\x7F]/g, '') : 
        '';
    
    // Generate unique canvas ID for barcode
    const barcodeCanvasId = 'barcode-canvas-' + Date.now();
    
    searchResults.innerHTML = `
        <div class="col-span-12 lg:col-span-6 xl:col-span-4">
            <div class="box">
                <div class="p-5">
                    <div class="flex items-center mb-5">
                        <div class="w-20 h-20 image-fit zoom-in rounded-full overflow-hidden mr-4">
                            <img alt="${student.student_name}" class="rounded-full" src="${student.photo ? '/' + student.photo : '/dist/images/preview-10.jpg'}">
                        </div>
                        <div>
                            <h5 class="text-base font-medium">${student.student_name}</h5>
                            <p class="text-slate-500 text-xs">${student.id_number}</p>
                            <p class="text-slate-500 text-xs">${student.year_level}</p>
                        </div>
                    </div>
                    
                    ${sanitizedBarcode ? `
                        <div class="mb-5">
                            <div class="text-slate-500 text-xs mb-2">Barcode</div>
                            <canvas id="${barcodeCanvasId}" style="max-width: 100%; height: auto;"></canvas>
                            <div class="text-center mt-2 text-xs text-slate-500">${sanitizedBarcode}</div>
                        </div>
                    ` : ''}
                    
                    <div class="grid grid-cols-2 gap-4 mt-5">
                        <div>
                            <div class="text-slate-500 text-xs">College</div>
                            <div class="text-base font-medium">${student.college ? student.college.college_name : 'N/A'}</div>
                        </div>
                        <div>
                            <div class="text-slate-500 text-xs">Program</div>
                            <div class="text-base font-medium">${student.program ? student.program.program_name : 'N/A'}</div>
                        </div>
                        <div>
                            <div class="text-slate-500 text-xs">Organization</div>
                            <div class="text-base font-medium">${student.organization ? student.organization.organization_name : 'N/A'}</div>
                        </div>
                        <div>
                            <div class="text-slate-500 text-xs">Status</div>
                            <div class="text-base font-medium">
                                ${student.status === 'active' ? '<span class="text-success">Active</span>' : '<span class="text-danger">Inactive</span>'}
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-5">
                        <div class="text-slate-500 text-xs">Address</div>
                        <div class="text-base">${student.address || 'N/A'}</div>
                    </div>
                    
                    <button class="btn btn-primary w-full mt-5" onclick="viewFullDetails(${student.id})">
                        View Complete Details
                    </button>
                </div>
            </div>
        </div>
    `;
    
    // Generate barcode using JsBarcode after HTML is inserted
    if (sanitizedBarcode) {
        setTimeout(() => {
            try {
                JsBarcode(`#${barcodeCanvasId}`, sanitizedBarcode, {
                    format: "CODE128",
                    width: 2,
                    height: 50,
                    displayValue: false,
                    background: "#ffffff",
                    lineColor: "#000000"
                });
            } catch (error) {
                console.error('Error generating barcode:', error);
            }
        }, 100);
    }
}

// Show Not Found
function showNotFound(barcode) {
    const searchResults = document.getElementById('search-results');
    searchResults.innerHTML = `
        <div class="col-span-12 text-center py-12 text-slate-500">
            <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-alert-circle mx-auto mb-4 text-slate-300">
                <circle cx="12" cy="12" r="10"></circle>
                <path d="M12 16v-4"></path>
                <path d="M12 8h.01"></path>
            </svg>
            <h5 class="text-lg font-medium mb-2">Student Not Found</h5>
            <p class="text-base mb-2">No student found with barcode: <strong>${barcode}</strong></p>
            <p class="text-xs mt-2 text-slate-400">Please check the barcode and try again</p>
            <p class="text-xs mt-1 text-slate-400">Make sure the student has a barcode generated in the Students section</p>
        </div>
    `;
}

// Clear Results
function clearResults() {
    const searchResults = document.getElementById('search-results');
    searchResults.innerHTML = `
        <div class="col-span-12 text-center py-12 text-slate-500">
            <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-scan-barcode mx-auto mb-4 text-slate-300">
                <path d="M3 7V5a2 2 0 0 1 2-2h2"/>
                <path d="M17 3h2a2 2 0 0 1 2 2v2"/>
                <path d="M21 17v2a2 2 0 0 1-2 2h-2"/>
                <path d="M7 21H5a2 2 0 0 1-2-2v-2"/>
                <path d="M7 8h8"/>
                <path d="M7 12h10"/>
                <path d="M7 16h6"/>
            </svg>
            <h5 class="text-lg font-medium mb-2">Ready to Scan</h5>
            <p>Scan a student barcode or enter it manually to view information</p>
        </div>
    `;
}

// View Full Details
window.viewFullDetails = function(studentId) {
    // Fetch complete student details
    fetch(`/scanner/details/${studentId}`, {
        method: 'GET',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.student) {
            displayStudentDetailsModal(data.student);
        } else {
            showError('Error!', 'Failed to load student details.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showError('Error!', 'Failed to load student details.');
    });
};

// Display Student Details Modal
function displayStudentDetailsModal(student) {
    const modalContent = document.getElementById('studentModalContent');
    
    // Sanitize barcode for Code128 (supports all ASCII, remove only control characters)
    const sanitizedBarcode = student.barcode ? 
        student.barcode.toString().replace(/[\x00-\x1F\x7F]/g, '') : 
        '';
    
    // Generate unique canvas ID for barcode
    const modalBarcodeCanvasId = 'modal-barcode-canvas-' + Date.now();
    
    modalContent.innerHTML = `
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div>
                <div class="w-32 h-32 mx-auto mb-4 image-fit zoom-in rounded-full overflow-hidden">
                    <img alt="${student.student_name}" class="rounded-full" src="${student.photo ? '/' + student.photo : '/dist/images/preview-10.jpg'}">
                </div>
                <div class="text-center">
                    <h3 class="text-xl font-medium mb-2">${student.student_name}</h3>
                    <p class="text-slate-500">${student.id_number}</p>
                </div>
            </div>
            <div>
                <h4 class="font-medium mb-4">Student Information</h4>
                <div class="grid grid-cols-1 gap-3">
                    <div class="flex justify-between border-b pb-2">
                        <span class="text-slate-500">ID Number:</span>
                        <span class="font-medium">${student.id_number}</span>
                    </div>
                    <div class="flex justify-between border-b pb-2">
                        <span class="text-slate-500">Year Level:</span>
                        <span class="font-medium">${student.year_level}</span>
                    </div>
                    <div class="flex justify-between border-b pb-2">
                        <span class="text-slate-500">College:</span>
                        <span class="font-medium">${student.college ? student.college.college_name : 'N/A'}</span>
                    </div>
                    <div class="flex justify-between border-b pb-2">
                        <span class="text-slate-500">Program:</span>
                        <span class="font-medium">${student.program ? student.program.program_name : 'N/A'}</span>
                    </div>
                    <div class="flex justify-between border-b pb-2">
                        <span class="text-slate-500">Organization:</span>
                        <span class="font-medium">${student.organization ? student.organization.organization_name : 'N/A'}</span>
                    </div>
                    <div class="flex justify-between border-b pb-2">
                        <span class="text-slate-500">Address:</span>
                        <span class="font-medium">${student.address || 'N/A'}</span>
                    </div>
                    <div class="flex justify-between border-b pb-2">
                        <span class="text-slate-500">Status:</span>
                        <span class="font-medium">
                            ${student.status === 'active' ? '<span class="text-success">Active</span>' : '<span class="text-danger">Inactive</span>'}
                        </span>
                    </div>
                </div>
            </div>
        </div>
        ${sanitizedBarcode ? `
            <div class="mt-6 text-center">
                <h4 class="font-medium mb-4">Barcode</h4>
                <canvas id="${modalBarcodeCanvasId}" style="max-width: 100%; height: auto; margin: 0 auto;"></canvas>
                <div class="text-center mt-2 text-slate-500">${sanitizedBarcode}</div>
            </div>
        ` : ''}
    `;
    
    // Generate barcode using JsBarcode after HTML is inserted
    if (sanitizedBarcode) {
        setTimeout(() => {
            try {
                JsBarcode(`#${modalBarcodeCanvasId}`, sanitizedBarcode, {
                    format: "CODE39",
                    width: 2,
                    height: 50,
                    displayValue: false,
                    background: "#ffffff",
                    lineColor: "#000000"
                });
            } catch (error) {
                console.error('Error generating barcode:', error);
            }
        }, 100);
    }
    
    // Trigger modal
    const modalTrigger = document.createElement('div');
    modalTrigger.style.display = 'none';
    modalTrigger.setAttribute('data-tw-toggle', 'modal');
    modalTrigger.setAttribute('data-tw-target', '#studentDetailsModal');
    document.body.appendChild(modalTrigger);
    modalTrigger.click();
    setTimeout(() => {
        document.body.removeChild(modalTrigger);
    }, 100);
};


// Toast Notification Helper using dynamic toast component
window.showToast = function(type, title, message) {
    // Generate unique ID for toast
    const toastId = 'toast-' + Date.now();
    
    // Get color class based on type
    let colorClass = 'text-success';
    let iconName = 'check-circle';
    
    switch(type) {
        case 'success':
            colorClass = 'text-success';
            iconName = 'check-circle';
            break;
        case 'error':
        case 'danger':
            colorClass = 'text-danger';
            iconName = 'x-circle';
            break;
        case 'warning':
            colorClass = 'text-warning';
            iconName = 'alert-triangle';
            break;
        case 'info':
            colorClass = 'text-primary';
            iconName = 'info';
            break;
        case 'pending':
            colorClass = 'text-pending';
            iconName = 'clock';
            break;
    }
    
    // Create toast HTML
    const toastHTML = `
        <div id="${toastId}" class="flex items-center py-3 px-4 mb-2 bg-white dark:bg-darkmode-600 dark:text-slate-300 rounded-md shadow-lg border border-slate-200/50">
            <i class="${colorClass}" data-lucide="${iconName}"></i>
            <div class="ml-4 mr-4">
                <div class="font-medium">${title}</div>
                <div class="text-slate-500 mt-1">${message}</div>
            </div>
            <button class="ml-auto" onclick="document.getElementById('${toastId}').remove()">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>
    `;
    
    // Create or get toast container
    let toastContainer = document.getElementById('toast-container');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.id = 'toast-container';
        toastContainer.style.cssText = 'position: fixed; top: 20px; right: 20px; z-index: 9999; max-width: 400px;';
        document.body.appendChild(toastContainer);
    }
    
    // Add toast to container
    toastContainer.insertAdjacentHTML('beforeend', toastHTML);
    
    // Initialize Lucide icons
    if (typeof lucide !== 'undefined' && lucide.icons) {
        try {
            lucide.createIcons({
                icons: lucide.icons,
                "stroke-width": 1.5,
                nameAttr: "data-lucide",
            });
        } catch (e) {
            console.log('Lucide icons initialization skipped:', e.message);
        }
    }
    
    // Auto remove after 3 seconds
    setTimeout(() => {
        const toastElement = document.getElementById(toastId);
        if (toastElement) {
            toastElement.style.transition = 'opacity 0.3s';
            toastElement.style.opacity = '0';
            setTimeout(() => {
                toastElement.remove();
            }, 300);
        }
    }, 3000);
};

// Convenience functions
window.showSuccess = function(title, message) {
    window.showToast('success', title, message);
};

window.showError = function(title, message) {
    window.showToast('error', title, message);
};

window.showWarning = function(title, message) {
    window.showToast('warning', title, message);
};

window.showInfo = function(title, message) {
    window.showToast('info', title, message);
};

window.showPending = function(title, message) {
    window.showToast('pending', title, message);
};

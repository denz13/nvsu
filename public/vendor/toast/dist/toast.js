// Custom function to show dynamic Notyf messages
function showToast(title, message, type, iconClass) {
    const notyf = new Notyf();

    // Show the toast with the provided title and message
    notyf.open({
        type: type,
        message: `<div class="font-medium">${title}</div><div class="text-slate-500 mt-1">${message}</div>`,
        duration: 3000, // Time in milliseconds before the toast auto-closes
        position: { x: 'right', y: 'top' }, // Adjust position as needed
        icon: {
            className: iconClass,
        },
        zIndex: 10000, // Set a higher z-index value to bring the notification to the front
    });
}

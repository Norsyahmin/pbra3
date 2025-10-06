// Enhanced notification debugging for homepage
console.log('Homepage notification debug loaded');

document.addEventListener('DOMContentLoaded', function () {
    console.log('=== HOMEPAGE NOTIFICATION DEBUGGING ===');

    // Check if navbar is loaded
    const navbar = document.getElementById('navBarComponent');
    console.log('Navbar component found:', !!navbar);

    // Check if notification elements exist
    const notificationBtn = document.querySelector('.notification-btn');
    const notificationContainer = document.getElementById('notification-container');
    const notificationList = document.getElementById('notification-list');
    const notificationDot = document.getElementById('notification-dot');

    console.log('Notification button found:', !!notificationBtn);
    console.log('Notification container found:', !!notificationContainer);
    console.log('Notification list found:', !!notificationList);
    console.log('Notification dot found:', !!notificationDot);

    if (notificationBtn) {
        console.log('Notification button onclick:', notificationBtn.getAttribute('onclick'));

        // Test notification button click
        notificationBtn.addEventListener('click', function () {
            console.log('Notification button clicked!');
            console.log('Container display before:', notificationContainer ? notificationContainer.style.display : 'N/A');
        });
    }

    // Check for JavaScript errors
    window.addEventListener('error', function (e) {
        if (e.error && e.error.stack && e.error.stack.includes('notification')) {
            console.error('Notification-related error:', e.error);
        }
    });

    // Test if toggleNotifications function exists
    console.log('toggleNotifications function exists:', typeof toggleNotifications !== 'undefined');

    // Test if fetchNotifications function exists
    console.log('fetchNotifications function exists:', typeof fetchNotifications !== 'undefined');

    // Check for conflicting global functions
    const globalFunctions = ['openModal', 'closeModal', 'toggleNotifications'];
    globalFunctions.forEach(func => {
        if (window[func]) {
            console.log(`Global function ${func} exists:`, typeof window[func]);
        }
    });

    // Monitor network requests for notification endpoint
    const originalFetch = window.fetch;
    window.fetch = function (...args) {
        const url = args[0];
        if (typeof url === 'string' && url.includes('process_notification.php')) {
            console.log('Notification fetch request:', url);
            return originalFetch.apply(this, args).then(response => {
                console.log('Notification response status:', response.status);
                return response;
            }).catch(error => {
                console.error('Notification fetch error:', error);
                throw error;
            });
        }
        return originalFetch.apply(this, args);
    };

    // Test notification loading after 2 seconds
    setTimeout(() => {
        console.log('Testing notification loading...');
        if (typeof toggleNotifications !== 'undefined') {
            console.log('Attempting to trigger notification loading');
            // Don't actually trigger it, just log that we could
        } else {
            console.error('toggleNotifications function not available');
        }
    }, 2000);
});
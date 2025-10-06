// CSS Conflict Detection for Notifications
console.log('CSS Conflict Detection loaded');

document.addEventListener('DOMContentLoaded', function () {
    console.log('=== CSS CONFLICT DETECTION ===');

    // Check notification elements
    const notificationContainer = document.getElementById('notification-container');
    const notificationBtn = document.querySelector('.notification-btn');
    const notificationList = document.getElementById('notification-list');
    const notificationDot = document.getElementById('notification-dot');

    if (notificationContainer) {
        console.log('=== NOTIFICATION CONTAINER STYLES ===');
        const styles = window.getComputedStyle(notificationContainer);
        console.log('Position:', styles.position);
        console.log('Top:', styles.top);
        console.log('Right:', styles.right);
        console.log('Width:', styles.width);
        console.log('Height:', styles.height);
        console.log('Z-Index:', styles.zIndex);
        console.log('Display:', styles.display);
        console.log('Background:', styles.backgroundColor);
        console.log('Border:', styles.border);
        console.log('Box Shadow:', styles.boxShadow);
        console.log('Overflow Y:', styles.overflowY);
        console.log('Border Radius:', styles.borderRadius);

        // Check all CSS rules that apply to this element
        const allRules = [];
        for (let sheet of document.styleSheets) {
            try {
                for (let rule of sheet.cssRules || sheet.rules) {
                    if (rule.selectorText && rule.selectorText.includes('notification-container')) {
                        allRules.push({
                            selector: rule.selectorText,
                            cssText: rule.cssText,
                            href: sheet.href || 'inline'
                        });
                    }
                }
            } catch (e) {
                console.warn('Cannot access stylesheet:', sheet.href, e);
            }
        }
        console.log('All CSS rules affecting notification-container:', allRules);
    }

    if (notificationBtn) {
        console.log('=== NOTIFICATION BUTTON STYLES ===');
        const btnStyles = window.getComputedStyle(notificationBtn);
        console.log('Position:', btnStyles.position);
        console.log('Font Size:', btnStyles.fontSize);
        console.log('Color:', btnStyles.color);
        console.log('Background:', btnStyles.backgroundColor);
        console.log('Border:', btnStyles.border);
        console.log('Cursor:', btnStyles.cursor);
    }

    if (notificationDot) {
        console.log('=== NOTIFICATION DOT STYLES ===');
        const dotStyles = window.getComputedStyle(notificationDot);
        console.log('Position:', dotStyles.position);
        console.log('Top:', dotStyles.top);
        console.log('Right:', dotStyles.right);
        console.log('Background:', dotStyles.backgroundColor);
        console.log('Color:', dotStyles.color);
        console.log('Width:', dotStyles.width);
        console.log('Height:', dotStyles.height);
        console.log('Display:', dotStyles.display);
        console.log('Z-Index:', dotStyles.zIndex);
    }

    // Check for CSS conflicts by looking at all loaded stylesheets
    console.log('=== LOADED STYLESHEETS ===');
    for (let i = 0; i < document.styleSheets.length; i++) {
        const sheet = document.styleSheets[i];
        console.log(`Sheet ${i}:`, sheet.href || 'inline', sheet.disabled ? '(disabled)' : '(enabled)');
    }

    // Test notification visibility
    setTimeout(() => {
        console.log('=== NOTIFICATION VISIBILITY TEST ===');
        if (notificationContainer) {
            const rect = notificationContainer.getBoundingClientRect();
            console.log('Container position on screen:', {
                top: rect.top,
                right: rect.right,
                bottom: rect.bottom,
                left: rect.left,
                width: rect.width,
                height: rect.height
            });
            console.log('Is visible:', rect.width > 0 && rect.height > 0);
            console.log('Is in viewport:', rect.top >= 0 && rect.left >= 0 && rect.bottom <= window.innerHeight && rect.right <= window.innerWidth);
        }
    }, 1000);
});
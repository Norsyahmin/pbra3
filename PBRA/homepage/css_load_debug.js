// CSS Load Order Detection
(function () {
    const loadTimes = [];
    const originalCreateElement = document.createElement;

    document.createElement = function (tagName) {
        const element = originalCreateElement.call(this, tagName);

        if (tagName.toLowerCase() === 'link' || tagName.toLowerCase() === 'style') {
            element.addEventListener('load', function () {
                const href = this.href || 'inline-style';
                loadTimes.push({
                    type: tagName,
                    href: href,
                    time: new Date().toISOString()
                });
                console.log('CSS loaded:', href);

                if (href.includes('navbar.css')) {
                    console.log('✅ Navbar CSS loaded - notification styles should be available');
                }
                if (href.includes('homepage.css')) {
                    console.log('⚠️ Homepage CSS loaded - check for conflicts');
                }
            });
        }

        return element;
    };

    // Also monitor existing stylesheets
    document.addEventListener('DOMContentLoaded', function () {
        console.log('=== CSS LOAD ORDER ANALYSIS ===');
        const sheets = Array.from(document.styleSheets);
        sheets.forEach((sheet, index) => {
            const href = sheet.href || 'inline';
            console.log(`${index + 1}. ${href}`);

            if (href.includes('homepage.css')) {
                console.log('   ⚠️  Homepage CSS may override notification styles');
            }
            if (href.includes('navbar.css')) {
                console.log('   ✅ Navbar CSS contains notification styles');
            }
        });

        console.log('Load times:', loadTimes);
    });
})();
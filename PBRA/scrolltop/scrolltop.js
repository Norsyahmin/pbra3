document.addEventListener('DOMContentLoaded', function () {
    // Prefer the button provided by the PHP include (#scrollTopBtn).
    // If it's not found, create a fallback button and append to the body.
    let scrollToTopButton = document.getElementById('scrollTopBtn');
    if (!scrollToTopButton) {
        scrollToTopButton = document.createElement('button');
        scrollToTopButton.id = 'scrollTopBtn';
        scrollToTopButton.classList.add('scroll-top-button');
        scrollToTopButton.innerHTML = '&uarr;'; // Up arrow character for the button
        document.body.appendChild(scrollToTopButton);
    }

    const SHOW_CLASS = 'show';

    // Show/hide the button based on scroll position
    function updateVisibility() {
        // Use pageYOffset for broader compatibility
        const scrolled = window.pageYOffset || document.documentElement.scrollTop;
        if (scrolled > 200) {
            scrollToTopButton.classList.add(SHOW_CLASS);
        } else {
            scrollToTopButton.classList.remove(SHOW_CLASS);
        }
    }

    window.addEventListener('scroll', updateVisibility, { passive: true });
    // Initial check in case the page loads scrolled (anchor link / deep link)
    updateVisibility();

    // Scroll to top when the button is clicked
    scrollToTopButton.addEventListener('click', function () {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });
});

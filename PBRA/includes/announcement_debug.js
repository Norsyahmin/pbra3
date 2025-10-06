// Announcement Debug Helper
console.log('Announcement debug script loaded');

// Check for common issues
document.addEventListener('DOMContentLoaded', function () {
    console.log('DOM loaded, checking announcement carousel...');

    // Check if carousel elements exist
    const carousel = document.querySelector('.announcement-carousel');
    const track = document.querySelector('.carousel-track');
    const slides = document.querySelectorAll('.announcement-slide');
    const dots = document.querySelectorAll('.carousel-dots .dot');

    console.log('Carousel found:', !!carousel);
    console.log('Track found:', !!track);
    console.log('Number of slides:', slides.length);
    console.log('Number of dots:', dots.length);

    // Check for images with loading issues
    const images = document.querySelectorAll('.announcement-slide img');
    images.forEach((img, index) => {
        console.log(`Image ${index + 1} src:`, img.src);

        img.onload = function () {
            console.log(`Image ${index + 1} loaded successfully`);
        };

        img.onerror = function () {
            console.error(`Image ${index + 1} failed to load:`, img.src);
            img.style.display = 'none';
        };
    });

    // Check if carousel buttons work
    const leftBtn = document.querySelector('.carousel-btn.left');
    const rightBtn = document.querySelector('.carousel-btn.right');

    if (leftBtn) {
        leftBtn.addEventListener('click', function () {
            console.log('Left carousel button clicked');
        });
    }

    if (rightBtn) {
        rightBtn.addEventListener('click', function () {
            console.log('Right carousel button clicked');
        });
    }

    // Log any JavaScript errors
    window.addEventListener('error', function (e) {
        console.error('JavaScript error in announcement section:', e.error);
    });
});
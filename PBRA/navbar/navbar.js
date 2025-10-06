// --- Navigation Bar Component Logic ---
const navBar = (function () {
    // Get DOM elements
    const sidebar = document.getElementById("sidebar");
    const sidebarOverlay = document.getElementById("sidebarOverlay");
    const topbar = document.getElementById("topbar");
    // Primary content container used for applying the blurred class. Some pages
    // didn't include #content (older pages use .content-body or .main), so try
    // sensible fallbacks to keep blur behavior consistent across the site.
    const content = document.getElementById("content") || document.querySelector('.content-body') || document.querySelector('.main') || null;
    const searchOverlay = document.getElementById("searchOverlay");
    const popupSearch = document.getElementById("popupSearch");
    const topSearch = document.getElementById("topSearch");

    // user menu elements (may be null if component not present)
    const userMenu = document.getElementById("userMenu");
    const userDropdown = userMenu ? userMenu.querySelector(".user-dropdown") : null;

    let lastScrollTop = 0;
    // Toggle sidebar visibility
    function toggleSidebar(event) {
        event && event.stopPropagation && event.stopPropagation();
        if (!sidebar || !sidebarOverlay) return;
        sidebar.classList.toggle("active");
        sidebarOverlay.classList.toggle("active");
    }

    // Close sidebar
    function closeSidebar() {
        if (!sidebar || !sidebarOverlay) return;
        sidebar.classList.remove("active");
        sidebarOverlay.classList.remove("active");
    }

    // Open search
    function openSearch() {
        if (!searchOverlay || !popupSearch) return;
        searchOverlay.classList.add("active");
        if (content) content.classList.add("blurred");
        setTimeout(() => popupSearch.focus(), 100);
    }

    // Close search
    function closeSearch(event) {
        if (!searchOverlay) return;
        // If the click is on the search overlay itself, or the close button, close it.
        if (
            event.target === searchOverlay ||
            (event.target && event.target.classList && event.target.classList.contains("search-close"))
        ) {
            searchOverlay.classList.remove("active");
            if (content) content.classList.remove("blurred");
            if (topSearch) topSearch.blur();
        }
    }

    // Toggle user dropdown menu
    function toggleUserMenu(event) {
        if (!userMenu) return;
        event && event.stopPropagation && event.stopPropagation();
        const isOpen = userMenu.classList.toggle("open");
        try { userMenu.setAttribute("aria-expanded", isOpen ? "true" : "false"); } catch (e) { }
    }

    // Close user menu
    function closeUserMenu() {
        if (!userMenu) return;
        userMenu.classList.remove("open");
        try { userMenu.setAttribute("aria-expanded", "false"); } catch (e) { }
    }

    // Close user menu on outside click
    document.addEventListener("click", function (e) {
        if (!userMenu) return;
        if (!userMenu.contains(e.target)) {
            closeUserMenu();
        }
    });

    // Keyboard escape for search and user menu
    document.addEventListener("keydown", function (e) {
        if (e.key === "Escape") {
            if (searchOverlay && searchOverlay.classList.contains("active")) {
                closeSearch({ target: searchOverlay });
            }
            if (userMenu && userMenu.classList.contains("open")) {
                closeUserMenu();
            }
        }
    });

    // Scroll behavior for topbar
    if (topbar) {
        window.addEventListener("scroll", function () {
            let st = window.pageYOffset || document.documentElement.scrollTop;
            if (st > lastScrollTop) {
                topbar.style.top = "-70px";
                closeSidebar();
            } else {
                topbar.style.top = "0";
            }
            lastScrollTop = st <= 0 ? 0 : st;
        });
    }

    // Return public methods
    return {
        toggleSidebar: toggleSidebar,
        closeSidebar: closeSidebar,
        openSearch: openSearch,
        closeSearch: closeSearch,
        toggleUserMenu: toggleUserMenu,
        closeUserMenu: closeUserMenu
    };
})();

// Notifications (robust implementation)
// Resolve a base URL relative to the loaded navbar script so AJAX paths work
function _resolveNotificationsUrl() {
    try {
        const script = Array.from(document.getElementsByTagName('script')).find(s => s.src && s.src.indexOf('navbar.js') !== -1);
        const base = script ? new URL(script.src, location.href).href.replace(/\/navbar\/navbar\.js(?:\?.*)?$/, '/') : location.href;
        // notification endpoint relative to the navbar directory
        const url = new URL('../homepage/process_notification.php', base).href;
        console.log('Resolved notification URL:', url);
        return url;
    } catch (e) {
        console.error('URL resolution error:', e);
        const fallbackUrl = '../homepage/process_notification.php';
        console.log('Using fallback notification URL:', fallbackUrl);
        return fallbackUrl;
    }
}

async function fetchNotifications() {
    const url = _resolveNotificationsUrl();
    let data;
    try {
        const res = await fetch(url, {
            cache: 'no-store',
            credentials: 'same-origin',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        });
        if (!res.ok) throw new Error('Network response was not ok: ' + res.status);
        const responseText = await res.text();

        // Try to parse as JSON
        try {
            data = JSON.parse(responseText);
        } catch (parseError) {
            console.error('Invalid JSON response:', responseText);
            throw new Error('Server returned invalid JSON');
        }
    } catch (err) {
        console.error('Failed to load notifications:', err);
        // Show error in notification list
        const notificationList = document.getElementById('notification-list');
        if (notificationList) {
            notificationList.innerHTML = '<li style="color: #dc3545;">Failed to load notifications</li>';
        }
        return;
    }

    const notificationList = document.getElementById('notification-list');
    const notificationDot = document.getElementById('notification-dot');
    if (!notificationList || !notificationDot) {
        console.error('Notification elements not found in DOM');
        return;
    }

    // clear existing items but preserve header and layout
    notificationList.innerHTML = '';

    const unread = Number(data.unreadCount) || 0;
    if (unread > 0) {
        notificationDot.style.display = 'block';
        notificationDot.textContent = String(unread);
    } else {
        notificationDot.style.display = 'none';
    }

    const list = Array.isArray(data.notifications) ? data.notifications : [];
    if (list.length === 0) {
        const li = document.createElement('li');
        li.textContent = 'No new notifications';
        li.style.color = '#6c757d';
        li.style.fontStyle = 'italic';
        notificationList.appendChild(li);
        return;
    }

    list.forEach(n => {
        const li = document.createElement('li');
        li.className = 'notification-item';
        // be defensive about fields (some backends may use 'msg' or 'message')
        const message = n.message || n.msg || n.text || '';
        const time = n.time || n.created_at || '';

        if (n.url && n.url !== '#') {
            const a = document.createElement('a');
            a.href = n.url;
            a.style.textDecoration = 'none';
            a.style.color = 'inherit';
            a.innerHTML = `${message} ${time ? `<br><small style="color:#666">${time}</small>` : ''}`;
            li.appendChild(a);
        } else {
            li.innerHTML = `${message} ${time ? `<br><small style="color:#666">${time}</small>` : ''}`;
        }

        notificationList.appendChild(li);
    });
}

async function markNotificationsRead() {
    const url = _resolveNotificationsUrl();
    try {
        const res = await fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'mark_read=true',
            credentials: 'same-origin'
        });
        if (res.ok) {
            const dot = document.getElementById('notification-dot');
            if (dot) dot.style.display = 'none';
        }
    } catch (e) {
        console.error('Failed to mark notifications read:', e);
    }
}

// Hide notification container when clicking outside; guard nulls
document.addEventListener('click', function (event) {
    const container = document.getElementById('notification-container');
    const button = document.querySelector('.notification-btn');
    if (!container || !button) return;
    if (!container.contains(event.target) && !button.contains(event.target)) {
        container.style.display = 'none';
    }
});

// Toggle and fetch when opened
function toggleNotifications() {
    const container = document.getElementById('notification-container');
    const button = document.querySelector('.notification-btn');

    if (!container) {
        console.error('Notification container not found');
        return;
    }

    const isOpen = container.style.display === 'block';
    if (isOpen) {
        container.style.display = 'none';
        if (button) button.setAttribute('aria-expanded', 'false');
    } else {
        container.style.display = 'block';
        if (button) button.setAttribute('aria-expanded', 'true');

        // Add loading indicator
        const notificationList = document.getElementById('notification-list');
        if (notificationList) {
            notificationList.innerHTML = '<li style="color: #6c757d; font-style: italic;">Loading notifications...</li>';
        }

        // fetch latest notifications and mark them read in the background
        fetchNotifications().then(() => {
            // mark as read shortly after opening (non-blocking)
            setTimeout(() => markNotificationsRead(), 400);
        }).catch(err => {
            console.error('Error in notification flow:', err);
        });
    }
}

// --- SEARCH FUNCTIONALITY ---
let searchData = [];
let searchLoadError = false;
let searchDebounceTimer = null;
const DEBOUNCE_DELAY = 200;

// Utility debounce wrapper
function debounce(fn, delay) {
    return function (...args) {
        clearTimeout(searchDebounceTimer);
        searchDebounceTimer = setTimeout(() => fn.apply(this, args), delay);
    };
}

// Load search data from server
async function loadSearchData() {
    try {
        const res = await fetch("../search/search_data.php", { cache: "no-store" });
        if (!res.ok) throw new Error("Failed to load search data");
        const data = await res.json();
        // support either arrays or properties users/features
        const users = Array.isArray(data.users) ? data.users : (Array.isArray(data.usersList) ? data.usersList : []);
        const features = Array.isArray(data.features) ? data.features : (Array.isArray(data.items) ? data.items : []);
        searchData = [...users, ...features].filter(Boolean);
        searchLoadError = false;
    } catch (e) {
        console.error("Search data load error:", e);
        searchData = [];
        searchLoadError = true;
    }
}

// Ensure a .search-results container exists near the input; return it
function ensureResultsContainer(input) {
    if (!input) return null;
    // attach the results box to the input's parent (not the input element itself)
    const parent = input.parentElement || document.body;
    // ensure parent is positioned so absolute box is anchored correctly
    if (parent && getComputedStyle(parent).position === 'static') {
        parent.style.position = 'relative';
    }
    let box = parent.querySelector(".search-results");
    if (!box) {
        box = document.createElement("div");
        box.className = "search-results";
        box.style.display = "none";
        box.style.position = "absolute";
        box.style.zIndex = "9999";
        box.style.maxHeight = "320px";
        box.style.overflowY = "auto";
        // base visual styles moved to CSS; only set basic spacing here
        box.style.padding = "6px";
        // append to parent (so position: absolute anchors to parent)
        parent.appendChild(box);
    }
    return box;
}

// Render results for a given input and list
function renderResults(input, results, query) {
    const box = ensureResultsContainer(input);
    if (!box) return;
    box.innerHTML = "";

    // position & size the box under the input for consistent layout
    try {
        // anchor relative to the input's offsetParent (the parent we appended to)
        const parent = input.parentElement || input.offsetParent || document.body;
        // ensure parent positioning (ensureResultsContainer already set position:relative)
        // compute left/top relative to parent using offset values
        let left = input.offsetLeft;
        let top = input.offsetTop + input.offsetHeight + 6;
        // when input is inside nested positioned elements, accumulate offsets until parent
        let el = input;
        while (el && el !== parent) {
            left += el.offsetLeft || 0;
            top += el.offsetTop || 0;
            el = el.offsetParent;
            if (!el) break;
        }
        // set exact width to match input
        const width = input.offsetWidth;
        box.style.left = `${left}px`;
        box.style.top = `${top}px`;
        box.style.width = `${width}px`;
        box.style.boxSizing = 'border-box';
        // match input border radius for consistent rounded look
        const br = window.getComputedStyle(input).borderRadius;
        if (br) box.style.borderRadius = br;

        // NEW: expose input height to CSS so result rows can match it
        const inputH = input.offsetHeight || parseInt(window.getComputedStyle(input).lineHeight) || 36;
        box.style.setProperty('--search-input-height', `${inputH}px`);
    } catch (e) {
        // ignore positioning errors and let CSS fallback
        console.warn("search dropdown positioning failed", e);
    }

    if (query.length === 0) {
        box.style.display = "none";
        return;
    }

    if (searchLoadError) {
        const err = document.createElement("div");
        err.className = "no-result";
        err.textContent = "Search not available";
        box.appendChild(err);
        box.style.display = "block";
        return;
    }

    if (!results || results.length === 0) {
        const no = document.createElement("div");
        no.className = "no-result";
        no.textContent = "No results found";
        box.appendChild(no);
        box.style.display = "block";
        return;
    }

    results.forEach(item => {
        const div = document.createElement("div");
        div.className = "result-item";
        // keep visual tweaks in CSS; only small event hooks here
        div.addEventListener("mouseenter", () => div.classList.add("hover"));
        div.addEventListener("mouseleave", () => div.classList.remove("hover"));

        const icon = document.createElement("i");
        const iconClass = item.type === "user" ? "fas fa-user" : (item.icon ? `fas fa-${item.icon}` : "fas fa-link");
        icon.className = iconClass + " result-icon";

        const text = document.createElement("span");
        text.textContent = item.name || item.title || "Untitled";
        text.className = "result-text";

        div.appendChild(icon);
        div.appendChild(text);

        div.addEventListener("click", () => {
            if (item.type === "user" && item.id) {
                window.location.href = `../profile/profile.php?id=${encodeURIComponent(item.id)}`;
            } else if (item.url) {
                window.location.href = item.url;
            }
        });

        box.appendChild(div);
    });

    box.style.display = "block";
}

// liveSearch handler accepts event or input element
function liveSearchEventHandler(e) {
    const input = e?.target || document.getElementById("search") || document.getElementById("topSearch") || document.getElementById("popupSearch");
    if (!input) return;
    const query = (input.value || "").trim().toLowerCase();
    if (query.length === 0) {
        renderResults(input, [], query);
        return;
    }

    const filtered = searchData.filter(item => {
        const name = (item.name || item.title || "").toString().toLowerCase();
        return name.includes(query);
    }).slice(0, 20); // limit results

    renderResults(input, filtered, query);
}

const liveSearchDebounced = debounce(liveSearchEventHandler, DEBOUNCE_DELAY);

// Initialize search wiring
function initializeSearch() {
    loadSearchData();

    const inputs = Array.from(document.querySelectorAll("#search, #topSearch, #popupSearch")).filter(Boolean);
    if (inputs.length === 0) {
        // no visible inputs now; try to attach later when DOM updates
        document.addEventListener("DOMContentLoaded", initializeSearch, { once: true });
        return;
    }

    inputs.forEach(input => {
        // ensure per-input results container exists
        ensureResultsContainer(input);
        // attach input listener
        input.removeEventListener("input", liveSearchDebounced);
        input.addEventListener("input", liveSearchDebounced);
        // show popup search overlay when focusing topSearch behavior remains handled elsewhere
    });

    // hide results when clicking outside any results or input
    document.addEventListener("click", function (ev) {
        inputs.forEach(input => {
            const box = ensureResultsContainer(input);
            if (!box) return;
            if (!box.contains(ev.target) && ev.target !== input) {
                box.style.display = "none";
            }
        });
    });
}

// call initialize on load
if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", initializeSearch);
} else {
    initializeSearch();
}

// Initialize notifications on page load
document.addEventListener('DOMContentLoaded', function () {
    // Check if notification elements exist
    const notificationBtn = document.querySelector('.notification-btn');
    const notificationContainer = document.getElementById('notification-container');

    if (notificationBtn && notificationContainer) {
        console.log('Notification system initialized');

        // Pre-fetch notification count on page load (optional)
        // Uncomment if you want to update the count immediately on page load
        // fetchNotifications();
    } else {
        console.warn('Notification elements not found on page');
    }
});


/**
 * TicketMaster.LT — Main JS
 * Sidebar toggle, AJAX helpers, UI utilities
 */

'use strict';

// ── Sidebar Toggle ─────────────────────────────────────────────
const sidebar  = document.getElementById('sidebar');
const mainWrap = document.getElementById('main');
const overlay  = document.getElementById('sidebar-overlay');

function isMobile() {
    return window.innerWidth < 992;
}

function toggleSidebar() {
    if (isMobile()) {
        sidebar.classList.toggle('show');
        overlay.classList.toggle('show');
    } else {
        sidebar.classList.toggle('hide');
        mainWrap.classList.toggle('sidebar-hide');
        // Persist state
        localStorage.setItem('sidebarHidden', sidebar.classList.contains('hide') ? '1' : '0');
    }
}

// Restore sidebar state on desktop
(function restoreSidebar() {
    if (!isMobile() && localStorage.getItem('sidebarHidden') === '1') {
        sidebar?.classList.add('hide');
        mainWrap?.classList.add('sidebar-hide');
    }
})();

// Burger button
document.querySelectorAll('.burger-btn').forEach(btn => {
    btn.addEventListener('click', toggleSidebar);
});

// Sidebar close button (mobile)
document.querySelectorAll('.sidebar-hide-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        sidebar.classList.remove('show');
        overlay.classList.remove('show');
    });
});

// Overlay click closes sidebar on mobile
overlay?.addEventListener('click', () => {
    sidebar.classList.remove('show');
    overlay.classList.remove('show');
});

// ── Submenu Toggle ─────────────────────────────────────────────
document.querySelectorAll('.menu .has-sub > a').forEach(link => {
    link.addEventListener('click', function (e) {
        e.preventDefault();
        const parent = this.closest('li');
        // Close other open submenus
        document.querySelectorAll('.menu .has-sub.open').forEach(item => {
            if (item !== parent) item.classList.remove('open');
        });
        parent.classList.toggle('open');
    });
});

// Keep submenu open if a child is active
document.querySelectorAll('.menu .submenu .active').forEach(item => {
    item.closest('.has-sub')?.classList.add('open');
});

// ── Active menu item based on URL ──────────────────────────────
(function highlightActiveMenu() {
    const currentPath = window.location.pathname;
    document.querySelectorAll('.menu a[href]').forEach(link => {
        const href = link.getAttribute('href');
        if (href && currentPath.endsWith(href) && href !== '/') {
            link.closest('li')?.classList.add('active');
            link.closest('.has-sub')?.classList.add('open');
        }
    });
})();

// ── AJAX CSRF helper ───────────────────────────────────────────
const CSRF_TOKEN_NAME = document.querySelector('meta[name="csrf-token-name"]')?.content || 'csrf_token';

function getCsrfHash() {
    return document.querySelector('meta[name="csrf-token"]')?.content || '';
}

/**
 * Perform an AJAX request with CSRF token automatically included.
 *
 * @param {string} url
 * @param {object} options   fetch() options (method, body, etc.)
 * @returns {Promise<any>}
 */
async function tmAjax(url, options = {}) {
    const headers = {
        'X-Requested-With': 'XMLHttpRequest',
        'Accept':           'application/json',
        ...options.headers,
    };

    // For non-GET requests attach CSRF (read fresh on every call)
    if (options.method && options.method.toUpperCase() !== 'GET') {
        const csrfHash = getCsrfHash();
        if (options.body instanceof FormData) {
            options.body.append(CSRF_TOKEN_NAME, csrfHash);
        } else {
            headers['Content-Type'] = headers['Content-Type'] || 'application/json';
            headers['X-CSRF-TOKEN'] = csrfHash;
        }
    }

    const response = await fetch(url, { ...options, headers });

    // Refresh CSRF token from response header
    const newToken = response.headers.get('X-CSRF-TOKEN');
    if (newToken) {
        document.querySelector('meta[name="csrf-token"]')?.setAttribute('content', newToken);
    }

    if (!response.ok) {
        const err = await response.json().catch(() => ({ message: 'Request failed' }));
        const error = new Error(err.message || `HTTP ${response.status}`);
        error.errors = err.errors || null;
        throw error;
    }

    return response.json();
}

// ── DataTables defaults ────────────────────────────────────────
if (typeof $.fn !== 'undefined' && typeof $.fn.DataTable !== 'undefined') {
    $.extend(true, $.fn.dataTable.defaults, {
        pageLength:  10,
        lengthMenu:  [[10, 25, 50, -1], [10, 25, 50, 'All']],
        language: {
            search:      '',
            searchPlaceholder: 'Search...',
            lengthMenu:  '_MENU_ per page',
            info:        'Showing _START_–_END_ of _TOTAL_',
            paginate: {
                previous: '&lsaquo;',
                next:     '&rsaquo;',
            },
        },
        dom: '<"d-flex justify-content-between align-items-center mb-3"lf>rt<"d-flex justify-content-between align-items-center mt-3"ip>',
        responsive: true,
    });
}

// ── Toast notification helper ──────────────────────────────────
/**
 * Show a Bootstrap toast.
 * @param {string} message
 * @param {'success'|'danger'|'warning'|'info'} type
 */
function showToast(message, type = 'success') {
    const container = document.getElementById('toast-container') || createToastContainer();
    const id = 'toast-' + Date.now();
    const html = `
        <div id="${id}" class="toast align-items-center text-bg-${type} border-0" role="alert" aria-live="assertive" aria-atomic="true">
          <div class="d-flex">
            <div class="toast-body fw-semibold">${message}</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
          </div>
        </div>`;
    container.insertAdjacentHTML('beforeend', html);
    const toastEl = document.getElementById(id);
    new bootstrap.Toast(toastEl, { delay: 3500 }).show();
    toastEl.addEventListener('hidden.bs.toast', () => toastEl.remove());
}

function createToastContainer() {
    const div = document.createElement('div');
    div.id = 'toast-container';
    div.className = 'toast-container position-fixed top-0 end-0 p-3';
    div.style.zIndex = '9999';
    document.body.appendChild(div);
    return div;
}

// ── Confirm delete helper ──────────────────────────────────────
function confirmDelete(url, itemName = 'this record') {
    if (!confirm(`Are you sure you want to delete ${itemName}? This cannot be undone.`)) return;
    tmAjax(url, { method: 'DELETE' })
        .then(res => { showToast(res.message || 'Deleted successfully.'); location.reload(); })
        .catch(err => showToast(err.message, 'danger'));
}

// ── Export utilities ───────────────────────────────────────────
function exportTable(tableId, filename, format = 'excel') {
    const params = new URLSearchParams(window.location.search);
    params.set('export', format);
    params.set('filename', filename);
    window.location.href = window.location.pathname + '?' + params.toString();
}

// ── Flatpickr date pickers ─────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    if (typeof flatpickr !== 'undefined') {
        document.querySelectorAll('.date-input, .datepicker').forEach(el => {
            flatpickr(el, { dateFormat: 'm/d/Y', allowInput: true });
        });
    }
});

// ── Expose globals ─────────────────────────────────────────────
window.tmAjax        = tmAjax;
window.showToast     = showToast;
window.confirmDelete = confirmDelete;
window.exportTable   = exportTable;

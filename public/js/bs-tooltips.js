// Bootstrap 5 - Initialize all tooltips on a page - from https://getbootstrap.com/docs/5.3/components/tooltips/#enable-tooltips
if (window.bootstrap && typeof window.bootstrap.Tooltip === 'function') {
    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach((tooltipTriggerEl) => {
        new bootstrap.Tooltip(tooltipTriggerEl);
    });
}

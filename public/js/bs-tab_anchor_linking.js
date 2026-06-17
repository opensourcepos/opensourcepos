(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {

        const triggers = Array.from(document.querySelectorAll('[data-bs-toggle="tab"][data-bs-target]'));
        const dropdownBtn = document.getElementById('configs-dropdown');

        // --- Sync active state and dropdown label on every tab change ---
        document.addEventListener('shown.bs.tab', function (e) {
            const target = e.target.getAttribute('data-bs-target');

            triggers.forEach(function (el) {
                el.classList.toggle('active', el.getAttribute('data-bs-target') === target);
            });

            if (dropdownBtn) {
                dropdownBtn.textContent = e.target.textContent.trim();
            }

            history.replaceState(null, null, location.pathname + location.search + target);
        });

        // --- Deep linking: activate correct tab on page load from URL hash ---
        if (location.hash) {
            const trigger = triggers.find(function (el) {
                return el.getAttribute('data-bs-target') === location.hash;
            });

            if (trigger) {
                bootstrap.Tab.getOrCreateInstance(trigger).show();
            }
        }

    });

}());

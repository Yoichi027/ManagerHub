(() => {
    const workspace = document.querySelector('[data-workspace]');
    const toggle = document.querySelector('[data-sidebar-toggle]');

    if (!workspace || !toggle) {
        return;
    }

    const key = 'manager-hub.sidebar-collapsed';
    const setCollapsed = (collapsed) => {
        workspace.classList.toggle('is-collapsed', collapsed);
        toggle.setAttribute('aria-expanded', String(!collapsed));
        toggle.querySelector('.visually-hidden').textContent = collapsed ? 'Expand navigation' : 'Collapse navigation';
    };

    setCollapsed(localStorage.getItem(key) === 'true');

    toggle.addEventListener('click', () => {
        const collapsed = !workspace.classList.contains('is-collapsed');
        localStorage.setItem(key, String(collapsed));
        setCollapsed(collapsed);
    });
})();

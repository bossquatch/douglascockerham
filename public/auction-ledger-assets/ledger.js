document.querySelectorAll('[data-filter]').forEach((filter) => {
    const panel = filter.closest('section');
    const rows = [...panel.querySelectorAll('[data-entry]')];
    const search = filter.querySelector('input[type=search]');
    const profitable = filter.querySelector('input[type=checkbox]');
    const result = filter.querySelector('[role=status]');

    function update() {
        const query = search.value.trim().toLowerCase();
        let visible = 0;
        rows.forEach((row) => {
            row.hidden = !`${row.textContent} ${row.dataset.key ?? ""}`.toLowerCase().includes(query)
                || Boolean(profitable?.checked && row.dataset.profitable !== 'true');
            if (!row.hidden) visible += 1;
        });
        result.textContent = visible ? `${visible} of ${rows.length} shown` : 'No matches';
    }

    if (!rows.length) return;
    filter.hidden = false;
    filter.addEventListener('input', update);
    update();
});

if ('showPopover' in HTMLElement.prototype) {
    let active;
    let pinned = false;
    let closeTimer;

    function close() {
        clearTimeout(closeTimer);
        active?.nextElementSibling.hidePopover();
        active = null;
        pinned = false;
    }

    function show(trigger) {
        clearTimeout(closeTimer);
        if (active === trigger) return;
        close();

        active = trigger;
        const tooltip = trigger.nextElementSibling;
        tooltip.showPopover();

        const anchor = trigger.getBoundingClientRect();
        const gap = 6;
        const edge = 12;
        const above = anchor.top >= tooltip.offsetHeight + gap + edge;
        const top = above ? anchor.top - tooltip.offsetHeight - gap : anchor.bottom + gap;
        tooltip.style.top = Math.max(edge, Math.min(top, document.documentElement.clientHeight - tooltip.offsetHeight - edge)) + 'px';
        tooltip.style.left = Math.max(edge, Math.min(anchor.left, document.documentElement.clientWidth - tooltip.offsetWidth - edge)) + 'px';
    }

    function scheduleClose(trigger) {
        if (active !== trigger || pinned) return;
        clearTimeout(closeTimer);
        closeTimer = setTimeout(close, 150);
    }

    document.querySelectorAll('.stat-tooltip-trigger').forEach((trigger) => {
        const tooltip = trigger.nextElementSibling;
        trigger.addEventListener('pointerenter', (event) => {
            if (event.pointerType === 'mouse') show(trigger);
        });
        trigger.addEventListener('pointerleave', () => {
            if (!trigger.matches(':focus-visible')) scheduleClose(trigger);
        });
        tooltip.addEventListener('pointerenter', () => clearTimeout(closeTimer));
        tooltip.addEventListener('pointerleave', () => {
            if (!trigger.matches(':focus-visible')) scheduleClose(trigger);
        });
        trigger.addEventListener('focus', () => {
            if (trigger.matches(':focus-visible')) show(trigger);
        });
        trigger.addEventListener('blur', () => {
            if (active === trigger) close();
        });
        trigger.addEventListener('click', () => {
            if (active === trigger && pinned) {
                close();
                return;
            }
            show(trigger);
            pinned = true;
        });
    });

    document.addEventListener('click', (event) => {
        if (active && !event.target.closest('.stat-tooltip-trigger, .stat-tooltip')) close();
    });
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && active) close();
    });
    window.addEventListener('resize', close);
    window.addEventListener('scroll', close, { passive: true });
}

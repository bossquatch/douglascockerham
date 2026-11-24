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

const statTooltipTriggers = document.querySelectorAll('.stat-tooltip-trigger');
statTooltipTriggers.forEach((trigger) => {
    trigger.addEventListener('click', () => {
        trigger.removeAttribute('data-dismissed');
        statTooltipTriggers.forEach((other) => {
            if (other !== trigger) other.removeAttribute('data-open');
        });
        trigger.toggleAttribute('data-open');
    });
    trigger.addEventListener('pointerenter', () => trigger.removeAttribute('data-dismissed'));
    trigger.addEventListener('focus', () => trigger.removeAttribute('data-dismissed'));
});

if (statTooltipTriggers.length) {
    document.addEventListener('click', (event) => {
        if (event.target.closest('.stat-tooltip-trigger, .stat-tooltip')) return;
        statTooltipTriggers.forEach((trigger) => trigger.removeAttribute('data-open'));
    });
    document.addEventListener('keydown', (event) => {
        if (event.key !== 'Escape') return;
        statTooltipTriggers.forEach((trigger) => {
            trigger.removeAttribute('data-open');
            trigger.setAttribute('data-dismissed', '');
        });
    });
}

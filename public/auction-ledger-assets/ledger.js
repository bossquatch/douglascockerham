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

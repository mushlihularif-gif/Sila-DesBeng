(() => {
    const root = document.querySelector('[data-village-directory]');
    if (!root) return;
    const input = root.querySelector('[data-village-search]');
    if (!input) return;
    const list = root.querySelector('[data-village-list]');
    const rows = [...list.querySelectorAll('[data-village-row]')];
    const toggle = root.querySelector('[data-village-toggle]');
    const label = root.querySelector('[data-village-toggle-label]');
    const empty = root.querySelector('[data-village-empty]');
    const status = root.querySelector('[data-village-status]');
    let expanded = false;
    let animation;

    function render(animate = false) {
        const before = list.getBoundingClientRect().height;
        animation?.cancel();
        const query = input.value.trim().toLocaleLowerCase('id');
        const matches = rows.filter(row => row.dataset.villageName.toLocaleLowerCase('id').includes(query));
        const visible = new Set(expanded ? matches : matches.slice(0, 5));
        rows.forEach(row => { row.hidden = !visible.has(row); });
        toggle.hidden = matches.length <= 5;
        toggle.setAttribute('aria-expanded', String(expanded));
        label.textContent = expanded ? 'Sembunyikan' : 'Tampilkan lebih banyak';
        empty.hidden = matches.length > 0;
        status.textContent = `Menampilkan ${visible.size} dari ${matches.length} desa atau kelurahan yang sudah bergabung.`;
        if (animate && list.animate && !window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            animation = list.animate([{height: `${before}px`}, {height: `${list.getBoundingClientRect().height}px`}], {
                duration: 300, easing: 'ease-in-out',
            });
        }
    }
    input.addEventListener('input', () => { expanded = false; render(); });
    root.querySelector('[data-village-search-form]').addEventListener('submit', event => {
        event.preventDefault();
        expanded = false;
        render();
    });
    toggle.addEventListener('click', () => { expanded = !expanded; render(true); });
    render();
})();

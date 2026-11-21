(() => {
    const enhance = () => {
        // Preserve the table's columns and controls; scrolling stays inside the card.
        document.querySelectorAll('.segi-citizen main table, .segi-admin .content-wrapper table').forEach(table => {
            if (table.hasAttribute('data-no-table-scroll') || table.classList.contains('no-table-scroll')) return;
            if (table.closest('.table-responsive, .overflow-x-auto, .segi-table-scroll, .dataTables_scrollBody')) return;
            const wrapper = document.createElement('div');
            wrapper.className = 'segi-table-scroll';
            wrapper.tabIndex = 0;
            wrapper.setAttribute('role', 'region');
            wrapper.setAttribute('aria-label', 'Tabel — geser ke samping untuk melihat kolom lainnya');
            table.before(wrapper);
            wrapper.append(table);
        });
        document.querySelectorAll('.segi-delivery-choices').forEach(group => {
            group.setAttribute('role', 'radiogroup');
            group.setAttribute('aria-label', 'Metode penerimaan');
            const cards = Array.from(group.querySelectorAll('.delivery-method-card'));
            const sync = () => cards.forEach(card => card.setAttribute('aria-checked', String(card.classList.contains('active'))));
            cards.forEach((card, index) => {
                card.setAttribute('role', 'radio');
                card.tabIndex = 0;
                card.addEventListener('keydown', event => {
                    if (event.key === ' ' || event.key === 'Enter') {
                        event.preventDefault(); card.click();
                    } else if (['ArrowLeft', 'ArrowRight'].includes(event.key)) {
                        event.preventDefault();
                        const next = cards[(index + (event.key === 'ArrowRight' ? 1 : cards.length - 1)) % cards.length];
                        next.focus(); next.click();
                    }
                });
                new MutationObserver(sync).observe(card, {attributes: true, attributeFilter: ['class']});
            });
            sync();
        });
    };
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', enhance, {once: true});
    else enhance();
})();

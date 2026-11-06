(() => {
    function init(root) {
        if (root.dataset.ready) return;
        root.dataset.ready = '1';
        const money = amount => Number(amount) === 0 ? 'Gratis' : 'Rp' + new Intl.NumberFormat('id-ID').format(amount);
        const delivery = root.querySelector('[data-shipping-method="delivery"]');
        const pickup = root.querySelector('[data-shipping-method="pickup"]');
        const rows = root.querySelector('[data-overrides]');
        let nextId = Date.now();
        function update() {
            root.querySelector('[data-delivery-options]').hidden = !delivery.checked;
            const lines = [];
            if (pickup.checked) lines.push('Ambil di toko: tanpa ongkir');
            root.querySelectorAll('[data-shipping-zone]').forEach(zone => {
                const enabled = zone.querySelector('[data-zone-enabled]').checked;
                const free = zone.querySelector('[data-fee-mode]').value === 'free';
                const fee = zone.querySelector('[data-fee]');
                zone.querySelector('[data-zone-price]').hidden = !enabled;
                zone.querySelector('[data-not-served]').hidden = enabled;
                zone.querySelector('[data-fee-field]').hidden = free;
                if (free) fee.value = '0';
                fee.required = delivery.checked && enabled && !free;
                if (delivery.checked) lines.push(zone.dataset.label + ': ' + (enabled ? money(fee.value) : 'Belum dilayani'));
            });
            rows.querySelectorAll('[data-override-row]').forEach(row => {
                const select = row.querySelector('[data-override-region]');
                const fee = row.querySelector('[data-override-fee]');
                select.disabled = fee.disabled = !delivery.checked;
                if (delivery.checked && select.value) lines.push(select.selectedOptions[0].textContent + ': ' + money(fee.value));
            });
            if (!pickup.checked && !delivery.checked) lines.push('Pilih setidaknya satu cara menerima barang.');
            const summary = root.querySelector('[data-shipping-summary]');
            summary.replaceChildren(...lines.map(line => {
                const li = document.createElement('li'); li.textContent = line; return li;
            }));
        }
        root.addEventListener('input', update);
        root.addEventListener('change', update);
        root.addEventListener('click', event => {
            const remove = event.target.closest('[data-remove-override]');
            if (remove) { remove.closest('[data-override-row]').remove(); update(); }
            if (event.target.closest('[data-add-override]')) {
                const template = root.querySelector('[data-override-template]');
                const wrapper = document.createElement('div');
                wrapper.innerHTML = template.innerHTML.replaceAll('__INDEX__', String(nextId++));
                const row = wrapper.firstElementChild; rows.append(row); update();
                row.querySelector('select').focus();
            }
        });
        update();
    }
    function boot() { document.querySelectorAll('[data-market-shipping-editor]').forEach(init); }
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', boot); else boot();
})();

import test from 'node:test';
import assert from 'node:assert/strict';
import fs from 'node:fs';
import vm from 'node:vm';

const source = fs.readFileSync(new URL('../../resources/views/user/laporan/create.blade.php', import.meta.url), 'utf8');
const script = source.slice(source.indexOf('    let map, marker, geocoder;')).split('</script>')[0];

function picker() {
    const elements = new Map();
    const requests = [];
    const element = id => {
        if (!elements.has(id)) elements.set(id, { value: '', innerHTML: '', innerText: '', classList: {
            hidden: true, add() { this.hidden = true; }, remove() { this.hidden = false; }
        }});
        return elements.get(id);
    };
    const map = { on() {}, panTo() {}, invalidateSize() {} };
    const marker = { addTo() { return this; }, on() {}, setLatLng() {} };
    const L = { map: () => map, tileLayer: () => ({ addTo() {} }), marker: () => marker };
    const context = vm.createContext({
        window: { L }, L, document: { getElementById: element }, console,
        setTimeout() {}, fetch: () => new Promise(resolve => requests.push(resolve)),
    });
    vm.runInContext(script, context);
    context.initLeafletPicker();
    return { context, element, requests };
}

test('Leaflet confirmation saves the selected coordinates without Google Maps', () => {
    const { context, element } = picker();
    context.showLocationModal(1.47, 102.13);
    element('modal-address-input').value = 'Alamat pilihan warga';
    element('btn-confirm').onclick();
    assert.equal(element('latitude').value, 1.47);
    assert.equal(element('longitude').value, 102.13);
    assert.equal(element('nama_lokasi').value, 'Alamat pilihan warga');
    assert.equal(element('location-modal').classList.hidden, true);
});

test('a late geocode response cannot reopen a cancelled location modal', async () => {
    const { context, element, requests } = picker();
    context.showLocationModal(1.47, 102.13);
    element('btn-cancel').onclick();
    requests[0]({ json: async () => ({ address: 'Respons terlambat' }) });
    await new Promise(resolve => setImmediate(resolve));
    assert.equal(element('location-modal').classList.hidden, true);
    assert.equal(element('latitude').value, '');
    assert.doesNotThrow(() => element('btn-confirm').onclick());
});

test('only the latest selected point may update the address', async () => {
    const { context, element, requests } = picker();
    context.showLocationModal(1, 102);
    context.showLocationModal(2, 103);
    requests[1]({ json: async () => ({ address: 'Titik kedua' }) });
    await new Promise(resolve => setImmediate(resolve));
    requests[0]({ json: async () => ({ address: 'Titik pertama' }) });
    await new Promise(resolve => setImmediate(resolve));
    assert.equal(element('modal-address-input').value, 'Titik kedua');
    element('btn-confirm').onclick();
    assert.equal(element('latitude').value, 2);
});

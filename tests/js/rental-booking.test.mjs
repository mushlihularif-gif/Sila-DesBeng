import test from 'node:test';
import assert from 'node:assert/strict';
import fs from 'node:fs';
import vm from 'node:vm';

const blade = fs.readFileSync(new URL('../../resources/views/users/rental-booking.blade.php', import.meta.url), 'utf8');
const source = [...blade.matchAll(/<script>([\s\S]*?)<\/script>/g)].at(-1)[1]
    .replace(/{{[\s\S]*?}}/g, '10');

function page(delivery, payment) {
    const nodes = new Map();
    const element = (id, value = '') => {
        const node = { value, dataset: {}, style: {}, checked: true, disabled: false, handlers: {},
            classList: {add() {}, remove() {}}, addEventListener(event, handler) { this.handlers[event] = handler; } };
        nodes.set(id, node); return node;
    };
    element('booking-form'); element('delivery-method-input', delivery);
    element('payment-method-hidden', payment); element('quantity-display', '2'); element('quantity-display-jemput', '3');
    element('hidden-quantity', '1'); element('confirmation-modal'); element('proceed-confirmation');
    element('agree-sop-antar'); element('agree-sop-jemput');
    element('start-date', '2026-11-12'); element('end-date', '2026-11-14');
    element('start-date-jemput', '2026-11-15'); element('end-date-jemput', '2026-11-16');
    element('rental-purpose', 'Acara'); element('rental-purpose-jemput', 'Acara Jemput');
    element('subtotal'); element('subtotal-jemput');
    const bankFile = {name: 'bank.jpg'}, walletFile = {name: 'wallet.jpg'};
    element('transfer-payment-sewa').querySelector = () => ({files: [bankFile]});
    element('ewallet-payment-sewa').querySelector = () => ({files: [walletFile]});
    const requests = [];
    const context = { console,
        document: {readyState: 'complete', getElementById: id => nodes.get(id) ?? null, querySelectorAll: () => [], addEventListener() {}},
        window: {scrollTo() {}, location: {}, addEventListener() {}},
        FormData: class extends Map { constructor() { super([['payment_proof', 'stale hidden file']]); } },
        Swal: {fire() {}, close() {}},
        fetch: (url, options) => { requests.push(options); return new Promise(() => {}); }
    };
    vm.runInNewContext(source, context);
    return {nodes, requests, bankFile, walletFile};
}

for (const payment of ['transfer', 'ewallet']) {
    test(`${payment}: selected proof and method survive hidden fields; double click sends once`, () => {
        const p = page('antar', payment);
        assert.equal(p.nodes.get('payment-method-hidden').value, payment);
        const button = p.nodes.get('proceed-confirmation');
        button.handlers.click(); button.handlers.click();
        assert.equal(p.requests.length, 1);
        assert.equal(button.disabled, true);
        const data = p.requests[0].body;
        assert.equal(data.get('payment_method'), payment);
        assert.equal(data.get('payment_proof'), payment === 'transfer' ? p.bankFile : p.walletFile);
        assert.equal(data.get('quantity'), '2');
        assert.equal(data.get('sop_accepted'), '1');
        assert.ok(data.get('idempotency_key'));
    });
}

test('collection uses its own quantity and dates, cash, and no hidden transfer proof', () => {
    const p = page('jemput', 'transfer'); p.nodes.get('proceed-confirmation').handlers.click();
    const data = p.requests[0].body;
    assert.equal(data.get('quantity'), '3'); assert.equal(data.get('payment_method'), 'tunai');
    assert.equal(data.has('payment_proof'), false);
    assert.equal(p.nodes.get('start-date').value, '2026-11-15');
    assert.equal(p.nodes.get('end-date').value, '2026-11-16');
    assert.equal(p.nodes.get('rental-purpose').value, 'Acara Jemput');
});

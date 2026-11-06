import test from 'node:test';
import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import vm from 'node:vm';

function load() {
    const context = vm.createContext({});
    vm.runInContext(readFileSync('public/js/market-shipping-quote.js', 'utf8'), context);
    return context.MarketShippingQuote;
}

test('a slow quote for an old address cannot replace the latest pickup quote', async () => {
    const Quote = load(), pending = [], results = [];
    const quote = new Quote(params => new Promise(resolve => pending.push({ params, resolve })), state => results.push(state));
    const first = quote.refresh({ delivery_method: 'antar', delivery_region_id: 12 });
    const second = quote.refresh({ delivery_method: 'jemput' });
    pending[1].resolve({ available: true, cost: 0 }); await second;
    pending[0].resolve({ available: true, cost: 25000 }); await first;
    assert.equal(results.at(-1).cost, 0);
    assert.equal(results.length, 3);
});

test('changing destination immediately blocks checkout until the new quote arrives', async () => {
    const Quote = load(), results = []; let resolve;
    const quote = new Quote(() => new Promise(done => resolve = done), state => results.push(state));
    const request = quote.refresh({ delivery_region_id: 12 });
    assert.equal(results[0].pending, true);
    assert.equal(results[0].available, false);
    assert.equal(results[0].cost, null);
    resolve({ available: false, cost: null, message: 'Belum dilayani' }); await request;
    assert.equal(results.at(-1).cost, null);
    assert.equal(results.at(-1).available, false);
});

test('network failure does not turn shipping into free delivery', async () => {
    const Quote = load(), results = [];
    const quote = new Quote(async () => { throw new Error('Koneksi terputus'); }, state => results.push(state));
    await quote.refresh({});
    assert.equal(results.at(-1).available, false);
    assert.equal(results.at(-1).cost, null);
    assert.equal(results.at(-1).message, 'Koneksi terputus');
});

import test from 'node:test';
import assert from 'node:assert/strict';
import fs from 'node:fs';
import vm from 'node:vm';

for (const page of ['create', 'index']) {
    const source = fs.readFileSync(new URL(`../../resources/views/user/laporan/${page}.blade.php`, import.meta.url), 'utf8');
    const script = source.slice(source.indexOf('// Canvas Vector Abstract Background Script')).split('</script>')[0];

    test(`${page}: background script parses and initializes on first page load`, () => {
        const listeners = new Map();
        const context = vm.createContext({
            window: {},
            document: { readyState: 'loading', getElementById: () => null, addEventListener: (event, fn) => listeners.set(event, fn) },
            setTimeout: fn => fn(),
        });
        vm.runInContext(script, context);
        assert.equal(typeof context.window.initAbstractCanvas, 'function');
        assert.equal(typeof listeners.get('DOMContentLoaded'), 'function');
        assert.doesNotThrow(() => listeners.get('DOMContentLoaded')());
    });

    test(`${page}: navigating back initializes the existing background function`, () => {
        let calls = 0;
        vm.runInNewContext(script, {
            window: { initAbstractCanvas: () => calls++ },
            document: { readyState: 'complete', addEventListener() {} },
            setTimeout: fn => fn(),
        });
        assert.equal(calls, 1);
    });
}

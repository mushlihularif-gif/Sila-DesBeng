import { readFileSync } from 'node:fs';
import vm from 'node:vm';
import test from 'node:test';
import assert from 'node:assert/strict';

const admin = readFileSync(new URL('../../resources/views/admin/unit/partials/unit_chat_panel.blade.php', import.meta.url), 'utf8');
const widget = readFileSync(new URL('../../resources/views/components/unit-chat-widget.blade.php', import.meta.url), 'utf8');
function extract(source, name) {
    const start = source.indexOf(`    function ${name}(`);
    assert.ok(start >= 0);
    const end = source.indexOf('\n    }', start) + '\n    }'.length;
    return source.slice(start, end);
}

test('chat escapes quotation marks and markup before inserting HTML attributes', () => {
    for (const source of [admin, widget]) {
        const context = vm.createContext({});
        vm.runInContext(extract(source, 'escapeHtml'), context);
        assert.equal(context.escapeHtml('" onerror="alert(1)\'><svg>'), '&quot; onerror=&quot;alert(1)&#039;&gt;&lt;svg&gt;');
    }
});

test('admin product links reject executable and external URLs', () => {
    const context = vm.createContext({ URL, window: { location: { origin: 'https://segidaerah.test' } } });
    vm.runInContext(extract(admin, 'safeChatProductUrl'), context);
    for (const url of ['javascript:alert(1)', 'data:text/html,<script>', '//phishing.example/login', 'https://phishing.example/']) {
        assert.equal(context.safeChatProductUrl(url), '');
    }
    assert.equal(context.safeChatProductUrl('/unit-gas/1'), 'https://segidaerah.test/unit-gas/1');
});

test('guest chat keeps the server token from history and send responses', () => {
    const saved = new Map();
    const context = vm.createContext({ unitServiceCode: 'gas', unitRegionId: 7, unitChatSessionToken: '', localStorage: { setItem: (key, value) => saved.set(key, value) } });
    vm.runInContext(extract(widget, 'rememberChatToken'), context);
    context.rememberChatToken({ session: { session_token: 'a'.repeat(64) } });
    assert.equal(saved.get('unitChatToken_gas_7'), 'a'.repeat(64));
    context.rememberChatToken({ session_token: 'b'.repeat(64) });
    assert.equal(context.unitChatSessionToken, 'b'.repeat(64));
});

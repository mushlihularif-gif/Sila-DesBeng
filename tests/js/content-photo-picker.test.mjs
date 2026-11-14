import test from 'node:test';
import assert from 'node:assert/strict';
import {mountPhotoPicker, validatePhoto} from '../../public/js/content-photo-picker.js';

test('three photo slots preserve other files when replacing, cancelling or rejecting a selection', () => {
    class Element {
        children = []; files = [];
        append(...nodes) { this.children.push(...nodes); }
        replaceChildren() { this.children = []; }
        click() { this.clicked = true; }
    }
    const originalDocument = globalThis.document, originalTransfer = globalThis.DataTransfer;
    const create = URL.createObjectURL, revoke = URL.revokeObjectURL;
    const released = []; let count = 0, changes = 0;
    globalThis.document = {createElement: () => new Element()};
    globalThis.DataTransfer = class { files = []; items = {add: file => this.files.push(file)}; };
    URL.createObjectURL = () => `blob:${++count}`; URL.revokeObjectURL = url => released.push(url);
    try {
        const input = new Element(), container = new Element(), status = new Element();
        const picker = mountPhotoPicker(input, container, status, () => changes++);
        const choose = (slot, file) => {
            const element = container.children[slot].children.find(node => node.type === 'file');
            element.files = file ? [file] : []; element.onchange();
        };
        const first = {name:'first.jpg',type:'image/jpeg',size:100}, second = {name:'second.png',type:'image/png',size:200};
        assert.equal(container.children.length, 3);
        choose(0, first); choose(2, second);
        assert.deepEqual(input.files,[first,second]);
        choose(0, null); // Cancelling the file dialog must keep the existing picture.
        assert.deepEqual(input.files,[first,second]);
        choose(0, {name:'fake.html',type:'text/html',size:100});
        assert.deepEqual(input.files,[first,second]); assert.match(status.textContent,/JPG/);
        choose(0, second);
        assert.deepEqual(input.files,[second,second]);
        container.children[0].children.find(node => node.textContent === 'Batalkan').onclick();
        assert.deepEqual(input.files,[second]);
        assert.match(status.textContent,/1 foto/);
        picker.clear(); assert.deepEqual(input.files,[]); assert.equal(container.children.length,3);
        assert.equal(changes,5); assert.ok(released.length > 0);
        picker.destroy();
    } finally {
        globalThis.document = originalDocument; globalThis.DataTransfer = originalTransfer;
        URL.createObjectURL = create; URL.revokeObjectURL = revoke;
    }
});

test('oversized photos are rejected before uploading', () => {
    assert.match(validatePhoto({type:'image/jpeg',size:5*1024*1024+1}),/5 MB/);
    assert.equal(validatePhoto({type:'image/webp',size:5*1024*1024}), '');
});

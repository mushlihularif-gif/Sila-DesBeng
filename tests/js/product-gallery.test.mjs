import test from 'node:test';
import assert from 'node:assert/strict';
import fs from 'node:fs';
import vm from 'node:vm';

const source = fs.readFileSync(new URL('../../resources/views/users/partials/product-gallery-scripts.blade.php', import.meta.url), 'utf8').replace(/<\/?script>/g, '');

function setup({ count = 3, autoplay = true, reducedMotion = false } = {}) {
    const element = () => ({
        style: {}, dataset: {}, attributes: {}, listeners: new Map(),
        setAttribute(key, value) { this.attributes[key] = value; },
        addEventListener(key, callback) {
            if (!this.listeners.has(key)) this.listeners.set(key, new Set());
            this.listeners.get(key).add(callback);
        },
        removeEventListener(key, callback) { this.listeners.get(key)?.delete(callback); },
        emit(key, event = {}) { [...(this.listeners.get(key) || [])].forEach(callback => callback(event)); },
    });
    const gallery = element(), carousel = element(), navigation = element(), counter = element();
    const previous = element(), next = element(), dots = Array.from({ length: count }, element);
    carousel.children = Array.from({ length: count }, element);
    navigation.dataset.autoplay = String(autoplay);
    navigation.querySelectorAll = () => dots;
    navigation.querySelector = selector => ({ '.product-gallery-counter': counter, '.product-gallery-prev': previous, '.product-gallery-next': next })[selector];
    gallery.querySelector = selector => selector === '#product-carousel' ? carousel : count > 1 ? navigation : null;
    gallery.contains = target => [previous, next, ...dots].includes(target);
    const document = { ...element(), readyState: 'complete', hidden: false, querySelectorAll: () => [gallery] };
    const timers = new Map(); let timerId = 0;
    vm.runInNewContext(source, {
        document, window: { matchMedia: () => ({ matches: reducedMotion }) },
        setInterval(callback) { timers.set(++timerId, callback); return timerId; },
        clearInterval(id) { timers.delete(id); },
    });
    return { gallery, carousel, counter, dots, previous, next, document, timers, tick: () => [...timers.values()].forEach(callback => callback()) };
}

test('arrows wrap, dots select a photo, and keyboard navigation updates the counter', () => {
    const g = setup();
    g.next.emit('click');
    assert.equal(g.carousel.style.transform, 'translateX(-100%)');
    assert.equal(g.counter.textContent, '2 / 3');
    assert.equal(g.dots[1].attributes['aria-current'], 'true');
    g.previous.emit('click'); g.previous.emit('click');
    assert.equal(g.counter.textContent, '3 / 3');
    g.dots[0].emit('click');
    let prevented = false;
    g.gallery.emit('keydown', { key: 'ArrowRight', preventDefault() { prevented = true; } });
    assert.equal(prevented, true);
    assert.equal(g.counter.textContent, '2 / 3');
});

test('autoplay pauses on hover, focus and hidden tabs; manual selection stays in place', () => {
    const g = setup();
    g.tick(); assert.equal(g.counter.textContent, '2 / 3');
    g.gallery.emit('mouseenter'); assert.equal(g.timers.size, 0);
    g.gallery.emit('mouseleave'); assert.equal(g.timers.size, 1);
    g.gallery.emit('focusin'); assert.equal(g.timers.size, 0);
    g.gallery.emit('focusout', { relatedTarget: null }); assert.equal(g.timers.size, 1);
    g.document.hidden = true; g.document.emit('visibilitychange'); assert.equal(g.timers.size, 0);
    g.document.hidden = false; g.document.emit('visibilitychange'); assert.equal(g.timers.size, 1);
    g.next.emit('click');
    g.gallery.emit('mouseleave'); g.document.emit('visibilitychange'); g.tick();
    assert.equal(g.timers.size, 0);
    assert.equal(g.counter.textContent, '3 / 3');
});

test('reinitializing does not duplicate timers or button handlers and caching stops playback', () => {
    const g = setup();
    g.document.emit('turbo:load'); g.document.emit('turbo:load');
    assert.equal(g.timers.size, 1);
    g.next.emit('click'); assert.equal(g.counter.textContent, '2 / 3');
    g.document.emit('turbo:load');
    g.document.emit('turbo:before-cache');
    assert.equal(g.timers.size, 0);
    assert.equal(g.next.listeners.get('click').size, 0);
});

test('horizontal swipes change photos but vertical scrolling does not', () => {
    const g = setup();
    g.gallery.emit('touchstart', { touches: [{ clientX: 150, clientY: 20 }] });
    g.gallery.emit('touchend', { changedTouches: [{ clientX: 40, clientY: 25 }] });
    assert.equal(g.counter.textContent, '2 / 3');
    g.gallery.emit('touchstart', { touches: [{ clientX: 150, clientY: 20 }] });
    g.gallery.emit('touchend', { changedTouches: [{ clientX: 80, clientY: 180 }] });
    assert.equal(g.counter.textContent, '2 / 3');
});

test('single-photo, empty, reduced-motion and manual-only galleries do not autoplay', () => {
    for (const options of [{ count: 0 }, { count: 1 }, { reducedMotion: true }, { autoplay: false }]) {
        const g = setup(options);
        assert.equal(g.timers.size, 0);
        if (g.carousel.children.length > 1) {
            g.next.emit('click'); assert.equal(g.counter.textContent, '2 / 3');
        }
    }
});

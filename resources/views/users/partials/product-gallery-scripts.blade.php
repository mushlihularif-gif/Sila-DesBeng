<script>
(() => {
    const initProductGalleries = () => {
        document.querySelectorAll('.catalog-detail-gallery').forEach(gallery => {
            gallery.productGalleryCleanup?.();
            const carousel = gallery.querySelector('#product-carousel');
            const navigation = gallery.querySelector('.product-gallery-navigation');
            if (!carousel || !navigation || carousel.children.length < 2) return;

            const dots = Array.from(navigation.querySelectorAll('.product-gallery-dot'));
            const counter = navigation.querySelector('.product-gallery-counter');
            const total = carousel.children.length;
            let current = 0;
            let timer;
            let manuallySelected = false;
            let hovering = false;
            let focused = false;
            let touchStart = null;
            const listeners = [];
            const listen = (target, name, handler, options) => {
                target.addEventListener(name, handler, options);
                listeners.push(() => target.removeEventListener(name, handler, options));
            };
            const stop = () => { clearInterval(timer); timer = null; };
            const show = (index, manual = false) => {
                current = (index + total) % total;
                carousel.style.transform = `translateX(-${current * 100}%)`;
                dots.forEach((dot, i) => dot.setAttribute('aria-current', String(i === current)));
                counter.textContent = `${current + 1} / ${total}`;
                counter.setAttribute('aria-label', `Foto ${current + 1} dari ${total}`);
                if (manual) {
                    manuallySelected = true;
                    stop();
                }
            };
            const start = () => {
                stop();
                if (navigation.dataset.autoplay !== 'true' || manuallySelected || hovering || focused || document.hidden || window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
                timer = setInterval(() => show(current + 1), 5000);
            };

            gallery.tabIndex = 0;
            gallery.setAttribute('role', 'region');
            gallery.setAttribute('aria-label', 'Galeri foto produk');
            listen(navigation.querySelector('.product-gallery-prev'), 'click', () => show(current - 1, true));
            listen(navigation.querySelector('.product-gallery-next'), 'click', () => show(current + 1, true));
            dots.forEach((dot, i) => listen(dot, 'click', () => show(i, true)));
            listen(gallery, 'keydown', event => {
                if (event.key !== 'ArrowLeft' && event.key !== 'ArrowRight') return;
                event.preventDefault();
                show(current + (event.key === 'ArrowRight' ? 1 : -1), true);
            });
            listen(gallery, 'touchstart', event => {
                const touch = event.touches[0];
                touchStart = event.touches.length === 1 ? { x: touch.clientX, y: touch.clientY } : null;
            }, { passive: true });
            listen(gallery, 'touchend', event => {
                if (!touchStart || !event.changedTouches.length) return;
                const dx = event.changedTouches[0].clientX - touchStart.x;
                const dy = event.changedTouches[0].clientY - touchStart.y;
                touchStart = null;
                if (Math.abs(dx) >= 40 && Math.abs(dx) > Math.abs(dy)) show(current + (dx < 0 ? 1 : -1), true);
            }, { passive: true });
            listen(gallery, 'touchcancel', () => { touchStart = null; });
            listen(gallery, 'mouseenter', () => { hovering = true; stop(); });
            listen(gallery, 'mouseleave', () => { hovering = false; start(); });
            listen(gallery, 'focusin', () => { focused = true; stop(); });
            listen(gallery, 'focusout', event => {
                focused = gallery.contains(event.relatedTarget);
                start();
            });
            listen(document, 'visibilitychange', start);
            gallery.productGalleryCleanup = () => {
                stop();
                listeners.forEach(remove => remove());
            };
            show(0);
            start();
        });
    };
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initProductGalleries, { once: true });
    } else {
        initProductGalleries();
    }
    document.addEventListener('turbo:load', initProductGalleries);
    document.addEventListener('turbo:before-cache', () => {
        document.querySelectorAll('.catalog-detail-gallery').forEach(gallery => gallery.productGalleryCleanup?.());
    });
})();
</script>

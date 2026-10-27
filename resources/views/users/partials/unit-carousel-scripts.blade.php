<script>
(() => {
    const init = () => {
        const cards = Array.from(document.querySelectorAll('.unit-card'));
        if (cards.length === 0) return;

        const titleElement = document.getElementById('unit-title');
        const nextBtn = document.getElementById('unit-next');
        const prevBtn = document.getElementById('unit-prev');
        const speechBox = document.getElementById('unit-speech-box');
        const speechWrapper = document.getElementById('speech-text-wrapper');
        const speechHeading = document.getElementById('speech-heading');
        const speechBody = document.getElementById('speech-body');
        const speechBadge = document.getElementById('speech-badge');
        const speechBadgeLabel = document.getElementById('speech-badge-label');
        const speechBadgeIcon = document.getElementById('speech-badge-icon');

        const n = cards.length;
        let currentIndex = 0;
        const updateCarousel = () => {
            cards.forEach((card, index) => {
                // Remove all state classes
                card.className = card.className.replace(/\bstate-\d\b/g, '').trim();

                let diff = (index - currentIndex) % n;
                if (diff < 0) diff += n;

                let state = 5; // Default Hidden

                if (n === 1) {
                    if (diff === 0) state = 1;
                } else if (n === 2) {
                    if (diff === 0) state = 1;
                    if (diff === 1) state = 2;
                } else if (n === 3) {
                    if (diff === 0) state = 1;
                    if (diff === 1) state = 2;
                    if (diff === 2) state = 0;
                } else {
                    if (diff === 0) state = 1; // Center
                    else if (diff === 1) state = 2; // Right
                    else if (diff === 2) state = 3; // Far Right (Hidden, fading out)
                    else if (diff === n - 2) state = 4; // Far Left (Hidden, fading in)
                    else if (diff === n - 1) state = 0; // Left
                    else state = 5; // Deep Hidden
                }

                card.classList.add(`state-${state}`);

                if (diff === 0) {
                    // 1. Update Title Navigasi Bawah
                    if (titleElement) {
                        titleElement.style.opacity = '0';
                        setTimeout(() => {
                            titleElement.textContent = card.getAttribute('data-name');
                            titleElement.style.opacity = '1';
                        }, 180);
                    }

                    // 2. Update Kotak Narasi Dinamis Atas
                    if (speechBox && speechWrapper) {
                        speechWrapper.classList.remove('fade-in');
                        speechWrapper.classList.add('fade-out');

                        setTimeout(() => {
                            if (speechHeading) speechHeading.textContent = card.getAttribute('data-heading') || '';
                            if (speechBody) speechBody.textContent = card.getAttribute('data-body') || '';
                            if (speechBadgeLabel) speechBadgeLabel.textContent = card.getAttribute('data-badge') || '';
                            if (speechBadgeIcon) speechBadgeIcon.className = `bx ${card.getAttribute('data-icon') || 'bx-grid-alt'} text-sm`;

                            const boxBg = card.getAttribute('data-box-bg') || 'bg-amber-50/90';
                            const boxBorder = card.getAttribute('data-box-border') || 'border-amber-200';
                            const badgeBg = card.getAttribute('data-badge-bg') || 'bg-amber-100 text-amber-800 border-amber-300';
                            const textColor = card.getAttribute('data-text-color') || 'text-amber-950';

                            speechBox.className = `unit-speech-box relative overflow-hidden rounded-2xl sm:rounded-3xl px-4 py-4 sm:px-6 sm:py-5 border shadow-md shadow-slate-900/5 backdrop-blur-xl transition-all duration-300 ${boxBg} ${boxBorder}`;
                            if (speechBadge) speechBadge.className = `inline-flex shrink-0 self-center items-center gap-1.5 rounded-full border px-3 py-1 text-[10px] font-extrabold uppercase tracking-[0.1em] ${badgeBg}`;
                            if (speechHeading) speechHeading.className = `text-sm sm:text-base font-extrabold leading-snug ${textColor}`;

                            speechWrapper.classList.remove('fade-out');
                            speechWrapper.classList.add('fade-in');
                        }, 180);
                    }
                }
            });

        };

        const handleNext = () => {
            if (n <= 1) return;
            currentIndex = (currentIndex + 1) % n;
            updateCarousel();
        };

        const handlePrev = () => {
            if (n <= 1) return;
            currentIndex = (currentIndex - 1 + n) % n;
            updateCarousel();
        };

        if (nextBtn) {
            nextBtn.hidden = n <= 1;
            nextBtn.onclick = handleNext;
        }
        if (prevBtn) {
            prevBtn.hidden = n <= 1;
            prevBtn.onclick = handlePrev;
        }

        updateCarousel();

    };
    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", init, { once: true });
    } else {
        init();
    }
    document.addEventListener("turbo:load", init);
})();
</script>

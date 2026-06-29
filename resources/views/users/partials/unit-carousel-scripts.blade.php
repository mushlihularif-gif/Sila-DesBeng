<script>
    const UnitCarousel = {
        init() {
            this.initUnitCarousel();
        },

        initUnitCarousel() {
            const cards = Array.from(document.querySelectorAll('.unit-card'));
            if (cards.length === 0) return;

            const titleElement = document.getElementById('unit-title');
            const nextBtn = document.getElementById('unit-next');
            const prevBtn = document.getElementById('unit-prev');

            const stateClasses = ['state-0', 'state-1', 'state-2', 'state-3', 'state-4', 'state-5'];
            
            // Inisialisasi posisi secara dinamis sesuai jumlah cards
            let positions = [];
            const n = cards.length;
            
            if (n === 1) {
                positions = [1];
            } else if (n === 2) {
                positions = [1, 2];
            } else if (n === 3) {
                positions = [1, 2, 0];
            } else {
                // Untuk n >= 4
                positions = Array.from({length: n}, (_, i) => {
                    if (i === 0) return 1; // Center
                    if (i === 1) return 2; // Right
                    if (i === 2) return 3; // Far Right
                    if (i === n - 1) return 0; // Left
                    if (i === n - 2) return 4; // Far Left
                    return 5; // Hidden off-screen
                });
            }

            let autoSlideInterval;
            const autoSlideDelay = 3000;

            const updateCarousel = () => {
                cards.forEach((card, index) => {
                    card.classList.remove(...stateClasses);
                    const currentPos = positions[index];
                    card.classList.add(stateClasses[currentPos] || 'state-3');

                    if (currentPos === 1 && titleElement) {
                        titleElement.style.opacity = '0';
                        setTimeout(() => {
                            titleElement.textContent = card.getAttribute('data-name');
                            titleElement.style.opacity = '1';
                        }, 200);
                    }
                });
            };

            const handleNext = () => {
                if (n === 1) return;
                
                // Shift positions array to the right cyclically
                positions.unshift(positions.pop());
                updateCarousel();
            };

            const handlePrev = () => {
                if (n === 1) return;
                
                // Shift positions array to the left cyclically
                positions.push(positions.shift());
                updateCarousel();
            };

            const startAutoSlide = () => {
                if (n <= 1) return;
                clearInterval(autoSlideInterval);
                autoSlideInterval = setInterval(handleNext, autoSlideDelay);
            };

            const resetAutoSlide = () => {
                if (n <= 1) return;
                clearInterval(autoSlideInterval);
                startAutoSlide();
            };

            if (nextBtn) {
                const newNext = nextBtn.cloneNode(true);
                nextBtn.parentNode.replaceChild(newNext, nextBtn);
                newNext.addEventListener('click', () => {
                    handleNext();
                    resetAutoSlide();
                });
            }
            if (prevBtn) {
                const newPrev = prevBtn.cloneNode(true);
                prevBtn.parentNode.replaceChild(newPrev, prevBtn);
                newPrev.addEventListener('click', () => {
                    handlePrev();
                    resetAutoSlide();
                });
            }

            const container = document.getElementById('unit-carousel-container');
            if (container) {
                container.addEventListener('mouseenter', () => clearInterval(autoSlideInterval));
                container.addEventListener('mouseleave', startAutoSlide);
            }

            updateCarousel();
            startAutoSlide();

        },
    };
    
    document.addEventListener('DOMContentLoaded', () => {
        UnitCarousel.init();
    });
    
    document.addEventListener('turbo:load', () => {
        UnitCarousel.init();
    });
</script>

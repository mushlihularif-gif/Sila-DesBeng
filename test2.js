
    let isExpanded = false;

    function toggleCards() {
        const extraCards = document.querySelectorAll('.extra-card');
        const toggleText = document.getElementById('toggleText');
        const arrowDown = document.getElementById('toggleArrowDown');
        const arrowUp = document.getElementById('toggleArrowUp');

        isExpanded = !isExpanded;

        extraCards.forEach(card => {
            card.style.overflow = 'hidden'; // Ensure hidden during transition

            if (isExpanded) {
                card.classList.remove('is-collapsed');
                card.classList.add('is-expanded');
                // Remove overflow hidden after animation to allow shadow bleed
                setTimeout(() => {
                    if (card.classList.contains('is-expanded')) {
                        card.style.overflow = 'visible';
                    }
                }, 500);
            } else {
                card.classList.remove('is-expanded');
                card.classList.add('is-collapsed');
            }
        });

        if (isExpanded) {
            toggleText.textContent = 'Sembunyikan';
            arrowDown.classList.add('hidden');
            arrowUp.classList.remove('hidden');
        } else {
            toggleText.textContent = 'Tampilkan';
            arrowDown.classList.remove('hidden');
            arrowUp.classList.add('hidden');
        }
    }

    function filterCards() {
        const query = document.getElementById('searchInput').value.toLowerCase();
        const cards = document.querySelectorAll('.desa-card');
        const emptyState = document.getElementById('empty-state');
        const toggleBtn = document.getElementById('toggleBtn');
        let visibleCount = 0;

        // Sembunyikan tombol toggle saat ada pencarian
        if (toggleBtn) {
            toggleBtn.style.display = query.length > 0 ? 'none' : '';
        }

        cards.forEach(card => {
            const name = card.getAttribute('data-name');
            const fullName = name;
            
            if (fullName.includes(query)) {
                card.classList.remove('search-hidden');
                
                // Jika sedang mencari, paksa tampilkan semua (override is-collapsed)
                if (query.length > 0 && card.classList.contains('extra-card')) {
                    card.classList.add('force-show');
                    card.style.overflow = 'visible';
                } else {
                    card.classList.remove('force-show');
                    if (card.classList.contains('is-collapsed')) {
                        card.style.overflow = 'hidden';
                    }
                }
                
                visibleCount++;
            } else {
                card.classList.add('search-hidden');
            }
        });

        if (visibleCount === 0) {
            emptyState.classList.remove('hidden');
        } else {
            emptyState.classList.add('hidden');
        }
    }

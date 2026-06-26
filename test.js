
        (() => {
            const BerandaPage = {
            // Initialize all components
            init() {
                this.initYearSelectors(); // Call this FIRST to ensure filters always work
                this.initCarousel();
                try {
                    this.initCharts();
                } catch (e) {
                    console.error("Charts failed to initialize:", e);
                }
                this.initUnitCarousel();
                this.initNavbarMarginSync();
            },

            // Sinkronisasi margin beranda dengan navbar dan fake blur
            initNavbarMarginSync() {
                const navbar = document.getElementById('master-navbar');
                const berandaSection = document.getElementById('beranda');
                const blurLayer = document.getElementById('navbar-blur-bg');
                
                if (!navbar || !berandaSection) return;

                let lastScrollState = null;
                let cachedNavHeight = navbar.offsetHeight;

                const syncHeightAndMargin = () => {
                    const currentScrollY = window.scrollY;
                    const isScrolled = currentScrollY > 10;
                    
                    // Handle blur fade out naturally
                    if (blurLayer && lastScrollState !== isScrolled) {
                        if (isScrolled) {
                            blurLayer.classList.add('hidden-blur');
                        } else {
                            blurLayer.classList.remove('hidden-blur');
                        }
                        lastScrollState = isScrolled;
                    }

                    // Reset padding to normal if we reach the absolute top and navbar is shown
                    if (currentScrollY <= 10 && !navbar.classList.contains('hidden-nav')) {
                        berandaSection.style.paddingTop = cachedNavHeight + 'px';
                        if (blurLayer) blurLayer.style.height = cachedNavHeight + 'px';
                    }
                };

                // Setup image sync for fake blur
                if (blurLayer) {
                    if (window.syncNavbarBlurImage) {
                        window.syncNavbarBlurImage(0);
                    }
                    window.syncNavbarBlurImage = (slideIndex) => {
                        const slideContainers = document.querySelectorAll('.carousel-slide');
                        if(slideContainers[slideIndex]) {
                            const desktopImg = slideContainers[slideIndex].querySelector('img.md\\:block, img.hidden.md\\:block');
                            const mobileImg = slideContainers[slideIndex].querySelector('img.md\\:hidden, img.block.md\\:hidden');
                            
                            let img = slideContainers[slideIndex].querySelector('img');
                            
                            if (desktopImg && mobileImg) {
                                img = window.innerWidth >= 768 ? desktopImg : mobileImg;
                            }
                            
                            if (img) {
                                const imgSrc = img.getAttribute('src');
                                blurLayer.style.backgroundImage = `url('${imgSrc}')`;
                            }
                        }
                    };
                    setTimeout(() => { if (window.syncNavbarBlurImage) window.syncNavbarBlurImage(0); }, 50);
                }

                // Clear previous listeners if any (Turbo Drive support)
                if (window.berandaScrollHandler) {
                    window.removeEventListener('scroll', window.berandaScrollHandler);
                    window.removeEventListener('resize', window.berandaResizeHandler);
                }

                let ticking = false;
                window.berandaScrollHandler = () => {
                    if (!ticking) {
                        window.requestAnimationFrame(() => {
                            syncHeightAndMargin();
                            ticking = false;
                        });
                        ticking = true;
                    }
                };

                window.berandaResizeHandler = () => {
                    cachedNavHeight = navbar.offsetHeight;
                    lastNavState = null; // force update
                    syncHeightAndMargin();
                };

                syncHeightAndMargin();

                window.addEventListener('resize', window.berandaResizeHandler);
                window.addEventListener('scroll', window.berandaScrollHandler);

                const masterToggle = document.getElementById('master-navbar-toggle');
                if (masterToggle && !masterToggle.dataset.berandaToggle) {
                    masterToggle.dataset.berandaToggle = 'true';
                    masterToggle.addEventListener('click', () => {
                        // KUNCI PERBAIKAN: Hanya ubah padding saat tombol toggle DITEKAN secara manual!
                        setTimeout(() => {
                            const isHidden = navbar.classList.contains('hidden-nav');
                            berandaSection.style.paddingTop = isHidden ? '0px' : cachedNavHeight + 'px';
                            if (blurLayer) blurLayer.style.height = isHidden ? '0px' : cachedNavHeight + 'px';
                        }, 10);
                    });
                }
            },

            // Initialize Year & Region Selectors
            initYearSelectors() {
                const kecamatanSelect = document.getElementById('kecamatanSelect');
                const desaSelect = document.getElementById('desaSelect');
                const globalYearSelect = document.getElementById('globalYearSelect');

                if (!kecamatanSelect || !desaSelect || !globalYearSelect) return;

                // Handle cascading changes
                const redirectWithFilters = async () => {
                    const url = new URL(window.location.href);
                    url.searchParams.set('kecamatan_id', kecamatanSelect.value || 'all');
                    url.searchParams.set('desa_id', desaSelect.value || 'all');
                    url.searchParams.set('year', globalYearSelect.value || new Date().getFullYear());
                    
                    // Identify sections to update
                    const sectionIds = ['search-results-section', 'populer-section', 'kabar-daerah-section', 'charts-section'];
                    const elementsToDim = sectionIds.map(id => document.getElementById(id)).filter(Boolean);

                    // Show subtle loading state only on affected sections
                    elementsToDim.forEach(el => {
                        el.style.transition = 'opacity 0.3s ease';
                        el.style.opacity = '0.5';
                        el.style.pointerEvents = 'none';
                    });

                    try {
                        // Push state without reloading
                        window.history.pushState({}, '', url.toString());

                        const response = await fetch(url.toString(), {
                            headers: { 'X-Requested-With': 'XMLHttpRequest' }
                        });
                        
                        if (!response.ok) throw new Error('Network response was not ok');
                        
                        const htmlString = await response.text();
                        
                        const parser = new DOMParser();
                        const newDoc = parser.parseFromString(htmlString, 'text/html');
                        
                        let updatedAny = false;
                        
                        // Replace only the specific sections
                        sectionIds.forEach(id => {
                            const oldEl = document.getElementById(id);
                            const newEl = newDoc.getElementById(id);
                            if (oldEl && newEl) {
                                oldEl.innerHTML = newEl.innerHTML;
                                updatedAny = true;
                            } else if (oldEl && !newEl) {
                                oldEl.innerHTML = ''; // Clear if removed
                                updatedAny = true;
                            }
                        });
                        
                        // If we didn't find specific sections, fallback to full reload just in case
                        if (!updatedAny) {
                            window.location.reload();
                        } else {
                            // Only re-initialize charts if charts-section was updated
                            try {
                                BerandaPage.initCharts();
                            } catch (e) {
                                console.error("Charts failed to re-initialize:", e);
                            }
                            
                            // Re-init any other specific components inside those sections if needed
                            // Note: We don't call BerandaPage.init() to avoid destroying select2 and carousel
                        }
                    } catch (error) {
                        console.error('AJAX failed, falling back to reload:', error);
                        window.location.reload();
                    } finally {
                        // Restore opacity
                        const elementsToRestore = sectionIds.map(id => document.getElementById(id)).filter(Boolean);
                        elementsToRestore.forEach(el => {
                            el.style.opacity = '1';
                            el.style.pointerEvents = 'auto';
                        });
                    }
                };

                // When Kecamatan changes, reset Desa
                kecamatanSelect.addEventListener('change', function() {
                    desaSelect.value = 'all';
                    redirectWithFilters();
                });

                // When Desa or Year changes, just submit
                desaSelect.addEventListener('change', redirectWithFilters);
                globalYearSelect.addEventListener('change', redirectWithFilters);
            },

            // Carousel initialization
            initCarousel() {
                const carouselSlides = document.getElementById('carousel-slides');
                if (!carouselSlides) return;

                const prevButton = document.getElementById('carousel-prev');
                const nextButton = document.getElementById('carousel-next');
                // Use let so we can update the reference after cloning
                let indicators = document.querySelectorAll('.carousel-indicator');

                let currentSlide = 0;
                const totalSlides = 1;
                let autoSlideInterval;
                const autoSlideDelay = 7000; // 7 Seconds

                let blurTimeout;
                const goToSlide = (slideIndex) => {
                    currentSlide = slideIndex;
                    carouselSlides.style.transform = `translateX(-${slideIndex * 100}%)`;
                    
                    if(window.syncNavbarBlurImage) {
                        clearTimeout(blurTimeout);
                        blurTimeout = setTimeout(() => {
                            window.syncNavbarBlurImage(slideIndex);
                        }, 400); // Sinkronkan dengan durasi transisi slide (duration-500)
                    }

                    // indicators variable now points to the LIVE elements in DOM
                    indicators.forEach((indicator, index) => {
                        indicator.classList.toggle('bg-white', index === slideIndex);
                        indicator.classList.toggle('w-8', index === slideIndex);
                        indicator.classList.toggle('bg-white/50', index !== slideIndex);
                        indicator.classList.toggle('w-2.5', index !== slideIndex);
                    });
                };

                const nextSlide = () => {
                    currentSlide = (currentSlide + 1) % totalSlides;
                    goToSlide(currentSlide);
                };

                const prevSlide = () => {
                    currentSlide = (currentSlide - 1 + totalSlides) % totalSlides;
                    goToSlide(currentSlide);
                };

                const startAutoSlide = () => {
                    clearInterval(autoSlideInterval);
                    autoSlideInterval = setInterval(nextSlide, autoSlideDelay);
                };

                const resetAutoSlide = () => {
                    clearInterval(autoSlideInterval);
                    startAutoSlide();
                };

                if (nextButton) {
                    const newNext = nextButton.cloneNode(true);
                    nextButton.parentNode.replaceChild(newNext, nextButton);
                    newNext.addEventListener('click', () => {
                        nextSlide();
                        resetAutoSlide();
                    });
                }

                if (prevButton) {
                    const newPrev = prevButton.cloneNode(true);
                    prevButton.parentNode.replaceChild(newPrev, prevButton);
                    newPrev.addEventListener('click', () => {
                        prevSlide();
                        resetAutoSlide();
                    });
                }

                // Fix: Update indicators reference after cloning
                const newIndicatorsList = [];
                indicators.forEach((indicator, index) => {
                    const newIndicator = indicator.cloneNode(true);
                    indicator.parentNode.replaceChild(newIndicator, indicator);
                    newIndicator.addEventListener('click', () => {
                        goToSlide(index);
                        resetAutoSlide();
                    });
                    newIndicatorsList.push(newIndicator);
                });
                indicators = newIndicatorsList; // Update reference to new nodes

                startAutoSlide();
            },

            // Sinkronisasi background blur navbar dihilangkan, sekarang menggunakan CSS native

            // Charts initialization
            initCharts() {
                let attempts = 0;
                const checkAndInit = () => {
                    if (typeof ApexCharts !== 'undefined') {
                        this.initKinerjaChart();
                        this.initUnitChart();
                    } else if (attempts < 50) { // Try for 5 seconds
                        attempts++;
                        setTimeout(checkAndInit, 100);
                    } else {
                        console.error('ApexCharts failed to load after 5 seconds.');
                    }
                };
                checkAndInit();
            },

            // Kinerja Layanan Chart
            initKinerjaChart() {
                const container = document.querySelector("#kinerjaChart");
                if (!container) return;

                container.innerHTML = '';
                
                let kinerjaData;
                try {
                    kinerjaData = JSON.parse(container.getAttribute('data-chart'));
                } catch(e) {
                    console.error("Failed to parse kinerja data", e);
                    return;
                }

                const options = {
                    series: [{
                        name: 'Kinerja (Juta Rupiah)',
                        data: kinerjaData.data
                    }],
                    chart: {
                        type: 'area',
                        height: 280,
                        toolbar: {
                            show: false
                        },
                        zoom: {
                            enabled: false
                        },
                        background: 'transparent'
                    },
                    colors: ['#f59e0b'],
                    stroke: {
                        curve: 'smooth',
                        width: 2.5
                    },
                    fill: {
                        type: 'gradient',
                        gradient: {
                            shadeIntensity: 1,
                            opacityFrom: 0.45,
                            opacityTo: 0.05,
                            stops: [0, 90, 100]
                        }
                    },
                    dataLabels: {
                        enabled: false
                    },
                    markers: {
                        size: 0,
                        hover: {
                            size: 5
                        }
                    },
                    xaxis: {
                        categories: kinerjaData.categories,
                        labels: {
                            style: {
                                colors: '#374151',
                                fontSize: '12px',
                                fontWeight: 500
                            }
                        },
                        axisBorder: {
                            show: false
                        },
                        axisTicks: {
                            show: false
                        }
                    },
                    yaxis: {
                        tickAmount: 5,
                        labels: {
                            formatter: (val) => val.toFixed(1),
                            style: {
                                colors: '#6b7280',
                                fontSize: '11px'
                            }
                        }
                    },
                    grid: {
                        borderColor: '#e5e7eb',
                        strokeDashArray: 3,
                        xaxis: {
                            lines: {
                                show: true
                            }
                        },
                        yaxis: {
                            lines: {
                                show: true
                            }
                        },
                        padding: {
                            top: 0,
                            right: 5,
                            bottom: 0,
                            left: 5
                        }
                    },
                    tooltip: {
                        y: {
                            formatter: (val) => 'Rp ' + val.toFixed(1) + ' Juta'
                        }
                    }
                };

                const chart = new ApexCharts(container, options);
                chart.render();
            },

            // Unit Populer Chart
            initUnitChart() {
                const container = document.querySelector("#unitChart");
                if (!container) return;

                container.innerHTML = '';
                
                let unitPopulerData;
                try {
                    unitPopulerData = JSON.parse(container.getAttribute('data-chart'));
                } catch(e) {
                    console.error("Failed to parse unit data", e);
                    return;
                }

                const options = {
                    series: [{
                            name: 'Unit Penyewaan Alat',
                            data: unitPopulerData.rental
                        },
                        {
                            name: 'Unit Penjualan Gas',
                            data: unitPopulerData.gas
                        },
                        {
                            name: 'Unit Peminjaman Mobil',
                            data: unitPopulerData.mobil
                        },
                        {
                            name: 'Unit Fasilitas Umum',
                            data: unitPopulerData.fasilitas
                        },
                        {
                            name: 'Pelaporan Warga',
                            data: unitPopulerData.laporan
                        },
                        {
                            name: 'Pengumuman & Event',
                            data: unitPopulerData.pengumuman
                        }
                    ],
                    chart: {
                        type: 'bar',
                        height: 280,
                        toolbar: {
                            show: false
                        },
                        stacked: false,
                        background: 'transparent'
                    },
                    colors: ['#f59e0b', '#3b82f6', '#10b981', '#8b5cf6', '#ef4444', '#06b6d4'],
                    plotOptions: {
                        bar: {
                            horizontal: false,
                            columnWidth: '50%',
                            borderRadius: 4,
                            dataLabels: {
                                position: 'top'
                            }
                        }
                    },
                    dataLabels: {
                        enabled: false
                    },
                    xaxis: {
                        categories: unitPopulerData.categories,
                        labels: {
                            style: {
                                colors: '#374151',
                                fontSize: '12px',
                                fontWeight: 500
                            }
                        },
                        axisBorder: {
                            show: false
                        },
                        axisTicks: {
                            show: false
                        }
                    },
                    yaxis: {
                        labels: {
                            style: {
                                colors: '#6b7280',
                                fontSize: '11px'
                            }
                        }
                    },
                    grid: {
                        borderColor: '#e5e7eb',
                        strokeDashArray: 3,
                        padding: {
                            top: 0,
                            right: 10,
                            bottom: 0,
                            left: 5
                        }
                    },
                    legend: {
                        show: false
                    },
                    tooltip: {
                        shared: true,
                        intersect: false
                    }
                };

                const chart = new ApexCharts(container, options);
                chart.render();
            },

            // Unit Carousel
            initUnitCarousel() {
                const cards = Array.from(document.querySelectorAll('.unit-card'));
                if (cards.length === 0) return;

                const titleElement = document.getElementById('unit-title');
                const nextBtn = document.getElementById('unit-next');
                const prevBtn = document.getElementById('unit-prev');

                const n = cards.length;
                let currentIndex = 0;
                let autoSlideInterval;
                const autoSlideDelay = 3000;

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

                        if (diff === 0 && titleElement) {
                            titleElement.style.opacity = '0';
                            setTimeout(() => {
                                titleElement.textContent = card.getAttribute('data-name');
                                titleElement.style.opacity = '1';
                            }, 200);
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
                    newNext.parentElement.classList.remove('z-60');
                    newNext.parentElement.classList.add('z-[60]');
                }
                if (prevBtn) {
                    const newPrev = prevBtn.cloneNode(true);
                    prevBtn.parentNode.replaceChild(newPrev, prevBtn);
                    newPrev.addEventListener('click', () => {
                        handlePrev();
                        resetAutoSlide();
                    });
                }

                // Pause on hover (optional but good UX)
                const container = document.getElementById('unit-carousel-container');
                if (container) {
                    container.addEventListener('mouseenter', () => clearInterval(autoSlideInterval));
                    container.addEventListener('mouseleave', startAutoSlide);
                }

                updateCarousel();
                startAutoSlide();

                // Click handlers are managed via inline onclick attributes in HTML
            },
        };
        // Initialize
        BerandaPage.init();

        // --- LIVE SEARCH (AJAX GRID) ---
        const searchInput = document.getElementById('beranda-search-input');
        const searchResultsSection = document.getElementById('search-results-section');
        const searchResultsGrid = document.getElementById('search-results-grid');
        const searchTitle = document.getElementById('search-title');
        let searchTimeout = null;

        if (searchInput && searchResultsSection && searchResultsGrid) {
            // Handle Input Typing
            searchInput.addEventListener('input', function() {
                const query = this.value.trim();

                clearTimeout(searchTimeout);

                if (query.length < 2) {
                    searchResultsSection.classList.remove('opacity-100', 'translate-y-0');
                    searchResultsSection.classList.add('opacity-0', '-translate-y-4');
                    setTimeout(() => {
                        if (searchInput.value.length < 2) {
                            searchResultsSection.classList.add('hidden');
                        }
                    }, 400);
                    return;
                }

                // Debounce Request (300ms)
                searchTimeout = setTimeout(() => {
                    fetch(`1?search=${encodeURIComponent(query)}`, {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        // Avoid race condition: ignore if input changed while fetching
                        if (searchInput.value.trim() !== query) return;
                        
                        renderSearchResults(data, query);
                    })
                    .catch(error => {
                        if (searchInput.value.trim() !== query) return;
                        console.error("Live search error:", error);
                    });
                }, 300);
            });

            function renderSearchResults(data, query) {
                // Update title
                searchTitle.innerText = `Hasil Pencarian: "${query}"`;

                if (data.length === 0) {
                    searchResultsGrid.innerHTML = `
                        <div class="w-full text-center py-8">
                            <div class="bg-gray-50 rounded-lg p-8 inline-block">
                                <i class="bx bx-search-alt text-4xl text-gray-300 mb-3 block"></i>
                                <p class="text-gray-500">Tidak ada produk yang cocok dengan pencarian Anda.</p>
                            </div>
                        </div>`;
                } else {
                    let html = '';
                    data.forEach(item => {
                        let badgeColor = 'bg-gray-100 text-gray-600';
                        let badgeText = item.type.toUpperCase();
                        
                        if (item.type === 'rental') { badgeColor = 'custom-badge-rental'; badgeText = 'Sewa'; }
                        if (item.type === 'gas') { badgeColor = 'custom-badge-gas'; badgeText = 'Beli'; }
                        if (item.type === 'profile') { badgeColor = 'custom-badge-profile'; badgeText = 'Profil'; }
                        if (item.type === 'region') { badgeColor = 'custom-badge-region'; badgeText = 'Wilayah'; }

                        let priceHtml = '';
                        if (item.type === 'profile') {
                            priceHtml = `<p class="text-xs text-gray-500 font-medium mt-2">${item.price_formatted}</p>`;
                        } else if (item.type === 'region') {
                            priceHtml = `
                            <div class="mt-3">
                                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 custom-region-badge border rounded-lg text-xs font-bold shadow-sm">
                                    <i class='bx bx-map custom-region-icon text-sm'></i>
                                    ${item.price_formatted}
                                </span>
                            </div>`;
                        } else {
                            priceHtml = `
                                <div class="flex items-center justify-between mt-3">
                                    <div>
                                        <p class="text-[10px] text-gray-400 font-medium uppercase tracking-wider mb-0.5">Harga ${badgeText}</p>
                                        <p class="text-base font-black text-[#115789]">${item.price_formatted} <span class="text-[10px] font-medium text-gray-500 normal-case">/${item.unit}</span></p>
                                    </div>
                                    <div class="bg-blue-50 w-8 h-8 rounded-full flex items-center justify-center group-hover:bg-[#115789] transition-colors duration-300">
                                        <i class='bx bx-right-arrow-alt text-blue-600 group-hover:text-white text-xl'></i>
                                    </div>
                                </div>
                            `;
                        }

                        html += `
                        <a href="${item.link}" class="block p-4 group">
                            <div class="product-card bg-white rounded-[2rem] p-6 shadow-lg hover:shadow-2xl transition-all duration-300 hover:-translate-y-2 mx-auto w-full max-w-[380px] h-full flex flex-col">
                                ${item.image_url ? `
                                <div class="product-image-wrapper mb-6 relative aspect-[4/3] overflow-hidden rounded-2xl flex-shrink-0">
                                    <img src="${item.image_url}" alt="${item.name}" loading="lazy" class="product-image w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                                </div>
                                ` : ''}
                                <div class="product-info text-center flex-1 flex flex-col justify-center">
                                    <h3 class="product-name text-sm font-bold text-gray-800 mb-2">${item.name}</h3>
                                    <div>
                                        <span class="inline-block px-3 py-1 text-[10px] font-bold rounded-full ${badgeColor} mt-1">${badgeText}</span>
                                    </div>
                                    ${item.type === 'profile' || item.type === 'region' ? priceHtml : ''}
                                </div>
                                ${item.type !== 'profile' && item.type !== 'region' ? priceHtml : ''}
                            </div>
                        </a>
                        `;
                    });
                    searchResultsGrid.innerHTML = html;
                }
                
                // Show the section with smooth animation
                searchResultsSection.classList.remove('hidden');
                // Trigger reflow
                void searchResultsSection.offsetWidth;
                searchResultsSection.classList.remove('opacity-0', '-translate-y-4');
                searchResultsSection.classList.add('opacity-100', 'translate-y-0');
            }
        }

        // Check if user just logged out and show login modal
        (session('logout_success'))
            // Wait a bit for the page to fully load
            setTimeout(function() {
                const loginButton = document.getElementById('btn-open-login');
                if (loginButton) {
                    loginButton.click();
                }
            }, 300);
        
        })();
    
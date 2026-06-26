const BerandaPage = {
            // Initialize all components
            init() {
                this.initYearSelectors(); // Call this FIRST to ensure filters always work
                try {
                    this.initCarousel();
                } catch (e) {
                    console.error("Carousel failed to initialize:", e);
                }
                try {
                    this.initCharts();
                } catch (e) {
                    console.error("Charts failed to initialize:", e);
                }
                this.initUnitCarousel();
                this.initNavbarMarginSync();
            },

            // Sinkronisasi tinggi layer blur dan padding
            initNavbarMarginSync() {
                const navbar = document.getElementById('master-navbar');
                if (!navbar) return;

                const syncHeights = () => {
                    document.body.style.setProperty('--nav-height', navbar.offsetHeight + 'px');
                };
                
                syncHeights();
                window.addEventListener('resize', syncHeights);
                const logoImg = navbar.querySelector('.sd-nav-logo img');
                if (logoImg) logoImg.addEventListener('load', syncHeights);
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
                            } else if (!oldEl && newEl) {
                                // If search-results-section didn't exist before, insert it before populer-section
                                if (id === 'search-results-section') {
                                    const populer = document.getElementById('populer-section');
                                    if (populer) {
                                        const wrapper = document.createElement('div');
                                        wrapper.id = 'search-results-section';
                                        wrapper.className = newEl.className;
                                        wrapper.innerHTML = newEl.innerHTML;
                                        populer.parentNode.insertBefore(wrapper, populer);
                                        updatedAny = true;
                                    }
                                }
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

                const blurCarouselSlides = document.getElementById('blur-carousel-slides');
                if (blurCarouselSlides) {
                    const populateBlurSlides = () => {
                        blurCarouselSlides.innerHTML = '';
                        const slides = carouselSlides.querySelectorAll('.carousel-slide');
                        slides.forEach((slide) => {
                            const desktopImg = slide.querySelector('img[class*="md:block"]');
                            const mobileImg = slide.querySelector('img[class*="md:hidden"]');
                            let img = slide.querySelector('img');
                            if (desktopImg && mobileImg) {
                                img = window.innerWidth >= 768 ? desktopImg : mobileImg;
                            }
                            const blurSlide = document.createElement('div');
                            blurSlide.className = 'blur-slide';
                            if (img) blurSlide.style.backgroundImage = `url('${img.getAttribute('src')}')`;
                            blurCarouselSlides.appendChild(blurSlide);
                        });
                    };
                    populateBlurSlides();
                    window.addEventListener('resize', () => { setTimeout(populateBlurSlides, 200); }, { passive: true });
                }

                const prevButton = document.getElementById('carousel-prev');
                const nextButton = document.getElementById('carousel-next');
                // Use let so we can update the reference after cloning
                let indicators = document.querySelectorAll('.carousel-indicator');

                let currentSlide = 0;
                const totalSlides = 3;
                let autoSlideInterval;
                const autoSlideDelay = 7000; // 7 Seconds

                let blurTimeout;
                const goToSlide = (slideIndex) => {
                    currentSlide = slideIndex;
                    requestAnimationFrame(() => {
                        carouselSlides.style.transform = `translateX(-${slideIndex * 100}%)`;
                        const blurCarouselSlides = document.getElementById('blur-carousel-slides');
                        if (blurCarouselSlides) blurCarouselSlides.style.transform = `translateX(-${slideIndex * 100}%)`;
                    });

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
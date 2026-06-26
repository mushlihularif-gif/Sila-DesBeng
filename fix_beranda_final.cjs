const fs = require('fs');
const file = 'resources/views/beranda/index.blade.php';
let content = fs.readFileSync(file, 'utf8');

// Replace HTML for navbar-blur-bg
content = content.replace(
    '<!-- Layer khusus untuk efek blur di belakang navbar (hanya di paling atas) -->\n        <div id="navbar-blur-bg"></div>',
    '<!-- Layer khusus untuk efek blur di belakang navbar (hanya di paling atas) -->\n        <div id="navbar-blur-bg">\n            <div id="blur-carousel-slides" class="flex transition-transform duration-500 ease-out h-full w-full"></div>\n        </div>'
);

// Replace CSS for navbar-blur-bg
const cssRegex = /\/\* Styling untuk layer buram di belakang navbar \*\/[\s\S]*?#beranda \{\s*transition: padding-top 0\.3s ease;\s*\}/;
const newCss = `/* Styling untuk layer sinkron di belakang navbar */
        #navbar-blur-bg {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: var(--nav-height, 96px);
            z-index: 40;
            overflow: hidden;
            transform: translateZ(0); /* HW acceleration */
            transition: transform 0.3s ease-in-out;
            pointer-events: none;
            will-change: transform;
        }
        body:has(#master-navbar.hidden-nav) #navbar-blur-bg {
            transform: translateY(-100%) translateZ(0);
        }
        .blur-slide {
            min-width: 100%;
            height: 400px;
            background-size: cover;
            background-position: center;
            flex-shrink: 0;
        }
        @media (min-width: 768px) { .blur-slide { height: 40vw; } }
        @media (min-width: 1024px) { .blur-slide { height: 45vw; } }

        #beranda {
            padding-top: var(--nav-height, 96px);
            transition: padding-top 0.3s ease-in-out;
            will-change: padding-top, scroll-position;
        }
        body:has(#master-navbar.hidden-nav) #beranda {
            padding-top: 0 !important;
        }`;
content = content.replace(cssRegex, newCss);

// Replace initNavbarMarginSync
const syncRegex = /\/\/ Sinkronisasi margin beranda dengan navbar dan fake blur[\s\S]*?initYearSelectors\(\) \{/;
const newSync = `// Sinkronisasi tinggi layer blur dan padding
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
            initYearSelectors() {`;
content = content.replace(syncRegex, newSync);

// Replace initCarousel
const carouselRegex = /initCarousel\(\) \{[\s\S]*?const prevButton = document\.getElementById\('carousel-prev'\);/;
const newCarousel = `initCarousel() {
                const carouselSlides = document.getElementById('carousel-slides');
                if (!carouselSlides) return;

                const blurCarouselSlides = document.getElementById('blur-carousel-slides');
                if (blurCarouselSlides) {
                    const populateBlurSlides = () => {
                        blurCarouselSlides.innerHTML = '';
                        const slides = carouselSlides.querySelectorAll('.carousel-slide');
                        slides.forEach((slide) => {
                            const desktopImg = slide.querySelector('img.md\\\\:block, img.hidden.md\\\\:block');
                            const mobileImg = slide.querySelector('img.md\\\\:hidden, img.block.md\\\\:hidden');
                            let img = slide.querySelector('img');
                            if (desktopImg && mobileImg) {
                                img = window.innerWidth >= 768 ? desktopImg : mobileImg;
                            }
                            const blurSlide = document.createElement('div');
                            blurSlide.className = 'blur-slide';
                            if (img) blurSlide.style.backgroundImage = \`url('\${img.getAttribute('src')}')\`;
                            blurCarouselSlides.appendChild(blurSlide);
                        });
                    };
                    populateBlurSlides();
                    window.addEventListener('resize', () => { setTimeout(populateBlurSlides, 200); }, { passive: true });
                }

                const prevButton = document.getElementById('carousel-prev');`;
content = content.replace(carouselRegex, newCarousel);

// Replace goToSlide
const goToSlideRegex = /const goToSlide = \(slideIndex\) => \{[\s\S]*?indicator\.classList\.toggle\('bg-white', index === slideIndex\);/;
const newGoToSlide = `const goToSlide = (slideIndex) => {
                    currentSlide = slideIndex;
                    carouselSlides.style.transform = \`translateX(-\${slideIndex * 100}%)\`;
                    const blurCarouselSlides = document.getElementById('blur-carousel-slides');
                    if (blurCarouselSlides) blurCarouselSlides.style.transform = \`translateX(-\${slideIndex * 100}%)\`;

                    // indicators variable now points to the LIVE elements in DOM
                    indicators.forEach((indicator, index) => {
                        indicator.classList.toggle('bg-white', index === slideIndex);`;
content = content.replace(goToSlideRegex, newGoToSlide);

fs.writeFileSync(file, content);
console.log("Success");

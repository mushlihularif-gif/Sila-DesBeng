const fs = require('fs');
const file = 'resources/views/beranda/index.blade.php';
let content = fs.readFileSync(file, 'utf8');

// Add will-change and requestAnimationFrame
content = content.replace(
    'id="carousel-slides" class="flex transition-transform duration-500 ease-out"',
    'id="carousel-slides" class="flex transition-transform duration-500 ease-out" style="will-change: transform; transform: translateZ(0);"'
);

content = content.replace(
    'id="blur-carousel-slides" class="flex transition-transform duration-500 ease-out h-full w-full"',
    'id="blur-carousel-slides" class="flex transition-transform duration-500 ease-out h-full w-full" style="will-change: transform; transform: translateZ(0);"'
);

const goToSlideRegex = /const goToSlide = \(slideIndex\) => \{[\s\S]*?if \(blurCarouselSlides\) blurCarouselSlides\.style\.transform = `translateX\(\-\$\{slideIndex \* 100\}%\)`;/;
const newGoToSlide = `const goToSlide = (slideIndex) => {
                    currentSlide = slideIndex;
                    requestAnimationFrame(() => {
                        carouselSlides.style.transform = \`translateX(-\${slideIndex * 100}%)\`;
                        const blurCarouselSlides = document.getElementById('blur-carousel-slides');
                        if (blurCarouselSlides) blurCarouselSlides.style.transform = \`translateX(-\${slideIndex * 100}%)\`;
                    });`;
content = content.replace(goToSlideRegex, newGoToSlide);

fs.writeFileSync(file, content);
console.log("Success");

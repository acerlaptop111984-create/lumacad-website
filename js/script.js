const navLinks = document.querySelectorAll('nav a');
const sections = document.querySelectorAll('section[id]');

function updateActiveLink() {
    let current = 'home';

    sections.forEach(section => {
        const sectionTop = section.offsetTop - 100;
        if (window.scrollY >= sectionTop) {
            current = section.getAttribute('id');
        }
    });

    navLinks.forEach(link => {
        link.classList.remove('active');
        const href = link.getAttribute('href');
        if (href === '#' + current || (href === 'index.php' && current === 'home')) {
            link.classList.add('active');
        }
    });
}

if (sections.length > 0) {
    window.addEventListener('scroll', updateActiveLink);
    window.addEventListener('load', updateActiveLink);
}

navLinks.forEach(link => {
    link.addEventListener('click', function () {
        navLinks.forEach(l => l.classList.remove('active'));
        this.classList.add('active');
    });
});

window.addEventListener('load', function () {
    const main = document.querySelector('main');
    if (main) {
        main.classList.add('fade-in');
    }
});

let currentSlide = 0;
const container = document.getElementById('carouselContainer');
const slides = container ? container.querySelectorAll('.carousel-slide') : [];

function getSlidesPerView() {
    if (window.innerWidth <= 768) return 1;
    if (window.innerWidth <= 1024) return 2;
    return 3;
}

function updateCarousel() {
    const slidesPerView = getSlidesPerView();
    const maxSlide = Math.max(0, slides.length - slidesPerView);

    if (currentSlide > maxSlide) {
        currentSlide = maxSlide;
    }
    if (currentSlide < 0) {
        currentSlide = 0;
    }

    const slideWidth = slides[0]?.offsetWidth || 0;
    const gap = 32; // 2rem gap
    const offset = currentSlide * (slideWidth + gap);

    if (container) {
        container.style.transform = `translateX(-${offset}px)`;
    }

    updateDots();
}

function moveSlide(direction) {
    const slidesPerView = getSlidesPerView();
    const maxSlide = Math.max(0, slides.length - slidesPerView);

    currentSlide += direction;

    if (currentSlide > maxSlide) {
        currentSlide = 0;
    }
    if (currentSlide < 0) {
        currentSlide = maxSlide;
    }

    updateCarousel();
}

function goToSlide(index) {
    currentSlide = index;
    updateCarousel();
}

function updateDots() {
    const dots = document.querySelectorAll('.dot');
    dots.forEach((dot, index) => {
        dot.classList.toggle('active', index === currentSlide);
    });
}

function createDots() {
    const dotsContainer = document.getElementById('carouselDots');
    if (!dotsContainer) return;

    const slidesPerView = getSlidesPerView();
    const dotCount = Math.max(1, slides.length - slidesPerView + 1);

    dotsContainer.innerHTML = '';
    for (let i = 0; i < dotCount; i++) {
        const dot = document.createElement('span');
        dot.className = 'dot' + (i === 0 ? ' active' : '');
        dot.onclick = () => goToSlide(i);
        dotsContainer.appendChild(dot);
    }
}

if (slides.length > 0) {
    window.addEventListener('load', function () {
        createDots();
        updateCarousel();
    });

    window.addEventListener('resize', function () {
        createDots();
        updateCarousel();
    });
}

document.addEventListener('DOMContentLoaded', function () {
    // Fade in effect for all pages
    const main = document.querySelector('main, .customer-main, .contact-main, .pricing-main');
    if (main) {
        main.style.opacity = '0';
        main.style.transform = 'translateY(10px)';
        setTimeout(function () {
            main.style.transition = 'opacity 0.4s ease, transform 0.4s ease';
            main.style.opacity = '1';
            main.style.transform = 'translateY(0)';
        }, 50);
    }


});

function toggleSidebar() {
    const sidebar = document.getElementById('sidebarNav');
    const overlay = document.querySelector('.sidebar-overlay');

    if (sidebar) {
        sidebar.classList.toggle('active');
    }
    if (overlay) {
        overlay.classList.toggle('active');
    }
    document.body.classList.toggle('sidebar-open');

    if (document.body.classList.contains('sidebar-open')) {
        document.body.style.position = 'fixed';
        document.body.style.width = '100%';
    } else {
        document.body.style.position = '';
        document.body.style.width = '';
    }
}

document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
        const sidebar = document.getElementById('sidebarNav');
        if (sidebar && sidebar.classList.contains('active')) {
            toggleSidebar();
        }
    }
});
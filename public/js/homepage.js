// Events slider logic
(function() {
    const track = document.getElementById('sliderTrack');
    if (!track) return;
    const prevBtn = document.getElementById('prevBtn');
    const nextBtn = document.getElementById('nextBtn');
    const dotsContainer = document.getElementById('sliderDots');

    let slides = Array.from(track.querySelectorAll('[data-event]'));
    if (slides.length === 0) return;

    const firstClone = slides[0].cloneNode(true);
    const lastClone = slides[slides.length - 1].cloneNode(true);
    firstClone.setAttribute('data-clone', 'first');
    lastClone.setAttribute('data-clone', 'last');
    track.insertBefore(lastClone, slides[0]);
    track.appendChild(firstClone);

    slides = Array.from(track.querySelectorAll('[data-event]'));

    let index = 1; 
    let isTransitioning = false;
    let autoplayTimer = null;
    const AUTOPLAY_MS = 4000;
    const TRANSITION_MS = 500;

    function updateDots(activeIndex) {
        dotsContainer.innerHTML = '';
        const realCount = slides.length - 2;
        for (let i = 0; i < realCount; i++) {
            const btn = document.createElement('button');
            btn.className = 'dot' + (i === activeIndex ? ' active' : '');
            btn.setAttribute('role', 'tab');
            btn.setAttribute('aria-selected', i === activeIndex ? 'true' : 'false');
            btn.setAttribute('tabindex', i === activeIndex ? '0' : '-1');
            btn.addEventListener('click', () => goTo(i + 1));
            dotsContainer.appendChild(btn);
        }
    }

    function setTranslate() {
        const percentage = index * -100;
        track.style.transform = 'translateX(' + percentage + '%)';
    }

    function startAutoplay() {
        stopAutoplay();
        autoplayTimer = setInterval(() => {
            next();
        }, AUTOPLAY_MS);
    }

    function stopAutoplay() {
        if (autoplayTimer) clearInterval(autoplayTimer);
        autoplayTimer = null;
    }

    function next() {
        if (isTransitioning) return;
        isTransitioning = true;
        index += 1;
        track.style.transition = `transform ${TRANSITION_MS}ms ease`;
        setTranslate();
        setTimeout(() => { if (isTransitioning) isTransitioning = false; }, TRANSITION_MS + 120);
    }

    function prev() {
        if (isTransitioning) return;
        isTransitioning = true;
        index -= 1;
        track.style.transition = `transform ${TRANSITION_MS}ms ease`;
        setTranslate();
        setTimeout(() => { if (isTransitioning) isTransitioning = false; }, TRANSITION_MS + 120);
    }

    function goTo(targetIndex) {
        if (isTransitioning) return;
        isTransitioning = true;
        index = targetIndex;
        track.style.transition = `transform ${TRANSITION_MS}ms ease`;
        setTranslate();
        setTimeout(() => { if (isTransitioning) isTransitioning = false; }, TRANSITION_MS + 120);
    }

    track.addEventListener('transitionend', () => {
        const realCount = slides.length - 2;
        if (slides[index].getAttribute('data-clone') === 'first') {
            track.style.transition = 'none';
            index = 1;
            setTranslate();
        } else if (slides[index].getAttribute('data-clone') === 'last') {
            track.style.transition = 'none';
            index = realCount;
            setTranslate();
        }
        requestAnimationFrame(() => {
            requestAnimationFrame(() => {
                track.style.transition = '';
                isTransitioning = false;
                updateDots(index - 1);
            });
        });
    });

    // Controls
    nextBtn.addEventListener('click', () => { stopAutoplay(); next(); startAutoplay(); });
    prevBtn.addEventListener('click', () => { stopAutoplay(); prev(); startAutoplay(); });

    // Keyboard
    track.addEventListener('keydown', (e) => {
        if (e.key === 'ArrowRight') { stopAutoplay(); next(); startAutoplay(); }
        if (e.key === 'ArrowLeft') { stopAutoplay(); prev(); startAutoplay(); }
    });

    // Pause on hover
    const sliderSection = document.getElementById('events-slider');
    sliderSection.addEventListener('mouseenter', stopAutoplay);
    sliderSection.addEventListener('mouseleave', startAutoplay);

    // Touch
    let touchStartX = 0;
    let touchDeltaX = 0;
    track.addEventListener('touchstart', (e) => {
        stopAutoplay();
        touchStartX = e.touches[0].clientX;
        touchDeltaX = 0;
    }, { passive: true });
    track.addEventListener('touchmove', (e) => {
        touchDeltaX = e.touches[0].clientX - touchStartX;
    }, { passive: true });
    track.addEventListener('touchend', () => {
        if (Math.abs(touchDeltaX) > 40) {
            if (touchDeltaX < 0) next(); else prev();
        }
        startAutoplay();
    });

    updateDots(index - 1);
    setTranslate();
    setTimeout(startAutoplay, 300);

    document.addEventListener('visibilitychange', () => {
        if (document.visibilityState === 'visible') startAutoplay();
        else stopAutoplay();
    });
})();

// Categories carousel controls
(function() {
    const track = document.getElementById('catTrack');
    if (!track) return;
    const prev = document.getElementById('catPrev');
    const next = document.getElementById('catNext');

    function scrollByCard(dir) {
        const card = track.querySelector('.cat-card');
        const amount = card ? card.getBoundingClientRect().width + 18 : 320;
        track.scrollBy({ left: dir * amount, behavior: 'smooth' });
    }

    prev.addEventListener('click', () => scrollByCard(-1));
    next.addEventListener('click', () => scrollByCard(1));
})();

// Sports carousel navigation
(() => {
    const track = document.getElementById('sportsTrack');
    if (!track) return;
    const prev = document.getElementById('sportsPrev');
    const next = document.getElementById('sportsNext');

    function scrollByCard(dir) {
        const card = track.querySelector('.cat-card');
        const amount = card ? card.getBoundingClientRect().width + 18 : 320;
        track.scrollBy({ left: dir * amount, behavior: 'smooth' });
    }

    prev.addEventListener('click', () => scrollByCard(-1));
    next.addEventListener('click', () => scrollByCard(1));
})();

// Category view functionality
function viewCategory(category) {
    console.log(`Viewing category: ${category}`);
    
    // Handle specific category redirects
    switch(category) {
        case 'nightlife':
            window.location.href = 'entertainment.php';
            break;
        case 'football':
            window.location.href = 'sports.php';
            break;
        default:
            // For other categories, show alert for now
            alert(`Viewing ${category} events! This would typically redirect to a category page or show filtered events.`);
            break;
    }
}

// Optional: Function to show category events (for future implementation)
function showCategoryEvents(category) {
    // This function could be implemented to:
    // 1. Filter events on the current page
    // 2. Load events via AJAX
    // 3. Redirect to a dedicated category page
    console.log(`Loading events for category: ${category}`);
}
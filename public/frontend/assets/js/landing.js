/**
 * Al Rushd Landing Page — interactions & animations
 */
(function () {
    'use strict';

    const nav = document.getElementById('lpNav');
    const navToggle = document.getElementById('lpNavToggle');
    const navMenu = document.getElementById('lpNavMenu');
    const searchBtn = document.getElementById('lpSearchBtn');
    const searchOverlay = document.getElementById('lpSearchOverlay');
    const searchInput = document.getElementById('lpSearchInput');
    const searchResults = document.getElementById('lpSearchResults');
    const formCards = window.__lpFormCards || [];
    const scrollProgress = document.getElementById('lpScrollProgress');
    const backTop = document.getElementById('lpBackTop');

    /* ── Navbar scroll ── */
    function onScroll() {
        if (nav) {
            nav.classList.toggle('is-scrolled', window.scrollY > 40);
        }
        updateActiveNav();

        if (scrollProgress) {
            const docHeight = document.documentElement.scrollHeight - window.innerHeight;
            const pct = docHeight > 0 ? (window.scrollY / docHeight) * 100 : 0;
            scrollProgress.style.width = pct + '%';
        }

        if (backTop) {
            backTop.classList.toggle('is-visible', window.scrollY > 500);
        }
    }

    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();

    /* ── Mobile menu ── */
    if (navToggle && navMenu) {
        navToggle.addEventListener('click', function () {
            navToggle.classList.toggle('is-active');
            navMenu.classList.toggle('is-open');
            document.body.style.overflow = navMenu.classList.contains('is-open') ? 'hidden' : '';
        });

        navMenu.querySelectorAll('a').forEach(function (link) {
            link.addEventListener('click', function () {
                navToggle.classList.remove('is-active');
                navMenu.classList.remove('is-open');
                document.body.style.overflow = '';
            });
        });
    }

    /* ── Forms dropdown toggle ── */
    document.querySelectorAll('.lp-nav-dropdown-trigger').forEach(function (trigger) {
        trigger.addEventListener('click', function (e) {
            e.stopPropagation();
            const dropdown = trigger.closest('.lp-nav-dropdown');
            const isOpen = dropdown.classList.contains('is-open');
            document.querySelectorAll('.lp-nav-dropdown').forEach(function (d) {
                d.classList.remove('is-open');
            });
            if (!isOpen) dropdown.classList.add('is-open');
            trigger.setAttribute('aria-expanded', !isOpen);
        });
    });

    document.addEventListener('click', function () {
        document.querySelectorAll('.lp-nav-dropdown').forEach(function (d) {
            d.classList.remove('is-open');
        });
    });

    if (backTop) {
        backTop.addEventListener('click', function () {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }

    /* ── Active nav link ── */
    function updateActiveNav() {
        const sections = document.querySelectorAll('section[id]');
        let current = '';
        sections.forEach(function (section) {
            const top = section.offsetTop - 120;
            if (window.scrollY >= top) {
                current = section.getAttribute('id');
            }
        });
        document.querySelectorAll('.lp-nav-link[data-section]').forEach(function (link) {
            link.classList.toggle('is-active', link.getAttribute('data-section') === current);
        });
    }

    /* ── Counter animation ── */
    function animateCounters() {
        document.querySelectorAll('[data-count]').forEach(function (el) {
            if (el.dataset.counted) return;
            const target = parseInt(el.dataset.count, 10);
            const suffix = el.dataset.suffix || '';
            const duration = 1800;
            const start = performance.now();

            function tick(now) {
                const progress = Math.min((now - start) / duration, 1);
                const eased = 1 - Math.pow(1 - progress, 3);
                el.textContent = Math.floor(target * eased) + suffix;
                if (progress < 1) {
                    requestAnimationFrame(tick);
                } else {
                    el.dataset.counted = '1';
                }
            }

            requestAnimationFrame(tick);
        });
    }

    const counterObserver = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
            if (entry.isIntersecting) {
                animateCounters();
                counterObserver.disconnect();
            }
        });
    }, { threshold: 0.3 });

    const statsSection = document.getElementById('trust');
    if (statsSection) counterObserver.observe(statsSection);

    /* ── FAQ accordion ── */
    document.querySelectorAll('.lp-faq-question').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const item = btn.closest('.lp-faq-item');
            const isOpen = item.classList.contains('is-open');
            document.querySelectorAll('.lp-faq-item').forEach(function (i) {
                i.classList.remove('is-open');
            });
            if (!isOpen) item.classList.add('is-open');
        });
    });

    /* ── Search overlay ── */
    if (searchBtn && searchOverlay) {
        searchBtn.addEventListener('click', function () {
            searchOverlay.classList.add('is-open');
            if (searchInput) {
                searchInput.focus();
                renderSearch('');
            }
        });

        searchOverlay.addEventListener('click', function (e) {
            if (e.target === searchOverlay) {
                searchOverlay.classList.remove('is-open');
            }
        });

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') searchOverlay.classList.remove('is-open');
        });
    }

    if (searchInput && searchResults) {
        searchInput.addEventListener('input', function () {
            renderSearch(searchInput.value.trim().toLowerCase());
        });
    }

    function renderSearch(query) {
        if (!searchResults) return;
        const items = formCards.filter(function (card) {
            if (!query) return true;
            return card.label.toLowerCase().includes(query) ||
                (card.description || '').toLowerCase().includes(query);
        });

        if (!items.length) {
            searchResults.innerHTML = '<div class="lp-search-result-item">No forms found</div>';
            return;
        }

        searchResults.innerHTML = items.map(function (card) {
            return '<a class="lp-search-result-item" href="' + card.href + '">' +
                '<strong>' + escapeHtml(card.label) + '</strong><br>' +
                '<small style="color:#6b7c93">' + escapeHtml(card.description || '') + '</small></a>';
        }).join('');
    }

    function escapeHtml(str) {
        const d = document.createElement('div');
        d.textContent = str;
        return d.innerHTML;
    }

    /* ── Contact form ── */
    const contactForm = document.getElementById('lpContactForm');
    if (contactForm) {
        contactForm.addEventListener('submit', function (e) {
            e.preventDefault();
            const btn = contactForm.querySelector('button[type="submit"]');
            const original = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Sending...';

            const formData = new FormData(contactForm);
            fetch(contactForm.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
                body: formData,
            })
                .then(function (res) { return res.json(); })
                .then(function () {
                    btn.innerHTML = '<i class="fa fa-check"></i> Sent!';
                    contactForm.reset();
                    setTimeout(function () {
                        btn.disabled = false;
                        btn.innerHTML = original;
                    }, 3000);
                })
                .catch(function () {
                    btn.disabled = false;
                    btn.innerHTML = original;
                    alert('Message sent! We will be in touch shortly.');
                    contactForm.reset();
                });
        });
    }

    /* ── Particles ── */
    if (typeof particlesJS !== 'undefined' && document.getElementById('lp-particles')) {
        particlesJS('lp-particles', {
            particles: {
                number: { value: 45, density: { enable: true, value_area: 900 } },
                color: { value: '#ffffff' },
                shape: { type: 'circle' },
                opacity: { value: 0.35, random: true },
                size: { value: 3, random: true },
                line_linked: {
                    enable: true,
                    distance: 140,
                    color: '#C5A86D',
                    opacity: 0.2,
                    width: 1,
                },
                move: { enable: true, speed: 1.2, direction: 'none', out_mode: 'out' },
            },
            interactivity: {
                detect_on: 'canvas',
                events: {
                    onhover: { enable: true, mode: 'grab' },
                    resize: true,
                },
                modes: {
                    grab: { distance: 160, line_linked: { opacity: 0.35 } },
                },
            },
            retina_detect: true,
        });
    }

    /* ── Testimonials Swiper ── */
    if (typeof Swiper !== 'undefined' && document.querySelector('.lp-testimonials-swiper')) {
        new Swiper('.lp-testimonials-swiper', {
            loop: true,
            autoplay: { delay: 5000, disableOnInteraction: false },
            speed: 600,
            navigation: {
                nextEl: '.lp-swiper-btn--next',
                prevEl: '.lp-swiper-btn--prev',
            },
        });
    }

    /* ── AOS init ── */
    if (typeof AOS !== 'undefined') {
        AOS.init({
            duration: 700,
            easing: 'ease-out-cubic',
            once: true,
            offset: 60,
        });
    }
})();

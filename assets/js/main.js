(() => {
    const menuToggle = document.querySelector('.menu-toggle');
    const siteNav = document.querySelector('.site-nav');

    if (menuToggle && siteNav) {
        menuToggle.addEventListener('click', () => {
            const expanded = menuToggle.getAttribute('aria-expanded') === 'true';
            menuToggle.setAttribute('aria-expanded', expanded ? 'false' : 'true');
            siteNav.classList.toggle('is-open');
        });

        siteNav.querySelectorAll('a').forEach((link) => {
            link.addEventListener('click', () => {
                menuToggle.setAttribute('aria-expanded', 'false');
                siteNav.classList.remove('is-open');
            });
        });
    }

    const revealTargets = document.querySelectorAll('.section, .featured-card, .car-card, .timeline-list li, .admin-panel, .stat-card');

    if (!('IntersectionObserver' in window) || revealTargets.length === 0) {
        return;
    }

    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                entry.target.classList.add('in-view');
                observer.unobserve(entry.target);
            }
        });
    }, {
        rootMargin: '0px 0px -8% 0px',
        threshold: 0.05,
    });

    revealTargets.forEach((target, index) => {
        target.classList.add('reveal-ready');
        target.style.transitionDelay = `${Math.min(index * 25, 180)}ms`;
        observer.observe(target);
    });
})();

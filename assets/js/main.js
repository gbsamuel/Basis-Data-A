/**
 * SIREKA - Main JavaScript Helpers
 */

document.addEventListener('DOMContentLoaded', function() {
    // 1. Mobile Sidebar Toggle
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebar = document.querySelector('.sidebar');
    if (sidebarToggle && sidebar) {
        sidebarToggle.addEventListener('click', function() {
            sidebar.classList.toggle('d-none');
        });
    }

    // 2. Initialize Bootstrap Tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // 3. Dynamic Company -> Division selector for forms
    const companySelect = document.getElementById('filter_company');
    const divisionSelect = document.getElementById('filter_division');
    
    if (companySelect && divisionSelect) {
        companySelect.addEventListener('change', function() {
            const selectedCompany = this.value;
            const options = divisionSelect.querySelectorAll('option');
            
            options.forEach(option => {
                if (!option.value) return; // keep "Semua Divisi"
                const optionCompany = option.getAttribute('data-company');
                if (!selectedCompany || optionCompany === selectedCompany) {
                    option.style.display = '';
                } else {
                    option.style.display = 'none';
                }
            });
            
            // Reset to first option
            divisionSelect.selectedIndex = 0;
        });
    }

    // 4. Object Scroll Move & Stagger Animation System (Atas & Bawah)
    initScrollAnimations();
});

/**
 * Object Move & Staggered Scroll Animation System
 */
function initScrollAnimations() {
    // 1. Top Reading Scroll Progress Bar
    let progressBar = document.getElementById('scrollProgressBar');
    if (!progressBar) {
        progressBar = document.createElement('div');
        progressBar.id = 'scrollProgressBar';
        progressBar.className = 'scroll-progress-bar';
        document.body.prepend(progressBar);
    }

    // 2. Scroll Tracker (Direction & Glassmorphism)
    let lastScrollTop = window.pageYOffset || document.documentElement.scrollTop;
    let ticking = false;

    function handleScroll() {
        const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
        const docHeight = document.documentElement.scrollHeight - document.documentElement.clientHeight;
        
        // Update reading progress bar (0% - 100%)
        if (docHeight > 0) {
            const scrollPercent = Math.min(100, Math.max(0, (scrollTop / docHeight) * 100));
            progressBar.style.width = scrollPercent + '%';
        }

        // Header glassmorphism on scroll
        if (scrollTop > 20) {
            document.body.classList.add('is-scrolled');
        } else {
            document.body.classList.remove('is-scrolled');
        }

        // Scroll Direction (Atas & Bawah)
        if (Math.abs(scrollTop - lastScrollTop) > 6) {
            if (scrollTop > lastScrollTop && scrollTop > 60) {
                document.body.classList.add('scrolling-down');
                document.body.classList.remove('scrolling-up');
            } else {
                document.body.classList.add('scrolling-up');
                document.body.classList.remove('scrolling-down');
            }
            lastScrollTop = scrollTop;
        }

        ticking = false;
    }

    window.addEventListener('scroll', function() {
        if (!ticking) {
            window.requestAnimationFrame(handleScroll);
            ticking = true;
        }
    }, { passive: true });

    handleScroll();

    // 3. Staggered Item Discovery ("Muncul Satu-Satu" & Move Effect)
    // Find all group containers to stagger their children sequentially
    const groupContainers = document.querySelectorAll(
        '.row, .accordion, .timeline-steps, .list-group, .card-deck'
    );

    groupContainers.forEach(container => {
        // Collect direct children or child items
        let items = [];
        if (container.classList.contains('row')) {
            items = Array.from(container.children).filter(child => 
                child.matches('[class*="col-"], .col')
            );
        } else if (container.classList.contains('accordion')) {
            items = Array.from(container.querySelectorAll('.accordion-item'));
        } else if (container.classList.contains('timeline-steps')) {
            items = Array.from(container.querySelectorAll('.timeline-step'));
        } else {
            items = Array.from(container.children);
        }

        items.forEach((item, idx) => {
            if (!item.matches('.reveal-on-scroll, .reveal-fade-up, .reveal-fade-down, .reveal-fade-left, .reveal-fade-right, .reveal-scale, .reveal-item')) {
                item.classList.add('reveal-item');
            }
            // Assign sequential stagger delay so items appear one by one
            const delay = Math.min((idx % 6) * 0.1, 0.6);
            item.style.transitionDelay = `${delay}s`;
        });
    });

    // Standalone cards, tables, and banners
    const standaloneElements = document.querySelectorAll(
        '.card-custom, .card, .table-responsive, .loa-container, .alert'
    );
    standaloneElements.forEach(el => {
        // If it's already inside an animated column, let the column handle it
        if (!el.closest('.reveal-item') && !el.matches('.reveal-on-scroll, .reveal-fade-up, .reveal-fade-down, .reveal-fade-left, .reveal-fade-right, .reveal-scale, .reveal-item')) {
            el.classList.add('reveal-item');
        }
    });

    // 4. Bi-directional Intersection Observer
    const allAnimatedItems = document.querySelectorAll(
        '.reveal-on-scroll, .reveal-item, .reveal-fade-up, .reveal-fade-down, .reveal-fade-left, .reveal-fade-right, .reveal-scale'
    );

    if ('IntersectionObserver' in window) {
        const observerOptions = {
            root: null,
            threshold: 0.12,
            rootMargin: '0px 0px -40px 0px'
        };

        const scrollObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    // Item enters viewport -> trigger move and appear
                    entry.target.classList.add('revealed');
                } else {
                    // Check if item scrolled completely out of view above or below
                    const rect = entry.boundingClientRect;
                    if (rect.top > window.innerHeight || rect.bottom < 0) {
                        // Reset so when user scrolls back in either direction, it moves & appears again
                        entry.target.classList.remove('revealed');
                    }
                }
            });
        }, observerOptions);

        allAnimatedItems.forEach(el => scrollObserver.observe(el));

        // Smooth initial entrance for items already in the viewport on page load
        setTimeout(() => {
            allAnimatedItems.forEach(el => {
                const rect = el.getBoundingClientRect();
                if (rect.top < window.innerHeight && rect.bottom > 0) {
                    el.classList.add('revealed');
                }
            });
        }, 60);

    } else {
        // Fallback for non-supporting browsers
        allAnimatedItems.forEach(el => el.classList.add('revealed'));
    }
}

/**
 * Confirmation dialog helper
 */
function confirmAction(message, formId) {
    if (confirm(message || 'Apakah Anda yakin ingin melanjutkan tindakan ini?')) {
        if (formId) {
            document.getElementById(formId).submit();
        }
        return true;
    }
    return false;
}

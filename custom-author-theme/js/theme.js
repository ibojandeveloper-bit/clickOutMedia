/**
 * Custom Author Theme JavaScript
 * Handles lazy loading, animations, and enhanced UX
 */

(function() {
    'use strict';
    
    // Initialize when DOM is ready
    document.addEventListener('DOMContentLoaded', function() {
        initLazyLoading();
        initAnimations();
        initAccessibility();
        initPerformanceOptimizations();
    });
    
    /**
     * Lazy loading for images
     */
    function initLazyLoading() {
        // Native lazy loading support check
        if ('loading' in HTMLImageElement.prototype) {
            // Browser supports native lazy loading
            const images = document.querySelectorAll('img[loading="lazy"]');
            images.forEach(img => {
                img.src = img.dataset.src || img.src;
            });
        } else {
            // Fallback for browsers without native support
            const imageObserver = new IntersectionObserver((entries, observer) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const img = entry.target;
                        img.src = img.dataset.src || img.src;
                        img.classList.remove('lazy');
                        observer.unobserve(img);
                    }
                });
            });
            
            document.querySelectorAll('img[loading="lazy"]').forEach(img => {
                imageObserver.observe(img);
            });
        }
    }
    
    /**
     * Initialize scroll animations
     */
    function initAnimations() {
        const animationObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('fade-in');
                }
            });
        }, {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        });
        
        // Observe elements for animation
        document.querySelectorAll('.post-item, .news-item, .author-profile, .latest-posts').forEach(el => {
            animationObserver.observe(el);
        });
    }
    
    /**
     * Enhance accessibility
     */
    function initAccessibility() {
        // Add skip links functionality
        const skipLink = document.querySelector('.skip-link');
        if (skipLink) {
            skipLink.addEventListener('click', function(e) {
                e.preventDefault();
                const target = document.querySelector('#primary');
                if (target) {
                    target.focus();
                    target.scrollIntoView({ behavior: 'smooth' });
                }
            });
        }
        
        // Enhance keyboard navigation for social links
        const socialLinks = document.querySelectorAll('.social-link');
        socialLinks.forEach(link => {
            link.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    this.click();
                }
            });
        });
        
        // Add ARIA labels to pagination
        const pageNumbers = document.querySelectorAll('.page-number');
        pageNumbers.forEach((link, index) => {
            if (link.classList.contains('current')) {
                link.setAttribute('aria-current', 'page');
                link.setAttribute('aria-label', `Current page, page ${link.textContent}`);
            } else {
                link.setAttribute('aria-label', `Go to page ${link.textContent}`);
            }
        });
    }
    
    /**
     * Performance optimizations
     */
    function initPerformanceOptimizations() {
        // Prefetch next page on hover
        const paginationLinks = document.querySelectorAll('.page-number:not(.current)');
        paginationLinks.forEach(link => {
            link.addEventListener('mouseenter', function() {
                const linkElement = document.createElement('link');
                linkElement.rel = 'prefetch';
                linkElement.href = this.href;
                document.head.appendChild(linkElement);
            }, { once: true });
        });
        
        // Optimize post thumbnails loading
        const postThumbnails = document.querySelectorAll('.post-thumbnail img, .news-thumbnail img');
        postThumbnails.forEach(img => {
            img.addEventListener('load', function() {
                this.style.opacity = '1';
            });
            
            img.addEventListener('error', function() {
                this.style.display = 'none';
            });
        });
    }
    
    /**
     * Bio expand/collapse functionality
     */
    window.toggleBio = function(event) {
        event.preventDefault();
        
        const aboutContent = event.target.closest('.about-content');
        const excerpt = aboutContent.querySelector('p:first-child');
        const fullBio = aboutContent.querySelector('.full-bio');
        
        if (fullBio.style.display === 'none' || !fullBio.style.display) {
            // Show full bio
            excerpt.style.display = 'none';
            fullBio.style.display = 'block';
            fullBio.classList.add('fade-in');
        } else {
            // Show excerpt
            excerpt.style.display = 'block';
            fullBio.style.display = 'none';
            excerpt.classList.add('fade-in');
        }
    };
    
    /**
     * Smooth scrolling for anchor links
     */
    document.addEventListener('click', function(e) {
        if (e.target.tagName === 'A' && e.target.getAttribute('href').startsWith('#')) {
            e.preventDefault();
            const target = document.querySelector(e.target.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        }
    });
    
    /**
     * Progressive image enhancement
     */
    function enhanceImages() {
        const images = document.querySelectorAll('img');
        images.forEach(img => {
            // Add loading placeholder
            img.style.backgroundColor = '#f0f0f0';
            
            // Add error handling
            img.addEventListener('error', function() {
                this.alt = 'Image unavailable';
                this.style.backgroundColor = '#e0e0e0';
                this.style.color = '#666';
                this.style.textAlign = 'center';
                this.style.display = 'flex';
                this.style.alignItems = 'center';
                this.style.justifyContent = 'center';
            });
        });
    }
    
    // Call image enhancement
    enhanceImages();
    
    /**
     * Handle viewport changes for responsive behavior
     */
    function handleViewportChanges() {
        let resizeTimer;
        window.addEventListener('resize', function() {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(function() {
                // Recalculate layout-dependent elements
                const sidebarWidgets = document.querySelectorAll('.sidebar-widget');
                sidebarWidgets.forEach(widget => {
                    widget.style.minHeight = 'auto';
                });
            }, 250);
        });
    }
    
    handleViewportChanges();
    
    /**
     * Preload critical resources
     */
    function preloadCriticalResources() {
        // Preload next page images on author pages
        if (document.body.classList.contains('author-page')) {
            const nextPageLink = document.querySelector('.page-number[aria-label*="Go to page"]:last-of-type');
            if (nextPageLink) {
                const link = document.createElement('link');
                link.rel = 'dns-prefetch';
                link.href = nextPageLink.href;
                document.head.appendChild(link);
            }
        }
    }
    
    preloadCriticalResources();
    
    /**
     * Handle focus management for keyboard navigation
     */
    function initFocusManagement() {
        let isTabbing = false;
        
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Tab') {
                isTabbing = true;
            }
        });
        
        document.addEventListener('mousedown', function() {
            isTabbing = false;
        });
        
        document.addEventListener('focusin', function(e) {
            if (isTabbing) {
                e.target.classList.add('keyboard-focus');
            }
        });
        
        document.addEventListener('focusout', function(e) {
            e.target.classList.remove('keyboard-focus');
        });
    }
    
    initFocusManagement();
    
    /**
     * Social sharing enhancement (if needed)
     */
    function initSocialSharing() {
        const socialLinks = document.querySelectorAll('.social-link');
        socialLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                // Add analytics tracking if needed
                if (typeof gtag !== 'undefined') {
                    gtag('event', 'social_click', {
                        'social_network': this.className.split(' ').find(cls => cls !== 'social-link'),
                        'page_location': window.location.href
                    });
                }
            });
        });
    }
    
    initSocialSharing();
    
})();

/**
 * Core Web Vitals optimization
 */
// Optimize Largest Contentful Paint (LCP)
function optimizeLCP() {
    // Preload hero image if exists
    const heroImage = document.querySelector('.author-avatar img');
    if (heroImage && heroImage.src) {
        const preloadLink = document.createElement('link');
        preloadLink.rel = 'preload';
        preloadLink.as = 'image';
        preloadLink.href = heroImage.src;
        document.head.appendChild(preloadLink);
    }
}

// Optimize First Input Delay (FID)
function optimizeFID() {
    // Defer non-critical JavaScript
    if ('requestIdleCallback' in window) {
        requestIdleCallback(function() {
            // Initialize non-critical features
            initNonCriticalFeatures();
        });
    } else {
        setTimeout(initNonCriticalFeatures, 1);
    }
}

function initNonCriticalFeatures() {
    // Initialize features that aren't immediately needed
    console.log('Non-critical features initialized');
}

// Optimize Cumulative Layout Shift (CLS)
function optimizeCLS() {
    // Set dimensions for images to prevent layout shift
    const images = document.querySelectorAll('img:not([width]):not([height])');
    images.forEach(img => {
        img.addEventListener('load', function() {
            // Prevent layout shift by setting dimensions
            if (!this.width || !this.height) {
                const rect = this.getBoundingClientRect();
                this.setAttribute('width', rect.width);
                this.setAttribute('height', rect.height);
            }
        });
    });
}

// Initialize optimizations
document.addEventListener('DOMContentLoaded', function() {
    optimizeLCP();
    optimizeFID();
    optimizeCLS();
});
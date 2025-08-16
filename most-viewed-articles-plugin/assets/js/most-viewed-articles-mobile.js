(function($) {
    'use strict';
    
    var MostViewedArticlesMobile = {
        
        // Lightweight cache for mobile
        cache: {},
        cacheExpiry: {},
        isLoading: false,
        touchStartTime: 0,
        
        /**
         * Initialize mobile-optimized widget
         */
        init: function() {
            this.bindMobileEvents();
            this.initMobileOptimizations();
            this.preloadCriticalData();
            
            console.log('MostViewedArticles Mobile initialized');
        },
        
        /**
         * Bind mobile-specific events
         */
        bindMobileEvents: function() {
            var self = this;
            
            // Use touch events for better mobile performance
            $(document).on('touchstart', '.mva-tab', function(e) {
                self.touchStartTime = Date.now();
                $(this).addClass('mva-tab-pressed');
            });
            
            $(document).on('touchend', '.mva-tab', function(e) {
                var $tab = $(this);
                $tab.removeClass('mva-tab-pressed');
                
                // Only trigger if touch was quick (not scroll)
                if (Date.now() - self.touchStartTime < 200) {
                    self.handleTabSwitch.call(self, e);
                }
            });
            
            // Fallback for non-touch devices
            $(document).on('click', '.mva-tab', function(e) {
                if (!('ontouchstart' in window)) {
                    self.handleTabSwitch.call(self, e);
                }
            });
            
            // Mobile-specific optimizations
            this.initIntersectionObserver();
            this.initMobilePreloading();
        },
        
        /**
         * Handle tab switching with mobile optimizations
         */
        handleTabSwitch: function(e) {
            e.preventDefault();
            
            // Prevent multiple rapid taps
            if (this.isLoading) {
                return;
            }
            
            var $tab = $(e.target);
            var $widget = $tab.closest('.mva-widget');
            var timeframe = $tab.data('timeframe');
            var $content = $widget.find('.mva-articles[data-timeframe="' + timeframe + '"]');
            
            // Quick visual feedback
            this.updateActiveTab($widget, $tab);
            this.showContent($widget, timeframe);
            
            // Performance timing
            var startTime = performance.now();
            
            // Check cache first
            if (this.isCached(timeframe)) {
                var cachedData = this.getFromCache(timeframe);
                this.renderArticlesMobile($content, cachedData);
                
                var endTime = performance.now();
                console.log('Mobile tab switch: ' + Math.round(endTime - startTime) + 'ms (cached)');
                return;
            }
            
            // Load data with mobile optimizations
            this.loadArticlesMobile($widget, timeframe, startTime);
        },
        
        /**
         * Update active tab state
         */
        updateActiveTab: function($widget, $activeTab) {
            $widget.find('.mva-tab')
                   .removeClass('active')
                   .attr('aria-selected', 'false');
            
            $activeTab.addClass('active')
                     .attr('aria-selected', 'true');
        },
        
        /**
         * Show/hide content with mobile-friendly animation
         */
        showContent: function($widget, timeframe) {
            $widget.find('.mva-articles').hide();
            $widget.find('.mva-articles[data-timeframe="' + timeframe + '"]').show();
        },
        
        /**
         * Load articles with mobile optimizations
         */
        loadArticlesMobile: function($widget, timeframe, startTime) {
            var self = this;
            var $content = $widget.find('.mva-articles[data-timeframe="' + timeframe + '"]');
            
            this.isLoading = true;
            $content.html(this.getMobileLoadingHTML());
            
            // Mobile-optimized AJAX request
            $.ajax({
                url: mva_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'get_most_viewed',
                    timeframe: timeframe,
                    nonce: mva_ajax.nonce,
                    minimal: true,
                    mobile: true
                },
                timeout: 8000, // Shorter timeout for mobile
                success: function(response) {
                    self.isLoading = false;
                    
                    if (response.success) {
                        // Cache the response
                        self.saveToCache(timeframe, response.data.articles);
                        
                        // Render with mobile optimizations
                        self.renderArticlesMobile($content, response.data.articles);
                        
                        // Performance logging
                        if (startTime) {
                            var totalTime = performance.now() - startTime;
                            console.log('Mobile tab switch: ' + Math.round(totalTime) + 'ms');
                            console.log('Server: ' + response.data.query_time_ms + 'ms');
                            console.log('Articles: ' + response.data.count);
                        }
                    } else {
                        $content.html('<p class="mva-error">Error loading articles.</p>');
                    }
                },
                error: function(xhr, status, error) {
                    self.isLoading = false;
                    console.error('Mobile AJAX Error:', error);
                    $content.html('<p class="mva-error">Network error. Tap to retry.</p>');
                    
                    // Add retry functionality
                    $content.find('.mva-error').on('click', function() {
                        self.loadArticlesMobile($widget, timeframe, performance.now());
                    });
                }
            });
        },
        
        /**
         * Render articles with mobile optimizations
         */
        renderArticlesMobile: function($container, articles) {
            if (!articles || articles.length === 0) {
                $container.html('<p class="mva-no-articles">No articles found.</p>');
                return;
            }
            
            // Build HTML efficiently for mobile
            var htmlParts = ['<ol class="mva-articles-list">'];
            
            for (var i = 0; i < articles.length; i++) {
                var article = articles[i];
                htmlParts.push(
                    '<li class="mva-article-item">',
                    '<span class="mva-rank" aria-label="Rank ' + (i + 1) + '">' + (i + 1) + '.</span>',
                    '<a href="' + this.escapeHtml(article.permalink) + '" class="mva-article-link">',
                    '<span class="mva-article-title">' + this.escapeHtml(article.title) + '</span>',
                    '<span class="mva-view-count">' + article.views + ' views</span>',
                    '</a>',
                    '</li>'
                );
            }
            
            htmlParts.push('</ol>');
            
            // Single DOM update for mobile performance
            $container.html(htmlParts.join(''));
            
            // Add mobile-specific enhancements
            this.enhanceMobileLinks($container);
        },
        
        /**
         * Enhance links for mobile interaction
         */
        enhanceMobileLinks: function($container) {
            $container.find('.mva-article-link').on('touchstart', function() {
                $(this).addClass('mva-link-pressed');
            }).on('touchend touchcancel', function() {
                var $link = $(this);
                setTimeout(function() {
                    $link.removeClass('mva-link-pressed');
                }, 150);
            });
        },
        
        /**
         * Mobile-optimized cache management
         */
        isCached: function(timeframe) {
            var now = Date.now();
            return this.cache[timeframe] && 
                   this.cacheExpiry[timeframe] && 
                   this.cacheExpiry[timeframe] > now;
        },
        
        getFromCache: function(timeframe) {
            return this.cache[timeframe];
        },
        
        saveToCache: function(timeframe, data) {
            this.cache[timeframe] = data;
            this.cacheExpiry[timeframe] = Date.now() + (mva_ajax.cache_duration * 1000);
            
            // Mobile: Use sessionStorage for persistence
            try {
                if (typeof Storage !== 'undefined') {
                    sessionStorage.setItem('mva_mobile_' + timeframe, JSON.stringify({
                        data: data,
                        expiry: this.cacheExpiry[timeframe]
                    }));
                }
            } catch (e) {
                // Storage quota exceeded - mobile has limited storage
                console.warn('Mobile storage limit reached');
            }
        },
        
        /**
         * Initialize mobile-specific optimizations
         */
        initMobileOptimizations: function() {
            // Restore from sessionStorage
            this.restoreMobileCache();
            
            // Mobile performance monitoring
            this.initMobilePerformanceMonitoring();
            
            // Handle mobile orientation changes
            this.handleOrientationChange();
        },
        
        /**
         * Restore cache from mobile storage
         */
        restoreMobileCache: function() {
            try {
                if (typeof Storage !== 'undefined') {
                    ['week', 'month'].forEach(function(timeframe) {
                        var stored = sessionStorage.getItem('mva_mobile_' + timeframe);
                        if (stored) {
                            var data = JSON.parse(stored);
                            if (data.expiry > Date.now()) {
                                this.cache[timeframe] = data.data;
                                this.cacheExpiry[timeframe] = data.expiry;
                            } else {
                                sessionStorage.removeItem('mva_mobile_' + timeframe);
                            }
                        }
                    }.bind(this));
                }
            } catch (e) {
                console.warn('Error restoring mobile cache:', e);
            }
        },
        
        /**
         * Handle mobile orientation changes
         */
        handleOrientationChange: function() {
            $(window).on('orientationchange', function() {
                // Invalidate layout cache on orientation change
                setTimeout(function() {
                    $('.mva-widget').trigger('mva:orientation-change');
                }, 500);
            });
        },
        
        /**
         * Initialize intersection observer for mobile performance
         */
        initIntersectionObserver: function() {
            if ('IntersectionObserver' in window) {
                var self = this;
                var observer = new IntersectionObserver(function(entries) {
                    entries.forEach(function(entry) {
                        if (entry.isIntersecting) {
                            var $widget = $(entry.target);
                            self.preloadWidgetData($widget);
                            observer.unobserve(entry.target);
                        }
                    });
                }, {
                    rootMargin: '50px 0px',
                    threshold: 0.1
                });
                
                // Observe all widgets
                $('.mva-widget').each(function() {
                    observer.observe(this);
                });
            }
        },
        
        /**
         * Preload widget data when visible
         */
        preloadWidgetData: function($widget) {
            var self = this;
            
            // Preload month data if week is active
            var activeTab = $widget.find('.mva-tab.active').data('timeframe');
            var preloadTimeframe = (activeTab === 'week') ? 'month' : 'week';
            
            if (!this.isCached(preloadTimeframe)) {
                // Silent preload for mobile
                setTimeout(function() {
                    self.loadArticlesMobile($widget, preloadTimeframe, null, true);
                }, 1000);
            }
        },
        
        /**
         * Initialize mobile preloading strategy
         */
        initMobilePreloading: function() {
            var self = this;
            
            // Preload on user interaction
            $(document).one('touchstart click scroll', function() {
                self.preloadCriticalData();
            });
        },
        
        /**
         * Preload critical data for mobile
         */
        preloadCriticalData: function() {
            var self = this;
            
            // Only preload if not already loaded
            if (!this.isCached('week')) {
                // Preload week data (most commonly accessed)
                var $firstWidget = $('.mva-widget').first();
                if ($firstWidget.length) {
                    this.loadArticlesMobile($firstWidget, 'week', null, true);
                }
            }
        },
        
        /**
         * Mobile performance monitoring
         */
        initMobilePerformanceMonitoring: function() {
            // Monitor mobile-specific metrics
            if (window.performance && window.performance.timing) {
                $(window).on('load', function() {
                    var timing = window.performance.timing;
                    var loadTime = timing.loadEventEnd - timing.navigationStart;
                    var domReady = timing.domContentLoadedEventEnd - timing.navigationStart;
                    
                    console.log('Mobile page load: ' + loadTime + 'ms');
                    console.log('Mobile DOM ready: ' + domReady + 'ms');
                    
                    // Track mobile-specific issues
                    if (loadTime > 5000) {
                        console.warn('Slow mobile load detected');
                    }
                });
            }
            
            // Monitor memory usage on mobile
            if (window.performance && window.performance.memory) {
                setInterval(function() {
                    var memory = window.performance.memory;
                    var usedMB = Math.round(memory.usedJSHeapSize / 1048576);
                    
                    if (usedMB > 50) { // 50MB threshold for mobile
                        console.warn('High memory usage on mobile: ' + usedMB + 'MB');
                    }
                }, 30000);
            }
        },
        
        /**
         * Get mobile-optimized loading HTML
         */
        getMobileLoadingHTML: function() {
            return '<div class="mva-loading mva-mobile-loading">' +
                   '<div class="mva-spinner-mobile"></div>' +
                   '<p>Loading...</p>' +
                   '</div>';
        },
        
        /**
         * Escape HTML for mobile security
         */
        escapeHtml: function(text) {
            if (!text) return '';
            
            var map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            
            return String(text).replace(/[&<>"']/g, function(m) { 
                return map[m]; 
            });
        },
        
        /**
         * Clear mobile cache
         */
        clearMobileCache: function() {
            this.cache = {};
            this.cacheExpiry = {};
            
            try {
                if (typeof Storage !== 'undefined') {
                    ['week', 'month'].forEach(function(timeframe) {
                        sessionStorage.removeItem('mva_mobile_' + timeframe);
                    });
                }
            } catch (e) {
                console.warn('Error clearing mobile cache:', e);
            }
            
            console.log('Mobile cache cleared');
        },
        
        /**
         * Mobile network detection
         */
        detectMobileNetwork: function() {
            if ('connection' in navigator) {
                var connection = navigator.connection;
                var effectiveType = connection.effectiveType;
                
                // Adjust behavior based on connection
                if (effectiveType === 'slow-2g' || effectiveType === '2g') {
                    // Ultra-lightweight mode
                    this.enableUltraLightMode();
                } else if (effectiveType === '3g') {
                    // Conservative loading
                    this.enableConservativeMode();
                }
                
                console.log('Mobile connection: ' + effectiveType);
            }
        },
        
        /**
         * Enable ultra-light mode for slow connections
         */
        enableUltraLightMode: function() {
            // Disable animations
            $('<style>').prop('type', 'text/css').html('* { transition: none !important; animation: none !important; }').appendTo('head');
            
            // Reduce cache duration
            mva_ajax.cache_duration = 60; // 1 minute instead of 5
            
            console.log('Ultra-light mode enabled');
        },
        
        /**
         * Enable conservative mode for moderate connections
         */
        enableConservativeMode: function() {
            // Reduce preloading
            this.preloadCriticalData = function() {
                // Minimal preloading
            };
            
            console.log('Conservative mode enabled');
        }
    };
    
    // Initialize when DOM is ready
    $(document).ready(function() {
        MostViewedArticlesMobile.init();
        MostViewedArticlesMobile.detectMobileNetwork();
    });
    
    // Make available globally for debugging
    window.MostViewedArticlesMobile = MostViewedArticlesMobile;
    
})(jQuery);
/**
 * Most Viewed Articles Widget JavaScript (Performance Optimized)
 */
(function($) {
    'use strict';
    
    var MostViewedArticles = {
        
        // Performance cache
        cache: {},
        cacheExpiry: {},
        
        /**
         * Initialize the widget with performance monitoring
         */
        init: function() {
            this.bindEvents();
            this.initPerformanceMonitoring();
            console.log('MostViewedArticles initialized with caching enabled');
        },
        
        /**
         * Bind events with performance optimization
         */
        bindEvents: function() {
            $(document).on('click', '.mva-tab', this.handleTabClick.bind(this));
            
            // Preload month data on hover for better UX
            $(document).on('mouseenter', '.mva-tab[data-timeframe="month"]', this.preloadMonthData.bind(this));
        },
        
        /**
         * Handle tab click with caching
         */
        handleTabClick: function(e) {
            e.preventDefault();
            
            var $tab = $(e.target);
            var $widget = $tab.closest('.mva-widget');
            var timeframe = $tab.data('timeframe');
            var $content = $widget.find('.mva-articles[data-timeframe="' + timeframe + '"]');
            
            // Performance timing
            var startTime = performance.now();
            
            // Update active tab
            $widget.find('.mva-tab').removeClass('active').attr('aria-pressed', 'false');
            $tab.addClass('active').attr('aria-pressed', 'true');
            
            // Show/hide content with animation
            $widget.find('.mva-articles').hide();
            $content.show();
            
            // Check cache first (Client-side Layer 1)
            if (this.isCached(timeframe)) {
                var cachedData = this.getFromCache(timeframe);
                this.renderArticles($content, cachedData);
                
                var endTime = performance.now();
                console.log('Tab switch completed in ' + Math.round(endTime - startTime) + 'ms (cached)');
                return;
            }
            
            // Load data if not cached
            this.loadArticles($widget, timeframe, startTime);
        },
        
        /**
         * Preload month data on hover for instant UX
         */
        preloadMonthData: function(e) {
            var timeframe = 'month';
            
            // Only preload if not already cached
            if (!this.isCached(timeframe)) {
                var $widget = $(e.target).closest('.mva-widget');
                this.loadArticles($widget, timeframe, null, true); // Silent preload
            }
        },
        
        /**
         * Load articles via AJAX with performance optimization
         */
        loadArticles: function($widget, timeframe, startTime, silent) {
            var self = this;
            var $content = $widget.find('.mva-articles[data-timeframe="' + timeframe + '"]');
            
            // Show loading only if not silent
            if (!silent) {
                $content.html('<div class="mva-loading">' + this.getLoadingHTML() + '</div>');
            }
            
            // AJAX with performance monitoring
            $.ajax({
                url: mva_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'get_most_viewed',
                    timeframe: timeframe,
                    nonce: mva_ajax.nonce
                },
                success: function(response) {
                    if (response.success) {
                        // Cache the response
                        self.saveToCache(timeframe, response.data.articles);
                        
                        // Render if not silent
                        if (!silent) {
                            self.renderArticles($content, response.data.articles);
                        }
                        
                        // Performance logging
                        if (startTime) {
                            var totalTime = performance.now() - startTime;
                            console.log('Tab switch completed in ' + Math.round(totalTime) + 'ms');
                            console.log('Server query: ' + response.data.query_time_ms + 'ms');
                            console.log('Was server cached: ' + response.data.cached);
                            console.log('Articles loaded: ' + response.data.count);
                        }
                    } else {
                        if (!silent) {
                            $content.html('<p class="mva-error">Error loading articles.</p>');
                        }
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', error);
                    if (!silent) {
                        $content.html('<p class="mva-error">Error loading articles: ' + error + '</p>');
                    }
                }
            });
        },
        
        /**
         * Render articles with performance optimization
         */
        renderArticles: function($container, articles) {
            if (!articles || articles.length === 0) {
                $container.html('<p class="mva-no-articles">No articles found.</p>');
                return;
            }
            
            // Build HTML efficiently
            var htmlParts = ['<ol class="mva-articles-list">'];
            
            for (var i = 0; i < articles.length; i++) {
                var article = articles[i];
                htmlParts.push(
                    '<li class="mva-article-item">',
                    '<span class="mva-rank">' + (i + 1) + '.</span>',
                    '<a href="' + this.escapeHtml(article.permalink) + '" class="mva-article-link">',
                    '<span class="mva-article-title">' + this.escapeHtml(article.title) + '</span>',
                    '<span class="mva-view-count">(' + article.views + ' views)</span>',
                    '</a>',
                    '</li>'
                );
            }
            
            htmlParts.push('</ol>');
            
            // Single DOM update for performance
            $container.html(htmlParts.join(''));
        },
        
        /**
         * Cache management
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
        },
        
        /**
         * Performance monitoring
         */
        initPerformanceMonitoring: function() {
            // Monitor page load performance
            if (window.performance && window.performance.timing) {
                $(window).on('load', function() {
                    var loadTime = window.performance.timing.loadEventEnd - window.performance.timing.navigationStart;
                    console.log('Page load time: ' + loadTime + 'ms');
                });
            }
        },
        
        /**
         * Get loading HTML with spinner
         */
        getLoadingHTML: function() {
            return '<div class="mva-spinner" aria-label="Loading"></div><p>Loading articles...</p>';
        },
        
        /**
         * Escape HTML for security
         */
        escapeHtml: function(text) {
            var map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return text.replace(/[&<>"']/g, function(m) { return map[m]; });
        }
    };
    
    // Initialize when document is ready
    $(document).ready(function() {
        MostViewedArticles.init();
    });
    
    // Make available globally for debugging
    window.MostViewedArticles = MostViewedArticles;
    
})(jQuery);
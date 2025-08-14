/**
 * Most Viewed Articles Widget JavaScript
 */
(function($) {
    'use strict';
    
    var MostViewedArticles = {
        
        /**
         * Initialize the widget
         */
        init: function() {
            this.bindEvents();
        },
        
        /**
         * Bind events
         */
        bindEvents: function() {
            $(document).on('click', '.mva-tab', this.handleTabClick);
        },
        
        /**
         * Handle tab click
         */
        handleTabClick: function(e) {
            e.preventDefault();
            
            var $tab = $(this);
            var $widget = $tab.closest('.mva-widget');
            var timeframe = $tab.data('timeframe');
            var $content = $widget.find('.mva-articles[data-timeframe="' + timeframe + '"]');
            
            // Update active tab
            $widget.find('.mva-tab').removeClass('active');
            $tab.addClass('active');
            
            // Show/hide content
            $widget.find('.mva-articles').hide();
            $content.show();
            
            // Load content if empty
            if ($content.is(':empty') || $content.find('.mva-loading').length > 0) {
                MostViewedArticles.loadArticles($widget, timeframe);
            }
        },
        
        /**
         * Load articles via AJAX
         */
        loadArticles: function($widget, timeframe) {
            var $content = $widget.find('.mva-articles[data-timeframe="' + timeframe + '"]');
            
            // Show loading
            $content.html('<div class="mva-loading">' + this.getLoadingHTML() + '</div>');
            
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
                        MostViewedArticles.renderArticles($content, response.data);
                    } else {
                        $content.html('<p class="mva-error">Error loading articles.</p>');
                    }
                },
                error: function() {
                    $content.html('<p class="mva-error">Error loading articles.</p>');
                }
            });
        },
        
        /**
         * Render articles
         */
        renderArticles: function($container, articles) {
            if (!articles || articles.length === 0) {
                $container.html('<p class="mva-no-articles">No articles found.</p>');
                return;
            }
            
            var html = '<ol class="mva-articles-list">';
            
            $.each(articles, function(index, article) {
                html += '<li class="mva-article-item">';
                html += '<span class="mva-rank">' + (index + 1) + '.</span>';
                html += '<a href="' + article.permalink + '" class="mva-article-link">';
                html += '<span class="mva-article-title">' + article.title + '</span>';
                html += '<span class="mva-view-count">(' + article.views + ' views)</span>';
                html += '</a>';
                html += '</li>';
            });
            
            html += '</ol>';
            
            $container.html(html);
        },
        
        /**
         * Get loading HTML
         */
        getLoadingHTML: function() {
            return '<div class="mva-spinner"></div><p>Loading articles...</p>';
        }
    };
    
    // Initialize when document is ready
    $(document).ready(function() {
        MostViewedArticles.init();
    });
    
})(jQuery);
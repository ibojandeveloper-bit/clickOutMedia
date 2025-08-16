<?php
/**
 * Plugin Name: Most Viewed Articles (Optimized)
 * Description: A high-performance widget that displays the most viewed articles with tabbed interface for "This Week" and "This Month".
 * Version: 1.1.0
 * Author: Bojan Ilievski
 * Text Domain: most-viewed-articles
 * Domain Path: /languages
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Main plugin class - Optimized for performance
 */
class Most_Viewed_Articles {
    
    /**
     * Plugin version
     */
    const VERSION = '1.1.0';
    
    /**
     * Cache expiration time
     */
    const CACHE_EXPIRATION = 300; // 5 minutes
    
    /**
     * Initialize the plugin
     */
    public function __construct() {
        add_action('init', array($this, 'init'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('wp_ajax_get_most_viewed', array($this, 'ajax_get_most_viewed'));
        add_action('wp_ajax_nopriv_get_most_viewed', array($this, 'ajax_get_most_viewed'));
        add_action('wp', array($this, 'track_post_view'));
        add_action('widgets_init', array($this, 'register_widget'));
        
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
    }
    
    /**
     * Initialize plugin
     */
    public function init() {
        load_plugin_textdomain('most-viewed-articles', false, dirname(plugin_basename(__FILE__)) . '/languages');
    }
    
    /**
     * Enqueue scripts and styles with optimization
     */
public function enqueue_scripts() {
    // Only load on pages with the widget
    if (!is_active_widget(false, false, 'most_viewed_articles_widget')) {
        return;
    }
    
    // Mobile-specific optimizations
    if (wp_is_mobile()) {
        // Load mobile-optimized JavaScript
        wp_enqueue_script(
            'most-viewed-articles-mobile',
            plugins_url('assets/js/most-viewed-articles-mobile.js', __FILE__),
            array('jquery'),
            self::VERSION,
            true
        );
        
        // Mobile-optimized CSS
        wp_enqueue_style(
            'most-viewed-articles-mobile',
            plugins_url('assets/css/most-viewed-articles-mobile.css', __FILE__),
            array(),
            self::VERSION
        );
    } else {
        // Desktop version
        wp_enqueue_script(
            'most-viewed-articles',
            plugins_url('assets/js/most-viewed-articles.js', __FILE__),
            array('jquery'),
            self::VERSION,
            true
        );
        
        wp_enqueue_style(
            'most-viewed-articles',
            plugins_url('assets/css/most-viewed-articles.css', __FILE__),
            array(),
            self::VERSION
        );
    }
    
    // Localize script
    wp_localize_script(
        wp_is_mobile() ? 'most-viewed-articles-mobile' : 'most-viewed-articles',
        'mva_ajax',
        array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('mva_nonce'),
            'cache_enabled' => true,
            'cache_duration' => self::CACHE_EXPIRATION,
            'is_mobile' => wp_is_mobile()
        )
    );
}
    
    /**
     * Track post views with performance optimization
     */
    public function track_post_view() {
        if (!is_single() || !is_main_query()) {
            return;
        }
        
        global $post;
              if ($post->post_type !== 'post') {
            return;
        }  

        
        $post_id = $post->ID;
        $user_ip = $this->get_user_ip();
        $current_time = current_time('timestamp');
        
        // Check if this IP has viewed this post in the last hour (performance optimization)
        $last_view = get_transient("mva_view_{$post_id}_{$user_ip}");
        
        if ($last_view) {
            return; // Don't count duplicate views
        }
        
        // Set transient for 1 hour to prevent duplicate views
        set_transient("mva_view_{$post_id}_{$user_ip}", $current_time, HOUR_IN_SECONDS);
        
        // Update view count with atomic operation
        $view_count = get_post_meta($post_id, '_mva_view_count', true);
        $view_count = $view_count ? intval($view_count) + 1 : 1;
        update_post_meta($post_id, '_mva_view_count', $view_count);
        
        // Store view with timestamp for time-based queries (optimized)
        $views_data = get_post_meta($post_id, '_mva_views_data', true);
        if (!$views_data) {
            $views_data = array();
        }
        
        $views_data[] = array(
            'timestamp' => $current_time,
            'ip' => hash('sha256', $user_ip) // Hash IP for privacy
        );
        
        // Keep only last 30 days of data (performance optimization)
        $thirty_days_ago = $current_time - (30 * DAY_IN_SECONDS);
        $views_data = array_filter($views_data, function($view) use ($thirty_days_ago) {
            return isset($view['timestamp']) && $view['timestamp'] >= $thirty_days_ago;
        });
        
        update_post_meta($post_id, '_mva_views_data', $views_data);
        
        // Clear cache when views are updated
        $this->clear_article_cache();
    }
    
    /**
     * Get user IP address with proxy support
     */
    private function get_user_ip() {
        $ip_keys = array(
            'HTTP_CF_CONNECTING_IP',     // CloudFlare
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR'
        );
        
        foreach ($ip_keys as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    $ip = trim($ip);
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                        return $ip;
                    }
                }
            }
        }
        
        return isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '0.0.0.0';
    }
    
/**
 * AJAX handler for getting most viewed articles (Performance Optimized)
 */
public function ajax_get_most_viewed() {
    if (!check_ajax_referer('mva_nonce', 'nonce', false)) {
        wp_send_json_error('Invalid nonce');
        return;
    }
    
    $timeframe = isset($_POST['timeframe']) ? sanitize_text_field($_POST['timeframe']) : 'week';
    $minimal = isset($_POST['minimal']) && $_POST['minimal'] === '1';
    
    $cache_key = "mva_articles_{$timeframe}_10";
    $was_cached = get_transient($cache_key) !== false;
    
    $start_time = microtime(true);
    $articles = $this->get_most_viewed_articles($timeframe);
    $query_time = round((microtime(true) - $start_time) * 1000, 2);
    
    $response = array(
        'articles' => $articles,
        'query_time_ms' => $query_time,
        'cached' => $was_cached,
        'count' => count($articles)
    );
    
    // Add minimal response option to reduce payload size
    if (!$minimal) {
        $response['timeframe'] = $timeframe;
        $response['timestamp'] = current_time('timestamp');
    }
    
    // Add compression headers
    if (!headers_sent()) {
        header('Content-Type: application/json; charset=utf-8');
        if (function_exists('gzencode') && strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') !== false) {
            header('Content-Encoding: gzip');
            $output = gzencode(json_encode(array('success' => true, 'data' => $response)));
            echo $output;
            exit;
        }
    }
    
    wp_send_json_success($response);
}

/**
 * Get most viewed articles for a timeframe (Highly Optimized)
 */
public function get_most_viewed_articles($timeframe = 'week', $limit = 10) {
    // Check cache first (Performance Layer 1) - Extended cache time for month
    $cache_duration = ($timeframe === 'month') ? (self::CACHE_EXPIRATION * 4) : self::CACHE_EXPIRATION;
    $cache_key = "mva_articles_{$timeframe}_{$limit}";
    $cached_result = get_transient($cache_key);
    
    if ($cached_result !== false) {
        return $cached_result;
    }
    
    global $wpdb;
    
    $current_time = current_time('timestamp');
    
    // Calculate time ranges
    $time_start = ($timeframe === 'week') 
        ? $current_time - (7 * DAY_IN_SECONDS)
        : $current_time - (30 * DAY_IN_SECONDS);
    
    // MAJOR OPTIMIZATION: Use view count meta for initial filtering
    $query = $wpdb->prepare("
        SELECT p.ID, p.post_title, 
               CAST(pm_count.meta_value AS UNSIGNED) as total_views,
               pm_data.meta_value as views_data
        FROM {$wpdb->posts} p
        INNER JOIN {$wpdb->postmeta} pm_count ON (p.ID = pm_count.post_id AND pm_count.meta_key = '_mva_view_count')
        INNER JOIN {$wpdb->postmeta} pm_data ON (p.ID = pm_data.post_id AND pm_data.meta_key = '_mva_views_data')
        WHERE p.post_type = 'post'
        AND p.post_status = 'publish'
        AND CAST(pm_count.meta_value AS UNSIGNED) > 0
        ORDER BY CAST(pm_count.meta_value AS UNSIGNED) DESC
        LIMIT %d
    ", $limit * 2); // Reduced multiplier since we're pre-filtering
    
    $posts = $wpdb->get_results($query);
    
    if (empty($posts)) {
        // Cache empty result briefly to avoid repeated queries
        set_transient($cache_key, array(), 300);
        return array();
    }
    
    $articles = array();
    
    foreach ($posts as $post) {
        $views_data = maybe_unserialize($post->views_data);
        if (!is_array($views_data)) {
            continue;
        }
        
        $view_count = 0;
        
        // Optimized counting with early break for better performance
        foreach ($views_data as $view) {
            if (!isset($view['timestamp'])) continue;
            
            if ($view['timestamp'] >= $time_start) {
                $view_count++;
            }
            // Skip older entries since array should be somewhat chronological
            elseif ($view['timestamp'] < ($time_start - DAY_IN_SECONDS)) {
                break;
            }
        }
        
        if ($view_count > 0) {
            $articles[] = array(
                'id' => (int)$post->ID,
                'title' => $post->post_title,
                'permalink' => get_permalink($post->ID),
                'views' => $view_count
            );
        }
        
        // Early exit when we have enough articles
        if (count($articles) >= $limit) {
            break;
        }
    }
    
    // Sort by timeframe-specific view count (not total views)
    if (count($articles) > 1) {
        usort($articles, function($a, $b) {
            return $b['views'] - $a['views'];
        });
    }
    
    $final_articles = array_slice($articles, 0, $limit);
    
    // Cache with different durations based on timeframe
    set_transient($cache_key, $final_articles, $cache_duration);
    
    return $final_articles;
}

    
    /**
     * Clear article cache
     */
    private function clear_article_cache() {
        delete_transient('mva_articles_week_10');
        delete_transient('mva_articles_month_10');
    }
    
    /**
     * Register the widget
     */
    public function register_widget() {
        register_widget('Most_Viewed_Articles_Widget');
    }
    
    /**
     * Plugin activation
     */
    public function activate() {
    global $wpdb;
    
    // Composite indexes for better performance
    $wpdb->query("
        CREATE INDEX IF NOT EXISTS idx_mva_post_meta_composite 
        ON {$wpdb->postmeta} (meta_key, post_id, meta_value(50))
    ");
    
    $wpdb->query("
        CREATE INDEX IF NOT EXISTS idx_posts_type_status_date 
        ON {$wpdb->posts} (post_type, post_status, post_date DESC)
    ");
    
    // Analyze tables for optimization
    $wpdb->query("ANALYZE TABLE {$wpdb->posts}, {$wpdb->postmeta}");
    }
    
    /**
     * Plugin deactivation
     */
    public function deactivate() {
        // Clear all caches
        $this->clear_article_cache();
        flush_rewrite_rules();
    }
}

/**
 * Most Viewed Articles Widget Class (Optimized)
 */
class Most_Viewed_Articles_Widget extends WP_Widget {
    
    public function __construct() {
        parent::__construct(
            'most_viewed_articles_widget',
            __('Most Viewed Articles', 'most-viewed-articles'),
            array(
                'description' => __('Display most viewed articles with high-performance tabbed interface', 'most-viewed-articles'),
                'classname' => 'most-viewed-articles-widget'
            )
        );
    }
    
    /**
     * Widget frontend display (Optimized)
     */
    public function widget($args, $instance) {
        echo $args['before_widget'];
        
        $title = !empty($instance['title']) ? $instance['title'] : __('Most Viewed Articles', 'most-viewed-articles');
        echo $args['before_title'] . apply_filters('widget_title', $title) . $args['after_title'];
        
        $plugin = new Most_Viewed_Articles();
        $week_articles = $plugin->get_most_viewed_articles('week');
        ?>
        
        <div class="mva-widget" data-widget-id="<?php echo esc_attr($this->id); ?>" data-cache-enabled="true">
            <div class="mva-tabs">
                <button class="mva-tab active" data-timeframe="week" aria-label="<?php esc_attr_e('Show this week articles', 'most-viewed-articles'); ?>">
                    <?php _e('This Week', 'most-viewed-articles'); ?>
                </button>
                <button class="mva-tab" data-timeframe="month" aria-label="<?php esc_attr_e('Show this month articles', 'most-viewed-articles'); ?>">
                    <?php _e('This Month', 'most-viewed-articles'); ?>
                </button>
            </div>
            
            <div class="mva-content">
                <div class="mva-articles" data-timeframe="week" style="display: block;">
                    <?php $this->render_articles($week_articles); ?>
                </div>
                <div class="mva-articles" data-timeframe="month" style="display: none;">
                </div>
            </div>
        </div>
        
        <?php
        echo $args['after_widget'];
    }
    
    /**
     * Render articles with performance optimization
     */
    private function render_articles($articles) {
        if (empty($articles)) {
            echo '<p class="mva-no-articles">' . __('No articles found.', 'most-viewed-articles') . '</p>';
            return;
        }
        
        echo '<ol class="mva-articles-list">';
        foreach ($articles as $index => $article) {
            echo '<li class="mva-article-item">';
            echo '<span class="mva-rank">' . esc_html($index + 1) . '.</span>';
            echo '<a href="' . esc_url($article['permalink']) . '" class="mva-article-link">';
            echo '<span class="mva-article-title">' . esc_html($article['title']) . '</span>';
            echo '<span class="mva-view-count">(' . esc_html($article['views']) . ' views)</span>';
            echo '</a>';
            echo '</li>';
        }
        echo '</ol>';
    }
    
    /**
     * Widget backend form
     */
    public function form($instance) {
        $title = !empty($instance['title']) ? $instance['title'] : __('Most Viewed Articles', 'most-viewed-articles');
        ?>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('title')); ?>">
                <?php _e('Title:', 'most-viewed-articles'); ?>
            </label>
            <input class="widefat" id="<?php echo esc_attr($this->get_field_id('title')); ?>" 
                   name="<?php echo esc_attr($this->get_field_name('title')); ?>" type="text" 
                   value="<?php echo esc_attr($title); ?>">
        </p>
        <p class="description">
            <?php _e('High-performance widget with 5-minute caching for optimal speed.', 'most-viewed-articles'); ?>
        </p>
        <?php
    }
    
    /**
     * Update widget settings
     */
    public function update($new_instance, $old_instance) {
        $instance = array();
        $instance['title'] = (!empty($new_instance['title'])) ? sanitize_text_field($new_instance['title']) : '';
        
        // Clear cache when widget is updated
        delete_transient('mva_articles_week_10');
        delete_transient('mva_articles_month_10');
        
        return $instance;
    }
}

// Initialize the plugin
new Most_Viewed_Articles();
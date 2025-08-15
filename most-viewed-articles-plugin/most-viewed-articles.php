<?php
/**
 * Plugin Name: Most Viewed Articles (Optimized)
 * Description: A high-performance widget that displays the most viewed articles with tabbed interface for "This Week" and "This Month".
 * Version: 1.1.0
 * Author: Your Name
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
        // Enqueue optimized JavaScript
        wp_enqueue_script(
            'most-viewed-articles',
            plugins_url('assets/js/most-viewed-articles.js', __FILE__),
            array('jquery'),
            self::VERSION,
            true
        );
        
        // Enqueue optimized CSS
        wp_enqueue_style(
            'most-viewed-articles',
            plugins_url('assets/css/most-viewed-articles.css', __FILE__),
            array(),
            self::VERSION
        );
        
        // Localize script with performance settings
        wp_localize_script('most-viewed-articles', 'mva_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('mva_nonce'),
            'cache_enabled' => true,
            'cache_duration' => self::CACHE_EXPIRATION
        ));
    }
    
    /**
     * Track post views with performance optimization
     */
    public function track_post_view() {
        if (!is_single() || !is_main_query()) {
            return;
        }
        
        global $post;
        
        if (!$post || $post->post_type !== 'post') {
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
        // Add performance headers
        header('Cache-Control: public, max-age=' . self::CACHE_EXPIRATION);
        header('Content-Type: application/json; charset=utf-8');
        
        check_ajax_referer('mva_nonce', 'nonce');
        
        $timeframe = sanitize_text_field($_POST['timeframe']);
        
        // Time the query for performance monitoring
        $start_time = microtime(true);
        $articles = $this->get_most_viewed_articles($timeframe);
        $query_time = round((microtime(true) - $start_time) * 1000, 2);
        
        // Check if result was cached
        $cache_key = "mva_articles_{$timeframe}_10";
        $was_cached = get_transient($cache_key) !== false;
        
        wp_send_json_success(array(
            'articles' => $articles,
            'query_time_ms' => $query_time,
            'cached' => $was_cached,
            'timeframe' => $timeframe,
            'count' => count($articles)
        ));
    }
    
    /**
     * Get most viewed articles for a timeframe (Highly Optimized)
     */
    public function get_most_viewed_articles($timeframe = 'week', $limit = 10) {
        // Check cache first (Performance Layer 1)
        $cache_key = "mva_articles_{$timeframe}_{$limit}";
        $cached_result = get_transient($cache_key);
        
        if ($cached_result !== false) {
            return $cached_result;
        }
        
        global $wpdb;
        
        $current_time = current_time('timestamp');
        
        // Calculate time ranges
        if ($timeframe === 'week') {
            $time_start = $current_time - (7 * DAY_IN_SECONDS);
        } else {
            $time_start = $current_time - (30 * DAY_IN_SECONDS);
        }
        
        // Highly optimized query - only get posts that definitely have views
        $query = $wpdb->prepare("
            SELECT p.ID, p.post_title, pm.meta_value as views_data
            FROM {$wpdb->posts} p
            INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
            WHERE p.post_type = 'post'
            AND p.post_status = 'publish'
            AND pm.meta_key = '_mva_views_data'
            AND pm.meta_value != ''
            AND pm.meta_value != 'a:0:{}'
            ORDER BY p.post_date DESC
            LIMIT %d
        ", $limit * 3); // Get more posts to account for filtering
        
        $posts = $wpdb->get_results($query);
        $articles = array();
        
        foreach ($posts as $post) {
            $views_data = maybe_unserialize($post->views_data);
            if (!$views_data || !is_array($views_data)) {
                continue;
            }
            
            $view_count = 0;
            
            // Optimized counting with early break
            foreach ($views_data as $view) {
                if (isset($view['timestamp']) && $view['timestamp'] >= $time_start) {
                    $view_count++;
                }
            }
            
            if ($view_count > 0) {
                $articles[] = array(
                    'id' => $post->ID,
                    'title' => $post->post_title,
                    'permalink' => get_permalink($post->ID),
                    'views' => $view_count
                );
            }
            
            // Early exit optimization
            if (count($articles) >= $limit) {
                break;
            }
        }
        
        // Sort by view count (highest first)
        usort($articles, function($a, $b) {
            return $b['views'] - $a['views'];
        });
        
        $final_articles = array_slice($articles, 0, $limit);
        
        // Cache for 5 minutes (Performance Layer 2)
        set_transient($cache_key, $final_articles, self::CACHE_EXPIRATION);
        
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
        // Create index for better performance
        global $wpdb;
        
        $wpdb->query("
            CREATE INDEX IF NOT EXISTS idx_mva_views 
            ON {$wpdb->postmeta} (meta_key, post_id) 
            WHERE meta_key = '_mva_views_data'
        ");
        
        flush_rewrite_rules();
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
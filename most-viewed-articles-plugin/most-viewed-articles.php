<?php
/**
 * Plugin Name: Most Viewed Articles
 * Description: A widget that displays the most viewed articles with tabbed interface for "This Week" and "This Month".
 * Version: 1.0.0
 * Author: Bojan Ilievski
 * Text Domain: most-viewed-articles
 * Domain Path: /languages
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Main plugin class
 */
class Most_Viewed_Articles {
    
    /**
     * Plugin version
     */
    const VERSION = '1.0.0';
    
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
     * Enqueue scripts and styles
     */
    public function enqueue_scripts() {
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
        
        wp_localize_script('most-viewed-articles', 'mva_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('mva_nonce')
        ));
    }
    
    /**
     * Track post views
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
        
        // Check if this IP has viewed this post in the last hour
        $last_view = get_transient("mva_view_{$post_id}_{$user_ip}");
        
        if ($last_view) {
            return; // Don't count duplicate views
        }
        
        // Set transient for 1 hour to prevent duplicate views
        set_transient("mva_view_{$post_id}_{$user_ip}", $current_time, HOUR_IN_SECONDS);
        
        // Update view count
        $view_count = get_post_meta($post_id, '_mva_view_count', true);
        $view_count = $view_count ? intval($view_count) + 1 : 1;
        update_post_meta($post_id, '_mva_view_count', $view_count);
        
        // Store view with timestamp for time-based queries
        $views_data = get_post_meta($post_id, '_mva_views_data', true);
        if (!$views_data) {
            $views_data = array();
        }
        
        $views_data[] = array(
            'timestamp' => $current_time,
            'ip' => hash('sha256', $user_ip) // Hash IP for privacy
        );
        
        // Keep only last 30 days of data
        $thirty_days_ago = $current_time - (30 * DAY_IN_SECONDS);
        $views_data = array_filter($views_data, function($view) use ($thirty_days_ago) {
            return $view['timestamp'] >= $thirty_days_ago;
        });
        
        update_post_meta($post_id, '_mva_views_data', $views_data);
    }
    
    /**
     * Get user IP address
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
     * AJAX handler for getting most viewed articles
     */
    public function ajax_get_most_viewed() {
        check_ajax_referer('mva_nonce', 'nonce');
        
        $timeframe = sanitize_text_field($_POST['timeframe']);
        $articles = $this->get_most_viewed_articles($timeframe);
        
        wp_send_json_success($articles);
    }
    
    /**
     * Get most viewed articles for a timeframe
     */
    public function get_most_viewed_articles($timeframe = 'week', $limit = 10) {
        global $wpdb;
        
        $current_time = current_time('timestamp');
        
        if ($timeframe === 'week') {
            $time_ago = $current_time - (7 * DAY_IN_SECONDS);
        } else {
            $time_ago = $current_time - (30 * DAY_IN_SECONDS);
        }
        
        // Get posts with view data
        $query = "
            SELECT p.ID, p.post_title, p.post_name, pm.meta_value as views_data
            FROM {$wpdb->posts} p
            INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
            WHERE p.post_type = 'post'
            AND p.post_status = 'publish'
            AND pm.meta_key = '_mva_views_data'
        ";
        
        $posts = $wpdb->get_results($query);
        $articles = array();
        
        foreach ($posts as $post) {
            $views_data = maybe_unserialize($post->views_data);
            if (!$views_data) {
                continue;
            }
            
            $view_count = 0;
            foreach ($views_data as $view) {
                if ($view['timestamp'] >= $time_ago) {
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
        }
        
        // Sort by view count
        usort($articles, function($a, $b) {
            return $b['views'] - $a['views'];
        });
        
        return array_slice($articles, 0, $limit);
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
        // Create necessary database tables or options if needed
        flush_rewrite_rules();
    }
    
    /**
     * Plugin deactivation
     */
    public function deactivate() {
        flush_rewrite_rules();
    }
}

/**
 * Most Viewed Articles Widget Class
 */
class Most_Viewed_Articles_Widget extends WP_Widget {
    
    public function __construct() {
        parent::__construct(
            'most_viewed_articles_widget',
            __('Most Viewed Articles', 'most-viewed-articles'),
            array(
                'description' => __('Display most viewed articles with tabbed interface', 'most-viewed-articles'),
                'classname' => 'most-viewed-articles-widget'
            )
        );
    }
    
    /**
     * Widget frontend display
     */
    public function widget($args, $instance) {
        echo $args['before_widget'];
        
        $title = !empty($instance['title']) ? $instance['title'] : __('Most Viewed Articles', 'most-viewed-articles');
        echo $args['before_title'] . apply_filters('widget_title', $title) . $args['after_title'];
        
        $plugin = new Most_Viewed_Articles();
        $week_articles = $plugin->get_most_viewed_articles('week');
        ?>
        
        <div class="mva-widget" data-widget-id="<?php echo $this->id; ?>">
            <div class="mva-tabs">
                <button class="mva-tab active" data-timeframe="week">
                    <?php _e('This Week', 'most-viewed-articles'); ?>
                </button>
                <button class="mva-tab" data-timeframe="month">
                    <?php _e('This Month', 'most-viewed-articles'); ?>
                </button>
            </div>
            
            <div class="mva-content">
                <div class="mva-articles" data-timeframe="week">
                    <?php $this->render_articles($week_articles); ?>
                </div>
                <div class="mva-articles" data-timeframe="month" style="display: none;">
                    <!-- Content loaded via AJAX -->
                </div>
            </div>
        </div>
        
        <?php
        echo $args['after_widget'];
    }
    
    /**
     * Render articles list
     */
    private function render_articles($articles) {
        if (empty($articles)) {
            echo '<p>' . __('No articles found.', 'most-viewed-articles') . '</p>';
            return;
        }
        
        echo '<ol class="mva-articles-list">';
        foreach ($articles as $index => $article) {
            echo '<li class="mva-article-item">';
            echo '<span class="mva-rank">' . ($index + 1) . '.</span>';
            echo '<a href="' . esc_url($article['permalink']) . '" class="mva-article-link">';
            echo '<span class="mva-article-title">' . esc_html($article['title']) . '</span>';
            echo '<span class="mva-view-count">(' . $article['views'] . ' views)</span>';
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
            <label for="<?php echo $this->get_field_id('title'); ?>">
                <?php _e('Title:', 'most-viewed-articles'); ?>
            </label>
            <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" 
                   name="<?php echo $this->get_field_name('title'); ?>" type="text" 
                   value="<?php echo esc_attr($title); ?>">
        </p>
        <?php
    }
    
    /**
     * Update widget settings
     */
    public function update($new_instance, $old_instance) {
        $instance = array();
        $instance['title'] = (!empty($new_instance['title'])) ? sanitize_text_field($new_instance['title']) : '';
        return $instance;
    }
}

// Initialize the plugin
new Most_Viewed_Articles();
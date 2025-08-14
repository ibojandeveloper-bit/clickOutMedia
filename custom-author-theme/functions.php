<?php
/**
 * Custom Author Theme functions and definitions
 *
 * @package Custom_Author_Theme
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Theme setup
 */
function custom_author_theme_setup() {
    // Add theme support for post thumbnails
    add_theme_support('post-thumbnails');
    
    // Add theme support for title tag
    add_theme_support('title-tag');
    
    // Add theme support for HTML5
    add_theme_support('html5', array(
        'search-form',
        'comment-form',
        'comment-list',
        'gallery',
        'caption',
        'script',
        'style'
    ));
    
    // Add theme support for responsive embeds
    add_theme_support('responsive-embeds');
    
    // Register navigation menus
    register_nav_menus(array(
        'primary' => __('Primary Menu', 'custom-author-theme'),
        'footer' => __('Footer Menu', 'custom-author-theme'),
    ));
    
    // Add image sizes
    add_image_size('author-thumbnail', 80, 80, true);
    add_image_size('post-thumbnail-small', 80, 60, true);
    add_image_size('news-thumbnail', 60, 45, true);
}
add_action('after_setup_theme', 'custom_author_theme_setup');

/**
 * Enqueue scripts and styles
 */
function custom_author_theme_scripts() {
    // Enqueue styles
    wp_enqueue_style(
        'custom-author-theme-style',
        get_stylesheet_uri(),
        array(),
        wp_get_theme()->get('Version')
    );
    
    // Enqueue custom JavaScript for lazy loading and interactions
    wp_enqueue_script(
        'custom-author-theme-script',
        get_template_directory_uri() . '/js/theme.js',
        array(),
        wp_get_theme()->get('Version'),
        true
    );
    
    // Add inline CSS for critical above-the-fold styles
    $critical_css = "
        body{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Oxygen,Ubuntu,Cantarell,sans-serif;margin:0;padding:0}
        .main-content{max-width:1200px;margin:0 auto;padding:20px}
        .author-profile{background:#fff;border-radius:8px;padding:30px;margin-bottom:30px}
    ";
    wp_add_inline_style('custom-author-theme-style', $critical_css);
}
add_action('wp_enqueue_scripts', 'custom_author_theme_scripts');

/**
 * Register widget areas
 */
function custom_author_theme_widgets_init() {
    register_sidebar(array(
        'name'          => __('Author Sidebar', 'custom-author-theme'),
        'id'            => 'author-sidebar',
        'description'   => __('Add widgets here to appear in the author page sidebar.', 'custom-author-theme'),
        'before_widget' => '<div class="sidebar-widget">',
        'after_widget'  => '</div>',
        'before_title'  => '<h3>',
        'after_title'   => '</h3>',
    ));
}
add_action('widgets_init', 'custom_author_theme_widgets_init');

/**
 * Add custom fields to user profile
 */
function custom_author_theme_user_profile_fields($user) {
    ?>
    <h3><?php _e('Author Profile Information', 'custom-author-theme'); ?></h3>
    
    <table class="form-table">
        <tr>
            <th><label for="author_title"><?php _e('Author Title', 'custom-author-theme'); ?></label></th>
            <td>
                <input type="text" name="author_title" id="author_title" 
                       value="<?php echo esc_attr(get_user_meta($user->ID, 'author_title', true)); ?>" 
                       class="regular-text" />
                <p class="description"><?php _e('e.g., Poker Expert, Senior Writer', 'custom-author-theme'); ?></p>
            </td>
        </tr>
        
        <tr>
            <th><label for="custom_avatar"><?php _e('Custom Avatar URL', 'custom-author-theme'); ?></label></th>
            <td>
                <input type="url" name="custom_avatar" id="custom_avatar" 
                       value="<?php echo esc_url(get_user_meta($user->ID, 'custom_avatar', true)); ?>" 
                       class="regular-text" />
                <p class="description"><?php _e('Enter a URL for a custom profile picture.', 'custom-author-theme'); ?></p>
                <button type="button" class="button" onclick="openMediaUploader('custom_avatar')"><?php _e('Upload Image', 'custom-author-theme'); ?></button>
            </td>
        </tr>
        
        <tr>
            <th><label for="facebook_url"><?php _e('Facebook URL', 'custom-author-theme'); ?></label></th>
            <td>
                <input type="url" name="facebook_url" id="facebook_url" 
                       value="<?php echo esc_url(get_user_meta($user->ID, 'facebook_url', true)); ?>" 
                       class="regular-text" />
            </td>
        </tr>
        
        <tr>
            <th><label for="twitter_url"><?php _e('Twitter URL', 'custom-author-theme'); ?></label></th>
            <td>
                <input type="url" name="twitter_url" id="twitter_url" 
                       value="<?php echo esc_url(get_user_meta($user->ID, 'twitter_url', true)); ?>" 
                       class="regular-text" />
            </td>
        </tr>
        
        <tr>
            <th><label for="youtube_url"><?php _e('YouTube URL', 'custom-author-theme'); ?></label></th>
            <td>
                <input type="url" name="youtube_url" id="youtube_url" 
                       value="<?php echo esc_url(get_user_meta($user->ID, 'youtube_url', true)); ?>" 
                       class="regular-text" />
            </td>
        </tr>
        
        <tr>
            <th><label for="instagram_url"><?php _e('Instagram URL', 'custom-author-theme'); ?></label></th>
            <td>
                <input type="url" name="instagram_url" id="instagram_url" 
                       value="<?php echo esc_url(get_user_meta($user->ID, 'instagram_url', true)); ?>" 
                       class="regular-text" />
            </td>
        </tr>
        
        <tr>
            <th><label for="linkedin_url"><?php _e('LinkedIn URL', 'custom-author-theme'); ?></label></th>
            <td>
                <input type="url" name="linkedin_url" id="linkedin_url" 
                       value="<?php echo esc_url(get_user_meta($user->ID, 'linkedin_url', true)); ?>" 
                       class="regular-text" />
            </td>
        </tr>
    </table>
    
    <script>
    function openMediaUploader(inputId) {
        var mediaUploader = wp.media({
            title: '<?php _e('Choose Profile Image', 'custom-author-theme'); ?>',
            button: {
                text: '<?php _e('Choose Image', 'custom-author-theme'); ?>'
            },
            multiple: false
        });
        
        mediaUploader.on('select', function() {
            var attachment = mediaUploader.state().get('selection').first().toJSON();
            document.getElementById(inputId).value = attachment.url;
        });
        
        mediaUploader.open();
    }
    </script>
    
    <?php
}
add_action('show_user_profile', 'custom_author_theme_user_profile_fields');
add_action('edit_user_profile', 'custom_author_theme_user_profile_fields');

/**
 * Save custom user profile fields
 */
function custom_author_theme_save_user_profile_fields($user_id) {
    if (!current_user_can('edit_user', $user_id)) {
        return false;
    }
    
    $fields = array(
        'author_title',
        'custom_avatar', 
        'facebook_url',
        'twitter_url',
        'youtube_url',
        'instagram_url',
        'linkedin_url'
    );
    
    foreach ($fields as $field) {
        if (isset($_POST[$field])) {
            $value = sanitize_text_field($_POST[$field]);
            if (in_array($field, array('custom_avatar', 'facebook_url', 'twitter_url', 'youtube_url', 'instagram_url', 'linkedin_url'))) {
                $value = esc_url_raw($value);
            }
            update_user_meta($user_id, $field, $value);
        }
    }
}
add_action('personal_options_update', 'custom_author_theme_save_user_profile_fields');
add_action('edit_user_profile_update', 'custom_author_theme_save_user_profile_fields');

/**
 * Enqueue media uploader scripts
 */
function custom_author_theme_admin_scripts($hook) {
    if ($hook == 'profile.php' || $hook == 'user-edit.php') {
        wp_enqueue_media();
    }
}
add_action('admin_enqueue_scripts', 'custom_author_theme_admin_scripts');

/**
 * Add meta box for featured news posts
 */
function custom_author_theme_add_meta_boxes() {
    add_meta_box(
        'featured_news',
        __('Featured as News', 'custom-author-theme'),
        'custom_author_theme_featured_news_callback',
        'post',
        'side',
        'default'
    );
}
add_action('add_meta_boxes', 'custom_author_theme_add_meta_boxes');

/**
 * Meta box callback for featured news
 */
function custom_author_theme_featured_news_callback($post) {
    wp_nonce_field('custom_author_theme_featured_news', 'custom_author_theme_featured_news_nonce');
    $featured = get_post_meta($post->ID, 'featured_as_news', true);
    ?>
    <label for="featured_as_news">
        <input type="checkbox" id="featured_as_news" name="featured_as_news" value="1" <?php checked($featured, '1'); ?> />
        <?php _e('Feature this post in the Latest News sidebar', 'custom-author-theme'); ?>
    </label>
    <?php
}

/**
 * Save featured news meta box
 */
function custom_author_theme_save_featured_news($post_id) {
    if (!isset($_POST['custom_author_theme_featured_news_nonce'])) {
        return;
    }
    
    if (!wp_verify_nonce($_POST['custom_author_theme_featured_news_nonce'], 'custom_author_theme_featured_news')) {
        return;
    }
    
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }
    
    $featured = isset($_POST['featured_as_news']) ? '1' : '0';
    update_post_meta($post_id, 'featured_as_news', $featured);
}
add_action('save_post', 'custom_author_theme_save_featured_news');

/**
 * Optimize images for performance
 */
function custom_author_theme_optimize_images($attr, $attachment, $size) {
    $attr['loading'] = 'lazy';
    $attr['decoding'] = 'async';
    return $attr;
}
add_filter('wp_get_attachment_image_attributes', 'custom_author_theme_optimize_images', 10, 3);

/**
 * Add preload hints for critical resources
 */
function custom_author_theme_preload_resources() {
    // Preload critical CSS
    echo '<link rel="preload" href="' . get_stylesheet_uri() . '" as="style" onload="this.onload=null;this.rel=\'stylesheet\'">';
    
    // Preload web fonts if using custom fonts
    if (get_theme_mod('custom_font_enabled', false)) {
        echo '<link rel="preconnect" href="https://fonts.googleapis.com">';
        echo '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>';
    }
}
add_action('wp_head', 'custom_author_theme_preload_resources', 1);

/**
 * Add schema markup for authors
 */
function custom_author_theme_author_schema() {
    if (is_author()) {
        $author = get_queried_object();
        $author_url = get_author_posts_url($author->ID);
        $avatar_url = get_user_meta($author->ID, 'custom_avatar', true);
        if (!$avatar_url) {
            $avatar_url = get_avatar_url($author->ID);
        }
        
        $schema = array(
            '@context' => 'https://schema.org',
            '@type' => 'Person',
            'name' => $author->display_name,
            'url' => $author_url,
            'image' => $avatar_url,
            'jobTitle' => get_user_meta($author->ID, 'author_title', true),
            'description' => get_user_meta($author->ID, 'description', true)
        );
        
        // Add social media profiles
        $social_profiles = array();
        $social_fields = array('facebook_url', 'twitter_url', 'youtube_url', 'instagram_url', 'linkedin_url');
        
        foreach ($social_fields as $field) {
            $url = get_user_meta($author->ID, $field, true);
            if ($url) {
                $social_profiles[] = $url;
            }
        }
        
        if (!empty($social_profiles)) {
            $schema['sameAs'] = $social_profiles;
        }
        
        echo '<script type="application/ld+json">' . json_encode($schema, JSON_UNESCAPED_SLASHES) . '</script>';
    }
}
add_action('wp_head', 'custom_author_theme_author_schema');

/**
 * Customize excerpt length for author pages
 */
function custom_author_theme_excerpt_length($length) {
    if (is_author()) {
        return 25;
    }
    return $length;
}
add_filter('excerpt_length', 'custom_author_theme_excerpt_length');

/**
 * Add custom body classes
 */
function custom_author_theme_body_classes($classes) {
    if (is_author()) {
        $classes[] = 'author-page';
        $author = get_queried_object();
        $classes[] = 'author-' . $author->user_nicename;
    }
    return $classes;
}
add_filter('body_class', 'custom_author_theme_body_classes');

/**
 * Security enhancements
 */
function custom_author_theme_security_headers() {
    if (!is_admin()) {
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: SAMEORIGIN');
        header('X-XSS-Protection: 1; mode=block');
    }
}
add_action('send_headers', 'custom_author_theme_security_headers');

/**
 * Performance optimization - Remove unnecessary WordPress features
 */
function custom_author_theme_optimize_wp() {
    // Remove emoji scripts
    remove_action('wp_head', 'print_emoji_detection_script', 7);
    remove_action('wp_print_styles', 'print_emoji_styles');
    
    // Remove WordPress version from head
    remove_action('wp_head', 'wp_generator');
    
    // Remove RSD link
    remove_action('wp_head', 'rsd_link');
    
    // Remove wlwmanifest link
    remove_action('wp_head', 'wlwmanifest_link');
}
add_action('init', 'custom_author_theme_optimize_wp');

/**
 * Add custom CSS for admin
 */
function custom_author_theme_admin_css() {
    echo '<style>
        .form-table th { width: 150px; }
        .form-table .regular-text { width: 300px; }
        #featured_as_news { margin-right: 8px; }
    </style>';
}
add_action('admin_head', 'custom_author_theme_admin_css');
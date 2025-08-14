<?php
/**
 * The template for displaying author pages
 *
 * @package Custom_Author_Theme
 */

get_header(); ?>

<main class="main-content">
    <div class="primary-content">
        <?php
        // Get the author object
        $author = get_queried_object();
        $author_id = $author->ID;
        
        // Get custom fields for author
        $author_title = get_user_meta($author_id, 'author_title', true);
        $author_bio = get_user_meta($author_id, 'description', true);
        $facebook_url = get_user_meta($author_id, 'facebook_url', true);
        $twitter_url = get_user_meta($author_id, 'twitter_url', true);
        $youtube_url = get_user_meta($author_id, 'youtube_url', true);
        $instagram_url = get_user_meta($author_id, 'instagram_url', true);
        $linkedin_url = get_user_meta($author_id, 'linkedin_url', true);
        ?>
        
        <!-- Author Profile Section -->
        <section class="author-profile fade-in">
            <div class="author-header">
                <div class="author-avatar">
                    <?php 
                    $avatar_url = get_user_meta($author_id, 'custom_avatar', true);
                    if ($avatar_url) {
                        echo '<img src="' . esc_url($avatar_url) . '" alt="' . esc_attr($author->display_name) . '" loading="lazy">';
                    } else {
                        echo get_avatar($author_id, 80);
                    }
                    ?>
                </div>
                <div class="author-info">
                    <h1><?php echo esc_html($author->display_name); ?></h1>
                    <?php if ($author_title) : ?>
                        <p class="author-title"><?php echo esc_html($author_title); ?></p>
                    <?php endif; ?>
                    
                    <?php if ($facebook_url || $twitter_url || $youtube_url || $instagram_url || $linkedin_url) : ?>
                        <div class="social-links">
                            <?php if ($facebook_url) : ?>
                                <a href="<?php echo esc_url($facebook_url); ?>" class="social-link facebook" target="_blank" rel="noopener" aria-label="Facebook">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                                    </svg>
                                </a>
                            <?php endif; ?>
                            
                            <?php if ($twitter_url) : ?>
                                <a href="<?php echo esc_url($twitter_url); ?>" class="social-link twitter" target="_blank" rel="noopener" aria-label="Twitter">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/>
                                    </svg>
                                </a>
                            <?php endif; ?>
                            
                            <?php if ($youtube_url) : ?>
                                <a href="<?php echo esc_url($youtube_url); ?>" class="social-link youtube" target="_blank" rel="noopener" aria-label="YouTube">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/>
                                    </svg>
                                </a>
                            <?php endif; ?>
                            
                            <?php if ($instagram_url) : ?>
                                <a href="<?php echo esc_url($instagram_url); ?>" class="social-link instagram" target="_blank" rel="noopener" aria-label="Instagram">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                                    </svg>
                                </a>
                            <?php endif; ?>
                            
                            <?php if ($linkedin_url) : ?>
                                <a href="<?php echo esc_url($linkedin_url); ?>" class="social-link linkedin" target="_blank" rel="noopener" aria-label="LinkedIn">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
                                    </svg>
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <?php if ($author_bio) : ?>
                <div class="about-section">
                    <h2><?php printf(__('About %s', 'custom-author-theme'), esc_html($author->display_name)); ?></h2>
                    <div class="about-content">
                        <?php 
                        $bio_excerpt = wp_trim_words($author_bio, 40, '...');
                        echo '<p>' . esc_html($bio_excerpt);
                        if (str_word_count($author_bio) > 40) {
                            echo ' <a href="#" class="expand-link" onclick="toggleBio(event)">' . __('Expand', 'custom-author-theme') . '</a>';
                        }
                        echo '</p>';
                        
                        if (str_word_count($author_bio) > 40) {
                            echo '<div class="full-bio" style="display: none;">';
                            echo '<p>' . esc_html($author_bio) . ' <a href="#" class="expand-link" onclick="toggleBio(event)">' . __('Collapse', 'custom-author-theme') . '</a></p>';
                            echo '</div>';
                        }
                        ?>
                    </div>
                </div>
            <?php endif; ?>
        </section>
        
        <!-- Latest Posts Section -->
        <section class="latest-posts fade-in">
            <h2><?php printf(__('Latest Posts from %s', 'custom-author-theme'), esc_html($author->display_name)); ?></h2>
            
            <?php
            // Query author posts
            $paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
            $author_posts = new WP_Query(array(
                'author' => $author_id,
                'post_type' => 'post',
                'post_status' => 'publish',
                'posts_per_page' => 10,
                'paged' => $paged
            ));
            
            if ($author_posts->have_posts()) : ?>
                <ul class="posts-list">
                    <?php while ($author_posts->have_posts()) : $author_posts->the_post(); ?>
                        <li class="post-item">
                            <?php if (has_post_thumbnail()) : ?>
                                <div class="post-thumbnail">
                                    <a href="<?php the_permalink(); ?>">
                                        <?php the_post_thumbnail('thumbnail', array('loading' => 'lazy')); ?>
                                    </a>
                                </div>
                            <?php endif; ?>
                            
                            <div class="post-content">
                                <h3>
                                    <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                                </h3>
                                <div class="post-excerpt">
                                    <?php echo wp_trim_words(get_the_excerpt(), 25, '...'); ?>
                                </div>
                            </div>
                        </li>
                    <?php endwhile; ?>
                </ul>
                
                <?php
                // Pagination
                $total_pages = $author_posts->max_num_pages;
                if ($total_pages > 1) : ?>
                    <div class="posts-pagination">
                        <div class="pagination-numbers">
                            <?php
                            $current_page = max(1, get_query_var('paged'));
                            
                            // Previous page
                            if ($current_page > 1) {
                                echo '<a href="' . get_author_posts_url($author_id, '', get_pagenum_link($current_page - 1)) . '" class="page-number">‹</a>';
                            }
                            
                            // Page numbers
                            for ($i = 1; $i <= min(5, $total_pages); $i++) {
                                $class = ($i == $current_page) ? 'page-number current' : 'page-number';
                                echo '<a href="' . get_author_posts_url($author_id, '', get_pagenum_link($i)) . '" class="' . $class . '">' . $i . '</a>';
                            }
                            
                            // Last page indicator
                            if ($total_pages > 5) {
                                echo '<span class="page-number">...</span>';
                                echo '<a href="' . get_author_posts_url($author_id, '', get_pagenum_link($total_pages)) . '" class="page-number">Last (' . $total_pages . ')</a>';
                            }
                            
                            // Next page
                            if ($current_page < $total_pages) {
                                echo '<a href="' . get_author_posts_url($author_id, '', get_pagenum_link($current_page + 1)) . '" class="page-number">›</a>';
                            }
                            ?>
                        </div>
                    </div>
                <?php endif; ?>
                
            <?php else : ?>
                <p><?php _e('No posts found by this author.', 'custom-author-theme'); ?></p>
            <?php endif; 
            
            wp_reset_postdata();
            ?>
        </section>
    </div>
    
    <!-- Sidebar -->
    <aside class="sidebar">
        <?php dynamic_sidebar('author-sidebar'); ?>
        
        <!-- Latest News Widget (Hardcoded for demo) -->
        <div class="sidebar-widget">
            <h3><?php _e('Latest News', 'custom-author-theme'); ?></h3>
            <?php
            $latest_news = new WP_Query(array(
                'post_type' => 'post',
                'posts_per_page' => 5,
                'meta_query' => array(
                    array(
                        'key' => 'featured_as_news',
                        'value' => '1',
                        'compare' => '='
                    )
                )
            ));
            
            if ($latest_news->have_posts()) :
                while ($latest_news->have_posts()) : $latest_news->the_post(); ?>
                    <div class="news-item">
                        <?php if (has_post_thumbnail()) : ?>
                            <div class="news-thumbnail">
                                <a href="<?php the_permalink(); ?>">
                                    <?php the_post_thumbnail('thumbnail', array('loading' => 'lazy')); ?>
                                </a>
                            </div>
                        <?php endif; ?>
                        <div class="news-content">
                            <h4>
                                <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                            </h4>
                        </div>
                    </div>
                <?php endwhile;
                wp_reset_postdata();
            else : ?>
                <p><?php _e('No news items found.', 'custom-author-theme'); ?></p>
            <?php endif; ?>
            
            <a href="<?php echo esc_url(home_url('/news')); ?>" class="show-all-link">
                <?php _e('Show all news', 'custom-author-theme'); ?>
            </a>
        </div>
    </aside>
</main>

<script>
function toggleBio(event) {
    event.preventDefault();
    const aboutContent = event.target.closest('.about-content');
    const excerpt = aboutContent.querySelector('p:first-child');
    const fullBio = aboutContent.querySelector('.full-bio');
    
    if (fullBio.style.display === 'none') {
        excerpt.style.display = 'none';
        fullBio.style.display = 'block';
    } else {
        excerpt.style.display = 'block';
        fullBio.style.display = 'none';
    }
}

// Lazy loading enhancement
document.addEventListener('DOMContentLoaded', function() {
    // Add fade-in animation to elements
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('fade-in');
            }
        });
    }, observerOptions);
    
    document.querySelectorAll('.post-item, .news-item').forEach(item => {
        observer.observe(item);
    });
});
</script>

<?php get_footer(); ?>
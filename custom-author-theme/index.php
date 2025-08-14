<?php
/**
 * The main template file
 *
 * @package Custom_Author_Theme
 */

get_header();
?>

<main id="primary" class="site-main">
    <div class="container">
        <div class="main-content">
            <div class="primary-content">
                
                <?php if (have_posts()) : ?>
                    
                    <?php if (is_home() && !is_front_page()) : ?>
                        <header class="page-header">
                            <h1 class="page-title"><?php single_post_title(); ?></h1>
                        </header>
                    <?php endif; ?>
                    
                    <div class="posts-container">
                        <?php while (have_posts()) : the_post(); ?>
                            
                            <article id="post-<?php the_ID(); ?>" <?php post_class('post-item fade-in'); ?>>
                                
                                <?php if (has_post_thumbnail()) : ?>
                                    <div class="post-thumbnail">
                                        <a href="<?php the_permalink(); ?>">
                                            <?php the_post_thumbnail('medium', array('loading' => 'lazy')); ?>
                                        </a>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="post-content">
                                    <header class="entry-header">
                                        <h2 class="entry-title">
                                            <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                                        </h2>
                                        
                                        <div class="entry-meta">
                                            <span class="post-author">
                                                <?php _e('By', 'custom-author-theme'); ?>
                                                <a href="<?php echo esc_url(get_author_posts_url(get_the_author_meta('ID'))); ?>">
                                                    <?php the_author(); ?>
                                                </a>
                                            </span>
                                            <span class="post-date">
                                                <time datetime="<?php echo esc_attr(get_the_date('c')); ?>">
                                                    <?php echo esc_html(get_the_date()); ?>
                                                </time>
                                            </span>
                                        </div>
                                    </header>
                                    
                                    <div class="entry-summary">
                                        <?php the_excerpt(); ?>
                                    </div>
                                    
                                    <footer class="entry-footer">
                                        <a href="<?php the_permalink(); ?>" class="read-more">
                                            <?php _e('Read More', 'custom-author-theme'); ?>
                                        </a>
                                    </footer>
                                </div>
                            </article>
                            
                        <?php endwhile; ?>
                    </div>
                    
                    <?php
                    // Pagination
                    the_posts_pagination(array(
                        'mid_size' => 2,
                        'prev_text' => __('&laquo; Previous', 'custom-author-theme'),
                        'next_text' => __('Next &raquo;', 'custom-author-theme'),
                    ));
                    ?>
                    
                <?php else : ?>
                    
                    <section class="no-results not-found">
                        <header class="page-header">
                            <h1 class="page-title"><?php _e('Nothing here', 'custom-author-theme'); ?></h1>
                        </header>
                        
                        <div class="page-content">
                            <?php if (is_home() && current_user_can('publish_posts')) : ?>
                                
                                <p>
                                    <?php
                                    printf(
                                        wp_kses(
                                            __('Ready to publish your first post? <a href="%1$s">Get started here</a>.', 'custom-author-theme'),
                                            array(
                                                'a' => array(
                                                    'href' => array(),
                                                ),
                                            )
                                        ),
                                        esc_url(admin_url('post-new.php'))
                                    );
                                    ?>
                                </p>
                                
                            <?php elseif (is_search()) : ?>
                                
                                <p><?php _e('Sorry, but nothing matched your search terms. Please try again with some different keywords.', 'custom-author-theme'); ?></p>
                                <?php get_search_form(); ?>
                                
                            <?php else : ?>
                                
                                <p><?php _e('It seems we can&rsquo;t find what you&rsquo;re looking for. Perhaps searching can help.', 'custom-author-theme'); ?></p>
                                <?php get_search_form(); ?>
                                
                            <?php endif; ?>
                        </div>
                    </section>
                    
                <?php endif; ?>
                
            </div>
            
            <aside class="sidebar">
                <?php get_sidebar(); ?>
            </aside>
        </div>
    </div>
</main>

<?php get_footer(); ?>
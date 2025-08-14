<?php
/**
 * The sidebar containing the main widget area
 *
 * @package Custom_Author_Theme
 */

if (!is_active_sidebar('sidebar-1') && !is_active_sidebar('author-sidebar')) {
    return;
}
?>

<aside id="secondary" class="widget-area">
    
    <?php if (is_author() && is_active_sidebar('author-sidebar')) : ?>
        
        <!-- Author Page Sidebar -->
        <div class="author-sidebar-widgets">
            <?php dynamic_sidebar('author-sidebar'); ?>
        </div>
        
    <?php endif; ?>
    
    <?php if (is_active_sidebar('sidebar-1')) : ?>
        
        <!-- Primary Sidebar -->
        <div class="primary-sidebar-widgets">
            <?php dynamic_sidebar('sidebar-1'); ?>
        </div>
        
    <?php endif; ?>
    
    <?php if (!is_active_sidebar('sidebar-1') && !is_active_sidebar('author-sidebar')) : ?>
        
        <!-- Default content when no widgets are active -->
        <div class="sidebar-widget">
            <h3><?php _e('Add Widgets', 'custom-author-theme'); ?></h3>
            <p><?php _e('Go to Appearance → Widgets to add widgets to this sidebar.', 'custom-author-theme'); ?></p>
        </div>
        
    <?php endif; ?>
    
</aside><!-- #secondary -->
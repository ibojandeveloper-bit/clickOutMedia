</div><!-- #content -->

    <!-- Footer Placeholder -->
    <footer class="site-footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-copyright">
                    <p>&copy; <?php echo date('Y'); ?> <?php bloginfo('name'); ?>. <?php _e('All rights reserved.', 'custom-author-theme'); ?></p>
                </div>
                
                <?php if (has_nav_menu('footer')) : ?>
                    <nav class="footer-navigation" aria-label="<?php esc_attr_e('Footer Menu', 'custom-author-theme'); ?>">
                        <?php
                        wp_nav_menu(array(
                            'theme_location' => 'footer',
                            'menu_id'        => 'footer-menu',
                            'container'      => false,
                            'menu_class'     => 'footer-links',
                        ));
                        ?>
                    </nav>
                <?php else : ?>
                    <div class="footer-links">
                        <a href="<?php echo esc_url(home_url('/terms')); ?>"><?php _e('Terms & Conditions', 'custom-author-theme'); ?></a>
                        <a href="<?php echo esc_url(home_url('/privacy')); ?>"><?php _e('Privacy Notice', 'custom-author-theme'); ?></a>
                        <a href="<?php echo esc_url(home_url('/contact')); ?>"><?php _e('Contact', 'custom-author-theme'); ?></a>
                        <a href="<?php echo esc_url(home_url('/help')); ?>"><?php _e('Help', 'custom-author-theme'); ?></a>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="footer-disclaimer">
                <p><?php _e('Play Responsibly', 'custom-author-theme'); ?></p>
            </div>
        </div>
    </footer>
</div><!-- #page -->

<?php wp_footer(); ?>
</body>
</html>
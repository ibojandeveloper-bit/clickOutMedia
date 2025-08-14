<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#2c3e50">
    <link rel="profile" href="https://gmpg.org/xfn/11">
    
    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<div id="page" class="site">
    <a class="skip-link screen-reader-text" href="#primary">
        <?php _e('Skip to content', 'custom-author-theme'); ?>
    </a>

    <!-- Header Placeholder -->
    <header class="site-header">
        <div class="container">
            <h1><?php bloginfo('name'); ?></h1>
            <p><?php bloginfo('description'); ?></p>
            
            <?php if (has_nav_menu('primary')) : ?>
                <nav class="main-navigation" aria-label="<?php esc_attr_e('Primary Menu', 'custom-author-theme'); ?>">
                    <?php
                    wp_nav_menu(array(
                        'theme_location' => 'primary',
                        'menu_id'        => 'primary-menu',
                        'container'      => false,
                        'menu_class'     => 'nav-menu',
                    ));
                    ?>
                </nav>
            <?php endif; ?>
        </div>
    </header>

    <div id="content" class="site-content">
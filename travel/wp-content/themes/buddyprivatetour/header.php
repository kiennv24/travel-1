<!DOCTYPE html>
<html <?php language_attributes(); ?> lang="en">
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php wp_title( '|', true, 'right' ); ?><?php bloginfo( 'name' ); ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <?php wp_head(); ?>
    <script>const BPT_IS_WORDPRESS = true;</script>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<!-- Top Announcement Ticker Bar -->
<div class="header-top-ticker">
    <div class="container">
        <span><span data-i18n="ticker_welcome">✨ Welcome to BUDDY PRIVATE TOUR – 24/7 Hotline / WhatsApp Support:</span> <a href="tel:+84567599998">+84 567 599 998</a></span>
    </div>
</div>

<?php
$current_page = 'home';
if (isset($_GET['pagename'])) {
    $current_page = sanitize_key($_GET['pagename']);
} elseif (is_page()) {
    $current_page = get_post_field('post_name', get_post());
} elseif (function_exists('is_product') && (is_product() || is_shop() || is_product_taxonomy())) {
    $current_page = 'tour';
} elseif (!is_front_page() && is_home()) {
    $current_page = 'home';
}
?>
<!-- Main Sticky Glassmorphism Header -->
<header class="site-header">
    <div class="header-main-wrap">
        <!-- Desktop Header Row -->
        <div class="header-top-row desktop-header-row">
            <!-- Brand Logo -->
            <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="brand-logo" title="BUDDY PRIVATE TOUR">
                <img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/logo.png' ); ?>" alt="BUDDY PRIVATE TOUR" class="site-logo-img">
            </a>

            <!-- Nav Links Column -->
            <div class="header-nav-column">
                <nav class="main-navigation">
                    <ul class="nav-menu">
                        <li class="<?php echo ($current_page === 'home') ? 'active' : ''; ?>">
                            <a href="<?php echo esc_url( home_url( '/' ) ); ?>"><i class="fa-solid fa-house" style="font-size: 0.8rem;"></i> <span data-i18n="nav_home">HOME</span></a>
                        </li>
                        <li class="<?php echo ($current_page === 'about-us') ? 'active' : ''; ?>">
                            <a href="<?php echo esc_url( site_url( '?pagename=about-us' ) ); ?>" data-i18n="nav_about">ABOUT US</a>
                        </li>
                        <li class="menu-item-has-children <?php echo in_array($current_page, ['tour', 'tour-detail']) ? 'active' : ''; ?>">
                            <a href="<?php echo esc_url( site_url( '?pagename=tour' ) ); ?>"><span data-i18n="nav_tour">TOURS</span></a>
                            <ul class="sub-menu">
                                <li><a href="<?php echo esc_url( site_url( '?pagename=tour&category=northern-vietnam-tours' ) ); ?>"><i class="fa-solid fa-mountain-sun" style="color: #0284c7;"></i> <span data-i18n="nav_north">HANOI & NORTH VIETNAM</span></a></li>
                                <li><a href="<?php echo esc_url( site_url( '?pagename=tour&category=central-vietnam-tours' ) ); ?>"><i class="fa-solid fa-umbrella-beach" style="color: #0ea5e9;"></i> <span data-i18n="nav_central">DA NANG & CENTRAL VIETNAM</span></a></li>
                                <li><a href="<?php echo esc_url( site_url( '?pagename=tour&category=southern-vietnam-tours' ) ); ?>"><i class="fa-solid fa-city" style="color: #f97316;"></i> <span data-i18n="nav_south">HO CHI MINH CITY & SOUTH</span></a></li>
                            </ul>
                        </li>
                        <li class="<?php echo in_array($current_page, ['other-services', 'other-services-detail']) ? 'active' : ''; ?>">
                            <a href="<?php echo esc_url( site_url( '?pagename=other-services' ) ); ?>" data-i18n="nav_other_services">CAR RENTAL & HOTELS</a>
                        </li>
                        <li class="<?php echo ($current_page === 'videos') ? 'active' : ''; ?>">
                            <a href="<?php echo esc_url( site_url( '?pagename=videos' ) ); ?>" data-i18n="nav_videos">VIDEOS</a>
                        </li>
                        <li class="<?php echo ($current_page === 'feedback') ? 'active' : ''; ?>">
                            <a href="<?php echo esc_url( site_url( '?pagename=feedback' ) ); ?>" data-i18n="nav_feedback">FEEDBACK</a>
                        </li>
                        <li class="<?php echo ($current_page === 'contact') ? 'active' : ''; ?>">
                            <a href="<?php echo esc_url( site_url( '?pagename=contact' ) ); ?>" data-i18n="nav_contact">CONTACT</a>
                        </li>
                    </ul>
                </nav>

                <!-- Right Actions: Cart -->
                <div class="header-right-actions">
                    <a href="<?php echo esc_url( function_exists('wc_get_cart_url') ? wc_get_cart_url() : site_url( '?pagename=cart' ) ); ?>" class="nav-cart-btn" title="Your Cart" data-i18n-title="nav_cart">
                        <i class="fa-solid fa-bag-shopping"></i>
                        <span class="cart-badge"><?php echo bpt_db_cart_count(); ?></span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Mobile Header Layout -->
        <div class="mobile-header-wrapper">
            <div class="mobile-header-bar">
                <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="mobile-brand-logo">
                    <img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/logo.png' ); ?>" alt="BUDDY PRIVATE TOUR" class="mobile-site-logo">
                </a>

                <div class="mobile-header-right">
                    <a href="<?php echo esc_url( function_exists('wc_get_cart_url') ? wc_get_cart_url() : site_url( '?pagename=cart' ) ); ?>" class="nav-cart-btn" title="Cart" style="width: 38px; height: 38px; font-size: 1rem;">
                        <i class="fa-solid fa-bag-shopping"></i>
                        <span class="cart-badge"><?php echo bpt_db_cart_count(); ?></span>
                    </a>
                    
                    <button type="button" class="mobile-menu-toggle-btn" id="mobileMenuToggleBtn" aria-label="Toggle navigation">
                        <i class="fa-solid fa-bars"></i>
                    </button>
                </div>
            </div>

            <!-- Mobile Dropdown Drawer -->
            <div class="mobile-menu-drawer" id="mobileMenuDrawer">
                <ul class="mobile-menu-list">
                    <li class="<?php echo ($current_page === 'home') ? 'active' : ''; ?>"><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><i class="fa-solid fa-house"></i> <span data-i18n="nav_home">HOME</span></a></li>
                    <li class="<?php echo ($current_page === 'about-us') ? 'active' : ''; ?>"><a href="<?php echo esc_url( site_url( '?pagename=about-us' ) ); ?>"><i class="fa-solid fa-circle-info"></i> <span data-i18n="nav_about">ABOUT US</span></a></li>
                    <li class="mobile-menu-has-sub <?php echo in_array($current_page, ['tour', 'tour-detail']) ? 'active' : ''; ?>">
                        <div class="mobile-sub-head">
                            <a href="<?php echo esc_url( site_url( '?pagename=tour' ) ); ?>"><i class="fa-solid fa-compass"></i> <span data-i18n="nav_tour">TOURS</span></a>
                            <span class="mobile-sub-toggle"><i class="fa-solid fa-chevron-down"></i></span>
                        </div>
                        <ul class="mobile-sub-list">
                            <li><a href="<?php echo esc_url( site_url( '?pagename=tour&category=northern-vietnam-tours' ) ); ?>" data-i18n="nav_north">Hanoi & North Vietnam</a></li>
                            <li><a href="<?php echo esc_url( site_url( '?pagename=tour&category=central-vietnam-tours' ) ); ?>" data-i18n="nav_central">Da Nang & Central Vietnam</a></li>
                            <li><a href="<?php echo esc_url( site_url( '?pagename=tour&category=southern-vietnam-tours' ) ); ?>" data-i18n="nav_south">Ho Chi Minh City & South</a></li>
                        </ul>
                    </li>
                    <li class="<?php echo in_array($current_page, ['other-services', 'other-services-detail']) ? 'active' : ''; ?>"><a href="<?php echo esc_url( site_url( '?pagename=other-services' ) ); ?>"><i class="fa-solid fa-car"></i> <span data-i18n="nav_other_services">CAR RENTAL & HOTELS</span></a></li>
                    <li class="<?php echo ($current_page === 'videos') ? 'active' : ''; ?>"><a href="<?php echo esc_url( site_url( '?pagename=videos' ) ); ?>"><i class="fa-solid fa-video"></i> <span data-i18n="nav_videos">VIDEOS</span></a></li>
                    <li class="<?php echo ($current_page === 'feedback') ? 'active' : ''; ?>"><a href="<?php echo esc_url( site_url( '?pagename=feedback' ) ); ?>"><i class="fa-solid fa-star"></i> <span data-i18n="nav_feedback">FEEDBACK</span></a></li>
                    <li class="<?php echo ($current_page === 'contact') ? 'active' : ''; ?>"><a href="<?php echo esc_url( site_url( '?pagename=contact' ) ); ?>"><i class="fa-solid fa-headset"></i> <span data-i18n="nav_contact">CONTACT</span></a></li>
                </ul>
            </div>
        </div>
    </div>
</header>

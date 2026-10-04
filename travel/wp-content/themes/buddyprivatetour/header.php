<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php wp_head(); ?>
    <script>const BPT_IS_WORDPRESS = true;</script>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<!-- Main Header Navigation matching user screenshot -->
<header class="site-header">
    <div class="header-main-wrap">
        <!-- Desktop Header Row -->
        <div class="header-top-row desktop-header-row">
            <!-- Brand Logo -->
            <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="brand-logo">
                <img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/logo.png' ); ?>" alt="BUDDY PRIVATE TOUR" class="site-logo-img">
            </a>

            <!-- Nav and Marquee Column (Desktop) -->
            <div class="header-nav-column">
                <nav class="main-navigation">
                    <ul class="nav-menu">
                        <li class="menu-item-home"><a href="<?php echo esc_url( home_url( '/' ) ); ?>">HOME</a></li>
                        <li><a href="<?php echo esc_url( site_url( '?pagename=about-us' ) ); ?>">ABOUT US</a></li>
                        <li class="menu-item-has-children">
                            <a href="<?php echo esc_url( site_url( '?pagename=tour' ) ); ?>">TOUR</a>
                            <ul class="sub-menu">
                                <li><a href="<?php echo esc_url( site_url( '?pagename=tour&category=northern-vietnam-tours' ) ); ?>">HA NOI AND NEARBY TOURS (NORTHERN VIETNAM TOURS)</a></li>
                                <li><a href="<?php echo esc_url( site_url( '?pagename=tour&category=central-vietnam-tours' ) ); ?>">DANANG AND NEARBY TOURS (CENTRAL VIETNAM TOURS)</a></li>
                                <li><a href="<?php echo esc_url( site_url( '?pagename=tour&category=southern-vietnam-tours' ) ); ?>">HO CHI MINH CITY AND NEARBY TOURS (SOUTHERN VIETNAM TOURS)</a></li>
                            </ul>
                        </li>
                        <li><a href="<?php echo esc_url( site_url( '?pagename=other-services' ) ); ?>">OTHER SERVICES</a></li>
                        <li><a href="<?php echo esc_url( site_url( '?pagename=tour' ) ); ?>#diary">TOUR DIARY</a></li>
                        <li><a href="<?php echo esc_url( site_url( '?pagename=videos' ) ); ?>">VIDEOS</a></li>
                        <li><a href="<?php echo esc_url( site_url( '?pagename=contact' ) ); ?>">CONTACT</a></li>
                        <li>
                            <a href="<?php echo esc_url( function_exists('wc_get_cart_url') ? wc_get_cart_url() : site_url( '?pagename=cart' ) ); ?>" class="nav-cart-btn" title="Cart">
                                <i class="fa-solid fa-cart-shopping"></i>
                                <span class="cart-badge"><?php echo bpt_db_cart_count(); ?></span>
                            </a>
                        </li>
                    </ul>
                </nav>

                <!-- Running ticker text bounded in nav column -->
                <div class="nav-marquee-wrapper">
                    <div class="nav-marquee-content">
                        <span>Chào mừng quý khách đến với BUDDY PRIVATE TOUR – Hotline: +84567599998</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Mobile Header Layout (Matching exact user screenshot) -->
        <div class="mobile-header-wrapper">
            <!-- Centered Logo -->
            <div class="mobile-logo-wrap">
                <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="mobile-brand-logo">
                    <img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/logo.png' ); ?>" alt="BUDDY PRIVATE TOUR" class="mobile-site-logo">
                </a>
            </div>

            <!-- Mobile MENU Bar -->
            <div class="mobile-menu-box">
                <div class="mobile-menu-bar" id="mobileMenuBar">
                    <span class="mobile-menu-text">MENU</span>
                    <div class="mobile-menu-right-actions">
                        <a href="<?php echo esc_url( function_exists('wc_get_cart_url') ? wc_get_cart_url() : site_url( '?pagename=cart' ) ); ?>" class="mobile-menu-cart-icon-btn" title="Cart" onclick="event.stopPropagation();">
                            <i class="fa-solid fa-cart-shopping"></i>
                            <span class="cart-badge"><?php echo bpt_db_cart_count(); ?></span>
                        </a>
                        <span class="mobile-menu-icon"><i class="fa-solid fa-bars"></i></span>
                    </div>
                </div>

                <!-- Dropdown Menu List -->
                <div class="mobile-menu-dropdown" id="mobileMenuDropdown">
                    <ul class="mobile-menu-list">
                        <li class="mobile-menu-item-home"><a href="<?php echo esc_url( home_url( '/' ) ); ?>">HOME</a></li>
                        <li><a href="<?php echo esc_url( site_url( '?pagename=about-us' ) ); ?>">ABOUT US</a></li>
                        <li class="mobile-menu-has-sub">
                            <div class="mobile-menu-sub-head">
                                <a href="<?php echo esc_url( site_url( '?pagename=tour' ) ); ?>">TOUR</a>
                                <span class="mobile-sub-chevron"><i class="fa-solid fa-chevron-down"></i></span>
                            </div>
                            <ul class="mobile-menu-sub-list">
                                <li><a href="<?php echo esc_url( site_url( '?pagename=tour&category=northern-vietnam-tours' ) ); ?>">HA NOI AND NEARBY TOURS (NORTHERN VIETNAM TOURS)</a></li>
                                <li><a href="<?php echo esc_url( site_url( '?pagename=tour&category=central-vietnam-tours' ) ); ?>">DANANG AND NEARBY TOURS (CENTRAL VIETNAM TOURS)</a></li>
                                <li><a href="<?php echo esc_url( site_url( '?pagename=tour&category=southern-vietnam-tours' ) ); ?>">HO CHI MINH CITY AND NEARBY TOURS (SOUTHERN VIETNAM TOURS)</a></li>
                            </ul>
                        </li>
                        <li><a href="<?php echo esc_url( site_url( '?pagename=other-services' ) ); ?>">OTHER SERVICES</a></li>
                        <li><a href="<?php echo esc_url( site_url( '?pagename=tour' ) ); ?>#diary">TOUR DIARY</a></li>
                        <li><a href="<?php echo esc_url( site_url( '?pagename=videos' ) ); ?>">VIDEOS</a></li>
                        <li><a href="<?php echo esc_url( site_url( '?pagename=contact' ) ); ?>">CONTACT</a></li>
                        <li>
                            <a href="<?php echo esc_url( function_exists('wc_get_cart_url') ? wc_get_cart_url() : site_url( '?pagename=cart' ) ); ?>" class="mobile-menu-cart-link">
                                CART (<?php echo bpt_db_cart_count(); ?>)
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</header>


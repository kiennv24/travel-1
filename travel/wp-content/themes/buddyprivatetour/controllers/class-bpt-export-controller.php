<?php
/**
 * Export Controller for Static Site Generation and ZIP Download
 * Buddy Private Tour - Luxury UI/UX Redesign
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

class BPT_ExportController {
    public static function init() {
        add_action('wp', [__CLASS__, 'handleExport']);
        add_action('admin_bar_menu', [__CLASS__, 'addAdminBarExportLink'], 999);
    }

    public static function addAdminBarExportLink($wp_admin_bar) {
        if (!current_user_can('manage_options')) {
            return;
        }
        $wp_admin_bar->add_node([
            'id'    => 'bpt-export-site',
            'title' => '📦 Xuất File Tĩnh (ZIP)',
            'href'  => home_url('/?controller=export'),
            'meta'  => ['target' => '_blank', 'title' => 'Xuất toàn bộ trang tĩnh & Tải file ZIP']
        ]);
    }

    public static function handleExport() {
        if (isset($_GET['controller']) && $_GET['controller'] === 'export') {
            // Require Admin Login
            if (!is_user_logged_in()) {
                auth_redirect();
                exit();
            }
            if (!current_user_can('manage_options')) {
                wp_die(
                    '<div style="text-align:center; padding: 2rem; font-family:sans-serif;">' .
                    '<h2 style="color:#ef4444;">🚫 Truy cập bị từ chối</h2>' .
                    '<p>Bạn cần đăng nhập bằng tài khoản Quản trị viên (Admin) để sử dụng chức năng Export ZIP này.</p>' .
                    '<a href="' . esc_url(wp_login_url(home_url('/?controller=export'))) . '" style="display:inline-block; margin-top:1rem; padding:0.6rem 1.5rem; background:#0284c7; color:#fff; text-decoration:none; border-radius:4px; font-weight:bold;">Đăng nhập Admin</a>' .
                    '</div>',
                    'Yêu cầu quyền Quản trị viên',
                    ['response' => 403]
                );
                exit();
            }

            if (!defined('BPT_EXPORT_COMPILING')) {
                define('BPT_EXPORT_COMPILING', true);
            }
            $action = $_GET['action'] ?? 'index';

            $tempBase = sys_get_temp_dir() . '/bpt_static_export';
            $exportDir = $tempBase . '/site';
            $zipFilePath = $tempBase . '/buddyprivatetour-static-site.zip';

            if ($action === 'downloadZip') {
                if (file_exists($zipFilePath)) {
                    while (ob_get_level()) {
                        ob_end_clean();
                    }
                    header('Content-Description: File Transfer');
                    header('Content-Type: application/zip');
                    header('Content-Disposition: attachment; filename="buddyprivatetour-static-site.zip"');
                    header('Expires: 0');
                    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
                    header('Pragma: public');
                    header('Content-Length: ' . filesize($zipFilePath));
                    readfile($zipFilePath);
                    exit();
                } else {
                    $action = 'index';
                }
            }

            // Clean previous temp directory if exists
            if (is_dir($exportDir)) {
                self::deleteDir($exportDir);
            }

            // Ensure directories exist in system temp
            if (!is_dir($tempBase)) mkdir($tempBase, 0777, true);
            if (!is_dir($exportDir)) mkdir($exportDir, 0777, true);
            if (!is_dir($exportDir . '/products')) mkdir($exportDir . '/products', 0777, true);
            if (!is_dir($exportDir . '/assets/css')) mkdir($exportDir . '/assets/css', 0777, true);
            if (!is_dir($exportDir . '/assets/js')) mkdir($exportDir . '/assets/js', 0777, true);
            if (!is_dir($exportDir . '/assets/images')) mkdir($exportDir . '/assets/images', 0777, true);

            // Copy all styles & JS & Images
            $cssFiles = glob(get_template_directory() . '/assets/css/*.css');
            if (!empty($cssFiles)) {
                foreach ($cssFiles as $cssFile) {
                    @copy($cssFile, $exportDir . '/assets/css/' . basename($cssFile));
                }
            }

            $jsFiles = glob(get_template_directory() . '/assets/js/*.js');
            if (!empty($jsFiles)) {
                foreach ($jsFiles as $jsFile) {
                    @copy($jsFile, $exportDir . '/assets/js/' . basename($jsFile));
                }
            }
            
            $imgFiles = glob(get_template_directory() . '/assets/images/*.*');
            if (!empty($imgFiles)) {
                foreach ($imgFiles as $imgFile) {
                    @copy($imgFile, $exportDir . '/assets/images/' . basename($imgFile));
                }
            }

            // Fetch DB data
            $categories = BPT_CategoryModel::getAll();
            $products = BPT_ProductModel::getAll();

            $cloudflare_base = 'https://buddyprivatetour.com/wp-content/themes/buddyprivatetour/';
            
            $mapped_products = array_map(function($product) use ($cloudflare_base) {
                $img = $product['image'];
                if ($img && !preg_match('/^https?:\/\//i', $img)) {
                    $product['image'] = $cloudflare_base . ltrim($img, './');
                }
                return $product;
            }, $products);

            $db_cart_items = bpt_db_cart_get_items();
            $mapped_cart = array();
            foreach ($db_cart_items as $item) {
                $img = $item['image'];
                if ($img && !preg_match('/^https?:\/\//i', $img)) {
                    $img = $cloudflare_base . ltrim($img, './');
                }
                $mapped_cart[] = array(
                    'id' => intval($item['id']),
                    'title' => $item['title'],
                    'price' => floatval($item['price']),
                    'image' => $img,
                    'duration' => $item['duration'] ?? 'Full Day',
                    'category_id' => intval($item['category_id'] ?? 0),
                    'category_name' => $item['category_name'] ?? '',
                    'quantity' => intval($item['quantity'])
                );
            }
            
            global $wpdb;
            $recent_codes = isset($_SESSION['bpt_recent_orders']) ? $_SESSION['bpt_recent_orders'] : [];
            $mapped_orders = array();
            if (!empty($recent_codes)) {
                $in_clause = implode("','", array_map('esc_sql', $recent_codes));
                $db_orders = $wpdb->get_results("SELECT * FROM orders WHERE order_code IN ('$in_clause') ORDER BY created_at DESC", ARRAY_A);
                foreach ($db_orders as $o) {
                    $items = $wpdb->get_results($wpdb->prepare("SELECT * FROM order_items WHERE order_id = %d", $o['id']), ARRAY_A);
                    $mapped_items = array();
                    foreach ($items as $it) {
                        $mapped_items[] = array(
                            'id' => intval($it['product_id']),
                            'title' => $it['product_title'],
                            'price' => floatval($it['price']),
                            'image' => '',
                            'duration' => '',
                            'quantity' => intval($it['quantity'])
                        );
                    }
                    $mapped_orders[] = array(
                        'order_code' => $o['order_code'],
                        'customer' => array(
                            'name' => $o['customer_name'],
                            'phone' => $o['customer_phone'],
                            'notes' => $o['notes']
                        ),
                        'items' => $mapped_items,
                        'total_amount' => floatval($o['total_amount']),
                        'created_at' => $o['created_at']
                    );
                }
            }

            $seedCategoriesJson = json_encode($categories, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            $seedProductsJson = json_encode($mapped_products, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            $seedCartJson = json_encode($mapped_cart, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            $seedOrdersJson = json_encode($mapped_orders, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

            // Generate HTML files
            $indexHtml = self::buildStaticIndex($categories, $mapped_products, $seedProductsJson, $seedCategoriesJson, $seedCartJson, $seedOrdersJson);
            file_put_contents($exportDir . '/index.html', $indexHtml);

            file_put_contents($exportDir . '/about-us.html', self::buildStaticAboutUs());
            file_put_contents($exportDir . '/tour.html', self::buildStaticTour($mapped_products, $seedProductsJson, $seedCategoriesJson, $seedCartJson, $seedOrdersJson));
            file_put_contents($exportDir . '/other-services.html', self::buildStaticOtherServices());
            $servicesList = BPT_ProductModel::getByCategorySlug('other-services');
            if (empty($servicesList)) {
                $servicesList = [['slug' => 'hotel'], ['slug' => 'car-rental'], ['slug' => 'airline-tickets']];
            }
            foreach ($servicesList as $srvItem) {
                $slug = $srvItem['slug'];
                file_put_contents($exportDir . '/other-services-detail-' . $slug . '.html', self::buildStaticOtherServicesDetail($slug));
            }
            file_put_contents($exportDir . '/videos.html', self::buildStaticVideos());
            file_put_contents($exportDir . '/contact.html', self::buildStaticContact());

            foreach ($mapped_products as $product) {
                $productHtml = self::buildStaticProduct($product, $mapped_products, $seedProductsJson, $seedCategoriesJson, $seedCartJson, $seedOrdersJson);
                file_put_contents($exportDir . '/products/product-' . $product['id'] . '.html', $productHtml);
            }

            $cartHtml = self::buildStaticCart($seedProductsJson, $seedCategoriesJson, $seedCartJson, $seedOrdersJson);
            file_put_contents($exportDir . '/cart.html', $cartHtml);

            $checkoutHtml = self::buildStaticCheckout($seedProductsJson, $seedCategoriesJson, $seedCartJson, $seedOrdersJson);
            file_put_contents($exportDir . '/checkout.html', $checkoutHtml);

            // Create ZIP
            self::createZipFromDir($exportDir, $zipFilePath);

            // Automatically clean up raw temporary HTML directory
            self::deleteDir($exportDir);

            $productCount = count($products);
            $zipSizeMb = round(filesize($zipFilePath) / (1024 * 1024), 2);
            
            get_header();
            ?>
            <div style="background: linear-gradient(135deg, #0284c7, #0f172a); color: #fff; padding: 4rem 0; text-align: center;">
                <div class="container">
                    <span style="background: rgba(255,255,255,0.2); padding: 0.35rem 1rem; border-radius: 999px; font-weight: 700; font-size: 0.85rem;">EXPORTER TĨNH & ĐÓNG GÓI ZIP THÀNH CÔNG</span>
                    <h1 style="font-size: 2.75rem; font-weight: 800; margin-top: 1rem;">🚀 Đã Xuất Tất Cả Trang Tĩnh & Nén File ZIP</h1>
                    <p style="color: #e2e8f0; max-width: 650px; margin: 0.75rem auto 0; font-size: 1.1rem;">Toàn bộ sản phẩm, chi tiết từng tour, giỏ hàng & thanh toán đã được nén vào 1 file ZIP duy nhất!</p>
                </div>
            </div>

            <div class="section-padding">
                <div class="container">
                    <div style="max-width: 860px; margin: 0 auto; background: #fff; padding: 2.5rem; border-radius: 20px; border: 1px solid var(--border-color); box-shadow: 0 10px 30px rgba(0,0,0,0.06);">
                        
                        <!-- Download Box -->
                        <div style="background: linear-gradient(135deg, #f0fdf4, #dcfce7); border: 2px solid #10b981; border-radius: 16px; padding: 2rem; text-align: center; margin-bottom: 2rem;">
                            <i class="fa-solid fa-file-zipper" style="font-size: 3.5rem; color: #10b981; margin-bottom: 1rem; display: block;"></i>
                            <h3 style="font-size: 1.6rem; font-weight: 800; color: #0f172a; margin-bottom: 0.5rem;">Tải Về Toàn Bộ Trang Web Tĩnh (.ZIP)</h3>
                            <p style="color: var(--text-muted); margin-bottom: 1.5rem;">Dung lượng: <strong><?php echo $zipSizeMb; ?> MB</strong> | Bao gồm: <strong><?php echo $productCount; ?> trang chi tiết sản phẩm</strong>, Trang chủ, Giới thiệu, Giỏ hàng & Thanh toán (dùng LocalStorage).</p>

                            <a href="index.php?controller=export&action=downloadZip" class="tour-card-cta-btn" style="padding: 1rem 2.25rem; font-size: 1.1rem; background: #0284c7; box-shadow: 0 4px 15px rgba(2, 132, 199, 0.4);">
                                <i class="fa-solid fa-download"></i> Tải Về File ZIP (buddyprivatetour-static-site.zip)
                            </a>
                        </div>

                        <!-- Important Instruction Banner -->
                        <div style="background: #fffbeb; border: 2px solid #f59e0b; border-radius: 16px; padding: 1.5rem; margin-bottom: 2rem;">
                            <h4 style="font-size: 1.15rem; font-weight: 800; color: #b45309; margin-bottom: 0.75rem; display: flex; align-items: center; gap: 0.5rem;">
                                <i class="fa-solid fa-triangle-exclamation" style="font-size: 1.3rem;"></i> ⚠️ HƯỚNG DẪN MỞ WEB TĨNH KHÔNG BỊ MẤT CSS
                            </h4>
                            <p style="color: #92400e; font-size: 0.95rem; line-height: 1.6; margin-bottom: 0.75rem;">
                                <strong>Bước 1:</strong> Sau khi tải file <code>buddyprivatetour-static-site.zip</code> về máy, bạn <strong>PHẢI GIẢI NÉN (Extract All / Giải nén)</strong> ra một thư mục riêng biệt.
                            </p>
                            <p style="color: #92400e; font-size: 0.95rem; line-height: 1.6; margin-bottom: 0.75rem;">
                                <strong>Bước 2:</strong> Mở thư mục vừa giải nén và nhấp đúp chuột vào file <strong><code>index.html</code></strong> để duyệt web.
                            </p>
                        </div>

                        <div style="display: flex; gap: 1rem; justify-content: center;">
                            <a href="index.php" class="tour-card-cta-btn" style="background: #0f172a;"><i class="fa-solid fa-arrow-left"></i> Quay Về Trang Chủ WordPress</a>
                        </div>
                    </div>
                </div>
            </div>
            <?php
            get_footer();
            exit();
        }
    }

    private static function deleteDir($dir) {
        if (!is_dir($dir)) return;
        $items = @scandir($dir);
        if ($items === false) return;
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') continue;
            $path = $dir . '/' . $item;
            if (is_dir($path)) {
                self::deleteDir($path);
            } else {
                @unlink($path);
            }
        }
        @rmdir($dir);
    }

    private static function createZipFromDir($sourceDir, $outZipPath) {
        if (file_exists($outZipPath)) {
            @unlink($outZipPath);
        }

        if (class_exists('ZipArchive')) {
            $zip = new ZipArchive();
            if ($zip->open($outZipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
                $files = new RecursiveIteratorIterator(
                    new RecursiveDirectoryIterator($sourceDir, RecursiveDirectoryIterator::SKIP_DOTS),
                    RecursiveIteratorIterator::LEAVES_ONLY
                );

                foreach ($files as $file) {
                    if (!$file->isDir()) {
                        $filePath = $file->getRealPath();
                        $relativePath = substr($filePath, strlen(realpath($sourceDir)) + 1);
                        $relativePath = str_replace('\\', '/', $relativePath);
                        $zip->addFile($filePath, $relativePath);
                    }
                }
                $zip->close();
                if (file_exists($outZipPath) && filesize($outZipPath) > 0) {
                    return true;
                }
            }
        }

        if (file_exists(ABSPATH . 'wp-admin/includes/class-pclzip.php')) {
            require_once ABSPATH . 'wp-admin/includes/class-pclzip.php';
            $pclZip = new PclZip($outZipPath);
            $v_list = $pclZip->create($sourceDir, PCLZIP_OPT_REMOVE_PATH, $sourceDir);
            if ($v_list != 0 && file_exists($outZipPath) && filesize($outZipPath) > 0) {
                return true;
            }
        }

        return false;
    }

    private static function getStaticHeader($title = "BUDDY PRIVATE TOUR (Static Site)", $isSubfolder = false) {
        $assetPrefix = $isSubfolder ? '../' : '';

        return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{$title}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="{$assetPrefix}assets/css/style.css">
    <link rel="stylesheet" href="{$assetPrefix}assets/css/tour-detail.css">
    <link rel="stylesheet" href="{$assetPrefix}assets/css/tour.css">
    <link rel="stylesheet" href="{$assetPrefix}assets/css/cart.css">
    <link rel="stylesheet" href="{$assetPrefix}assets/css/checkout.css">
    <link rel="stylesheet" href="{$assetPrefix}assets/css/other-services.css">
    <link rel="stylesheet" href="{$assetPrefix}assets/css/pages.css">
    <script src="{$assetPrefix}assets/js/translations.js"></script>
    <script src="{$assetPrefix}assets/js/app-localstorage.js"></script>
    <script src="{$assetPrefix}assets/js/main.js" defer></script>
</head>
<body>

<!-- Top Announcement Ticker Bar -->
<div class="header-top-ticker">
    <div class="container">
        <span>✨ <span data-i18n="ticker_welcome">Welcome to BUDDY PRIVATE TOUR – 24/7 Hotline / WhatsApp Support:</span> <a href="tel:+84567599998">+84 567 599 998</a></span>
    </div>
</div>

<!-- Main Sticky Glassmorphism Header -->
<header class="site-header">
    <div class="header-main-wrap">
        <div class="header-top-row desktop-header-row">
            <a href="{$assetPrefix}index.html" class="brand-logo" title="BUDDY PRIVATE TOUR">
                <img src="{$assetPrefix}assets/images/logo.png" alt="BUDDY PRIVATE TOUR" class="site-logo-img">
            </a>

            <div class="header-nav-column">
                <nav class="main-navigation">
                    <ul class="nav-menu">
                        <li class="menu-item-home"><a href="{$assetPrefix}index.html"><i class="fa-solid fa-house" style="font-size: 0.8rem;"></i> <span data-i18n="nav_home">HOME</span></a></li>
                        <li><a href="{$assetPrefix}about-us.html"><span data-i18n="nav_about">ABOUT US</span></a></li>
                        <li class="menu-item-has-children">
                            <a href="{$assetPrefix}tour.html"><span data-i18n="nav_tour">TOURS</span></a>
                            <ul class="sub-menu">
                                <li><a href="{$assetPrefix}tour.html?category=northern-vietnam-tours"><i class="fa-solid fa-mountain-sun" style="color: #0284c7;"></i> <span data-i18n="nav_north">HANOI & NORTH VIETNAM</span></a></li>
                                <li><a href="{$assetPrefix}tour.html?category=central-vietnam-tours"><i class="fa-solid fa-umbrella-beach" style="color: #0ea5e9;"></i> <span data-i18n="nav_central">DA NANG & CENTRAL VIETNAM</span></a></li>
                                <li><a href="{$assetPrefix}tour.html?category=southern-vietnam-tours"><i class="fa-solid fa-city" style="color: #f97316;"></i> <span data-i18n="nav_south">HO CHI MINH CITY & SOUTH</span></a></li>
                            </ul>
                        </li>
                        <li><a href="{$assetPrefix}other-services.html"><span data-i18n="nav_other_services">CAR RENTAL & HOTELS</span></a></li>
                        <li><a href="{$assetPrefix}videos.html"><span data-i18n="nav_videos">VIDEOS</span></a></li>
                        <li><a href="{$assetPrefix}contact.html"><span data-i18n="nav_contact">CONTACT</span></a></li>
                    </ul>
                </nav>

                <div class="header-right-actions">
                    <div class="bpt-lang-switcher" title="Select Language">
                        <button type="button" class="bpt-lang-btn active" data-lang="en">🇬🇧 EN</button>
                        <span class="bpt-lang-sep">|</span>
                        <button type="button" class="bpt-lang-btn" data-lang="vi">🇻🇳 VI</button>
                    </div>

                    <a href="{$assetPrefix}cart.html" class="nav-cart-btn" title="Shopping Cart">
                        <i class="fa-solid fa-bag-shopping"></i>
                        <span class="cart-badge">0</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Mobile Header Layout -->
        <div class="mobile-header-wrapper">
            <div class="mobile-header-bar">
                <a href="{$assetPrefix}index.html" class="mobile-brand-logo">
                    <img src="{$assetPrefix}assets/images/logo.png" alt="BUDDY PRIVATE TOUR" class="mobile-site-logo">
                </a>

                <div class="mobile-header-right">
                    <div class="bpt-lang-switcher" title="Select Language" style="padding: 3px 6px;">
                        <button type="button" class="bpt-lang-btn active" data-lang="en" style="font-size: 0.72rem; padding: 2px 5px;">EN</button>
                        <span class="bpt-lang-sep">|</span>
                        <button type="button" class="bpt-lang-btn" data-lang="vi" style="font-size: 0.72rem; padding: 2px 5px;">VI</button>
                    </div>

                    <a href="{$assetPrefix}cart.html" class="nav-cart-btn" title="Shopping Cart" style="width: 38px; height: 38px; font-size: 1rem;">
                        <i class="fa-solid fa-bag-shopping"></i>
                        <span class="cart-badge">0</span>
                    </a>
                    
                    <button type="button" class="mobile-menu-toggle-btn" id="mobileMenuToggleBtn" aria-label="Toggle navigation">
                        <i class="fa-solid fa-bars"></i>
                    </button>
                </div>
            </div>

            <div class="mobile-menu-drawer" id="mobileMenuDrawer">
                <ul class="mobile-menu-list">
                    <li><a href="{$assetPrefix}index.html"><i class="fa-solid fa-house" style="color: #0284c7; margin-right: 0.5rem;"></i> <span data-i18n="nav_home">HOME</span></a></li>
                    <li><a href="{$assetPrefix}about-us.html"><i class="fa-solid fa-circle-info" style="color: #0284c7; margin-right: 0.5rem;"></i> <span data-i18n="nav_about">ABOUT US</span></a></li>
                    <li class="mobile-menu-has-sub">
                        <div class="mobile-sub-head">
                            <a href="{$assetPrefix}tour.html"><i class="fa-solid fa-compass" style="color: #0284c7; margin-right: 0.5rem;"></i> <span data-i18n="nav_tour">TOURS</span></a>
                            <span class="mobile-sub-toggle"><i class="fa-solid fa-chevron-down"></i></span>
                        </div>
                        <ul class="mobile-sub-list">
                            <li><a href="{$assetPrefix}tour.html?category=northern-vietnam-tours"><span data-i18n="nav_north">Hanoi & North Vietnam</span></a></li>
                            <li><a href="{$assetPrefix}tour.html?category=central-vietnam-tours"><span data-i18n="nav_central">Da Nang & Central Vietnam</span></a></li>
                            <li><a href="{$assetPrefix}tour.html?category=southern-vietnam-tours"><span data-i18n="nav_south">Ho Chi Minh City & South</span></a></li>
                        </ul>
                    </li>
                    <li><a href="{$assetPrefix}other-services.html"><i class="fa-solid fa-car" style="color: #0284c7; margin-right: 0.5rem;"></i> <span data-i18n="nav_other_services">CAR RENTAL & HOTELS</span></a></li>
                    <li><a href="{$assetPrefix}videos.html"><i class="fa-solid fa-video" style="color: #0284c7; margin-right: 0.5rem;"></i> <span data-i18n="nav_videos">VIDEOS</span></a></li>
                    <li><a href="{$assetPrefix}contact.html"><i class="fa-solid fa-headset" style="color: #0284c7; margin-right: 0.5rem;"></i> <span data-i18n="nav_contact">CONTACT</span></a></li>
                </ul>
            </div>
        </div>
    </div>
</header>
HTML;
    }

    private static function getStaticFooter($isSubfolder = false) {
        $assetPrefix = $isSubfolder ? '../' : '';

        return <<<HTML
<!-- Luxury Dark Footer Section (#0f172a) -->
<footer id="contact" class="site-footer">
    <div class="container">
        <div class="footer-grid">
            <!-- Col 1: Brand & License Information -->
            <div class="footer-col">
                <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 1.25rem;">
                    <img src="{$assetPrefix}assets/images/logo.png" alt="BUDDY PRIVATE TOUR" style="height: 48px; width: auto; filter: brightness(0) invert(1);">
                </div>
                <p class="footer-brand-desc">
                    Chuyên gia tổ chức các chuyến du lịch trải nghiệm riêng tư, cá nhân hóa cao cấp tại Việt Nam. Đồng hành cùng hướng dẫn viên bản địa am hiểu văn hóa và đội xe hiện đại.
                </p>
                <div style="font-size: 0.85rem; color: #94a3b8; line-height: 1.6; margin-bottom: 1rem;">
                    <strong><span data-i18n="footer_license_lbl">Tour Operator License:</span></strong> 48-0284/2024/TCDL-GP LHQT<br>
                    <strong><span data-i18n="footer_tax_lbl">Enterprise Reg Code:</span></strong> 0402198998
                </div>
                <img src="{$assetPrefix}assets/images/dathongbao.png" class="footer-mit-badge" alt="Đã Thông Báo Bộ Công Thương">
            </div>

            <!-- Col 2: Quick Links -->
            <div class="footer-col">
                <h4 data-i18n="footer_col_nav">QUICK LINKS</h4>
                <ul class="footer-links-list">
                    <li><a href="{$assetPrefix}index.html"><i class="fa-solid fa-angle-right"></i> <span data-i18n="nav_home">Home</span></a></li>
                    <li><a href="{$assetPrefix}about-us.html"><i class="fa-solid fa-angle-right"></i> <span data-i18n="nav_about">About Us</span></a></li>
                    <li><a href="{$assetPrefix}tour.html"><i class="fa-solid fa-angle-right"></i> <span data-i18n="nav_tour">Tours</span></a></li>
                    <li><a href="{$assetPrefix}other-services.html"><i class="fa-solid fa-angle-right"></i> <span data-i18n="nav_other_services">Car Rental & Hotels</span></a></li>
                    <li><a href="{$assetPrefix}videos.html"><i class="fa-solid fa-angle-right"></i> <span data-i18n="nav_videos">Journey Videos</span></a></li>
                    <li><a href="{$assetPrefix}contact.html"><i class="fa-solid fa-angle-right"></i> <span data-i18n="nav_contact">Contact Us</span></a></li>
                </ul>
            </div>

            <!-- Col 3: Popular Destinations -->
            <div class="footer-col">
                <h4 data-i18n="footer_col_dest">POPULAR DESTINATIONS</h4>
                <ul class="footer-links-list">
                    <li><a href="{$assetPrefix}tour.html?category=northern-vietnam-tours"><i class="fa-solid fa-location-dot" style="color: #0284c7;"></i> <span data-i18n="footer_dest_hanoi">Hanoi & Old Quarter</span></a></li>
                    <li><a href="{$assetPrefix}tour.html?category=northern-vietnam-tours"><i class="fa-solid fa-location-dot" style="color: #0284c7;"></i> <span data-i18n="footer_dest_halong">Halong Bay & Ninh Binh</span></a></li>
                    <li><a href="{$assetPrefix}tour.html?category=central-vietnam-tours"><i class="fa-solid fa-location-dot" style="color: #0284c7;"></i> <span data-i18n="footer_dest_danang">Da Nang & Ba Na Hills</span></a></li>
                    <li><a href="{$assetPrefix}tour.html?category=central-vietnam-tours"><i class="fa-solid fa-location-dot" style="color: #0284c7;"></i> <span data-i18n="footer_dest_hoian">Hoi An Ancient Town & Hue</span></a></li>
                    <li><a href="{$assetPrefix}tour.html?category=southern-vietnam-tours"><i class="fa-solid fa-location-dot" style="color: #0284c7;"></i> <span data-i18n="footer_dest_saigon">Ho Chi Minh City & Cu Chi</span></a></li>
                    <li><a href="{$assetPrefix}tour.html?category=southern-vietnam-tours"><i class="fa-solid fa-location-dot" style="color: #0284c7;"></i> <span data-i18n="footer_dest_phuquoc">Phu Quoc Island</span></a></li>
                </ul>
            </div>

            <!-- Col 4: Support & Contact -->
            <div class="footer-col">
                <h4 data-i18n="footer_col_support">CUSTOMER SUPPORT</h4>
                <div class="footer-contact-info">
                    <div class="footer-contact-item">
                        <i class="fa-solid fa-location-dot"></i>
                        <span>24/9 Nguyen Luong Bang, Lien Chieu, Da Nang, Vietnam</span>
                    </div>
                    <div class="footer-contact-item">
                        <i class="fa-solid fa-phone-volume"></i>
                        <span>Hotline / WhatsApp: <a href="tel:+84567599998" style="color: #f97316; font-weight: 800;">+84 567 599 998</a></span>
                    </div>
                    <div class="footer-contact-item">
                        <i class="fa-solid fa-envelope"></i>
                        <span>buddyprivatetour@gmail.com</span>
                    </div>
                    <div class="footer-contact-item">
                        <i class="fa-solid fa-clock"></i>
                        <span data-i18n="footer_247_support">24/7 Online Customer Support</span>
                    </div>
                </div>

                <div class="footer-social-links">
                    <a href="https://zalo.me/84567599998" target="_blank" class="footer-social-btn" title="Zalo"><i class="fa-solid fa-comment-dots"></i></a>
                    <a href="mailto:buddyprivatetour@gmail.com" class="footer-social-btn" title="Email"><i class="fa-solid fa-envelope"></i></a>
                    <a href="#" class="footer-social-btn" title="Facebook"><i class="fa-brands fa-facebook-f"></i></a>
                    <a href="#" class="footer-social-btn" title="YouTube"><i class="fa-brands fa-youtube"></i></a>
                    <a href="#" class="footer-social-btn" title="TikTok"><i class="fa-brands fa-tiktok"></i></a>
                    <a href="#" class="footer-social-btn" title="TripAdvisor"><i class="fa-solid fa-plane"></i></a>
                </div>
            </div>
        </div>
    </div>

    <!-- Bottom Bar -->
    <div class="footer-bottom-bar">
        <div class="container footer-bottom-flex">
            <div>
                © 2026 <strong>BUDDY PRIVATE TOUR</strong>. <span data-i18n="footer_copyright">All Rights Reserved. Crafted with Passion for Travel Excellence.</span>
            </div>
            <div class="footer-payment-icons">
                <span class="footer-payment-tag"><i class="fa-solid fa-qrcode" style="color: #0284c7;"></i> VietQR</span>
                <span class="footer-payment-tag"><i class="fa-brands fa-cc-visa"></i> Visa</span>
                <span class="footer-payment-tag"><i class="fa-brands fa-cc-mastercard"></i> Mastercard</span>
                <span class="footer-payment-tag"><i class="fa-solid fa-shield-halved" style="color: #10b981;"></i> SSL Secure</span>
            </div>
        </div>
    </div>
</footer>

<!-- Floating Actions in Bottom-Left Corner -->
<div class="floating-left-actions">
    <a href="tel:+84567599998" class="bpt-hotline-pill" title="Gọi Hotline: +84567599998">
        <div class="bpt-hotline-circle">
            <div class="bpt-hotline-red">
                <i class="fa-solid fa-phone"></i>
            </div>
        </div>
        <span class="bpt-hotline-number">+84 567 599 998</span>
    </a>
    <a href="https://zalo.me/84567599998" target="_blank" rel="noopener noreferrer" class="bpt-zalo-bubble" title="Chat Zalo: +84567599998">
        <div class="bpt-zalo-inner">
            <span class="bpt-zalo-text">Zalo</span>
        </div>
    </a>
</div>

<!-- Floating Back to Top Button -->
<a href="#" class="floating-back-to-top" title="Lên đầu trang" onclick="window.scrollTo({top: 0, behavior: 'smooth'}); return false;">
    <i class="fa-solid fa-arrow-up"></i>
</a>

</body>
</html>
HTML;
    }

    private static function buildStaticAboutUs() {
        $header = self::getStaticHeader("About Us - Buddy Private Tour");
        $footer = self::getStaticFooter();
        
        $page = get_page_by_path('about-us');
        $content = $page ? apply_filters('the_content', $page->post_content) : '';
        $content = str_replace(home_url(), 'https://buddyprivatetour.com', $content);
        
        return <<<HTML
{$header}
<div class="section-padding" style="background: #f8fafc; min-height: 60vh;">
    <div class="container">
        <h1 style="font-size: 2.2rem; font-weight: 800; color: #0f172a; margin-bottom: 2rem; text-transform: uppercase;" data-i18n="nav_about">ABOUT US</h1>
        <div style="background: #fff; padding: 3rem; border-radius: 20px; border: 1px solid var(--border-color); box-shadow: var(--shadow-sm); max-width: 960px; margin: 0 auto;">
            <div class="about-content" style="font-size: 1.05rem; line-height: 1.85; color: #334155;">
                {$content}
            </div>
        </div>
    </div>
</div>
{$footer}
HTML;
    }

    private static function buildStaticTour($products, $seedProductsJson, $seedCategoriesJson, $seedCartJson, $seedOrdersJson) {
        $header = self::getStaticHeader("Tour Packages - Buddy Private Tour");
        $footer = self::getStaticFooter();
        return <<<HTML
{$header}
<div class="tour-page-wrapper">
    <div class="container">
        <!-- Breadcrumb Bar -->
        <div class="site-breadcrumb-wrap">
            <nav class="site-breadcrumb-box">
                <a href="index.html" class="crumb-link">
                    <i class="fa-solid fa-house"></i> <span data-i18n="crumb_home">Home</span>
                </a>
                <span class="crumb-separator">/</span>
                <span id="static-crumb-trail"><span class="crumb-current" data-i18n="crumb_tour">Tours</span></span>
            </nav>
        </div>

        <div class="tour-page-header-wrap">
            <h1 class="tour-page-heading" id="static-tour-heading">
                ALL PRIVATE TOURS
            </h1>
            <p class="tour-page-desc" data-i18n="tour_page_desc">All premium private tour packages include private guide, hotel pickup/drop-off, and 5-star standard care.</p>
        </div>

        <!-- Regional Category Filter Pills -->
        <div class="tour-category-pills-bar">
            <a href="tour.html" id="cat-tab-all" class="cat-pill-btn active"><i class="fa-solid fa-globe"></i> <span data-i18n="filter_all_tours">All Tours</span></a>
            <a href="tour.html?category=northern-vietnam-tours" id="cat-tab-north" class="cat-pill-btn"><i class="fa-solid fa-mountain-sun"></i> <span data-i18n="filter_north">Hanoi & North Vietnam</span></a>
            <a href="tour.html?category=central-vietnam-tours" id="cat-tab-central" class="cat-pill-btn"><i class="fa-solid fa-umbrella-beach"></i> <span data-i18n="filter_central">Da Nang & Central Vietnam</span></a>
            <a href="tour.html?category=southern-vietnam-tours" id="cat-tab-south" class="cat-pill-btn"><i class="fa-solid fa-city"></i> <span data-i18n="filter_south">Ho Chi Minh City & South</span></a>
        </div>

        <!-- Tour List Grid -->
        <div class="tours-grid" id="static-tours-grid"></div>
    </div>
</div>

<script>
const seedProducts = {$seedProductsJson};
const seedCategories = {$seedCategoriesJson};
const seedCart = {$seedCartJson};
const seedOrders = {$seedOrdersJson};

document.addEventListener('DOMContentLoaded', function() {
    BPT_Storage.init(seedProducts, seedCategories, seedCart, seedOrders);
    renderAllTours();
});

function renderAllTours() {
    const allProducts = BPT_Storage.getProducts();
    const grid = document.getElementById('static-tours-grid');
    const heading = document.getElementById('static-tour-heading');
    const crumbTrail = document.getElementById('static-crumb-trail');
    
    const urlParams = new URLSearchParams(window.location.search);
    const categoryParam = (urlParams.get('category') || '').toLowerCase();
    
    let filterKey = '';
    let currentCatName = 'ALL PRIVATE TOURS';
    
    document.querySelectorAll('.cat-pill-btn').forEach(btn => btn.classList.remove('active'));

    if (categoryParam) {
        if (categoryParam.includes('north') || categoryParam.includes('ha-noi') || categoryParam === '1') {
            filterKey = 'north';
            currentCatName = 'HANOI & NORTHERN VIETNAM TOURS';
            const t = document.getElementById('cat-tab-north');
            if (t) t.classList.add('active');
        } else if (categoryParam.includes('central') || categoryParam.includes('da-nang') || categoryParam.includes('danang') || categoryParam === '2') {
            filterKey = 'central';
            currentCatName = 'DA NANG & CENTRAL VIETNAM TOURS';
            const t = document.getElementById('cat-tab-central');
            if (t) t.classList.add('active');
        } else if (categoryParam.includes('south') || categoryParam.includes('ho-chi-minh') || categoryParam === '3') {
            filterKey = 'south';
            currentCatName = 'HO CHI MINH CITY & SOUTHERN VIETNAM TOURS';
            const t = document.getElementById('cat-tab-south');
            if (t) t.classList.add('active');
        }
    } else {
        const t = document.getElementById('cat-tab-all');
        if (t) t.classList.add('active');
    }

    heading.textContent = currentCatName;
    document.title = (currentCatName !== 'ALL PRIVATE TOURS' ? currentCatName + ' - ' : '') + 'Buddy Private Tour';

    if (filterKey) {
        crumbTrail.innerHTML = `
            <a href="tour.html" class="crumb-link" data-i18n="crumb_tour">Tours</a>
            <span class="crumb-separator">/</span>
            <span class="crumb-current crumb-category">\${currentCatName}</span>
        `;
    } else {
        crumbTrail.innerHTML = `<span class="crumb-current" data-i18n="crumb_tour">Tours</span>`;
    }

    let products = allProducts;
    if (filterKey) {
        products = allProducts.filter(p => {
            const cSlug = (p.category_slug || '').toLowerCase();
            const cName = (p.category_name || '').toLowerCase();
            const cId = parseInt(p.category_id) || 0;
            if (filterKey === 'north') {
                return cSlug.includes('north') || cName.includes('north') || cSlug.includes('ha-noi') || cName.includes('ha noi') || cName.includes('bắc') || cId === 1;
            }
            if (filterKey === 'central') {
                return cSlug.includes('central') || cName.includes('central') || cSlug.includes('da-nang') || cName.includes('da nang') || cName.includes('trung') || cId === 2;
            }
            if (filterKey === 'south') {
                return cSlug.includes('south') || cName.includes('south') || cSlug.includes('ho-chi-minh') || cName.includes('ho chi minh') || cName.includes('nam') || cId === 3;
            }
            return true;
        });
    }

    if (products.length === 0) {
        grid.innerHTML = `
            <div style="grid-column: 1 / -1; background: #fff; padding: 4rem; text-align: center; border-radius: 16px; border: 1px solid #e2e8f0;">
                <p style="color: #64748b; font-size: 1.1rem; margin: 0;" data-i18n="no_tours_found">No tours found in this category.</p>
            </div>`;
        return;
    }

    let html = '';
    products.forEach(p => {
        const imgSrc = BPT_Storage.fixImagePath(p.image);
        html += `
            <div class="tour-card">
                <div class="tour-card-image">
                    <a href="products/product-\${p.id}.html">
                        <img src="\${imgSrc}" alt="\${p.title}" loading="lazy">
                    </a>
                    <span class="tour-badge-private"><i class="fa-solid fa-gem"></i> <span data-i18n="card_badge_private">Private Tour</span></span>
                    <span class="tour-badge-duration"><i class="fa-regular fa-clock"></i> \${p.duration || 'Full Day'}</span>
                    <button type="button" class="tour-wishlist-btn"><i class="fa-regular fa-heart"></i></button>
                </div>
                <div class="tour-card-body">
                    <div class="tour-card-meta">
                        <span class="tour-location-tag"><i class="fa-solid fa-location-dot"></i> \${p.category_name}</span>
                        <div class="tour-rating-stars"><i class="fa-solid fa-star"></i> 4.9 <span style="color: #64748b; font-weight: normal;" data-i18n="card_reviews_suffix">(86)</span></div>
                    </div>
                    <h3 class="tour-card-title">
                        <a href="products/product-\${p.id}.html">\${p.title}</a>
                    </h3>
                    <div class="tour-card-perks">
                        <span class="tour-card-perk-item"><i class="fa-solid fa-check"></i> <span data-i18n="card_perk_car">Private car</span></span>
                        <span class="tour-card-perk-item"><i class="fa-solid fa-check"></i> <span data-i18n="card_perk_guide">Expert guide</span></span>
                        <span class="tour-card-perk-item"><i class="fa-solid fa-check"></i> <span data-i18n="card_perk_pickup">Hotel pickup</span></span>
                    </div>
                    <div class="tour-card-footer">
                        <div class="tour-price-wrap">
                            <span class="tour-price-label" data-i18n="card_price_from">From</span>
                            <span class="tour-price-value">\${BPT_Storage.formatCurrency(p.price)}</span>
                        </div>
                        <a href="products/product-\${p.id}.html" class="tour-card-cta-btn">
                            <span data-i18n="card_btn_view">View Tour</span> <i class="fa-solid fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>`;
    });
    grid.innerHTML = html;
}
</script>
{$footer}
HTML;
    }

    private static function buildStaticOtherServices() {
        $header = self::getStaticHeader("Car Rental & Hotels - Buddy Private Tour");
        $footer = self::getStaticFooter();
        
        return <<<HTML
{$header}
<div class="section-padding" style="background: #f8fafc; min-height: 80vh;">
    <div class="container">
        <div class="site-breadcrumb-wrap" style="padding-top: 0; margin-bottom: 1.75rem;">
            <nav class="site-breadcrumb-box">
                <a href="index.html" class="crumb-link"><i class="fa-solid fa-house"></i> <span data-i18n="crumb_home">Home</span></a>
                <span class="crumb-separator">/</span>
                <span class="crumb-current" data-i18n="nav_other_services">Other Services</span>
            </nav>
        </div>

        <h1 style="font-size: 2.2rem; font-weight: 800; color: #0f172a; margin-bottom: 2.5rem; text-transform: uppercase;">CAR RENTAL & TRAVEL SERVICES</h1>

        <div class="tours-grid">
            <div class="tour-card">
                <div class="tour-card-image">
                    <a href="other-services-detail-car-rental.html"><img src="assets/images/dkt-transport-cung-cap-dich-vu-cho-thue-xe-7-cho-tai-ha-noi.webp" alt="Car Rental"></a>
                </div>
                <div class="tour-card-body">
                    <h3 class="tour-card-title"><a href="other-services-detail-car-rental.html">CAR RENTAL FROM 4 - 45 SEATS</a></h3>
                    <div class="tour-card-footer" style="border-top: none;"><a href="other-services-detail-car-rental.html" class="tour-card-cta-btn" style="width: 100%; justify-content: center;"><span>View Details</span></a></div>
                </div>
            </div>
            <div class="tour-card">
                <div class="tour-card-image">
                    <a href="other-services-detail-hotel.html"><img src="assets/images/hotel.webp" alt="Hotel & Resort"></a>
                </div>
                <div class="tour-card-body">
                    <h3 class="tour-card-title"><a href="other-services-detail-hotel.html">HOTEL & RESORT RESERVATIONS</a></h3>
                    <div class="tour-card-footer" style="border-top: none;"><a href="other-services-detail-hotel.html" class="tour-card-cta-btn" style="width: 100%; justify-content: center;"><span>View Details</span></a></div>
                </div>
            </div>
            <div class="tour-card">
                <div class="tour-card-image">
                    <a href="other-services-detail-airline-tickets.html"><img src="assets/images/ve-7546-1571044895.webp" alt="Airline Tickets"></a>
                </div>
                <div class="tour-card-body">
                    <h3 class="tour-card-title"><a href="other-services-detail-airline-tickets.html">CHEAP AIRLINE TICKETS</a></h3>
                    <div class="tour-card-footer" style="border-top: none;"><a href="other-services-detail-airline-tickets.html" class="tour-card-cta-btn" style="width: 100%; justify-content: center;"><span>View Details</span></a></div>
                </div>
            </div>
        </div>
    </div>
</div>
{$footer}
HTML;
    }

    private static function buildStaticOtherServicesDetail($service_slug) {
        $service = BPT_ServiceModel::getBySlug($service_slug);
        if (!$service) return '';
        
        $header = self::getStaticHeader($service['title'] . " - Buddy Private Tour");
        $footer = self::getStaticFooter();
        
        $imagesHtml = '';
        foreach ($service['images'] as $img_url) {
            $src = $img_url;
            if (!preg_match('/^https?:\/\//i', $src)) {
                $src = ltrim($src, './');
            }
            $imagesHtml .= '<div style="width: 100%; border-radius: 12px; overflow: hidden; box-shadow: var(--shadow-sm);"><img src="' . esc_attr($src) . '" alt="' . esc_attr($service['title']) . '" style="width: 100%; height: auto; display: block;"></div>';
        }

        return <<<HTML
{$header}
<div class="section-padding" style="background: #f8fafc; min-height: 80vh; padding-top: 1.5rem; padding-bottom: 4rem;">
    <div class="container" style="max-width: 900px; margin: 0 auto;">
        <div class="site-breadcrumb-wrap" style="padding-top: 0; margin-bottom: 1.75rem;">
            <nav class="site-breadcrumb-box">
                <a href="index.html" class="crumb-link"><i class="fa-solid fa-house"></i> <span data-i18n="crumb_home">Home</span></a>
                <span class="crumb-separator">/</span>
                <a href="other-services.html" class="crumb-link" data-i18n="nav_other_services">Other Services</a>
                <span class="crumb-separator">/</span>
                <span class="crumb-current">{$service['title']}</span>
            </nav>
        </div>

        <div style="margin-bottom: 2rem;">
            <h1 style="font-size: 2rem; font-weight: 800; color: #0f172a; text-transform: uppercase; margin-bottom: 0.5rem;">{$service['title']}</h1>
            <p style="color: #64748b; font-size: 0.95rem;"><i class="fa-regular fa-calendar-days"></i> Latest update 2026</p>
        </div>

        <div class="gallery-stack" style="display: flex; flex-direction: column; gap: 1.5rem; margin-bottom: 3rem;">
            {$imagesHtml}
        </div>
    </div>
</div>
{$footer}
HTML;
    }

    private static function buildStaticVideos() {
        $header = self::getStaticHeader("Videos - Buddy Private Tour");
        $footer = self::getStaticFooter();
        
        return <<<HTML
{$header}
<div class="section-padding" style="background: #f8fafc; min-height: 80vh;">
    <div class="container">
        <h1 style="font-size: 2.2rem; font-weight: 800; color: #0f172a; margin-bottom: 2.5rem; text-transform: uppercase;" data-i18n="nav_videos">JOURNEY VIDEOS</h1>

        <div class="home-videos-section">
            <div class="home-videos-grid">
                <div class="home-video-card">
                    <div class="home-video-wrapper">
                        <iframe width="560" height="315" src="https://www.youtube.com/embed/AAeb7JPwJfw" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
                    </div>
                </div>

                <div class="home-video-card">
                    <div class="home-video-wrapper">
                        <iframe width="560" height="315" src="https://www.youtube.com/embed/STt213h-_hM" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>  
{$footer}
HTML;
    }

    private static function buildStaticContact() {
        $header = self::getStaticHeader("Contact - Buddy Private Tour");
        $footer = self::getStaticFooter();
        
        return <<<HTML
{$header}
<div class="section-padding" style="background: #ffffff; min-height: 80vh; padding-top: 3.5rem; padding-bottom: 5rem;">
    <div class="container" style="max-width: 1100px;">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 4.5rem;">
            <div>
                <h1 style="font-size: 1.85rem; font-weight: 800; color: #0f172a; text-transform: uppercase; margin-bottom: 1.5rem;" data-i18n="form_title">CUSTOMIZE YOUR PRIVATE TOUR</h1>
                <form action="#" method="POST" onsubmit="alert('Thank you! Your message has been sent successfully.'); return false;">
                    <div style="margin-bottom: 1.25rem;">
                        <label for="c_name" style="display: block; font-size: 0.88rem; color: #334155; font-weight: 700; margin-bottom: 0.4rem;" data-i18n="form_fullname_lbl">FULL NAME *</label>
                        <input type="text" id="c_name" required class="form-control" style="border-radius: 10px; padding: 0.75rem;" placeholder="e.g. John Smith" data-i18n-placeholder="form_fullname_ph">
                    </div>
                    <div style="margin-bottom: 1.25rem;">
                        <label for="c_phone" style="display: block; font-size: 0.88rem; color: #334155; font-weight: 700; margin-bottom: 0.4rem;" data-i18n="form_phone_lbl">PHONE / WHATSAPP *</label>
                        <input type="tel" id="c_phone" required class="form-control" style="border-radius: 10px; padding: 0.75rem;" placeholder="e.g. +84 567 599 998" data-i18n-placeholder="form_phone_ph">
                    </div>
                    <div style="margin-bottom: 1.5rem;">
                        <label for="c_msg" style="display: block; font-size: 0.88rem; color: #334155; font-weight: 700; margin-bottom: 0.4rem;" data-i18n="form_notes_lbl">SPECIAL REQUESTS / DATES</label>
                        <textarea id="c_msg" rows="4" class="form-control" style="border-radius: 10px; padding: 0.75rem;" placeholder="Tell us about your preferred schedule, number of travelers, hotel preferences..." data-i18n-placeholder="form_notes_ph"></textarea>
                    </div>
                    <button type="submit" class="tour-card-cta-btn" style="padding: 0.85rem 2rem; font-size: 1rem;" data-i18n="form_btn_submit">SEND REQUEST NOW</button>
                </form>
            </div>

            <div style="display: flex; flex-direction: column; justify-content: flex-start; padding-top: 1.5rem;">
                <h3 style="font-size: 1.25rem; font-weight: 800; color: #0f172a; text-transform: uppercase; margin-bottom: 1rem;" data-i18n="footer_col_support">CUSTOMER SUPPORT</h3>
                <h4 style="font-size: 1.05rem; font-weight: 800; color: #0284c7; margin-bottom: 1.25rem;">BUDDY PRIVATE TOUR</h4>
                <div style="font-size: 0.95rem; line-height: 1.8; color: #475569; display: flex; flex-direction: column; gap: 1rem;">
                    <p style="margin: 0;"><i class="fa-solid fa-location-dot" style="color: #0284c7; width: 20px;"></i> 24/9 Nguyen Luong Bang, Hoa Khanh Bac, Lien Chieu, Da Nang, Vietnam.</p>
                    <p style="margin: 0;"><i class="fa-solid fa-phone" style="color: #0284c7; width: 20px;"></i> Hotline / WhatsApp: <strong>+84 567 599 998</strong></p>
                    <p style="margin: 0;"><i class="fa-solid fa-envelope" style="color: #0284c7; width: 20px;"></i> Email: buddyprivatetour@gmail.com</p>
                </div>
            </div>
        </div>
    </div>
</div>
{$footer}
HTML;
    }

    private static function buildStaticIndex($categories, $products, $seedProductsJson, $seedCategoriesJson, $seedCartJson, $seedOrdersJson) {
        $header = self::getStaticHeader("Buddy Private Tour - Modern Luxury Private Journeys");
        $footer = self::getStaticFooter();

        return <<<HTML
{$header}
<!-- Hero Slider Section -->
<section class="hero-slider" id="hero-slider">
    <div class="slider-wrapper">
        <div class="slide-item active">
            <img src="assets/images/bn3.jpg" alt="Buddy Private Tour" class="slide-img" />
            <div class="hero-overlay">
                <span class="hero-tagline"><i class="fa-solid fa-crown"></i> <span data-i18n="hero_tagline_1">Exclusive Private Tour Services</span></span>
                <h1 class="hero-title" data-i18n="hero_title_1">Explore Vietnam In Your Own Unique Way</h1>
                <p class="hero-subtitle" data-i18n="hero_sub_1">Tailor-made private journeys for families & friends featuring modern private cars and licensed local tour guides.</p>
            </div>
        </div>
        <div class="slide-item">
            <img src="assets/images/bn2.jpg" alt="Buddy Private Tour" class="slide-img" />
            <div class="hero-overlay">
                <span class="hero-tagline"><i class="fa-solid fa-percent"></i> <span data-i18n="hero_tagline_2">Seasonal Tour Offers</span></span>
                <h2 class="hero-title" style="font-size: 2.85rem; font-weight: 800;" data-i18n="hero_title_2">Cherish Every Unforgettable Moment</h2>
                <p class="hero-subtitle" data-i18n="hero_sub_2">Indulge in 5-star standard private services with complete itinerary flexibility.</p>
            </div>
        </div>
        <div class="slide-item">
            <img src="assets/images/bn1.jpg" alt="Buddy Private Tour" class="slide-img" />
            <div class="hero-overlay">
                <span class="hero-tagline"><i class="fa-solid fa-gem"></i> <span data-i18n="hero_tagline_3">Distinctive Travel Experiences</span></span>
                <h2 class="hero-title" style="font-size: 2.85rem; font-weight: 800;" data-i18n="hero_title_3">Hanoi • Da Nang • Phu Quoc • Saigon</h2>
                <p class="hero-subtitle" data-i18n="hero_sub_3">Easy online booking, instant payment via VietQR, and immediate electronic voucher delivery.</p>
            </div>
        </div>
    </div>

    <div class="slider-bullets">
        <span class="bullet active" onclick="setSlide(0)"></span>
        <span class="bullet" onclick="setSlide(1)"></span>
        <span class="bullet" onclick="setSlide(2)"></span>
    </div>

    <button class="slider-nav prev" onclick="changeSlide(-1)"><i class="fa-solid fa-chevron-left"></i></button>
    <button class="slider-nav next" onclick="changeSlide(1)"><i class="fa-solid fa-chevron-right"></i></button>
</section>

<!-- Floating Interactive Search Pill -->
<div class="floating-search-pill-wrap">
    <form id="heroSearchForm" class="floating-search-pill" action="tour.html" method="GET">
        <div class="search-field-block">
            <span class="search-field-label"><i class="fa-solid fa-location-dot"></i> <span data-i18n="search_dest_label">Destination</span></span>
            <select name="category" id="searchDestination" class="search-field-select">
                <option value="" data-i18n="search_dest_placeholder">All Destinations</option>
                <option value="northern-vietnam-tours" data-i18n="search_dest_north">Hanoi & North Vietnam (Halong Bay, Ninh Binh)</option>
                <option value="central-vietnam-tours" data-i18n="search_dest_central">Da Nang & Central Vietnam (Hoi An, Hue, Ba Na)</option>
                <option value="southern-vietnam-tours" data-i18n="search_dest_south">Ho Chi Minh City & South (Mekong Delta, Phu Quoc)</option>
            </select>
        </div>

        <div class="search-field-block">
            <span class="search-field-label"><i class="fa-regular fa-calendar-days"></i> <span data-i18n="search_date_label">Departure Date</span></span>
            <input type="date" name="travel_date" id="searchDate" class="search-field-input" value="2026-10-04">
        </div>

        <div class="search-field-block">
            <span class="search-field-label"><i class="fa-solid fa-user-group"></i> <span data-i18n="search_guest_label">Number of Guests</span></span>
            <select name="guests" id="searchGuests" class="search-field-select">
                <option value="1" data-i18n="search_guest_1">1 Guest (Solo Private)</option>
                <option value="2" selected data-i18n="search_guest_2">2 Guests (Couples / Friends)</option>
                <option value="4" data-i18n="search_guest_4">3 - 5 Guests (Family)</option>
                <option value="8" data-i18n="search_guest_8">6+ Guests (Private Group)</option>
            </select>
        </div>

        <button type="submit" class="search-cta-btn">
            <i class="fa-solid fa-magnifying-glass"></i>
            <span data-i18n="btn_search">Search Tours</span>
        </button>
    </form>
</div>

<div id="catalog" class="section-padding">
    <div class="container">
        <div id="static-catalog-container"></div>

        <!-- Home Featured Videos Section -->
        <div class="home-videos-section">
            <div class="cat-header-wrap" style="margin-bottom: 2rem;">
                <span class="category-sub-badge"><i class="fa-solid fa-video"></i> <span data-i18n="sec_videos_badge">Real Journey Clips</span></span>
                <h2 class="category-section-title" data-i18n="sec_videos_title">JOURNEYS WITH BUDDY PRIVATE TOUR</h2>
            </div>
            <div class="home-videos-grid">
                <div class="home-video-card">
                    <div class="home-video-wrapper">
                        <iframe width="560" height="315" src="https://www.youtube.com/embed/AAeb7JPwJfw" title="YouTube video player" frameborder="0" allowfullscreen></iframe>
                    </div>
                </div>
                <div class="home-video-card">
                    <div class="home-video-wrapper">
                        <iframe width="560" height="315" src="https://www.youtube.com/embed/STt213h-_hM" title="YouTube video player" frameborder="0" allowfullscreen></iframe>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="middle-banner-fullwidth">
    <img src="assets/images/bn3.jpg" alt="Buddy Private Tour Banner">
</div>

<!-- Consultation form section -->
<section id="consultation" class="consultation-section" style="background-image: url('assets/images/halong-bg.jpg');">
    <div class="container">
        <div class="glass-form-box">
            <h3 class="glass-form-title" data-i18n="form_title">CUSTOMIZE YOUR PRIVATE TOUR</h3>
            <p class="glass-form-subtitle" data-i18n="form_subtitle">Leave your contact details and our travel experts will respond within 15 minutes</p>
            <form action="#" method="POST" onsubmit="alert('Thank you! Your inquiry has been submitted.'); return false;">
                <div class="glass-form-group">
                    <label for="cust_name" data-i18n="form_fullname_lbl">FULL NAME *</label>
                    <input type="text" id="cust_name" class="glass-form-control" required placeholder="e.g. John Smith" data-i18n-placeholder="form_fullname_ph">
                </div>
                <div class="glass-form-group">
                    <label for="cust_phone" data-i18n="form_phone_lbl">PHONE / WHATSAPP *</label>
                    <input type="tel" id="cust_phone" class="glass-form-control" required placeholder="e.g. +84 567 599 998" data-i18n-placeholder="form_phone_ph">
                </div>
                <div class="glass-form-group">
                    <label for="cust_msg" data-i18n="form_notes_lbl">SPECIAL REQUESTS / DATES</label>
                    <textarea id="cust_msg" class="glass-form-control" rows="3" placeholder="Tell us about your preferred schedule, number of travelers, hotel preferences..." data-i18n-placeholder="form_notes_ph"></textarea>
                </div>
                <div style="text-align: center; margin-top: 1.75rem;">
                    <button type="submit" class="glass-btn-submit" data-i18n="form_btn_submit"><i class="fa-solid fa-paper-plane"></i> SEND REQUEST NOW</button>
                </div>
            </form>
        </div>
    </div>
</section>

<script>
const seedProducts = {$seedProductsJson};
const seedCategories = {$seedCategoriesJson};
const seedCart = {$seedCartJson};
const seedOrders = {$seedOrdersJson};

document.addEventListener('DOMContentLoaded', function() {
    BPT_Storage.init(seedProducts, seedCategories, seedCart, seedOrders);
    renderCatalog();
});

function renderCatalog() {
    const categories = BPT_Storage.getCategories();
    const products = BPT_Storage.getProducts();
    const container = document.getElementById('static-catalog-container');
    let html = '';

    const getOrder = (cat) => {
        const s = ((cat.slug || '') + ' ' + (cat.name || '')).toLowerCase();
        if (s.includes('north') || s.includes('bac') || s.includes('hà nội') || s.includes('ha noi')) return 1;
        if (s.includes('central') || s.includes('trung') || s.includes('đà nẵng') || s.includes('da nang')) return 2;
        if (s.includes('south') || s.includes('nam') || s.includes('hồ chí minh') || s.includes('hcm')) return 3;
        return 99;
    };

    const sortedCategories = [...categories].sort((a, b) => getOrder(a) - getOrder(b));

    sortedCategories.forEach(cat => {
        if (cat.slug === 'other-services' || cat.id == 4 || cat.id == 21) return;
        const catProducts = products.filter(p => p.category_id == cat.id);
        if (catProducts.length === 0) return;

        const s = ((cat.slug || '') + ' ' + (cat.name || '')).toLowerCase();
        const isCentral = s.includes('central') || s.includes('trung') || cat.id == 2 || cat.id == 19;

        if (isCentral) {
            html += `
            <div class="middle-banner-section">
                <img src="assets/images/quangbinh-banner.jpg" alt="Quang Binh Banner">
            </div>`;
        }

        let anchorAlias = '';
        if (s.includes('north') || s.includes('bac')) anchorAlias = 'northern-vietnam-tours';
        else if (s.includes('central') || s.includes('trung')) anchorAlias = 'central-vietnam-tours';
        else if (s.includes('south') || s.includes('nam')) anchorAlias = 'southern-vietnam-tours';

        html += `
        \${anchorAlias ? \`<div id="\${anchorAlias}" style="position:relative; top:-90px; visibility:hidden;"></div>\` : ''}
        <div id="\${cat.slug}" style="margin-bottom: 4.5rem; scroll-margin-top: 100px;">
            <div class="cat-header-wrap">
                <span class="category-sub-badge"><i class="fa-solid fa-sparkles"></i> <span data-i18n="cat_badge_selected">Hand-Picked Itineraries</span></span>
                <h2 class="category-section-title">\${cat.name}</h2>
                <p class="category-section-desc" data-i18n="cat_desc_common">Experience luxury private journeys with modern vehicles and fully customizable itineraries.</p>
            </div>
            <div class="tours-grid">`;

        catProducts.forEach(p => {
            const imgSrc = BPT_Storage.fixImagePath(p.image);
            html += `
                <div class="tour-card">
                    <div class="tour-card-image">
                        <a href="products/product-\${p.id}.html">
                            <img src="\${imgSrc}" alt="\${p.title}" loading="lazy">
                        </a>
                        <span class="tour-badge-private"><i class="fa-solid fa-gem"></i> <span data-i18n="card_badge_private">Private Tour</span></span>
                        <span class="tour-badge-duration"><i class="fa-regular fa-clock"></i> \${p.duration || 'Full Day'}</span>
                        <button type="button" class="tour-wishlist-btn"><i class="fa-regular fa-heart"></i></button>
                    </div>
                    <div class="tour-card-body">
                        <div class="tour-card-meta">
                            <span class="tour-location-tag"><i class="fa-solid fa-location-dot"></i> \${cat.name}</span>
                            <div class="tour-rating-stars"><i class="fa-solid fa-star"></i> 4.9 <span style="color: #64748b; font-weight: normal;" data-i18n="card_reviews_suffix">(86 reviews)</span></div>
                        </div>
                        <h3 class="tour-card-title">
                            <a href="products/product-\${p.id}.html">\${p.title}</a>
                        </h3>
                        <div class="tour-card-perks">
                            <span class="tour-card-perk-item"><i class="fa-solid fa-check"></i> <span data-i18n="card_perk_car">Private car</span></span>
                            <span class="tour-card-perk-item"><i class="fa-solid fa-check"></i> <span data-i18n="card_perk_guide">Expert guide</span></span>
                            <span class="tour-card-perk-item"><i class="fa-solid fa-check"></i> <span data-i18n="card_perk_ticket">Entry tickets</span></span>
                        </div>
                        <div class="tour-card-footer">
                            <div class="tour-price-wrap">
                                <span class="tour-price-label" data-i18n="card_price_from">From</span>
                                <span class="tour-price-value">\${BPT_Storage.formatCurrency(p.price)}</span>
                            </div>
                            <a href="products/product-\${p.id}.html" class="tour-card-cta-btn">
                                <span data-i18n="card_btn_view">View Tour</span> <i class="fa-solid fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>`;
        });

        html += `</div></div>`;
    });

    container.innerHTML = html;
}

let currentSlide = 0;
const slides = document.querySelectorAll('.slide-item');
const bullets = document.querySelectorAll('.bullet');
let slideInterval = setInterval(nextSlide, 5500);

function showSlide(index) {
    if (slides.length === 0) return;
    slides.forEach(s => s.classList.remove('active'));
    bullets.forEach(b => b.classList.remove('active'));

    currentSlide = (index + slides.length) % slides.length;
    slides[currentSlide].classList.add('active');
    bullets[currentSlide].classList.add('active');
}

function setSlide(index) {
    showSlide(index);
    resetTimer();
}

function changeSlide(direction) {
    showSlide(currentSlide + direction);
    resetTimer();
}

function nextSlide() {
    showSlide(currentSlide + 1);
}

function resetTimer() {
    clearInterval(slideInterval);
    slideInterval = setInterval(nextSlide, 5500);
}
</script>
{$footer}
HTML;
    }

    private static function buildStaticProduct($product, $allProducts, $seedProductsJson, $seedCategoriesJson, $seedCartJson, $seedOrdersJson) {
        $pTitle = htmlspecialchars($product['title'], ENT_QUOTES, 'UTF-8');
        $header = self::getStaticHeader($pTitle . " - Buddy Private Tour", true);
        $footer = self::getStaticFooter(true);
        
        $gallery_images = json_decode($product['images'] ?? '[]', true) ?: [];
        $included = json_decode($product['included'] ?? '[]', true) ?: [];
        $excluded = json_decode($product['excluded'] ?? '[]', true) ?: [];
        
        if (empty($included) && !empty($product['included'])) {
            $included = array_filter(array_map('trim', explode("\n", $product['included'])));
        }
        if (empty($excluded) && !empty($product['excluded'])) {
            $excluded = array_filter(array_map('trim', explode("\n", $product['excluded'])));
        }

        $all_gallery = array_values(array_unique(array_filter(array_merge([$product['image']], $gallery_images))));
        while (count($all_gallery) < 5) {
            $all_gallery[] = $product['image'];
        }

        $pSummary = htmlspecialchars($product['summary'] ?? '', ENT_QUOTES, 'UTF-8');
        $pDesc = wpautop(wp_kses_post($product['description'] ?? ''));
        $pCategory = htmlspecialchars($product['category_name'], ENT_QUOTES, 'UTF-8');
        $pCategorySlug = htmlspecialchars($product['category_slug'] ?? 'tour', ENT_QUOTES, 'UTF-8');
        $pPrice = floatval($product['price']);
        $pId = intval($product['id']);
        $duration = !empty($product['duration']) ? htmlspecialchars($product['duration']) : 'Full Day';

        $mosaicHtml = '';
        for ($i = 1; $i <= 4; $i++) {
            $img = htmlspecialchars($all_gallery[$i] ?? $all_gallery[0], ENT_QUOTES, 'UTF-8');
            $mosaicHtml .= '<div class="mosaic-item" onclick="switchBptMosaicImage(\'' . $img . '\', this)"><img src="' . $img . '" alt="Gallery ' . $i . '"></div>';
        }

        $includedHtml = '';
        if (!empty($included)) {
            foreach ($included as $inc) {
                $includedHtml .= '<li><i class="fa-solid fa-check"></i> <span>' . htmlspecialchars($inc, ENT_QUOTES, 'UTF-8') . '</span></li>';
            }
        } else {
            $includedHtml = '<li><i class="fa-solid fa-check"></i> <span data-i18n="inc_item_1">Modern air-conditioned private vehicle throughout</span></li><li><i class="fa-solid fa-check"></i> <span data-i18n="inc_item_2">Professional English / Vietnamese speaking private guide</span></li><li><i class="fa-solid fa-check"></i> <span data-i18n="inc_item_3">All entrance fees and sightseeing tickets</span></li>';
        }

        $excludedHtml = '';
        if (!empty($excluded)) {
            foreach ($excluded as $exc) {
                $excludedHtml .= '<li><i class="fa-solid fa-xmark"></i> <span>' . htmlspecialchars($exc, ENT_QUOTES, 'UTF-8') . '</span></li>';
            }
        } else {
            $excludedHtml = '<li><i class="fa-solid fa-xmark"></i> <span data-i18n="exc_item_1">Personal expenses outside program (shopping, laundry)</span></li><li><i class="fa-solid fa-xmark"></i> <span data-i18n="exc_item_2">Gratuities & tips for driver and tour guide</span></li>';
        }

        return <<<HTML
{$header}
<div class="site-breadcrumb-wrap container">
    <nav class="site-breadcrumb-box">
        <a href="../index.html" class="crumb-link"><i class="fa-solid fa-house"></i> <span data-i18n="crumb_home">Home</span></a>
        <span class="crumb-separator">/</span>
        <a href="../tour.html" class="crumb-link"><span data-i18n="crumb_tour">Tours</span></a>
        <span class="crumb-separator">/</span>
        <a href="../tour.html?category={$pCategorySlug}" class="crumb-link crumb-category">{$pCategory}</a>
        <span class="crumb-separator">/</span>
        <span class="crumb-current">{$pTitle}</span>
    </nav>
</div>

<div class="section-padding" style="padding-top: 1.5rem; padding-bottom: 5rem; background: #ffffff;">
    <div class="container">
        <div class="tour-detail-header-wrap">
            <h1 class="tour-detail-main-title">{$pTitle}</h1>
            <div class="tour-detail-sub-meta">
                <span class="tour-detail-badge-pill"><i class="fa-solid fa-gem"></i> <span data-i18n="detail_badge_private">Private Tour</span></span>
                <span class="tour-detail-rating"><i class="fa-solid fa-star"></i> 4.9 <span style="color: #64748b; font-weight: normal;" data-i18n="detail_rating_reviews">(86 international traveler reviews)</span></span>
                <span class="tour-detail-loc"><i class="fa-solid fa-location-dot"></i> {$pCategory}</span>
            </div>
        </div>

        <!-- 1. MOSAIC IMAGE GALLERY -->
        <div class="tour-mosaic-gallery">
            <div class="mosaic-item mosaic-main" onclick="switchBptMosaicImage('{$all_gallery[0]}', this)">
                <img id="bpt-main-img" src="{$all_gallery[0]}" alt="{$pTitle}">
            </div>
            {$mosaicHtml}
            <button type="button" class="mosaic-view-all-btn" onclick="alert('Viewing all gallery photos!')">
                <i class="fa-solid fa-grip-vertical"></i> <span data-i18n="gallery_btn_view_all">View all photos</span> (5)
            </button>
        </div>

        <!-- 2. MAIN 2-COLUMN LAYOUT -->
        <div class="bpt-tour-detail-grid">
            <div class="tour-main-content-col">
                <div class="tour-quick-info-bar">
                    <div class="quick-info-item">
                        <div class="quick-info-icon"><i class="fa-solid fa-car-side"></i></div>
                        <div class="quick-info-text">
                            <span class="quick-info-label" data-i18n="info_vehicle_lbl">Transportation</span>
                            <span class="quick-info-val" data-i18n="info_vehicle_val">Modern Private Car</span>
                        </div>
                    </div>
                    <div class="quick-info-item">
                        <div class="quick-info-icon"><i class="fa-solid fa-language"></i></div>
                        <div class="quick-info-text">
                            <span class="quick-info-label" data-i18n="info_guide_lbl">Tour Guide</span>
                            <span class="quick-info-val" data-i18n="info_guide_val">Private English/Vietnamese</span>
                        </div>
                    </div>
                    <div class="quick-info-item">
                        <div class="quick-info-icon"><i class="fa-solid fa-user-shield"></i></div>
                        <div class="quick-info-text">
                            <span class="quick-info-label" data-i18n="info_type_lbl">Tour Type</span>
                            <span class="quick-info-val" data-i18n="info_type_val">100% Private Tour</span>
                        </div>
                    </div>
                    <div class="quick-info-item">
                        <div class="quick-info-icon"><i class="fa-solid fa-hotel"></i></div>
                        <div class="quick-info-text">
                            <span class="quick-info-label" data-i18n="info_pickup_lbl">Pickup & Dropoff</span>
                            <span class="quick-info-val" data-i18n="info_pickup_val">Complimentary Hotel Pickup</span>
                        </div>
                    </div>
                    <div class="quick-info-item">
                        <div class="quick-info-icon"><i class="fa-solid fa-clock"></i></div>
                        <div class="quick-info-text">
                            <span class="quick-info-label" data-i18n="info_duration_lbl">Duration</span>
                            <span class="quick-info-val">{$duration}</span>
                        </div>
                    </div>
                </div>

                <div class="tour-highlights-card">
                    <h3 class="tour-highlights-title"><i class="fa-solid fa-star"></i> <span data-i18n="sec_highlights_title">TOUR HIGHLIGHTS</span></h3>
                    <ul class="tour-highlights-list">
                        <li class="tour-highlight-item"><i class="fa-solid fa-circle-check"></i> <span>Enjoy a 100% personalized private journey fully customized to your preferences.</span></li>
                        <li class="tour-highlight-item"><i class="fa-solid fa-circle-check"></i> <span>Premium modern air-conditioned private vehicle with direct hotel/airport pickup.</span></li>
                        <li class="tour-highlight-item"><i class="fa-solid fa-circle-check"></i> <span>Enthusiastic, licensed local tour guide speaking fluent English & Vietnamese.</span></li>
                        <li class="tour-highlight-item"><i class="fa-solid fa-circle-check"></i> <span>Taste handpicked authentic regional specialties at verified dining venues.</span></li>
                    </ul>
                </div>

                <div class="tour-timeline-section">
                    <h3 class="tour-section-heading"><i class="fa-solid fa-route" style="color: #0284c7;"></i> <span data-i18n="sec_itinerary_title">DETAILED ITINERARY</span></h3>
                    <div class="tour-timeline-container">
                        <div class="timeline-day-card active">
                            <div class="timeline-node">1</div>
                            <div class="timeline-day-header" onclick="toggleTimelineDay(this)">
                                <span class="timeline-day-title" data-i18n="timeline_day_1_title">Morning: Hotel Pickup & Signature Sightseeing</span>
                                <span class="timeline-day-toggle"><i class="fa-solid fa-chevron-down"></i></span>
                            </div>
                            <div class="timeline-day-body">
                                <p data-i18n="timeline_day_1_body">Private car and guide pick you up promptly at your hotel or meeting point. Begin your journey visiting world-famous landmarks while enjoying insightful cultural stories from your dedicated local guide.</p>
                            </div>
                        </div>
                        <div class="timeline-day-card active">
                            <div class="timeline-node">2</div>
                            <div class="timeline-day-header" onclick="toggleTimelineDay(this)">
                                <span class="timeline-day-title" data-i18n="timeline_day_2_title">Afternoon: Authentic Local Cuisine & Cultural Discovery</span>
                                <span class="timeline-day-toggle"><i class="fa-solid fa-chevron-down"></i></span>
                            </div>
                            <div class="timeline-day-body">
                                <p data-i18n="timeline_day_2_body">Savor a delicious traditional lunch at handpicked local restaurants. Continue exploring scenic attractions, capturing photos, and shopping for specialties before a safe private drop-off at your hotel.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tour-inclusions-grid">
                    <div class="inclusions-card included">
                        <h4 class="inclusions-card-title"><i class="fa-solid fa-circle-check"></i> <span data-i18n="sec_included_title">INCLUDED IN TOUR</span></h4>
                        <ul class="inclusions-list">{$includedHtml}</ul>
                    </div>
                    <div class="inclusions-card excluded">
                        <h4 class="inclusions-card-title"><i class="fa-solid fa-circle-xmark"></i> <span data-i18n="sec_excluded_title">EXCLUDED FROM TOUR</span></h4>
                        <ul class="inclusions-list">{$excludedHtml}</ul>
                    </div>
                </div>

                <div>
                    <h3 class="tour-section-heading"><i class="fa-solid fa-circle-info" style="color: #0284c7;"></i> <span data-i18n="sec_overview_title">ABOUT THIS TRIP</span></h3>
                    <div class="bpt-tour-rich-description">
                        {$pDesc}
                    </div>
                </div>
            </div>

            <!-- RIGHT STICKY BOOKING CARD -->
            <div class="tour-sidebar-col">
                <div class="sticky-booking-card">
                    <div class="booking-card-header">
                        <div class="booking-price-wrap">
                            <span class="booking-price-label" data-i18n="booking_price_lbl">Special private tour price</span>
                            <div class="booking-price-value">
                                <span id="static-price-val">...</span>
                                <span class="booking-price-unit" data-i18n="booking_price_unit">/ guest</span>
                            </div>
                        </div>
                        <span style="background: #ffedd5; color: #c2410c; font-size: 0.78rem; font-weight: 800; padding: 0.25rem 0.65rem; border-radius: 9999px;" data-i18n="booking_discount_badge">Save 15%</span>
                    </div>

                    <div class="booking-form-group">
                        <label class="booking-input-label" for="detail-tour-date"><i class="fa-regular fa-calendar"></i> <span data-i18n="booking_date_lbl">Select Departure Date:</span></label>
                        <input type="date" id="detail-tour-date" class="booking-input-control" value="2026-10-04">
                    </div>

                    <div class="booking-form-group">
                        <label class="booking-input-label"><i class="fa-solid fa-user-group"></i> <span data-i18n="booking_guests_lbl">Number of Guests:</span></label>
                        <div class="booking-guest-stepper">
                            <button type="button" class="guest-stepper-btn" onclick="updateTourDetailGuests(-1, {$pPrice})">−</button>
                            <input type="number" id="detail-guest-count" class="guest-stepper-input" value="2" min="1" max="50" readonly>
                            <button type="button" class="guest-stepper-btn" onclick="updateTourDetailGuests(1, {$pPrice})">+</button>
                        </div>
                    </div>

                    <div class="booking-calc-summary">
                        <div class="booking-calc-row">
                            <span><span data-i18n="booking_calc_label">Tour price x</span> <strong id="calc-guest-display">2</strong> <span data-i18n="booking_calc_guest_unit">guests:</span></span>
                            <span class="val" id="calc-subtotal-display">...</span>
                        </div>
                        <div class="booking-calc-row">
                            <span data-i18n="booking_calc_service_lbl">Private guide & service fees:</span>
                            <span style="color: #15803d; font-weight: 700;" data-i18n="booking_calc_free">Free</span>
                        </div>
                        <div class="booking-calc-total">
                            <span data-i18n="booking_calc_subtotal_lbl">Subtotal:</span>
                            <span class="val" id="calc-grand-total">...</span>
                        </div>
                    </div>

                    <button type="button" onclick="addToCartLocalDetail({$pId})" class="booking-cta-add-cart">
                        <i class="fa-solid fa-cart-shopping"></i> <span data-i18n="btn_book_now">BOOK THIS TOUR</span>
                    </button>

                    <div class="booking-trust-guarantees">
                        <div class="booking-trust-item"><i class="fa-solid fa-circle-check"></i> <span data-i18n="guarantee_free_cancel">Free cancellation up to 24 hours in advance</span></div>
                        <div class="booking-trust-item"><i class="fa-solid fa-bolt"></i> <span data-i18n="guarantee_instant_confirm">Instant confirmation & VietQR E-voucher</span></div>
                        <div class="booking-trust-item"><i class="fa-solid fa-shield-halved"></i> <span data-i18n="guarantee_quality">Guaranteed 5-star service excellence</span></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const seedProducts = {$seedProductsJson};
const seedCategories = {$seedCategoriesJson};
const seedCart = {$seedCartJson};
const seedOrders = {$seedOrdersJson};
const tourPrice = {$pPrice};

document.addEventListener('DOMContentLoaded', function() {
    BPT_Storage.init(seedProducts, seedCategories, seedCart, seedOrders);
    document.getElementById('static-price-val').textContent = BPT_Storage.formatCurrency(tourPrice);
    updateTourDetailGuests(0, tourPrice);
});

function addToCartLocalDetail(id) {
    const qty = parseInt(document.getElementById('detail-guest-count').value) || 1;
    BPT_Storage.addToCart(id, qty);
    window.location.href = '../cart.html';
}
</script>
{$footer}
HTML;
    }

    private static function buildStaticCart($seedProductsJson, $seedCategoriesJson, $seedCartJson, $seedOrdersJson) {
        $header = self::getStaticHeader("Shopping Cart - Buddy Private Tour");
        $footer = self::getStaticFooter();

        return <<<HTML
{$header}
<div class="cart-page-wrapper">
    <div class="site-breadcrumb-wrap container">
        <nav class="site-breadcrumb-box">
            <a href="index.html" class="crumb-link"><i class="fa-solid fa-house"></i> <span data-i18n="crumb_home">Home</span></a>
            <span class="crumb-separator">/</span>
            <span class="crumb-current" data-i18n="nav_cart">Shopping Cart</span>
        </nav>
    </div>

    <div class="container">
        <div class="cart-title-section">
            <h1 class="cart-page-heading" data-i18n="cart_page_heading">YOUR SHOPPING CART</h1>
            <p class="cart-page-desc" data-i18n="cart_page_desc">Review your selected private tour packages before proceeding to checkout and generating your VietQR electronic voucher.</p>
        </div>

        <div id="cart-root"></div>
    </div>
</div>

<script>
const seedProducts = {$seedProductsJson};
const seedCategories = {$seedCategoriesJson};
const seedCart = {$seedCartJson};
const seedOrders = {$seedOrdersJson};

document.addEventListener('DOMContentLoaded', function() {
    BPT_Storage.init(seedProducts, seedCategories, seedCart, seedOrders);
    renderCart();
});

function renderCart() {
    const cart = BPT_Storage.getCart();
    const categories = BPT_Storage.getCategories();
    const root = document.getElementById('cart-root');

    if (cart.length === 0) {
        root.innerHTML = `
            <div class="cart-empty-wrapper">
                <div class="cart-empty-icon"><i class="fa-solid fa-bag-shopping"></i></div>
                <h2 class="cart-empty-title" data-i18n="cart_empty_title">Your cart is currently empty!</h2>
                <p class="cart-empty-desc" data-i18n="cart_empty_desc">Explore our exclusive private tours across North, Central, and South Vietnam.</p>
                <a href="tour.html" class="btn-cart-empty-cta"><i class="fa-solid fa-compass"></i> <span data-i18n="cart_btn_explore">Explore Tours Now</span></a>
            </div>`;
        return;
    }

    let rowsHtml = '';
    let totalQty = 0;
    cart.forEach(item => {
        totalQty += parseInt(item.quantity);
        const imgSrc = BPT_Storage.fixImagePath(item.image);
        let catName = item.category_name || '';
        if (!catName && item.category_id) {
            const c = categories.find(cat => cat.id == item.category_id);
            if (c) catName = c.name;
        }

        rowsHtml += `
            <tr>
                <td class="cart-col-details">
                    <div class="cart-product-cell">
                        <a href="products/product-\${item.id}.html" class="cart-item-thumb">
                            <img src="\${imgSrc}" alt="\${item.title}">
                        </a>
                        <div class="cart-item-info">
                            \${catName ? `<span class="cart-item-cat">\${catName}</span>` : ''}
                            <a href="products/product-\${item.id}.html" class="cart-item-name">\${item.title}</a>
                        </div>
                    </div>
                </td>
                <td style="text-align: center;">
                    <span class="cart-unit-price">\${BPT_Storage.formatCurrency(item.price)}</span>
                </td>
                <td style="text-align: center;">
                    <div class="cart-stepper">
                        <button type="button" class="cart-stepper-btn" onclick="BPT_Storage.updateCartQuantity(\${item.id}, \${item.quantity - 1}); renderCart();">−</button>
                        <input type="number" id="qty-input-\${item.id}" value="\${item.quantity}" min="1" max="50" class="cart-stepper-input" onchange="BPT_Storage.updateCartQuantity(\${item.id}, this.value); renderCart();">
                        <button type="button" class="cart-stepper-btn" onclick="BPT_Storage.updateCartQuantity(\${item.id}, \${item.quantity + 1}); renderCart();">+</button>
                    </div>
                </td>
                <td style="text-align: center;">
                    <button onclick="if(confirm('Are you sure you want to remove this tour from cart?')){BPT_Storage.removeFromCart(\${item.id}); renderCart();}" class="cart-delete-action" title="Remove">
                        <i class="fa-solid fa-trash-can"></i>
                    </button>
                </td>
            </tr>`;
    });

    root.innerHTML = `
        <div class="cart-layout-grid">
            <div class="cart-items-card">
                <div class="cart-card-header">
                    <h2><i class="fa-solid fa-clipboard-list"></i> <span data-i18n="cart_list_heading">Selected Tour Packages</span></h2>
                    <span class="cart-count-badge">\${totalQty} <span data-i18n="cart_badge_tickets">tickets selected</span></span>
                </div>

                <table class="cart-modern-table">
                    <thead>
                        <tr>
                            <th style="width: 48%;" data-i18n="cart_th_info">Tour Details</th>
                            <th style="width: 22%; text-align: center;" data-i18n="cart_th_price">Unit Price</th>
                            <th style="width: 20%; text-align: center;" data-i18n="cart_th_qty">Guests</th>
                            <th style="width: 10%; text-align: center;"></th>
                        </tr>
                    </thead>
                    <tbody>\${rowsHtml}</tbody>
                </table>

                <div class="cart-card-footer">
                    <a href="tour.html" class="cart-continue-link"><i class="fa-solid fa-arrow-left"></i> <span data-i18n="cart_btn_continue">Choose More Tours</span></a>
                    <span style="font-size: 0.85rem; color: #64748b;"><i class="fa-solid fa-circle-check" style="color: #10b981;"></i> Includes modern private car and dedicated tour guide.</span>
                </div>
            </div>

            <div class="cart-summary-sidebar">
                <div class="cart-summary-box">
                    <h3 class="cart-summary-title"><i class="fa-solid fa-receipt"></i> <span data-i18n="cart_summary_heading">Order Summary</span></h3>
                    <div class="cart-summary-row">
                        <span>Total guests / tickets:</span>
                        <span class="val">\${totalQty} guests</span>
                    </div>
                    <div class="cart-summary-row">
                        <span data-i18n="cart_summary_subtotal">Tours Subtotal:</span>
                        <span class="val">\${BPT_Storage.formatCurrency(BPT_Storage.getCartTotal())}</span>
                    </div>
                    <div class="cart-summary-row">
                        <span data-i18n="cart_summary_guide">Private Driver & Guide:</span>
                        <span class="free-tag" data-i18n="cart_summary_free">Included</span>
                    </div>
                    <div class="cart-summary-divider"></div>
                    <div class="cart-grand-total-row">
                        <span class="label" data-i18n="cart_summary_total">Total Payment:</span>
                        <span class="amount">\${BPT_Storage.formatCurrency(BPT_Storage.getCartTotal())}</span>
                    </div>
                    <a href="checkout.html" class="btn-cart-checkout"><span data-i18n="cart_btn_checkout">PROCEED TO SECURE CHECKOUT</span> <i class="fa-solid fa-arrow-right"></i></a>
                </div>
            </div>
        </div>`;
}
</script>
{$footer}
HTML;
    }

    private static function buildStaticCheckout($seedProductsJson, $seedCategoriesJson, $seedCartJson, $seedOrdersJson) {
        $header = self::getStaticHeader("Checkout & VietQR - Buddy Private Tour");
        $footer = self::getStaticFooter();

        return <<<HTML
{$header}
<div class="site-breadcrumb-wrap container">
    <nav class="site-breadcrumb-box" id="checkout-breadcrumbs">
        <a href="index.html" class="crumb-link"><i class="fa-solid fa-house"></i> <span data-i18n="crumb_home">Home</span></a>
        <span class="crumb-separator">/</span>
        <span class="crumb-current" data-i18n="crumb_order_received">Ticket Confirmation & VietQR</span>
    </nav>
</div>

<div class="section-padding checkout-section">
    <div class="container">
        <div class="woocommerce checkout-native-wrapper" id="checkout-root"></div>
    </div>
</div>

<script>
const seedProducts = {$seedProductsJson};
const seedCategories = {$seedCategoriesJson};
const seedCart = {$seedCartJson};
const seedOrders = {$seedOrdersJson};

document.addEventListener('DOMContentLoaded', function() {
    BPT_Storage.init(seedProducts, seedCategories, seedCart, seedOrders);
    renderCheckout();
});

function renderCheckout() {
    const cart = BPT_Storage.getCart();
    const root = document.getElementById('checkout-root');

    if (cart.length === 0) {
        root.innerHTML = `
            <div class="cart-empty-wrapper">
                <div class="cart-empty-icon"><i class="fa-solid fa-bag-shopping"></i></div>
                <h2 class="cart-empty-title" data-i18n="cart_empty_title">Your cart is currently empty!</h2>
                <a href="tour.html" class="btn-cart-empty-cta" data-i18n="cart_btn_explore">Explore Tours Now</a>
            </div>`;
        return;
    }

    const cartTotal = BPT_Storage.getCartTotal();
    let itemsRows = '';
    cart.forEach(item => {
        itemsRows += `
            <tr>
                <td style="padding: 0.75rem 0; color: #1e293b;">\${item.title} &times; <strong>\${item.quantity}</strong></td>
                <td style="padding: 0.75rem 0; text-align: right; font-weight: 700;">\${BPT_Storage.formatCurrency(item.price * item.quantity)}</td>
            </tr>`;
    });

    root.innerHTML = `
        <form class="woocommerce-checkout" id="static-checkout-form" onsubmit="handleStaticCheckout(event)">
            <div>
                <div class="woocommerce-billing-fields">
                    <h3>Customer Information</h3>
                    <div class="woocommerce-billing-fields__field-wrapper">
                        <div class="form-row">
                            <label for="billing_name" data-i18n="form_fullname_lbl">FULL NAME <span style="color:red;">*</span></label>
                            <input type="text" class="input-text" id="billing_name" required placeholder="e.g. John Smith" data-i18n-placeholder="form_fullname_ph">
                        </div>
                        <div class="form-row">
                            <label for="billing_phone" data-i18n="form_phone_lbl">PHONE / WHATSAPP <span style="color:red;">*</span></label>
                            <input type="tel" class="input-text" id="billing_phone" required placeholder="e.g. +84 567 599 998" data-i18n-placeholder="form_phone_ph">
                        </div>
                        <div class="form-row">
                            <label for="billing_email" data-i18n="form_email_lbl">EMAIL ADDRESS <span style="color:red;">*</span></label>
                            <input type="email" class="input-text" id="billing_email" required placeholder="e.g. john@example.com" data-i18n-placeholder="form_email_ph">
                        </div>
                        <div class="form-row">
                            <label for="billing_notes" data-i18n="form_notes_lbl">SPECIAL REQUESTS / DATES</label>
                            <textarea class="input-text" id="billing_notes" rows="3" placeholder="Hotel pickup address or flight arrival details..." data-i18n-placeholder="form_notes_ph"></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="woocommerce-checkout-review-order">
                <h3 id="order_review_heading">Your Tour Order</h3>
                <table class="shop_table woocommerce-checkout-review-order-table">
                    <tbody>\${itemsRows}</tbody>
                    <tfoot>
                        <tr class="order-total">
                            <th>Total:</th>
                            <td>\${BPT_Storage.formatCurrency(cartTotal)}</td>
                        </tr>
                    </tfoot>
                </table>

                <div style="background: #ffffff; padding: 1.25rem; border-radius: 12px; border: 1px solid #e2e8f0; margin-bottom: 1.25rem;">
                    <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem; font-weight: 800; color: #0284c7;">
                        <i class="fa-solid fa-qrcode"></i> Instant VietQR Bank Transfer
                    </div>
                    <p style="font-size: 0.85rem; color: #64748b; margin: 0;">Napas247 instant QR code will be generated immediately after clicking place order.</p>
                </div>

                <button type="submit" class="btn-checkout-place-order">
                    <i class="fa-solid fa-lock"></i> CONFIRM & GENERATE VIETQR
                </button>
            </div>
        </form>`;
}

function handleStaticCheckout(e) {
    e.preventDefault();
    const name = document.getElementById('billing_name').value.trim();
    const phone = document.getElementById('billing_phone').value.trim();
    const email = document.getElementById('billing_email').value.trim();
    const notes = document.getElementById('billing_notes').value.trim();

    const cartTotal = BPT_Storage.getCartTotal();
    const cartItems = BPT_Storage.getCart();
    const res = BPT_Storage.placeOrder({ name, phone, email, notes });

    if (res.success) {
        const qrUrl = 'https://img.vietqr.io/image/970436-9782794280-compact.jpg?amount=' + cartTotal + '&addInfo=' + encodeURIComponent(res.order_code) + '&accountName=' + encodeURIComponent('NGUYEN VAN KIEN');
        
        let orderItemsRows = '';
        cartItems.forEach(it => {
            orderItemsRows += `
                <div style="display: flex; justify-content: space-between; padding: 0.5rem 0; border-bottom: 1px solid #f1f5f9;">
                    <span>\${it.title} &times; <strong>\${it.quantity}</strong></span>
                    <span style="font-weight: 700;">\${BPT_Storage.formatCurrency(it.price * it.quantity)}</span>
                </div>`;
        });

        document.getElementById('checkout-root').innerHTML = `
            <div class="e-ticket-voucher-card">
                <div class="e-ticket-badge-header">
                    <div class="e-ticket-logo-brand"><i class="fa-solid fa-ticket"></i> E-TICKET VOUCHER</div>
                    <div class="e-ticket-status-pill"><i class="fa-solid fa-circle-check"></i> ORDER CREATED SUCCESSFULLY</div>
                </div>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin-bottom: 2rem; background: #f8fafc; padding: 1.5rem; border-radius: 12px;">
                    <div><span style="font-size: 0.78rem; color: #64748b; text-transform: uppercase;">Order Code:</span><br><strong style="font-size: 1.15rem; color: #0284c7;">\${res.order_code}</strong></div>
                    <div><span style="font-size: 0.78rem; color: #64748b; text-transform: uppercase;">Customer:</span><br><strong style="font-size: 1.05rem;">\${name}</strong></div>
                    <div><span style="font-size: 0.78rem; color: #64748b; text-transform: uppercase;">Phone:</span><br><strong style="font-size: 1.05rem;">\${phone}</strong></div>
                    <div><span style="font-size: 0.78rem; color: #64748b; text-transform: uppercase;">Total Amount:</span><br><strong style="font-size: 1.25rem; color: #f97316;">\${BPT_Storage.formatCurrency(cartTotal)}</strong></div>
                </div>

                <!-- VietQR Hero Scan Box -->
                <div class="vietqr-scan-hero-box">
                    <h3 class="vietqr-scan-title">Scan VietQR Code With Your Banking App</h3>
                    <p class="vietqr-scan-subtitle">Open any banking or e-wallet application and scan the code below for instant zero-fee transfer.</p>
                    
                    <div class="vietqr-image-wrapper">
                        <img src="\${qrUrl}" alt="VietQR Payment Code">
                    </div>

                    <!-- Bank Transfer Details Table with 1-Click Copy Buttons -->
                    <div class="bank-transfer-details-box">
                        <div class="bank-detail-row">
                            <span class="bank-detail-label">Beneficiary Bank:</span>
                            <span class="bank-detail-val">Vietcombank (Joint Stock Commercial Bank for Foreign Trade of Vietnam)</span>
                        </div>
                        <div class="bank-detail-row">
                            <span class="bank-detail-label">Account Number:</span>
                            <span class="bank-detail-val">
                                <span>9782794280</span>
                                <button type="button" class="bank-copy-btn" onclick="copyToClipboard('9782794280', 'Account number copied!')"><i class="fa-regular fa-copy"></i> Copy</button>
                            </span>
                        </div>
                        <div class="bank-detail-row">
                            <span class="bank-detail-label">Account Holder:</span>
                            <span class="bank-detail-val">NGUYEN VAN KIEN</span>
                        </div>
                        <div class="bank-detail-row">
                            <span class="bank-detail-label">Amount:</span>
                            <span class="bank-detail-val amount-highlight">
                                <span>\${BPT_Storage.formatCurrency(cartTotal)}</span>
                                <button type="button" class="bank-copy-btn" onclick="copyToClipboard('\${cartTotal}', 'Amount copied!')"><i class="fa-regular fa-copy"></i> Copy</button>
                            </span>
                        </div>
                        <div class="bank-detail-row">
                            <span class="bank-detail-label">Transfer Description:</span>
                            <span class="bank-detail-val">
                                <span style="color: #0284c7;">\${res.order_code}</span>
                                <button type="button" class="bank-copy-btn" onclick="copyToClipboard('\${res.order_code}', 'Transfer description copied!')"><i class="fa-regular fa-copy"></i> Copy</button>
                            </span>
                        </div>
                    </div>
                </div>

                <div style="text-align: center; margin-top: 2rem;">
                    <a href="index.html" class="tour-card-cta-btn" style="display: inline-flex; padding: 0.85rem 2rem;">
                        <i class="fa-solid fa-house"></i> <span data-i18n="crumb_home">Back to Home</span>
                    </a>
                </div>
            </div>`;
    }
}
</script>
{$footer}
HTML;
    }
}

BPT_ExportController::init();

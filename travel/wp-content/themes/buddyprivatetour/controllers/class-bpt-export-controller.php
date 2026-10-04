<?php
/**
 * Export Controller for Static Site Generation and ZIP Download
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
                    '<a href="' . esc_url(wp_login_url(home_url('/?controller=export'))) . '" style="display:inline-block; margin-top:1rem; padding:0.6rem 1.5rem; background:#0b5ed7; color:#fff; text-decoration:none; border-radius:4px; font-weight:bold;">Đăng nhập Admin</a>' .
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
                    'duration' => $item['duration'],
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
            <div style="background: linear-gradient(135deg, #0b5ed7, #0c4a85); color: #fff; padding: 4rem 0; text-align: center;">
                <div class="container">
                    <span style="background: rgba(255,255,255,0.2); padding: 0.35rem 1rem; border-radius: 999px; font-weight: 700; font-size: 0.85rem;">EXPORTER TĨNH & ĐÓNG GÓI ZIP THÀNH CÔNG</span>
                    <h1 style="font-size: 2.75rem; font-weight: 800; margin-top: 1rem;">🚀 Đã Xuất Tất Cả Trang Tĩnh & Nén File ZIP</h1>
                    <p style="color: #e2e8f0; max-width: 650px; margin: 0.75rem auto 0; font-size: 1.1rem;">Toàn bộ sản phẩm, chi tiết từng tour, giỏ hàng & thanh toán đã được nén vào 1 file ZIP duy nhất!</p>
                </div>
            </div>

            <div class="section-padding">
                <div class="container">
                    <div style="max-width: 860px; margin: 0 auto; background: #fff; padding: 2.5rem; border-radius: var(--radius-lg); border: 1px solid var(--border-color); box-shadow: var(--shadow-lg);">
                        
                        <!-- Download Box -->
                        <div style="background: linear-gradient(135deg, #ecfdf5, #d1fae5); border: 2px solid var(--primary-color); border-radius: var(--radius-md); padding: 2rem; text-align: center; margin-bottom: 2rem;">
                            <i class="fa-solid fa-file-zipper" style="font-size: 3.5rem; color: var(--primary-color); margin-bottom: 1rem; display: block;"></i>
                            <h3 style="font-size: 1.6rem; font-weight: 800; color: var(--text-dark); margin-bottom: 0.5rem;">Tải Về Toàn Bộ Trang Web Tĩnh (.ZIP)</h3>
                            <p style="color: var(--text-muted); margin-bottom: 1.5rem;">Dung lượng: <strong><?php echo $zipSizeMb; ?> MB</strong> | Bao gồm: <strong><?php echo $productCount; ?> trang chi tiết sản phẩm</strong>, Trang chủ, Giới thiệu, Giỏ hàng & Thanh toán (dùng LocalStorage).</p>

                            <a href="index.php?controller=export&action=downloadZip" class="btn-primary" style="padding: 1rem 2rem; font-size: 1.2rem; background: var(--primary-color);">
                                <i class="fa-solid fa-download"></i> ⬇️ Tải Về File ZIP (buddyprivatetour-static-site.zip)
                            </a>
                        </div>

                        <!-- Important Instruction Banner -->
                        <div style="background: #fffbeb; border: 2px solid #f59e0b; border-radius: var(--radius-md); padding: 1.5rem; margin-bottom: 2rem;">
                            <h4 style="font-size: 1.15rem; font-weight: 800; color: #b45309; margin-bottom: 0.75rem; display: flex; align-items: center; gap: 0.5rem;">
                                <i class="fa-solid fa-triangle-exclamation" style="font-size: 1.3rem;"></i> ⚠️ QUAN TRỌNG: HƯỚNG DẪN MỞ WEB TĨNH KHÔNG BỊ MẤT CSS
                            </h4>
                            <p style="color: #92400e; font-size: 0.95rem; line-height: 1.6; margin-bottom: 0.75rem;">
                                <strong>Bước 1:</strong> Sau khi tải file <code>buddyprivatetour-static-site.zip</code> về máy, bạn <strong>PHẢI GIẢI NÉN (Extract All / Giải nén)</strong> ra một thư mục riêng biệt.
                            </p>
                            <p style="color: #92400e; font-size: 0.95rem; line-height: 1.6; margin-bottom: 0.75rem;">
                                <strong>Bước 2:</strong> Mở thư mục vừa giải nén và nhấp đúp chuột vào file <strong><code>index.html</code></strong> để duyệt web.
                            </p>
                            <p style="color: #b91c1c; font-size: 0.9rem; line-height: 1.5; margin: 0; background: rgba(239, 68, 68, 0.1); padding: 0.65rem 0.85rem; border-radius: 4px;">
                                ❌ <em>Lưu ý: Không mở trực tiếp file HTML từ bên trong file ZIP khi chưa giải nén, vì Windows sẽ chỉ mở 1 file tạm trong thư mục Temp mà không tải thư mục chứa CSS, dẫn tới bị mất giao diện và lỗi chuyển trang (ERR_FILE_NOT_FOUND).</em>
                            </p>
                        </div>

                        <h3 style="font-size: 1.3rem; font-weight: 800; margin-bottom: 1rem; color: var(--text-dark); display: flex; align-items: center; gap: 0.5rem;">
                            <i class="fa-solid fa-layer-group" style="color: var(--primary-color);"></i> Danh Sách Thành Phần Trong File ZIP Đã Đóng Gói
                        </h3>

                        <div style="background: #f8fafc; border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 1.25rem; margin-bottom: 2rem;">
                            <ul style="list-style: none;">
                                <li style="margin-bottom: 0.75rem; display: flex; align-items: center; justify-content: space-between;">
                                    <span><i class="fa-solid fa-file-code" style="color: #0284c7;"></i> <strong>index.html</strong> (Trang chủ tĩnh)</span>
                                    <span style="font-size: 0.85rem; color: #16a34a; font-weight: 700;">Đã đóng gói</span>
                                </li>
                                <li style="margin-bottom: 0.75rem; display: flex; align-items: center; justify-content: space-between;">
                                    <span><i class="fa-solid fa-folder-open" style="color: #f59e0b;"></i> <strong>products/</strong> (<?php echo $productCount; ?> file HTML chi tiết tour)</span>
                                    <span style="font-size: 0.85rem; color: #16a34a; font-weight: 700;">Đã đóng gói</span>
                                </li>
                                <li style="margin-bottom: 0.75rem; display: flex; align-items: center; justify-content: space-between;">
                                    <span><i class="fa-solid fa-file-code" style="color: #0284c7;"></i> <strong>cart.html & checkout.html</strong> (Giỏ hàng & VietQR)</span>
                                    <span style="font-size: 0.85rem; color: #16a34a; font-weight: 700;">Đã đóng gói</span>
                                </li>
                                <li style="margin-bottom: 0.75rem; display: flex; align-items: center; justify-content: space-between;">
                                    <span><i class="fa-solid fa-file-code" style="color: #0284c7;"></i> <strong>about-us.html, tour.html, other-services.html, videos.html, contact.html</strong></span>
                                    <span style="font-size: 0.85rem; color: #16a34a; font-weight: 700;">Đã đóng gói</span>
                                </li>
                                <li style="display: flex; align-items: center; justify-content: space-between;">
                                    <span><i class="fa-solid fa-folder-tree" style="color: #0284c7;"></i> <strong>assets/</strong> (CSS, JavaScript, Images, LocalStorage Engine)</span>
                                    <span style="font-size: 0.85rem; color: #16a34a; font-weight: 700;">Đã đóng gói</span>
                                </li>
                            </ul>
                        </div>

                        <div style="display: flex; gap: 1rem; justify-content: center;">
                            <a href="index.php" class="btn-primary"><i class="fa-solid fa-arrow-left"></i> Quay Về Trang Chủ WordPress</a>
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

        // 1. Try PHP ZipArchive
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

        // 2. Try WordPress built-in PclZip
        if (file_exists(ABSPATH . 'wp-admin/includes/class-pclzip.php')) {
            require_once ABSPATH . 'wp-admin/includes/class-pclzip.php';
            $pclZip = new PclZip($outZipPath);
            $v_list = $pclZip->create($sourceDir, PCLZIP_OPT_REMOVE_PATH, $sourceDir);
            if ($v_list != 0 && file_exists($outZipPath) && filesize($outZipPath) > 0) {
                return true;
            }
        }

        // 3. Try Windows PowerShell Compress-Archive
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $cleanSource = str_replace('/', '\\', realpath($sourceDir)) . '\\*';
            $cleanDest = str_replace('/', '\\', $outZipPath);
            $cmd = 'powershell.exe -NoProfile -ExecutionPolicy Bypass -Command "Compress-Archive -Path \'' . $cleanSource . '\' -DestinationPath \'' . $cleanDest . '\' -Force"';
            @shell_exec($cmd);
            if (file_exists($outZipPath) && filesize($outZipPath) > 0) {
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
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Caveat:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="{$assetPrefix}assets/css/style.css">
    <link rel="stylesheet" href="{$assetPrefix}assets/css/tour-detail.css">
    <link rel="stylesheet" href="{$assetPrefix}assets/css/tour.css">
    <link rel="stylesheet" href="{$assetPrefix}assets/css/cart.css">
    <link rel="stylesheet" href="{$assetPrefix}assets/css/checkout.css">
    <link rel="stylesheet" href="{$assetPrefix}assets/css/other-services.css">
    <link rel="stylesheet" href="{$assetPrefix}assets/css/pages.css">
    <script src="{$assetPrefix}assets/js/app-localstorage.js"></script>
    <script src="{$assetPrefix}assets/js/main.js" defer></script>
</head>
<body>

<header class="site-header">
    <div class="header-main-wrap">
        <!-- Desktop Header Row -->
        <div class="header-top-row desktop-header-row">
            <a href="{$assetPrefix}index.html" class="brand-logo">
                <img src="{$assetPrefix}assets/images/logo.png" alt="BUDDY PRIVATE TOUR" class="site-logo-img">
            </a>
            <div class="header-nav-column">
                <nav class="main-navigation">
                    <ul class="nav-menu">
                        <li class="menu-item-home"><a href="{$assetPrefix}index.html">HOME</a></li>
                        <li><a href="{$assetPrefix}about-us.html">ABOUT US</a></li>
                        <li class="menu-item-has-children">
                            <a href="{$assetPrefix}tour.html">TOUR</a>
                            <ul class="sub-menu">
                                <li><a href="{$assetPrefix}tour.html?category=northern-vietnam-tours">HA NOI AND NEARBY TOURS (NORTHERN VIETNAM TOURS)</a></li>
                                <li><a href="{$assetPrefix}tour.html?category=central-vietnam-tours">DANANG AND NEARBY TOURS (CENTRAL VIETNAM TOURS)</a></li>
                                <li><a href="{$assetPrefix}tour.html?category=southern-vietnam-tours">HO CHI MINH CITY AND NEARBY TOURS (SOUTHERN VIETNAM TOURS)</a></li>
                            </ul>
                        </li>
                        <li><a href="{$assetPrefix}other-services.html">OTHER SERVICES</a></li>
                        <li><a href="{$assetPrefix}tour.html#diary">TOUR DIARY</a></li>
                        <li><a href="{$assetPrefix}videos.html">VIDEOS</a></li>
                        <li><a href="{$assetPrefix}contact.html">CONTACT</a></li>
                        <li>
                            <a href="{$assetPrefix}cart.html" class="nav-cart-btn" title="Cart">
                                <i class="fa-solid fa-cart-shopping"></i>
                                <span class="cart-badge">0</span>
                            </a>
                        </li>
                    </ul>
                </nav>
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
                <a href="{$assetPrefix}index.html" class="mobile-brand-logo">
                    <img src="{$assetPrefix}assets/images/logo.png" alt="BUDDY PRIVATE TOUR" class="mobile-site-logo">
                </a>
            </div>

            <!-- Mobile MENU Bar -->
            <div class="mobile-menu-box">
                <div class="mobile-menu-bar" id="mobileMenuBar">
                    <span class="mobile-menu-text">MENU</span>
                    <div class="mobile-menu-right-actions">
                        <a href="{$assetPrefix}cart.html" class="mobile-menu-cart-icon-btn" title="Cart" onclick="event.stopPropagation();">
                            <i class="fa-solid fa-cart-shopping"></i>
                            <span class="cart-badge">0</span>
                        </a>
                        <span class="mobile-menu-icon"><i class="fa-solid fa-bars"></i></span>
                    </div>
                </div>

                <!-- Dropdown Menu List -->
                <div class="mobile-menu-dropdown" id="mobileMenuDropdown">
                    <ul class="mobile-menu-list">
                        <li class="mobile-menu-item-home"><a href="{$assetPrefix}index.html">HOME</a></li>
                        <li><a href="{$assetPrefix}about-us.html">ABOUT US</a></li>
                        <li class="mobile-menu-has-sub">
                            <div class="mobile-menu-sub-head">
                                <a href="{$assetPrefix}tour.html">TOUR</a>
                                <span class="mobile-sub-chevron"><i class="fa-solid fa-chevron-down"></i></span>
                            </div>
                            <ul class="mobile-menu-sub-list">
                                <li><a href="{$assetPrefix}tour.html?category=northern-vietnam-tours">HA NOI AND NEARBY TOURS (NORTHERN VIETNAM TOURS)</a></li>
                                <li><a href="{$assetPrefix}tour.html?category=central-vietnam-tours">DANANG AND NEARBY TOURS (CENTRAL VIETNAM TOURS)</a></li>
                                <li><a href="{$assetPrefix}tour.html?category=southern-vietnam-tours">HO CHI MINH CITY AND NEARBY TOURS (SOUTHERN VIETNAM TOURS)</a></li>
                            </ul>
                        </li>
                        <li><a href="{$assetPrefix}other-services.html">OTHER SERVICES</a></li>
                        <li><a href="{$assetPrefix}tour.html#diary">TOUR DIARY</a></li>
                        <li><a href="{$assetPrefix}videos.html">VIDEOS</a></li>
                        <li><a href="{$assetPrefix}contact.html">CONTACT</a></li>
                        <li>
                            <a href="{$assetPrefix}cart.html" class="mobile-menu-cart-link">
                                CART
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</header>
HTML;
    }

    private static function getStaticFooter($isSubfolder = false) {
        $assetPrefix = $isSubfolder ? '../' : '';

        return <<<HTML
<!-- Contact Information section -->
<footer id="contact" class="contact-info-section">
    <div class="container">
        <h3 class="contact-info-title">CONTACT INFOMATION</h3>
        <h4 class="contact-info-brand">BUDDY PRIVATE TOUR</h4>
        
        <div class="contact-info-details">
            <p><strong>Address:</strong> 24/9 Nguyen Luong Bang street, Hoa Khanh Bac, Lien Chieu, Da Nang.</p>
            <p><strong>Zalo / WhatsApp:</strong> +84567599998 – <strong>Hotline:</strong> +84567599998</p>
            <p><strong>Email:</strong> buddyprivatetour@gmail.com</p>
        </div>

        <img src="{$assetPrefix}assets/images/dathongbao.png" class="mit-badge" alt="Đã Thông Báo Bộ Công Thương">
    </div>

    <!-- Bottom Bar and Social Media Links -->
    <div class="bottom-bar">
        <div class="container">
            <div class="bottom-bar-socials">
                <a href="mailto:buddyprivatetour@gmail.com" class="social-icon-link" title="Email"><img src="{$assetPrefix}assets/images/icon-email.png" alt="Email"></a>
                <a href="#" class="social-icon-link" title="TikTok"><img src="{$assetPrefix}assets/images/icon-tiktok.png" alt="TikTok"></a>
                <a href="#" class="social-icon-link" title="YouTube"><img src="{$assetPrefix}assets/images/icon-youtube.png" alt="YouTube"></a>
                <a href="#" class="social-icon-link" title="Facebook"><img src="{$assetPrefix}assets/images/icon-facebook.png" alt="Facebook"></a>
                <a href="#" class="social-icon-link" title="Viber"><img src="{$assetPrefix}assets/images/icon-viber.png" alt="Viber"></a>
                <a href="#" class="social-icon-link" title="WeChat"><img src="{$assetPrefix}assets/images/icon-wechat.png" alt="WeChat"></a>
                <a href="https://zalo.me/84567599998" target="_blank" class="social-icon-link" title="Zalo"><img src="{$assetPrefix}assets/images/icon-zalo.png" alt="Zalo"></a>
                <a href="#" class="social-icon-link" title="TripAdvisor"><img src="{$assetPrefix}assets/images/icon-tripadvisor.png" alt="TripAdvisor"></a>
            </div>
        </div>
    </div>
</footer>

<!-- Floating actions in bottom-left corner exactly like screenshots -->
<div class="floating-left-actions">
    <a href="tel:+84567599998" class="bpt-hotline-pill" title="Call Hotline: +84567599998">
        <div class="bpt-hotline-circle">
            <div class="bpt-hotline-red">
                <i class="fa-solid fa-phone"></i>
            </div>
        </div>
        <span class="bpt-hotline-number">+84567599998</span>
    </a>
    <a href="https://zalo.me/84567599998" target="_blank" rel="noopener noreferrer" class="bpt-zalo-bubble" title="Chat Zalo / WhatsApp: +84567599998">
        <div class="bpt-zalo-inner">
            <span class="bpt-zalo-text">Zalo</span>
        </div>
    </a>
</div>

<!-- Floating Back to Top Button on bottom right -->
<a href="#" class="floating-back-to-top" title="Back to Top" onclick="window.scrollTo({top: 0, behavior: 'smooth'}); return false;">
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
        <h1 style="font-size: 2.2rem; font-weight: 800; color: #0c4a85; margin-bottom: 2.5rem; text-transform: uppercase;">ABOUT US</h1>
        <div style="background: #fff; padding: 3rem; border-radius: var(--radius-lg); border: 1px solid var(--border-color); box-shadow: 0 4px 15px rgba(0,0,0,0.05); max-width: 900px; margin: 0 auto;">
            <div class="about-content" style="font-size: 1.05rem; line-height: 1.8; color: #334155;">
                {$content}
            </div>
        </div>
    </div>
</div>
{$footer}
HTML;
    }

    private static function buildStaticTour($products, $seedProductsJson, $seedCategoriesJson, $seedCartJson, $seedOrdersJson) {
        $header = self::getStaticHeader("Tour - Buddy Private Tour");
        $footer = self::getStaticFooter();
        return <<<HTML
{$header}
<div class="tour-page-wrapper">
    <div class="container">
        <!-- Breadcrumb bar matching user screenshot -->
        <div class="site-breadcrumb-wrap">
            <nav class="site-breadcrumb-box">
                <a href="index.html" class="crumb-link">
                    <i class="fa-solid fa-house"></i>Home
                </a>
                <span class="crumb-separator">/</span>
                <span id="static-crumb-trail"><span class="crumb-current">Tour</span></span>
            </nav>
        </div>

        <h1 class="tour-page-heading" id="static-tour-heading">
            TOUR
        </h1>

        <!-- Regional Category Filter Tabs -->
        <div class="tour-category-tabs" style="display: flex; gap: 0.75rem; margin-bottom: 2rem; flex-wrap: wrap;">
            <a href="tour.html" id="cat-tab-all" class="cat-filter-btn active" style="padding: 0.55rem 1.25rem; border-radius: 9999px; text-decoration: none; font-size: 0.9rem; font-weight: 700; background: #0c4a85; color: #fff; border: 1px solid #0c4a85; transition: all 0.2s;">All Tours</a>
            <a href="tour.html?category=northern-vietnam-tours" id="cat-tab-north" class="cat-filter-btn" style="padding: 0.55rem 1.25rem; border-radius: 9999px; text-decoration: none; font-size: 0.9rem; font-weight: 700; background: #fff; color: #334155; border: 1px solid #cbd5e1; transition: all 0.2s;">Northern Vietnam Tours</a>
            <a href="tour.html?category=central-vietnam-tours" id="cat-tab-central" class="cat-filter-btn" style="padding: 0.55rem 1.25rem; border-radius: 9999px; text-decoration: none; font-size: 0.9rem; font-weight: 700; background: #fff; color: #334155; border: 1px solid #cbd5e1; transition: all 0.2s;">Central Vietnam Tours</a>
            <a href="tour.html?category=southern-vietnam-tours" id="cat-tab-south" class="cat-filter-btn" style="padding: 0.55rem 1.25rem; border-radius: 9999px; text-decoration: none; font-size: 0.9rem; font-weight: 700; background: #fff; color: #334155; border: 1px solid #cbd5e1; transition: all 0.2s;">Southern Vietnam Tours</a>
        </div>

        <!-- Tour list grid -->
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
    
    // Parse category from URL query param (?category=... or ?cat_id=...)
    const urlParams = new URLSearchParams(window.location.search);
    const categoryParam = (urlParams.get('category') || '').toLowerCase();
    const catIdParam = parseInt(urlParams.get('cat_id')) || 0;
    
    let filterKey = '';
    let currentCatName = 'TOUR';
    
    // Reset tabs
    document.querySelectorAll('.cat-filter-btn').forEach(btn => {
        btn.style.background = '#fff';
        btn.style.color = '#334155';
        btn.style.borderColor = '#cbd5e1';
    });

    if (categoryParam) {
        if (categoryParam.includes('north') || categoryParam.includes('ha-noi') || categoryParam === '1') {
            filterKey = 'north';
            currentCatName = 'HA NOI AND NEARBY TOURS (NORTHERN VIETNAM TOURS)';
            setActiveTab('cat-tab-north');
        } else if (categoryParam.includes('central') || categoryParam.includes('da-nang') || categoryParam.includes('danang') || categoryParam === '2') {
            filterKey = 'central';
            currentCatName = 'DANANG AND NEARBY TOURS (CENTRAL VIETNAM TOURS)';
            setActiveTab('cat-tab-central');
        } else if (categoryParam.includes('south') || categoryParam.includes('ho-chi-minh') || categoryParam === '3') {
            filterKey = 'south';
            currentCatName = 'HO CHI MINH CITY AND NEARBY TOURS (SOUTHERN VIETNAM TOURS)';
            setActiveTab('cat-tab-south');
        }
    } else if (catIdParam > 0) {
        if (catIdParam === 1) {
            filterKey = 'north';
            currentCatName = 'HA NOI AND NEARBY TOURS (NORTHERN VIETNAM TOURS)';
            setActiveTab('cat-tab-north');
        } else if (catIdParam === 2) {
            filterKey = 'central';
            currentCatName = 'DANANG AND NEARBY TOURS (CENTRAL VIETNAM TOURS)';
            setActiveTab('cat-tab-central');
        } else if (catIdParam === 3) {
            filterKey = 'south';
            currentCatName = 'HO CHI MINH CITY AND NEARBY TOURS (SOUTHERN VIETNAM TOURS)';
            setActiveTab('cat-tab-south');
        }
    } else {
        setActiveTab('cat-tab-all');
    }

    heading.textContent = currentCatName;
    document.title = (currentCatName !== 'TOUR' ? currentCatName + ' - ' : '') + 'Buddy Private Tour';

    if (filterKey) {
        crumbTrail.innerHTML = `
            <a href="tour.html" class="crumb-link">Tour</a>
            <span class="crumb-separator">/</span>
            <span class="crumb-current crumb-category">\${currentCatName}</span>
        `;
    } else {
        crumbTrail.innerHTML = `<span class="crumb-current">Tour</span>`;
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
            <div style="grid-column: 1 / -1; background: #fff; padding: 3rem; text-align: center; border-radius: 8px; border: 1px solid #e2e8f0;">
                <p style="color: #64748b; font-size: 1.1rem; margin: 0;">No tours found in this category.</p>
            </div>`;
        return;
    }

    let html = '';
    products.forEach(p => {
        const imgSrc = BPT_Storage.fixImagePath(p.image);
        html += `
            <div class="tour-card">
                <span class="tour-card-badge">
                    \${p.category_name}
                </span>
                <div class="tour-card-image">
                    <a href="products/product-\${p.id}.html">
                        <img src="\${imgSrc}" alt="\${p.title}" loading="lazy">
                    </a>
                </div>
                <div class="tour-card-body">
                    <h4 class="tour-card-title">
                        <a href="products/product-\${p.id}.html">\${p.title}</a>
                    </h4>
                    <div class="tour-card-price">
                        \${BPT_Storage.formatCurrency(p.price)}
                    </div>
                    <button onclick="BPT_Storage.addToCart(\${p.id}, 1); window.location.href='cart.html';" class="tour-card-btn" style="background: #22c55e; border: none; cursor: pointer;">
                        <i class="fa-solid fa-cart-plus"></i> Add to shopping cart
                    </button>
                </div>
            </div>`;
    });
    grid.innerHTML = html;
}

function setActiveTab(tabId) {
    const el = document.getElementById(tabId);
    if (el) {
        el.style.background = '#0c4a85';
        el.style.color = '#fff';
        el.style.borderColor = '#0c4a85';
    }
}
</script>
{$footer}
HTML;
    }

    private static function buildStaticOtherServices() {
        $header = self::getStaticHeader("Other Services - Buddy Private Tour");
        $footer = self::getStaticFooter();
        
        $services = BPT_ProductModel::getByCategorySlug('other-services');
        if (empty($services)) {
            $services = [
                [
                    'slug' => 'car-rental',
                    'title' => 'CAR RENTAL FROM 4-45',
                    'summary' => 'Professional private car rental services with clean vehicles, experienced english-speaking drivers, and competitive pricing for travelers in Vietnam.',
                    'image' => 'assets/images/dkt-transport-cung-cap-dich-vu-cho-thue-xe-7-cho-tai-ha-noi-1024x576.jpg'
                ],
                [
                    'slug' => 'hotel',
                    'title' => 'HOTEL',
                    'summary' => 'Hand-picked accommodation recommendations, hotel bookings, and custom holiday stays across Vietnam ranging from luxury resorts to local homestays.',
                    'image' => 'assets/images/mau-tam-trang-tri-giuong-khach-san-dep-nhat-19.jpg'
                ],
                [
                    'slug' => 'airline-tickets',
                    'title' => 'CHEAP AIRLINE TICKETS',
                    'summary' => 'Domestic and international flight ticketing services with optimized routes, cheap fares, and full-support booking management.',
                    'image' => 'assets/images/ve-7546-1571044895.jpg'
                ]
            ];
        }

        $itemsHtml = '';
        foreach ($services as $srv) {
            $imgSrc = $srv['image'];
            if (!preg_match('/^https?:\/\//i', $imgSrc)) {
                $imgSrc = ltrim($imgSrc, './');
            }
            $detailUrl = 'other-services-detail-' . $srv['slug'] . '.html';
            $title = esc_html($srv['title'] ?? '');
            $summary = trim(strip_tags($srv['summary'] ?? ''));
            $descHtml = '';
            if (!empty($summary)) {
                $descHtml = '<p style="color: #64748b; font-size: 0.95rem; line-height: 1.6; margin: 0;">' . esc_html($summary) . '</p>';
            }

            $itemsHtml .= <<<ITEM
            <div class="service-item" style="background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden; display: flex; box-shadow: 0 4px 12px rgba(0,0,0,0.03);">
                <div style="flex: 1; max-width: 340px; min-width: 260px; height: 210px; overflow: hidden;">
                    <a href="{$detailUrl}" style="display: block; width: 100%; height: 100%;">
                        <img src="{$imgSrc}" alt="{$title}" style="width: 100%; height: 100%; object-fit: cover;">
                    </a>
                </div>
                <div style="flex: 2; padding: 1.75rem 2rem; display: flex; flex-direction: column; justify-content: center; align-items: flex-start; gap: 0.85rem;">
                    <h3 style="font-size: 1.35rem; font-weight: 800; color: #1e293b; text-transform: uppercase; margin: 0;">
                        <a href="{$detailUrl}" style="color: inherit; text-decoration: none;">{$title}</a>
                    </h3>
                    {$descHtml}
                    <a href="{$detailUrl}" class="btn-primary" style="background: #004b99; border-radius: 4px; padding: 0.6rem 1.6rem; font-size: 0.9rem; font-weight: 700; color: #fff; text-decoration: none; display: inline-flex; align-items: center; gap: 0.4rem;">
                        Read more <i class="fa-solid fa-arrow-right"></i>
                    </a>
                </div>
            </div>
ITEM;
        }

        return <<<HTML
{$header}
<div class="section-padding" style="background: #f8fafc; min-height: 80vh; padding: 2rem 0 4rem 0;">
    <div class="container">
        <!-- Breadcrumb Navigation -->
        <div class="site-breadcrumb-wrap" style="padding-top: 0; margin-bottom: 1.75rem;">
            <nav class="site-breadcrumb-box">
                <a href="index.html" class="crumb-link">
                    <i class="fa-solid fa-house"></i>Home
                </a>
                <span class="crumb-separator">/</span>
                <span class="crumb-current">Other Services</span>
            </nav>
        </div>

        <h1 style="font-size: 2.2rem; font-weight: 800; color: #0c4a85; margin-bottom: 2.5rem; text-transform: uppercase;">OTHER SERVICES</h1>

        <div class="services-list" style="display: flex; flex-direction: column; gap: 2rem; max-width: 960px; margin: 0 auto;">
            {$itemsHtml}
        </div>
    </div>
</div>
{$footer}
HTML;
    }

    private static function buildStaticOtherServicesDetail($service_slug) {
        $service = BPT_ServiceModel::getBySlug($service_slug);
        if (!$service) {
            return '';
        }
        $service['date'] = $service['date_published'];
        
        $header = self::getStaticHeader($service['title'] . " - Buddy Private Tour");
        $footer = self::getStaticFooter();
        
        $imagesHtml = '';
        foreach ($service['images'] as $img_url) {
            $src = $img_url;
            if (!preg_match('/^https?:\/\//i', $src)) {
                $src = ltrim($src, './');
            }
            $imagesHtml .= '
                <div style="width: 100%; border: 1px solid #e2e8f0; border-radius: 2px; overflow: hidden; background: #fff; box-shadow: 0 1px 4px rgba(0,0,0,0.03);">
                    <img src="' . esc_attr($src) . '" alt="' . esc_attr($service['title']) . '" style="width: 100%; height: auto; display: block; object-fit: contain; max-height: 550px;">
                </div>';
        }

        return <<<HTML
{$header}
<div class="section-padding" style="background: #f8fafc; min-height: 80vh; padding-top: 1.5rem; padding-bottom: 4rem;">
    <div class="container" style="max-width: 900px; margin: 0 auto;">
        
        <!-- Breadcrumb bar matching user screenshot -->
        <div class="site-breadcrumb-wrap" style="padding-top: 0; margin-bottom: 1.75rem;">
            <nav class="site-breadcrumb-box">
                <a href="index.html" class="crumb-link">
                    <i class="fa-solid fa-house"></i>Home
                </a>
                <span class="crumb-separator">/</span>
                <a href="other-services.html" class="crumb-link">
                    Other Services
                </a>
                <span class="crumb-separator">/</span>
                <span class="crumb-current">{$service['title']}</span>
            </nav>
        </div>

        <!-- Header Info matching screenshot 100% -->
        <div style="margin-bottom: 1.25rem;">
            <h1 style="font-size: 1.65rem; font-weight: 800; color: #1e293b; text-transform: uppercase; margin-bottom: 0.35rem; letter-spacing: 0.3px;">{$service['title']}</h1>
            <div style="font-size: 0.82rem; color: #64748b; display: flex; align-items: center; gap: 0.85rem; flex-wrap: wrap;">
                <span><i class="fa-solid fa-user" style="color: #0c63c7; font-size: 0.78rem;"></i> by: qcv</span>
                <span><i class="fa-regular fa-folder-open" style="color: #64748b; font-size: 0.8rem;"></i> Other Services</span>
                <span><i class="fa-regular fa-comment" style="color: #64748b; font-size: 0.8rem;"></i> <span id="comment-count-display">0</span></span>
                <span><i class="fa-regular fa-calendar-days" style="color: #64748b; font-size: 0.8rem;"></i> {$service['date']}</span>
            </div>
        </div>

        <!-- Vertical list of 5 images -->
        <div class="gallery-stack" style="display: flex; flex-direction: column; gap: 2rem; margin-bottom: 3.5rem; align-items: center;">
            {$imagesHtml}
        </div>

        <div style="font-size: 0.9rem; color: var(--text-muted); margin-bottom: 2.5rem;">
            <i class="fa-solid fa-tag"></i> <strong>Tags:</strong> No Tag
        </div>

        <!-- Comments Area -->
        <div style="background: #fff; border: 1px solid var(--border-color); padding: 3rem; border-radius: var(--radius-lg); box-shadow: 0 4px 20px rgba(0,0,0,0.04);">
            
            <!-- List of Comments -->
            <div id="comments-list-section" style="margin-bottom: 3rem; display: none;">
                <h3 style="font-size: 1.4rem; font-weight: 800; color: #1e293b; margin-bottom: 1.5rem; border-bottom: 2px solid var(--border-color); padding-bottom: 0.5rem;">Comments</h3>
                <div id="comments-container" style="display: flex; flex-direction: column; gap: 1.5rem;"></div>
            </div>

            <!-- Comment Form -->
            <h3 style="font-size: 1.5rem; font-weight: 800; color: #1e293b; margin-bottom: 0.5rem;">Leave Comments</h3>
            <p style="color: var(--text-muted); font-size: 0.85rem; margin-bottom: 1.5rem;">Your comment will be saved and displayed directly below.</p>
            
            <form id="comment-form" action="" method="POST" style="display: flex; flex-direction: column; gap: 1.25rem;">
                <input type="hidden" name="action" value="add_comment">
                
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <input type="checkbox" id="save_info" style="cursor: pointer;">
                    <label for="save_info" style="font-size: 0.85rem; color: var(--text-muted); cursor: pointer; user-select: none;">Save my name, email, and website in this browser for the next time I comment.</label>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem;">
                    <div>
                        <label for="author_name" style="display: block; font-weight: 700; font-size: 0.9rem; margin-bottom: 0.4rem; color: var(--text-dark);">Name <span style="color: red;">*</span></label>
                        <input type="text" name="author_name" id="author_name" required placeholder="Your full name" style="color:#000; background:#fff; border:1px solid #cbd5e1; padding:0.75rem; width:100%; border-radius:var(--radius-sm);">
                    </div>
                    <div>
                        <label for="author_email" style="display: block; font-weight: 700; font-size: 0.9rem; margin-bottom: 0.4rem; color: var(--text-dark);">Email <span style="color: red;">*</span></label>
                        <input type="email" name="author_email" id="author_email" required placeholder="Your email address" style="color:#000; background:#fff; border:1px solid #cbd5e1; padding:0.75rem; width:100%; border-radius:var(--radius-sm);">
                    </div>
                </div>

                <div>
                    <label for="author_website" style="display: block; font-weight: 700; font-size: 0.9rem; margin-bottom: 0.4rem; color: var(--text-dark);">Website</label>
                    <input type="text" name="author_website" id="author_website" placeholder="Website (optional)" style="color:#000; background:#fff; border:1px solid #cbd5e1; padding:0.75rem; width:100%; border-radius:var(--radius-sm);">
                </div>

                <div>
                    <label for="comment_text" style="display: block; font-weight: 700; font-size: 0.9rem; margin-bottom: 0.4rem; color: var(--text-dark);">Comment <span style="color: red;">*</span></label>
                    <textarea name="comment_text" id="comment_text" rows="5" required placeholder="Write your comment here..." style="color:#000; background:#fff; border:1px solid #cbd5e1; padding:0.75rem; width:100%; border-radius:var(--radius-sm); font-family: inherit; line-height: 1.6;"></textarea>
                </div>

                <div style="margin-top: 0.5rem;">
                    <button type="submit" style="background: #0a58ca; border: none; font-weight: 700; border-radius: 4px; padding: 0.75rem 2rem; font-size: 0.9rem; color: #fff; cursor: pointer; transition: background 0.2s;">Post Comment</button>
                </div>
            </form>

        </div>

    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const serviceSlug = '{$service_slug}';
    
    // Auto fill saved info
    if (localStorage.getItem('bpt_comment_save_info') === 'true') {
        document.getElementById('save_info').checked = true;
        document.getElementById('author_name').value = localStorage.getItem('bpt_comment_author_name') || '';
        document.getElementById('author_email').value = localStorage.getItem('bpt_comment_author_email') || '';
        document.getElementById('author_website').value = localStorage.getItem('bpt_comment_author_website') || '';
    }

    // Capture form submission to save details
    document.getElementById('comment-form').addEventListener('submit', function(e) {
        const saveChecked = document.getElementById('save_info').checked;
        const nameVal = document.getElementById('author_name').value;
        const emailVal = document.getElementById('author_email').value;
        const webVal = document.getElementById('author_website').value;
        const textVal = document.getElementById('comment_text').value;

        if (saveChecked) {
            localStorage.setItem('bpt_comment_save_info', 'true');
            localStorage.setItem('bpt_comment_author_name', nameVal);
            localStorage.setItem('bpt_comment_author_email', emailVal);
            localStorage.setItem('bpt_comment_author_website', webVal);
        } else {
            localStorage.setItem('bpt_comment_save_info', 'false');
            localStorage.removeItem('bpt_comment_author_name');
            localStorage.removeItem('bpt_comment_author_email');
            localStorage.removeItem('bpt_comment_author_website');
        }

        e.preventDefault(); // Stop normal post submit
        
        // Save comment to localStorage
        const commentObj = {
            author_name: nameVal,
            created_at: new Date().toISOString().replace('T', ' ').substring(0, 19),
            comment_text: textVal
        };
        
        let localComments = [];
        try {
            localComments = JSON.parse(localStorage.getItem('bpt_comments_' + serviceSlug)) || [];
        } catch(err) {
            localComments = [];
        }
        localComments.unshift(commentObj);
        localStorage.setItem('bpt_comments_' + serviceSlug, JSON.stringify(localComments));
        
        // Re-render comments lists
        renderLocalComments();
        
        // Reset textarea
        document.getElementById('comment_text').value = '';
        alert('Thank you! Your comment has been posted successfully.');
    });

    // Helper to render static offline comments
    function renderLocalComments() {
        let localComments = [];
        try {
            localComments = JSON.parse(localStorage.getItem('bpt_comments_' + serviceSlug)) || [];
        } catch(err) {
            localComments = [];
        }

        const container = document.getElementById('comments-container');
        const section = document.getElementById('comments-list-section');
        const countDisplay = document.getElementById('comment-count-display');
        
        if (localComments.length === 0) {
            section.style.display = 'none';
            countDisplay.textContent = '0';
        } else {
            section.style.display = 'block';
            countDisplay.textContent = localComments.length;
            
            let html = '';
            localComments.forEach(cmt => {
                html += `
                    <div class="comment-card" style="background: #f8fafc; padding: 1.5rem; border-radius: var(--radius-md); border: 1px solid var(--border-color);">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                            <strong style="color: #0c4a85; font-size: 1.05rem;">\${escapeHtml(cmt.author_name)}</strong>
                            <span style="font-size: 0.8rem; color: var(--text-muted);">\${cmt.created_at}</span>
                        </div>
                        <p style="color: #334155; margin: 0; line-height: 1.6;">\${escapeHtml(cmt.comment_text).replace(/\\n/g, '<br>')}</p>
                    </div>`;
            });
            container.innerHTML = html;
        }
    }

    function escapeHtml(text) {
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.replace(/[&<>"']/g, function(m) { return map[m]; });
    }

    renderLocalComments();
});
</script>
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
        <h1 style="font-size: 2.2rem; font-weight: 800; color: #0c4a85; margin-bottom: 2.5rem; text-transform: uppercase;">VIDEOS</h1>

        <div class="home-videos-section">
            <div class="home-videos-grid">
                <!-- Video 1 -->
                <div class="home-video-card">
                    <div class="home-video-wrapper">
                        <iframe width="560" height="315" src="https://www.youtube.com/embed/AAeb7JPwJfw" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
                    </div>
                </div>

                <!-- Video 2 -->
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
            <!-- Contact Form (Left Column) -->
            <div>
                <h1 style="font-size: 1.65rem; font-weight: 800; color: #1e293b; text-transform: uppercase; margin-top: 0; margin-bottom: 2rem; letter-spacing: 0.3px;">CONTACT</h1>
                
                <form action="#" method="POST" onsubmit="alert('Thank you! Your message has been sent successfully.'); return false;">
                    <div style="margin-bottom: 1.25rem;">
                        <label for="c_name" style="display: block; font-size: 0.88rem; color: #64748b; font-weight: 500; margin-bottom: 0.4rem;">Your Name:</label>
                        <input type="text" id="c_name" required class="form-control" style="color: #1e293b; background: #fff; border: 1px solid #e2e8f0; padding: 0.55rem 0.75rem; width: 100%; border-radius: 2px; font-size: 0.9rem;">
                    </div>
                    <div style="margin-bottom: 1.25rem;">
                        <label for="c_phone" style="display: block; font-size: 0.88rem; color: #64748b; font-weight: 500; margin-bottom: 0.4rem;">Your Phone:</label>
                        <input type="tel" id="c_phone" required class="form-control" style="color: #1e293b; background: #fff; border: 1px solid #e2e8f0; padding: 0.55rem 0.75rem; width: 100%; border-radius: 2px; font-size: 0.9rem;">
                    </div>
                    <div style="margin-bottom: 1.5rem;">
                        <label for="c_msg" style="display: block; font-size: 0.88rem; color: #64748b; font-weight: 500; margin-bottom: 0.4rem;">Your Message:</label>
                        <textarea id="c_msg" rows="5" class="form-control" style="color: #1e293b; background: #fff; border: 1px solid #e2e8f0; padding: 0.55rem 0.75rem; width: 100%; border-radius: 2px; font-size: 0.9rem; height: 130px; resize: vertical;"></textarea>
                    </div>
                    <button type="submit" style="background: #004b99; color: #fff; font-weight: 800; font-size: 0.85rem; padding: 0.65rem 1.75rem; border: none; border-radius: 2px; cursor: pointer; text-transform: uppercase; letter-spacing: 0.5px; transition: background 0.2s ease;">SEND MESSAGE</button>
                </form>
            </div>

            <!-- Contact Info (Right Column) -->
            <div style="display: flex; flex-direction: column; justify-content: flex-start; padding-top: 3.5rem;">
                <h3 style="font-size: 1.05rem; font-weight: 800; color: #1e293b; text-transform: uppercase; margin-top: 0; margin-bottom: 1.25rem; border-bottom: 1px solid #e5e7eb; padding-bottom: 0.65rem; letter-spacing: 0.3px;">CONTACT INFOMATION</h3>
                <h4 style="font-size: 0.95rem; font-weight: 800; color: #1e293b; text-transform: uppercase; margin-bottom: 1.25rem; letter-spacing: 0.3px;">BUDDY PRIVATE TOUR</h4>
                
                <div style="font-size: 0.88rem; line-height: 1.7; color: #475569; display: flex; flex-direction: column; gap: 1rem;">
                    <p style="margin: 0;">Address: 24/9 Nguyen Luong Bang street, Hoa Khanh Bac, Lien Chieu, Da Nang.</p>
                    <p style="margin: 0;">Zalo / WhatsApp: +84567599998 – Hotline: +84567599998</p>
                    <p style="margin: 0;">Email: buddyprivatetour@gmail.com</p>
                </div>
            </div>
        </div>
    </div>
</div>
{$footer}
HTML;
    }

    private static function buildStaticIndex($categories, $products, $seedProductsJson, $seedCategoriesJson, $seedCartJson, $seedOrdersJson) {
        $header = self::getStaticHeader("Home - Buddy Private Tour");
        $footer = self::getStaticFooter();

        $homepage_id = get_option('page_on_front');
        $homepage_post = $homepage_id ? get_post($homepage_id) : null;
        $homepage_content = '';
        if ($homepage_post) {
            $homepage_content = apply_filters('the_content', $homepage_post->post_content);
            $homepage_content = str_replace(home_url(), 'https://buddyprivatetour.com', $homepage_content);
        }

        $otherServicesItems = BPT_ProductModel::getByCategorySlug('other-services');
        if (empty($otherServicesItems)) {
            $otherServicesItems = [
                [
                    'slug' => 'car-rental',
                    'title' => 'CAR RENTAL FROM 4-45',
                    'image' => 'https://pub-2487a20a0c6f4a658cc699f285f4c0a8.r2.dev/wp-content/uploads/2026/08/dkt-transport-cung-cap-dich-vu-cho-thue-xe-7-cho-tai-ha-noi.webp'
                ],
                [
                    'slug' => 'hotel',
                    'title' => 'HOTEL',
                    'image' => 'https://pub-2487a20a0c6f4a658cc699f285f4c0a8.r2.dev/wp-content/uploads/2026/08/hotel.webp'
                ],
                [
                    'slug' => 'airline-tickets',
                    'title' => 'CHEAP AIRLINE TICKETS',
                    'image' => 'https://pub-2487a20a0c6f4a658cc699f285f4c0a8.r2.dev/wp-content/uploads/2026/08/ve-7546-1571044895.webp'
                ]
            ];
        }

        $otherServicesCardsHtml = '';
        foreach ($otherServicesItems as $srv) {
            $imgSrc = $srv['image'];
            if (!preg_match('/^https?:\/\//i', $imgSrc)) {
                $imgSrc = ltrim($imgSrc, './');
            }
            $detailUrl = 'other-services-detail-' . $srv['slug'] . '.html';
            $title = esc_html($srv['title']);

            $otherServicesCardsHtml .= <<<CARD
                <div class="tour-card">
                    <div class="tour-card-image">
                        <a href="{$detailUrl}">
                            <img src="{$imgSrc}" alt="{$title}">
                        </a>
                    </div>
                    <div class="tour-card-body">
                        <h4 class="tour-card-title"><a href="{$detailUrl}">{$title}</a></h4>
                        <a href="{$detailUrl}" class="card-readmore-btn">
                            <i class="fa-regular fa-circle-right"></i> Read more
                        </a>
                    </div>
                </div>
CARD;
        }

        // Build Pre-rendered Static Tours Catalog HTML
        $staticCatalogHtml = '';
        foreach ($categories as $cat) {
            if (($cat['slug'] ?? '') === 'other-services' || (int)($cat['id'] ?? 0) === 4 || (int)($cat['id'] ?? 0) === 21) continue;
            
            $catProducts = array_filter($products, function($p) use ($cat) {
                return (int)$p['category_id'] === (int)$cat['id'];
            });
            if (empty($catProducts)) continue;

            $cat_str = strtolower(($cat['slug'] ?? '') . ' ' . ($cat['name'] ?? ''));
            $isNorth = (strpos($cat_str, 'north') !== false || strpos($cat_str, 'bac') !== false || (int)($cat['id'] ?? 0) === 1 || (int)($cat['id'] ?? 0) === 18);
            $isCentral = (strpos($cat_str, 'central') !== false || strpos($cat_str, 'trung') !== false || (int)($cat['id'] ?? 0) === 2 || (int)($cat['id'] ?? 0) === 19);
            $isSouth = (strpos($cat_str, 'south') !== false || strpos($cat_str, 'nam') !== false || (int)($cat['id'] ?? 0) === 3 || (int)($cat['id'] ?? 0) === 20);

            if ($isCentral) {
                $staticCatalogHtml .= <<<QB
                <div class="middle-banner-section">
                    <img src="https://buddyprivatetour.com/wp-content/uploads/2022/10/WCQB.jpg" alt="Quang Binh Banner">
                </div>
QB;
            }

            $anchorAlias = '';
            if ($isNorth) $anchorAlias = 'northern-vietnam-tours';
            elseif ($isCentral) $anchorAlias = 'central-vietnam-tours';
            elseif ($isSouth) $anchorAlias = 'southern-vietnam-tours';

            $anchorHtml = $anchorAlias ? "<div id=\"{$anchorAlias}\" style=\"position:relative; top:-90px; visibility:hidden;\"></div>" : '';
            $catSlug = esc_attr($cat['slug']);
            $catName = esc_html($cat['name']);

            $cardsHtml = '';
            foreach ($catProducts as $p) {
                $pId = $p['id'];
                $pTitle = esc_html($p['title']);
                $pImg = $p['image'];
                if ($pImg && !preg_match('/^https?:\/\//i', $pImg)) {
                    $pImg = 'assets/images/' . basename($pImg);
                }
                $cardsHtml .= <<<CARD
                <div class="tour-card">
                    <div class="tour-card-image">
                        <a href="products/product-{$pId}.html">
                            <img src="{$pImg}" alt="{$pTitle}" loading="lazy">
                        </a>
                    </div>
                    <div class="tour-card-body">
                        <h4 class="tour-card-title"><a href="products/product-{$pId}.html">{$pTitle}</a></h4>
                        <a href="products/product-{$pId}.html" class="card-readmore-btn">
                            <i class="fa-regular fa-circle-right"></i> Read more
                        </a>
                    </div>
                </div>
CARD;
            }

            $staticCatalogHtml .= <<<CAT
            {$anchorHtml}
            <div id="{$catSlug}" style="margin-bottom: 4rem; scroll-margin-top: 100px;">
                <div class="cat-header-wrap">
                    <img src="assets/images/new-badge.png" class="new-badge-icon" alt="New Badge">
                    <h2 class="category-section-title">{$catName}</h2>
                    <img src="assets/images/new-badge.png" class="new-badge-icon" alt="New Badge">
                </div>
                <div class="tours-grid">
                    {$cardsHtml}
                </div>
            </div>
CAT;
        }

        return <<<HTML
{$header}
<!-- Hero Slider Section matching 3 banner designs -->
<section class="hero-slider" id="hero-slider">
    <div class="slider-wrapper">
        <!-- Slide 1: Buddy Private Tour Overview (in Hanoi, Da nang, Nha trang, Da lat, Ho chi minh, Phu quoc) -->
        <div class="slide-item active">
            <a href="#catalog" class="slide-link">
                <img src="https://buddyprivatetour.com/wp-content/uploads/2019/12/bn3.jpg" alt="Buddy Private Tour - in Hanoi, Da nang, Nha trang, Da lat, Ho chi minh, Phu quoc" class="slide-img" />
            </a>
        </div>
        <!-- Slide 2: Stimulate tourism with Buddy Private Tour - Discount up to 50% -->
        <div class="slide-item">
            <a href="#catalog" class="slide-link">
                <img src="https://buddyprivatetour.com/wp-content/uploads/2019/12/bn2.jpg" alt="Stimulate tourism with Buddy Private Tour - Discount up to 50%" class="slide-img" />
            </a>
        </div>
        <!-- Slide 3: Roaming Everywhere Together - BUDDY PRIVATE TOUR - Travel Phu Quoc Island -->
        <div class="slide-item">
            <a href="#catalog" class="slide-link">
                <img src="https://buddyprivatetour.com/wp-content/uploads/2019/12/bn1.jpg" alt="Roaming Everywhere Together - BUDDY PRIVATE TOUR - Travel Phu Quoc Island" class="slide-img" />
            </a>
        </div>
    </div>

    <!-- Bullets -->
    <div class="slider-bullets">
        <span class="bullet active" onclick="setSlide(0)" aria-label="Slide 1"></span>
        <span class="bullet" onclick="setSlide(1)" aria-label="Slide 2"></span>
        <span class="bullet" onclick="setSlide(2)" aria-label="Slide 3"></span>
    </div>

    <!-- Navigation Arrows -->
    <button class="slider-nav prev" onclick="changeSlide(-1)" aria-label="Previous Slide">
        <i class="fa-solid fa-chevron-left"></i>
    </button>
    <button class="slider-nav next" onclick="changeSlide(1)" aria-label="Next Slide">
        <i class="fa-solid fa-chevron-right"></i>
    </button>
</section>

<!-- Editable Content Area for Homepage (Elementor / Gutenberg) -->
<div class="homepage-editable-content container" style="margin-top: 3rem; margin-bottom: 2rem;">
    {$homepage_content}
</div>

<div id="catalog" class="section-padding">
    <div class="container">
        <div id="static-catalog-container">
            {$staticCatalogHtml}
        </div>

        <!-- Home Featured Videos Section -->
        <div class="home-videos-section">
            <div class="home-videos-grid">
                <!-- Video 1 -->
                <div class="home-video-card">
                    <div class="home-video-wrapper">
                        <iframe width="560" height="315" src="https://www.youtube.com/embed/AAeb7JPwJfw" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
                    </div>
                </div>

                <!-- Video 2 -->
                <div class="home-video-card">
                    <div class="home-video-wrapper">
                        <iframe width="560" height="315" src="https://www.youtube.com/embed/STt213h-_hM" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Full-width Middle Banner (bn3.jpg) stretched 100% across the screen -->
<div class="middle-banner-fullwidth" style="width: 100%; margin-top: 2rem; margin-bottom: 3.5rem; overflow: hidden; line-height: 0;">
    <img src="assets/images/bn3.jpg" alt="Buddy Private Tour Banner" style="width: 100%; height: auto; display: block; object-fit: cover;">
</div>

<div class="section-padding" style="padding-top: 0; background: #fff;">
    <div class="container">
        <!-- Other Services Section below tours and videos -->
        <div id="other-services" class="other-services-home-section" style="margin-bottom: 4rem;">
            <div class="cat-header-wrap">
                <img src="assets/images/new-badge.png" class="new-badge-icon" alt="New Badge">
                <h2 class="category-section-title">OTHER SERVICES</h2>
                <img src="assets/images/new-badge.png" class="new-badge-icon" alt="New Badge">
            </div>

            <div class="tours-grid">
                {$otherServicesCardsHtml}
            </div>
        </div>
    </div>
</div>

<!-- Consultation form section (Glassmorphism layout) -->
<section id="consultation" class="consultation-section" style="background-image: url('https://buddyprivatetour.com/wp-content/uploads/2022/10/post_vinh-ha-long-054559201019.jpg');">
    <div class="container">
        <div class="glass-form-box">
            <h3 class="glass-form-title">TOUR CONSULTATION & QUICK BOOKING</h3>
            <form action="#" method="POST" onsubmit="alert('Thank you! Your inquiry has been sent successfully.'); return false;">
                <div class="glass-form-group">
                    <label for="cust_name">Your Name:</label>
                    <input type="text" id="cust_name" class="glass-form-control" required placeholder="Full Name">
                </div>
                <div class="glass-form-group">
                    <label for="cust_phone">Your Phone:</label>
                    <input type="tel" id="cust_phone" class="glass-form-control" required placeholder="Phone Number / WhatsApp">
                </div>
                <div class="glass-form-group">
                    <label for="cust_msg">Your Message:</label>
                    <textarea id="cust_msg" class="glass-form-control" rows="4" placeholder="Your travel dates, number of persons, or special requests..."></textarea>
                </div>
                <div style="text-align: center; margin-top: 1.5rem;">
                    <button type="submit" class="glass-btn-submit">SEND INQUIRY</button>
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
        if (s.includes('other') || s.includes('xe') || s.includes('service')) return 4;
        return 99;
    };

    const sortedCategories = [...categories].sort((a, b) => getOrder(a) - getOrder(b));

    sortedCategories.forEach(cat => {
        if (cat.slug === 'other-services' || cat.id == 4 || cat.id == 21) return;

        const s = ((cat.slug || '') + ' ' + (cat.name || '')).toLowerCase();
        const isCentral = s.includes('central') || s.includes('trung') || cat.id == 2 || cat.id == 19;

        if (isCentral) {
            html += `
            <div class="middle-banner-section">
                <img src="https://buddyprivatetour.com/wp-content/uploads/2022/10/WCQB.jpg" alt="Quang Binh Banner">
            </div>`;
        }

        const catProducts = products.filter(p => p.category_id == cat.id);
        if (catProducts.length === 0) return;

        let anchorAlias = '';
        if (s.includes('north') || s.includes('bac')) anchorAlias = 'northern-vietnam-tours';
        else if (s.includes('central') || s.includes('trung')) anchorAlias = 'central-vietnam-tours';
        else if (s.includes('south') || s.includes('nam')) anchorAlias = 'southern-vietnam-tours';

        html += `
        \${anchorAlias ? \`<div id="\${anchorAlias}" style="position:relative; top:-90px; visibility:hidden;"></div>\` : ''}
        <div id="\${cat.slug}" style="margin-bottom: 4rem; scroll-margin-top: 100px;">
            <div class="cat-header-wrap">
                <img src="assets/images/new-badge.png" class="new-badge-icon" alt="New Badge">
                <h2 class="category-section-title">\${cat.name}</h2>
                <img src="assets/images/new-badge.png" class="new-badge-icon" alt="New Badge">
            </div>
            <div class="tours-grid">`;

        catProducts.forEach(p => {
            const imgSrc = BPT_Storage.fixImagePath(p.image);
            html += `
                <div class="tour-card">
                    <div class="tour-card-image">
                        <a href="products/product-\${p.id}.html">
                            <img src="\${imgSrc}" alt="\${p.title}">
                        </a>
                    </div>
                    <div class="tour-card-body">
                        <h4 class="tour-card-title"><a href="products/product-\${p.id}.html">\${p.title}</a></h4>
                        <a href="products/product-\${p.id}.html" class="card-readmore-btn">
                            <i class="fa-regular fa-circle-right"></i> Read more
                        </a>
                    </div>
                </div>`;
        });

        html += `</div></div>`;
    });

    container.innerHTML = html;
}

// Hero Slider Script
let currentSlide = 0;
const slides = document.querySelectorAll('.slide-item');
const bullets = document.querySelectorAll('.bullet');
let slideInterval = setInterval(nextSlide, 4500);

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
    slideInterval = setInterval(nextSlide, 4500);
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

        $pSummary = htmlspecialchars($product['summary'] ?? '', ENT_QUOTES, 'UTF-8');
        $pDesc = wpautop(wp_kses_post($product['description'] ?? ''));
        $pImageRaw = htmlspecialchars($product['image'], ENT_QUOTES, 'UTF-8');
        $pCategory = htmlspecialchars($product['category_name'], ENT_QUOTES, 'UTF-8');
        $pCategorySlug = htmlspecialchars($product['category_slug'] ?? 'tour', ENT_QUOTES, 'UTF-8');
        $pFormattedPrice = format_currency($product['price']);
        $pId = intval($product['id']);

        // Gallery HTML
        $all_gallery = array_merge([$product['image']], $gallery_images);
        $all_gallery = array_unique(array_filter($all_gallery));
        $thumbnailsHtml = '';
        if (count($all_gallery) > 1) {
            $thumbnailsHtml .= '<div class="bpt-thumbnails-grid" style="display: flex; gap: 0.5rem; margin-top: 0.85rem; flex-wrap: wrap;">';
            foreach ($all_gallery as $index => $img_path) {
                $img_url = htmlspecialchars($img_path, ENT_QUOTES, 'UTF-8');
                $activeBorder = ($index === 0) ? '#0c4a85' : '#e2e8f0';
                $activeCls = ($index === 0) ? 'active' : '';
                $thumbnailsHtml .= '<div class="bpt-thumb-item ' . $activeCls . '" onclick="switchBptImage(\'' . $img_url . '\', this)" style="width: 70px; height: 55px; border: 1px solid ' . $activeBorder . '; overflow: hidden; cursor: pointer;"><img src="' . $img_url . '" style="width: 100%; height: 100%; object-fit: cover; display: block;"></div>';
            }
            $thumbnailsHtml .= '</div>';
        }

        // Related Products JSON & first item
        $relatedProducts = array_values(array_filter($allProducts, function($p) use ($product) {
            return $p['id'] != $product['id'] && ($p['category_id'] == $product['category_id'] || empty($product['category_id']));
        }));
        if (empty($relatedProducts)) {
            $relatedProducts = array_values(array_filter($allProducts, function($p) use ($product) {
                return $p['id'] != $product['id'];
            }));
        }
        $relJsonData = array_map(function($p) {
            return [
                'id' => intval($p['id']),
                'title' => $p['title'],
                'price' => format_currency($p['price']),
                'image' => $p['image'],
                'url' => 'product-' . $p['id'] . '.html'
            ];
        }, $relatedProducts);
        $relatedProductsJson = json_encode($relJsonData, JSON_UNESCAPED_UNICODE);
        $firstRel = !empty($relJsonData) ? $relJsonData[0] : null;
        $firstRelImg = $firstRel ? $firstRel['image'] : '';
        $firstRelTitle = $firstRel ? $firstRel['title'] : '';
        $firstRelPrice = $firstRel ? $firstRel['price'] : '';
        $firstRelUrl = $firstRel ? $firstRel['url'] : '#';

        // Included / Excluded HTML
        $includedHtml = '';
        if (!empty($included)) {
            $includedHtml .= '<ul style="list-style: none; display: flex; flex-direction: column; gap: 0.6rem; padding-left: 0; margin-bottom: 0;">';
            foreach ($included as $inc) {
                $includedHtml .= '<li style="display: flex; align-items: flex-start; gap: 0.5rem; color: #1e293b; font-size: 0.95rem; line-height: 1.5;"><span style="color: #1e293b; font-weight: bold; margin-top: -0.1rem; font-size: 0.8rem;">♦</span><span>' . htmlspecialchars($inc, ENT_QUOTES, 'UTF-8') . '</span></li>';
            }
            $includedHtml .= '</ul>';
        } else {
            $includedHtml = '<p style="font-size: 0.95rem; color: #64748b; font-style: italic;">No specifications.</p>';
        }

        $excludedHtml = '';
        if (!empty($excluded)) {
            $excludedHtml .= '<ul style="list-style: none; display: flex; flex-direction: column; gap: 0.6rem; padding-left: 0; margin-bottom: 0;">';
            foreach ($excluded as $exc) {
                $excludedHtml .= '<li style="display: flex; align-items: flex-start; gap: 0.5rem; color: #1e293b; font-size: 0.95rem; line-height: 1.5;"><span style="color: #1e293b; font-weight: bold; margin-top: -0.1rem; font-size: 0.8rem;">♦</span><span>' . htmlspecialchars($exc, ENT_QUOTES, 'UTF-8') . '</span></li>';
            }
            $excludedHtml .= '</ul>';
        } else {
            $excludedHtml = '<p style="font-size: 0.95rem; color: #64748b; font-style: italic;">No specifications.</p>';
        }

        $summaryBlock = !empty($pSummary) ? '<p style="font-size: 1.1rem; color: #334155; line-height: 1.8; margin-bottom: 1.5rem; font-weight: 500; font-style: italic;">' . $pSummary . '</p>' : '';

        return <<<HTML
{$header}
<!-- Breadcrumbs Section matching dynamic tour detail -->
<div class="site-breadcrumb-wrap container">
    <nav class="site-breadcrumb-box">
        <a href="../index.html" class="crumb-link">
            <i class="fa-solid fa-house"></i>Home
        </a>
        <span class="crumb-separator">/</span>
        <a href="../tour.html" class="crumb-link">
            Tour
        </a>
        <span class="crumb-separator">/</span>
        <a href="../tour.html?category={$pCategorySlug}" class="crumb-link crumb-category">
            {$pCategory}
        </a>
        <span class="crumb-separator">/</span>
        <span class="crumb-current">{$pTitle}</span>
    </nav>
</div>

<div class="section-padding" style="padding-top: 1.5rem; padding-bottom: 4rem; background: #fff;">
    <div class="container">
        <!-- Main Product 3-Column Section matching screenshot -->
        <div class="bpt-tour-top-grid">
            
            <!-- Column 1: Main Image & Thumbnails -->
            <div class="bpt-gallery-col">
                <div class="bpt-main-image-wrap">
                    <img id="bpt-main-img" src="{$pImageRaw}" alt="{$pTitle}">
                </div>
                {$thumbnailsHtml}
            </div>
            
            <!-- Column 2: Product Title, Price, Quantity & Add to Cart Button -->
            <div class="bpt-tour-summary-col">
                <h1 style="font-size: 1.6rem; font-weight: 700; color: #1e293b; line-height: 1.25; margin-top: 0; margin-bottom: 0.5rem; text-transform: uppercase;">
                    {$pTitle}
                </h1>
                
                <!-- Price Tag -->
                <div style="font-size: 1.35rem; font-weight: 800; color: #cc0000; margin-bottom: 2rem;">
                    {$pFormattedPrice}
                </div>
                
                <!-- Amount Stepper Row -->
                <div style="display: flex; align-items: center; gap: 1.5rem; margin-bottom: 1.75rem;">
                    <span style="font-weight: 500; font-size: 0.82rem; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;">AMOUNT:</span>
                    
                    <div class="bpt-qty-stepper">
                        <button type="button" onclick="changeQty(-1)" class="bpt-qty-btn">−</button>
                        <input type="number" id="detail-qty" value="1" min="1" max="50" class="bpt-qty-input" readonly>
                        <button type="button" onclick="changeQty(1)" class="bpt-qty-btn">+</button>
                    </div>
                </div>
                
                <!-- Orange Add to Cart Button -->
                <button onclick="addToCartLocalDetail({$pId})" class="bpt-add-cart-orange-btn">
                    ADD TO SHOPPING<br>CART
                </button>
            </div>

            <!-- Column 3: Related Tour Carousel Card matching screenshot -->
            <div class="bpt-tour-sidebar-col">
                <div class="bpt-side-related-card">
                    <!-- Top Header with Navigation Arrows -->
                    <div class="bpt-side-related-header">
                        <button type="button" onclick="prevRelated()" class="bpt-rel-nav-btn" aria-label="Previous">&lt;</button>
                        <button type="button" onclick="nextRelated()" class="bpt-rel-nav-btn" aria-label="Next">&gt;</button>
                    </div>
                    
                    <!-- Related Image -->
                    <div style="height: 175px; overflow: hidden; background: #fff;">
                        <a id="side-rel-img-link" href="{$firstRelUrl}">
                            <img id="side-rel-img" src="{$firstRelImg}" alt="{$firstRelTitle}" style="width: 100%; height: 100%; object-fit: cover; display: block;">
                        </a>
                    </div>
                    
                    <!-- Related Body -->
                    <div style="padding: 0.75rem 0.5rem 1.25rem; text-align: center;">
                        <a id="side-rel-title" href="{$firstRelUrl}" style="color: #333333; font-weight: 600; font-size: 0.88rem; line-height: 1.35; text-decoration: none; display: block; margin-bottom: 0.5rem; text-transform: uppercase;">
                            {$firstRelTitle}
                        </a>
                        <div id="side-rel-price" style="font-size: 0.95rem; font-weight: 800; color: #cc0000;">
                            {$firstRelPrice}
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Tabs / Content Section matching screenshot -->
        <div class="bpt-tour-tabs-wrapper">
            <!-- Tab Navigation matching screenshot with Blue active block and bullets -->
            <div class="bpt-tab-nav-bar">
                <button onclick="switchTab('description')" id="tab-btn-description" class="bpt-tab-btn-active">DESCRIPTION</button>
                <button onclick="switchTab('reviews')" id="tab-btn-reviews" class="bpt-tab-btn-inactive"><span>•</span> REVIEWS (0)</button>
                <button onclick="switchTab('tags')" id="tab-btn-tags" class="bpt-tab-btn-inactive"><span>•</span> TAGS</button>
            </div>
            
            <!-- Tab Content: Description -->
            <div id="tab-content-description" class="bpt-tab-content" style="display: block;">
                <div style="max-width: 900px; margin: 0 auto;">
                    {$summaryBlock}
                    
                    <!-- Main Description Text with rich HTML & embedded images -->
                    <div class="bpt-tour-rich-description" style="font-size: 1.05rem; color: #1e293b; line-height: 1.8; margin-bottom: 2.5rem;">
                        {$pDesc}
                    </div>
                    
                    <!-- What's Included & What's Excluded Section -->
                    <div style="display: flex; flex-direction: column; gap: 1.5rem; margin-top: 3rem; padding-top: 2rem; border-top: 1px solid #cbd5e1; margin-bottom: 2rem;">
                        <!-- Included -->
                        <div>
                            <h4 style="font-size: 1.05rem; font-weight: 800; color: #1e293b; margin-bottom: 1rem; text-transform: none;">
                                What's included:
                            </h4>
                            {$includedHtml}
                        </div>
                        
                        <!-- Excluded -->
                        <div style="margin-top: 0.5rem;">
                            <h4 style="font-size: 1.05rem; font-weight: 800; color: #1e293b; margin-bottom: 1rem; text-transform: none;">
                                What's excluded:
                            </h4>
                            {$excludedHtml}
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Tab Content: Reviews -->
            <div id="tab-content-reviews" class="bpt-tab-content" style="display: none;">
                <div style="max-width: 900px; margin: 0 auto; padding: 2.5rem; background: #f8fafc; border-radius: var(--radius-md); border: 1px solid #e2e8f0; text-align: center;">
                    <i class="fa-solid fa-comments" style="font-size: 3rem; color: #94a3b8; margin-bottom: 1rem;"></i>
                    <h3 style="font-size: 1.25rem; font-weight: 700; color: #1e293b; margin-bottom: 0.5rem;">REVIEWS (0)</h3>
                    <p style="color: #64748b; font-size: 0.95rem; margin-bottom: 1.5rem;">There are no reviews for this tour yet. Be the first to share your experience!</p>
                    <button class="btn-primary" style="background: #0c4a85;" onclick="alert('Review system is coming soon!')">WRITE A REVIEW</button>
                </div>
            </div>
            
            <!-- Tab Content: Tags -->
            <div id="tab-content-tags" class="bpt-tab-content" style="display: none;">
                <div style="max-width: 900px; margin: 0 auto; display: flex; gap: 0.75rem; flex-wrap: wrap;">
                    <span style="background: #f1f5f9; border: 1px solid #cbd5e1; padding: 0.4rem 1rem; border-radius: 9999px; font-weight: 600; font-size: 0.85rem; color: #475569;">
                        <i class="fa-solid fa-tag"></i> {$pCategorySlug}
                    </span>
                    <span style="background: #f1f5f9; border: 1px solid #cbd5e1; padding: 0.4rem 1rem; border-radius: 9999px; font-weight: 600; font-size: 0.85rem; color: #475569;">
                        <i class="fa-solid fa-tag"></i> travel
                    </span>
                    <span style="background: #f1f5f9; border: 1px solid #cbd5e1; padding: 0.4rem 1rem; border-radius: 9999px; font-weight: 600; font-size: 0.85rem; color: #475569;">
                        <i class="fa-solid fa-tag"></i> private-tour
                    </span>
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
const relatedTours = {$relatedProductsJson};
let relatedIndex = 0;

document.addEventListener('DOMContentLoaded', function() {
    BPT_Storage.init(seedProducts, seedCategories, seedCart, seedOrders);
});

function switchBptImage(imgUrl, thumb) {
    document.getElementById('bpt-main-img').src = imgUrl;
    
    // Remove active class styling from all thumbnail borders
    const thumbs = document.querySelectorAll('.bpt-thumb-item');
    thumbs.forEach(t => {
        t.style.borderColor = '#e2e8f0';
    });
    
    // Apply active border color
    if (thumb) thumb.style.borderColor = '#0c4a85';
}

function changeQty(amount) {
    const qtyInput = document.getElementById('detail-qty');
    let currentVal = parseInt(qtyInput.value) || 1;
    currentVal += amount;
    if (currentVal < 1) currentVal = 1;
    if (currentVal > 50) currentVal = 50;
    qtyInput.value = currentVal;
}

function switchTab(tabId) {
    // Hide all tab contents
    document.querySelectorAll('.bpt-tab-content').forEach(content => {
        content.style.display = 'none';
    });
    
    // Show selected tab content
    const selectedContent = document.getElementById('tab-content-' + tabId);
    if (selectedContent) selectedContent.style.display = 'block';
    
    // Reset all tab button styles
    const tabs = ['description', 'reviews', 'tags'];
    tabs.forEach(t => {
        const btn = document.getElementById('tab-btn-' + t);
        if (btn) {
            btn.className = (t === tabId) ? 'bpt-tab-btn-active' : 'bpt-tab-btn-inactive';
        }
    });
}

function updateRelatedTour() {
    if (!relatedTours || relatedTours.length === 0) return;
    const current = relatedTours[relatedIndex];
    const imgElem = document.getElementById('side-rel-img');
    const titleElem = document.getElementById('side-rel-title');
    const linkElem = document.getElementById('side-rel-img-link');
    const priceElem = document.getElementById('side-rel-price');

    if (imgElem) {
        imgElem.src = current.image;
        imgElem.alt = current.title;
    }
    if (titleElem) {
        titleElem.textContent = current.title;
        titleElem.href = current.url;
    }
    if (linkElem) linkElem.href = current.url;
    if (priceElem) priceElem.textContent = current.price;
}

function prevRelated() {
    if (!relatedTours || relatedTours.length === 0) return;
    relatedIndex = (relatedIndex - 1 + relatedTours.length) % relatedTours.length;
    updateRelatedTour();
}

function nextRelated() {
    if (!relatedTours || relatedTours.length === 0) return;
    relatedIndex = (relatedIndex + 1) % relatedTours.length;
    updateRelatedTour();
}

function addToCartLocalDetail(id) {
    const qty = parseInt(document.getElementById('detail-qty').value) || 1;
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
    <!-- Breadcrumb Navigation -->
    <div class="site-breadcrumb-wrap container">
        <nav class="site-breadcrumb-box">
            <a href="index.html" class="crumb-link">
                <i class="fa-solid fa-house"></i>Home
            </a>
            <span class="crumb-separator">/</span>
            <span class="crumb-current">Shopping Cart</span>
        </nav>
    </div>

    <div class="container">
        <!-- Title Section -->
        <div class="cart-title-section">
            <h1 class="cart-page-heading">YOUR SHOPPING CART</h1>
            <p class="cart-page-desc">Manage your tour selection and proceed to secure checkout.</p>
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

    let cartHtml = '';
    if (cart.length === 0) {
        cartHtml = `
            <div class="cart-empty-wrapper">
                <div class="cart-empty-icon">
                    <i class="fa-solid fa-cart-shopping"></i>
                </div>
                <h2 class="cart-empty-title">Your cart is currently empty!</h2>
                <p class="cart-empty-desc">
                    Explore our private tours across Northern, Central & Southern Vietnam and add them to your cart.
                </p>
                <a href="tour.html" class="btn-cart-empty-cta">
                    <i class="fa-solid fa-compass"></i> Explore Tours Now
                </a>
            </div>`;
    } else {
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
            if (!catName) {
                const prod = BPT_Storage.getProductById(item.id);
                if (prod && prod.category_id) {
                    const c = categories.find(cat => cat.id == prod.category_id);
                    if (c) catName = c.name;
                }
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
                                <a href="products/product-\${item.id}.html" class="cart-item-name">
                                    \${item.title}
                                </a>
                            </div>
                        </div>
                    </td>
                    <td class="cart-col-price" style="text-align: center;">
                        <span class="cart-unit-price">\${BPT_Storage.formatCurrency(item.price)}</span>
                    </td>
                    <td class="cart-col-qty" style="text-align: center;">
                        <div class="cart-stepper">
                            <button type="button" class="cart-stepper-btn" onclick="BPT_Storage.updateCartQuantity(\${item.id}, \${item.quantity - 1}); renderCart();">−</button>
                            <input type="number" id="qty-input-\${item.id}" value="\${item.quantity}" min="1" max="50" class="cart-stepper-input" onchange="BPT_Storage.updateCartQuantity(\${item.id}, this.value); renderCart();">
                            <button type="button" class="cart-stepper-btn" onclick="BPT_Storage.updateCartQuantity(\${item.id}, \${item.quantity + 1}); renderCart();">+</button>
                        </div>
                    </td>
                    <td class="cart-col-action" style="text-align: center;">
                        <button onclick="if(confirm('Are you sure you want to remove this tour from your cart?')){BPT_Storage.removeFromCart(\${item.id}); renderCart();}" class="cart-delete-action" title="Remove tour">
                            <i class="fa-solid fa-trash-can"></i>
                        </button>
                    </td>
                </tr>`;
        });

        cartHtml = `
            <div class="cart-layout-grid">
                <!-- Left: Products List Card -->
                <div class="cart-items-card">
                    <div class="cart-card-header">
                        <h2>
                            <i class="fa-solid fa-clipboard-list" style="color: #004b99;"></i> Selected Tour Packages
                        </h2>
                        <span class="cart-count-badge">\${totalQty} ticket(s)</span>
                    </div>

                    <table class="cart-modern-table">
                        <thead>
                            <tr>
                                <th style="width: 50%;">Tour Details</th>
                                <th style="width: 25%; text-align: center;">Tour Price</th>
                                <th style="width: 25%; text-align: center;">Quantity</th>
                                <th style="width: 40px;"></th>
                            </tr>
                        </thead>
                        <tbody>
                            \${rowsHtml}
                        </tbody>
                    </table>

                    <div class="cart-card-footer">
                        <a href="tour.html" class="cart-continue-link">
                            <i class="fa-solid fa-arrow-left"></i> Continue Browsing Tours
                        </a>
                        <span style="font-size: 0.85rem; color: #64748b;">
                            <i class="fa-solid fa-circle-info" style="color: #0284c7;"></i> All rates include taxes and private guide services.
                        </span>
                    </div>
                </div>

                <!-- Right: Sticky Order Summary Card -->
                <div class="cart-summary-sidebar">
                    <div class="cart-summary-box">
                        <h3 class="cart-summary-title">
                            <i class="fa-solid fa-receipt" style="color: #f7941d;"></i> Order Summary
                        </h3>

                        <div class="cart-summary-row">
                            <span>Total Tickets:</span>
                            <span class="val">\${totalQty} ticket(s)</span>
                        </div>

                        <div class="cart-summary-row">
                            <span>Subtotal:</span>
                            <span class="val">\${BPT_Storage.formatCurrency(BPT_Storage.getCartTotal())}</span>
                        </div>

                        <div class="cart-summary-row">
                            <span>Payment Processing:</span>
                            <span class="free-tag">Free</span>
                        </div>

                        <div class="cart-summary-divider"></div>

                        <div class="cart-grand-total-row">
                            <span class="label">Total Amount:</span>
                            <span class="amount">\${BPT_Storage.formatCurrency(BPT_Storage.getCartTotal())}</span>
                        </div>

                        <a href="checkout.html" class="btn-cart-checkout">
                            PROCEED TO CHECKOUT <i class="fa-solid fa-arrow-right"></i>
                        </a>
                    </div>

                    <div class="cart-trust-box">
                        <div class="cart-trust-item">
                            <i class="fa-solid fa-shield-halved"></i>
                            <span>Secure online payments powered by <strong>VietQR Gateway</strong>.</span>
                        </div>
                        <div class="cart-trust-item">
                            <i class="fa-solid fa-car-side"></i>
                            <span>Guaranteed on-time pickup with modern private vehicles.</span>
                        </div>
                        <div class="cart-trust-item">
                            <i class="fa-solid fa-headset"></i>
                            <span>24/7 Hotline Support: <strong>+84567599998</strong></span>
                        </div>
                    </div>
                </div>
            </div>`;
    }

    root.innerHTML = cartHtml;
}
</script>
{$footer}
HTML;
    }

    private static function buildStaticCheckout($seedProductsJson, $seedCategoriesJson, $seedCartJson, $seedOrdersJson) {
        $header = self::getStaticHeader("Checkout - Buddy Private Tour");
        $footer = self::getStaticFooter();

        return <<<HTML
{$header}
<div class="site-breadcrumb-wrap container">
    <nav class="site-breadcrumb-box" id="checkout-breadcrumbs">
        <a href="index.html" class="crumb-link">
            <i class="fa-solid fa-house"></i>Home
        </a>
        <span class="crumb-separator">/</span>
        <span class="crumb-current">Checkout</span>
    </nav>
</div>

<div class="section-padding checkout-section" style="background: #f8fafc; padding: 2rem 0 4rem 0;">
    <div class="container">
        <div class="woocommerce checkout-native-wrapper" style="background: #ffffff; padding: 2.5rem 3rem; border-radius: 12px; border: 1px solid #e2e8f0; box-shadow: 0 4px 20px rgba(0,0,0,0.04); max-width: 1050px; margin: 0 auto;" id="checkout-root"></div>
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
            <div class="cart-empty-wrapper" style="text-align:center; padding: 3rem 1rem;">
                <div class="cart-empty-icon" style="font-size: 3.5rem; color: #cbd5e1; margin-bottom: 1rem;">
                    <i class="fa-solid fa-cart-shopping"></i>
                </div>
                <h2 class="cart-empty-title" style="color: #0f172a; font-weight: 800; font-size: 1.5rem; margin-bottom: 0.5rem;">Your cart is currently empty!</h2>
                <p class="cart-empty-desc" style="color: #64748b; margin-bottom: 1.5rem;">
                    Explore our private tours across Northern, Central & Southern Vietnam and select your journey.
                </p>
                <a href="tour.html" class="btn-cart-empty-cta" style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.75rem 1.75rem; background: #004b99; color: #fff; border-radius: 6px; font-weight: 700; text-decoration: none;">
                    <i class="fa-solid fa-compass"></i> Explore Tours Now
                </a>
            </div>`;
        return;
    }

    const cartTotal = BPT_Storage.getCartTotal();
    let itemsRows = '';
    cart.forEach(item => {
        itemsRows += `
            <tr class="cart_item" style="border-bottom: 1px solid #f1f5f9;">
                <td class="product-name" style="padding: 0.85rem 0; color: #1e293b; font-size: 0.95rem;">
                    \${item.title}&nbsp; <strong class="product-quantity">&times;&nbsp;\${item.quantity}</strong>
                </td>
                <td class="product-total" style="padding: 0.85rem 0; text-align: right; color: #1e293b; font-size: 0.95rem;">
                    <span class="woocommerce-Price-amount amount">\${BPT_Storage.formatCurrency(item.price * item.quantity)}</span>
                </td>
            </tr>`;
    });

    root.innerHTML = `
        <form name="checkout" method="post" class="checkout woocommerce-checkout" id="static-checkout-form" onsubmit="handleStaticCheckout(event)">
            <!-- Left Column: Customer Details (Billing & Additional) -->
            <div class="col2-set" id="customer_details">
                <div class="col-1">
                    <div class="woocommerce-billing-fields">
                        <h3 style="font-size: 1.35rem; font-weight: 800; color: #0f172a; margin-bottom: 1.25rem;">Billing details</h3>
                        <div class="woocommerce-billing-fields__field-wrapper">
                            <p class="form-row form-row-first validate-required" id="billing_first_name_field">
                                <label for="billing_first_name">First name&nbsp;<abbr class="required" title="required">*</abbr></label>
                                <span class="woocommerce-input-wrapper">
                                    <input type="text" class="input-text" name="billing_first_name" id="billing_first_name" placeholder="" value="" required autocomplete="given-name">
                                </span>
                            </p>
                            <p class="form-row form-row-last validate-required" id="billing_last_name_field">
                                <label for="billing_last_name">Last name&nbsp;<abbr class="required" title="required">*</abbr></label>
                                <span class="woocommerce-input-wrapper">
                                    <input type="text" class="input-text" name="billing_last_name" id="billing_last_name" placeholder="" value="" required autocomplete="family-name">
                                </span>
                            </p>
                            <div class="clear"></div>

                            <p class="form-row form-row-wide address-field update_totals_on_change validate-required" id="billing_country_field">
                                <label for="billing_country">Country / Region&nbsp;<abbr class="required" title="required">*</abbr></label>
                                <span class="woocommerce-input-wrapper">
                                    <select name="billing_country" id="billing_country" class="country_to_state country_select" autocomplete="country">
                                        <option value="VN" selected="selected">Vietnam</option>
                                        <option value="US">United States (US)</option>
                                        <option value="AU">Australia</option>
                                        <option value="GB">United Kingdom (UK)</option>
                                        <option value="SG">Singapore</option>
                                        <option value="OTHER">Other Country</option>
                                    </select>
                                </span>
                            </p>

                            <p class="form-row form-row-wide address-field validate-required" id="billing_address_1_field">
                                <label for="billing_address_1">Street address&nbsp;<abbr class="required" title="required">*</abbr></label>
                                <span class="woocommerce-input-wrapper">
                                    <input type="text" class="input-text" name="billing_address_1" id="billing_address_1" placeholder="House number and street name" value="" required autocomplete="address-line1">
                                </span>
                            </p>
                            <p class="form-row form-row-wide address-field" id="billing_address_2_field">
                                <span class="woocommerce-input-wrapper">
                                    <input type="text" class="input-text" name="billing_address_2" id="billing_address_2" placeholder="Apartment, suite, unit, etc. (optional)" value="" autocomplete="address-line2">
                                </span>
                            </p>

                            <p class="form-row form-row-wide address-field validate-postcode" id="billing_postcode_field">
                                <label for="billing_postcode">Postcode / ZIP <span class="optional">(optional)</span></label>
                                <span class="woocommerce-input-wrapper">
                                    <input type="text" class="input-text" name="billing_postcode" id="billing_postcode" placeholder="" value="" autocomplete="postal-code">
                                </span>
                            </p>

                            <p class="form-row form-row-wide address-field validate-required" id="billing_city_field">
                                <label for="billing_city">Town / City&nbsp;<abbr class="required" title="required">*</abbr></label>
                                <span class="woocommerce-input-wrapper">
                                    <input type="text" class="input-text" name="billing_city" id="billing_city" placeholder="" value="" required autocomplete="address-level2">
                                </span>
                            </p>

                            <p class="form-row form-row-wide validate-phone" id="billing_phone_field">
                                <label for="billing_phone">Phone <span class="optional">(optional)</span></label>
                                <span class="woocommerce-input-wrapper">
                                    <input type="tel" class="input-text" name="billing_phone" id="billing_phone" placeholder="" value="" autocomplete="tel">
                                </span>
                            </p>

                            <p class="form-row form-row-wide validate-required validate-email" id="billing_email_field">
                                <label for="billing_email">Email address&nbsp;<abbr class="required" title="required">*</abbr></label>
                                <span class="woocommerce-input-wrapper">
                                    <input type="email" class="input-text" name="billing_email" id="billing_email" placeholder="" value="" required autocomplete="email">
                                </span>
                            </p>
                        </div>
                    </div>
                </div>

                <div class="col-2">
                    <div class="woocommerce-additional-fields">
                        <h3 style="font-size: 1.35rem; font-weight: 800; color: #0f172a; margin-bottom: 1.25rem;">Additional information</h3>
                        <div class="woocommerce-additional-fields__field-wrapper">
                            <p class="form-row notes" id="order_comments_field" style="margin-bottom: 1rem;">
                                <label for="order_comments" style="display: block; font-weight: 700; font-size: 0.9rem; margin-bottom: 0.4rem; color: #334155;">Order notes <span style="font-weight: normal; color: #64748b;">(optional)</span></label>
                                <textarea name="order_comments" class="input-text" id="order_comments" placeholder="Notes about your order, e.g. special notes for delivery." rows="3" style="width: 100%; padding: 0.7rem 0.85rem; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 0.95rem;"></textarea>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Order Review & VietQR Gateway -->
            <div id="order_review" class="woocommerce-checkout-review-order">
                <table class="shop_table woocommerce-checkout-review-order-table" style="width: 100%; border-collapse: collapse; margin-bottom: 1.5rem;">
                    <thead>
                        <tr>
                            <th class="product-name" style="padding: 0.75rem 0; border-bottom: 1px solid #e2e8f0; font-weight: 700; color: #0f172a; text-align: left;">Product</th>
                            <th class="product-total" style="padding: 0.75rem 0; border-bottom: 1px solid #e2e8f0; font-weight: 700; color: #0f172a; text-align: right;">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        \${itemsRows}
                    </tbody>
                    <tfoot>
                        <tr class="cart-subtotal">
                            <th style="padding: 0.75rem 0; border-bottom: 1px solid #f1f5f9; text-align: left; font-weight: 700; color: #0f172a;">Subtotal</th>
                            <td style="padding: 0.75rem 0; border-bottom: 1px solid #f1f5f9; text-align: right; font-weight: 700; color: #0f172a;"><span class="woocommerce-Price-amount amount">\${BPT_Storage.formatCurrency(cartTotal)}</span></td>
                        </tr>
                        <tr class="order-total">
                            <th style="padding: 0.85rem 0; text-align: left; font-size: 1.1rem; font-weight: 800; color: #0f172a;">Total</th>
                            <td style="padding: 0.85rem 0; text-align: right;"><strong style="font-size: 1.2rem; font-weight: 800; color: #dc2626;"><span class="woocommerce-Price-amount amount">\${BPT_Storage.formatCurrency(cartTotal)}</span></strong></td>
                        </tr>
                    </tfoot>
                </table>

                <div id="payment" class="woocommerce-checkout-payment" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 1.25rem; margin-top: 1rem;">
                    <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 1.25rem; margin-bottom: 1rem;">
                        <div style="margin-bottom: 0.85rem; text-align: center;">
                            <img src="https://cdn.vietqr.io/img/vietqr.png" alt="VietQR" style="height: 24px; display: inline-block;">
                        </div>
                        
                        <div class="vietqr-row">
                            <div class="list-bank"><div class="list-bank-box"><img src="https://cdn.vietqr.io/img/VCB.png" alt="Vietcombank"></div></div>
                            <div class="list-bank"><div class="list-bank-box"><img src="https://cdn.vietqr.io/img/ICB.png" alt="VietinBank"></div></div>
                            <div class="list-bank"><div class="list-bank-box"><img src="https://cdn.vietqr.io/img/BIDV.png" alt="BIDV"></div></div>
                            <div class="list-bank"><div class="list-bank-box"><img src="https://cdn.vietqr.io/img/VBA.png" alt="Agribank"></div></div>
                            <div class="list-bank"><div class="list-bank-box"><img src="https://cdn.vietqr.io/img/MB.png" alt="MBBank"></div></div>
                            <div class="list-bank"><div class="list-bank-box"><img src="https://cdn.vietqr.io/img/TCB.png" alt="Techcombank"></div></div>
                            <div class="list-bank"><div class="list-bank-box"><img src="https://cdn.vietqr.io/img/ACB.png" alt="ACB"></div></div>
                            <div class="list-bank"><div class="list-bank-box"><img src="https://cdn.vietqr.io/img/VPB.png" alt="VPBank"></div></div>
                            <div class="list-bank"><div class="list-bank-box"><img src="https://cdn.vietqr.io/img/TPB.png" alt="TPBank"></div></div>
                            <div class="list-bank"><div class="list-bank-box"><img src="https://cdn.vietqr.io/img/STB.png" alt="Sacombank"></div></div>
                            <div class="list-bank"><div class="list-bank-box"><img src="https://cdn.vietqr.io/img/HDB.png" alt="HDBank"></div></div>
                            <div class="list-bank"><div class="list-bank-box"><img src="https://cdn.vietqr.io/img/VIB.png" alt="VIB"></div></div>
                            <div class="list-bank"><div class="list-bank-box"><img src="https://cdn.vietqr.io/img/SHB.png" alt="SHB"></div></div>
                            <div class="list-bank"><div class="list-bank-box"><img src="https://cdn.vietqr.io/img/TIMO.png" alt="Timo"></div></div>
                            <div class="list-bank"><div class="list-bank-box"><img src="https://cdn.vietqr.io/img/MOMO.png" alt="MoMo"></div></div>
                            <div style="grid-column: 1 / -1; text-align: center; font-size: 0.82rem; color: #64748b; font-weight: 500; padding: 4px 0 2px 0;">và hơn 40+ ngân hàng & ví điện tử khác</div>
                        </div>
                    </div>

                    <p style="font-size: 0.85rem; color: #475569; line-height: 1.5; margin: 1rem 0;">
                        Your personal data will be used to process your order, support your experience throughout this website, and for other purposes described in our privacy policy.
                    </p>

                    <div class="form-row place-order">
                        <button type="submit" class="button alt" name="woocommerce_checkout_place_order" id="place_order" value="Place order" style="width: 100%; padding: 0.9rem 1.5rem; background: #004b99; color: #ffffff; font-weight: 700; font-size: 1.05rem; border: none; border-radius: 4px; cursor: pointer; transition: background 0.2s ease;">
                            Place order
                        </button>
                    </div>
                </div>
            </div>
        </form>
    `;
}

function handleStaticCheckout(e) {
    e.preventDefault();
    const firstName = (document.getElementById('billing_first_name') ? document.getElementById('billing_first_name').value : '').trim();
    const lastName = (document.getElementById('billing_last_name') ? document.getElementById('billing_last_name').value : '').trim();
    const name = (firstName + ' ' + lastName).trim() || firstName || lastName;
    const country = (document.getElementById('billing_country') ? document.getElementById('billing_country').value : 'VN');
    const address = (document.getElementById('billing_address_1') ? document.getElementById('billing_address_1').value : '').trim();
    const city = (document.getElementById('billing_city') ? document.getElementById('billing_city').value : '').trim();
    const phone = (document.getElementById('billing_phone') ? document.getElementById('billing_phone').value : '').trim();
    const email = (document.getElementById('billing_email') ? document.getElementById('billing_email').value : '').trim();
    const notes = (document.getElementById('order_comments') ? document.getElementById('order_comments').value : '').trim();

    const cartTotal = BPT_Storage.getCartTotal();
    const cartItems = BPT_Storage.getCart();
    const res = BPT_Storage.placeOrder({ 
        name: name, 
        phone: phone, 
        email: email, 
        country: country,
        address: address,
        city: city,
        notes: notes, 
        payment_method: 'Quét mã QR (VietQR)'
    });
    
    if (res.success) {
        // Update breadcrumb
        const breadcrumbEl = document.getElementById('checkout-breadcrumbs');
        if (breadcrumbEl) {
            breadcrumbEl.innerHTML = `
                <a href="index.html" class="crumb-link"><i class="fa-solid fa-house"></i>Home</a>
                <span class="crumb-separator">/</span>
                <a href="checkout.html" class="crumb-link">Checkout</a>
                <span class="crumb-separator">/</span>
                <span class="crumb-current">Order Confirmation</span>
            `;
        }

        const today = new Date().toLocaleDateString('en-GB', { day: 'numeric', month: 'long', year: 'numeric' });
        const qrUrl = 'https://img.vietqr.io/image/970436-9782794280-compact.jpg?amount=' + cartTotal + '&addInfo=' + encodeURIComponent(res.order_code) + '&accountName=' + encodeURIComponent('NGUYEN VAN KIEN');
        
        let orderItemsRows = '';
        cartItems.forEach(it => {
            orderItemsRows += `
                <tr class="woocommerce-table__line-item order_item" style="border-bottom: 1px solid #f1f5f9;">
                    <td class="woocommerce-table__product-name product-name" style="padding: 0.75rem 1rem; color: #1e293b;">
                        \${it.title} <strong class="product-quantity">&times;&nbsp;\${it.quantity}</strong>
                    </td>
                    <td class="woocommerce-table__product-total product-total" style="padding: 0.75rem 1rem; text-align: right; color: #1e293b;">
                        <span class="woocommerce-Price-amount amount">\${BPT_Storage.formatCurrency(it.price * it.quantity)}</span>
                    </td>
                </tr>`;
        });

        document.getElementById('checkout-root').innerHTML = `
            <div class="woocommerce-order" style="padding: 0.5rem 0;">
                <p class="woocommerce-notice woocommerce-notice--success woocommerce-thankyou-order-received" style="font-size: 1rem; color: #334155; margin-bottom: 1.5rem;">
                    Thank you. Your order has been received.
                </p>

                <!-- Top Order Overview Bar -->
                <ul class="woocommerce-order-overview woocommerce-thankyou-order-details order_details" style="display: flex; flex-wrap: wrap; gap: 2.5rem; list-style: none; padding: 0 0 1.5rem 0; border-bottom: 1px dashed #e2e8f0; margin: 0 0 2rem 0; font-size: 0.92rem;">
                    <li class="woocommerce-order-overview__order order">
                        <span style="font-size: 0.75rem; color: #64748b; text-transform: uppercase; display: block; margin-bottom: 0.2rem;">ORDER NUMBER:</span>
                        <strong style="color: #0f172a; font-size: 1.05rem;">\${res.order_num}</strong>
                    </li>
                    <li class="woocommerce-order-overview__date date">
                        <span style="font-size: 0.75rem; color: #64748b; text-transform: uppercase; display: block; margin-bottom: 0.2rem;">DATE:</span>
                        <strong style="color: #0f172a; font-size: 1rem;">\${today}</strong>
                    </li>
                    <li class="woocommerce-order-overview__email email">
                        <span style="font-size: 0.75rem; color: #64748b; text-transform: uppercase; display: block; margin-bottom: 0.2rem;">EMAIL:</span>
                        <strong style="color: #0f172a; font-size: 1rem;">\${email || 'kiennv.24itb@vku.udn.vn'}</strong>
                    </li>
                    <li class="woocommerce-order-overview__total total">
                        <span style="font-size: 0.75rem; color: #64748b; text-transform: uppercase; display: block; margin-bottom: 0.2rem;">TOTAL:</span>
                        <strong style="color: #0f172a; font-size: 1.05rem;">\${BPT_Storage.formatCurrency(cartTotal)}</strong>
                    </li>
                </ul>

                <!-- Bank transfer QR code Section -->
                <div class="woocommerce-vietqr-section" style="text-align: center; margin: 2.5rem 0 2rem;">
                    <h2 style="font-size: 1.45rem; font-weight: 800; color: #0f172a; margin-bottom: 1.25rem;">Bank transfer QR code</h2>
                    
                    <div class="vietqr-card-box" style="display: inline-block; background: #ffffff; padding: 1rem; border-radius: 12px; border: 1px solid #e2e8f0; box-shadow: 0 4px 16px rgba(0,0,0,0.06); max-width: 320px;">
                        <img src="\${qrUrl}" alt="VietQR Payment Code" style="width: 100%; max-width: 280px; height: auto; border-radius: 8px; display: block;">
                    </div>
                </div>

                <!-- Our bank details Section -->
                <div class="woocommerce-bank-details-section" style="text-align: center; margin: 2rem 0;">
                    <h2 style="font-size: 1.45rem; font-weight: 800; color: #0f172a; margin-bottom: 0.5rem;">Our bank details</h2>
                    
                    <h4 style="color: red; text-align: center; margin-bottom: 1.25rem; font-weight: 700; font-size: 1.05rem;">
                        Vui lòng chuyển đúng nội dung <b style="color: red;">\${res.order_code}</b> để chúng tôi có thể xác nhận thanh toán
                    </h4>

                    <table class="table table-bordered" style="font-size: 15px; max-width: 600px; margin-left: auto; margin-right: auto; width: 100%; border-collapse: collapse; border: 1px solid #dee2e6;">
                        <tbody>
                            <tr style="border-bottom: 1px solid #dee2e6;">
                                <td class="text-right width-column-25" style="text-align: right; width: 35%; padding: 10px 16px; border: 1px solid #dee2e6;">
                                    <strong style="color: #0f172a;">Tên tài khoản:</strong>
                                </td>
                                <td class="text-left payment-instruction width-column-25" style="text-align: left; padding: 10px 16px; border: 1px solid #dee2e6; color: #0f172a;">
                                    NGUYEN VAN KIEN
                                </td>
                            </tr>
                            <tr style="border-bottom: 1px solid #dee2e6; background-color: #FBFBFB;">
                                <td class="text-right width-column-25" style="text-align: right; width: 35%; padding: 10px 16px; border: 1px solid #dee2e6;">
                                    <strong style="color: #0f172a;">Số tài khoản:</strong>
                                </td>
                                <td class="text-left payment-instruction width-column-25" style="text-align: left; padding: 10px 16px; border: 1px solid #dee2e6; color: #0f172a;">
                                    9782794280
                                </td>
                            </tr>
                            <tr style="border-bottom: 1px solid #dee2e6;">
                                <td class="text-right width-column-25" style="text-align: right; width: 35%; padding: 10px 16px; border: 1px solid #dee2e6;">
                                    <strong style="color: #0f172a;">Ngân hàng:</strong>
                                </td>
                                <td class="text-left payment-instruction width-column-25" style="text-align: left; padding: 10px 16px; border: 1px solid #dee2e6; color: #0f172a;">
                                    Ngân hàng TMCP Ngoại Thương Việt Nam
                                </td>
                            </tr>
                            <tr style="border-bottom: 1px solid #dee2e6;">
                                <td class="text-right width-column-25" style="text-align: right; width: 35%; padding: 10px 16px; border: 1px solid #dee2e6;">
                                    <strong style="color: #0f172a;">Số tiền:</strong>
                                </td>
                                <td class="text-left payment-instruction width-column-25" style="text-align: left; padding: 10px 16px; border: 1px solid #dee2e6; color: #0f172a;">
                                    \${cartTotal}<sup>vnd</sup>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Order details Table -->
                <section class="woocommerce-order-details" style="margin-top: 2.5rem;">
                    <h2 style="font-size: 1.35rem; font-weight: 800; color: #0f172a; margin-bottom: 1rem;">Order details</h2>
                    <table class="woocommerce-table woocommerce-table--order-details shop_table order_details" style="width: 100%; border-collapse: collapse; border: 1px solid #e2e8f0; font-size: 0.92rem; margin-bottom: 2rem;">
                        <thead>
                            <tr style="background: #f8fafc; border-bottom: 1px solid #e2e8f0;">
                                <th class="woocommerce-table__product-name product-name" style="padding: 0.75rem 1rem; text-align: left; font-weight: 700; color: #0f172a;">Product</th>
                                <th class="woocommerce-table__product-table product-total" style="padding: 0.75rem 1rem; text-align: right; font-weight: 700; color: #0f172a;">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            \${orderItemsRows}
                        </tbody>
                        <tfoot>
                            <tr style="border-top: 1px solid #e2e8f0;">
                                <th scope="row" style="padding: 0.75rem 1rem; text-align: left; color: #0f172a;">Subtotal:</th>
                                <td style="padding: 0.75rem 1rem; text-align: right; font-weight: 700; color: #0f172a;"><span class="woocommerce-Price-amount amount">\${BPT_Storage.formatCurrency(cartTotal)}</span></td>
                            </tr>
                            <tr style="border-top: 1px solid #e2e8f0;">
                                <th scope="row" style="padding: 0.75rem 1rem; text-align: left; color: #0f172a;">Total:</th>
                                <td style="padding: 0.75rem 1rem; text-align: right; font-weight: 700; color: #0f172a;"><span class="woocommerce-Price-amount amount">\${BPT_Storage.formatCurrency(cartTotal)}</span></td>
                            </tr>
                        </tfoot>
                    </table>
                </section>

                <!-- Billing address Section -->
                <section class="woocommerce-customer-details" style="margin-top: 2rem;">
                    <h2 style="font-size: 1.35rem; font-weight: 800; color: #0f172a; margin-bottom: 1rem;">Billing address</h2>
                    <div style="border: 1px solid #e2e8f0; border-radius: 8px; padding: 1.25rem 1.5rem; background: #ffffff; color: #334155; line-height: 1.6; font-size: 0.92rem;">
                        <div style="font-weight: 700; color: #0f172a; margin-bottom: 0.25rem;">\${name || 'NGUYỄN -KIÊN'}</div>
                        <div>\${address || 'quận 10'}</div>
                        <div>\${city || 'đà nẵng'}</div>
                        <div style="margin-top: 0.35rem;"><i class="fa-solid fa-phone" style="font-size: 0.8rem; margin-right: 0.35rem; color: #64748b;"></i> \${phone || '0782794280'}</div>
                        <div><i class="fa-solid fa-envelope" style="font-size: 0.8rem; margin-right: 0.35rem; color: #64748b;"></i> \${email || 'kiennv.24itb@vku.udn.vn'}</div>
                    </div>
                </section>

                <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap; margin-top: 2.5rem;">
                    <a href="tour.html" class="btn-secondary" style="padding: 0.75rem 1.5rem; border-radius: 6px; text-decoration: none; border: 1px solid #cbd5e1; color: #334155; font-weight: 600; font-size: 0.92rem;">
                        <i class="fa-solid fa-compass"></i> Explore More Tours
                    </a>
                    <a href="index.html" class="btn-primary" style="padding: 0.75rem 1.5rem; border-radius: 6px; background: #004b99; color: #fff; text-decoration: none; font-weight: 700; font-size: 0.92rem;">
                        <i class="fa-solid fa-house"></i> Back to Homepage
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

// Instantiate controller
BPT_ExportController::init();

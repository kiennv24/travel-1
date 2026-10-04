<?php
/**
 * Category & Product Database Models
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

class BPT_CategoryModel {
    public static function getAll() {
        global $wpdb;
        $cats = [];
        if (taxonomy_exists('product_cat')) {
            $terms = get_terms([
                'taxonomy'   => 'product_cat',
                'hide_empty' => false,
            ]);
            if (!empty($terms) && !is_wp_error($terms)) {
                foreach ($terms as $t) {
                    if ($t->slug === 'uncategorized') continue;
                    $cats[] = [
                        'id'   => $t->term_id,
                        'name' => $t->name,
                        'slug' => $t->slug
                    ];
                }
            }
        }
        if (empty($cats)) {
            $cats = $wpdb->get_results("SELECT * FROM categories ORDER BY id ASC", ARRAY_A) ?: [];
        }

        $getOrder = function($slug, $name) {
            $s = strtolower($slug . ' ' . $name);
            if (strpos($s, 'north') !== false || strpos($s, 'bac') !== false || strpos($s, 'hà nội') !== false || strpos($s, 'ha noi') !== false) return 1;
            if (strpos($s, 'central') !== false || strpos($s, 'trung') !== false || strpos($s, 'đà nẵng') !== false || strpos($s, 'da nang') !== false) return 2;
            if (strpos($s, 'south') !== false || strpos($s, 'nam') !== false || strpos($s, 'hồ chí minh') !== false || strpos($s, 'hcm') !== false) return 3;
            if (strpos($s, 'other') !== false || strpos($s, 'xe') !== false || strpos($s, 'service') !== false) return 4;
            return 99;
        };

        usort($cats, function($a, $b) use ($getOrder) {
            return $getOrder($a['slug'], $a['name']) <=> $getOrder($b['slug'], $b['name']);
        });

        return $cats;
    }
}

class BPT_ProductModel {
    public static function getAll() {
        global $wpdb;

        // 1. Fetch from WooCommerce Products so any newly added product in WooCommerce admin appears immediately
        if (post_type_exists('product')) {
            $posts = get_posts([
                'post_type'   => 'product',
                'post_status' => 'publish',
                'numberposts' => -1,
                'orderby'     => 'menu_order date',
                'order'       => 'ASC'
            ]);
            if (!empty($posts)) {
                $list = [];
                foreach ($posts as $post) {
                    $item = self::fromWcPost($post);
                    if ($item) {
                        $list[] = $item;
                    }
                }
                if (!empty($list)) {
                    return $list;
                }
            }
        }

        // 2. Fallback to custom products table
        $sql = "SELECT p.*, c.name as category_name, c.slug as category_slug 
                FROM products p 
                JOIN categories c ON p.category_id = c.id 
                ORDER BY p.id ASC";
        $results = $wpdb->get_results($sql, ARRAY_A);
        if (!empty($results)) {
            return $results;
        }

        return [];
    }

    public static function findById($id) {
        global $wpdb;
        if (empty($id)) return null;

        // 1. Try WooCommerce post by ID
        $post = get_post($id);
        if ($post && $post->post_type === 'product') {
            return self::fromWcPost($post);
        }

        // 2. Try finding WooCommerce product by meta _bpt_product_id
        $wc_post_id = $wpdb->get_var($wpdb->prepare(
            "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_bpt_product_id' AND meta_value = %s LIMIT 1",
            $id
        ));
        if ($wc_post_id) {
            $post = get_post($wc_post_id);
            if ($post) {
                return self::fromWcPost($post);
            }
        }

        // 3. Fallback to custom products table
        $sql = $wpdb->prepare(
            "SELECT p.*, c.name as category_name, c.slug as category_slug 
             FROM products p 
             JOIN categories c ON p.category_id = c.id 
             WHERE p.id = %d LIMIT 1",
            $id
        );
        $row = $wpdb->get_row($sql, ARRAY_A);
        if ($row) {
            return $row;
        }

        return null;
    }

    public static function findBySlug($slug) {
        global $wpdb;
        if (empty($slug)) return null;

        // 1. Try WooCommerce product post by slug
        $post = get_page_by_path($slug, OBJECT, 'product');
        if ($post) {
            return self::fromWcPost($post);
        }

        // 2. Fallback to custom products table
        $sql = $wpdb->prepare(
            "SELECT p.*, c.name as category_name, c.slug as category_slug 
             FROM products p 
             JOIN categories c ON p.category_id = c.id 
             WHERE p.slug = %s LIMIT 1",
            $slug
        );
        $row = $wpdb->get_row($sql, ARRAY_A);
        if ($row) {
            return $row;
        }

        return null;
    }

    /**
     * Convert a WooCommerce WP_Post or WC_Product into the standard Tour array
     */
    public static function fromWcPost($post) {
        if (is_numeric($post)) {
            $post = get_post($post);
        }
        if (!$post) return null;

        $post_id = $post->ID;
        $price = (float)get_post_meta($post_id, '_price', true);
        if (!$price) {
            $price = (float)get_post_meta($post_id, '_regular_price', true);
        }

        $formatted_price = get_post_meta($post_id, '_formatted_price', true);
        if (empty($formatted_price) && function_exists('format_currency')) {
            $formatted_price = format_currency($price);
        }

        $duration = get_post_meta($post_id, '_duration', true) ?: 'Full Day';
        $highlights = get_post_meta($post_id, '_highlights', true) ?: '[]';
        $included = get_post_meta($post_id, '_included', true) ?: '[]';
        $excluded = get_post_meta($post_id, '_excluded', true) ?: '[]';

        // Image
        $image_id = get_post_thumbnail_id($post_id);
        $image = $image_id ? wp_get_attachment_url($image_id) : '';

        // Extract any <img> tags from content
        $content_images = [];
        if (!empty($post->post_content)) {
            if (preg_match_all('/<img[^>]+src=["\']([^"\']+)["\']/i', $post->post_content, $matches)) {
                if (!empty($matches[1])) {
                    foreach ($matches[1] as $m_url) {
                        $m_url = trim($m_url);
                        if (!empty($m_url) && !in_array($m_url, $content_images)) {
                            $content_images[] = $m_url;
                        }
                    }
                }
            }
        }

        if (empty($image) && !empty($content_images)) {
            $image = $content_images[0];
        }

        // Helper to normalize image filename (removes WP dimension suffix -300x225 etc.)
        $get_base_key = function($url) {
            $path = parse_url($url, PHP_URL_PATH);
            $filename = basename($path);
            return preg_replace('/-\d+x\d+(\.[a-zA-Z0-9]+)$/i', '$1', strtolower($filename));
        };

        // Gallery Images
        $gallery_ids_str = get_post_meta($post_id, '_product_image_gallery', true);
        $gallery_urls = [];
        $seen_keys = [];

        if (!empty($image)) {
            $gallery_urls[] = $image;
            $seen_keys[$get_base_key($image)] = true;
        }

        if (!empty($gallery_ids_str)) {
            $gallery_ids = array_filter(explode(',', $gallery_ids_str));
            foreach ($gallery_ids as $gid) {
                $gurl = wp_get_attachment_url($gid);
                if ($gurl) {
                    $key = $get_base_key($gurl);
                    if (!isset($seen_keys[$key])) {
                        $gallery_urls[] = $gurl;
                        $seen_keys[$key] = true;
                    }
                }
            }
        }

        // Merge content images into gallery (avoid duplicate base images)
        foreach ($content_images as $ci) {
            $key = $get_base_key($ci);
            if (!isset($seen_keys[$key])) {
                $gallery_urls[] = $ci;
                $seen_keys[$key] = true;
            }
        }
        $gallery_urls = array_values(array_filter($gallery_urls));

        // Clean summary (strip HTML tags so <img> tags don't leak into card text)
        $summary = trim(strip_tags($post->post_excerpt));
        if (empty($summary)) {
            $clean_content = trim(strip_tags($post->post_content));
            if (!empty($clean_content)) {
                $summary = wp_trim_words($clean_content, 35, '...');
            }
        }

        // Category
        $terms = wp_get_post_terms($post_id, 'product_cat');
        $cat_name = (!empty($terms) && !is_wp_error($terms)) ? $terms[0]->name : 'Vietnam Tours';
        $cat_slug = (!empty($terms) && !is_wp_error($terms)) ? $terms[0]->slug : 'vietnam-tours';
        $cat_id = (!empty($terms) && !is_wp_error($terms)) ? $terms[0]->term_id : 1;

        $custom_bpt_id = (int)get_post_meta($post_id, '_bpt_product_id', true);

        return [
            'id'              => $custom_bpt_id ?: $post_id,
            'wc_post_id'      => $post_id,
            'slug'            => $post->post_name,
            'title'           => $post->post_title,
            'category_id'     => $cat_id,
            'category_name'   => $cat_name,
            'category_slug'   => $cat_slug,
            'price'           => $price,
            'formatted_price' => $formatted_price,
            'duration'        => $duration,
            'image'           => $image,
            'images'          => json_encode($gallery_urls, JSON_UNESCAPED_SLASHES),
            'summary'         => $summary,
            'description'     => $post->post_content,
            'highlights'      => $highlights,
            'included'        => $included,
            'excluded'        => $excluded,
        ];
    }

    public static function getByCategorySlug($slug) {
        $all = self::getAll();
        return array_values(array_filter($all, function($p) use ($slug) {
            return ($p['category_slug'] ?? '') === $slug;
        }));
    }

    public static function getRelated($categoryId, $excludeId, $limit = 5) {
        global $wpdb;
        $sql = $wpdb->prepare(
            "SELECT p.*, c.name as category_name, c.slug as category_slug 
             FROM products p 
             JOIN categories c ON p.category_id = c.id 
             WHERE p.category_id = %d AND p.id != %d 
             ORDER BY p.id ASC 
             LIMIT %d",
            $categoryId,
            $excludeId,
            $limit
        );
        $results = $wpdb->get_results($sql, ARRAY_A);
        
        // If we don't have enough, fetch others to fill up the limit
        if (count($results) < $limit) {
            $needed = $limit - count($results);
            $exclude_ids = array_merge([$excludeId], array_column($results, 'id'));
            
            // Build placeholders for NOT IN clause
            $placeholders = implode(',', array_fill(0, count($exclude_ids), '%d'));
            
            $sql_fill = $wpdb->prepare(
                "SELECT p.*, c.name as category_name, c.slug as category_slug 
                 FROM products p 
                 JOIN categories c ON p.category_id = c.id 
                 WHERE p.id NOT IN ($placeholders) 
                 ORDER BY p.id ASC 
                 LIMIT %d",
                array_merge($exclude_ids, [$needed])
            );
            $fill_results = $wpdb->get_results($sql_fill, ARRAY_A);
            $results = array_merge($results, $fill_results);
        }
        
        return $results;
    }
}

class BPT_ServiceModel {
    public static function initTable() {
        global $wpdb;
        $table_name = 'services';
        
        if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
            $charset_collate = $wpdb->get_charset_collate();
            $sql = "CREATE TABLE $table_name (
                id bigint(20) NOT NULL AUTO_INCREMENT,
                slug varchar(50) NOT NULL UNIQUE,
                title varchar(150) NOT NULL,
                date_published varchar(50) NOT NULL,
                images text NOT NULL,
                PRIMARY KEY (id)
            ) $charset_collate;";
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($sql);
            
            $wpdb->insert($table_name, [
                'slug' => 'hotel',
                'title' => 'HOTEL',
                'date_published' => '2022-10-25 03:33:37',
                'images' => json_encode([
                    'assets/images/mau-tam-trang-tri-giuong-khach-san-dep-nhat-19.jpg',
                    'https://images.unsplash.com/photo-1566073771259-6a8506099945?auto=format&fit=crop&w=1200&q=80',
                    'https://images.unsplash.com/photo-1590490360182-c33d57733427?auto=format&fit=crop&w=1200&q=80',
                    'https://images.unsplash.com/photo-1576013551627-0cc20b96c2a7?auto=format&fit=crop&w=1200&q=80',
                    'https://images.unsplash.com/photo-1618773928121-c32242e63f39?auto=format&fit=crop&w=1200&q=80'
                ])
            ]);
            $wpdb->insert($table_name, [
                'slug' => 'car-rental',
                'title' => 'CAR RENTAL FROM 4-45',
                'date_published' => '2022-10-25 03:30:12',
                'images' => json_encode([
                    'assets/images/dkt-transport-cung-cap-dich-vu-cho-thue-xe-7-cho-tai-ha-noi-1024x576.jpg',
                    'https://images.unsplash.com/photo-1549399542-7e3f8b79c341?auto=format&fit=crop&w=1200&q=80',
                    'https://images.unsplash.com/photo-1503376780353-7e6692767b70?auto=format&fit=crop&w=1200&q=80',
                    'https://images.unsplash.com/photo-1544620347-c4fd4a3d5957?auto=format&fit=crop&w=1200&q=80',
                    'https://images.unsplash.com/photo-1533473359331-0135ef1b58bf?auto=format&fit=crop&w=1200&q=80'
                ])
            ]);
            $wpdb->insert($table_name, [
                'slug' => 'airline-tickets',
                'title' => 'CHEAP AIRLINE TICKETS',
                'date_published' => '2022-10-25 03:28:45',
                'images' => json_encode([
                    'assets/images/ve-7546-1571044895.jpg',
                    'https://images.unsplash.com/photo-1436491865332-7a61a109cc05?auto=format&fit=crop&w=1200&q=80',
                    'https://images.unsplash.com/photo-1483450388369-9ed95738483c?auto=format&fit=crop&w=1200&q=80',
                    'https://images.unsplash.com/photo-1530521954074-e64f6810b32d?auto=format&fit=crop&w=1200&q=80',
                    'https://images.unsplash.com/photo-1470229722913-7c0e2dbbafd3?auto=format&fit=crop&w=1200&q=80'
                ])
            ]);
        }
    }

    public static function getBySlug($slug) {
        global $wpdb;
        
        // 1. Check if WooCommerce product exists
        $wc_prod = BPT_ProductModel::findBySlug($slug);
        if ($wc_prod && ($wc_prod['category_slug'] ?? '') === 'other-services') {
            $gallery = json_decode($wc_prod['images'] ?? '[]', true) ?: [];
            if (empty($gallery) && !empty($wc_prod['image'])) {
                $gallery = [$wc_prod['image']];
            }
            return [
                'id'             => $wc_prod['id'],
                'slug'           => $wc_prod['slug'],
                'title'          => $wc_prod['title'],
                'date_published' => '2022-10-25 03:30:00',
                'images'         => $gallery,
                'summary'        => $wc_prod['summary'],
                'description'    => $wc_prod['description']
            ];
        }

        // 2. Fallback to services table
        self::initTable();
        $row = $wpdb->get_row($wpdb->prepare("SELECT * FROM services WHERE slug = %s", $slug), ARRAY_A);
        if ($row) {
            $row['images'] = json_decode($row['images'], true) ?: [];
        }
        return $row;
    }

    public static function getAll() {
        // Fetch products in Other Services category
        $prods = BPT_ProductModel::getByCategorySlug('other-services');
        if (!empty($prods)) {
            $list = [];
            foreach ($prods as $p) {
                $gallery = json_decode($p['images'] ?? '[]', true) ?: [];
                if (empty($gallery) && !empty($p['image'])) {
                    $gallery = [$p['image']];
                }
                $list[] = [
                    'id'             => $p['id'],
                    'slug'           => $p['slug'],
                    'title'          => $p['title'],
                    'date_published' => '2022-10-25 03:30:00',
                    'images'         => $gallery,
                    'summary'        => $p['summary'],
                    'description'    => $p['description']
                ];
            }
            return $list;
        }

        global $wpdb;
        self::initTable();
        $rows = $wpdb->get_results("SELECT * FROM services ORDER BY id ASC", ARRAY_A);
        foreach ($rows as &$row) {
            $row['images'] = json_decode($row['images'], true) ?: [];
        }
        return $rows;
    }
}

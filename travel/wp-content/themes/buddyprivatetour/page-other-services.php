<?php
/**
 * Template Name: Other Services Page
 */
get_header();

$raw_services = BPT_ProductModel::getByCategorySlug('other-services');
$services = [];

if (!empty($raw_services)) {
    foreach ($raw_services as $rs) {
        $img = $rs['image'] ?: 'assets/images/dkt-transport-cung-cap-dich-vu-cho-thue-xe-7-cho-tai-ha-noi-1024x576.jpg';
        $services[] = [
            'slug' => $rs['slug'],
            'title' => $rs['title'],
            'desc' => $rs['summary'] ?? '',
            'image' => bpt_resolve_image_url($img),
            'url' => site_url('?pagename=other-services-detail&service=' . $rs['slug'])
        ];
    }
} else {
    $services = [
        [
            'slug' => 'car-rental',
            'title' => 'CAR RENTAL FROM 4-45',
            'desc' => 'Professional private car rental services with clean vehicles, experienced english-speaking drivers, and competitive pricing for travelers in Vietnam.',
            'image' => get_template_directory_uri() . '/assets/images/dkt-transport-cung-cap-dich-vu-cho-thue-xe-7-cho-tai-ha-noi-1024x576.jpg',
            'url' => site_url('?pagename=other-services-detail&service=car-rental')
        ],
        [
            'slug' => 'hotel',
            'title' => 'HOTEL',
            'desc' => 'Hand-picked accommodation recommendations, hotel bookings, and custom holiday stays across Vietnam ranging from luxury resorts to local homestays.',
            'image' => get_template_directory_uri() . '/assets/images/mau-tam-trang-tri-giuong-khach-san-dep-nhat-19.jpg',
            'url' => site_url('?pagename=other-services-detail&service=hotel')
        ],
        [
            'slug' => 'airline-tickets',
            'title' => 'CHEAP AIRLINE TICKETS',
            'desc' => 'Domestic and international flight ticketing services with optimized routes, cheap fares, and full-support booking management.',
            'image' => get_template_directory_uri() . '/assets/images/ve-7546-1571044895.jpg',
            'url' => site_url('?pagename=other-services-detail&service=airline-tickets')
        ]
    ];
}
?>

<div class="section-padding" style="background: #f8fafc; min-height: 80vh; padding: 2rem 0 4rem 0;">
    <div class="container">
        <!-- Breadcrumb Navigation -->
        <div class="site-breadcrumb-wrap" style="padding-top: 0; margin-bottom: 1.75rem;">
            <nav class="site-breadcrumb-box">
                <a href="<?php echo esc_url(home_url('/')); ?>" class="crumb-link">
                    <i class="fa-solid fa-house"></i>Home
                </a>
                <span class="crumb-separator">/</span>
                <span class="crumb-current">Other Services</span>
            </nav>
        </div>

        <h1 style="font-size: 2.2rem; font-weight: 800; color: #0c4a85; margin-bottom: 2.5rem; text-transform: uppercase;">OTHER SERVICES</h1>

        <div class="services-list" style="display: flex; flex-direction: column; gap: 2rem; max-width: 960px; margin: 0 auto;">
            <?php foreach ($services as $srv) : ?>
                <div class="service-item" style="background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden; display: flex; box-shadow: 0 4px 12px rgba(0,0,0,0.03);">
                    <div style="flex: 1; max-width: 340px; min-width: 260px; height: 210px; overflow: hidden;">
                        <a href="<?php echo esc_url($srv['url']); ?>" style="display: block; width: 100%; height: 100%;">
                            <img src="<?php echo esc_url($srv['image']); ?>" alt="<?php echo esc_attr($srv['title']); ?>" style="width: 100%; height: 100%; object-fit: cover;">
                        </a>
                    </div>
                    <div style="flex: 2; padding: 1.75rem 2rem; display: flex; flex-direction: column; justify-content: center; align-items: flex-start; gap: 0.85rem;">
                        <h3 style="font-size: 1.35rem; font-weight: 800; color: #1e293b; text-transform: uppercase; margin: 0;">
                            <a href="<?php echo esc_url($srv['url']); ?>" style="color: inherit; text-decoration: none;">
                                <?php echo esc_html($srv['title']); ?>
                            </a>
                        </h3>
                        <?php if (!empty($srv['desc'])) : ?>
                            <p style="color: #64748b; font-size: 0.95rem; line-height: 1.6; margin: 0;">
                                <?php echo esc_html($srv['desc']); ?>
                            </p>
                        <?php endif; ?>
                        <a href="<?php echo esc_url($srv['url']); ?>" class="btn-primary" style="background: #004b99; border-radius: 4px; padding: 0.6rem 1.6rem; font-size: 0.9rem; font-weight: 700; color: #fff; text-decoration: none; display: inline-flex; align-items: center; gap: 0.4rem;">
                            Read more <i class="fa-solid fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php get_footer(); ?>

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

<div class="section-padding" style="background: #f8fafc; min-height: 80vh; padding: 2rem 0 5rem 0;">
    <div class="container">
        <!-- Breadcrumb Navigation -->
        <div class="site-breadcrumb-wrap" style="padding-top: 0; margin-bottom: 1.75rem;">
            <nav class="site-breadcrumb-box">
                <a href="<?php echo esc_url(home_url('/')); ?>" class="crumb-link">
                    <i class="fa-solid fa-house"></i> <span data-i18n="crumb_home">Home</span>
                </a>
                <span class="crumb-separator">/</span>
                <span class="crumb-current" data-i18n="nav_other_services">Car Rental & Hotels</span>
            </nav>
        </div>

        <h1 style="font-size: 2.2rem; font-weight: 800; color: #0f172a; margin-bottom: 2.5rem; text-transform: uppercase;" data-i18n="nav_other_services">CAR RENTAL & TRAVEL SERVICES</h1>

        <div class="tours-grid">
            <?php foreach ($services as $srv) : ?>
                <div class="tour-card">
                    <div class="tour-card-image">
                        <a href="<?php echo esc_url($srv['url']); ?>">
                            <img src="<?php echo esc_url($srv['image']); ?>" alt="<?php echo esc_attr($srv['title']); ?>" loading="lazy">
                        </a>
                        <span class="tour-badge-private"><i class="fa-solid fa-star"></i> <span>Premium Service</span></span>
                    </div>
                    <div class="tour-card-body">
                        <h3 class="tour-card-title">
                            <a href="<?php echo esc_url($srv['url']); ?>"><?php echo esc_html($srv['title']); ?></a>
                        </h3>
                        <?php if (!empty($srv['desc'])) : ?>
                            <p style="color: #64748b; font-size: 0.92rem; line-height: 1.6; margin: 0.5rem 0 1rem;">
                                <?php echo esc_html($srv['desc']); ?>
                            </p>
                        <?php endif; ?>
                        <div class="tour-card-footer" style="border-top: none; margin-top: auto;">
                            <a href="<?php echo esc_url($srv['url']); ?>" class="tour-card-cta-btn" style="width: 100%; justify-content: center;">
                                <span>View Details</span> <i class="fa-solid fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php get_footer(); ?>

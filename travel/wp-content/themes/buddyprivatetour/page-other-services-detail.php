<?php
/**
 * Template Name: Other Services Detail Page
 */

get_header();

// Fetch service details
$service_slug = isset($_GET['service']) ? sanitize_key($_GET['service']) : '';

$service = BPT_ServiceModel::getBySlug($service_slug);

if (!$service) {
    // If not found, load 404 page
    include get_template_directory() . '/404.php';
    exit;
}

$service['date'] = $service['date_published'];

// Setup Database comment table if not exists
global $wpdb;
$table_name = $wpdb->prefix . 'service_comments';
if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
    $charset_collate = $wpdb->get_charset_collate();
    $sql = "CREATE TABLE $table_name (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        service_slug varchar(50) NOT NULL,
        author_name varchar(100) NOT NULL,
        author_email varchar(100) NOT NULL,
        author_website varchar(100) DEFAULT '',
        comment_text text NOT NULL,
        created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

// Handle comment submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_comment') {
    $author_name = sanitize_text_field($_POST['author_name']);
    $author_email = sanitize_email($_POST['author_email']);
    $author_website = sanitize_text_field($_POST['author_website']);
    $comment_text = sanitize_textarea_field($_POST['comment_text']);
    
    if ($author_name && $author_email && $comment_text) {
        $wpdb->insert($table_name, [
            'service_slug' => $service_slug,
            'author_name' => $author_name,
            'author_email' => $author_email,
            'author_website' => $author_website,
            'comment_text' => $comment_text,
            'created_at' => current_time('mysql')
        ]);
        
        wp_safe_redirect(add_query_arg('posted', 'true'));
        exit;
    }
}

// Fetch comments from DB
$comments = $wpdb->get_results(
    $wpdb->prepare("SELECT * FROM $table_name WHERE service_slug = %s ORDER BY id DESC", $service_slug),
    ARRAY_A
);
$comments_count = count($comments);
?>

<div class="section-padding services-detail-wrapper">
    <div class="container services-detail-container">
        
        <!-- Breadcrumb bar matching user screenshot -->
        <div class="site-breadcrumb-wrap" style="padding-top: 0; margin-bottom: 1.75rem;">
            <nav class="site-breadcrumb-box">
                <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="crumb-link">
                    <i class="fa-solid fa-house"></i>Home
                </a>
                <span class="crumb-separator">/</span>
                <a href="<?php echo esc_url( site_url( '?pagename=other-services' ) ); ?>" class="crumb-link">
                    Other Services
                </a>
                <span class="crumb-separator">/</span>
                <span class="crumb-current"><?php echo esc_html($service['title']); ?></span>
            </nav>
        </div>

        <!-- Article Card -->
        <div class="services-article-card">
            <h1 class="services-article-title">
                <?php echo esc_html($service['title']); ?>
            </h1>
            <div class="services-meta-bar">
                <span class="services-meta-item"><i class="fa-solid fa-user"></i> by: qcv</span>
                <span class="services-meta-item"><i class="fa-regular fa-folder-open"></i> Other Services</span>
                <span class="services-meta-item"><i class="fa-regular fa-comment"></i> <span id="comment-count-display"><?php echo $comments_count; ?></span></span>
                <span class="services-meta-item"><i class="fa-regular fa-calendar-days"></i> <?php echo esc_html($service['date']); ?></span>
            </div>

            <!-- Vertical list of 5 images -->
            <div class="gallery-stack" style="display: flex; flex-direction: column; gap: 2rem; margin-bottom: 3.5rem; align-items: center;">
                <?php foreach ($service['images'] as $img_url) : ?>
                    <div style="width: 100%; border: 1px solid #e2e8f0; border-radius: 2px; overflow: hidden; background: #fff; box-shadow: 0 1px 4px rgba(0,0,0,0.03);">
                        <img src="<?php echo esc_url(bpt_resolve_image_url($img_url)); ?>" alt="<?php echo esc_attr($service['title']); ?>" style="width: 100%; height: auto; display: block; object-fit: contain; max-height: 550px;">
                    </div>
                <?php endforeach; ?>
            </div>

            <div style="font-size: 0.9rem; color: var(--text-muted); margin-bottom: 1rem;">
                <i class="fa-solid fa-tag"></i> <strong>Tags:</strong> No Tag
            </div>
        </div>

        <!-- Comments Area Card -->
        <div class="services-comments-card">
            
            <!-- List of Comments -->
            <div id="comments-list-section" style="margin-bottom: 3rem; display: <?php echo empty($comments) ? 'none' : 'block'; ?>;">
                <h3 class="services-comments-heading">Comments</h3>
                <div id="comments-container" style="display: flex; flex-direction: column; gap: 1.5rem;">
                    <?php foreach ($comments as $cmt) : ?>
                        <div class="services-comment-item">
                            <div class="services-comment-header">
                                <strong class="services-comment-author"><?php echo esc_html($cmt['author_name']); ?></strong>
                                <span class="services-comment-date"><?php echo esc_html($cmt['created_at']); ?></span>
                            </div>
                            <p class="services-comment-text"><?php echo nl2br(esc_html($cmt['comment_text'])); ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Comment Form -->
            <h3 class="services-comments-heading" style="border-bottom: none; margin-bottom: 0.25rem;">Leave Comments</h3>
            <p style="color: var(--text-muted); font-size: 0.85rem; margin-bottom: 1.5rem;">Your comment will be posted directly below.</p>
            
            <form id="comment-form" action="" method="POST" class="services-comment-form">
                <input type="hidden" name="action" value="add_comment">
                
                <div class="services-form-group-full" style="display: flex; align-items: center; gap: 0.5rem;">
                    <input type="checkbox" id="save_info" style="cursor: pointer;">
                    <label for="save_info" style="font-size: 0.85rem; color: var(--text-muted); cursor: pointer; user-select: none;">Save my name, email, and website in this browser for the next time I comment.</label>
                </div>

                <div>
                    <label for="author_name" style="display: block; font-weight: 700; font-size: 0.9rem; margin-bottom: 0.4rem; color: var(--text-dark);">Name <span style="color: red;">*</span></label>
                    <input type="text" name="author_name" id="author_name" required placeholder="Your full name" class="services-form-input">
                </div>
                <div>
                    <label for="author_email" style="display: block; font-weight: 700; font-size: 0.9rem; margin-bottom: 0.4rem; color: var(--text-dark);">Email <span style="color: red;">*</span></label>
                    <input type="email" name="author_email" id="author_email" required placeholder="Your email address" class="services-form-input">
                </div>

                <div class="services-form-group-full">
                    <label for="author_website" style="display: block; font-weight: 700; font-size: 0.9rem; margin-bottom: 0.4rem; color: var(--text-dark);">Website</label>
                    <input type="text" name="author_website" id="author_website" placeholder="Website (optional)" class="services-form-input">
                </div>

                <div class="services-form-group-full">
                    <label for="comment_text" style="display: block; font-weight: 700; font-size: 0.9rem; margin-bottom: 0.4rem; color: var(--text-dark);">Comment <span style="color: red;">*</span></label>
                    <textarea name="comment_text" id="comment_text" rows="5" required placeholder="Write your comment here..." class="services-form-textarea"></textarea>
                </div>

                <div class="services-form-group-full" style="margin-top: 0.5rem;">
                    <button type="submit" class="services-submit-btn">Post Comment</button>
                </div>
            </form>

        </div>

    </div>
</div>

<!-- LocalStorage Integration script for Static fallbacks -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const serviceSlug = '<?php echo esc_js($service_slug); ?>';
    
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
        }

        // Save into LocalStorage array
        let localComments = JSON.parse(localStorage.getItem('bpt_local_comments_' + serviceSlug) || '[]');
        localComments.unshift({
            name: nameVal,
            email: emailVal,
            website: webVal,
            text: textVal,
            date: new Date().toLocaleString()
        });
        localStorage.setItem('bpt_local_comments_' + serviceSlug, JSON.stringify(localComments));
    });
});
</script>

<?php get_footer(); ?>

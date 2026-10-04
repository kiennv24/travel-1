<?php
/**
 * Template Name: Videos Page
 */
get_header();
?>

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

<?php get_footer(); ?>

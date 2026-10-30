<?php
/**
 * Sample AI Analysis admin page (Pro teaser).
 *
 * @package    WP_TripAdvisor_Review
 * @subpackage WP_TripAdvisor_Review/admin/partials
 */

if ( ! current_user_can( 'manage_options' ) ) {
	return;
}
?>
<div class="">
	<h1></h1>
	<div class="wrap" id="wp_rev_maindiv">
		<img class="wprev_headerimg" src="<?php echo esc_url( plugin_dir_url( __FILE__ ) . 'logo.png' ); ?>">
		<?php include 'tabmenu.php'; ?>

		<div class="wpfbr-pro-feature-panel wpfbr-pro-feature-panel--notice">
			<p style="margin: 0; font-size: 14px;">
				<strong><?php esc_html_e( 'Sample AI Analysis', 'wp-tripadvisor-review-slider' ); ?></strong> —
				<?php esc_html_e( 'This is a sample AI analysis. Generate your own custom analysis by upgrading to the Pro version.', 'wp-tripadvisor-review-slider' ); ?>
				<a href="https://wpreviewslider.com/" target="_blank" rel="noopener noreferrer" style="font-weight: bold; text-decoration: underline;"><?php esc_html_e( 'Upgrade to Pro', 'wp-tripadvisor-review-slider' ); ?></a>
				<span style="margin-left:8px;"><?php esc_html_e( 'Use code', 'wp-tripadvisor-review-slider' ); ?> <code>WPPRO15</code> <?php esc_html_e( 'for 15% off.', 'wp-tripadvisor-review-slider' ); ?></span>
			</p>
		</div>

		<div id="wprevpro_ai_analysis_page">
			<div id="ai_dashboard" class="ai-dashboard" style="display:none">
				<div id="ai_report_heading" class="ai-report-heading">
					<h2 id="ai_report_heading_title"><?php esc_html_e( 'AI Analysis', 'wp-tripadvisor-review-slider' ); ?></h2>
					<div id="ai_report_heading_date" class="ai-report-heading-date"></div>
				</div>
				<div id="ai_alert_banner" class="ai-alert-banner" style="display:none" role="status"></div>

				<div class="ai-hero">
					<div id="ai_hero_kpis" class="ai-hero-kpis"></div>
					<div class="ai-hero-viz">
						<div class="ai-panel ai-hero-doughnut">
							<div class="ai-panel-title"><?php esc_html_e( 'Sentiment Mix', 'wp-tripadvisor-review-slider' ); ?></div>
							<div class="ai-doughnut-wrap">
								<canvas id="ai_sentiment_doughnut"></canvas>
							</div>
							<div id="ai_doughnut_legend" class="ai-doughnut-legend"></div>
						</div>
						<div class="ai-panel ai-hero-timeline sentiment_over_time_div">
							<div class="ai-panel-title"><?php esc_html_e( 'Sentiment Over Time', 'wp-tripadvisor-review-slider' ); ?></div>
							<p class="ai-panel-hint"><?php esc_html_e( 'Click a point to open the reviews from that period.', 'wp-tripadvisor-review-slider' ); ?></p>
							<div class="ai-timeline-wrap">
								<canvas id="ai_sentiment_timeline"></canvas>
							</div>
						</div>
					</div>
				</div>

				<div id="ai_action_center" class="ai-action-center" style="display:none">
					<div class="ai-action-heading">
						<div>
							<div class="ai-kicker"><?php esc_html_e( 'Action Center', 'wp-tripadvisor-review-slider' ); ?></div>
							<h2><?php esc_html_e( 'What to do next', 'wp-tripadvisor-review-slider' ); ?></h2>
						</div>
						<p><?php esc_html_e( 'Check off items as you complete them. These stay on this browser so you can track progress.', 'wp-tripadvisor-review-slider' ); ?></p>
					</div>
					<div class="ai-action-grid">
						<div id="ai_recommendations" class="ai-panel ai-action-recs"></div>
						<div id="ai_review_growth" class="ai-panel ai-action-growth"></div>
					</div>
				</div>

				<div class="ai-sections">
					<div class="ai-section" id="tab-overview">
						<div id="ai_summary_panel"></div>
						<div class="ai-panel executive_overview_div">
							<div class="ai-panel-title"><?php esc_html_e( 'Overview', 'wp-tripadvisor-review-slider' ); ?></div>
							<div id="ai_report_markdown" class="ai-overview-body is-collapsed"></div>
							<button type="button" id="ai_overview_toggle" class="button ai-text-toggle" aria-expanded="false" aria-controls="ai_report_markdown"><?php esc_html_e( 'Read full overview', 'wp-tripadvisor-review-slider' ); ?></button>
						</div>
					</div>
					<div class="ai-section" id="tab-themes">
						<div class="ai-split-grid">
							<div id="ai_themes_panel" class="ai-panel"></div>
							<div id="ai_pain_points_panel" class="ai-panel"></div>
						</div>
					</div>
					<div class="ai-section" id="tab-audience">
						<div id="ai_personas_panel"></div>
						<div id="ai_swot_panel"></div>
					</div>
					<div class="ai-section" id="tab-faqs">
						<div id="ai_faqs_panel" class="ai-panel"></div>
						<div id="ai_notes_panel" class="ai-panel" style="display:none"></div>
					</div>
				</div>

				<div class="ai-dash-footer">
					<button type="button" id="ai_export_md" class="button"><?php esc_html_e( 'Export Full Report', 'wp-tripadvisor-review-slider' ); ?></button>
					<button type="button" id="ai_print_report" class="button"><?php esc_html_e( 'Print Report', 'wp-tripadvisor-review-slider' ); ?></button>
					<button type="button" id="ai_toggle_json" class="button"><?php esc_html_e( 'Show Structured JSON', 'wp-tripadvisor-review-slider' ); ?></button>
					<button type="button" id="ai_export_json" class="button"><?php esc_html_e( 'Export JSON', 'wp-tripadvisor-review-slider' ); ?></button>
				</div>
				<div id="ai_json_container" style="display:none">
					<textarea id="ai_report_json" readonly></textarea>
				</div>
			</div>

			<div id="tb_content_popup" style="display:none;">
				<div id="review_details">
					<div class="wpproslider_t6_DIV_1 w3_wprs-col l12">
						<div class="wpproslider_t6_DIV_2 wprev_preview_bg1 wprev_preview_bradius" style="border: 1px solid rgb(238, 238, 238); border-radius: 0px; background: rgb(253, 253, 253);">
							<div class="wpproslider_t6_STRONG_5 wprev_preview_tcolor2">
								<?php esc_html_e( 'Review Source Details', 'wp-tripadvisor-review-slider' ); ?>
							</div>
							<div class="wpproslider_t6_DIV_4 sourcerevdetails"></div>
						</div>
					</div>
					<div class="wpproslider_t6_DIV_1 w3_wprs-col l12">
						<div class="wpproslider_t6_DIV_2 wprev_preview_bg1 wprev_preview_bradius" style="border: 1px solid rgb(238, 238, 238); border-radius: 0px; background: rgb(253, 253, 253);">
							<div class="wpproslider_t6_DIV_2_top" style="line-height:24px;">
								<div class="wpproslider_t6_DIV_3L">
									<a id="from_url_review" target="_blank">
										<img src="<?php echo esc_url( trailingslashit( wprev_trip_plugin_url ) . 'public/partials/imgs/tripadvisor_mystery_man.png' ); ?>" class="wprev_avatar_opt wpproslider_t6_IMG_2" alt="">
									</a>
								</div>
								<div class="wpproslider_t6_DIV_3">
									<div class="wpproslider_t6_STRONG_5 wprev_preview_tcolor2 t6displayname">
										<span id="wprev_showname"><?php esc_html_e( 'John Smith', 'wp-tripadvisor-review-slider' ); ?></span>
									</div>
									<div class="wpproslider_t6_star_DIV">
										<span id="starloc1" class="wprevpro_star_imgs" style="color: rgb(253, 211, 20);">
											<span class="svgicons svg-wprsp-star"></span><span class="svgicons svg-wprsp-star"></span><span class="svgicons svg-wprsp-star"></span><span class="svgicons svg-wprsp-star"></span><span class="svgicons svg-wprsp-star-o"></span>
										</span>
									</div>
									<div class="wpproslider_t6_SPAN_6 wprev_preview_tcolor2 t6datediv" style="color: rgb(85, 85, 85);">
										<span id="wprev_showdate">1/12/2017</span>
									</div>
								</div>
							</div>
							<div class="wpproslider_t6_DIV_4">
								<p class="wpproslider_t6_P_4 wprev_preview_tcolor1" style="color: rgb(85, 85, 85);"></p>
							</div>
							<div class="wpproslider_t6_DIV_3_logo">
								<a id="from_url" href="" target="_blank"><img src="" alt="" class="wprevpro_t6_site_logo siteicon"></a>
							</div>
						</div>
					</div>
				</div>
				<div id="review_list" style="display:none;">
					<table class="wp-list-table widefat striped posts">
						<thead>
							<tr>
								<th scope="col" width="80px" class="manage-column"><?php esc_html_e( 'Name', 'wp-tripadvisor-review-slider' ); ?></th>
								<th scope="col" width="70px" class="manage-column"><?php esc_html_e( 'Rating', 'wp-tripadvisor-review-slider' ); ?></th>
								<th scope="col" class="manage-column"><?php esc_html_e( 'Review Title/Text', 'wp-tripadvisor-review-slider' ); ?></th>
								<th scope="col" width="75px" class="manage-column"><?php esc_html_e( 'Date', 'wp-tripadvisor-review-slider' ); ?></th>
								<th scope="col" width="100px" class="manage-column"><?php esc_html_e( 'Words/Char', 'wp-tripadvisor-review-slider' ); ?></th>
								<th scope="col" width="100px" class="manage-column"><?php esc_html_e( 'Social Page', 'wp-tripadvisor-review-slider' ); ?></th>
							</tr>
						</thead>
						<tbody id="review_list_body"></tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
</div>
</br></br></br></br>

<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function imagero_register_settings() {
	register_setting( 'imagero_options_group', 'imagero_max_width', [ 'sanitize_callback' => 'absint' ] );
	register_setting( 'imagero_options_group', 'imagero_max_height', [ 'sanitize_callback' => 'absint' ] );
	register_setting( 'imagero_options_group', 'imagero_webp_enabled', [ 'sanitize_callback' => 'absint' ] );
	register_setting( 'imagero_options_group', 'imagero_quality', [ 'sanitize_callback' => 'absint' ] );
	
    // Watermark Settings
    register_setting( 'imagero_options_group', 'imagero_watermark_enabled', [ 'sanitize_callback' => 'absint' ] );
    register_setting( 'imagero_options_group', 'imagero_watermark_type', [ 'sanitize_callback' => 'sanitize_text_field' ] );
    register_setting( 'imagero_options_group', 'imagero_watermark_content', [ 'sanitize_callback' => 'sanitize_text_field' ] );
    register_setting( 'imagero_options_group', 'imagero_watermark_position', [ 'sanitize_callback' => 'sanitize_text_field' ] );
    register_setting( 'imagero_options_group', 'imagero_watermark_opacity', [ 'sanitize_callback' => 'absint' ] );
    register_setting( 'imagero_options_group', 'imagero_watermark_color', [ 'sanitize_callback' => 'sanitize_text_field' ] );

    // R2 Settings
    register_setting( 'imagero_options_group', 'imagero_r2_enabled', [ 'sanitize_callback' => 'absint' ] );
    register_setting( 'imagero_options_group', 'imagero_r2_endpoint', [ 'sanitize_callback' => 'sanitize_url' ] );
    register_setting( 'imagero_options_group', 'imagero_r2_access_key', [ 'sanitize_callback' => 'sanitize_text_field' ] );
    register_setting( 'imagero_options_group', 'imagero_r2_secret_key', [ 'sanitize_callback' => 'sanitize_text_field' ] );
    register_setting( 'imagero_options_group', 'imagero_r2_bucket', [ 'sanitize_callback' => 'sanitize_text_field' ] );
    register_setting( 'imagero_options_group', 'imagero_r2_public_domain', [ 'sanitize_callback' => 'sanitize_url' ] );
}
add_action( 'admin_init', 'imagero_register_settings' );

function imagero_register_options_page() {
	add_options_page(
		'Imagero Optimizer',
		'Imagero Optimizer',
		'manage_options',
		'imagero-optimizer',
		'imagero_options_page_html'
	);
}
add_action( 'admin_menu', 'imagero_register_options_page' );

function imagero_format_bytes( $bytes, $precision = 2 ) {
    $units = array( 'B', 'KB', 'MB', 'GB', 'TB' );
    $bytes = max( $bytes, 0 );
    $pow = floor( ( $bytes ? log( $bytes ) : 0 ) / log( 1024 ) );
    $pow = min( $pow, count( $units ) - 1 );
    $bytes /= pow( 1024, $pow );
    return round( $bytes, $precision ) . ' ' . $units[$pow];
}

function imagero_options_page_html() {
	?>
	<div class="wrap imagero-settings-wrapper font-sans text-slate-800 mr-5">
		<div class="flex justify-between items-center mb-6 mt-4">
            <h1 class="text-2xl font-bold text-slate-900 !m-0">Imagero Optimizer</h1>
            <button type="button" class="bg-primary hover:bg-blue-600 text-white font-semibold py-2 px-6 rounded-lg transition-colors shadow-sm" id="imagero-save-btn-top">Save Settings</button>
        </div>
        
        <div id="imagero-message-container"></div>

		<form id="imagero-settings-form" class="flex flex-col lg:flex-row gap-6">
            <!-- Main Content Column -->
            <div class="flex-1 min-w-0">
                
                <!-- Tabs -->
                <div class="flex space-x-2 mb-6 border-b border-slate-200 pb-1">
                    <button type="button" class="imagero-tab-link active px-4 py-2 rounded-t-lg font-medium text-primary border-b-2 border-primary bg-blue-50/50" data-target="general">General</button>
                    <button type="button" class="imagero-tab-link px-4 py-2 rounded-t-lg font-medium text-slate-500 hover:text-slate-700 hover:bg-slate-50 transition-colors" data-target="watermark">Watermark</button>
                    <button type="button" class="imagero-tab-link px-4 py-2 rounded-t-lg font-medium text-slate-500 hover:text-slate-700 hover:bg-slate-50 transition-colors" data-target="r2">Cloudflare R2</button>
                </div>

                <div class="imagero-tab-content">
                    <!-- General Tab -->
                    <div id="general" class="imagero-tab-pane block">
                        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50">
                                <h5 class="text-lg font-semibold text-slate-800 !m-0">Image Optimization</h5>
                            </div>
                            <div class="p-6 space-y-6">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label class="block text-sm font-medium text-slate-700 mb-2">Max Width (px)</label>
                                        <input type="number" class="w-full border-slate-300 rounded-lg shadow-sm focus:border-primary focus:ring focus:ring-primary/20 transition-all" name="imagero_max_width" value="<?php echo esc_attr( get_option( 'imagero_max_width' ) ); ?>">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-slate-700 mb-2">Max Height (px)</label>
                                        <input type="number" class="w-full border-slate-300 rounded-lg shadow-sm focus:border-primary focus:ring focus:ring-primary/20 transition-all" name="imagero_max_height" value="<?php echo esc_attr( get_option( 'imagero_max_height' ) ); ?>">
                                    </div>
                                </div>
                                
                                <div class="flex items-center justify-between p-4 bg-slate-50 rounded-lg border border-slate-100">
                                    <div>
                                        <span class="block font-medium text-slate-900">WebP Conversion</span>
                                        <span class="block text-sm text-slate-500">Automatically convert uploaded images to WebP format</span>
                                    </div>
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" name="imagero_webp_enabled" value="1" class="sr-only peer" <?php checked( 1, get_option( 'imagero_webp_enabled' ), true ); ?>>
                                        <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary/20 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                                    </label>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-2">Quality (0-100)</label>
                                    <input type="number" class="w-full max-w-xs border-slate-300 rounded-lg shadow-sm focus:border-primary focus:ring focus:ring-primary/20 transition-all" name="imagero_quality" value="<?php echo esc_attr( get_option( 'imagero_quality' ) ); ?>" min="0" max="100">
                                </div>

                                <!-- Stats Section -->
                                <div class="mt-8 pt-6 border-t border-slate-100">
                                    <h6 class="text-sm font-semibold text-slate-500 uppercase tracking-wider mb-4">Optimization Stats</h6>
                                    <div class="grid grid-cols-2 gap-4">
                                        <div class="bg-green-50 rounded-lg p-4 border border-green-100">
                                            <span class="block text-2xl font-bold text-green-700"><?php echo esc_html( imagero_format_bytes( get_option( 'imagero_total_saved_bytes', 0 ) ) ); ?></span>
                                            <span class="text-sm font-medium text-green-600">Total Space Saved</span>
                                        </div>
                                        <div class="bg-blue-50 rounded-lg p-4 border border-blue-100">
                                            <span class="block text-2xl font-bold text-blue-700"><?php echo number_format( get_option( 'imagero_total_processed_images', 0 ) ); ?></span>
                                            <span class="text-sm font-medium text-blue-600">Images Optimized</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Watermark Tab -->
                    <div id="watermark" class="imagero-tab-pane hidden">
                        
                        <!-- Position Selector -->
                        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden mb-6">
                            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50">
                                <h5 class="text-lg font-semibold text-slate-800 !m-0">Button Position</h5>
                                <p class="text-sm text-slate-500 mt-1">Choose where the watermark appears on your images</p>
                            </div>
                            <div class="p-6">
                                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                    <?php 
                                        $positions = [
                                            'top-left' => 'Top Left',
                                            'top-right' => 'Top Right',
                                            'bottom-left' => 'Bottom Left',
                                            'bottom-right' => 'Bottom Right'
                                        ];
                                        $current_pos = get_option( 'imagero_watermark_position', 'bottom-right' );
                                    ?>
                                    <?php foreach($positions as $key => $label): ?>
                                    <label class="imagero-position-card cursor-pointer group relative">
                                        <input type="radio" name="imagero_watermark_position" value="<?php echo esc_attr( $key ); ?>" <?php checked( $current_pos, $key ); ?> class="peer sr-only">
                                        <div class="p-4 rounded-lg border-2 border-slate-200 hover:border-primary/50 hover:bg-slate-50 transition-all peer-checked:border-danger peer-checked:bg-red-50/30 flex flex-col items-center">
                                            <div class="w-16 h-10 bg-slate-100 rounded border border-slate-300 mb-3 relative shadow-inner">
                                                <div class="imagero-dot w-2.5 h-2.5 bg-danger rounded-full absolute shadow-sm <?php echo esc_attr( $key ); ?>"></div>
                                            </div>
                                            <span class="text-sm font-medium text-slate-700 peer-checked:text-danger"><?php echo esc_html( $label ); ?></span>
                                        </div>
                                    </label>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Appearance Settings -->
                        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50">
                                <h5 class="text-lg font-semibold text-slate-800 !m-0">Appearance Settings</h5>
                                <p class="text-sm text-slate-500 mt-1">Customize the look and feel of your watermark</p>
                            </div>
                            <div class="p-6 space-y-6">
                                <div class="flex items-center justify-between p-4 bg-slate-50 rounded-lg border border-slate-100">
                                    <div>
                                        <span class="block font-medium text-slate-900">Enable Watermark</span>
                                        <span class="block text-sm text-slate-500">Apply watermark to new uploads</span>
                                    </div>
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" name="imagero_watermark_enabled" value="1" id="imagero_watermark_enabled" class="sr-only peer" <?php checked( 1, get_option( 'imagero_watermark_enabled' ), true ); ?>>
                                        <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary/20 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                                    </label>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label class="block text-sm font-medium text-slate-700 mb-2">Watermark Type</label>
                                        <select class="w-full border-slate-300 rounded-lg shadow-sm focus:border-primary focus:ring focus:ring-primary/20 transition-all" name="imagero_watermark_type" id="imagero_watermark_type">
                                            <option value="text" <?php selected( 'text', get_option( 'imagero_watermark_type' ) ); ?>>Text</option>
                                            <option value="image" <?php selected( 'image', get_option( 'imagero_watermark_type' ) ); ?>>Image</option>
                                        </select>
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-slate-700 mb-2">Watermark Content</label>
                                        <input type="text" class="w-full border-slate-300 rounded-lg shadow-sm focus:border-primary focus:ring focus:ring-primary/20 transition-all" name="imagero_watermark_content" id="imagero_watermark_content" value="<?php echo esc_attr( get_option( 'imagero_watermark_content' ) ); ?>">
                                        <p class="text-xs text-slate-500 mt-1">Enter text or Image URL</p>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-slate-700 mb-2">Text Color</label>
                                        <div>
                                            <input type="text" class="imagero-color-picker" name="imagero_watermark_color" id="imagero_watermark_color" value="<?php echo esc_attr( get_option( 'imagero_watermark_color', '#ffffff' ) ); ?>">
                                        </div>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-slate-700 mb-2">Opacity (0-100)</label>
                                        <input type="number" class="w-full border-slate-300 rounded-lg shadow-sm focus:border-primary focus:ring focus:ring-primary/20 transition-all" name="imagero_watermark_opacity" id="imagero_watermark_opacity" value="<?php echo esc_attr( get_option( 'imagero_watermark_opacity' ) ); ?>" min="0" max="100">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- R2 Tab -->
                    <div id="r2" class="imagero-tab-pane hidden">
                        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50">
                                <h5 class="text-lg font-semibold text-slate-800 !m-0">Cloudflare R2 Configuration</h5>
                            </div>
                            <div class="p-6 space-y-6">
                                <div class="flex items-center justify-between p-4 bg-slate-50 rounded-lg border border-slate-100">
                                    <div>
                                        <span class="block font-medium text-slate-900">Enable R2 Offload</span>
                                        <span class="block text-sm text-slate-500">Offload images to Cloudflare R2 bucket</span>
                                    </div>
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" name="imagero_r2_enabled" value="1" class="sr-only peer" <?php checked( 1, get_option( 'imagero_r2_enabled' ), true ); ?>>
                                        <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary/20 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                                    </label>
                                </div>
                                
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-slate-700 mb-2">R2 Endpoint URL</label>
                                        <input type="text" class="w-full border-slate-300 rounded-lg shadow-sm focus:border-primary focus:ring focus:ring-primary/20 transition-all" name="imagero_r2_endpoint" value="<?php echo esc_attr( get_option( 'imagero_r2_endpoint' ) ); ?>">
                                    </div>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-slate-700 mb-2">Access Key ID</label>
                                            <input type="text" class="w-full border-slate-300 rounded-lg shadow-sm focus:border-primary focus:ring focus:ring-primary/20 transition-all" name="imagero_r2_access_key" value="<?php echo esc_attr( get_option( 'imagero_r2_access_key' ) ); ?>">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-slate-700 mb-2">Secret Access Key</label>
                                            <input type="password" class="w-full border-slate-300 rounded-lg shadow-sm focus:border-primary focus:ring focus:ring-primary/20 transition-all" name="imagero_r2_secret_key" value="<?php echo esc_attr( get_option( 'imagero_r2_secret_key' ) ); ?>">
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-slate-700 mb-2">Bucket Name</label>
                                            <input type="text" class="w-full border-slate-300 rounded-lg shadow-sm focus:border-primary focus:ring focus:ring-primary/20 transition-all" name="imagero_r2_bucket" value="<?php echo esc_attr( get_option( 'imagero_r2_bucket' ) ); ?>">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-slate-700 mb-2">Public Domain (CDN)</label>
                                            <input type="text" class="w-full border-slate-300 rounded-lg shadow-sm focus:border-primary focus:ring focus:ring-primary/20 transition-all" name="imagero_r2_public_domain" value="<?php echo esc_attr( get_option( 'imagero_r2_public_domain' ) ); ?>">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar Column -->
            <div class="w-full lg:w-80 flex-shrink-0">
                <!-- Live Preview -->
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden mb-6 sticky top-10">
                    <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50">
                        <h5 class="text-lg font-semibold text-slate-800 !m-0 flex items-center">
                            <span class="dashicons dashicons-visibility mr-2"></span>Live Preview
                        </h5>
                        <p class="text-sm text-slate-500 mt-1">See how your watermark will look</p>
                    </div>
                    <div class="p-6 bg-slate-100 flex justify-center items-center">
                        <div id="imagero-live-preview-container" class="relative w-full max-w-[300px] aspect-video rounded-lg overflow-hidden shadow-md imagero-preview-bg">
                            <div id="imagero-watermark-preview" class="absolute px-2 py-1 rounded transition-all duration-300 pointer-events-none whitespace-nowrap">
                                Watermark
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tips -->
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50">
                        <h5 class="text-lg font-semibold text-slate-800 !m-0 flex items-center">
                            <span class="dashicons dashicons-lightbulb mr-2"></span>Tips & Best Practices
                        </h5>
                    </div>
                    <div class="p-6">
                        <ul class="space-y-4">
                            <li class="flex items-start">
                                <span class="dashicons dashicons-yes text-green-500 mr-2 mt-1 flex-shrink-0"></span>
                                <div class="text-sm text-slate-600"><strong class="text-slate-800">Color Contrast:</strong> Ensure good contrast between background and text colors for visibility.</div>
                            </li>
                            <li class="flex items-start">
                                <span class="dashicons dashicons-yes text-green-500 mr-2 mt-1 flex-shrink-0"></span>
                                <div class="text-sm text-slate-600"><strong class="text-slate-800">Position:</strong> Bottom-right is most common, but test what works best for your content.</div>
                            </li>
                            <li class="flex items-start">
                                <span class="dashicons dashicons-yes text-green-500 mr-2 mt-1 flex-shrink-0"></span>
                                <div class="text-sm text-slate-600"><strong class="text-slate-800">Opacity:</strong> Lower opacity (30-50%) is less intrusive but still protects your image.</div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
		</form>
	</div>
	<?php
}

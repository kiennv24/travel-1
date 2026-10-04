=== Imagero - Auto Image Optimizer & Watermark with R2 Offload ===
Contributors: wpnovate
Tags: image optimization, webp, watermark
Requires at least: 6.0
Tested up to: 6.9
Stable tag: 1.0.0
Requires PHP: 7.4
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Automatically resize, convert to WebP, and watermark uploaded images. Optionally offload to Cloudflare R2.

== Description ==

This plugin provides a complete solution for optimizing images in WordPress. 
It automatically:
1.  **Resizes** large images to a maximum width/height.
2.  **Converts** images to WebP format for faster loading.
3.  **Adds Watermarks** (Text or Image) to protect your content.
4.  **Offloads** images to Cloudflare R2 storage to save server space and bandwidth.

== Installation ==

1.  Upload the plugin files to the `/wp-content/plugins/imagero` directory, or install the plugin through the WordPress plugins screen.
2.  Activate the plugin through the 'Plugins' screen in WordPress.
3.  Go to **Settings -> Imagero Optimizer** to configure the plugin.

== Configuration ==

1.  **General Settings**: Set the maximum width/height for images and enable WebP conversion.
2.  **Watermark Settings**: Choose between Text or Image watermark, set the content, position, and opacity.
3.  **Cloudflare R2 Settings**: Enter your R2 Endpoint, Access Keys, Bucket Name, and Public Domain.

== External Services ==

This plugin optionally connects to Cloudflare R2, a cloud object storage service provided by Cloudflare, Inc.

= Cloudflare R2 Storage =

**What it is used for:**
When enabled, this plugin offloads your uploaded images to your Cloudflare R2 bucket. This helps reduce server storage usage and can improve image delivery performance through Cloudflare's global network.

**What data is sent:**
- Image files (after processing/optimization) are uploaded to your R2 bucket
- No personal user data is collected or transmitted

**When data is sent:**
- Only when you enable the "R2 Offload" feature in the plugin settings
- Only when new images are uploaded to your WordPress media library
- Images are sent to the R2 endpoint URL that YOU configure in the settings

**Service provider:**
This service is provided by Cloudflare, Inc.
- [Cloudflare Terms of Service](https://www.cloudflare.com/terms/)
- [Cloudflare Privacy Policy](https://www.cloudflare.com/privacypolicy/)

**Note:** You must have your own Cloudflare account and R2 bucket. The plugin uses YOUR credentials (Access Key, Secret Key, Bucket Name, Endpoint) that you provide in the settings. No data is sent to the plugin developer.

== Frequently Asked Questions ==

= Does this replace the original image? =
Yes, the plugin processes the image immediately upon upload. If WebP is enabled, the original image is replaced with the WebP version.

= Can I use an image for the watermark? =
Yes, you can provide the URL of an image to use as a watermark. Ensure the image is already uploaded to your media library.

= How do I get my R2 credentials? =
Log in to your Cloudflare dashboard, go to R2, and create an API token with Read/Write permissions.

= Is Cloudflare R2 required? =
No, R2 offload is completely optional. The image optimization and watermark features work independently without R2.

== Changelog ==

= 1.0.0 =
* Initial release
* Image resizing and optimization
* WebP conversion
* Text and image watermarks
* Cloudflare R2 offload support

<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Imagero_Image_Processor {

    public function __construct() {
        add_filter( 'wp_handle_upload', [ $this, 'handle_upload' ] );
        add_filter( 'wp_generate_attachment_metadata', [ $this, 'handle_metadata_offload' ], 10, 2 );
        add_filter( 'wp_get_attachment_url', [ $this, 'rewrite_attachment_url' ], 10, 2 );
        add_filter( 'wp_calculate_image_srcset', [ $this, 'rewrite_srcset' ], 10, 5 );
    }

    public function handle_upload( $upload ) {
        if ( ! isset( $upload['type'] ) || strpos( $upload['type'], 'image/' ) === false ) {
            return $upload;
        }

        $file_path = $upload['file'];
        $original_size = file_exists( $file_path ) ? filesize( $file_path ) : 0;
        
        // 1. Resize & Watermark & WebP
        $processed_file = $this->process_image( $file_path, $upload['type'] );
        
        if ( $processed_file && $processed_file !== $file_path ) {
            // Update upload array if file changed (e.g. WebP conversion)
            $upload['file'] = $processed_file;
            $upload['type'] = 'image/webp';
            $upload['url'] = str_replace( pathinfo( $file_path, PATHINFO_EXTENSION ), 'webp', $upload['url'] );
            
            // Remove original if different
            if ( file_exists( $file_path ) ) {
                wp_delete_file( $file_path );
            }
            $file_path = $processed_file;
        }

        // Calculate Stats
        $final_size = file_exists( $file_path ) ? filesize( $file_path ) : 0;
        if ( $original_size > 0 && $final_size > 0 ) {
            $saved_bytes = max( 0, $original_size - $final_size );
            
            $total_saved = (int) get_option( 'imagero_total_saved_bytes', 0 );
            update_option( 'imagero_total_saved_bytes', $total_saved + $saved_bytes );
            
            $total_images = (int) get_option( 'imagero_total_processed_images', 0 );
            update_option( 'imagero_total_processed_images', $total_images + 1 );
        }

        return $upload;
    }

    public function handle_metadata_offload( $metadata, $attachment_id ) {
        if ( ! get_option( 'imagero_r2_enabled' ) ) {
            return $metadata;
        }

        $file = get_attached_file( $attachment_id );
        if ( ! $file || ! file_exists( $file ) ) {
            return $metadata;
        }

        // Offload main file
        if ( $this->offload_to_r2( $file ) ) {
            update_post_meta( $attachment_id, 'imagero_is_offloaded', true );
        }

        // Offload sizes
        if ( isset( $metadata['sizes'] ) && is_array( $metadata['sizes'] ) ) {
            $dir = dirname( $file );
            foreach ( $metadata['sizes'] as $size => $size_info ) {
                $size_file = $dir . '/' . $size_info['file'];
                if ( file_exists( $size_file ) ) {
                    $this->offload_to_r2( $size_file );
                }
            }
        }

        return $metadata;
    }

    private function process_image( $file_path, $mime_type ) {
        $quality = (int) get_option( 'imagero_quality', 80 );
        if ( $quality < 1 || $quality > 100 ) $quality = 80;

        $max_width = (int) get_option( 'imagero_max_width', 1920 );
        $max_height = (int) get_option( 'imagero_max_height', 1080 );
        $webp_enabled = (bool) get_option( 'imagero_webp_enabled', 1 );

        $editor = wp_get_image_editor( $file_path );
        if ( ! is_wp_error( $editor ) ) {
            $editor->set_quality( $quality );
            $size = $editor->get_size();
            
            $dirty = false;
            if ( ( $max_width && $size['width'] > $max_width ) || ( $max_height && $size['height'] > $max_height ) ) {
                $editor->resize( $max_width, $max_height, false );
                $dirty = true;
            }

            // Save if resized or need compression
            if ( $dirty ) {
                $editor->save( $file_path );
            }

            // 2. Watermark
            if ( get_option( 'imagero_watermark_enabled' ) ) {
                $watermarked = $this->apply_watermark_to_file( $file_path );
                if ( $watermarked ) {
                    $editor = wp_get_image_editor( $file_path );
                    if ( ! is_wp_error( $editor ) ) {
                        $editor->set_quality( $quality );
                    }
                }
            }

            // 3. WebP Conversion
            if ( $webp_enabled && ! is_wp_error( $editor ) ) {
                $new_path = preg_replace( '/\.[^.]+$/', '.webp', $file_path );
                $saved = $editor->save( $new_path, 'image/webp' );
                if ( ! is_wp_error( $saved ) && file_exists( $new_path ) ) {
                    return $new_path;
                }
            } elseif ( ! $webp_enabled ) {
                // Compress original format
                $editor->save( $file_path );
            }
        }

        // Direct GD Fallback
        if ( extension_loaded( 'gd' ) && file_exists( $file_path ) ) {
            $info = @getimagesize( $file_path );
            if ( $info ) {
                $img = null;
                switch ( $info['mime'] ) {
                    case 'image/jpeg': $img = @imagecreatefromjpeg( $file_path ); break;
                    case 'image/png': $img = @imagecreatefrompng( $file_path ); break;
                    case 'image/webp': if ( function_exists('imagecreatefromwebp') ) $img = @imagecreatefromwebp( $file_path ); break;
                }

                if ( $img ) {
                    $w = imagesx( $img );
                    $h = imagesy( $img );
                    if ( ( $max_width && $w > $max_width ) || ( $max_height && $h > $max_height ) ) {
                        $scale = min( $max_width / $w, $max_height / $h );
                        $nw = (int)round( $w * $scale );
                        $nh = (int)round( $h * $scale );
                        $canvas = imagecreatetruecolor( $nw, $nh );
                        imagealphablending( $canvas, false );
                        imagesavealpha( $canvas, true );
                        imagecopyresampled( $canvas, $img, 0, 0, 0, 0, $nw, $nh, $w, $h );
                        imagedestroy( $img );
                        $img = $canvas;
                    }

                    if ( $webp_enabled && function_exists( 'imagewebp' ) ) {
                        $new_path = preg_replace( '/\.[^.]+$/', '.webp', $file_path );
                        if ( @imagewebp( $img, $new_path, $quality ) && file_exists( $new_path ) ) {
                            imagedestroy( $img );
                            return $new_path;
                        }
                    }

                    if ( $info['mime'] === 'image/jpeg' ) {
                        @imagejpeg( $img, $file_path, $quality );
                    } elseif ( $info['mime'] === 'image/png' ) {
                        @imagepng( $img, $file_path, 9 );
                    }
                    imagedestroy( $img );
                }
            }
        }

        return $file_path;
    }

    private function apply_watermark_to_file( $image_path ) {
        $info = getimagesize( $image_path );
        if ( ! $info ) return false;
        
        $mime = $info['mime'];

        switch ( $mime ) {
            case 'image/jpeg':
                $image = imagecreatefromjpeg( $image_path );
                break;
            case 'image/png':
                $image = imagecreatefrompng( $image_path );
                // Preserve transparency
                imagealphablending($image, true);
                imagesavealpha($image, true);
                break;
            case 'image/gif':
                $image = imagecreatefromgif( $image_path );
                break;
            default:
                return false;
        }

        if ( ! $image ) return false;

        $type = get_option( 'imagero_watermark_type' );
        $position = get_option( 'imagero_watermark_position' );
        $opacity = get_option( 'imagero_watermark_opacity', 100 );
        
        // Calculate position
        $img_w = imagesx( $image );
        $img_h = imagesy( $image );
        
        if ( $type === 'text' ) {
            $text = get_option( 'imagero_watermark_content' );
            if ( empty( $text ) ) return false;

            $font_size = 5; // GD built-in font size (1-5)
            // Alpha: 0 (opaque) to 127 (transparent)
            // Opacity input: 100 (opaque) to 0 (transparent)
            $alpha = (int) ( 127 - ( $opacity * 1.27 ) );
            
            $hex_color = get_option( 'imagero_watermark_color', '#ffffff' );
            $hex_color = ltrim( $hex_color, '#' );
            
            if ( strlen( $hex_color ) == 6 ) {
                list( $r, $g, $b ) = sscanf( $hex_color, "%02x%02x%02x" );
            } else {
                $r = $g = $b = 255;
            }
            
            $color = imagecolorallocatealpha( $image, $r, $g, $b, $alpha );
            
            // Simple positioning for built-in font
            $fw = imagefontwidth( $font_size ) * strlen( $text );
            $fh = imagefontheight( $font_size );
            
            $x = 10; $y = 10;
            switch ( $position ) {
                case 'center': $x = ($img_w - $fw) / 2; $y = ($img_h - $fh) / 2; break;
                case 'bottom-right': $x = $img_w - $fw - 10; $y = $img_h - $fh - 10; break;
                case 'bottom-left': $x = 10; $y = $img_h - $fh - 10; break;
                case 'top-right': $x = $img_w - $fw - 10; $y = 10; break;
                case 'top-left': $x = 10; $y = 10; break;
            }
            
            imagestring( $image, $font_size, $x, $y, $text, $color );
            
        } elseif ( $type === 'image' ) {
            $watermark_url = get_option( 'imagero_watermark_content' );
            if ( empty( $watermark_url ) ) return false;

            // Need local path for GD
            $watermark_id = attachment_url_to_postid( $watermark_url );
            if ( $watermark_id ) {
                $watermark_path = get_attached_file( $watermark_id );
                if ( file_exists( $watermark_path ) ) {
                    $stamp = imagecreatefrompng( $watermark_path ); // Assuming PNG watermark
                    if ( $stamp ) {
                        $sx = imagesx( $stamp );
                        $sy = imagesy( $stamp );
                        
                        $x = 10; $y = 10;
                        switch ( $position ) {
                            case 'center': $x = ($img_w - $sx) / 2; $y = ($img_h - $sy) / 2; break;
                            case 'bottom-right': $x = $img_w - $sx - 10; $y = $img_h - $sy - 10; break;
                            case 'bottom-left': $x = 10; $y = $img_h - $sy - 10; break;
                            case 'top-right': $x = $img_w - $sx - 10; $y = 10; break;
                            case 'top-left': $x = 10; $y = 10; break;
                        }
                        
                        // Copy watermark
                        imagecopy( $image, $stamp, $x, $y, 0, 0, $sx, $sy );
                        imagedestroy( $stamp );
                    }
                }
            }
        }

        // Save back to file
        $result = false;
        switch ( $mime ) {
            case 'image/jpeg':
                $result = imagejpeg( $image, $image_path, 90 );
                break;
            case 'image/png':
                $result = imagepng( $image, $image_path );
                break;
            case 'image/gif':
                $result = imagegif( $image, $image_path );
                break;
        }
        imagedestroy( $image );
        
        return $result;
    }

    private function offload_to_r2( $file_path ) {
        $uploader = new Imagero_R2_Uploader();
        if ( ! $uploader->is_configured() ) {
            return false;
        }

        $uploads_dir = wp_upload_dir();
        // Get relative path from uploads dir
        $relative_path = str_replace( $uploads_dir['basedir'] . '/', '', $file_path );
        
        // Upload
        return $uploader->upload_file( $file_path, $relative_path );
    }

    public function rewrite_attachment_url( $url, $post_id ) {
        if ( ! get_option( 'imagero_r2_enabled' ) ) {
            return $url;
        }
        
        // Check if offloaded
        if ( ! get_post_meta( $post_id, 'imagero_is_offloaded', true ) ) {
            return $url;
        }
        
        $uploader = new Imagero_R2_Uploader();
        if ( ! $uploader->is_configured() ) {
            return $url;
        }

        $uploads_dir = wp_upload_dir();
        $base_url = $uploads_dir['baseurl'];
        $public_domain = get_option( 'imagero_r2_public_domain' );
        
        if ( $public_domain && strpos( $url, $base_url ) !== false ) {
            // Replace base URL with R2 public domain
            $r2_base = rtrim( $public_domain, '/' );
            $local_base = rtrim( $base_url, '/' );
            return str_replace( $local_base, $r2_base, $url );
        }

        return $url;
    }
    
    public function rewrite_srcset( $sources, $size_array, $image_src, $image_meta, $attachment_id ) {
        if ( ! get_option( 'imagero_r2_enabled' ) ) {
            return $sources;
        }
        
        // Check if offloaded
        if ( ! get_post_meta( $attachment_id, 'imagero_is_offloaded', true ) ) {
            return $sources;
        }
        
        $uploader = new Imagero_R2_Uploader();
        if ( ! $uploader->is_configured() ) {
            return $sources;
        }
        
        $uploads_dir = wp_upload_dir();
        $base_url = $uploads_dir['baseurl'];
        $public_domain = get_option( 'imagero_r2_public_domain' );
        
        if ( $public_domain ) {
            $r2_base = rtrim( $public_domain, '/' );
            $local_base = rtrim( $base_url, '/' );
            
            foreach ( $sources as &$source ) {
                $source['url'] = str_replace( $local_base, $r2_base, $source['url'] );
            }
        }
        
        return $sources;
    }
}

new Imagero_Image_Processor();

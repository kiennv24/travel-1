<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Imagero_R2_Uploader {

    private $endpoint;
    private $access_key;
    private $secret_key;
    private $bucket;
    private $region = 'auto'; // R2 uses 'auto'

    public function __construct() {
        $this->endpoint = get_option( 'imagero_r2_endpoint' );
        $this->access_key = get_option( 'imagero_r2_access_key' );
        $this->secret_key = get_option( 'imagero_r2_secret_key' );
        $this->bucket = get_option( 'imagero_r2_bucket' );
    }

    public function upload_file( $file_path, $remote_filename ) {
        if ( ! $this->is_configured() ) {
            return false;
        }

        $content = file_get_contents( $file_path );
        $content_type = mime_content_type( $file_path );
        
        // Prepare S3 upload
        $host = wp_parse_url( $this->endpoint, PHP_URL_HOST );
        $uri = '/' . $this->bucket . '/' . $remote_filename;
        $url = $this->endpoint . '/' . $this->bucket . '/' . $remote_filename;
        
        $timestamp = time();
        $date_long = gmdate( 'Ymd\THis\Z', $timestamp );
        $date_short = gmdate( 'Ymd', $timestamp );
        
        // Canonical Request
        $method = 'PUT';
        $canonical_uri = '/' . $this->bucket . '/' . $remote_filename;
        $canonical_querystring = '';
        $headers = [
            'host' => $host,
            'x-amz-content-sha256' => hash( 'sha256', $content ),
            'x-amz-date' => $date_long,
        ];
        
        ksort( $headers );
        $canonical_headers = '';
        $signed_headers = '';
        foreach ( $headers as $key => $value ) {
            $canonical_headers .= $key . ':' . $value . "\n";
            $signed_headers .= $key . ';';
        }
        $signed_headers = rtrim( $signed_headers, ';' );
        
        $payload_hash = hash( 'sha256', $content );
        $canonical_request = "$method\n$canonical_uri\n$canonical_querystring\n$canonical_headers\n$signed_headers\n$payload_hash";
        
        // String to Sign
        $algorithm = 'AWS4-HMAC-SHA256';
        $credential_scope = "$date_short/$this->region/s3/aws4_request";
        $string_to_sign = "$algorithm\n$date_long\n$credential_scope\n" . hash( 'sha256', $canonical_request );
        
        // Signature
        $k_secret = 'AWS4' . $this->secret_key;
        $k_date = hash_hmac( 'sha256', $date_short, $k_secret, true );
        $k_region = hash_hmac( 'sha256', $this->region, $k_date, true );
        $k_service = hash_hmac( 'sha256', 's3', $k_region, true );
        $k_signing = hash_hmac( 'sha256', 'aws4_request', $k_service, true );
        $signature = hash_hmac( 'sha256', $string_to_sign, $k_signing );
        
        $authorization = "$algorithm Credential=$this->access_key/$credential_scope, SignedHeaders=$signed_headers, Signature=$signature";
        
        $args = [
            'method' => 'PUT',
            'body' => $content,
            'headers' => [
                'Authorization' => $authorization,
                'x-amz-date' => $date_long,
                'x-amz-content-sha256' => $payload_hash,
                'Content-Type' => $content_type,
            ],
        ];
        
        $response = wp_remote_request( $url, $args );
        
        if ( is_wp_error( $response ) ) {
            return false;
        }
        
        $response_code = wp_remote_retrieve_response_code( $response );
        if ( $response_code >= 200 && $response_code < 300 ) {
            return true;
        } else {
            return false;
        }
    }

    public function is_configured() {
        return $this->endpoint && $this->access_key && $this->secret_key && $this->bucket;
    }
    
    public function get_public_url( $filename ) {
        $public_domain = get_option( 'imagero_r2_public_domain' );
        if ( $public_domain ) {
            return rtrim( $public_domain, '/' ) . '/' . $filename;
        }
        // Fallback to direct R2 URL if public domain not set (though usually not public by default without custom domain or worker)
        return $this->endpoint . '/' . $this->bucket . '/' . $filename;
    }
}

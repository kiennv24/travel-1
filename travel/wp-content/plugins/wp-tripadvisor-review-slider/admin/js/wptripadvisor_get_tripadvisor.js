(function( $ ) {
	'use strict';

	$(function(){

		function showMsg( $el, html, isError ) {
			$el.css( 'color', isError ? '#a00' : '#0073aa' ).html( html );
		}

		// Add a new TripAdvisor source.
		$( '#wptrip_add_source' ).on( 'click', function() {
			var $btn = $( this );
			var url = $.trim( $( '#tripadvisor_new_url' ).val() );
			var name = $.trim( $( '#tripadvisor_new_name' ).val() );

			if ( url === '' ) {
				showMsg( $( '#wptrip_add_msg' ), 'Please enter a TripAdvisor business URL.', true );
				return;
			}

			$btn.prop( 'disabled', true );
			$( '#wptrip_add_loader' ).css( 'display', 'inline-block' );
			showMsg( $( '#wptrip_add_msg' ), '', false );

			$.post( ajaxurl, {
				action: 'wptripadvisor_add_source',
				tripadvisor_url: url,
				businessname: name,
				wptripadvisor_nonce: adminjs_script_vars.wptripadvisor_nonce
			} ).done( function( response ) {
				var obj;
				try {
					obj = ( typeof response === 'object' ) ? response : JSON.parse( response );
				} catch ( e ) {
					showMsg( $( '#wptrip_add_msg' ), 'Unexpected response. Please try again.', true );
					return;
				}
				if ( obj.ack !== 'success' ) {
					showMsg( $( '#wptrip_add_msg' ), obj.ackmsg || 'Error adding source.', true );
					return;
				}
				showMsg( $( '#wptrip_add_msg' ), obj.ackmsg || 'Source added.', false );
				$( '#tripadvisor_new_url' ).val( '' );
				$( '#tripadvisor_new_name' ).val( '' );
				if ( obj.row_html ) {
					$( '.wptrip-no-sources' ).remove();
					$( '#wptrip_sources_tbody' ).append( obj.row_html );
				} else {
					window.location.reload();
				}
			} ).fail( function() {
				showMsg( $( '#wptrip_add_msg' ), 'Request failed. Please try again.', true );
			} ).always( function() {
				$btn.prop( 'disabled', false );
				$( '#wptrip_add_loader' ).hide();
			} );
		} );

		// Download reviews for one source.
		$( '#currentsources' ).on( 'click', '.downloadrevs', function() {
			var $btn = $( this );
			var pageid = $btn.attr( 'data-pageid' );
			var $row = $btn.closest( 'tr' );
			var $loader = $btn.siblings( '.buttonloader2' );
			var $msg = $row.find( '.trip-source-msg' );

			if ( ! pageid ) {
				showMsg( $msg, 'Missing page ID.', true );
				return;
			}

			$btn.hide();
			$loader.css( 'display', 'inline-block' );
			showMsg( $msg, 'Downloading… this can take a minute or two while we crawl the TripAdvisor page.', false );

			$.post( ajaxurl, {
				action: 'wptripadvisor_download_source',
				pageid: pageid,
				wptripadvisor_nonce: adminjs_script_vars.wptripadvisor_nonce
			} ).done( function( response ) {
				var obj;
				try {
					obj = ( typeof response === 'object' ) ? response : JSON.parse( response );
				} catch ( e ) {
					console.log( 'TripAdvisor crawl raw response (parse failed):', response );
					showMsg( $msg, 'Unexpected response. Please try again or contact support.', true );
					return;
				}
				if ( typeof obj.crawl_debug !== 'undefined' ) {
					console.log( 'TripAdvisor crawl server response:', obj.crawl_debug );
				} else {
					console.log( 'TripAdvisor download response:', obj );
				}
				if ( obj.ack !== 'success' ) {
					showMsg( $msg, obj.ackmsg || 'Download failed.', true );
					return;
				}
				showMsg( $msg, obj.ackmsg || 'Download complete.', false );
				if ( typeof obj.avg !== 'undefined' || typeof obj.total !== 'undefined' ) {
					var avg = ( typeof obj.avg !== 'undefined' && obj.avg !== '' ) ? obj.avg : '—';
					var total = ( typeof obj.total !== 'undefined' && obj.total !== '' ) ? obj.total : '—';
					$row.find( '.trip-source-stats' ).text( avg + ' / ' + total );
				}
			} ).fail( function( xhr ) {
				console.log( 'TripAdvisor download AJAX failed:', xhr && xhr.responseText ? xhr.responseText : xhr );
				showMsg( $msg, 'Request failed. Please try again.', true );
			} ).always( function() {
				$loader.hide();
				$btn.show();
			} );
		} );

	});

})( jQuery );

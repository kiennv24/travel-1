(function( $ ) {
	'use strict';

	$(function(){
		
		// Help button.
		$( '#wptripadvisor_helpicon' ).click(function() {
			openpopup(
				'Tips',
				'<p>- Click the <i class="dashicons dashicons-visibility text_green" aria-hidden="true"></i> icon to hide or show a review on your site.</p>' +
				'<p>- Click the <i class="dashicons dashicons-admin-tools" aria-hidden="true"></i> icon to edit the reviewer photo.</p>' +
				'<p>- Click the <i class="dashicons dashicons-trash" aria-hidden="true"></i> icon to delete a review from your WordPress database.</p>' +
				'<p><b>- Remove All Reviews:</b> Deletes all reviews in your WordPress database and starts over. It does NOT affect your reviews on TripAdvisor.</p>',
				''
			);
		});

		// Remove all / remove by source.
		$( '#wptripadvisor_removeallbtn' ).click(function() {
			var sec = $( this ).attr( 'data-sec' );
			var btnhtml2 = '';
			var pagearray = [];
			try {
				pagearray = JSON.parse( adminjs_script_vars.pagenamearray || '[]' );
			} catch ( e ) {
				pagearray = [];
			}
			for ( var i = 0; i < pagearray.length; i++ ) {
				if ( ! pagearray[i] ) {
					continue;
				}
				var tempopt = encodeURIComponent( pagearray[i] );
				btnhtml2 += '<a class="button rmrevbtn dashicons-before dashicons-no" href="?page=wp_tripadvisor-reviews&opt_type=page&opt=' + tempopt + '&_wpnonce=' + sec + '">' + $('<div>').text( pagearray[i] ).html() + '</a> ';
			}
			var bySource = btnhtml2 ? '<p>Remove by source:</p>' + btnhtml2 : '';
			openpopup(
				'Are you sure?',
				'<p>This will delete reviews in your WordPress database. It does NOT affect your reviews on TripAdvisor.</p>',
				'<a class="button dashicons-before dashicons-no" href="?page=wp_tripadvisor-reviews&opt=delall&_wpnonce=' + sec + '">Remove All</a>' + bySource
			);
		});

		// Upload avatar.
		$( '#upload_avatar_button' ).click(function() {
			tb_show( 'Upload Reviewer Avatar', 'media-upload.php?referer=wp_tripadvisor-reviews&type=image&TB_iframe=true&post_id=0', false );
			return false;
		});
		window.send_to_editor = function( html ) {
			var image_url = jQuery( '<div>' + html + '</div>' ).find( 'img' ).attr( 'src' );
			$( '#wprevpro_nr_avatar_url' ).val( image_url );
			$( '#avatar_preview' ).attr( 'src', image_url );
			tb_remove();
		};

		// Default avatar click.
		$( '.rlimg' ).click(function() {
			var tempsrc = $( this ).attr( 'src' );
			$( '#wprevpro_nr_avatar_url' ).val( tempsrc );
			$( '#wprevpro_nr_avatar_url' ).select();
			$( '#avatar_preview' ).attr( 'src', tempsrc );
		});

		function openpopup( title, body, body2 ) {
			jQuery( '#popup_titletext' ).html( title );
			jQuery( '#popup_bobytext1' ).html( body );
			jQuery( '#popup_bobytext2' ).html( body2 );
			var popup = jQuery( '#popup_review_list' ).popup({
				width: 400,
				offsetX: -100,
				offsetY: 0
			});
			popup.open();
			var bodyheight = Number( jQuery( '.popup-content' ).height() ) + 10;
			jQuery( '#popup_review_list' ).height( bodyheight );
		}

		/* ---------------------------------------------------------------
		 * Edit Review popup — opens instantly from row data (no request),
		 * saves via AJAX, and never navigates away, so the reviewer never
		 * loses their scroll position in the list.
		 * ------------------------------------------------------------- */

		var $editOverlay = $( '#wptripadvisor_new_review' );
		var $editForm     = $( '#newreviewform' );

		function closeReviewModal() {
			$editOverlay.removeClass( 'is-open' );
			$( '#wprevpro_save_review_msg' ).empty();
		}

		// Show a dismissible admin notice at the top of the list. Auto-fades so
		// the user's place in the list is never disrupted by a page reload.
		function showListNotice( message, isError ) {
			var cls      = isError ? 'error' : 'updated';
			var $notices = $( '#wprevpro_notices_area' );
			var $notice  = $( '<div class="' + cls + ' settings-error notice is-dismissible"><p><strong></strong></p></div>' );
			$notice.find( 'strong' ).text( message );
			$notices.html( $notice );
			setTimeout(function() {
				$notice.fadeOut( 400, function() { $( this ).remove(); } );
			}, 3000 );
		}

		function openReviewModal( data ) {
			var rating = parseInt( data.rating, 10 ) || 0;
			for ( var i = 1; i <= 5; i++ ) {
				$( '#wprevpro_nr_rating' + i + '-radio' ).prop( 'checked', i === rating );
			}
			$( '#wprevpro_nr_title' ).val( data.title || '' );
			$( '#wprevpro_nr_text' ).val( data.text || '' );
			$( '#wprevpro_nr_name' ).val( data.name || '' );
			$( '#wprevpro_nr_pagename' ).val( data.pagename || '' );
			$( '#wprevpro_nr_avatar_url' ).val( data.userpic || '' );
			$( '#avatar_preview' ).attr( 'src', data.userpic || '' );
			$( '#wprevpro_nr_date' ).val( data.date || '' );
			$( '#editrid' ).val( data.rid || '' );
			$( '#editrtype' ).val( data.type || '' );
			$( '#wprevpro_save_review_msg' ).empty();
			$editOverlay.addClass( 'is-open' );
		}

		// Open the popup from a row's edit link, populated straight from that
		// row's data-* attributes — no page load required.
		$( document ).on( 'click', '.wprevpro_editrev_link', function( e ) {
			e.preventDefault();
			var $link = $( this );
			openReviewModal({
				rid:      $link.data( 'rid' ),
				rating:   $link.data( 'rating' ),
				title:    $link.data( 'title' ),
				text:     $link.data( 'text' ),
				name:     $link.data( 'name' ),
				pagename: $link.data( 'pagename' ),
				userpic:  $link.data( 'userpic' ),
				date:     $link.data( 'date' ),
				type:     $link.data( 'type' )
			});
		});

		$( '#wptripadvisor_addnewreview_cancel' ).click(function( e ) {
			e.preventDefault();
			closeReviewModal();
		});

		$( '#wptripadvisor_modal_closebtn' ).click(function() {
			closeReviewModal();
		});

		// Click on the dark backdrop (not the card itself) also closes it.
		$editOverlay.on( 'click', function( e ) {
			if ( e.target === this ) {
				closeReviewModal();
			}
		});

		$( document ).on( 'keydown', function( e ) {
			if ( e.keyCode === 27 && $editOverlay.hasClass( 'is-open' ) ) {
				closeReviewModal();
			}
		});

		// The row action icons are now non-navigating spans (role="button"),
		// so mirror native button behaviour: Enter or Space activates them.
		$( document ).on( 'keydown', '.wprevpro_iconbtn', function( e ) {
			if ( e.keyCode === 13 || e.keyCode === 32 ) {
				e.preventDefault();
				$( this ).trigger( 'click' );
			}
		});

		/* ---------------------------------------------------------------
		 * Hide / unhide a review via AJAX — toggles the row in place so the
		 * reviewer never leaves the page or loses their scroll position.
		 * ------------------------------------------------------------- */
		$( document ).on( 'click', '.wprevpro_hiderev_link', function( e ) {
			e.preventDefault();
			var $link = $( this );
			var rid   = $link.data( 'rid' );
			if ( $link.data( 'busy' ) ) {
				return;
			}
			$link.data( 'busy', true );

			$.post( ajaxurl, {
				action:              'tripadvisor_hide_review',
				wptripadvisor_nonce: adminjs_script_vars.wptripadvisor_nonce,
				reviewid:            rid,
				myaction:            'hideshow'
			}).done(function( response ) {
				var parts    = ( typeof response === 'string' ? response : '' ).split( '-' );
				var newvalue = parts.length >= 3 ? parts[2] : 'fail';
				var $row     = $( '#' + rid );
				var $icon    = $link.find( 'i' );

				if ( newvalue === 'yes' ) {
					$row.addClass( 'hiddenrow' );
					$icon.removeClass( 'dashicons-visibility' ).addClass( 'dashicons-hidden' );
				} else if ( newvalue === '' ) {
					$row.removeClass( 'hiddenrow' );
					$icon.removeClass( 'dashicons-hidden' ).addClass( 'dashicons-visibility' );
				} else {
					showListNotice( 'Could not update the review. Please try again.', true );
				}
			}).fail(function() {
				showListNotice( 'Could not reach the server. Please try again.', true );
			}).always(function() {
				$link.data( 'busy', false );
			});
		});

		/* ---------------------------------------------------------------
		 * Delete a review via AJAX — removes the row in place after a
		 * confirmation, again without any page reload.
		 * ------------------------------------------------------------- */
		$( document ).on( 'click', '.wprevpro_deleterev_link', function( e ) {
			e.preventDefault();
			var $link = $( this );
			var rid   = $link.data( 'rid' );

			if ( ! window.confirm( 'Delete this review from your WordPress database? This does NOT affect your reviews on TripAdvisor and cannot be undone.' ) ) {
				return;
			}
			if ( $link.data( 'busy' ) ) {
				return;
			}
			$link.data( 'busy', true );

			$.post( ajaxurl, {
				action:              'tripadvisor_hide_review',
				wptripadvisor_nonce: adminjs_script_vars.wptripadvisor_nonce,
				reviewid:            rid,
				myaction:            'deleterev'
			}).done(function( response ) {
				if ( typeof response === 'string' && response.indexOf( '-success' ) !== -1 ) {
					$( '#' + rid ).fadeOut( 300, function() { $( this ).remove(); } );
					showListNotice( 'Review deleted.' );
				} else {
					showListNotice( 'Could not delete the review. Please try again.', true );
				}
			}).fail(function() {
				showListNotice( 'Could not reach the server. Please try again.', true );
			}).always(function() {
				$link.data( 'busy', false );
			});
		});

		// Save via AJAX: update the row in place and close the popup — the
		// page never reloads, so the list keeps its current scroll position.
		$editForm.on( 'submit', function( e ) {
			e.preventDefault();

			var $submitBtn   = $( '#wprevpro_submitreviewbtn' );
			var rid          = $( '#editrid' ).val();
			var avatarUrl    = $( '#wprevpro_nr_avatar_url' ).val();
			var reviewDate   = $( '#wprevpro_nr_date' ).val();
			var originalText = $submitBtn.val();

			$submitBtn.prop( 'disabled', true ).val( 'Saving...' );
			$( '#wprevpro_save_review_msg' ).empty();

			$.post( ajaxurl, {
				action:      'wptripadvisor_save_review',
				wptripadvisor_nonce: adminjs_script_vars.wptripadvisor_nonce,
				editrid:     rid,
				avatar_url:  avatarUrl,
				review_date: reviewDate
			}).done(function( response ) {
				if ( response && response.success ) {
					var $row = $( '#' + rid );
					if ( response.data && response.data.userpic ) {
						$row.find( '.wprevpro_pic_cell img' ).attr( 'src', response.data.userpic );
					}
					if ( response.data && response.data.date ) {
						$row.find( '.wprevpro_date_cell' ).text( response.data.date );
					}
					closeReviewModal();
					showListNotice( 'Review Updated!' );
					} else {
					var message = ( response && response.data && response.data.message ) ? response.data.message : 'Something went wrong. Please try again.';
					$( '#wprevpro_save_review_msg' ).html( '<p class="wprevpro_error_msg">' + message + '</p>' );
				}
			}).fail(function() {
				$( '#wprevpro_save_review_msg' ).html( '<p class="wprevpro_error_msg">Could not reach the server. Please try again.</p>' );
			}).always(function() {
				$submitBtn.prop( 'disabled', false ).val( originalText );
			});
		});
		
	});

})( jQuery );

(function( $ ) {
	'use strict';

	$(function(){

		$( ".wprs_rd_more" ).click(function() {
			$(this ).hide();
			$(this ).next("span").show(0, function() {
				$(this ).css('opacity', '1.0');
			  });
			$(this ).closest( ".wprev-slider-widget" ).css( "height", "auto" );
			$(this ).closest( ".wprev-slider" ).css( "height", "auto" );
		});

		// Mobile one-review-per-slide rewrite (matches Google free behavior).
		$( ".wprev-slider, .wprev-slider-widget" ).each(function() {
			var oneonmobile = $(this).attr( "data-onemobil" );
			if(oneonmobile=="yes" && window.matchMedia("(max-width: 600px)").matches){
				var $slider = $(this);
				var $newlis = [];
				$slider.find('li').each(function() {
					$(this).find('.w3_wprs-col').each(function() {
						var $col = $(this).clone();
						$col.removeClass (function (index, className) {
							return (className.match (/(^|\s)l\S+/g) || []).join(' ');
						});
						$col.addClass('l12');
						var $li = $('<li></li>').append($col);
						$newlis.push($li);
					});
				});
				if($newlis.length>0){
					$slider.find('ul').empty().append($newlis);
				}
			}
		});
		
		$( ".wprev-slider" ).each(function( index ) {
			createaslider(this,'shortcode');
		});
		$( ".wprev-slider-widget" ).each(function( index ) {
			createaslider(this,'widget');
		});

		function createaslider(thissliderdiv,type){
			var sliderhideprevnext = $(thissliderdiv).attr( "data-sliderhideprevnext" );
			var sliderhidedots = $(thissliderdiv).attr( "data-sliderhidedots" );
			var sliderautoplay = $(thissliderdiv).attr( "data-sliderautoplay" );
			var slidespeed = $(thissliderdiv).attr( "data-slidespeed" );
			var slideautodelay = $(thissliderdiv).attr( "data-slideautodelay" );
			var sliderfixedheight = $(thissliderdiv).attr( "data-sliderfixedheight" );
			var revsameheight = $(thissliderdiv).attr( "data-revsameheight" );

			// Backward-compatible defaults matching previous hardcoded TripAdvisor JS.
			var showarrows = true;
			if(type=='widget'){
				showarrows = false;
			}
			if(sliderhideprevnext=="yes"){
				showarrows = false;
			}

			var shownav = true;
			if(sliderhidedots=="yes"){
				shownav = false;
			}

			var sautoplay = false;
			if(sliderautoplay=="yes"){
				sautoplay = true;
			}

			var sspeed = 750;
			if(slidespeed && !isNaN(parseFloat(slidespeed)) && parseFloat(slidespeed)>0){
				sspeed = parseFloat(slidespeed) * 1000;
			}

			var sdelay = 5000;
			if(type=='widget'){
				sdelay = 3000;
			}
			if(slideautodelay && !isNaN(parseFloat(slideautodelay)) && parseFloat(slideautodelay)>0){
				sdelay = parseFloat(slideautodelay) * 1000;
			}
			if(sdelay < sspeed){
				sdelay = sspeed;
			}

			var sanimate = true;
			if(sliderfixedheight=="yes"){
				sanimate = false;
			}

			$( thissliderdiv ).find('li').show();
			var slider = $( thissliderdiv ).wprs_unslider(
					{
					autoplay:sautoplay,
					infinite:false,
					delay: sdelay,
					speed: sspeed,
					animation: 'horizontal',
					arrows: showarrows,
					nav: shownav,
					animateHeight: sanimate,
					activeClass: 'wprs_unslider-active',
					}
				);

			if(sanimate){
				setTimeout(function(){
					var firstheight = $(thissliderdiv).find('.wprs_unslider-active').height();
					$(thissliderdiv).css( 'height', firstheight );
				}, 500);
			}

			if(sautoplay){
				slider.on('mouseover', function() {slider.data('wprs_unslider').stop();}).on('mouseout', function() {slider.data('wprs_unslider').start();});
			}

			if(revsameheight=='yes'){
				var maxh = 0;
				$(thissliderdiv).find('.indrevdiv').each(function(){
					var h = $(this).outerHeight();
					if(h>maxh){ maxh = h; }
				});
				if(maxh>0){
					$(thissliderdiv).find('.indrevdiv').css('min-height', maxh+'px');
				}
			}
		}

		$(".wptripadvisor_t1_outer_div, .wprevpro_t6_outer_div, .wptripadvisor_t1_outer_div_widget, .wprevpro_t6_outer_div_widget").on('mouseenter touchstart', '.wprevtooltip', function(e) {
			var titleText = $(this).attr('data-wprevtooltip');
			$(this).data('tiptext', titleText).removeAttr('data-wprevtooltip');
			$('<p class="wprevpro_tooltip"></p>').text(titleText).appendTo('body').css('top', (e.pageY - 15) + 'px').css('left', (e.pageX + 10) + 'px').fadeIn('slow');
		});
		$(".wptripadvisor_t1_outer_div, .wprevpro_t6_outer_div, .wptripadvisor_t1_outer_div_widget, .wprevpro_t6_outer_div_widget").on('mouseleave touchend', '.wprevtooltip', function(e) {
			$(this).attr('data-wprevtooltip', $(this).data('tiptext'));
			$('.wprevpro_tooltip').remove();
		});
		$(".wptripadvisor_t1_outer_div, .wprevpro_t6_outer_div, .wptripadvisor_t1_outer_div_widget, .wprevpro_t6_outer_div_widget").on('mousemove', '.wprevtooltip', function(e) {
			$('.wprevpro_tooltip').css('top', (e.pageY - 15) + 'px').css('left', (e.pageX + 10) + 'px');
		});

		// Lazy-load Lity only when review media thumbnails are on the page.
		// Use a manual open so we don't also fire Lity's built-in [data-lity] handler (double lightbox).
		function wprevBindMediaLightbox($root) {
			var $scope = $root && $root.length ? $root : $(document);
			$scope.off('click.wprevlity', 'a.wprev_media_img_a').on('click.wprevlity', 'a.wprev_media_img_a', function(e) {
				e.preventDefault();
				e.stopImmediatePropagation();
				var href = $(this).attr('href');
				if (!href || typeof lity !== 'function') {
					return;
				}
				lity(href);
			});
		}

		function wprevEnsureLity(callback) {
			if (typeof lity === 'function') {
				callback();
				return;
			}
			var base = (typeof wprevpublicjs_script_vars !== 'undefined' && (wprevpublicjs_script_vars.wprevplugin_url || wprevpublicjs_script_vars.wprevpluginsurl))
				? (wprevpublicjs_script_vars.wprevplugin_url || wprevpublicjs_script_vars.wprevpluginsurl)
				: '';
			if (!base) {
				return;
			}
			if (!document.getElementById('wprev_lity_css')) {
				var head = document.getElementsByTagName('head')[0];
				var link = document.createElement('link');
				link.id = 'wprev_lity_css';
				link.rel = 'stylesheet';
				link.type = 'text/css';
				link.href = base + '/public/css/lity.min.css';
				link.media = 'all';
				head.appendChild(link);
			}
			$.getScript(base + '/public/js/lity.min.js', callback);
		}

		if ($('.wprev_media_div a.wprev_media_img_a').length > 0) {
			wprevEnsureLity(function() {
				wprevBindMediaLightbox($(document));
			});
		}
		
	});

})( jQuery );

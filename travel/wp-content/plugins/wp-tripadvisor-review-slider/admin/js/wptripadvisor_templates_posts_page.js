(function( $ ) {
	'use strict';

	/**
	 * All of the code for your admin-facing JavaScript source
	 * should reside in this file.
	 *
	 * Note: It has been assumed you will write jQuery code here, so the
	 * $ function reference has been prepared for usage within the scope
	 * of this function.
	 *
	 * This enables you to define handlers, for when the DOM is ready:
	 * $( document ).ready(function() same as
	 * $(function() {
	 *
	 * });
	 *
	 * When the window is loaded:
	 *
	 * $( window ).load(function() {
	 *
	 * });
	 *
	 * ...and/or other possibilities.
	 *
	 * Ideally, it is not considered best practise to attach more than a
	 * single DOM-ready or window-load handler for a particular page.
	 * Although scripts in the WordPress core, Plugins and Themes may be
	 * practising this, we should strive to set a better example in our own work.
	 */
	 
	 //document ready
	$(function(){
	
		var prestyle = "";
		var isNewTemplateForm = false;
		var isResettingColors = false;
		//color picker
		var myOptions = {
			// a callback to fire whenever the color changes to a valid color
			change: function(event, ui){
				var color = ui.color.toString();
				var element = event.target;
				var curid = $(element).attr('id');
				$( element ).val(color)
				if(isResettingColors){
					return;
				}
				//manuall change after css. hack since jquery can't access before and after elements    border-top: 30px solid #943939;
				if(curid=='wprevpro_template_misc_bgcolor1'){
					prestyle = "<style>.wptripadvisor_t1_DIV_2::after,.wprev_preview_bg1_T1::after{ border-top: 30px solid "+color+"; }</style>";
				}
				changepreviewhtml();
			},
			// a callback to fire when the input is emptied or an invalid color
			clear: function() {}
		};
		 
		$('.my-color-field').wpColorPicker(myOptions);
		

		
		//for style preview changes.-------------
		//var starhtml = '<span class="wprevpro_star_imgs"><img src="' + adminjs_script_vars.pluginsUrl + '/public/partials/imgs/stars_5_yellow.png" alt="" >&nbsp;&nbsp;</span>';
		var starhtml = '<span class="starloc1 wprevpro_star_imgs wprevpro_star_imgsloc1"><span class="svgicons svg-wprsp-star"></span><span class="svgicons svg-wprsp-star"></span><span class="svgicons svg-wprsp-star"></span><span class="svgicons svg-wprsp-star"></span><span class="svgicons svg-wprsp-star"></span></span>';
		var sampltext = 'This is a sample review. Hands down the best experience we have had in the southeast! Awesome accommodations, great staff. We will gladly drive four hours for this gem!';
		var datehtml = '<span id="wprev_showdate">1/12/2017</span>';
		var lastnamehtml = '<span id="wprev_lastname">Doe</span>';
		var imagehref = adminjs_script_vars.pluginsUrl + '/public/partials/imgs/tripadvisor_mystery_man.png';
		var iconhref = adminjs_script_vars.pluginsUrl + '/public/partials/imgs/tripadvisor_small_icon.png';
		var imagehrefmystery = adminjs_script_vars.pluginsUrl + '/public/partials/imgs/tripadvisor_mystery_man.png';
		var avatarimg = imagehref;
		var verified1 = '<span class="verifiedloc1 wprevpro_verified_svg wprevtooltip" data-wprevtooltip="Verified on TripAdvisor"><span class="svgicons svg-wprsp-verified"></span></span>';
		
		var displayname = 'John '+lastnamehtml;
		var quoteimg = adminjs_script_vars.pluginsUrl + '/admin/partials/testimonial_quote.png';
		var samplemediaimage = adminjs_script_vars.pluginsUrl + '/admin/partials/samplemediaimage.jpg';
		var productimg = adminjs_script_vars.pluginsUrl + '/admin/partials/default-product-image2.png';
		var displayname3 = '<div id="wprev_showname">'+displayname+'</div>';
		
		var starimg = adminjs_script_vars.pluginsUrl + '/public/partials/imgs/tripadvisor_stars_5.png';
		var style1html ='<div class="wptripadvisor_t1_outer_div w3_wprs-row-padding">	\
							<div class="wptripadvisor_t1_DIV_1 w3_wprs-col">	\
								<div class="wptripadvisor_t1_DIV_2 wprev_preview_bg1_T1 wprev_preview_bradius_T1">	\
									<p class="wptripadvisor_t1_P_3 wprev_preview_tcolor1_T1">	\
										<span class="wprevpro_star_imgs_T1"><img src="'+starimg+'" alt="stars" class="wptripadvisor_t1_star_img_file">'+verified1+'</span>'+sampltext+'		</p>	\
										<img id="wprev_showicon" src="'+iconhref+'" alt="TripAdvisor Logo" class="wptripadvisor_t1_tripadvisor_logo siteicon">	\
								</div><span class="wptripadvisor_t1_A_8"><img src="'+avatarimg+'" alt="thumb" class="wptripadvisor_t1_IMG_4 wprev_avatar_opt"></span> <span class="wptripadvisor_t1_SPAN_5 wprev_preview_tcolor2_T1">'+displayname+'<br><span class="wprev_showdate_T1">'+datehtml+'</span> </span>	\
							</div>	\
							</div>';

		var style6html = '<div class="wprevpro_t6_outer_div w3_wprs-row-padding">	\
							<div class="wpproslider_t6_DIV_1 w3_wprs-col l12">	\
								<div class="wpproslider_t6_DIV_2 wprev_preview_bg1 wprev_preview_bradius">	\
									<div class="wpproslider_t6_DIV_2_top" style="line-height:24px;">	\
										<div class="wpproslider_t6_DIV_3L"><img src="'+avatarimg+'" class="wprev_avatar_opt wpproslider_t6_IMG_2"></div>	\
										<div class="wpproslider_t6_DIV_3">	\
											<div class="wpproslider_t6_STRONG_5 wprev_preview_tcolor2 t6displayname">'+displayname+'</div>	\
											<div class="wpproslider_t6_star_DIV"><span class="wprevpro_star_imgs"><img src="'+starimg+'" alt="stars" class="wptripadvisor_t6_star_img_file">'+verified1+'</span></div>	\
											<div class="wpproslider_t6_SPAN_6 wprev_preview_tcolor2 t6datediv">'+datehtml+'</div>	\
										</div>	\
									</div>	\
									<div class="wpproslider_t6_DIV_4"><p class="wpproslider_t6_P_4 wprev_preview_tcolor1">'+sampltext+'</p></div>	\
									<div class="wpproslider_t6_DIV_3_logo"><img id="wprev_showicon" src="'+iconhref+'" alt="TripAdvisor Logo" class="wprevpro_t6_site_logo siteicon"></div>	\
								</div></div></div>';
		
	var style2html = '<div class="wprevpro_t2_outer_div w3_wprs-row-padding">	\
							<div class="wpproslider_t2_DIV_1 w3_wprs-col l12">	\
								<div class="wpproslider_t2_DIV_2 wprev_preview_bg1 wprev_preview_bradius">	\
								<img src="'+avatarimg+'" class="wprev_avatar_opt wpproslider_t2_IMG_2">	\
								<div class="wpproslider_t2_DIV_3">	\
									<p class="wpproslider_t2_P_4 wprev_preview_tcolor1">	\
										'+starhtml+''+verified1+''+sampltext+'		</p> <strong class="wpproslider_t2_STRONG_5 wprev_preview_tcolor2">'+displayname+'</strong> <span class="wpproslider_t2_SPAN_6 wprev_preview_tcolor2">'+datehtml+'</span>	\
										<img id="wprev_showicon" src="'+iconhref+'" alt="TripAdvisor Logo" class="wprevpro_t2_site_logo siteicon">	\
								</div></div></div></div>';

		var style3html = '<div class="wprevpro_t3_outer_div w3_wprs-row-padding">	\
				<div class="wpproslider_t3_DIV_1 w3_wprs-col l12">	\
			<div class="wpproslider_t3_DIV_1a wprev_preview_bg2 wprev_preview_bradius">	\
				<div class="wpproslider_t3_DIV_2 wprev_preview_bg1 wprev_preview_tcolor2 wprev_preview_tcolor3">	\
					<div class="wpproslider_t3_avatar_div">	\
					<img src="'+avatarimg+'" class="wprev_avatar_opt wpproslider_t3_avatar">	\
					</div>	\
					'+displayname3+'</div>	\
				<p class="wpproslider_t3_P_3 wprev_preview_tcolor1"><img src="'+quoteimg+'" alt="" class="wpproslider_t3_quote">'+starhtml+''+verified1+''+sampltext+' '+datehtml+'</p>	\
				<img id="wprev_showicon" src="'+iconhref+'" alt="TripAdvisor Logo" class="wprevpro_t3_site_logo siteicon">	\
			</div>	\
		</div>	\
		</div>';

		var style4html = '<div class="wprevpro_t4_outer_div w3_wprs-row-padding">	\
			<div class="wpproslider_t4_DIV_1 w3_wprs-col l12">	\
		<div class="wpproslider_t4_DIV_1a wprev_preview_bg1 wprev_preview_bradius">	\
			<div class="wpproslider_t4_avatar_div">	\
			<img src="'+avatarimg+'" class="wprev_avatar_opt wpproslider_t4_IMG_2">	\
			</div>	\
			<h3 class="wpproslider_t4_H3_3 wprev_preview_tcolor1">'+displayname+'</h3>	\
			<span class="wpproslider_t4_SPAN_4">'+starhtml+''+verified1+'</span>	\
			<p class="wpproslider_t4_P_5 wprev_preview_tcolor2">'+sampltext+'</p>	\
			<span class="wpproslider_t4_date wprev_preview_tcolor3">'+datehtml+'</span>	\
			<div><img id="wprev_showicon" src="'+iconhref+'" alt="TripAdvisor Logo" class="wprevpro_t4_site_logo siteicon"></div>	\
		</div></div></div>';

		var style5html = '<div class="wprevpro_t5_outer_div w3_wprs-row-padding">	\
							<div class="wpproslider_t5_DIV_1 w3_wprs-col l12">	\
								<div class="wpproslider_t5_DIV_2 wprev_preview_bg1 wprev_preview_bradius">	\
								<div class="wpproslider_t5_DIV_3L"><img src="'+avatarimg+'" class="wprev_avatar_opt wpproslider_t5_IMG_2"><span class="wpproslider_t5_STRONG_5 wprev_preview_tcolor2">'+displayname+'</span></div>	\
								<div class="wpproslider_t5_DIV_3">	\
									<p class="wpproslider_t5_P_4 wprev_preview_tcolor1">	\
										'+starhtml+''+verified1+''+sampltext+'<span class="wpproslider_t5_SPAN_6 wprev_preview_tcolor2"> - '+datehtml+'</span></p> 	\
								</div>	\
								<div class="wpproslider_t5_DIV_3_logo"><img id="wprev_showicon" src="'+iconhref+'" alt="TripAdvisor Logo" class="wprevpro_t5_site_logo wprevsiteicon siteicon"></div>	\
								</div></div></div>';

		var style7html = '<div class="wprevpro_t7_outer_div w3_wprs-row-padding">	\
							<div class="wpproslider_t7_DIV_1 w3_wprs-col l12">	\
								<div class="wpproslider_t7_DIV_2 wprev_preview_bg1 wprev_preview_bradius">	\
								<div class="wpproslider_t7_DIV_2_top">	\
									<div class="wpproslider_t7_DIV_3L">	\
										<div class="wpproslider_t7_DIV_3_logo"><img id="wprev_showicon" src="'+iconhref+'" alt="TripAdvisor Logo" class="wprevpro_t7_site_logo siteicon"></div>	\
										<div class="wpproslider_t7_star_DIV">'+starhtml+''+verified1+'</div>	\
									</div>	\
								</div>	\
								<div class="wpproslider_t7_DIV_4">	\
									<div class="wpproslider_t7_DIV_3">	\
										<p class="wpproslider_t7_P_4 wprev_preview_tcolor1">"'+sampltext+'"</p> 	\
									</div>	\
									<div class="wpproslider_t7_STRONG_5 wprev_preview_tcolor2 t7displayname">'+displayname+'</div>	\
									<div class="wpproslider_t7_SPAN_6 wprev_preview_tcolor2 t7datediv">'+datehtml+'</div>	\
								</div>	\
								</div></div></div>';

		var style8html = '<div class="wprevpro_t8_outer_div w3_wprs-row-padding">	\
							<div class="wpproslider_t8_DIV_1 w3_wprs-col l12">	\
								<div class="wpproslider_t8_DIV_2 wprev_preview_bg1 wprev_preview_bradius">	\
									<div class="wpproslider_t8_DIV_2_top" style="line-height:24px;">	\
										<div class="wpproslider_t8_DIV_3">	\
											<div class="wpproslider_t8_STRONG_5 wprev_preview_tcolor2 t8displayname">'+displayname+'<span class="wpproslider_t8_SPAN_6 wprev_preview_tcolor2 t8datediv">'+datehtml+'</span></div>	\
											<div class="wpproslider_t8_star_DIV">'+starhtml+''+verified1+'</div>	\
											<div class="wpproslider_t8_DIV_4"><p class="wpproslider_t8_P_4 wprev_preview_tcolor1">	\
											'+sampltext+'</p> 	\
										</div>	\
										</div>	\
									</div>	\
									<div class="wpproslider_t8_DIV_3_logo"><img id="wprev_showicon" src="'+iconhref+'" alt="TripAdvisor Logo" class="wprevpro_t8_site_logo siteicon"></div>	\
								</div></div></div>';

		var style9html = '<div class=" wprevpro_t9_DIV_1 w3_wprs-col">	\
								<div class="wpproslider_t9_DIV_1a">	\
									<div class="wpproslider_t9_DIV_2 wprev_preview_bg1 wprev_preview_bradius">	\
									<div class="wpproslider_t9_DIV_2_top">	\
										<div class="wpproslider_t9_DIV_3L"><img src="'+avatarimg+'" alt="Avatar" class="wprev_avatar_opt wpproslider_t9_IMG_2 wprevpro_avatarimg"></div>	\
										<div class="wpproslider_t9_star_DIV wprevpro_star_imgs_T9">'+starhtml+''+verified1+'</div>	\
										<div class="wpprooutoffive">5 out of 5 stars</div><div class="wpproslider_t9_DIV_3_logo"><img id="wprev_showicon" src="'+iconhref+'" alt="TripAdvisor Logo" class="wprevpro_t9_site_logo siteicon"></div>	\
									</div>	\
									<div class="wpproslider_t9_DIV_3">	\
											<div class="t9displayname wpproslider_t9_STRONG_5 wprev_preview_tcolor2">'+displayname+'</div>	\
											<div class="wpproslider_t9_SPAN_6 wprev_preview_tcolor2"><span class="wprev_showdate_T9 wprev_preview_tcolor2">'+datehtml+'</span></div>	\
									</div>	\
									<div class="indrevdiv wpproslider_t9_DIV_4">	\
											<p class="wpproslider_t9_P_4 wprev_preview_tcolor1">'+sampltext+'</p>	\
									</div></div></div></div>';

		var style10html = '<div class="wprevpro_t10_outer_div w3_wprs-row-padding">	\
							<div class="wpproslider_t10_DIV_1 w3_wprs-col l12">	\
								<div class="wpproslider_t10_DIV_2 wprev_preview_bg1 wprev_preview_bradius">	\
									<div class="wpproslider_t10_DIV_2_top" style="line-height:24px;">	\
										<div class="wpproslider_t10_DIV_3L"><img src="'+avatarimg+'" class="wprev_avatar_opt wpproslider_t10_IMG_2"></div>	\
										<div class="wpproslider_t10_DIV_3">	\
											<div class="wpproslider_t10_STRONG_5 wprev_preview_tcolor2 t10displayname"><span class="t10_revname wprev_preview_tcolor1">'+displayname+'</span> left us a 5 star review</div>	\
											<div class="wpproslider_t10_star_DIV">'+starhtml+''+verified1+' <span class="t10_onsite"> on TripAdvisor</span></div>	\
											<div class="wpproslider_t10_SPAN_6 t10datediv">'+datehtml+'</div>	\
										</div>	\
									</div>	\
									<div class="wpproslider_t10_DIV_3_logo"><img id="wprev_showicon" src="'+iconhref+'" alt="TripAdvisor Logo" class="wprevpro_t10_site_logo siteicon"></div>	\
								</div></div></div>';

		var style11html = '<div class="wprevpro_t11_outer_div w3_wprs-row-padding">	\
							<div class="wpproslider_t11_DIV_1 w3_wprs-col l12">	\
								<div class="wpproslider_t11_DIV_2 wpproslider_t11_has_woo_image wprev_preview_bg1 wprev_preview_bradius">	\
								<div class="mscpic-img-side"><div class="mscpic-img-body"><img src="'+productimg+'" class="miscpic-listing-image" alt="sample product image"></div></div>	\
									<div class="wpproslider_t11_DIV_2_top" style="line-height:24px;">	\
										<div class="wpproslider_t11_DIV_3">	\
											<div class="wpproslider_t11_STRONG_5 wprev_preview_tcolor2 t11displayname">'+displayname+'<span class="wpproslider_t11_SPAN_6 wprev_preview_tcolor2 t11datediv">'+datehtml+'</span></div>	\
											<div class="wpproslider_t11_star_DIV">'+starhtml+''+verified1+'</div>	\
											<div class="wpproslider_t11_DIV_4"><p class="wpproslider_t11_P_4 wprev_preview_tcolor1">	\
											'+sampltext+'</p> 	\
											<div class="miscpicdiv mpdiv_t11 wprev_preview_tcolor1"><div class="mscpic-body wprev_preview_tcolor1"><span>Sample Product Title</span></div></div>	\
										</div>	\
										</div>	\
									</div>	\
									<div class="wpproslider_t11_DIV_3_logo"><img id="wprev_showicon" src="'+iconhref+'" alt="TripAdvisor Logo" class="wprevpro_t11_site_logo siteicon"></div>	\
								</div></div></div>';

		var style12html = '<div class="wprevpro_t12_outer_div w3_wprs-row-padding">	\
			<div class="wpproslider_t12_DIV_1 w3_wprs-col l12">	\
		<div class="wpproslider_t12_DIV_1a wprev_preview_bg1 wprev_preview_bradius">	\
			<span class="wpproslider_t12_SPAN_4">'+starhtml+''+verified1+'</span>	\
			<p class="wpproslider_t12_P_5 wprev_preview_tcolor2">'+sampltext+'</p>	\
			<div class="wpproslider_t12_avatar_div">	\
			<img src="'+avatarimg+'" class="wprev_avatar_opt wpproslider_t12_IMG_2">	\
			</div>	\
			<div><h3 class="wpproslider_t12_H3_3 wprev_preview_tcolor1">'+displayname+'</h3></div>	\
			<div><span class="wpproslider_t12_date wprev_preview_tcolor3">'+datehtml+'</span></div>	\
			<div><img id="wprev_showicon" src="'+iconhref+'" alt="TripAdvisor Logo" class="wprevpro_t12_site_logo siteicon"></div>	\
		</div></div></div>';

		var style13html = '<div class="wprevpro_t13_outer_div w3_wprs-col l4 outerrevdiv" style="width: 100%; display: inline-block;">	\
	<div class="wpproslider_t6_DIV_1a"><div class="indrevdiv wpproslider_t13_DIV_2 wprev_preview_bg1 wprev_preview_bradius"><div class="wpproslider_t13_DIV_4"><div class="indrevtxt wpproslider_t13_P_4 wprev_preview_tcolor1">	\
	<div class="wprev_preview_tcolor1">'+sampltext+'</div>	\
	</div></div>	\
		<div class="wpproslider_t13_DIV_2_bot">	\
			<div class="wpproslider_t6_DIV_3L"><img src="'+avatarimg+'" alt="Avatar" class="wprev_avatar_opt wpproslider_t13_IMG_2 wprevpro_avatarimg"></div>	\
			<div class="wpproslider_t13_DIV_3 wprev_preview_tcolor2">	\
				<div class="t13displayname wpproslider_t6_STRONG_5 wprev_preview_tcolor2">'+displayname+'</div>	\
				<div class="wpproslider_t13_star_DIV"><span class="wprevpro_star_imgs_T13">'+starhtml+''+verified1+'</span>	\
			</div><div class="wpproslider_t13_SPAN_6 wprev_preview_tcolor2"><span class="wprev_showdate_T13">'+datehtml+'</span></div></div>	\
		</div><div class="wpproslider_t13_DIV_3_logo">	\
		<img id="wprev_showicon" src="'+iconhref+'" alt="TripAdvisor Logo" class="wprevpro_t6_site_logo siteicon">	\
		</div></div></div></div>';

		var style14html = '<div class="wprevpro_t14_DIV_1 wprevpro_t14_outer_div outerrevdiv wprevpro_t14_DIV_1_preview">	\
			<div class="wprevpro_t14_DIV_1a">	\
				<div class="wprevpro_t14_DIV_2 wprev_preview_bradius indrevdiv">	\
				<div class="wprevpro_t14_image_wrapper" style="background-image: url('+samplemediaimage+');"> \
						<div class="wprevpro_t14_review_text_overlay wprev_preview_tcolor1 wprev_preview_bg1">	\
						<div class="indrevlineclamp wprev_preview_tcolor1">'+sampltext+'</div>	\
						</div></div>	\
					<div class="wprevpro_t14_footer indrevtxt wprev_preview_bg2 wprevpro_t14_SPAN_5 wprev_preview_tcolor2">	\
						<div class="wprevpro_t14_avatarimg"><span class="wprevpro_t14_A_8"><img src="'+avatarimg+'" alt="Avatar" class="wprev_avatar_opt wprevpro_t14_IMG_4 wprevpro_avatarimg"></span></div>	\
						<div class="wprevpro_t14_SPAN_5">	\
							<div class="wprevpro_t14_rname wprevpro_t14_name wprev_preview_tcolor2">'+displayname+'</div>	\
							<div class="wprevpro_star_imgs_T14">'+starhtml+''+verified1+'</div>	\
							<div class="wprevpro_t14_showdate wprevpro_t14_date wprev_preview_tcolor3">'+datehtml+'</div>	\
						</div>	\
						<img id="wprev_showicon" src="'+iconhref+'" alt="TripAdvisor Logo" class="wprevpro_t14_site_logo siteicon">	\
						</div>	\
				</div>	\
			</div>	\
		</div>';

		var style15html = '<div class="wpproslider_t15_DIV_1 w3_wprs-col l4 outerrevdiv" style="width: 100%; display: inline-block;">	\
		<div class="indrevdiv wpproslider_t15_DIV_1a wprev_preview_bg1 wprev_preview_bradius">	\
			<div class="wpproslider_t15_avatar_div"><img src="'+avatarimg+'" alt="Avatar" class="wpproslider_t15_IMG_2 wprevpro_avatarimg wprev_avatar_opt"></div>	\
			<div class="wpproslider_t15_H3_3 wprev_preview_tcolor1">'+displayname+'</div>	\
			<span class="wprev_showdate_T15 wprev_preview_tcolor3">'+datehtml+'</span>	\
			<span class="wpproslider_t15_SPAN_4">'+starhtml+''+verified1+'</span>	\
			<div class="indrevtxt wpproslider_t15_P_5 wprev_preview_tcolor2"><p class="wpproslider_t15_P_5 wprev_preview_tcolor2">'+sampltext+'</p></div>	\
			<div class="wprevt15_icon_div"><img id="wprev_showicon" src="'+iconhref+'" alt="TripAdvisor Logo" class="wprevpro_t15_site_logo siteicon"></div>	\
		</div></div>';
		
		changepreviewhtml();

		function buildInitialsAvatarDataUri(name, size){
			size = size || 100;
			name = (name || 'U').toString().trim();
			var words = name.split(/\s+/).filter(Boolean);
			var initials;
			if(words.length >= 2){
				initials = (words[0].charAt(0) + words[words.length - 1].charAt(0)).toUpperCase();
			} else if(name){
				initials = name.charAt(0).toUpperCase();
			} else {
				initials = 'U';
			}
			var hash = 0;
			for(var i = 0; i < name.length; i++){
				hash = ((hash << 5) - hash) + name.charCodeAt(i);
				hash |= 0;
			}
			var r = (hash >> 16) & 255;
			var g = (hash >> 8) & 255;
			var b = hash & 255;
			if(((r * 299) + (g * 587) + (b * 114)) / 1000 > 200){
				r = Math.max(0, r - 50);
				g = Math.max(0, g - 50);
				b = Math.max(0, b - 50);
			}
			var bg = '#' + [r, g, b].map(function(v){
				var h = v.toString(16);
				return h.length === 1 ? '0' + h : h;
			}).join('');
			var fontSize = Math.round(size * 0.4);
			var svg = '<svg xmlns="http://www.w3.org/2000/svg" width="'+size+'" height="'+size+'" viewBox="0 0 '+size+' '+size+'">' +
				'<rect width="100%" height="100%" fill="'+bg+'"/>' +
				'<text x="50%" y="50%" dy=".1em" fill="#ffffff" font-family="Arial,Helvetica,sans-serif" font-size="'+fontSize+'" font-weight="bold" text-anchor="middle" dominant-baseline="middle">'+initials+'</text>' +
				'</svg>';
			return 'data:image/svg+xml;base64,' + btoa(svg);
		}

		//reset colors to default
		$( "#wprevpro_pre_resetbtn" ).click(function() {
			resetcolors();
		});
		function resetcolors(){
				isResettingColors = true;
				var templatenum = $( "#wprevpro_template_style" ).val();
				//reset colors to default
				if(templatenum=='1'){
					
					$( "#wprevpro_template_misc_bradius" ).val('0');
					$( "#wprevpro_template_misc_bgcolor1" ).val('#ffffff');
					$( "#wprevpro_template_misc_bgcolor2" ).val('#ffffff');
					$( "#wprevpro_template_misc_tcolor1" ).val('#777777');
					$( "#wprevpro_template_misc_tcolor2" ).val('#555555');
					prestyle="";
					//reset color picker
					$('#wprevpro_template_misc_bgcolor1').iris('color', '#ffffff');
					$('#wprevpro_template_misc_bgcolor2').iris('color', '#ffffff');
					$( "#wprevpro_template_misc_tcolor1" ).iris('color','#777777');
					$( "#wprevpro_template_misc_tcolor2" ).iris('color','#555555');
				} else if(templatenum=='6'){
					$( "#wprevpro_template_misc_bradius" ).val('0');
					$( "#wprevpro_template_misc_bgcolor1" ).val('#fdfdfd');
					$( "#wprevpro_template_misc_bgcolor2" ).val('#eeeeee');
					$( "#wprevpro_template_misc_tcolor1" ).val('#555555');
					$( "#wprevpro_template_misc_tcolor2" ).val('#555555');
					prestyle="";
					$('#wprevpro_template_misc_bgcolor1').iris('color', '#fdfdfd');
					$('#wprevpro_template_misc_bgcolor2').iris('color', '#eeeeee');
					$( "#wprevpro_template_misc_tcolor1" ).iris('color','#555555');
					$( "#wprevpro_template_misc_tcolor2" ).iris('color','#555555');
				}
				isResettingColors = false;
				changepreviewhtml();
		}

		
		//on template num change
		$( "#wprevpro_template_style" ).change(function() {
				//reset colors on new templates (auto-save sets edittid, so track form state separately)
				if(isNewTemplateForm || $( "#edittid" ).val()==""){
				resetcolors();
				} else {
				changepreviewhtml();
				}
		});
		
		$( "#wprevpro_template_misc_showstars" ).change(function() {
				changepreviewhtml();
		});
		$( "#wprevpro_template_misc_showdate" ).change(function() {
				changepreviewhtml();
		});
		$( "#wprevpro_template_misc_showicon" ).change(function() {
				changepreviewhtml();
		});
		$( "#wprevpro_template_misc_bradius" ).change(function() {
				changepreviewhtml();
		});
		$( "#wprevpro_template_misc_bgcolor1" ).change(function() {
				changepreviewhtml();
		});
		$( "#wprevpro_template_misc_bgcolor2" ).change(function() {
				changepreviewhtml();
		});
		$( "#wprevpro_template_misc_tcolor1" ).change(function() {
				changepreviewhtml();
		});
		$( "#wprevpro_template_misc_tcolor2" ).change(function() {
				changepreviewhtml();
		});
		$( "#wprevpro_template_misc_tfont1" ).on('change keyup', function() {
				changepreviewhtml();
		});
		$( "#wprevpro_template_misc_tfont2" ).on('change keyup', function() {
				changepreviewhtml();
		});
		$( "#wprevpro_template_misc_avataropt" ).change(function() {
				changepreviewhtml();
		});
		$( "#wprevpro_template_misc_verified" ).change(function() {
				changepreviewhtml();
		});
		
		$( "#wprevpro_template_misc_lastname" ).change(function() {
				changepreviewhtml();
		});
		
		//custom css change preview
		var lastValue = '';
		$("#wptripadvisor_template_css").on('change keyup paste mouseup', function() {
			if ($(this).val() != lastValue) {
				lastValue = $(this).val();
				changepreviewhtml();
			}
		});

		function isProOnlyTemplateStyle(val){
			return val !== '1' && val !== '6' && val !== '' && val != null;
		}
		function updateProStyleNote(templatenum){
			$( "#wprevpro_pro_save_note" ).hide();
			if(isProOnlyTemplateStyle(templatenum)){
				$( "#wprevpro_template_style" ).addClass('wprevpro-pro-selected');
				$( "#wprevpro_pro_style_note" ).show();
			} else {
				$( "#wprevpro_template_style" ).removeClass('wprevpro-pro-selected');
				$( "#wprevpro_pro_style_note" ).hide();
			}
		}
		function showProSaveNote(){
			var note = $( "#wprevpro_pro_save_note" );
			var msg = note.attr( "data-msg" ) || "Style %s is Pro version only, please select style 1 or 6.";
			var style = $( "#wprevpro_template_style" ).val();
			note.text( msg.replace( "%s", style ) ).css( "display", "inline" );
		}

		function changepreviewhtml(){
			var templatenum = $( "#wprevpro_template_style" ).val();
			var bradius = $( "#wprevpro_template_misc_bradius" ).val();
			var bg1 = $( "#wprevpro_template_misc_bgcolor1" ).val();
			var bg2 = $( "#wprevpro_template_misc_bgcolor2" ).val();
			var tcolor1 = $( "#wprevpro_template_misc_tcolor1" ).val();
			var tcolor2 = $( "#wprevpro_template_misc_tcolor2" ).val();
			var tcolor3 = $( "#wprevpro_template_misc_tcolor3" ).val();
			var tfont1 = $( "#wprevpro_template_misc_tfont1" ).val();
			var tfont2 = $( "#wprevpro_template_misc_tfont2" ).val();
			var avataropt = $( "#wprevpro_template_misc_avataropt" ).val();
			var verified = $( "#wprevpro_template_misc_verified" ).val();
			var lastname = $( "#wprevpro_template_misc_lastname" ).val();

			prestyle = "";
			if(templatenum=='1'){
				prestyle = "<style>.wptripadvisor_t1_DIV_2::after,.wprev_preview_bg1_T1::after{ border-top: 30px solid "+bg1+"; }</style>";
			}
			if($( "#wptripadvisor_template_css" ).val()!=""){
				prestyle += '<style>'+$( "#wptripadvisor_template_css" ).val()+'</style>';
			}
			
				var previewhtml = '';
				if(templatenum=='1'){
					previewhtml = style1html;
					$( ".wprevpre_bgcolor2" ).hide();
					$( ".wprevpre_tcolor3" ).hide();
				} else if(templatenum=='2'){
					previewhtml = style2html;
					$( ".wprevpre_bgcolor2" ).show();
					$( ".wprevpre_tcolor3" ).hide();
				} else if(templatenum=='3'){
					previewhtml = style3html;
					$( ".wprevpre_bgcolor2" ).show();
					$( ".wprevpre_tcolor3" ).show();
				} else if(templatenum=='4'){
					previewhtml = style4html;
					$( ".wprevpre_bgcolor2" ).hide();
					$( ".wprevpre_tcolor3" ).show();
				} else if(templatenum=='5'){
					previewhtml = style5html;
					$( ".wprevpre_bgcolor2" ).show();
					$( ".wprevpre_tcolor3" ).hide();
				} else if(templatenum=='6'){
					previewhtml = style6html;
					$( ".wprevpre_bgcolor2" ).show();
					$( ".wprevpre_tcolor3" ).hide();
				} else if(templatenum=='7'){
					previewhtml = style7html;
					$( ".wprevpre_bgcolor2" ).hide();
					$( ".wprevpre_tcolor3" ).hide();
				} else if(templatenum=='8'){
					previewhtml = style8html;
					$( ".wprevpre_bgcolor2" ).hide();
					$( ".wprevpre_tcolor3" ).hide();
				} else if(templatenum=='9'){
					previewhtml = style9html;
					$( ".wprevpre_bgcolor2" ).hide();
					$( ".wprevpre_tcolor3" ).hide();
				} else if(templatenum=='10'){
					previewhtml = style10html;
					$( ".wprevpre_bgcolor2" ).hide();
					$( ".wprevpre_tcolor3" ).hide();
				} else if(templatenum=='11'){
					previewhtml = style11html;
					$( ".wprevpre_bgcolor2" ).hide();
					$( ".wprevpre_tcolor3" ).hide();
				} else if(templatenum=='12'){
					previewhtml = style12html;
					$( ".wprevpre_bgcolor2" ).hide();
					$( ".wprevpre_tcolor3" ).show();
				} else if(templatenum=='13'){
					previewhtml = style13html;
					$( ".wprevpre_bgcolor2" ).hide();
					$( ".wprevpre_tcolor3" ).hide();
				} else if(templatenum=='14'){
					previewhtml = style14html;
					$( ".wprevpre_bgcolor2" ).hide();
					$( ".wprevpre_tcolor3" ).show();
				} else if(templatenum=='15'){
					previewhtml = style15html;
					$( ".wprevpre_bgcolor2" ).hide();
					$( ".wprevpre_tcolor3" ).show();
				}
				$( "#wprevpro_template_preview" ).html(prestyle+previewhtml);
				updateProStyleNote(templatenum);
				if(!isProOnlyTemplateStyle(templatenum)){
					$( '.wprev_preview_bg1, .wprev_preview_bg1_T1' ).css( "border", "" );
					$( '.wprev_preview_bg1, .wprev_preview_bg1_T1' ).css( "border-bottom", "" );
					$( '.wprev_preview_tcolor3' ).css( "text-shadow", '' );
					if(templatenum=='6' && bg2){
						$( '.wprev_preview_bg1, .wprev_preview_bg1_T1' ).css( "border", '1px solid '+bg2 );
					}
				}
			//now hide and show things based on values in select boxes
			if($( "#wprevpro_template_misc_showstars" ).val()=="no"){
				$( ".wprevpro_star_imgs, .wprevpro_star_imgs_T1" ).hide();
			} else {
				$( ".wprevpro_star_imgs, .wprevpro_star_imgs_T1" ).show();
			}
			if($( "#wprevpro_template_misc_showdate" ).val()=="no"){
				$( "#wprev_showdate, .wprev_showdate_T1" ).hide();
			} else {
				$( "#wprev_showdate, .wprev_showdate_T1" ).show();
			}
			if($( "#wprevpro_template_misc_showicon" ).val()=="no"){
				$( "#wprev_showicon" ).hide();
			} else {
				$( "#wprev_showicon" ).show();
			}
			//set colors and bradius by changing css via jQuery     border-radius: 10px 10px 10px 10px;
			$( '.wprev_preview_bradius, .wprev_preview_bradius_T1' ).css( "border-radius", bradius+'px' );
			if(!isProOnlyTemplateStyle(templatenum)){
				if(bg1){
					$( '.wprev_preview_bg1, .wprev_preview_bg1_T1' ).css( "background", bg1 );
				}
				if(bg2){
					$( '.wprev_preview_bg2' ).css( "background", bg2 );
				}
				if(tcolor1){
					$( '.wprev_preview_tcolor1, .wprev_preview_tcolor1_T1' ).css( "color", tcolor1 );
				}
				if(tcolor2){
					$( '.wprev_preview_tcolor2, .wprev_preview_tcolor2_T1' ).css( "color", tcolor2 );
				}
			}
			if(tfont1 > 0){
				$( '.wprev_preview_tcolor1, .wprev_preview_tcolor1_T1' ).css( {"font-size": tfont1+"px", "line-height": "normal"} );
			} else {
				$( '.wprev_preview_tcolor1, .wprev_preview_tcolor1_T1' ).css( {"font-size": "", "line-height": ""} );
			}
			if(tfont2 > 0){
				$( '.wprev_preview_tcolor2, .wprev_preview_tcolor2_T1' ).css( {"font-size": tfont2+"px", "line-height": "normal"} );
			} else {
				$( '.wprev_preview_tcolor2, .wprev_preview_tcolor2_T1' ).css( {"font-size": "", "line-height": ""} );
			}
			
			if(avataropt=='hide'){
				//set to display none
				$( ".wprev_avatar_opt" ).hide();
				if(templatenum=='6'){
					$( ".wpproslider_t6_DIV_3L" ).hide();
				}
				if(templatenum=='5'){
					$( ".wpproslider_t5_DIV_3L" ).hide();
				}
				if(templatenum=='10'){
					$( ".wpproslider_t10_DIV_3L" ).hide();
				}
			} else if(avataropt=='mystery'){
				//set img src
				$(".wprev_avatar_opt").attr("src",imagehrefmystery);
				$( ".wprev_avatar_opt" ).show();
				if(templatenum=='6'){
					$( ".wpproslider_t6_DIV_3L" ).show();
				}
				if(templatenum=='5'){
					$( ".wpproslider_t5_DIV_3L" ).show();
				}
				if(templatenum=='10'){
					$( ".wpproslider_t10_DIV_3L" ).show();
				}
			} else if(avataropt=='init'){
				// Local SVG initials avatar (oxro.io is no longer available).
				$(".wprev_avatar_opt").attr("src", buildInitialsAvatarDataUri('John Doe'));
				$( ".wprev_avatar_opt" ).show();
				if(templatenum=='6'){
					$( ".wpproslider_t6_DIV_3L" ).show();
				}
				if(templatenum=='5'){
					$( ".wpproslider_t5_DIV_3L" ).show();
				}
				if(templatenum=='10'){
					$( ".wpproslider_t10_DIV_3L" ).show();
				}
			} else {
				$(".wprev_avatar_opt").attr("src",imagehref);
				$( ".wprev_avatar_opt" ).show();
				if(templatenum=='6'){
					$( ".wpproslider_t6_DIV_3L" ).show();
				}
				if(templatenum=='5'){
					$( ".wpproslider_t5_DIV_3L" ).show();
				}
				if(templatenum=='10'){
					$( ".wpproslider_t10_DIV_3L" ).show();
				}
			}
			
			//for hiding and showing verified star in preview
			if(verified=='yes1'){
				$( ".verifiedloc1" ).show();
			} else {
				$( ".verifiedloc1" ).hide();
			}
			
			//last name format
			//alert(lastname);
			if(lastname=="show"){
				$("#wprev_lastname").html("Doe").show();
				$(".t6displayname").html('John '+lastnamehtml);
			} else if(lastname=="hide"){
				$( "#wprev_lastname" ).hide();
				$(".t6displayname").html('John');
			} else if(lastname=="initial"){
				$("#wprev_lastname").html("D.").show();
				$(".t6displayname").html('John '+lastnamehtml);
			}
			
		}
	
		
		//help button clicked
		$( "#wpfbr_helpicon_posts" ).click(function() {
		  openpopup("Tips", '<p>This page will let you create multiple Reviews Templates that you can then add to your Posts or Pages via a shortcode or template function.</p>', "");
		});
		//display shortcode button click wpfbr_addnewtemplate
		$( ".wptripadvisor_displayshortcode" ).click(function() {
			//get id and template type
			var tid = $( this ).parent().attr( "templateid" );
			var ttype = $( this ).parent().attr( "templatetype" );
			
		  if(ttype=="widget"){
			openpopup("Widget Instructions", '<p>To display this in your Sidebar or other Widget areas, add the WP Reviews widget under Appearance > Widgets, and then select this template in the drop down.</p>', '');
		  } else {
			openpopup("How to Display", '<p>Enter this shortcode on a post, page, or text widget: </br></br>[wptripadvisor_usetemplate tid="'+tid+'"]</p><p>Or you can add the following php code to your template: </br></br><code> do_action( \'wprev_tripadvisor_plugin_action\', '+tid+' ); </code></p>', '');
		  }
		  
		});
		
		
		//launch pop-up windows code--------
		function openpopup(title, body, body2){

			//set text
			jQuery( "#popup_titletext").html(title);
			jQuery( "#popup_bobytext1").html(body);
			jQuery( "#popup_bobytext2").html(body2);
			
			var popup = jQuery('#popup_review_list').popup({
				width: 400,
				offsetX: -100,
				offsetY: 0,
			});
			
			popup.open();
			//set height
			var bodyheight = Number(jQuery( ".popup-content").height()) + 10;
			jQuery( "#popup_review_list").height(bodyheight);

		}
		//--------------------------------
		//get the url parameter-----------
		function getParameterByName(name, url) {
			if (!url) {
			  url = window.location.href;
			}
			name = name.replace(/[\[\]]/g, "\\$&");
			var regex = new RegExp("[?&]" + name + "(=([^&#]*)|&|#|$)"),
				results = regex.exec(url);
			if (!results) return null;
			if (!results[2]) return '';
			return decodeURIComponent(results[2].replace(/\+/g, " "));
		}
		//---------------------------------
		
		//hide or show new template form ----------
		var checkedittemplate = getParameterByName('taction'); // "lorem"
		if(checkedittemplate=="edit"){
			isNewTemplateForm = false;
			jQuery("#wpfbr_new_template").show("slow");
			checkwidgetradio();
			changepreviewhtml();
			showtemplatepreview();

		} else {
			jQuery("#wpfbr_new_template").hide();
		}
		
		$( "#wpfbr_addnewtemplate" ).click(function() {
		  isNewTemplateForm = true;
		  jQuery("#wpfbr_new_template").show("slow");
		  //go ahead and save the template with all the defaults so we can show the preview right away.
		  $( "#wptripadvisor_addnewtemplate_update" ).click();
		  
		  //setTimeout(function(){ 
			//showtemplatepreview();
		  //}, 1000);
		});	
		$( "#wpfbr_addnewtemplate_cancel" ).click(function() {
		  isNewTemplateForm = false;
		  jQuery("#wpfbr_new_template").hide("slow");
		  //reload page without taction and tid
		  setTimeout(function(){ 
			window.location.href = "?page=wp_tripadvisor-templates_posts"; 
		  }, 500);
		  
		});	
		
		//-------------------------------
		
		//form validation
		$("#newtemplateform").submit(function(){
			if(isProOnlyTemplateStyle($( "#wprevpro_template_style" ).val())){
				$( "#wprevpro_pro_style_note" ).show();
				showProSaveNote();
				return false;
			}
			if(jQuery( "#wptripadvisor_template_title").val()==""){
				alert("Please enter a title.");
				$( "#wptripadvisor_template_title" ).focus();
				return false;
			} else if(jQuery( "#wptripadvisor_t_display_num_total").val()<1){
				alert("Please enter a 1 or greater.");
				$( "#wptripadvisor_t_display_num_total" ).focus();
				return false;
			} else {
			return true;
			}

		});
		
		//widget radio clicked
		$('input[type=radio][name=wptripadvisor_template_type]').change(function() {
			checkwidgetradio();
		});
		
		//check widget radio----------------------
		function checkwidgetradio() {
			var widgetvalue = $("input[name=wptripadvisor_template_type]:checked").val();
			if (widgetvalue == 'widget') {
				//change how many per a row to 1
				$('#wptripadvisor_t_display_num').val("1");
				$('#wptripadvisor_t_display_num').hide();
				$('#wptripadvisor_t_display_num').prev().hide();
				//force hide arrows and do not allow horizontal scroll on slideshow
				//$('input:radio[name=wpfbr_sliderdirection]').val(['vertical']);
				//$('input[id=wpfbr_sliderdirection1-radio]').attr("disabled",true);
				$('input:radio[name=wpfbr_sliderarrows]').val(['no']);
				$('input[id=wpfbr_sliderarrows1-radio]').attr("disabled",true);
			}
			else if (widgetvalue == 'post') {
				//alert("post type");
				if($('#edittid').val()==""){
				$('#wptripadvisor_t_display_num').val("3");
				}
				$('#wptripadvisor_t_display_num').show();
				$('#wptripadvisor_t_display_num').prev().show();
				$('input[id=wpfbr_sliderdirection1-radio]').attr("disabled",false);
				$('input[id=wpfbr_sliderarrows1-radio]').attr("disabled",false);
			}
		}
		
		//simple tooltip for added elements and mobile devices
		$(".wprevpro_t1_outer_div").on('mouseenter touchstart', '.wprevtooltip', function(e) {
			var titleText = $(this).attr('data-wprevtooltip');
			$(this).data('tiptext', titleText).removeAttr('data-wprevtooltip');
			$('<p class="wprevpro_tooltip"></p>').text(titleText).appendTo('body').css('top', (e.pageY - 15) + 'px').css('left', (e.pageX + 10) + 'px').fadeIn('slow');
		});
		$(".wprevpro_t1_outer_div").on('mouseleave touchend', '.wprevtooltip', function(e) {
			$(this).attr('data-wprevtooltip', $(this).data('tiptext'));
			$('.wprevpro_tooltip').remove();
		});
		$(".wprevpro_t1_outer_div").on('mousemove', '.wprevtooltip', function(e) {
			$('.wprevpro_tooltip').css('top', (e.pageY - 15) + 'px').css('left', (e.pageX + 10) + 'px');
		});
		
		
		//==================================================
		//======badge, and preview========================
		
		//adding functionality for preview window.
		$( "#wpfbr_addnewtemplate_preview" ).click(function() {
			showtemplatepreview();
		});
			
		function showtemplatepreview(){
			console.log('rebuild slider');
			
			$( "#loadingpreview" ).show();
			$( "#wptripadvisor_preview_outermost" ).show();
			
			//for a test get html and re-add it. 
			var temphtml = '';	//call jquery and get html for slider here.
			var temptid = $('#edittid').val();
			var senddata = {
					action: 'wptripadvisor_get_preview',	//required
					wptripadvisor_nonce: adminjs_script_vars.wptripadvisor_nonce,
					tid: temptid,
					};
			//send to ajax to update db
			var jqxhr = jQuery.post(ajaxurl, senddata, function (response){
				//console.log(response);
				$( "#loadingpreview" ).hide();
				if(response) {
					try {
						var saveresult = JSON.parse(response);	//array
						console.log(saveresult);
						if(saveresult.ack=='success'){
							
							$( "#wptripadvisor_preview_outer" ).html(saveresult.templatehtml);
							//console.log($( document.getElementsByClassName("wprev-slider") ));
							createaslider($(document.getElementsByClassName("wprev-slider")),'shortcode');
							
						} else {
							$('#update_form_msg').show();
							alert('Error creating preview. Please contact support. '+ saveresult.ackmessage); 
						}
						
					} catch(e) {
						alert('Error creating preview. Contact support.'+e); // error in the above string (in this case, yes)!
					}
				} else {
					alert('Error creating preview. Please contact support.'); 
				}
			});
		}
		
	//for showing description after clicking help icon wprevpro_t_createslider
		$( ".wprevpro_helpicon_p" ).click(function() {
			$(this).closest('tr').find('p.description').each(function() {
				$( this ).toggle('fast');
			});
		});
		
		//creating slider
			function createaslider(thissliderdiv,type){
				
				var sliderhideprevnext = $(thissliderdiv).attr( "data-sliderhideprevnext" );
				var sliderhidedots = $(thissliderdiv).attr( "data-sliderhidedots" );
				var sliderautoplay = $(thissliderdiv).attr( "data-sliderautoplay" );
				var slidespeed = $(thissliderdiv).attr( "data-slidespeed" );
				var slideautodelay = $(thissliderdiv).attr( "data-slideautodelay" );
				var sliderfixedheight = $(thissliderdiv).attr( "data-sliderfixedheight" );
				var revsameheight = $(thissliderdiv).attr( "data-revsameheight" );
				
				var showarrows = true;
				if(sliderhideprevnext=="yes"){
					var showarrows = false;
				}
				var shownav = true;
				if(sliderhidedots=="yes"){
					var shownav = false;
				}
				var sautoplay = false;
				if(sliderautoplay=="yes"){
					var sautoplay = true;
				}
				var sspeed = parseFloat(slidespeed) * 1000;
				var sdelay = parseFloat(slideautodelay) * 1000;
				if(sdelay<sspeed){
					sdelay = sspeed;
				}
				var sanimate = true;
				if(sliderfixedheight=="yes"){
					sanimate = false;
				}

				//unhide other rows.
				$( thissliderdiv ).find('li').show();
				var slider = $( thissliderdiv ).wprs_unslider(
						{
						autoplay:sautoplay,
						infinite:false,
						delay: sdelay,
						speed: sspeed,
						animation: 'horizontal',
						arrows: showarrows,
						nav:shownav,
						animateHeight: sanimate,
						activeClass: 'wprs_unslider-active',
						}
					);
				
				if(sanimate==true){
				setTimeout(function(){ 
					//height of active slide
					var firstheight = $(thissliderdiv).find('.wprs_unslider-active').height();
					$(thissliderdiv).css( 'height', firstheight );
					$(thissliderdiv).find("li.wprevnextslide").removeClass('wprevnextslide');
				}, 500);
				}
				
				if(sautoplay==true){
					slider.on('mouseover', function() {slider.data('wprs_unslider').stop();}).on('mouseout', function() {slider.data('wprs_unslider').start();});
				}
				//force height if set
				if(revsameheight=='yes'){
					var maxheights = $(thissliderdiv).find(".indrevdiv").map(function (){return $(this).outerHeight();}).get();
					var maxHeightofslide = Math.max.apply(null, maxheights);if(maxHeightofslide>0){$(thissliderdiv).find(".indrevdiv").css( "min-height", maxHeightofslide );}
				}
				
				missingimgcheck();
				initPreviewLightbox();
								
			};
		
				
		function missingimgcheck(){
				// Hide images that fail to load (scope to preview only).
			  $('#wptripadvisor_preview_outer img.wprev_media_img').each(function () {
				var img = this;
				var $img = $(this);

				function markMissing() {
				  $img.addClass('wprev_missing_image');
				}

				// Already finished loading.
				if (img.complete) {
				  if (img.naturalWidth === 0) {
					markMissing();
				  }
				  return;
				}

				// Still loading — only hide if the load actually fails.
				$img.one('error', markMissing);
			  });
		}

		function initPreviewLightbox(){
			var $preview = $('#wptripadvisor_preview_outer');
			if (!$preview.find('.wprev_media_div a.wprev_media_img_a').length) {
				return;
			}

			var pluginsUrl = '';
			if (typeof wprevpublicjs_script_vars !== 'undefined' && wprevpublicjs_script_vars.wprevpluginsurl) {
				pluginsUrl = wprevpublicjs_script_vars.wprevpluginsurl;
			} else if (typeof adminjs_script_vars !== 'undefined' && adminjs_script_vars.pluginsUrl) {
				pluginsUrl = adminjs_script_vars.pluginsUrl;
			}
			if (!pluginsUrl) {
				return;
			}

			function bindMediaLightbox() {
				$preview.off('click.wprevlity', 'a.wprev_media_img_a').on('click.wprevlity', 'a.wprev_media_img_a', function(e) {
					e.preventDefault();
					e.stopImmediatePropagation();
					var href = $(this).attr('href');
					if (!href || typeof lity !== 'function') {
						return;
					}
					lity(href);
				});
			}

			function ensureLity(callback) {
				if (typeof lity === 'function') {
					callback();
					return;
				}
				if (!document.getElementById('wprev_lity_css')) {
					$('<link/>', {
						id: 'wprev_lity_css',
						rel: 'stylesheet',
						type: 'text/css',
						href: pluginsUrl + '/public/css/lity.min.css'
					}).appendTo('head');
				}
				$.getScript(pluginsUrl + '/public/js/lity.min.js', callback);
			}

			ensureLity(bindMediaLightbox);
		}
		
		//simple tooltip for added elements and mobile devices
		$("#wptripadvisor_preview_outer").on('mouseenter touchstart', '.wprevtooltip', function(e) {
			var titleText = $(this).attr('data-wprevtooltip');
			$(this).data('tiptext', titleText).removeAttr('data-wprevtooltip');
			$('<p class="wprevpro_tooltip"></p>').text(titleText).appendTo('body').css('top', (e.pageY - 15) + 'px').css('left', (e.pageX + 10) + 'px').fadeIn('slow');
		});
		$("#wptripadvisor_preview_outer").on('mouseleave touchend', '.wprevtooltip', function(e) {
			$(this).attr('data-wprevtooltip', $(this).data('tiptext'));
			$('.wprevpro_tooltip').remove();
		});
		$("#wptripadvisor_preview_outer").on('mousemove', '.wprevtooltip', function(e) {
			$('.wprevpro_tooltip').css('top', (e.pageY - 15) + 'px').css('left', (e.pageX + 10) + 'px');
		});
		
		//going to search for media added to reviews and load lity if we find them.
		/*
		setTimeout(function(){ mediareviewpopup(); }, 500);
		function mediareviewpopup(){
			//var mediadiv = $(".wprev_media_div");
			var mediadiv = $(document.getElementsByClassName("wprev_media_div"));
			if(mediadiv.length){
				//load js and css files.
				//console.log(wprevpublicjs_script_vars);
				$('<link/>', {
				   rel: 'stylesheet',
				   type: 'text/css',
				   href: adminjs_script_vars.pluginsUrl+"/public/css/lity.min.css"
				}).appendTo('head');
				$.getScript(adminjs_script_vars.pluginsUrl+"/public/js/lity.min.js", function() {
					//script is loaded and ran on document root.
				});
			}
		}
		*/
		
		//for updating the form without closing it, sending via ajax
		$( "#wptripadvisor_addnewtemplate_update" ).click(function(event) {
			if(isProOnlyTemplateStyle($( "#wprevpro_template_style" ).val())){
				event.preventDefault();
				$( "#wprevpro_pro_style_note" ).show();
				showProSaveNote();
				return;
			}
			console.log('updating');
			$( "#wptripadvisor_preview_outermost" ).show();
			
			$('#savingformimg').show();
			//get all the form values. newtemplateform
			event.preventDefault();

			var formArray = $( "#newtemplateform" ).serializeArray();
			//console.log(formArray);
			  var returnArray = {};
			  for (var i = 0; i < formArray.length; i++){
					returnArray[formArray[i]['name']] = formArray[i]['value'];
			  }
			 //console.log(returnArray);
  
			var jsonfields = JSON.stringify(returnArray);
			//console.log(jsonfields);
			var senddata = {
					action: 'wptripadvisor_save_template',	//required
					wptripadvisor_nonce: adminjs_script_vars.wptripadvisor_nonce,
					data: jsonfields,
					};
			//send to ajax to update db
			var jqxhr = jQuery.post(ajaxurl, senddata, function (response){
				//console.log(response);
				if(response) {
					try {
						var saveresult = JSON.parse(response);
						//console.log(saveresult);
						if(saveresult.ack=='success'){
							$('#savingformimg').hide();
							$('#update_form_msg').show();
							//save editid if this is a new insert
							if(saveresult.iu=='insert'){
								$('#edittid').val(saveresult.t_id);
							}
							//reload preview
							//showtemplatepreview();
							
							$( "#wptripadvisor_preview_outer" ).html(saveresult.templatehtml);
							createaslider($(document.getElementsByClassName("wprev-slider")),'shortcode');

							
						} else {
							$('#update_form_msg').html(saveresult.ackmessage);
							alert('Error saving/updating template. Please contact support. '+ saveresult.ackmessage); 
						}
						
					} catch(e) {
						alert('Error saving/updating template. Contact support.'+e); // error in the above string (in this case, yes)!
					}
				} else {
					alert('Error saving/updating template. Please contact support.'); 
				}

				//hide message after 3 seconds
				setTimeout(function(){ $('#update_form_msg').hide(); }, 2000);
			});

		});
		
		$( "#wptripadvisor_preview_outer" ).on( "click", ".wprs_rd_more", function( event ) {
			$(this ).hide();
			$(this ).next("span").show(0, function() {
				// Animation complete.
				$(this ).css('opacity', '1.0');
			  });
		
			//change height of wprev-slider-widget
			$(this ).closest( ".wprev-slider-widget" ).css( "height", "auto" );
			
			//change height of wprev-slider
			$(this ).closest( ".wprev-slider" ).css( "height", "auto" );

		});
		
		var currenttab = 0;
		function hideAllSettingTables(){
			$( "#settingtable0, #settingtable1, #settingtable2, #settingtable3, #settingtable4" ).hide();
		}
		$( ".gotopage0" ).click(function() {
			hideAllSettingTables();
			$( "#settingtable0" ).fadeIn();
			currenttab = 0;
			changecurrenttab(currenttab);
		});
		$( ".gotopage1" ).click(function() {
			hideAllSettingTables();
			$( "#settingtable1" ).fadeIn();
			currenttab = 1;
			changecurrenttab(currenttab);
		});
		$( ".gotopage2" ).click(function() {
			hideAllSettingTables();
			$( "#settingtable2" ).fadeIn();
			currenttab = 2;
			changecurrenttab(currenttab);
		});
		$( ".gotopage3" ).click(function() {
			hideAllSettingTables();
			$( "#settingtable3" ).fadeIn();
			currenttab = 3;
			changecurrenttab(currenttab);
		});
		$( ".gotopage4" ).click(function() {
			hideAllSettingTables();
			$( "#settingtable4" ).fadeIn();
			currenttab = 4;
			changecurrenttab(currenttab);
		});
		function changecurrenttab(ctab){
			$( ".settingtab" ).removeClass( "nav-tab-active" );
			$( "#settingtab"+ctab ).addClass("nav-tab-active");
		}
		
		//upload custom business picture----------------------------------
		$('#upload_licon_button').on("click",function() {
			tb_show('Upload Icon', 'media-upload.php?referer=wp_tripadvisor-templates_posts&type=image&TB_iframe=true&post_id=0', false);
			//store old send to editor function
			window.restore_send_to_editor = window.send_to_editor;
			window.send_to_editor = function(html) {
				var image_url = jQuery("<div>" + html + "</div>").find('img').attr('src');
				$('#wprevpro_t_bimgurl').val(image_url);
				tb_remove();
				//restore old send to editor function
				 window.send_to_editor = window.restore_send_to_editor;
			}
		
			return false;
		});
		
		//for setting badge title
		//if($( "#wprevpro_t_bname" ).val()==""){
		//setbadgetitle();
		//}
		$( "#wprevpro_t_filtersource" ).change(function() {
			setbadgetitle();
		});
		function setbadgetitle(){
			var $opt = $( "#wprevpro_t_filtersource option:selected" );
			if ( ! $opt.length || $opt.val() === '' ) {
				return;
			}
			$( "#wprevpro_t_bname" ).val( $.trim( $opt.text() ) );
			var fromurl = $opt.attr( "data-fromurl" );
			if ( fromurl ) {
				$( "#wprevpro_t_bnameurl" ).val( fromurl );
				$( "#wprevpro_t_bbtnurl" ).val( fromurl );
			}
		}
		if ( $( "#wprevpro_t_filtersource" ).length && $( "#wprevpro_t_bname" ).val() === '' ) {
			setbadgetitle();
		}
		
		//hide badge options if not using. wprevpro_t_blocation
		hideshowbadgeoptions();
		$( "#wprevpro_t_blocation" ).change(function() {
			hideshowbadgeoptions();
		});
		function hideshowbadgeoptions(){
			if($( "#wprevpro_t_blocation" ).val()==""){
				//hide
				$( ".badgehide" ).hide('slow');
			} else {
				$( ".badgehide" ).show('slow');
			}
		}

		
		
	});

})( jQuery );
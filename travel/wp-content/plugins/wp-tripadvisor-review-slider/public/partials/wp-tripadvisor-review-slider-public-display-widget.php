<?php

/**
 * Provide a public-facing view for the plugin
 *
 * This file is used to markup the public-facing aspects of the plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    WP_TripAdvisor_Review
 * @subpackage WP_TripAdvisor_Review/public/partials
 */

 	//db function variables
	global $wpdb;
	$table_name = $wpdb->prefix . 'wptripadvisor_post_templates';
	
 //use the template id to find template in db, echo error if we can't find it or just don't display anything
 	//Get the form--------------------------
	$tid = htmlentities($a['tid']);
	$tid = intval($tid);
	$currentform = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table_name WHERE id = %d", $tid ) );
	
	//check to make sure template found
	if(isset($currentform[0])){
		$iswidget = true;
		$template_misc_array = json_decode($currentform[0]->template_misc, true);
		if ( ! is_array( $template_misc_array ) ) {
			$template_misc_array = array();
		}
		
		//use values from currentform to get reviews from db
		$table_name = $wpdb->prefix . 'wptripadvisor_reviews';
		
		if($currentform[0]->hide_no_text=="yes"){
			$rlength = 1;
		} else {
			$rlength = 0;
		}
		
		if($currentform[0]->display_order=="random"){
			$sorttable = "RAND() ";
			$sortdir = "";
		} else {
			$sorttable = "created_time_stamp ";
			$sortdir = "DESC";
		}
		$rtype = "TripAdvisor";
		if($currentform[0]->rtype=='["fb"]'){
			$rtype = "Facebook";
		}
		if($currentform[0]->rtype=='["google"]'){
			$rtype = "TripAdvisor";
		}

		$reviewsperpage= $currentform[0]->display_num*$currentform[0]->display_num_rows;
		$tablelimit = $reviewsperpage;
		//change limit for slider
		if($currentform[0]->createslider == "yes"){
			$tablelimit = $tablelimit*$currentform[0]->numslides;
		}
		
				//min_rating filter----
		if($currentform[0]->min_rating>0){
			$min_rating = intval($currentform[0]->min_rating);
			//grab positive recommendations as well
			if($min_rating==1){
				$min_rating=0;
			}
			if($min_rating<3){
				//show positive and negative
				$ratingquery = " AND rating >= '".$min_rating."' ";
			} else {
				//only show positive
				$ratingquery = " AND rating >= '".$min_rating."' ";
			}
			
		} else {
			$min_rating ="";
			$ratingquery ="";
		}
		
		//location filter if set (empty = legacy All Sources; use last/only crawl or review source)
		$sourcelocationfilter ="";
		if ( empty( $template_misc_array['filtersource'] ) ) {
			$tripadvisor_crawls = json_decode( get_option( 'wprev_tripadvisor_crawls', '{}' ), true );
			if ( is_array( $tripadvisor_crawls ) && ! empty( $tripadvisor_crawls ) ) {
				$crawl_keys = array();
				foreach ( $tripadvisor_crawls as $crawl_pageid => $crawl_source ) {
					if ( is_array( $crawl_source ) && $crawl_pageid !== '' && $crawl_pageid !== '0' ) {
						$crawl_keys[] = (string) $crawl_pageid;
					}
				}
				if ( ! empty( $crawl_keys ) ) {
					$template_misc_array['filtersource'] = end( $crawl_keys );
				}
			}
			if ( empty( $template_misc_array['filtersource'] ) ) {
				$fallback_pageid = $wpdb->get_var( "SELECT pageid FROM {$table_name} WHERE type = 'TripAdvisor' AND pageid != '' ORDER BY id DESC LIMIT 1" );
				if ( $fallback_pageid ) {
					$template_misc_array['filtersource'] = $fallback_pageid;
				}
			}
		}
		// Never concatenate the stored filtersource value directly into the SQL
		// string — it is user-controlled template data and must stay out of the
		// $wpdb->prepare() format string. Use a %s placeholder instead.
		$sourcelocationvalue = null;
		if(isset($template_misc_array['filtersource']) && $template_misc_array['filtersource']!=""){
			$sourcelocationfilter = " AND pageid = %s";
			$sourcelocationvalue  = $template_misc_array['filtersource'];
		}
		
		//if we are hiding all reviews in badge settings then do not even look for them.
		//hide all the reviews!
		if(!isset($template_misc_array['bhreviews'])){
			$template_misc_array['bhreviews']='';
		}
		if($template_misc_array['bhreviews']=="yes"){
			$totalreviews = Array();
		} else {
			$prepareargs = array("0","$rlength","$rtype","yes");
			if($sourcelocationvalue !== null){
				$prepareargs[] = $sourcelocationvalue;
			}
			$totalreviews = $wpdb->get_results(
				$wpdb->prepare("SELECT * FROM ".$table_name."
				WHERE id>%d AND review_length >= %d AND type = %s AND hide != %s" .$ratingquery.$sourcelocationfilter."
				ORDER BY ".$sorttable." ".$sortdir." 
				LIMIT ".$tablelimit." ", $prepareargs)
			);
		}

		//if we are adding a badge then wrap the slider in another outer div with flex box and add another div beside slider.
		if(!isset($template_misc_array['blocation'])){$template_misc_array['blocation']="";}
		
		if($template_misc_array['blocation']!=""){
			
			//preset in case this is an old template
			if(!isset($template_misc_array['bname'])){$template_misc_array['bname']='';}
			if(!isset($template_misc_array['bimgurl'])){$template_misc_array['bimgurl']='';}
			if(!isset($template_misc_array['bbtnurl'])){$template_misc_array['bbtnurl']='';}
			if(!isset($template_misc_array['bnameurl'])){$template_misc_array['bnameurl']='';}
			if(!isset($template_misc_array['bbtncolor'])){$template_misc_array['bbtncolor']='';}
			if(!isset($template_misc_array['bbkcolor'])){$template_misc_array['bbkcolor']='';}
			if(!isset($template_misc_array['bbradius'])){$template_misc_array['bbradius']='';}
			if(!isset($template_misc_array['bdropsh'])){$template_misc_array['bdropsh']='';}
			if(!isset($template_misc_array['bcenter'])){$template_misc_array['bcenter']='';}
			if(!isset($template_misc_array['bhname'])){$template_misc_array['bhname']='';}
			if(!isset($template_misc_array['bhphoto'])){$template_misc_array['bhphoto']='';}
			if(!isset($template_misc_array['bhbased'])){$template_misc_array['bhbased']='';}
			if(!isset($template_misc_array['bhbtn'])){$template_misc_array['bhbtn']='';}
			if(!isset($template_misc_array['filtersource'])){$template_misc_array['filtersource']='';}
			if(!isset($template_misc_array['bhpow'])){$template_misc_array['bhpow']='';}
			if(!isset($template_misc_array['bshape'])){$template_misc_array['bshape']='';}
			if(!isset($template_misc_array['bobasedon'])){$template_misc_array['bobasedon']='';}
			if(!isset($template_misc_array['borevus'])){$template_misc_array['borevus']='';}
			
			//get badge info
			$businessname = $template_misc_array['bname'];
			$imageurl = $template_misc_array['bimgurl'];
			$butnlinkurl = $template_misc_array['bbtnurl'];
			$bnameurl = $template_misc_array['bnameurl'];
			$badge_imgs_base = wprev_trip_plugin_url . '/public/partials/imgs/';
			$badge_icon_svg  = $badge_imgs_base . 'tripadvisor_badge_icon.svg';
			$powered_by_svg  = $badge_imgs_base . 'poweredbytripadvisor.svg';
			// Upgrade legacy low-res default icons to the SVG owl.
			if ( $imageurl === '' || preg_match( '#/(tripadvisor-badge-icon-300x300|tripadvisor_small_icon|tripadvisor_outline)\.png$#i', $imageurl ) ) {
				$imageurl = $badge_icon_svg;
			}
			
			$bbtncolor = WP_TripAdvisor_Review_Sanitize::sanitize_css_color($template_misc_array['bbtncolor']);
			$bbackgroundcolor = WP_TripAdvisor_Review_Sanitize::sanitize_css_color($template_misc_array['bbkcolor']);
			$bborderradius = intval($template_misc_array['bbradius']);
			$bborderwidth = isset($template_misc_array['bbwidth']) ? absint($template_misc_array['bbwidth']) : 0;
			$bbordercolor = '';
			if(isset($template_misc_array['bbcolor']) && $template_misc_array['bbcolor'] !== ''){
				$bbordercolor = WP_TripAdvisor_Review_Sanitize::sanitize_css_color($template_misc_array['bbcolor']);
			}
			$bdropsh = esc_html($template_misc_array['bdropsh']);
			$bcenter = esc_html($template_misc_array['bcenter']);	//center the image above and center text below.
			$bshape = esc_html($template_misc_array['bshape']);	//round or square
			
			$bhname = esc_html($template_misc_array['bhname']);	//hide the name
			$bhnameclass = "";
			if($bhname =="yes"){$bhnameclass = "badgehideclass";}
			
			$bhphoto = esc_html($template_misc_array['bhphoto']);	//hide the photo
			$bhphotoclass = "";
			if($bhphoto =="yes"){$bhphotoclass = "badgehideclass";}
			
			$bhbased = esc_html($template_misc_array['bhbased']);	//hide the based on text
			$bhbasedclass = "";
			if($bhbased =="yes"){$bhbasedclass = "badgehideclass";}
			
			$bhbtn = esc_html($template_misc_array['bhbtn']);	//hide the review us button
			$bhbtnclass = "";
			if($bhbtn =="yes"){$bhbtnclass = "badgehideclass";}
			
			$bhpow = esc_html($template_misc_array['bhpow']);	//hide the powered by
			$bhpowclass = "";
			if($bhpow =="yes"){$bhpowclass = "badgehideclass";}
			
		
			$badge_style = "";
			$badge_style = $badge_style . 'a.wprev-tripadvisor-wr-a {background: '.$bbtncolor.' !important;}';
			$badge_style = $badge_style . 'a.wprev-tripadvisor-wr-a:hover {background: '.$bbtncolor.'de !important;}';

			$badge_place_style = 'background: '.$bbackgroundcolor.' !important;border-radius:'.$bborderradius.'px !important;';
			if($bborderwidth > 0){
				if($bbordercolor === ''){
					$bbordercolor = '#eeeeee';
				}
				$badge_place_style .= 'border:'.$bborderwidth.'px solid '.$bbordercolor.' !important;';
			} else {
				$badge_place_style .= 'border:none !important;';
			}
			$badge_style = $badge_style . '.wprev-tripadvisor-place {'.$badge_place_style.'}';
			if($bdropsh=="yes"){
				$badge_style = $badge_style . '.wprev-tripadvisor-place {box-shadow: rgba(0, 0, 0, .08) 2px 2px 3px 0px !important;}';
			} else {
				$badge_style = $badge_style . '.wprev-tripadvisor-place {box-shadow: none !important;}';
			}
			if($bcenter=="yes" && $template_misc_array['blocation']!="abovewide"){
				$badge_style = $badge_style . '.wprev-tripadvisor-place {flex-direction: column !important;align-items: center !important;}';
				$badge_style = $badge_style . '.wprev-tripadvisor-right {display: flex!important;align-items: center!important;flex-direction: column!important;width: 100% !important;text-align: center !important;}';
				$badge_style = $badge_style . '.wprev-tripadvisor-name{margin-bottom: 3px !important;}';
				$badge_style = $badge_style . '.wprev-tripadvisor-powered,.wprev-tripadvisor-wr {display: flex !important;justify-content: center !important;width: 100% !important;}';
				$badge_style = $badge_style . '.wprev-tripadvisor-powered img {margin-left: auto !important;margin-right: auto !important;}';
			}
			if($bshape=="round"){
				$badge_style = $badge_style . 'img.sprev-tripadvisor-left-src {border-radius: 50% !important;}';
			}
			
			// Source avg/total from the crawler, with a fallback to stored reviews.
			require_once __DIR__ . '/template_class.php';
			$badge_template_fns = new WP_Trip_Template_Functions();
			$templaceid = isset( $template_misc_array['filtersource'] ) ? $template_misc_array['filtersource'] : '';
			$badge_stats = $badge_template_fns->wprev_badge_avg_total( $templaceid );
			$badgeavg = $badge_stats['avg'];
			$badgetotal = $badge_stats['total'];

			//if this is left mid then add a style
			if($template_misc_array['blocation']=="leftmid" || $template_misc_array['blocation']=="rightmid" ){
				$badge_style = $badge_style . '.wprev_outer_wb {align-items: center !important;}';
			}
			// Style 6 adds 15px outer margin on the review row; normalize when badge is beside slider.
			$wprev_outer_wb_class = 'wprev_outer_wb';
			if($currentform[0]->style == "6"){
				$badge_side_locations = array( 'left', 'right', 'leftmid', 'rightmid' );
				if(in_array($template_misc_array['blocation'], $badge_side_locations, true)){
					$wprev_outer_wb_class .= ' wprev_badge_style6_side';
				}
			}
			//if this is above then we slightly change html again
			if($template_misc_array['blocation']=="above"){
				$badge_style = $badge_style . '.wprev_outer_wb {flex-direction: column !important;}.wprev_badge_div.badgeleft {margin-left: auto !important;margin-right: auto !important;}';
			}
			//if this is above and wide then we change html again
			$badgeabovewide1 = '';
			$badgeabovewide2 = '';
			$badgeabovewideclose ='';
			if($template_misc_array['blocation']=="abovewide"){
				$badge_style = $badge_style . '.wprev_outer_wb {flex-direction: column !important;}.wprev_badge_div.badgeleft {margin-left: auto !important;margin-right: auto !important;}.wprev_badge_div.badgeleft {margin: 0px 46px !important;}.wprev-tripadvisor-place {justify-content: space-between !important;align-items: center !important;}.wprev-tripadvisor-leftboth {display: flex !important;}  @media only screen and (max-width: 600px) {.wprev-tripadvisor-place {flex-direction: column;}}';
				$badgeabovewide1 = '<div class="wprev-tripadvisor-leftboth">';
				$badgeabovewide2 = '<div class="wprev-tripadvisor-right">';
				$badgeabovewideclose = '</div>'; 
			}
			
			$bimgsize = 50;
			if(isset($template_misc_array['bimgsize']) &&  $template_misc_array['bimgsize']>0){
				$bimgsize = absint($template_misc_array['bimgsize']);
				$badge_style = $badge_style . 'img.sprev-tripadvisor-left-src {min-width: '.$bimgsize.'px !important;min-height: '.$bimgsize.'px !important;}';
			}
			
			echo "<style>".$badge_style."</style>";
			if(!isset($wprev_outer_wb_class)){
				$wprev_outer_wb_class = 'wprev_outer_wb';
			}
			echo '<div class="'.esc_attr($wprev_outer_wb_class).'">'; 
			
			//print_r($template_misc_array);
			
			//change based on text if set
			$basedontext = 'Based on <span class="wprev_btot">'.$badgetotal.'</span> reviews';
			if($template_misc_array['bobasedon']!=""){
				$basedontext = esc_html( $template_misc_array['bobasedon'] );
			}
			$basedontext = str_replace("#",'<span class="wprev_btot">'.$badgetotal.'</span>',$basedontext);
			
			//change review us on text
			$reviewusontext = 'Review us on';
			if($template_misc_array['borevus']!=""){
				$reviewusontext = esc_html( $template_misc_array['borevus'] );
			}


			$tripadvisor_btn_svg = '<svg viewBox="0 0 1333.31 1333.31" height="18" width="18" aria-hidden="true"><circle cx="666.66" cy="666.66" r="666.66" fill="#34e0a1"/><path fill="#000000" fill-rule="nonzero" d="M1078.42 536.6l80.45-87.52h-178.4c-89.31-61.01-197.17-96.54-313.81-96.54-116.5 0-224.06 35.61-313.22 96.54H174.6l80.44 87.52c-49.31 44.99-80.22 109.8-80.22 181.75 0 135.79 110.09 245.88 245.88 245.88 64.51 0 123.27-24.88 167.14-65.55l78.81 85.81 78.81-85.73c43.87 40.67 102.57 65.47 167.07 65.47 135.79 0 246.03-110.09 246.03-245.88.07-72.03-30.84-136.83-80.15-181.75zM420.77 884.75c-91.92 0-166.4-74.48-166.4-166.4s74.49-166.4 166.4-166.4c91.92 0 166.4 74.49 166.4 166.4 0 91.91-74.49 166.4-166.4 166.4zm245.96-171.24c0-109.5-79.63-203.5-184.73-243.65 56.84-23.76 119.18-36.94 184.66-36.94 65.47 0 127.89 13.18 184.73 36.94-105.02 40.23-184.65 134.15-184.65 243.65zm245.88 171.24c-91.92 0-166.4-74.48-166.4-166.4s74.49-166.4 166.4-166.4c91.92 0 166.4 74.49 166.4 166.4 0 91.91-74.49 166.4-166.4 166.4zm0-253.7c-48.2 0-87.23 39.03-87.23 87.23 0 48.19 39.03 87.22 87.23 87.22 48.19 0 87.22-39.03 87.22-87.22 0-48.12-39.03-87.23-87.22-87.23zM508 718.35c0 48.19-39.03 87.22-87.23 87.22-48.19 0-87.22-39.03-87.22-87.22 0-48.2 39.03-87.23 87.22-87.23 48.19-.07 87.23 39.03 87.23 87.23z"/></svg>';

			$badgestarhtml = $badge_template_fns->wprev_badge_star_html( $badgeavg, $badge_imgs_base );

			$badgehtml = '<div class="wprev-tripadvisor-place">'.$badgeabovewide1.'<div class="wprev-tripadvisor-left '.$bhphotoclass.'"><img class="sprev-tripadvisor-left-src" src="'.esc_url($imageurl).'" alt="'.esc_attr($businessname).'" width="'.$bimgsize.'" height="'.$bimgsize.'" title="'.esc_attr($businessname).'"></div><div class="wprev-tripadvisor-right"><div class="wprev-tripadvisor-name '.$bhnameclass.'"><a href="'.esc_url($bnameurl).'" target="_blank" rel="nofollow noopener"><span class="wprev-businessname">'.esc_html($businessname).'</span></a></div>'.$badgestarhtml.'<div class="wprev-tripadvisor-basedon '.$bhbasedclass.'">'.$basedontext.'</div>'.$badgeabovewideclose.$badgeabovewideclose.$badgeabovewide2.'<div class="wprev-tripadvisor-powered '.$bhpowclass.'"><img class="wprev-tripadvisor-powered-img" src="'.esc_url($powered_by_svg).'" alt="powered by Tripadvisor" width="102" height="20" title="powered by Tripadvisor"></div><div class="wprev-tripadvisor-wr '.$bhbtnclass.'"><a class="wprev-tripadvisor-wr-a" target="_blank" rel="nofollow noopener" href="'.esc_url($butnlinkurl).'">'.$reviewusontext.' '.$tripadvisor_btn_svg.'</a></div></div></div>';
			
							
		}
			//actually adding badge html here for left and top
			if($template_misc_array['blocation']=="left" || $template_misc_array['blocation']=="leftmid" || $template_misc_array['blocation']=="above" || $template_misc_array['blocation']=="abovewide"){

				echo '<div class="wprev_badge_div badgeleft">';
				//this is where we could load badge styles in Pro version.
				
				echo $badgehtml;
					
				echo '</div>';
			}
			
	//only continue if some reviews found
	$makingslideshow=false;
	if(count($totalreviews)>0){
		
		//are we setting same height
		//need to pass this to javascript file
		$revsameheight = 'no';
		$notsameheight="revnotsameheight";
		if(isset($currentform[0]->review_same_height) && $currentform[0]->review_same_height!=""){
			if($currentform[0]->review_same_height=='yes'){
				$revsameheight = 'yes';
				$notsameheight="";
			}
		}
		
		//if creating a slider than we need to split into chunks for each slider
		$totalreviewschunked = array_chunk($totalreviews, $reviewsperpage);
		
		
//add styles from template misc here (all color/number values sanitized for CSS context)
			if(is_array($template_misc_array)){
				$misc_style = WP_TripAdvisor_Review_Sanitize::build_template_misc_style( $currentform[0]->id, $currentform[0]->style, $template_misc_array, '_widget', false );

				//------------------------
				echo "<style>".$misc_style."</style>";
			}
			//--------------------------
			

			//print out user style added
			echo "<style>".esc_html($currentform[0]->template_css)."</style>";
		
		
		
		
		
		//if making slide show then add it here
		if($currentform[0]->createslider == "yes"){
			//make sure we have enough to create a show here
			if($totalreviews>$reviewsperpage){
				$makingslideshow = true;
				$oneonmobile = "";
				if($currentform[0]->slidermobileview == "one"){
					$oneonmobile = "yes";
					//hide slider dots on mobile view.
					echo "<style>@media only screen and (max-width: 600px) {nav.wprs_unslider-nav {display: none;}}</style>";
				}
				$sliderautoplay = "";
				$slidespeed = "";
				$slideautodelay = "";
				$sliderhideprevnext = "";
				$sliderhidedots = "";
				$sliderfixedheight = "";
				
				
				
				if(isset($template_misc_array['sliderautoplay'])){ $sliderautoplay = $template_misc_array['sliderautoplay'];}
				if(isset($template_misc_array['slidespeed'])){ $slidespeed = $template_misc_array['slidespeed'];}
				if(isset($template_misc_array['slideautodelay'])){ $slideautodelay = $template_misc_array['slideautodelay'];}
				if(isset($template_misc_array['sliderhideprevnext'])){ $sliderhideprevnext = $template_misc_array['sliderhideprevnext'];}
				if(isset($template_misc_array['sliderhidedots'])){ $sliderhidedots = $template_misc_array['sliderhidedots'];}
				if(isset($template_misc_array['sliderfixedheight'])){ $sliderfixedheight = $template_misc_array['sliderfixedheight'];}
				
				//force static height if we are setting reviews same height
				if($revsameheight=="yes"){
					$sliderfixedheight = "yes";
				}
				
				//sliderautoplay,slidespeed,slideautodelay
				echo '<div class="wprev-slider-widget '.$notsameheight.'" id="wprev-slider-'.esc_html($currentform[0]->id).'" data-revsameheight="'.$revsameheight.'" data-onemobil="'.$oneonmobile.'" data-sliderautoplay="'.esc_html($sliderautoplay).'"  data-slidespeed="'.esc_html($slidespeed).'" data-slideautodelay="'.esc_html($slideautodelay).'" data-sliderhideprevnext="'.esc_html($sliderhideprevnext).'" data-sliderhidedots="'.esc_html($sliderhidedots).'" data-sliderfixedheight="'.esc_html($sliderfixedheight).'"><ul>';
			}
		} else {
			echo '<div class="wprev-no-slider '.$notsameheight.'" id="wprev-slider-'.esc_html($currentform[0]->id).'">';
		}
		
					
			
		
		$loopnum = 1;
		foreach ( $totalreviewschunked as $reviewschunked ){
			//echo "loop1";
			$totalreviewstemp = $reviewschunked;
			
			//need to break $totalreviewstemp up based on how many rows, create an multi array containing them
			if($currentform[0]->display_num_rows>1 && count($totalreviewstemp)>$currentform[0]->display_num){
				//count of reviews total is greater than display per row then we need to break in to multiple rows
				for ($row = 0; $row < $currentform[0]->display_num_rows; $row++) {
					$n=1;
					foreach ( $totalreviewstemp as $tempreview ){
						//echo "<br>".$tempreview->reviewer_name;
						//echo $n."-".$row."-".$currentform[0]->display_num;
						if($n>($row*$currentform[0]->display_num) && $n<=(($row+1)*$currentform[0]->display_num)){
							$rowarray[$row][$n]=$tempreview;
						}
						$n++;
					}
				}
			} else {
				//everything on one row so just put in multi array
				$rowarray[0]=$totalreviewstemp;
			}
			
			 
			//if making slide show
			if($makingslideshow){
				if($loopnum==1){
					echo '<li>';
				} else {
					echo '<li class="wprevnextslide">';
				}
			}
		 
				//include the correct tid here
				if($currentform[0]->style=="1"){
				$iswidget=false;
					include(plugin_dir_path( __FILE__ ) . '/template_style_1.php');
				} else if($currentform[0]->style=="6"){
				$iswidget=false;
					include(plugin_dir_path( __FILE__ ) . '/template_style_6.php');
				}
			
			//if making slide show then end loop here
			if($makingslideshow){
					echo '</li>';
			}
			$loopnum++;
		
		}	//end loop chunks
		//if making slide show then end it
		if($makingslideshow){
				echo '</ul></div>';

		} else {
		echo '</div>';
		}
	 
	}
				//actually adding badge html here for right side
			if($template_misc_array['blocation']=="right" || $template_misc_array['blocation']=="rightmid"){

				echo '<div class="wprev_badge_div badgeright">';
				//this is where we could load badge styles in Pro version.
				
				echo $badgehtml;
					
				echo '</div>';
			}
			
			//end badge div if we are adding one.
		if(isset($template_misc_array['blocation']) && $template_misc_array['blocation']!=""){
			echo '</div>';
		}
}
?>


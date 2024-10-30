<?php
/*
Plugin Name: Meeting Truth
Plugin URI: http://www.meetingtruth.com
Description: Plugin to display meeting truth events
Author: Remote New Media
Version: 1.2.7
Author URI: http://www.remotenewmedia.com
 */


//define( 'WP_DEBUG', true );


require_once( 'pages/page-event-list.php' );
require_once( 'pages/page-event-details.php' );


if ( ! class_exists( 'Meeting_Truth_Events_Plugin' ) ) {
	class Meeting_Truth_Events_Plugin {
		function Meeting_Truth_Events_Plugin() {
			register_activation_hook( __FILE__, array( &$this, 'hook_activation' ) );
			
			$this->register_admin_actions();
			$this->register_admin_filters();
			$this->register_page_actions();
			$this->register_page_filters();
		}
		
		function hook_activation() {
			update_option('rnmmt_rdoPayment','onmt');
			update_option('rnmmt_poweredby_link','false');
			
			update_option('rnmmt_events_details_layout','all-to-right');
			update_option('rnmmt_events_details_layout_show_teacher','true');
			
			update_option('rnmmt_events_style_highlight','ff6600');
			update_option('rnmmt_events_style_dark_colour','cccccc');
			update_option('rnmmt_events_style_med_colour','efefef');
			update_option('rnmmt_events_style_light_colour','ffffff');
		}
		
		function register_admin_actions() {
			add_action( 'admin_enqueue_scripts', array( &$this, 'action_admin_enqueue_scripts' ) );
			add_action( 'admin_menu', array( &$this, 'action_admin_menu' ) );
			add_action( 'load-post.php', array( &$this, 'action_meta_boxes_setup' ) );
			add_action( 'load-post-new.php', array( &$this, 'action_meta_boxes_setup' ) );
		}
		
		function register_admin_filters() {
			add_filter( 'plugin_action_links', array( &$this, 'filter_plugin_action_links' ), 10, 2 );
		}
		
		function register_page_actions() {
			add_action( 'wp_enqueue_scripts', array( &$this, 'action_wp_enqueue_scripts' ) );
		}
		
		function register_page_filters() {
			add_filter( 'the_content', array( &$this, 'filter_the_content' ) );
		}
		
		function action_admin_enqueue_scripts() {
			wp_enqueue_style( 'meeting-truth-style-admin', plugins_url( '/css/admin.min.css', __FILE__ ) );
			wp_enqueue_script( 'meeting-truth-script-admin', plugins_url( '/js/admin.js', __FILE__ ), array( 'jquery', 'iris' ) );
		}
		
		function action_admin_menu() {
			add_options_page( 'Meeting Truth', 'Meeting Truth', 1, 'Meeting_Truth', array( &$this, 'show_admin_page' ) );
		}
		
		function action_meta_boxes_setup() {
			include( 'admin-meta.php' );
		}
		
		function filter_plugin_action_links($links, $file) {
			$plugin = plugin_basename( __FILE__ );
			
			if ($file == $plugin) {
				$url = get_bloginfo( 'wpurl' );
				$settings_link = "<a href='{$url}/wp-admin/options-general.php?page=Meeting_Truth'>Settings</a>";
				array_unshift( $links, $settings_link );
			}
			
			return $links;
		}
		
		function action_wp_enqueue_scripts() {
			wp_enqueue_style( 'meeting-truth-style-dynamic', plugins_url( '/css/style-dynamic.php', __FILE__ ) );
			wp_enqueue_script( 'meeting-truth-script', plugins_url( '/js/front.js', __FILE__ ), array( 'jquery' ) );
			
			wp_localize_script( 'meeting-truth-script', 'meetingTruthPluginParams', array( 'pluginUrl' => plugin_dir_url( __FILE__ ) ));
		}
		
		function filter_the_content($content) {
			$post_id = get_the_ID();
			$display = get_post_meta( $post_id, 'rnmmt_post_display', true );
			$replace_content = false;
			
			$plugin_html = '';
			
			if($display == 'events' || 
				$display == 'sessions' ||
				$display == 'both') {
				
				$page_html = '';
				
				if( isset( $_REQUEST['mteid'] ) ) {
					$page = new Meeting_Truth_Event_Details_Page();
					$page->page_load( $_REQUEST );
					$page_html = $page->get_html();
					
					$replace_content = true;
				} else {
					$page = new Meeting_Truth_Event_List_Page();
					$page->page_load( $_REQUEST );
					$page_html = $page->get_html();
				}
				
				if( strlen( $page_html ) > 0 ) {
					$plugin_html .= '<div id="meeting_truth_events">';
					$plugin_html .= $page_html;
					$plugin_html .= $this->get_powered_by_html();
					$plugin_html .= '</div>';
				}
			}
			
			if( $replace_content ) {
				$content = $plugin_html;
			} else {
				$content = $content . $plugin_html;
			}
			
			return $content;
		}
		
		function show_admin_page() {
			include( 'pages/page-admin.php' );
		}
		
		function get_powered_by_html() {
			$html = "";
			
			$powered_by_url = plugins_url( '/img/poweredby.gif', __FILE__ ) ;
			$img = "<img class='poweredby' src='{$powered_by_url}' />";
			
			$link_to_mt = get_option( 'rnmmt_poweredby_link' );
			
			if ( $link_to_mt == 'true' ) {
				$html = '<a target="_blank" href="http://www.meetingtruth.com/">' . $img . '</a>';
			} else {
				$html = $img;
			}
			
			
			$logo_image_url = plugins_url( '/img/meeting-truth.gif', __FILE__ );
			$loading_image_url = plugins_url( '/img/loading.gif', __FILE__ );
			$html .= "<div id='rnmmt_redirecting' class='rnmmt_dialogbox'><div><img class='mt_logo' src='{$logo_image_url}' /><p></p><img class='mt_preload' src='{$loading_image_url}' /></div></div>";
			$html .= "<div id='rnmmt_dialogbox' class='rnmmt_dialogbox'><div><img class='mt_logo' src='{$logo_image_url}' /><p></p><div><a id='rnmmt_closeDialog' href='#' class='btnmain'>Close</a></div></div></div>";
			return $html;
		}
	}
	
	$wp_meeting_truth_events_plugin = new Meeting_Truth_Events_Plugin();
}
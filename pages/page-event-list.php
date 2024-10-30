<?php

require_once( dirname( plugin_dir_path( __FILE__ ) ) . '/classes/class-meeting-truth-service.php' );
require_once( dirname( plugin_dir_path( __FILE__ ) ) . '/classes/class-meeting-truth-event.php' );
require_once( dirname( plugin_dir_path( __FILE__ ) ) . '/classes/class-meeting-truth-event-set.php' );

if (  ! class_exists( 'Meeting_Truth_Event_List_Page' ) ) {
	class Meeting_Truth_Event_List_Page {
		private $service = null;
		private $page_no = 1;
		
		private $event_filter = '';
		private $venue_id = 0;
		private $group_id = 0;
		private $teacher_id = 0;
		private $country = '';
		private $mturl = '';
		private $postback_url = '';
		private $url = '';
		private $html = '';
		private $char = '?';
		private $events_display_type = '';
		private $events_display_grouping = '';
		
		private $pay_location = 'onmt';
		
		public function Meeting_Truth_Event_List_Page() {
			$this->mturl = 'http://www.meetingtruth.com';
			$the_id = get_the_ID();
			
			if( is_page( $the_id ) ) {
				$this->url = get_page_link( $the_id );
			} else {
				$this->url = get_permalink( $the_id );
			}
			
			if (strrpos($this->url, '?') > 0){
				$this->char = '&';
			}
			
			
			$this->pay_location = get_option( 'rnmmt_rdoPayment' );
			
			$this->service = Meeting_Truth_Service::get_instance();
		}
		
		public function page_load( $request ) {
			// The request MUST be read after the meta data, as request variables can change
			// the filter type and filter values.
			$this->get_meta_data();
			$this->read_variables_from_request( $request );
			
			$this->show();
		}
		
		private function read_variables_from_request( $request ) {
			$query_array = array();
			$this->postback_url = $this->url;
			
			if ( isset( $request['mtp'] ) ) {
				$this->page_no = $request['mtp'];
				$this->postback_url .= $this->char .'mtp=' . $this->page_no;
			}
			
			if ( isset( $request['mttid'] ) ) {
				$this->event_filter = 'teacher';
				$this->teacher_id = $request['mttid'];
				$this->postback_url .= $this->char . 'mttid=' . $this->teacher_id;
			}
		}
		
		private function get_meta_data() {
			$post_id = get_the_ID();
			
			$this->display_events = get_post_meta( $post_id, 'rnmmt_post_display_events', true );
			$this->display_sessions = get_post_meta( $post_id, 'rnmmt_post_display_sessions', true );
			$this->event_filter = get_post_meta( $post_id, 'rnmmt_post_events_filter', true );
			$this->teacher_id = get_post_meta( $post_id, 'rnmmt_post_events_filter_teacher_id', true );
			$this->country = get_post_meta( $post_id, 'rnmmt_post_events_filter_country', true );
			$this->venue_id = get_post_meta( $post_id, 'rnmmt_post_events_filter_venue_id', true );
			$this->group_id = get_post_meta( $post_id, 'rnmmt_post_events_filter_group_id', true );
			
			$this->events_display_type = get_post_meta( $post_id, 'rnmmt_post_events_display_type', true );
			$this->events_display_grouping = get_post_meta( $post_id, 'rnmmt_post_events_display_grouping', true );
		}
		
		private function show() {
			if( $this->display_events == 'true' ) {
				$this->show_events();
			}
			
			if( $this->display_sessions == 'true' ) {
				$this->show_sessions();
			}
		}
		
		private function show_events() {
			$event_set = $this->get_event_set();
			$show_teacher = true;
			
			$heading_html = '';
			$sessions_html = '';
			$sessions_heading_html = '';
			
			if( $this->event_filter == 'teacher' ) {
				$show_teacher = false;
				
				if( count($event_set->Events) > 0 ) {
					$teacher_name = $event_set->Events[0]->TeacherName;
					$heading_html = $this->get_teacher_heading_html( $teacher_name );
				}
			}
			
			if( $event_set->Count > 0 ) {
				$events_html = $this->get_events_html( $event_set, $show_teacher );
						
				
				$this->html .= $heading_html . $events_html;
			}
		}
		
		private function show_sessions() {
			$sessions_set = $this->service->get_sessions_by_teacher( $this->teacher_id );
				
			if( $sessions_set->count > 0 ) {
				$sessions_html = $this->get_sessions_html( $sessions_set );
					
				$this->html .= $sessions_html;
			}
		}
		
		private function get_event_set() {
			$event_set = null;
			
			switch( $this->event_filter ) {
				case 'all':
					$event_set = $this->service->get_events( '', $this->page_no );
					break;
				case 'country':
					$event_set = $this->service->get_events( 'xC' . $this->country, $this->page_no );
					break;
				case 'group':
					$event_set = $this->service->get_group_events( $this->group_id );
					break;
				case 'teacher':
					$event_set = $this->service->get_teacher_events( $this->teacher_id );
					break;
				case 'venue':
					$event_set = $this->service->get_venue_events( $this->venue_id );
					break;
				default :
					$event_set = $this->service->get_events( '', $this->page_no );
					break;
			}
			
			return $event_set;
		}
		
		private function get_teacher_heading_html( $teacher_name ) {
			return "<h4 class='highlight_title'>Meetings with {$teacher_name}</h4>";
		}
		
		private function get_events_html ( $event_set, $display_teacher = true ) {
			$html = '';
			
			if( $event_set->Pages == 1 ) {
				
				switch($this->events_display_type) {
					case 'detail':
						$html = $this->get_events_detail_html( $event_set, $display_teacher );
						break;
					default: case 'table':
						$html = $this->get_event_page_html( $event_set->Events, $display_teacher );
						break;
				}
			
				
			} else if ( $event_set->Pages > 1 ) {
				$html = $this->get_paged_event_set_html( $event_set, $display_teacher );
			}
			
			return $html;
		}
		
		private function get_event_page_html( $events, $display_teacher = true ) {
			$HTML = '<table class="eventListing"><thead><tr><th class="date">Date</th><th class="event">Event</th><th class="country">Location</th></tr></thead><tbody>';
			
			foreach( $events as &$event ) {
				$location_name = $event->LocationName;

				if( $event->Filter == 'xonline' ) {
					$location_name = 'Online';
				}
				
				$HTML .= '<tr itemscope itemtype="http://schema.org/Event">';
				
				$start_date = $this->get_iso8601_date( $event->StartDateRaw );
				$HTML .= "<td><time itemprop='startDate' datetime='{$start_date}'>{$event->DateString}</time>";
				if( null !== $event->EndDateRaw ) {
					$end_date = $this->get_iso8601_date( $event->EndDateRaw );
					$HTML .= "<time itemprop='endDate' datetime='{$end_date}'></time>";
				}
				$HTML .= "</td><td><a href='{$this->url}" . $this->char . "mteid={$event->UniqueId}' itemprop='url' >";
				
				if( $display_teacher ) {
					$HTML .= $event->TeacherName . '<br />';
				}
				
				$HTML .= "<span itemprop='name'>{$event->Name}</span></a></td>";
				$HTML .= "<td itemprop='location'>{$location_name}</td>";
				$HTML .= '</tr>';
			}
			
			$HTML .= "</tbody></table>";
			
			return $HTML;
		}
		
		private function get_paged_event_set_html ( $event_set, $display_teacher = true ) {
			$paging_html = $this->get_paging_html( $event_set->Pages, $event_set->PageNo );

			$html = $paging_html;
			
			switch($this->events_display_type) {
				case 'detail':
					$html .= $this->get_events_detail_html( $event_set, true );
					break;
				default: case 'table':
					$html .= $this->get_event_page_html( $event_set->Events, true );
					break;
			}
			
			$html .= $paging_html;
			
			return $html;
		}
		
		private function get_paging_html ( $page_count, $current_page ) {
			$url = $this->postback_url;
			
			$html = '<div class="pager">';
			for ($page = 1; $page <= $page_count; $page++)
			{
				if ($page <> $current_page) {
					$html .= "<a href='{$this->url}" . $this->char . "mtp={$page}'><span>{$page}</span></a>";
				} else {
					$html .= "<span>{$page}</span>";
				}
			}
			$html .= "</div>";
			
			return $html;
		}
		
		
		
		private function get_events_detail_html( $event_set, $display_teacher ) {
			$grouped_events = $this->get_events_grouped_by_date( $event_set );
		
			$html = '<div class="eventListing">';
			foreach( $grouped_events as $group_name => $events ) {
				$html .= "<h4 class='highlight_title'>{$group_name}</h4>";
			
				foreach( $events as &$event ) {
					$location_name = $event->LocationName;

					if( $event->Filter == 'xonline' ) {
						$location_name = 'Online';
					}
				
					$html .= '<div class="rnmmt_event" itemscope itemtype="http://schema.org/Event">';
					if( $display_teacher ) {
						$html .=  "<div class='rnmmt_teacher'>{$event->TeacherName}</div>";
					}
					$html .= "<div class='rnmmt_name' itemprop='name'>{$event->Name}</div>";
					
					$start_date = $this->get_iso8601_date( $event->StartDateRaw );
					$html .= "<div class='rnmmt_start_date'><time itemprop='startDate' datetime='{$start_date}'>{$event->DateString}</time>";
					
					if( null !== $event->EndDateRaw ) {
						$end_date = $this->get_iso8601_date( $event->EndDateRaw );
						$html .= "<time itemprop='endDate' datetime='{$end_date}'></time>";
					}
					
					$html .= "</div>";
					$html .= "<div class='rnmmt_location' itemprop='location'>{$location_name}</div>";
					$html .= "<div class='rnmmt_summary'>{$event->Summary}</div>";
					
					$html .= "<a href='{$this->url}" . $this->char . "mteid={$event->UniqueId}' itemprop='url' >Details</a>";
					
					$html .= '</div>';
				}
			}
			$html .= '</div>';
			
			return $html;
		}
		
		private function get_iso8601_date( $event_date ) {
			$con_date = str_replace( '/', '-', $event_date );
			$timestamp = strtotime( $con_date );
			
			return date('Y-m-d H:i:s', $timestamp);
		}
		
		private function get_events_grouped_by_date( $event_set ) {
			$grouped = array();
			$years = array();
			
			foreach( $event_set->Events as &$event) {
				$event->StartDateRaw = str_replace( '/', '-', $event->StartDateRaw );
				$date = getdate( strtotime( $event->StartDateRaw ) );
				
				$year = $date['year'];
				$month = $date['month'];
				
				if( ! isset( $years[ $year ] ) ) {
					$years[ $year ] = array();
				}
				
				if( ! isset( $years[ $year ][ $month ] ) ) {
					$years[ $year ][ $month ] = array();
				}
				
				array_push( $years[ $year ][ $month ], $event );
			}
			
			foreach( $years as $year => $months ) {
				
				foreach( $months as $month => $events ) {
					$group_name = $month . ' ' . $year;
					
					$grouped[ $group_name ] = $events;
				}
			}
			
			return $grouped;
		}
		
	
		
		
		private function get_sessions_html ( $sessionSet ) {
			$HTML = '<div class=\"tickets\"><h4 class="highlight_title">One-to-One Sessions</h4>';
			
			if(!$sessionSet->allfree) {
				$HTML .= "<p>All prices are in <strong>" . $sessionSet->currency . "</strong></p>";
			}
			
			$HTML .= "<table class=\"tickets eventListing\"><tbody><tr><th class='nameheader'>Name</th><th class='durationheader'>Duration</th><th class='priceheader'>Price</th><th class='bookheader'></th></tr></thead>";

			foreach( $sessionSet->sessions as &$session ) {
				$HTML .= "<tr>";
				$HTML .= "<td><strong>" . $session->Name . "</strong><br />" . $session->Summary . "</td>";
				$HTML .= "<td>" . $session->Duration . "</td>";
				$HTML .= "<td>" . $session->Price . "</td>";
				$HTML .= "<td><a href=\"\" class=\"btnSession\" data-id=\"" . $session->UniqueId . "\">Book</a></td>";
				$HTML .= "</tr>";
			}
		
			
			$HTML .= "</tbody></table><div id='rnmmt_content'></div>";
			
			//add card payment icons
			$HTML .= '<table style="display:none;"><tr><td colspan="2" id="card-images">';
			$HTML .= '<img src="' . plugins_url( '/img/visa.png', dirname( __FILE__ ) )  . '" alt="Visa" title="Visa">';
			$HTML .= '<img src="' . plugins_url( '/img/visa-electron.png', dirname( __FILE__ ) ) . '" alt="Visa" title="Visa">';
			$HTML .= '<img src="' . plugins_url( '/img/maestro.png', dirname( __FILE__ ) ) . '" alt="Maestro" title="Maestro">';
			$HTML .= '<img src="' . plugins_url( '/img/mastercard.png', dirname( __FILE__ ) ) . '" alt="Mastercard" title="Mastercard">';
			$HTML .= '<img src="' . plugins_url( '/img/solo.png', dirname( __FILE__ ) ) . '" alt="Solo" title="Solo">';
			$HTML .= '<img src="' . plugins_url( '/img/american-express.png', dirname( __FILE__ ) ) . '" alt="American Express" title="American Express">';
			$HTML .= '<img src="' . plugins_url( '/img/paypal.png', dirname( __FILE__ ) ) . '" alt="PayPal" title="PayPal">';
			
			$HTML .= '</td></tr>';

			if( $this->pay_location == 'onmt' ) {
				$HTML .= '<tr><td style="text-align: right;">You will be redirected to Meeting Truth to finish booking</td></tr>';
			}

			$HTML .= '</tbody></table>';
			
			$HTML .= "<input type='hidden' id='pay_location' value='{$this->pay_location}' />";
			
			return $HTML;
		}
		
		public function get_html() {
			$html = '';
			
			if( strlen( $this->html ) > 0 ) {
				$html = "<form method='post' action='{$this->url}' id='rnmmt_frm'>";
				$html .= "<div id='rnmmt_event_list'>{$this->html}</div>";
				$html .= "</form>";
			}
			
			return $html;
		}
	}

}


?>
<?php

require_once( 'classes/class-meeting-truth-service.php' );

if (  ! class_exists( 'Meeting_Truth_Event_Admin_Meta' ) ) {
	class Meeting_Truth_Event_Admin_Meta {
		private $service = null;
		
		function Meeting_Truth_Event_Admin_Meta() {
			$this->service = Meeting_Truth_Service::get_instance();
			$this->bind_actions();
		}
		
		function bind_actions() {
			add_action( 'add_meta_boxes', array( &$this, 'action_add_meta_boxes' ) );
			add_action( 'save_post', array( &$this, 'action_save_post_meta' ), 10, 2 );
		}
		
		function action_add_meta_boxes() {
			add_meta_box( 
				'rnmmt-post-display',
				'Meeting Truth Events',
				array( &$this, 'render_post_meta_box' ),
				'post',
				'advanced',
				'default'
			);
			
			add_meta_box( 
				'rnmmt-post-display',
				'Meeting Truth Events',
				array( &$this, 'render_post_meta_box' ),
				'page',
				'advanced',
				'default'
			);
		}
		
		function render_post_meta_box( $object, $box ) {
			$teachers = $this->service->get_teachers();
			$countries = $this->service->get_countries();
			$group_set = $this->service->get_groups();
			
			wp_nonce_field( basename( __FILE__ ), 'rnmmt_events_meta_nonce' );
			
			
			
			echo $this->get_display_select_html( $object->ID );
			echo $this->get_filters_select_html( $object->ID, $teachers, $countries, $group_set);
			echo $this->get_teachers_select_html( $object->ID, $teachers );
			echo $this->get_event_display_select_html( $object->ID );
			
		}
		
		private function get_display_select_html( $post_id ) {
			$display = get_post_meta( $post_id, 'rnmmt_post_display', true);
			
			$display_attributes = array(
				'id' => 'rnmmt_post_display',
				'name' => 'rnmmt_post_display',
			);
			
			$html = '<label for="rnmmt_post_display">Display</label>';
			$html .= $this->get_display_dropdown_html( $display_attributes, $display );
			return $html;
		}
		
		private function get_filters_select_html( $post_id, $teachers, $countries, $group_set ) {
			$selected_teacher = 0;
			$selected_country = '';
			$selected_group = 0;
			
			$event_filter = get_post_meta( $post_id, 'rnmmt_post_events_filter', true );
			
			$filter_teacher_attributes = array(
				'id' => 'rnmmt_event_teacher_id',
				'name' => 'rnmmt_event_teacher_id',
			);
			
			$country_attributes = array(
				'id' => 'rnmmt_event_country',
				'name' => 'rnmmt_event_country',
			);
			
			$group_attributes = array(
				'id' => 'rnmmt_event_group_id',
				'name' => 'rnmmt_event_group_id',
			);
			
			$session_teacher_attributes = array(
				'id' => 'rnmmt_session_teacher_id',
				'name' => 'rnmmt_session_teacher_id',
			);
			
			switch($event_filter) {
				case 'teacher':
					$selected_teacher = get_post_meta( $post_id, 'rnmmt_post_events_filter_teacher_id', true );
					$country_attributes['disabled'] = 'disabled';
					$group_attributes['disabled'] = 'disabled';
					break;
				case 'country':
					$selected_country = get_post_meta( $post_id, 'rnmmt_post_events_filter_country', true );
					$filter_teacher_attributes['disabled'] = 'disabled';
					$group_attributes['disabled'] = 'disabled';
					break;
				case 'group':
					$selected_group = get_post_meta( $post_id, 'rnmmt_post_events_filter_group_id', true );
					$filter_teacher_attributes['disabled'] = 'disabled';
					$country_attributes['disabled'] = 'disabled';
					break;
				default:
					$event_filter = 'all';
					$country_attributes['disabled'] = 'disabled';
					$filter_teacher_attributes['disabled'] = 'disabled';
					$group_attributes['disabled'] = 'disabled';
					break;
			}
			
			$session_teachers_select_html = $this->get_teachers_dropdown_html( $session_teacher_attributes, $teachers, $selected_teacher );
			$filter_teachers_select_html = $this->get_teachers_dropdown_html( $filter_teacher_attributes, $teachers, $selected_teacher );
			$countries_select_html = $this->get_countries_dropdown_html( $country_attributes, $countries, $selected_country );
			$groups_select_html = $this->get_groups_dropdown_html( $group_attributes, $group_set, $selected_group );
			
			$html = '';
			$html .= '<div id="rnmmt_event_filter_select"><p><strong>Filter Events</strong></p>';
			$html .= $this->get_filter_radio( 'rnmmt_event_filter', 'rnmmt_event_filter_all', 'all', 'Show All', $event_filter );
			$html .= '<br />';
			$html .= $this->get_filter_radio( 'rnmmt_event_filter', 'rnmmt_event_filter_teacher', 'teacher', 'By Teacher', $event_filter, 'rnmmt_event_teacher_id' );
			$html .= $filter_teachers_select_html;
			$html .= '<br />';
			$html .= $this->get_filter_radio( 'rnmmt_event_filter', 'rnmmt_event_filter_country', 'country', 'By Country', $event_filter, 'rnmmt_event_country' );
			$html .= $countries_select_html;
			$html .= '<br />';
			/*echo $this->get_filter_radio( 'rnmmt_event_filter', 'rnmmt_event_filter_venue', 'venue', 'By Venue', $event_filter );
			echo $venue_select_html;*/
			
			if( $group_set->Count > 0 ) {
				$html .= $this->get_filter_radio( 'rnmmt_event_filter', 'rnmmt_event_filter_group', 'group', 'By Group', $event_filter, 'rnmmt_event_group_id' );
				$html .= $groups_select_html;
			}
			
			$html .= '</div>';
			return $html;
		}
		
		private function get_teachers_select_html( $post_id, $teachers ) {
			$selected_teacher = 0;
			$selected_teacher = get_post_meta( $post_id, 'rnmmt_post_events_filter_teacher_id', true );
			
			$session_teacher_attributes = array(
				'id' => 'rnmmt_session_teacher_id',
				'name' => 'rnmmt_session_teacher_id',
			);
			
			$session_teachers_select_html = $this->get_teachers_dropdown_html( $session_teacher_attributes, $teachers, $selected_teacher );
			
			$html = '<div id="rnmmt_session_teacher_select"><p><strong>Teacher</strong></p>';
			$html .= $session_teachers_select_html;
			$html .= '</div>';
			return $html;
		}
		
		private function get_event_display_select_html( $post_id ) {
			
			$event_display_type = get_post_meta( $post_id, 'rnmmt_post_events_display_type', true );
			$event_display_grouping = get_post_meta( $post_id, 'rnmmt_post_events_display_grouping', true );
			$event_display = '';
			
			switch($event_display_type) {
				case 'detail':
					$event_display = $event_display_type . '-' . $event_display_grouping;
					break;
				case 'table':
					$event_display = $event_display_type;
					break;
				default:
					$event_display = 'table';
					break;
			}
			
			$html = '<div id="rnmmt_event_display_select"><p><strong>Events Display Format</strong></p>';
			$html .= $this->get_filter_radio( 'rnmmt_event_display', 'rnmmt_event_display_table', 'table', 'Simple listings', $event_display);
			$html .= '<br/>';
			$html .= $this->get_filter_radio( 'rnmmt_event_display', 'rnmmt_event_display_detail_month', 'detail-month', 'Full listings with summaries', $event_display);
			$html .= "</div>";
			return $html;
		}
		
		private function get_filter_radio( $name, $id, $value, $label, $selected_value, $dropdown_id = '' ) {
			$checked = '';
			
			if( $value == $selected_value ) {
				$checked = 'checked="checked"';
			}
			
			$html = "<input type='radio' name='{$name}' id='{$id}' value='{$value}' data-dropdown='{$dropdown_id}' {$checked}/>";
			$html .= "<label for='{$id}'>{$label}</label> ";
			
			return $html;
		}
		
		private function get_display_dropdown_html( $attributes, $selected ) {
			$HTML = '<select';
			
			foreach ( $attributes as $key => $value ) {
				$esc_value = esc_attr( $value );
				$HTML .= " {$key}='{$esc_value}'";
			}
			
			$HTML .= '>';
			
			$values = array(
				"None" => "none",
				"Events" => "events",
				"Sessions" => "sessions",
				"Events + Sessions" => "both"
			);
			
			foreach ($values as $name => $value) {
				if ($value == $selected) {
					$s = 'selected="selected"';
				} else {
					$s = "";
				}
				$HTML .= "<option value='{$value}' {$s}>{$name}</option>";
			}
			
			$HTML .= '</select>';
			return $HTML;
		}
		
		private function get_teachers_dropdown_html( $attributes, $teachers, $selected ) {
			$HTML = '<select';
			
			foreach ( $attributes as $key => $value ) {
				$esc_value = esc_attr( $value );
				$HTML .= " {$key}='{$esc_value}'";
			}
			
			$HTML .= '>';
			
			foreach ($teachers as &$teacher) {
				$value = esc_attr( $teacher->id );
				
				if ($teacher->id == $selected) {
					$s = 'selected="selected"';
				} else {
					$s = "";
				}
				$HTML .= "<option value='{$value}' {$s}>{$teacher->name}</option>";
			}
			
			$HTML .= '</select>';
			return $HTML;
		}
		
		private function get_groups_dropdown_html( $attributes, $group_set, $selected ) {
			$HTML = '<select';
			
			foreach ( $attributes as $key => $value ) {
				$esc_value = esc_attr( $value );
				$HTML .= " {$key}='{$esc_value}'";
			}
			
			$HTML .= '>';
			
			foreach ($group_set->Groups as &$group) {
				$value = esc_attr( $group->Id );
				
				if ($group->Id == $selected) {
					$s = 'selected="selected"';
				} else {
					$s = "";
				}
				$HTML .= "<option value='{$value}' {$s}>{$group->Name}</option>";
			}
			
			$HTML .= '</select>';
			return $HTML;
		}
		
		private function get_countries_dropdown_html( $attributes, $countries, $selected ) {
			$HTML = '<select';
			
			foreach ( $attributes as $key => $value ) {
				$esc_value = esc_attr( $value );
				$HTML .= " {$key}='{$esc_value}'";
			}
			
			$HTML .= '>';
			
			foreach ($countries as &$country) {
				$value = str_replace( ' ', '', $country->name );
				$value = esc_attr( $value );
				
				if ($value == $selected) {
					$s = 'selected="selected"';
				} else {
					$s = "";
				}
				$HTML .= "<option value='{$value}' {$s}>{$country->name}</option>";
			}
			
			$HTML .= '</select>';
			return $HTML;
		}
		
		
		function action_save_post_meta( $post_id, $post ) {
			if ( ! isset( $_POST['rnmmt_events_meta_nonce'] ) || ! isset( $_POST['rnmmt_event_filter'] ) ) {
				return $post_id;
			}
			
			$nonce = $_POST['rnmmt_events_meta_nonce'];
			
			if ( ! wp_verify_nonce( $nonce, basename( __FILE__ ) ) ) {
				return $post_id;
			}
			
			$post_type = get_post_type_object( $post->post_type );
			
			if ( ! current_user_can( $post_type->cap->edit_post, $post_id ) ) {
				return $post_id;
			}
			
			$display = $_POST['rnmmt_post_display'];
			$display_events = 'false';
			$display_sessions = 'false';
			
			switch( $display ) {
				case 'events':
					$display_events = 'true';
					
					$this->save_event_filter_meta( $post_id );
					break;
				case 'sessions':
					$display_sessions = 'true';
					
					$this->save_session_teacher_meta( $post_id );
					break;
				case 'both':
					$display_events = 'true';
					$display_sessions = 'true';
					
					$this->save_session_teacher_meta( $post_id );
					break;
			}
			
			$this->save_post_meta( $post_id, 'rnmmt_post_display', $display );
			$this->save_post_meta( $post_id, 'rnmmt_post_display_events', $display_events );
			$this->save_post_meta( $post_id, 'rnmmt_post_display_sessions', $display_sessions );
			
			$this->save_event_display_meta( $post_id );
		}
		
		function save_session_teacher_meta( $post_id ) {
			$teacher_id = $_POST['rnmmt_session_teacher_id'];
			
			$this->save_post_meta( $post_id, 'rnmmt_post_events_filter', 'teacher' );
			$this->save_post_meta( $post_id, 'rnmmt_post_events_filter_teacher_id', $teacher_id );
		}
		
		function save_event_filter_meta( $post_id ) {
			$teacher_id = false;
			$country = false;
			$venue_id = false;
			$group_id = false;
			$event_filter = $_POST['rnmmt_event_filter'];
			
			switch( $event_filter ) {
				case 'all':
					break;
				case 'teacher':
					$teacher_id = $_POST['rnmmt_event_teacher_id'];
					break;
				case 'country':
					$country = $_POST['rnmmt_event_country'];
					break;
				case 'venue':
					break;
				case 'group':
					$group_id = $_POST['rnmmt_event_group_id'];
					break;
			}
			
			$this->save_post_meta( $post_id, 'rnmmt_post_events_filter', $event_filter );
			$this->save_post_meta( $post_id, 'rnmmt_post_events_filter_teacher_id', $teacher_id );
			$this->save_post_meta( $post_id, 'rnmmt_post_events_filter_country', $country );
			$this->save_post_meta( $post_id, 'rnmmt_post_events_filter_venue_id', $venue_id );
			$this->save_post_meta( $post_id, 'rnmmt_post_events_filter_group_id', $group_id );
		}
		
		function save_event_display_meta( $post_id ) {
			$rnmmt_event_display = $_POST['rnmmt_event_display'];
			
			$display_type = '';
			$display_grouping = '';
			
			switch($rnmmt_event_display) {
				case 'table':
					$display_type = 'table';
					$display_grouping = '';
					break;
				case 'detail-month':
					$display_type = 'detail';
					$display_grouping = 'month';
			}
			
			$this->save_post_meta( $post_id, 'rnmmt_post_events_display_type', $display_type );
			$this->save_post_meta( $post_id, 'rnmmt_post_events_display_grouping', $display_grouping );
		}
		
		function save_post_meta( $post_id, $name, $value ) {
			$old_value = get_post_meta( $post_id, $name, true );
			
			if( $value && '' == $old_value ) {
				add_post_meta( $post_id, $name, $value, true);
			} else if ( $value && $value != $old_value ) {
				update_post_meta( $post_id, $name, $value);
			} else if ( '' == $value && $old_value ) {
				delete_post_meta( $post_id, $name, $value);
			}
		}
	}

	$mtle = new Meeting_Truth_Event_Admin_Meta();
}
?>
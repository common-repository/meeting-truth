<?php

require_once('class-meeting-truth-event.php');
require_once('class-meeting-truth-event-set.php');
require_once('class-meeting-truth-session.php');
require_once('class-meeting-truth-session-set.php');
require_once('class-meeting-truth-group.php');
require_once('class-meeting-truth-group-set.php');

if (  ! class_exists( 'Meeting_Truth_Service' ) ) {
	class Meeting_Truth_Service {
		private $service_url = 'http://meetingtruth.com/MTUtils.asmx/';
		//private $service_url = 'http://localhost:55970/MTUtils.asmx/';
		protected static $instance;
		protected function Meeting_Truth_Service() { }
		final private function __clone() { }
	   
		public static function get_instance() {
			if( ! isset( self::$instance ) ) {
				self::$instance = new Meeting_Truth_Service();
			}
			return self::$instance;
		}
		
		private function get_items_from_service( $service_name, $data = array() ) {
			$json_results = $this->get_json($service_name, $data);

			if ($json_results === NULL) 
				throw new Exception('Error parsing json');
			
			$items = $json_results->items;
			return $items;
		}
		
		private function get_json($service, $get_data = array()) {
			$endpoint = $this->service_url . $service;
			$get_string = '';
			
			foreach($get_data as $key=>$value) {
				$get_string .= $key . '=' . $value . '&';
			}

			// setup curl to make a call to the endpoint
			$session = curl_init($endpoint . '?' . $get_string);

			//Only add the GET data to the JSON call if there's data there
			if (!empty($get_string)) {
				//Send data to service using GET
				curl_setopt( $session, CURLOPT_HTTPHEADER, array('Content-Type:application/x-www-form-urlencoded'));
			}
			
			// indicates that we want the response back
			curl_setopt($session, CURLOPT_RETURNTRANSFER, true);

			// exec curl and get the data back
			$raw_json = curl_exec($session);
			
			//Close the curl session once we are finished retrieving the data
			curl_close($session);
			
			$raw_json = str_ireplace("(","",$raw_json);
			$raw_json = str_ireplace(")","",$raw_json);
			$raw_json = trim($raw_json);
			$raw_json = "{ \"items\" : " . $raw_json . "}";    
			
			$json = json_decode($raw_json);
			
			if( $json == null ) 
				throw new Exception( $raw_json . $get_string);
				
			return $json;
		}
		
		public function get_teachers() {
			return $this->get_items_from_service( 'GetTeacherList' );
		}
		
		public function get_countries() {
			return $this->get_items_from_service( 'GetCountryList' );
		}
		
		public function get_groups() {
			$group_set = new Meeting_Truth_Group_Set();
			$group_set->Count = 0;
			
			try {
				$raw_groups = $this->get_items_from_service( 'GetOrganisations' );
				$group_set->count = count( $raw_groups );
				
				foreach ( $raw_groups as &$raw_group ) {
					$group = new Meeting_Truth_Group();
					$group->Id = $raw_group->id;
					$group->Name = $raw_group->name;
					
					$group_set->add_group( $group );
				}
			} catch (Exception $e) {
				$group_set = new Meeting_Truth_Group_Set();
			}
	
			return $group_set;
		}
		
		public function get_teacher_events( $teacher_id ) {
			$eventSet = new Meeting_Truth_Event_Set();
			$data = array( 'id' => $teacher_id );
			$raw_events = $this->get_items_from_service( 'GetEventsByTeacherV2', $data );
			
			if( count($raw_events) > 0 ) {
				$eventSet->Pages = 1;
				$eventSet->PageNo = 1;
				$eventSet->Count = count($raw_events);
				
				foreach( $raw_events as &$raw_event ) {
					$Event = new Meeting_Truth_Event();
					$Event->UniqueId = $raw_event->uniqueId;
					$Event->Name = $raw_event->name;
					$Event->Summary = $raw_event->summary;
					$Event->Description = $raw_event->description;
					$Event->DateString = $raw_event->dateString;
					$Event->TeacherName = $raw_event->teacher;
					$Event->LocationName = $raw_event->location;
					$Event->Filter = $raw_event->filter;
					$Event->Country = $raw_event->country;
					$Event->StartDate = $raw_event->startDate;
					$Event->EndDate = $raw_event->dateend;
					$Event->StartDateRaw = $raw_event->datestartraw;
					
					$eventSet->AddEvent($Event);
				}
			} else {
				$eventSet->Pages = 0;
				$eventSet->Count = 0;
			}
			
			return $eventSet;
		}
		
		public function get_venue_events( $venue_id ) {
			$eventSet = new Meeting_Truth_Event_Set();
			$data = array("id" => $venue_id);
			$raw_events = $this->get_items_from_service( 'GetEventsByVenue', $data );
			
			if(count($raw_events) > 0) {
				$eventSet->Pages = 1;
				$eventSet->PageNo = 1;
				$eventSet->Count = count($raw_events);
				
				foreach($raw_events as &$raw_event) {
					$Event = new Meeting_Truth_Event();
					$Event->UniqueId = $raw_event->id;
					$Event->Name = $raw_event->name;
					$Event->Description = $raw_event->description;
					$Event->DateString = $raw_event->datestring;
					$Event->LocationName = $raw_event->location;
					$Event->Filter = $raw_event->filter;
					$eventSet->AddEvent($Event);
				}
			} else {
				$eventSet->Pages = 0;
				$eventSet->Count = 0;
			}
			
			return $eventSet;
		}
		
		public function get_group_events( $group_id ) {
		
			$eventSet = new Meeting_Truth_Event_Set();
			
			try {
				$data = array("Id" => $group_id);
				$raw_events = $this->get_items_from_service( 'GetEventsByGroup', $data );
				
				if(count($raw_events) > 0) {
					$eventSet->Pages = 1;
					$eventSet->PageNo = 1;
					$eventSet->Count = count($raw_events);
					
					foreach($raw_events as &$raw_event) {
						$Event = new Meeting_Truth_Event();
						$Event->UniqueId = $raw_event->id;
						$Event->Name = $raw_event->name;
						$Event->Description = $raw_event->description;
						$Event->DateString = $raw_event->datestring;
						$Event->LocationName = $raw_event->location;
						$Event->Filter = $raw_event->filter;
						$eventSet->AddEvent($Event);
					}
				} else {
					$eventSet->Pages = 0;
					$eventSet->Count = 0;
				}
			} catch (Exception $ex) {
				$eventSet = new Meeting_Truth_Event_Set();
			}
			
			return $eventSet;
		}
		
		public function get_event_details( $event_id ) {
			$data = array("eventId" => $event_id);
			$raw_event = $this->get_items_from_service( 'GetEventDetails', $data );

			$Event = new Meeting_Truth_Event();
			$Event->UniqueId = $raw_event->id;
			$Event->Name = $raw_event->name;
			$Event->Summary = $raw_event->summary;
			$Event->Description = $raw_event->description;
			$Event->TeacherName = $raw_event->teachername;
			$Event->LocationName = $raw_event->locality;
			$Event->StartDate = $raw_event->datestart;
			$Event->EndDate = $raw_event->dateend;
			$Event->DateString = $raw_event->datestring;
			$Event->Address = $raw_event->address;
			$Event->Organiser = $raw_event->organiser;
			$Event->MainImage = $raw_event->mainimage;
			$Event->Tickets = $raw_event->tickets;
			
			$Event->Currency = $raw_event->currency;
			$Event->CurrencyHTML = $raw_event->currencyHtml;    
			$Event->TicketType = $raw_event->ticketType;
			$Event->Available = $raw_event->available;
			$Event->TotalTickets = $raw_event->totaltickets;
			$Event->Latitude = $raw_event->lat;
			$Event->Longitude = $raw_event->long;
			$Event->HouseName = $raw_event->housename;
			$Event->ShowMap = ($raw_event->showmap == 1);
			$Event->Map = $raw_event->map;
			
			$Event->HasDiscountCodes = ($raw_event->hasdiscountcodes == 1);
			
			return $Event;
		}
		
		public function get_events( $filter, $page_no ) {
			$eventSet = new Meeting_Truth_Event_Set();
			
			$remaining = 0;
			$data = array("pageNo" => $page_no, "remaining" => (string)$remaining, "filter" => $filter);
			$rawEvents = $this->get_items_from_service("GetEventListItemsFiltered", $data);
			
			if(count($rawEvents) > 0) {
				$eventSet->Pages = $rawEvents[0]->pages;
				$eventSet->PageNo = $page_no;
				$eventSet->Count = count($rawEvents);
				
				foreach($rawEvents as &$raw_event) {
					$Event = new Meeting_Truth_Event();
					$Event->UniqueId = $raw_event->uniqueId;
					$Event->Name = $raw_event->name;
					$Event->DateString = $raw_event->dateString;
					$Event->TeacherName = $raw_event->teacher;
					$Event->LocationName = $raw_event->location;
					$Event->Filter = $raw_event->filter;
					
					$Event->StartDateRaw = $raw_event->datestartraw;
					$Event->EndDateRaw = $raw_event->dateendraw;
					$eventSet->AddEvent($Event);
				}
			} else {
				$eventSet->Pages = 0;
				$eventSet->Count = 0;
			}
			
			return $eventSet;
		}
		
		public function get_sessions_by_teacher( $teacher_id ){
			$sessionSet = new Meeting_Truth_Session_Set();
			
			$data = array("Id" => $teacher_id);
			$rawSessions = $this->get_items_from_service("GetSessionsByTeacher", $data);
			
			if(count($rawSessions) > 0) {
			
				$sessionSet->count = count($rawSessions);
				$sessionSet->currency = $rawSessions[0]->currency;
				$sessionSet->fee = $rawSessions[0]->fee;
				$sessionSet->allfree = $rawSessions[0]->allfree;
				
				//foreach($rawSessions->$sessions as &$raw_session) {
				for ($x=1;$x<=($sessionSet->count - 1);$x++){
					$Session = new Meeting_Truth_Session();
					$Session->UniqueId = $rawSessions[$x]->id;
					$Session->Name = $rawSessions[$x]->name;
					$Session->Summary = $rawSessions[$x]->summary;
					$Session->Price = $rawSessions[$x]->price;
					$Session->Duration = $rawSessions[$x]->duration;
					
					$sessionSet->add_session($Session);
				}
			} else {
				$sessionSet->Count = 0;
			}
			
			return $sessionSet;
		}
		
		public function check_voucher( $discount_code, $event_id ) {
			$isValid = false;
		
			$data = array( "discountCode" => $discount_code, "eventId" => $event_id );
			$response = $this->get_items_from_service("CheckDiscountCode", $data);
			
			if(count($response) > 0) {
				$isValid = $response[0]->IsValid;
				
				$isValid = ($isValid == 'true' || $isValid == 1);
			}
			
			return $isValid;
		}
	}
}
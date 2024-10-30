<?php

require_once( dirname( plugin_dir_path( __FILE__ ) ) . '/classes/class-meeting-truth-service.php' );
require_once( dirname( plugin_dir_path( __FILE__ ) ) . '/classes/class-meeting-truth-event.php' );
require_once( dirname( plugin_dir_path( __FILE__ ) ) . '/classes/class-meeting-truth-event-set.php' );

if (  ! class_exists( 'Meeting_Truth_Event_Details_Page' ) ) {
	class Meeting_Truth_Event_Details_Page {
		private $service = null;
		
		private $pay_location = 'onmt';
		private $event_id = 0;
		private $mturl = '';
		private $url = '';
		private $html = '';
		
		
		public function Meeting_Truth_Event_Details_Page() {
			$this->mturl = 'http://www.meetingtruth.com';
			$the_id = get_the_ID();
			
			if( is_page( $the_id ) ) {
				$this->url = get_page_link( $the_id );
			} else {
				$this->url = get_permalink( $the_id );
			}
			
			$this->pay_location = get_option( 'rnmmt_rdoPayment' );
			
			$this->service = Meeting_Truth_Service::get_instance();
		}
		
		public function page_load( $request ) {
			if ( isset( $request['mteid'] ) ) {
				$this->event_id = $request['mteid'];
				$this->show_event_details();
			} 
		}
		
		private function show_event_details() {
			$event = $this->service->get_event_details( $this->event_id );
			$event_html = $this->get_event_html( $event );
			$this->html = $event_html;
		}
		
		private function get_event_html( $event ) {
			if($this->pay_location != 'onmt') {
				$requires_online_payment = false;
	
				switch( $event->TicketType ) {
					case 'PayOnTheDoor':
						break;
					case 'PayOnTheDoorDeposit':
						$requires_online_payment = true;
						break;
					case 'DonationOnTheDoor':
						break;
					case 'PayTeacherDirect':
						break;
					case 'Free':
						break;
					case 'ListingOnly':
						break;
					default: 
						$requires_online_payment = true;
					break;
				}

				if(!$requires_online_payment) {
					$this->pay_location = 'onmt';
				}
			}

			$HTML = "<input type='hidden' id='pay_location' value='{$this->pay_location}' />";
			$HTML .= '<div class="back"><a href="javascript:history.back()">< Back to Events</a></div>';
			$HTML .= '<div class="event">';
			if ( $event->MainImage > "" ) {
				$HTML .= '<img class="mainimage" itemprop="image" src="http://meetingtruth.com' . $event->MainImage . '" alt="' . $event->Name . '" />';
			}
			$HTML .= '<span itemprop="performer" itemscope itemtype="http://schema.org/Person"><h2 itemprop="name">' . $event->TeacherName . '</h2></span>';
			$HTML .= '<h3 itemprop="name">' . $event->Name . '</h3><div class="text">';
			
			$HTML .= "<meta itemprop='startdate' content='{$event->StartDate}'/>";
			$HTML .= "<meta itemprop='enddate' content='{$event->EndDate}'/>";
			
			$HTML .= '<p class="when">' . $event->DateString . '<br />';
			$HTML .= '<span class="venue">' . $event->LocationName . '</span></p></div></div>';
			
			$HTML .= '<div class="description"><h2 class="title">Event Information</h2><div itemprop="description">' . $event->Description . '</div></div>';
			$HTML .= '<div class="organiser"><h5>Organiser&apos;s Details</h5><p>' . $event->Organiser . '</p></div>';

			if ( $event->Address > "" ) {
				$HTML .= '<div class="location"><h5>Address</h5><p>' . $event->Address . '</p></div>';
			}
			
			$HTML .= '<div itemprop="location" itemscope itemtype="http://schema.org/Place">';
			$HTML .= "<input type='hidden' id='rnmmt_housename' value='{$event->HouseName}' />";
			$HTML .= '<div itemprop="geo" itemscope itemtype="http://schema.org/GeoCoordinates">';
			$HTML .= "<input type='hidden' id='rnmmt_latitude' itemprop='latitude' content='{$event->Latitude}' value='{$event->Latitude}' />";
			$HTML .= "<input type='hidden' id='rnmmt_longitude' itemprop='longitude' content='{$event->Longitude}' value='{$event->Longitude}' />";
			$HTML .= '</div>';
			$HTML .= '</div>';
			
			if( $event->ShowMap ) {
				$HTML .= "<div class='map'>{$event->Map}</div>";
			} else {
				$HTML .= '<div class="mapfill"></div>';
			}
			
			//Display booking options
			if ( count( $event->Tickets ) > 0 && $event->EndDateRaw < date("Y-m-d H:i:s") ) {
				$HTML .= '<div class="tickets"><h4>Booking</h4>'; 
				if( $event->TicketType != 'Free' && $event->TicketType != 'DonationOnTheDoor' ) {
					$HTML .= '<p>All prices for this event are in <strong>' . $event->Currency . '</strong></p>';
				}
				$HTML .= $this->get_event_tickets_html( $event );
				$HTML .= '</div>';
			}
			
			return $HTML;
		}
		
		private function get_event_tickets_html ( $event ) {
			$HTML = '<table class="tickets">';
			$requires_online_payment = false;
			
			switch( $event->TicketType ) {
				case 'PayOnTheDoor':
					$HTML .= $this->get_payondoor_ticket_html( $event );
					break;
				case 'PayOnTheDoorDeposit':
					$HTML .= $this->get_payondoordeposit_ticket_html( $event );
					$requires_online_payment = true;
					break;
				case 'DonationOnTheDoor':
					$HTML .= $this->get_donationondoor_ticket_html( $event );
					break;
				case 'PayTeacherDirect':
					$HTML .= $this->get_donationondoor_ticket_html( $event );
					break;
				case 'Free':
					$HTML .= $this->get_free_ticket_html( $event );
					break;
				case 'ListingOnly':
					$HTML .= $this->get_free_ticket_html( $event );
					break;
				case 'OnlineDonation':
					$HTML .= $this->get_donationonline_ticket_html( $event );
					$requires_online_payment = true;
					break;
				default: 
					$HTML .= $this->get_payonline_ticket_html( $event );
					$requires_online_payment = true;
					break;
			}
			
			
			
			if( $event->TicketType == 'PayOnline' && $event->HasDiscountCodes ) {
				$HTML .= '<tr><td colspan="4" class="rnmmt_vouchercell">';
				$HTML .= '<label>Gift voucher</label>';
				$HTML .= '<input type="text" id="txtVoucherCode" name="txtVoucherCode">';
				$HTML .= '<a id="rnmmt_btnCheckVoucher" class="btnmain" href="">Check Voucher</a>';
				$HTML .= '</td></tr>';
			}
			
			$HTML .= '<tr><td colspan="4" style="text-align: right;" class="buyCell">';
			$HTML .= '<div class="rnmmt_packlock"></div><a id="rnmmt_btnBuy" data-id="' . $event->UniqueId . '" class="btnmain" href="">Book Now</a>';
			$HTML .= '</td></tr>';
			
			if( $requires_online_payment )
			{
				$HTML .= '<tr><td colspan="2" id="card-images">';
				$HTML .= '<img src="' . plugins_url( '/img/visa.png', dirname( __FILE__ ) )  . '" alt="Visa" title="Visa">';
				$HTML .= '<img src="' . plugins_url( '/img/visa-electron.png', dirname( __FILE__ ) ) . '" alt="Visa" title="Visa">';
				$HTML .= '<img src="' . plugins_url( '/img/maestro.png', dirname( __FILE__ ) ) . '" alt="Maestro" title="Maestro">';
				$HTML .= '<img src="' . plugins_url( '/img/mastercard.png', dirname( __FILE__ ) ) . '" alt="Mastercard" title="Mastercard">';
				$HTML .= '<img src="' . plugins_url( '/img/solo.png', dirname( __FILE__ ) ) . '" alt="Solo" title="Solo">';
				$HTML .= '<img src="' . plugins_url( '/img/american-express.png', dirname( __FILE__ ) ) . '" alt="American Express" title="American Express">';
				$HTML .= '<img src="' . plugins_url( '/img/paypal.png', dirname( __FILE__ ) ) . '" alt="PayPal" title="PayPal">';
				$HTML .= '</td><td /><td /></tr>';
			}
			
			if( $this->pay_location == 'onmt' || !$requires_online_payment ) {
				$HTML .= '<tr><td style="text-align: right;" colspan="4">You will be redirected to Meeting Truth to finish booking</td></tr>';
			}
			
			$HTML .= '</tbody></table>';
			
			return $HTML;
		}
		
		private function get_payonline_ticket_html( $event ) {
			$returnStr = '<thead><tr><th style="width: 50%">Ticket Type</th><th style="width: 12.5%">Price</th><th style="width: 12.5%" class="depositColumn">Deposit</th><th style="width: 12.5%">Quantity</th></tr></thead><tbody>';
			foreach ( $event->Tickets as $ticket ){
				$returnStr .= $this->get_paying_ticket_row_html( $event, $ticket );
			}
			return $returnStr;
		}
		
		private function get_payondoor_ticket_html( $event ) {
			$returnStr = '<thead><tr><th style="width: 50%">Ticket Type</th><th style="width: 12.5%">Door Price</th><th style="width: 12.5%" class="depositColumn">Deposit</th><th style="width: 12.5%">Quantity</th></tr></thead><tbody>';
			foreach ($event->Tickets as $ticket){
				$returnStr .= $this->get_paying_ticket_row_html( $event, $ticket );
			}
			return $returnStr;
		}
		
		private function get_payondoordeposit_ticket_html( $event ) {
			$returnStr = '<thead><tr><th style="width: 50%">Ticket Type</th><th style="width: 12.5%">Door Price</th><th style="width: 12.5%" class="depositColumn">Deposit</th><th style="width: 12.5%">Quantity</th></tr></thead><tbody>';
			foreach ($event->Tickets as $ticket){
				$returnStr .= $this->get_paying_ticket_row_html( $event, $ticket );
			}
			return $returnStr;
		}

		private function get_donationonline_ticket_html( $event ) {
			$returnStr = '<thead><tr><th style="width: 50%" colspan="2">Ticket Type</th><th style="width: 12.5%">Donation Per Ticket</th><th style="width: 12.5%">Quantity</th></tr></thead><tbody>';
			foreach ( $event->Tickets as $ticket ){
				$returnStr .= $this->get_donation_ticket_row_html( $event, $ticket );
			}
			return $returnStr;
		}
		
		private function get_donationondoor_ticket_html( $event ) {
			$maximumTicketQuantity = 10;
			$returnStr = '<thead><tr><th style="width: 50%">Ticket Type</th><th style="width: 12.5%">Price</th><th style="width: 12.5%" class="feeColumn">Fee</th><th style="width: 12.5%">Quantity</th></tr></thead><tbody>';
			
			foreach ($event->Tickets as $ticket){
				$summary = '<strong itemprop="name">' . $ticket->name . '</strong>';
				if($ticket->summary) {
					$summary .= '<br />' . $ticket->summary;
				}
				
				$returnStr .= '<tr itemprop="offers" itemscope itemtype="http://schema.org/Offer"><td>' . $summary . '</td>';
				$returnStr .= '<td itemprop="price">DONATION</td>';
				$returnStr .= '<td>' . $event->CurrencyHTML . $ticket->fee . '</td><td>';

				$qtySelectId = 'rnmmt_qtySelect' . $ticket->id;
				$qtySelectAttrs = array('id' => $qtySelectId, 'data-id' => $ticket->id, 'data-name' => $ticket->name );
				$qtySelectMax = min($ticket->noAvailable, $maximumTicketQuantity);
				$qtySelectHtml = $this->get_quantity_select_html($qtySelectAttrs, 0, $qtySelectMax, $ticket->multiplesOf);
				$returnStr .= $qtySelectHtml . '</td></tr>';
			}

			return $returnStr;
		}
		
		private function get_free_ticket_html( $event ) {
			$maximumTicketQuantity = 10;
			$returnStr = '<thead><tr><th style="width: 50%" colspan="2">Ticket Type</th><th style="width: 12.5%">Price</th><th style="width: 12.5%">Quantity</th></tr></thead><tbody>';
			foreach ($event->Tickets as $ticket){   
				// The ticket is cloned just in case the amount isn't actually "free" (no idea why this would be)
				$freeTicket = clone $ticket;
				$freeTicket->price = 0;
				$returnStr .= $this->get_paying_ticket_row_html( $event, $freeTicket, 'FREE', false );
			}

			return $returnStr;
		}
		
		private function get_payteacherdirect_ticket_html( $event ) {
			$maximumTicketQuantity = 10;
			$returnStr = '<thead><tr><th style="width: 50%">Ticket Type</th><th style="width: 12.5%">&nbsp;</th><th style="width: 12.5%" class="feeColumn">Fee</th><th style="width: 12.5%">Quantity</th></tr></thead><tbody>';
			foreach ($event->Tickets as $ticket){  
				$summary = '<strong itemprop="name">' . $ticket->name . '</strong>';
				if($event->summary) {
					$summary .= '<br />' . $event->summary;
				}
				
				$returnStr .= '<tr itemprop="offers" itemscope itemtype="http://schema.org/Offer"><td>' . $summary. '</td>';
				$returnStr .= '<td>Pay Organiser Direct</td>';
				$returnStr .= '<td>' . $event->currencyHtml . $ticket->fee . '</td><td>';
				
				if($ticket->noAvailable > 0) {
					echo $ticket->noAvailable;
					$qtySelectId = 'rnmmt_qtySelect' . $ticket->id;
					$qtySelectAttrs = array('id' => $qtySelectId, 'data-id' => $ticket->id, 'data-name' => $ticket->name);
					$qtySelectMax = min($ticket->noAvailable, $maximumTicketQuantity);
					$qtySelectHtml = $this->get_quantity_select_html($qtySelectAttrs, 0, $qtySelectMax, $ticket->multiplesOf);
					$returnStr .= $qtySelectHtml;
				} else {
					$returnStr .= "Sold out";
				}
				
				$returnStr .= '</td></tr>';
			}

			return $returnStr;
		}
		
		private function get_paying_ticket_row_html( $event, $ticket, $free_text = 'FREE', $show_deposit = true ) {
			$maximumTicketQuantity = 10;
			$HTML = "";
			$depositAmount = '-';
			$summary_colspan = 1;
			
			if( ! $show_deposit ) {
				$summary_colspan = 2;
			}
				
			$priceAmount = $free_text;
			$summary = '<strong itemprop="name">' . $ticket->name . '</strong>';
			
			if($ticket->summary) {
				$summary .= '<br />' . $ticket->summary;
			}
			
			if ($ticket->deposit > 0) {
				$depositAmount = $event->CurrencyHTML . $ticket->deposit;//.toFixed(2);
			}
			
			if ($ticket->price > 0) {
				$priceAmount = $event->CurrencyHTML . $ticket->price;//.toFixed(2);
			} 
			
			$HTML .= "<tr itemprop='offers' itemscope itemtype='http://schema.org/Offer'><td colspan='{$summary_colspan}'>{$summary}</td>";
			$HTML .= "<td itemprop='price'>{$priceAmount}</td>";
			
			if($show_deposit) {
				$HTML .= '<td>' . $depositAmount . '</td>';
			}
			
			$HTML .= '<td>';

			if($ticket->noAvailable > 0) {
				$qtySelectId = 'rnmmt_qtySelect' . $ticket->id;
                $hasDeposit = $ticket->deposit > 0 && $show_deposit;
                $qtySelectAttrs = array('id' => $qtySelectId, 'data-id' => $ticket->id, 'data-name' => $ticket->name, 'data-hasdeposit' => $hasDeposit );
				$qtySelectMax = min( $ticket->noAvailable, $maximumTicketQuantity );
				$qtySelectHtml = $this->get_quantity_select_html( $qtySelectAttrs, 0, $qtySelectMax, $ticket->multiplesOf );
				$HTML .= $qtySelectHtml;
			} else {
				$HTML .= "Sold out";
				$HTML .= '<meta itemprop="availability" content="http://schema.org/SoldOut"';
			}
			
			$HTML .= '</td></tr>';
			
			return $HTML;
		}

        
		private function get_donation_ticket_row_html( $event, $ticket ) {
			$maximumTicketQuantity = 10;
			$HTML = "";
			$summary_colspan = 2;
				
			$summary = '<strong itemprop="name">' . $ticket->name . '</strong>';
			
			if($ticket->summary) {
				$summary .= '<br />' . $ticket->summary;
			}
						
			$HTML .= "<tr itemprop='offers' itemscope itemtype='http://schema.org/Offer'><td colspan='{$summary_colspan}'>{$summary}</td>";
			$HTML .= "<td>";
            
			if($ticket->noAvailable > 0) {
			    $HTML .= "<input type='text' id='rnmmt_donationAmount{$ticket->id}' data-id='{$ticket->id}' />";
			} else {
				$HTML .= "-";
			}
			
			$HTML .= '</td>';
			$HTML .= '<td>';

			if($ticket->noAvailable > 0) {
				$qtySelectId = 'rnmmt_qtySelect' . $ticket->id;
                $qtySelectAttrs = array('id' => $qtySelectId, 'data-id' => $ticket->id, 'data-name' => $ticket->name );
				$qtySelectMax = min( $ticket->noAvailable, $maximumTicketQuantity );
				$qtySelectHtml = $this->get_quantity_select_html( $qtySelectAttrs, 0, $qtySelectMax, $ticket->multiplesOf );
				$HTML .= $qtySelectHtml;
			} else {
				$HTML .= "Sold out";
				$HTML .= '<meta itemprop="availability" content="http://schema.org/SoldOut"';
			}
			
			$HTML .= '</td></tr>';
			
			return $HTML;
		}
		
		private function get_quantity_select_html( $attributes, $start, $end, $multiples_of = 1 ) {
			$html = "<select ";
			
			foreach($attributes as $key => $value) {
				$html .= "{$key}='{$value}' ";
			}
			$html .= ">";
			
			for($i = $start; $i <= $end; $i += $multiples_of) {
				$html .= "<option value='{$i}'>{$i}</option>";
			}
			
			$html .= '</select>';
			return $html;
		}
		
		public function get_html() {
			
			$html = "<form method='post' action='{$this->url}' id='rnmmt_form'>";
			$html .= "<div id='rnmmt_event_details' itemscope itemtype='http://schema.org/Event'>{$this->html}</div>";
			$html .= '</form><div id="rnmmt_content"></div>';
			
			return $html;
		}
	}
}

?>
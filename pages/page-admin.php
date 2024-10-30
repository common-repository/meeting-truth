<?php

if (  ! class_exists( 'Meeting_Truth_Event_Admin_Page' ) ) {
	class Meeting_Truth_Event_Admin_Page {
		private $mturl = '';
		private $url = '';
		private $html = '';
		
		private $payment = '';
		
		private $settings = array();
		private $setting_defaults = array();
		private $setting_values = array();
		
		public function Meeting_Truth_Event_Admin_Page() {
			$this->mturl = 'http://www.meetingtruth.com';
			$this->url = str_replace( '%7E', '~', $_SERVER['REQUEST_URI']);
			
			$this->settings = array( 
				'rnmmt_rdoPayment' => 'radio',
				'rnmmt_poweredby_link' => 'checkbox',

				'rnmmt_events_details_layout' => 'select',
				'rnmmt_events_details_layout_show_teacher' => 'checkbox',

				'rnmmt_events_style_highlight' => 'hex',
				'rnmmt_events_style_dark_colour' => 'hex',
				'rnmmt_events_style_med_colour' => 'hex',
				'rnmmt_events_style_light_colour' => 'hex',
				
				'rnmmt_events_css' => 'textarea',
			);
			
			$this->setting_defaults = array( 
				'rnmmt_rdoPayment' => 'onmt',
				'rnmmt_poweredby_link' => 'false',
				
				'rnmmt_events_details_layout' => 'all-to-right',
				'rnmmt_events_details_layout_show_teacher' => 'true',
				
				'rnmmt_events_style_highlight' => 'ff6600',
				'rnmmt_events_style_dark_colour' => 'cccccc',
				'rnmmt_events_style_med_colour' => 'efefef',
				'rnmmt_events_style_light_colour' => 'ffffff',
				
				'rnmmt_events_css' => '',
			);
		}
		
		public function page_load( $request ) {
			if( isset( $request['rnmmt_hdn'] ) ) {
				foreach( $this->settings as $setting_name => $setting_type) {
					$value = '';
					
					// TODO: Use the type to do some validation
					switch( $setting_type ) {
						case 'checkbox':
							if( isset( $request[ $setting_name ] ) ) {
								$value = 'true';
							} else {
								$value = 'false';
							}
							break;
						case 'hex':
						case 'number':
						case 'select':
						case 'radio':
						case 'text':
						case 'textarea':
						default:
							$value = $request[ $setting_name ];
							break;
					}
					
					$this->setting_values[ $setting_name ] = $value;
					update_option( $setting_name, $value );
				}
			} else {
				foreach( $this->settings as $setting_name => $setting_type) {
					$value = get_option( $setting_name );
					if($value == '') {
						$value = $this->setting_defaults[ $setting_name ];
					}
					$this->setting_values[ $setting_name ] = $value;
				}
			}
		}
		
		private function selected_if( $item_value, $current_value ) {
			if( $item_value === $current_value ) {
				return 'selected="selected"';
			} else {
				return '';
			}
		}
		
		private function checked_if( $item_value, $current_value ) {
			if( $item_value === $current_value ) {
				return 'checked="checked"';
			} else {
				return '';
			}
		}
		
		public function render_html() { 
			$on_site_checked = '';
			$on_mt_checked = '';
			
			if( $this->setting_values['rnmmt_rdoPayment'] == 'onsite' ) {
				$on_site_checked = 'checked="checked"';
			} else {
				$on_mt_checked = 'checked="checked"';
			}
			
			?>
			<div id="rnmmt_plugin_settings" class="wrap">
				<h2>Meeting Truth Plugin Settings</h2>
				<form name="rnmmp_form" method="post" action="<?php echo $this->url; ?>">
					<input type="hidden" name="rnmmt_hdn" value="y" />
					<table class="form-table">
						<tbody>
							<tr>
								<th scope="row">
									Payment location
								</th>
								<td>
									<fieldset>
										<legend class="screen-reader-text">
											<span>Payment location</span>
										</legend>
										<label for="rnmmt_rdoPaymentOnSite">
											<input  type="radio" name="rnmmt_rdoPayment"  id="rnmmt_rdoPaymentOnSite" value="onsite" <?php echo $on_site_checked; ?>/>
											Via PayPal on-site
										</label>
										<br />
										<label for="rnmtm_rdoPaymentOnMT">
											<input type="radio" name="rnmmt_rdoPayment" id="rnmmt_rdoPaymentOnMT" value="onmt" <?php echo $on_mt_checked; ?>/>
											Go to Meeting Truth
										</label>
										<br />
										<p class="description">
											(This will not affect free events.)
										</p>
									</fieldset>
								</td>
							</tr>
							<tr>
								<th scope="row">
									Meeting Truth links
								</th>
								<td>
									<input type="checkbox" name="rnmmt_poweredby_link" id="rnmmt_poweredby_link" <?php echo $this->checked_if('true', $this->setting_values['rnmmt_poweredby_link']); ?> />
									<label for="rnmmt_poweredby_link">Link "powered by" banners to meetingtruth.com</label>
								</td>
							</tr>
						</tbody>
					</table>
					<h3>Styles</h3>
					<table class="form-table rnmmt_styles">
						<tbody>
							<tr>
								<th scope="row">
									<label for="rnmmt_events_style_highlight">Highlight Colour</label>
								</th>
								<td class='rnmmt_admin_colour_row'>
									<div class='rnmmt_colour_thumb'></div>
									<input type="text" name="rnmmt_events_style_highlight" id="rnmmt_events_style_highlight" value="<?php echo $this->setting_values['rnmmt_events_style_highlight']; ?>" />
								</td>
							</tr>
							<tr>
								<th scope="row">
									<label for="rnmmt_events_style_dark_colour">Dark Colour</label>
								</th>
								<td class='rnmmt_admin_colour_row'>
									<div class='rnmmt_colour_thumb'></div>
									<input type="text" name="rnmmt_events_style_dark_colour" id="rnmmt_events_style_dark_colour" value="<?php echo $this->setting_values['rnmmt_events_style_dark_colour']; ?>" />
								</td>
							</tr>
							<tr>
								<th scope="row">
									<label for="rnmmt_events_style_med_colour">Medium Colour</label>
								</th>
								<td class='rnmmt_admin_colour_row'>
									<div class='rnmmt_colour_thumb'></div>
									<input type="text" name="rnmmt_events_style_med_colour" id="rnmmt_events_style_med_colour" value="<?php echo $this->setting_values['rnmmt_events_style_med_colour']; ?>" />
								</td>
							</tr>
							<tr>
								<th scope="row">
									<label for="rnmmt_events_style_light_colour">Light Colour</label>
								</th>
								<td class='rnmmt_admin_colour_row'>
									<div class='rnmmt_colour_thumb'></div>
									<input type="text" name="rnmmt_events_style_light_colour" id="rnmmt_events_style_light_colour" value="<?php echo $this->setting_values['rnmmt_events_style_light_colour']; ?>" />
								</td>
							</tr>
							<tr>
								<th scope="row">Event Details Layout</th>
								<td>
									<fieldset>
										<legend class="screen-reader-text">
											<span>Event Table</span>
										</legend>
										<ul>
											<li>
												<label for="rnmmt_events_details_layout">General Layout</label>
												<select name="rnmmt_events_details_layout" id="rnmmt_events_details_layout">
													<option value="all-to-right" <?php echo $this->selected_if('all-to-right', $this->setting_values['rnmmt_events_details_layout']); ?>>Details on right</option>
													<option value="all-at-bottom" <?php echo $this->selected_if('all-at-bottom', $this->setting_values['rnmmt_events_details_layout']); ?>>Details at bottom</option>
												</select>
											</li>
											<li>
												<input type="checkbox" name="rnmmt_events_details_layout_show_teacher" id="rnmmt_events_details_layout_show_teacher" <?php echo $this->checked_if('true', $this->setting_values['rnmmt_events_details_layout_show_teacher']); ?> />
												<label for="rnmmt_events_details_layout_show_teacher">Display Teacher Name</label>
											</li>
										</ul>
									</fieldset>
								</td>
							</tr>
							<tr>
								<th scope="row">
									<label for="rnmmt_events_css">Custom CSS</label>
								</th>
								<td>
									<textarea name='rnmmt_events_css' id='rnmmt_events_css'><?php echo $this->setting_values['rnmmt_events_css']; ?></textarea>
								</td>
							</tr>
						</tbody>
					</table>
					<p class="submit">
						<input type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes" />
					</p>
				</form>
			</div>
			<?php
		}
	}
	
	$mtle = new Meeting_Truth_Event_Admin_Page();
	$mtle->page_load( $_REQUEST );
	$mtle->render_html();

}

?>
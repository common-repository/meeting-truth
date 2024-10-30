<?php
header('Content-type: text/css');

require(  dirname( dirname( dirname( dirname( dirname( __FILE__ ) ) ) ) ) . '/wp-config.php');

$rnmmt_events_style_highlight = get_option( 'rnmmt_events_style_highlight' );
$rnmmt_events_style_dark_colour = get_option( 'rnmmt_events_style_dark_colour' );
$rnmmt_events_style_med_colour = get_option( 'rnmmt_events_style_med_colour' );
$rnmmt_events_style_light_colour = get_option( 'rnmmt_events_style_light_colour' );
$rnmmt_events_css = get_option( 'rnmmt_events_css' );

if( strstr( $rnmmt_events_style_highlight, '#' ) == FALSE ) {
	$rnmmt_events_style_highlight = '#' . $rnmmt_events_style_highlight;
}
if( strstr( $rnmmt_events_style_dark_colour, '#' ) == FALSE ) {
	$rnmmt_events_style_dark_colour = '#' . $rnmmt_events_style_dark_colour;
}
if( strstr( $rnmmt_events_style_med_colour, '#' ) == FALSE ) {
	$rnmmt_events_style_med_colour = '#' . $rnmmt_events_style_med_colour;
}
if( strstr( $rnmmt_events_style_light_colour, '#' ) == FALSE ) {
	$rnmmt_events_style_light_colour = '#' . $rnmmt_events_style_light_colour;
}

$rnmmt_events_style_highlight_text_colour = '#ffffff';
$rnmmt_events_style_text_colour = '#000000';


$rnmmt_events_details_layout = get_option( 'rnmmt_events_details_layout' );
$rnmmt_events_details_layout_show_teacher = get_option( 'rnmmt_events_details_layout_show_teacher' );


$rnmmt_padlockUrl = plugins_url( '/img/padlock.gif', dirname( __FILE__ ) );

?>

/* dynamic css */
.rnmmt_packlock {
	width:16px;
	height:25px;
	background-image: url("<?php echo $rnmmt_padlockUrl; ?>");
	background-repeat: no-repeat;
	display:inline-block;
	vertical-align:text-bottom;
}

#meeting_truth_events .tickets h4 {
	
}

#meeting_truth_events .poweredby {
	margin: 1em 0;
}

#meeting_truth_events img {
	border-radius: 0;
	box-shadow: none;
	
	vertical-align:text-bottom;
}

#meeting_truth_events .highlight_title {
	background-color: <?php echo $rnmmt_events_style_highlight; ?>;
	color: <?php echo $rnmmt_events_style_highlight_text_colour; ?>;
	font-size: 1.2em;

	padding:0.8em;
	margin-bottom: 0.5em;
	font-weight: normal;
}

#meeting_truth_events .btnmain {
	background-color: <?php echo $rnmmt_events_style_highlight; ?>;
	color: <?php echo $rnmmt_events_style_highlight_text_colour; ?>;

	cursor: pointer;
	font-weight: bold;
	text-decoration:none;
	padding: 0 0.5em;
	height: 2em;
	line-height: 2em;
	display:inline-block;
	font-size: 15px;
	margin-left: 0.5em;
}


#meeting_truth_events table,
#meeting_truth_events table tr,
#meeting_truth_events table th,
#meeting_truth_events table td {
	border: 0;
}

#meeting_truth_events .buttons {
	display: inline-block;
	float: right;
	font-size: 0.9em;
}

#meeting_truth_events .formtable th {
	color: <?php echo $rnmmt_events_style_text_colour; ?>;
	text-align:right;
	font-weight: normal;
	width: 10em;
	padding: 0.5em;
}
#meeting_truth_events .formtable td {
	color: <?php echo $rnmmt_events_style_text_colour; ?>;
	padding: 0.5em;
}

#meeting_truth_events label {
	color: <?php echo $rnmmt_events_style_text_colour; ?>;
}

#meeting_truth_events #card-images img {
	width:14.2%;
	max-width:51px;
}

#meeting_truth_events .buyCell {
	min-width:10.9em;
}

#rnmmt_event_list .pager {
	color: <?php echo $rnmmt_events_style_highlight_colour; ?>;
	margin: 0.1em 0;
}

#rnmmt_event_list .pager span {
	margin: 0 0.2em;
}

#rnmmt_event_list .pager a {
	color: <?php echo $rnmmt_events_style_highlight; ?>;

}

#rnmmt_event_list .pager a:hover {
	color: <?php echo $rnmmt_events_style_text_colour; ?>
}


#rnmmt_event_list .eventListing a {
	color: <?php echo $rnmmt_events_style_highlight; ?>;
}

#rnmmt_event_list .eventListing a:hover {
	color: <?php echo $rnmmt_events_style_text_colour; ?>;
}


#rnmmt_event_list table.eventListing {
	border: solid 1px <?php echo $rnmmt_events_style_dark_colour; ?>;
}
#rnmmt_event_list table.eventListing td,
#rnmmt_event_list table.eventListing th {
	padding: 1em;
	border:0;
}

#rnmmt_event_list table.eventListing thead tr {
	background: <?php echo $rnmmt_events_style_dark_colour; ?>;
}

#rnmmt_event_list table.eventListing thead td,
#rnmmt_event_list table.eventListing thead th {
	color: <?php echo $rnmmt_events_style_text_colour; ?>;
	font-size: 1em;

	font-weight: normal;
}


#rnmmt_event_list table.eventListing tbody tr {
	background: <?php echo $rnmmt_events_style_med_colour; ?>;
}

#rnmmt_event_list table.eventListing tbody tr:nth-child(even) {
	background: <?php echo $rnmmt_events_style_light_colour; ?>;
}

#rnmmt_event_list table.eventListing tbody th,
#rnmmt_event_list table.eventListing tbody td {
	color: <?php echo $rnmmt_events_style_text_colour; ?>;
	font-size: 1em;

	font-weight: normal;
}

#rnmmt_event_list div.eventListing h4 {
	margin-top: 1em;
	margin-bottom: 0;
}

#rnmmt_event_list div.eventListing div.rnmmt_event {
	background: <?php echo $rnmmt_events_style_med_colour; ?>;
	margin-bottom: 1em;
	padding: 1em;
}

#rnmmt_event_list div.eventListing div.rnmmt_event .rnmmt_name {
	font-weight: bold;
	margin-top: 0;
	margin-bottom: 0;
}

#rnmmt_event_list div.eventListing div.rnmmt_event .rnmmt_teacher {
	font-weight: bold;
	font-size: 1.2em;
	margin-top: 0;
	margin-bottom: 0;
}

#rnmmt_event_list div.eventListing div.rnmmt_event .rnmmt_start_date {
	color: <?php echo $rnmmt_events_style_highlight; ?>;
	font-weight: bold;
	margin: 0.5em 0;
}

#rnmmt_event_list div.eventListing div.rnmmt_event .rnmmt_location {
	font-weight: bold;
	margin: 0.5em 0;
}

#rnmmt_event_list div.eventListing div.rnmmt_event .rnmmt_summary p {
	margin-bottom: 0.5em;
}

#rnmmt_event_list div.eventListing div.rnmmt_event a {
	font-weight: bold;
	display: block;
}


#rnmmt_event_details .event {
	background: <?php echo $rnmmt_events_style_med_colour; ?>;
	color: <?php echo $rnmmt_events_style_text_colour; ?>;
	font-size: 1em;

	display: inline-block;	
	box-sizing: border-box;
	width:100%;
	margin-bottom: 2%;
	padding: 1em;
}

#rnmmt_event_details .event h2 {
	margin:0;
	float:left;
	clear:left;
}

#rnmmt_event_details .event h3 {
	color: <?php echo $rnmmt_events_style_highlight; ?>;
	font-size: 1.7em;
	margin: 0;
	font-weight: normal;
	line-height:1.5em;
	float:left;
	clear:left;
	width:63%;
}

#rnmmt_event_details .text {
	float:left;
}

#rnmmt_event_details .event p.when {
	margin-bottom: 1em;
}

#rnmmt_event_details .organiser,
#rnmmt_event_details .location,
.mapfill{
	background-color: <?php echo $rnmmt_events_style_med_colour; ?>;
	color: <?php echo $rnmmt_events_style_text_colour; ?>;
	font-size: 1em;
	
	box-sizing: border-box;
	padding:1em;
	margin-top: 0.5em;
	margin-bottom: 0.5em;
	clear: right;
}

#rnmmt_event_details .map {
	box-sizing: border-box;
	margin-top: 0.5em;
	margin-bottom: 0.5em;
	clear: right;
}

#rnmmt_event_details .organiser h5,
#rnmmt_event_details .location h5 { 
	color: <?php echo $rnmmt_events_style_text_colour; ?>;
	font-size: 1em;
	margin: 0;
}

#rnmmt_event_details .organiser p,
#rnmmt_event_details .location p {
	margin: 0;
	word-wrap: break-word;
}

#rnmmt_event_details .description {
	box-sizing: border-box;
	padding-right: 1em;
}

#rnmmt_event_details .description .title {
	margin-top: 0;
}

#rnmmt_event_details .tickets {
	clear: both;
}

#rnmmt_event_details .tickets h4 {
	background-color: <?php echo $rnmmt_events_style_highlight; ?>;
	color: <?php echo $rnmmt_events_style_highlight_text_colour; ?>;
	font-size: 1.4em;

	padding-left: 1em;
	margin-bottom: 0.5em;
	font-weight: normal;
}

#rnmmt_event_details .tickets table {
	border: solid 1px <?php echo $rnmmt_events_style_dark_colour; ?>;
}

#rnmmt_event_details .tickets table thead tr {
	background-color: <?php echo $rnmmt_events_style_dark_colour; ?>;
	border: 0;
}

#rnmmt_event_details .tickets table thead td,
#rnmmt_event_details .tickets table thead th {
	color: <?php echo $rnmmt_events_style_text_colour; ?>;
	font-size: 1em;
	border: 0;
}

#rnmmt_event_details .tickets table tbody tr {
	background: <?php echo $rnmmt_events_style_med_colour; ?>;
}

#rnmmt_event_details .tickets table tbody tr:nth-child(even) {
	background: <?php echo $rnmmt_events_style_light_colour; ?>;
}

#rnmmt_event_details .tickets table tbody th,
#rnmmt_event_details .tickets table tbody td {
	color: <?php echo $rnmmt_events_style_text_colour; ?>;
	font-size: 1em;
	font-weight: normal;
	border: 0;
}

#rnmmt_event_details .tickets table td,
#rnmmt_event_details .tickets table th {
	padding: 1em;
}

#rnmmt_event_details .map #map_container {
	width: 100%;
	height: 100%;
}


.rnmmt_dialogbox {
	position: fixed;
	top: 40%;
	left: 40%;

	display: none;

	background-color: #000;
	background-color: rgba(0,0,0,.3);
	-webkit-border-top-right-radius: 15px;
	-webkit-border-bottom-right-radius: 15px;
	-webkit-border-bottom-left-radius: 15px;
	-webkit-border-top-left-radius: 15px;
	-moz-border-radius-topright: 15px;
	-moz-border-radius-bottomright: 15px;
	-moz-border-radius-bottomleft: 15px;
	-moz-border-radius-topleft: 15px;
	border-top-right-radius: 15px;
	border-bottom-right-radius: 15px;
	border-bottom-left-radius: 15px;
	border-top-left-radius: 15px;
	-moz-background-clip: padding;
	-webkit-background-clip: padding-box;
	background-clip: padding-box;
	padding: 10px;
	margin: 0;
	max-width: 800px;
	width: auto;
	min-width: 300px;
}

.rnmmt_dialogbox div {
	overflow: hidden;
	padding: 5px;
	padding: 1em;	
	text-align: center;

	background-color: #fff;
	-webkit-border-top-right-radius: 5px;
	-webkit-border-bottom-right-radius: 5px;
	-webkit-border-bottom-left-radius: 5px;
	-webkit-border-top-left-radius: 5px;
	-moz-border-radius-topright: 5px;
	-moz-border-radius-bottomright: 5px;
	-moz-border-radius-bottomleft: 5px;
	-moz-border-radius-topleft: 5px;
	border-top-right-radius: 5px;
	border-bottom-right-radius: 5px;
	border-bottom-left-radius: 5px;
	border-top-left-radius: 5px;
	-moz-background-clip: padding;
	-webkit-background-clip: padding-box;
	background-clip: padding-box;
	margin: 0!important;
}

.rnmmt_dialogbox .mt_logo {
	margin-bottom: 1em;
}

.rnmmt_dialogbox .mt_preload {
	width:45px;
}


<?php if( $rnmmt_events_details_layout_show_teacher == 'false' ): ?>
	#rnmmt_event_details .event h2 {
		display: none;
	}
<?php endif ?>


<?php if( $rnmmt_events_details_layout == 'all-to-right' ): ?>

	#rnmmt_event_details .description {
		width: 65%;
		float: left;
	}

	#rnmmt_event_details .mainimage,
	#rnmmt_event_details .location,
	#rnmmt_event_details .organiser,
	#rnmmt_event_details .map {
		float:right;
		width:35%;
	}

	#rnmmt_event_details .map {
		height: 240px;
	}
	
	.mapfill {
		display:none;
	}

<?php else: ?>

	#rnmmt_event_details .description {
		width: 100%;
	}

	#rnmmt_event_details .mainimage {
		float:right;
		width:35%;
	}

	#rnmmt_event_details .organiser {
		width:100%;
	}

	#rnmmt_event_details .location,
	#rnmmt_event_details .map,
	.mapfill{
		float:left;
		height: 200px;
	}

	#rnmmt_event_details .location {
		width:49%;
		margin-right:1%;
	}

	#rnmmt_event_details .map, 
	.mapfill {
		width:49%;
		margin-left:1%;
	}
<?php endif ?>


#rnmmt_event_details .mainimage img {
	width: 100%;
	float: right;
}


@media all and (max-width: 512px) {
	#rnmmt_event_details .tickets table td, #rnmmt_event_details .tickets table th {
		padding: 0.5em;
	}
	
	#rnmmt_event_details .tickets table td label {
		display:block;
	}
	
	#rnmmt_event_details .tickets table td input {
	}
	
	#rnmmt_event_details .buyCell {
		margin:1em;
	}
	
	.rnmmt_vouchercell {
		text-align:center;
	}
	
	.rnmmt_vouchercell input {
		width: 90%;
	}
	
	.rnmmt_vouchercell label {
		float:left;
	}
	
	#rnmmt_event_details .buyCell {
		text-align:center!important;
	}
	
	.rnmmt_dialogbox {
		left:0;
		width:100%;
		padding-left:0;
		padding-right:0;
		border-radius:0;
	}
	
	.rnmmt_dialogbox div {
		border-radius:0;
	}
}

@media all and (max-width: 360px) {
	#rnmmt_event_details .tickets table td, #rnmmt_event_details .tickets table th {
		padding: 0.1em;
	}
	
	#rnmmt_event_details .buyCell {
		margin:0.2em;
	}
}


<?php echo $rnmmt_events_css; ?>
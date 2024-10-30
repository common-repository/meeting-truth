jQuery(function ($) {
	$(".rnmmt_admin_colour_row input").iris({
		hide:true,
		border:false,
		change: function(event, ui) {
			var $this = $(this);
			var $thumb = $this.siblings(".rnmmt_colour_thumb");
			$thumb.css("background-color", ui.color.toString());
	}
	}).on("change", function() {
		var $this = $(this);
		var $thumb = $this.siblings(".rnmmt_colour_thumb");
		$thumb.css("background-color", $this.val());
	}).change();
	
	$(".rnmmt_admin_colour_row").on("focusout", function () {
		$(this).find("input").iris('hide');
	}).on("focusin", function () {
		$(this).find("input").iris('show');
	});
	
	

	$("#rnmmt_event_filter_select input[type='radio']").change(function () {
		if (this.checked) {
			var $this = $(this);
			var dropdownId = $this.attr('data-dropdown');

			if (dropdownId === '') {
				$("#rnmmt_event_filter_select select").prop('disabled', true);
			} else {
				$("#rnmmt_event_filter_select select:not(#" + dropdownId + ")").prop('disabled', true);
				$("#" + dropdownId).prop('disabled', false);
			}
		}
	});


	$("#rnmmt_post_display").change(function () {
		var value = $(this).val();

		switch (value) {
			case 'none':
				$("#rnmmt_event_filter_select").fadeOut();
				$("#rnmmt_session_teacher_select").fadeOut();
				$("#rnmmt_event_display_select").fadeOut();
				break;
			case 'events':
				$("#rnmmt_session_teacher_select").hide();
				$("#rnmmt_event_filter_select").fadeIn();
				$("#rnmmt_event_display_select").fadeIn();
				break;
			case 'sessions':
				$("#rnmmt_event_filter_select").hide();
				$("#rnmmt_session_teacher_select").fadeIn();
				$("#rnmmt_event_display_select").hide();
				break;
			case 'both':
				$("#rnmmt_event_filter_select").hide();
				$("#rnmmt_session_teacher_select").fadeIn();
				$("#rnmmt_event_display_select").fadeIn();
				break;
		}
	}).change();
});
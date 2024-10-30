jQuery(function ($) {
	function Ticket() {
		this.Id = null;
		this.Name = null;
		this.Quantity = null;
		this.Donation = null;
	}

	function AttendeeTicket() {
		this.attendeeName = null;
		this.attendeeEmail = null;
		this.ticketId = null;
		this.attendeeTel = null;
	}

	var hasDeposit = false;
	var mtUrl = 'http://meetingtruth.com';
	//var mtUrl = 'http://localhost:55970';


	if ($("#rnmmt_event_details .map").length) {
		var latitude = $("#rnmmt_latitude").val();
		var longitude = $("#rnmmt_longitude").val();
		var housename = $("#rnmmt_housename").val();

		if (!(window.google && window.google.maps)) {
			mapLoaded = function () {
				jQuery.ajax({
					type: 'GET',
					url: meetingTruthPluginParams.pluginUrl + 'js/gmapembed.js',
					cache: true,
					dataType: 'script',
					data: null,
					success: function () {
						gMap = {
							"lat": latitude,
							"lng": longitude,
							"name": housename
						};
						loadMap();
					}
				});
			};
			jQuery.ajax({
				type: 'GET',
				url: 'http://maps.google.com/maps/api/js?sensor=false&callback=mapLoaded',
				cache: true,
				dataType: 'script',
				data: null,
				success: function () {

				}
			});
		} else if (!(window.loadMap)) {
			jQuery.ajax({
				type: 'GET',
				url: meetingTruthPluginParams.pluginUrl + 'js/gmapembed.js',
				cache: true,
				dataType: 'script',
				data: null,
				success: function () {
					gMap = {
						"lat": latitude,
						"lng": longitude,
						"name": housename
					};
					loadMap();
				}
			});
		}
	}

	$("#rnmmt_ddlTeacher").change(function () {
		$("#rnmmt_ddlCountry").val("");
		$("#rnmmt_frm").submit();
	});

	$("#rnmmt_ddlCountry").change(function () {
		$("#rnmmt_ddlTeacher").val(0);
		$("#rnmmt_frm").submit();
	});

	//Bind session book button
	$('#meeting_truth_events .btnSession').bind('click', function (e) {
		var sessionId = parseInt($(this).attr('data-id'), 10);
		var payLocation = $("#pay_location").val();

		if (payLocation == 'onmt') {
			bookSessionOnMT(sessionId);
		} else {
			bookSessionOnSite(sessionId);
		}

		e.preventDefault();
	});

	//bind buy link to web service to create order
	$('#rnmmt_btnBuy').click(function (e) {
		var $this = $(this);
		validateForm(
			function () {
				var eventId = $this.attr("data-id");
				var payLocation = $("#pay_location").val();
				var tickets = getTickets();
				var discountCode = getDiscountCode();

				//If it has a deposit, we need to go to Meeting Truth for checkout
				if (tickets.length > 0) {
					if (payLocation == 'onmt' || hasDeposit) {
						bookEventOnMT(eventId, discountCode, tickets);
					} else {
						bookEventOnSite(eventId, discountCode, tickets);
					}
				} else {
					showDialogBox('Please select the number of tickets you require');
				}
			},
			function () {
				showDialogBox('Please enter a donation amount for each ticket you wish to purchase');
			});

		e.preventDefault();
	});

	$('#rnmmt_btnCheckVoucher').click(function (e) {
		var discountCode = getDiscountCode();
		var eventId = $("#rnmmt_btnBuy").attr("data-id");

		isDiscountCodeValid(discountCode, eventId, function (isValid) {
			if (isValid) {
				showDialogBox('The gift voucher you have entered is valid.<br /> This will be applied at the checkout.');
			} else {
				showDialogBox('The gift voucher you have entered is not valid.<br /> Please ensure you have typed it correctly');
			}
		});

		e.preventDefault();
	});


	$('#rnmmt_closeDialog').click(function (e) {
		$('#rnmmt_dialogbox').hide();
		e.preventDefault();
	});

	function validateForm(successCallback, failCallback) {
		var validationResult = true;
		var remaining = $('[id^=rnmmt_donationAmount]').length;
		if (remaining > 0) {
			$('[id^=rnmmt_donationAmount]').each(function () {
				var $this = $(this);
				var id = parseInt($this.attr('data-id'), 10);
				var qty = parseInt($('#rnmmt_qtySelect' + id).val(), 10);
				if (qty > 0 && !isValidDonationEntry($this.val())) {
					done(false);
				} else {
					done(true);
				}

			});
		} else {
			done(true);
		}
		function done(isValid) {
			remaining--;
			if (!isValid) {
				validationResult = false;
			}
			if (remaining < 1) {
				if (validationResult) {
					successCallback();
				} else {
					failCallback();
				}
			}
		}
	}

	
	function isValidDonationEntry(donationEntered) {
		var donationAmount = parseFloat(donationEntered);
		if (!isNaN(donationAmount) && donationAmount >= 0) {
			return true;
		} else {
			return false;
		}
	}


	function getTickets() {
		var tickets = [];

		$('[id^=rnmmt_qtySelect]').each(function () {
			var $this = $(this);

			var qty = parseInt($this.val(), 10);

			if (qty > 0) {
				var ticket = new Ticket();

				ticket.Id = parseInt($this.attr('data-id'), 10);
				ticket.Name = $this.attr('data-name');
				ticket.Quantity = qty;

				if ($('#rnmmt_donationAmount' + ticket.Id).length > 0) {
					ticket.Donation = parseFloat($('#rnmmt_donationAmount' + ticket.Id).val());
				}

				if ($this.attr('data-hasdeposit') === '1') {
					hasDeposit = true;
				}

				tickets.push(ticket);
			}
		});

		return tickets;
	}

	function loading() {
		jQuery('#meeting-truth').addClass('loading');
		var mtheight = jQuery('#meeting-truth').outerHeight();
		jQuery('#meeting-truth #loader .innerload').height(mtheight);
		jQuery('#meeting-truth #loader .innerload img').css('margin-top', ((mtheight / 2) - 40) + 'px');
	}

	function bookEventOnSite(eventId, discountCode, tickets) {
		var html = getTicketsDetailHTML(tickets);

		$('#rnmmt_content').html(html);
		$('#rnmmt_btnBuyNow').click(function (e) {
			completeOrderOnPayPal(eventId, discountCode);
			
			e.preventDefault();
		});
	}



	function getTicketsDetailHTML(tickets) {
		var html = "";
		for (var i = 0; i < tickets.length; i++) {
			var ticket = tickets[i];
			if (ticket.Quantity > 0) {
				html += getTicketDetailHTML(ticket);
			}
		}

		var cardImages = "";

		if ($("#card-images").length > 0) {
			cardImages = $("#card-images").html();
		}

		var padlockUrl = $("img.padlock").attr('src');

		html += '<table><tr><td id="card-images">' + cardImages + '</td>';
		html += '<td colspan="2" style="text-align: right;" class="buyCell"><div class="buttons">';
		html += '<div class="rnmmt_packlock"></div><a id="rnmmt_btnBuyNow" class="btnmain" href="">Make Payment</a>';
		html += '</td></tr></table>';
		return html;
	}

	function getTicketDetailHTML(ticket) {
		var html = "<h4 class='highlight_title'>" + ticket.Name + "</h4>";

		for (var i = 0; i < ticket.Quantity; i++) {
			html += "<table class='attendee formtable'>";
			html += "<tr><td colspan=2><strong>Person " + (i + 1) + "</strong><input class='ticketid' type='hidden' id='rnmmt_hdnTicketId" + i + "' value='" + ticket.Id + "'/>";
			html += "<input type='hidden' class='donation' value='" + ticket.Donation + "'/></td></tr>";
			html += "<tr><th>Name</th><td><input class='name' type='text' id='rnmmt_txtName" + i + "'></td></tr>";
			html += "<tr><th>Email</th><td><input class='email' type='text' id='rnmmt_txtEmail" + i + "'></td></tr>";
			html += "<tr><th>Phone no.</th><td><input class='phone' type='text' id='rnmmt_txtPhone" + i + "'> (optional)</td></tr>";
			html += "</table>";
		}



		return html;
	}


	//Get ticket details and save transaction
	//Then forward to PayPal for processing
	function completeOrderOnPayPal(eventId, discountCode) {
		var attendees = getAttendees();
		var validation = validateAttendees(attendees);

		if (validation !== null) {
			showDialogBox(validation);
		} else {
			loading();
			
			//Assign first attendee details as username and password
			var username = attendees[0].attendeeName;
			var email = attendees[0].attendeeEmail;

			showRedirectDialogBox("Taking you to Pay Pal to complete your booking");

			createInlineRemoteOrder(eventId, window.location.host + " - PayPal", username, email, discountCode, attendees, function (paypalUrl) {
				window.location = paypalUrl;
			});
		}
	}

	function getAttendees() {
		var attendees = [];
		$('.attendee').each(function (index) {
			var $this = $(this);
			var attendee = new AttendeeTicket();
			attendee.attendeeName = $this.find('input.name').val();
			attendee.attendeeEmail = $this.find('input.email').val();
			attendee.ticketId = $this.find('input.ticketid').val();
			attendee.attendeeTel = $this.find('input.phone').val();
			attendee.donationAmount = $this.find('input.donation').val();
			attendees.push(attendee);
		});
		return attendees;
	}

	function validateAttendees(attendees) {
		var validation = null;

		for (var i = 0; i < attendees.length; i++) {
			var attendee = attendees[i];
			if (attendee.attendeeName === '' || attendee.attendeeEmail === '') {
				validation = 'Please ensure all your ticket information has contact names and email address';
				break;
			}
		}

		return validation;
	}

	//Order via Meeting Truth
	function createRemoteOrder(eventId, siteId, discountCode, tickets, successCallback) {
		var ticketsJSON = getTicketsJSON(tickets);

		var jsonp_url = mtUrl + "/MTUtils.asmx/CreateRemoteOrderDiscounted?callback=?";
		var json_data = {
			"purchasedTickets": ticketsJSON,
			"eventId": eventId,
			"siteId": siteId,
			"discountCode": discountCode
		};

		jQuery.getJSON(jsonp_url, json_data, function (returnId) {
			if (returnId !== "") {
				successCallback(returnId);
			}
		});
	}

	//Order via Paypal
	function createInlineRemoteOrder(eventId, siteId, username, email, discountCode, purchasedTickets, successCallback) {
		var purchasedTicketsJSON = JSON.stringify(purchasedTickets);

		var jsonp_url = mtUrl + "/MTUtils.asmx/CreateInlineRemoteOrderDiscounted?callback=?";
		var json_data = {
			"name": username,
			"email": email,
			"purchasedTickets": purchasedTicketsJSON,
			"eventId": eventId,
			"siteId": siteId,
			"discountCode": discountCode
		};

		$.getJSON(jsonp_url, json_data, successCallback);
	}


	function getDiscountCode() {
		var $txtVoucherCode = $("#txtVoucherCode");
		var voucherCode = "";

		if ($txtVoucherCode.length > 0) {
			voucherCode = $txtVoucherCode.val();
		}

		return voucherCode;
	}

	function isDiscountCodeValid(discountCode, eventId, callback) {
		if (discountCode.trim() !== '') {
			var jsonp_url = mtUrl + "/MTUtils.asmx/CheckDiscountCode?callback=?";

			$.getJSON(jsonp_url, { "discountCode": discountCode, "eventId": eventId }, function (response) {
				callback(response.IsValid);
			});
		} else {
			callback(false);
		}
	}

	function getTicketsJSON(tickets) {
		var ticketsJSON = [];

		for (var i = 0; i < tickets.length; i++) {
			var ticket = tickets[i];
			var donationJson = '';
			if (ticket.Donation) {
				donationJson = ', "donationAmount":' + ticket.Donation;
			}
			var ticketJSON = '{"ticketId":' + ticket.Id + ',"qty":' + ticket.Quantity + donationJson + '}';
			ticketsJSON.push(ticketJSON);
		}

		var JSON = '[' + ticketsJSON.join(',') + ']';
		return JSON;
	}


	function bookSessionOnMT(sessionId) {
		bookSession(sessionId, function (eventId, tickets) {
			bookEventOnMT(eventId, "", tickets);
		});
	}

	function bookEventOnMT(eventId, discountCode, tickets) {
		loading();

		showRedirectDialogBox("Taking you to Meeting Truth to complete your booking");

		createRemoteOrder(eventId, window.location.host, discountCode, tickets, function (returnId) {
			window.location = mtUrl + "/shop/ordersummary.aspx?id=" + returnId + "&eId=" + eventId;
		});
	}

	function bookSessionOnSite(sessionId) {
		bookSession(sessionId, function (eventId, tickets) {
			bookEventOnSite(eventId, "", tickets);
		});
	}

	function bookSession(sessionId, callback) {
		var jsonp_url = mtUrl + "/MTUtils.asmx/BookSession?callback=?";

		jQuery.getJSON(jsonp_url, { "sessionId": sessionId }, function (data) {
			if (data !== "") {
				var tickets = [];
				tickets[0] = new ticketPurchase(data.ticketId, data.ticketName, 1);
				callback(data.eventId, tickets);
			} else {
				console.log("no data");
			}
		});
	}

	function ticketPurchase(ticketId, name, qty) {
		this.Id = ticketId;
		this.Name = name;
		this.Quantity = qty;
	}




	function showDialogBox(html) {
		$("#rnmmt_dialogbox p").html(html);
		$("#rnmmt_dialogbox").fadeIn();
	}

	function showRedirectDialogBox(html) {
		$("#rnmmt_redirecting p").html(html);
		$("#rnmmt_redirecting").fadeIn();
	}
});
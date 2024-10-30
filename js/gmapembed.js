function loadMap() {
	var latlng = new google.maps.LatLng(gMap.lat, gMap.lng);
	var myOptions = {
		zoom: 13,
		center: latlng,
		mapTypeId: google.maps.MapTypeId.ROADMAP,
		mapTypeControlOptions: {
			style: google.maps.MapTypeControlStyle.DROPDOWN_MENU,
			position: google.maps.ControlPosition.TOP_RIGHT
		}
	};
	var map = new google.maps.Map(document.getElementById("map_container"), myOptions);

	var marker = new google.maps.Marker({
		position: latlng,
		map: map,
		title: gMap.name
	});

}
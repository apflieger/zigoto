$(".layer-arrow").click(function() {
    $.scrollTo($(this).parent(), 650);
});

var map;
function initMap() {
    map = new google.maps.Map(document.getElementById('map'), {
        center: {lat: 46.227, lng: 2.213},
        zoom: 6,
        mapTypeControl: false,
        streetViewControl: false
    });

    /*var geocoder = new google.maps.Geocoder();

    geocoder.geocode({address: "France"}, function(results, status) {
        if (status === google.maps.GeocoderStatus.OK) {
            map.setCenter(results[0].geometry.location);
        } else {
            alert('Geocode was not successful for the following reason: ' + status);
        }
    });*/
}

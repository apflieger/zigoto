$(".layer-arrow").click(function() {
    $.scrollTo($(this).parent(), 650);
});

var map;
function initMap() {
    map = new google.maps.Map(document.getElementById('map'), {
        center: {lat: 46.227, lng: 2.213},
        zoom: 5,
        mapTypeControl: false,
        streetViewControl: false,
        zoomControl: false,
        scrollwheel: false,
        draggable: false
    });

    var markers = [
        {lat: 48.56024, lng: 1.82373},
        {lat: 48.07807, lng: 7.09716},
        {lat: 48.80686, lng: 2.76855},
        {lat: 48.92249, lng: 2.20825},
        {lat: 48.79239, lng: 2.25219},
        {lat: 48.84664, lng: 2.5653},
        {lat: 48.87194, lng: 2.3236},
        {lat: 48.862, lng: 2.31262},
        {lat: 48.86742, lng: 2.37854},
        {lat: 48.8385, lng: 2.36755},
        {lat: 48.92069, lng: 2.48016},
        {lat: 48.85477, lng: 2.29476},
        {lat: 49.03786, lng: 2.3703},
        {lat: 48.80324, lng: 2.37991},
        {lat: 48.67645, lng: 2.56805},
        {lat: 49.03606, lng: 1.58477},
        {lat: 49.53946, lng: 2.72735},
        {lat: 49.87162, lng: 1.10412},
        {lat: 49.83443, lng: 1.18927},
        {lat: 50.34721, lng: 3.05145},
        {lat: 50.30513, lng: 2.71911},
        {lat: 48.18073, lng: 0.26916},
        {lat: 47.96785, lng: 1.64794},
        {lat: 47.52461, lng: 4.83947},
        {lat: 47.68388, lng: 6.6687},
        {lat: 48.23199, lng: -2.8894},
        {lat: 47.17477, lng: -1.23046},
        {lat: 46.98774, lng: -1.61499},
        {lat: 45.88236, lng: 0.25268},
        {lat: 44.62957, lng: -0.6372},
        {lat: 44.91813, lng: -0.19775},
        {lat: 43.85829, lng: 1.17553},
        {lat: 43.86621, lng: 1.53808},
        {lat: 43.32517, lng: 2.8125},
        {lat: 43.49278, lng: 3.33984},
        {lat: 43.66787, lng: 4.05395},
        {lat: 43.55651, lng: 5.50415},
        {lat: 43.37311, lng: 5.39428},
        {lat: 43.2772, lng: 5.52612},
        {lat: 43.48481, lng: 5.51513},
        {lat: 43.1571, lng: 5.88867},
        {lat: 43.75522, lng: 6.88842},
        {lat: 43.66787, lng: 6.56982},
        {lat: 45.63708, lng: 4.85595},
        {lat: 45.7905, lng: 5.03173},
        {lat: 45.82114, lng: 4.81201},
        {lat: 45.32897, lng: 5.74584},
        {lat: 45.77518, lng: 4.37255},
        {lat: 46.75491, lng: 4.57031},
        {lat: 45.08127, lng: 1.86767},
        {lat: 46.87521, lng: 1.45019},
        {lat: 48.89361, lng: 5.65795}
    ];

    for (var i = 0; i < markers.length; i++) {
        var marker = new google.maps.Marker({
            position: markers[i],
            map: map
        });

        marker.addListener("click", function() {
            ga("send", "event", "map", "click-marker");
        });
    }
}

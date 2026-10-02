<?php
/**
 * Shared Leaflet pin picker. Expects $picker = array('lat', 'lng', 'office' => get_office_map_location()).
 * Renders lat/lng inputs named "lat" / "lng" plus the outside-PH confirm box; place inside a <form>.
 */
$picker = isset($picker) ? $picker : array('lat' => null, 'lng' => null, 'office' => null);
$office = isset($picker['office']) ? $picker['office'] : null;
$pin_lat = $picker['lat'];
$pin_lng = $picker['lng'];
?>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
<style type="text/css">
    .map-picker { margin-bottom: 12px; }
    .map-picker-canvas { height: 380px; border: 1px solid #e7eaec; border-radius: 8px; }
    .map-picker-coords { display: flex; flex-wrap: wrap; gap: 10px; align-items: flex-end; margin-top: 10px; }
    .map-picker-coords .form-group { margin: 0; }
    .map-picker-coords input[type=text] { width: 170px; }
    .map-picker-hint { color: #888; font-size: 12px; margin-top: 6px; }
    .map-picker-outside { margin-top: 8px; display: none; }
</style>

<div class="map-picker">
    <div id="map-picker-canvas" class="map-picker-canvas"></div>
    <div class="map-picker-coords">
        <div class="form-group">
            <label for="map-picker-lat">Latitude</label>
            <input type="text" id="map-picker-lat" name="lat" class="form-control" autocomplete="off"
                   value="<?php echo $pin_lat !== null ? htmlspecialchars($pin_lat, ENT_QUOTES, 'UTF-8') : ''; ?>"/>
        </div>
        <div class="form-group">
            <label for="map-picker-lng">Longitude</label>
            <input type="text" id="map-picker-lng" name="lng" class="form-control" autocomplete="off"
                   value="<?php echo $pin_lng !== null ? htmlspecialchars($pin_lng, ENT_QUOTES, 'UTF-8') : ''; ?>"/>
        </div>
        <div class="form-group">
            <button type="button" class="btn btn-default" id="map-picker-gps"><i class="fa fa-crosshairs"></i> Use my GPS</button>
        </div>
    </div>
    <div class="checkbox map-picker-outside" id="map-picker-outside">
        <label><input type="checkbox" name="confirm_outside" value="1"/> Pin is outside the Philippines</label>
    </div>
    <div class="map-picker-hint">Click the map or drag the marker to place the pin. You can also type the coordinates.</div>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script>
(function () {
    var pin = <?php echo json_encode(array('lat' => $pin_lat, 'lng' => $pin_lng)); ?>;
    var office = <?php echo json_encode($office); ?>;
    var latInput = document.getElementById('map-picker-lat');
    var lngInput = document.getElementById('map-picker-lng');
    var outsideBox = document.getElementById('map-picker-outside');

    var hasPin = pin.lat !== null && pin.lng !== null;
    var start = hasPin ? [pin.lat, pin.lng]
        : (office && office.lat && office.lng ? [parseFloat(office.lat), parseFloat(office.lng)] : [10.1494, 124.3252]);

    var map = L.map('map-picker-canvas').setView(start, hasPin ? 17 : 14);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap'
    }).addTo(map);

    if (office && office.lat && office.lng) {
        L.circleMarker([parseFloat(office.lat), parseFloat(office.lng)], {
            radius: 6, color: '#ed5565', fillColor: '#ed5565', fillOpacity: 0.8
        }).bindTooltip('Office').addTo(map);
    }

    var marker = null;

    function checkOutside(lat, lng) {
        var inside = lat >= 4 && lat <= 22 && lng >= 116 && lng <= 127;
        outsideBox.style.display = inside ? 'none' : 'block';
    }

    function setInputs(latlng) {
        latInput.value = latlng.lat.toFixed(7);
        lngInput.value = latlng.lng.toFixed(7);
        checkOutside(latlng.lat, latlng.lng);
    }

    function placeMarker(latlng) {
        if (!marker) {
            marker = L.marker(latlng, { draggable: true }).addTo(map);
            marker.on('dragend', function () { setInputs(marker.getLatLng()); });
        } else {
            marker.setLatLng(latlng);
        }
    }

    if (hasPin) {
        placeMarker(L.latLng(pin.lat, pin.lng));
        checkOutside(pin.lat, pin.lng);
    }

    map.on('click', function (e) {
        placeMarker(e.latlng);
        setInputs(e.latlng);
    });

    function onTyped() {
        var lat = parseFloat(latInput.value), lng = parseFloat(lngInput.value);
        if (isNaN(lat) || isNaN(lng) || lat < -90 || lat > 90 || lng < -180 || lng > 180) return;
        var ll = L.latLng(lat, lng);
        placeMarker(ll);
        map.panTo(ll);
        checkOutside(lat, lng);
    }
    latInput.addEventListener('change', onTyped);
    lngInput.addEventListener('change', onTyped);

    document.getElementById('map-picker-gps').addEventListener('click', function () {
        if (!navigator.geolocation) {
            alert('GPS is not supported on this device.');
            return;
        }
        navigator.geolocation.getCurrentPosition(function (pos) {
            var ll = L.latLng(pos.coords.latitude, pos.coords.longitude);
            placeMarker(ll);
            setInputs(ll);
            map.setView(ll, 18);
        }, function () {
            alert('Could not get your location. Allow location access and try again.');
        }, { enableHighAccuracy: true, timeout: 15000 });
    });
})();
</script>

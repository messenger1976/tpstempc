<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <?php
    $company = company_info_detail();
    $company_name = (isset($company->name) && $company->name !== '') ? $company->name : 'Cooperative';
    $targets = isset($collector_targets) ? $collector_targets : array();
    $office = isset($office_map_location) ? $office_map_location : null;
    $filter_mode = isset($filter_mode) ? $filter_mode : 'overdue';
    ?>
    <title><?php echo htmlspecialchars($company_name); ?> | Collector Map</title>
    <link href="<?php echo base_url(); ?>media/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?php echo base_url(); ?>assets/font-awesome/css/font-awesome.css?v=4.7.0" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine@3.2.12/dist/leaflet-routing-machine.css" />
    <style>
        * { box-sizing: border-box; }
        html, body { height: 100%; margin: 0; background: #f3f3f4; font-family: "open sans", "Helvetica Neue", Helvetica, Arial, sans-serif; }
        .collector-shell { display: flex; flex-direction: column; height: 100vh; }
        .collector-topbar {
            flex: 0 0 auto;
            background: linear-gradient(135deg, #1c84c6 0%, #1565a8 100%);
            color: #fff;
            padding: 10px 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
            z-index: 1000;
        }
        .collector-topbar h1 { margin: 0; font-size: 18px; font-weight: 700; }
        .collector-topbar .sub { font-size: 12px; opacity: 0.9; margin-top: 2px; }
        .collector-toolbar { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 10px; align-items: center; }
        .collector-toolbar .btn { border-radius: 20px; font-size: 12px; padding: 5px 12px; }
        .collector-toolbar .btn.active { background: #fff; color: #1c84c6; border-color: #fff; }
        .collector-toolbar .search-wrap { flex: 1 1 180px; min-width: 140px; }
        .collector-toolbar .search-wrap input {
            width: 100%; border: none; border-radius: 20px; padding: 7px 12px; font-size: 13px;
        }
        .collector-body { flex: 1 1 auto; display: flex; min-height: 0; position: relative; }
        #collector-map { flex: 1 1 auto; min-height: 0; z-index: 1; }
        .collector-panel {
            width: 320px; max-width: 92vw; background: #fff; border-left: 1px solid #e7eaec;
            display: flex; flex-direction: column; z-index: 2;
        }
        .collector-panel-header {
            padding: 10px 12px; border-bottom: 1px solid #e7eaec; font-weight: 600; font-size: 13px;
            background: #fafafa;
        }
        .collector-list { overflow-y: auto; flex: 1 1 auto; -webkit-overflow-scrolling: touch; }
        .collector-item {
            padding: 10px 12px; border-bottom: 1px solid #f0f0f0; cursor: pointer;
        }
        .collector-item:hover, .collector-item.active { background: #edf6ff; }
        .collector-item .name { font-weight: 600; font-size: 13px; color: #333; }
        .collector-item .meta { font-size: 11px; color: #777; margin-top: 3px; }
        .collector-item .balance { font-size: 12px; font-weight: 700; margin-top: 4px; }
        .badge-overdue { background: #ed5565; color: #fff; font-size: 10px; padding: 2px 6px; border-radius: 10px; }
        .badge-current { background: #1ab394; color: #fff; font-size: 10px; padding: 2px 6px; border-radius: 10px; }
        .map-fab-group {
            position: absolute; right: 12px; bottom: 16px; z-index: 500; display: flex; flex-direction: column; gap: 8px;
        }
        .map-fab {
            width: 44px; height: 44px; border-radius: 50%; border: none; background: #fff;
            box-shadow: 0 2px 8px rgba(0,0,0,0.25); color: #1c84c6; font-size: 18px;
        }
        .map-fab.gps-on { background: #1c84c6; color: #fff; }
        .gps-status {
            position: absolute; left: 12px; bottom: 16px; z-index: 500;
            background: rgba(255,255,255,0.95); padding: 6px 10px; border-radius: 16px;
            font-size: 11px; color: #555; box-shadow: 0 1px 6px rgba(0,0,0,0.15); max-width: 70%;
        }
        .gps-status.error { color: #a94442; }
        .gps-status.ok { color: #1ab394; }
        .leaflet-routing-container { max-height: 180px; overflow-y: auto; width: 260px; font-size: 11px; }
        .collector-gps-marker {
            background: #1ab394; color: #fff; border: 3px solid #fff; border-radius: 50%;
            width: 18px; height: 18px; box-shadow: 0 0 0 6px rgba(26,179,148,0.25);
        }
        .panel-toggle { display: none; }
        @media (max-width: 768px) {
            .collector-body { flex-direction: column; }
            .collector-panel {
                width: 100%; max-width: none; border-left: none; border-top: 1px solid #e7eaec;
                max-height: 38vh;
            }
            .panel-toggle {
                display: inline-block;
            }
            .collector-panel.collapsed { display: none; }
        }
    </style>
</head>
<body>
<div class="collector-shell">
    <div class="collector-topbar">
        <div style="display:flex; justify-content:space-between; align-items:flex-start;">
            <div>
                <h1><i class="fa fa-map-marker"></i> Collector Map</h1>
                <div class="sub"><?php echo htmlspecialchars($company_name); ?> &mdash; field collection directions</div>
            </div>
            <a href="<?php echo site_url(current_lang()); ?>" class="btn btn-default btn-xs" style="margin-top:2px;">
                <i class="fa fa-arrow-left"></i> Dashboard
            </a>
        </div>
        <div class="collector-toolbar">
            <a href="<?php echo site_url(current_lang() . '/collector_map/index?filter=overdue'); ?>"
               class="btn btn-default btn-sm <?php echo $filter_mode === 'overdue' ? 'active' : ''; ?>">Overdue</a>
            <a href="<?php echo site_url(current_lang() . '/collector_map/index?filter=all'); ?>"
               class="btn btn-default btn-sm <?php echo $filter_mode === 'all' ? 'active' : ''; ?>">All Members</a>
            <div class="search-wrap">
                <input type="search" id="collector-search" placeholder="Search name, ID, address..." autocomplete="off">
            </div>
            <button type="button" class="btn btn-default btn-sm panel-toggle" id="toggle-panel">
                <i class="fa fa-list"></i> List
            </button>
        </div>
    </div>

    <div class="collector-body">
        <div id="collector-map"></div>

        <div class="map-fab-group">
            <button type="button" class="map-fab" id="btn-center-gps" title="Center on my location"><i class="fa fa-crosshairs"></i></button>
            <button type="button" class="map-fab" id="btn-clear-route" title="Clear route"><i class="fa fa-times"></i></button>
        </div>
        <div class="gps-status" id="gps-status"><i class="fa fa-spinner fa-spin"></i> Locating GPS...</div>

        <div class="collector-panel" id="collector-panel">
            <div class="collector-panel-header">
                <?php echo count($targets); ?> member(s) on map
                <?php if ($filter_mode === 'overdue') { ?>&mdash; overdue loans<?php } ?>
            </div>
            <div class="collector-list" id="collector-list">
                <?php if (empty($targets)) { ?>
                    <div style="padding:16px; color:#777; font-size:13px;">
                        <?php if (empty($map_stats['table_ready'])) { ?>
                            Geocode cache not installed. Run <code>install_member_address_geocode.php</code> first.
                        <?php } elseif ($filter_mode === 'overdue') { ?>
                            No overdue members with geocoded addresses found.
                        <?php } else { ?>
                            No members with geocoded addresses found.
                        <?php } ?>
                    </div>
                <?php } else { ?>
                    <?php foreach ($targets as $t) {
                        $balance = number_format($t['outstanding_balance'], 2);
                        $days = intval($t['days_overdue']);
                    ?>
                    <div class="collector-item"
                         data-member-id="<?php echo htmlspecialchars($t['member_id']); ?>"
                         data-lat="<?php echo $t['lat']; ?>"
                         data-lng="<?php echo $t['lng']; ?>"
                         data-name="<?php echo htmlspecialchars($t['name']); ?>"
                         data-address="<?php echo htmlspecialchars($t['address']); ?>"
                         data-search="<?php echo htmlspecialchars(strtolower($t['member_id'] . ' ' . $t['name'] . ' ' . $t['address'])); ?>">
                        <div class="name">
                            <?php echo htmlspecialchars($t['member_id'] . ' — ' . $t['name']); ?>
                            <?php if ($days > 0) { ?>
                                <span class="badge-overdue"><?php echo $days; ?>d overdue</span>
                            <?php } elseif ($t['outstanding_balance'] > 0) { ?>
                                <span class="badge-current">Active loan</span>
                            <?php } ?>
                        </div>
                        <div class="meta"><?php echo htmlspecialchars($t['address']); ?></div>
                        <?php if ($t['outstanding_balance'] > 0) { ?>
                            <div class="balance" style="color:#ed5565;">Balance: <?php echo $balance; ?></div>
                        <?php } ?>
                    </div>
                    <?php } ?>
                <?php } ?>
            </div>
        </div>
    </div>
</div>

<script src="<?php echo base_url(); ?>media/js/jquery-1.10.2.js"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script src="https://unpkg.com/leaflet-routing-machine@3.2.12/dist/leaflet-routing-machine.js"></script>
<script>
(function() {
    var targets = <?php echo json_encode($targets); ?>;
    var office = <?php echo json_encode($office); ?>;
    var map, routingControl = null, gpsMarker = null, gpsWatchId = null;
    var userLat = null, userLng = null, gpsReady = false;
    var memberMarkers = {};

    function markerColor(days, balance) {
        if (days > 180) return '#a94442';
        if (days > 90) return '#d9534f';
        if (days > 30) return '#f8ac59';
        if (days > 0) return '#ed5565';
        if (balance > 0) return '#1c84c6';
        return '#676a6c';
    }

    function setGpsStatus(html, cls) {
        var el = document.getElementById('gps-status');
        el.className = 'gps-status ' + (cls || '');
        el.innerHTML = html;
    }

    function initMap() {
        map = L.map('collector-map', { zoomControl: true });
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 18,
            attribution: '&copy; OpenStreetMap'
        }).addTo(map);

        var bounds = [];
        $.each(targets, function(i, t) {
            var lat = parseFloat(t.lat), lng = parseFloat(t.lng);
            if (isNaN(lat) || isNaN(lng)) return;

            var color = markerColor(parseInt(t.days_overdue, 10) || 0, parseFloat(t.outstanding_balance) || 0);
            var popup = '<strong>' + escapeHtml(t.member_id + ' — ' + t.name) + '</strong><br>' +
                escapeHtml(t.address) + '<br>';
            if (parseFloat(t.outstanding_balance) > 0) {
                popup += '<span style="color:#ed5565;font-weight:bold;">Balance: ' +
                    Number(t.outstanding_balance).toLocaleString(undefined, {minimumFractionDigits:2}) + '</span><br>';
            }
            if (parseInt(t.days_overdue, 10) > 0) {
                popup += t.days_overdue + ' days overdue<br>';
            }
            popup += '<small><i class="fa fa-road"></i> Tap for directions from your GPS</small>';

            var marker = L.circleMarker([lat, lng], {
                radius: 9,
                color: color,
                weight: 2,
                fillColor: color,
                fillOpacity: 0.8
            }).bindPopup(popup).addTo(map);

            marker.on('click', function() {
                routeToMember(t);
                highlightListItem(t.member_id);
            });

            memberMarkers[t.member_id] = marker;
            bounds.push([lat, lng]);
        });

        if (office && office.lat && office.lng) {
            L.marker([parseFloat(office.lat), parseFloat(office.lng)], {
                icon: L.divIcon({
                    className: '',
                    html: '<div style="background:#ed5565;color:#fff;border:2px solid #fff;border-radius:50%;width:26px;height:26px;line-height:22px;text-align:center;box-shadow:0 1px 4px rgba(0,0,0,.4);"><i class="fa fa-building"></i></div>',
                    iconSize: [26, 26], iconAnchor: [13, 13]
                })
            }).bindPopup('<strong>' + escapeHtml(office.label || 'Office') + '</strong><br>' + escapeHtml(office.address || '')).addTo(map);
            bounds.push([parseFloat(office.lat), parseFloat(office.lng)]);
        }

        if (bounds.length) {
            map.fitBounds(bounds, { padding: [40, 40], maxZoom: 14 });
        } else {
            map.setView([10.1494, 124.3252], 12);
        }
    }

    function escapeHtml(text) {
        return $('<div/>').text(text || '').html();
    }

    function getRouteStart() {
        if (gpsReady && userLat !== null && userLng !== null) {
            return L.latLng(userLat, userLng);
        }
        if (office && office.lat && office.lng) {
            return L.latLng(parseFloat(office.lat), parseFloat(office.lng));
        }
        return null;
    }

    function routeToMember(t) {
        var start = getRouteStart();
        if (!start) {
            setGpsStatus('<i class="fa fa-warning"></i> Enable GPS or wait for location', 'error');
            return;
        }
        var dest = L.latLng(parseFloat(t.lat), parseFloat(t.lng));
        if (routingControl) {
            map.removeControl(routingControl);
            routingControl = null;
        }
        var fromLabel = gpsReady ? 'Your location' : 'Office';
        routingControl = L.Routing.control({
            waypoints: [start, dest],
            routeWhileDragging: false,
            addWaypoints: false,
            draggableWaypoints: false,
            fitSelectedRoutes: true,
            showAlternatives: false,
            createMarker: function() { return null; },
            lineOptions: { styles: [{ color: '#1c84c6', opacity: 0.9, weight: 6 }] },
            router: L.Routing.osrmv1({
                serviceUrl: 'https://router.project-osrm.org/route/v1',
                profile: 'driving'
            })
        }).addTo(map);

        routingControl.on('routesfound', function(e) {
            if (!e.routes || !e.routes.length) return;
            var s = e.routes[0].summary;
            var km = (s.totalDistance / 1000).toFixed(1);
            var mins = Math.round(s.totalTime / 60);
            var panel = $('.leaflet-routing-container .leaflet-routing-alt h2').first();
            if (panel.length) {
                panel.text(fromLabel + ' → ' + (t.name || t.member_id) + ' (' + km + ' km, ~' + mins + ' min)');
            }
        });
    }

    function highlightListItem(memberId) {
        $('.collector-item').removeClass('active');
        $('.collector-item[data-member-id="' + memberId + '"]').addClass('active');
        var item = $('.collector-item[data-member-id="' + memberId + '"]')[0];
        if (item) item.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
    }

    function updateGpsMarker(lat, lng) {
        if (!gpsMarker) {
            gpsMarker = L.marker([lat, lng], {
                icon: L.divIcon({
                    className: '',
                    html: '<div class="collector-gps-marker"></div>',
                    iconSize: [18, 18], iconAnchor: [9, 9]
                }),
                zIndexOffset: 2000
            }).bindPopup('<strong>Your location</strong><br><small>GPS position</small>').addTo(map);
        } else {
            gpsMarker.setLatLng([lat, lng]);
        }
    }

    function startGps() {
        if (!navigator.geolocation) {
            setGpsStatus('<i class="fa fa-warning"></i> GPS not supported — using office as start', 'error');
            return;
        }
        gpsWatchId = navigator.geolocation.watchPosition(function(pos) {
            userLat = pos.coords.latitude;
            userLng = pos.coords.longitude;
            gpsReady = true;
            updateGpsMarker(userLat, userLng);
            setGpsStatus('<i class="fa fa-location-arrow"></i> GPS active — routes start from you', 'ok');
            $('#btn-center-gps').addClass('gps-on');
        }, function(err) {
            var msg = 'GPS unavailable — routes start from office';
            if (err.code === 1) msg = 'GPS denied — allow location access, or routes use office';
            setGpsStatus('<i class="fa fa-warning"></i> ' + msg, 'error');
            gpsReady = false;
        }, { enableHighAccuracy: true, maximumAge: 10000, timeout: 15000 });
    }

    $(document).ready(function() {
        if (!$('#collector-map').length) return;
        initMap();
        startGps();

        $('#btn-center-gps').on('click', function() {
            if (gpsReady && userLat !== null) {
                map.setView([userLat, userLng], 16);
            } else {
                setGpsStatus('<i class="fa fa-warning"></i> Waiting for GPS fix...', 'error');
            }
        });

        $('#btn-clear-route').on('click', function() {
            if (routingControl) {
                map.removeControl(routingControl);
                routingControl = null;
            }
        });

        $('#collector-search').on('input', function() {
            var q = $(this).val().toLowerCase().trim();
            $('.collector-item').each(function() {
                var text = $(this).data('search') || '';
                $(this).toggle(!q || text.indexOf(q) !== -1);
            });
        });

        $('.collector-item').on('click', function() {
            var $el = $(this);
            var memberId = $el.data('member-id');
            var lat = parseFloat($el.data('lat'));
            var lng = parseFloat($el.data('lng'));
            highlightListItem(memberId);
            if (memberMarkers[memberId]) {
                map.setView([lat, lng], 15);
                memberMarkers[memberId].openPopup();
            }
            var t = null;
            for (var i = 0; i < targets.length; i++) {
                if (targets[i].member_id === memberId) { t = targets[i]; break; }
            }
            if (t) routeToMember(t);
        });

        $('#toggle-panel').on('click', function() {
            $('#collector-panel').toggleClass('collapsed');
        });

        setTimeout(function() { if (map) map.invalidateSize(); }, 300);
        $(window).on('resize', function() { if (map) map.invalidateSize(); });
    });
})();
</script>
</body>
</html>

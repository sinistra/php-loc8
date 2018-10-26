<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.1.2/js/bootstrap.min.js"></script>
    <script type="text/javascript" src="{{ URL::asset('js/jquery.easy-autocomplete.js') }}"></script>
    <script type="text/javascript" src="{{ URL::asset('js/tree.jquery.js') }}"></script>

    <link rel="stylesheet"
          href="https://fonts.googleapis.com/css?family=Roboto:regular,bold,italic,thin,light,bolditalic,black,medium&amp;lang=en">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Montserrat">
    <link rel="stylesheet" href="{{ URL::asset('css/easy-autocomplete.css') }}"/>
    <link rel="stylesheet" href="{{ URL::asset('css/easy-autocomplete.themes.css') }}"/>
    <link rel="stylesheet" href="{{ URL::asset('css/jqtree.css') }}"/>
    <link rel="stylesheet" href="{{ URL::asset('css/app.css') }}"/>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.2/css/bootstrap.min.css"
          integrity="sha384-Smlep5jCw/wG7hdkwQ/Z5nLIefveQRIY9nfy6xoR1uRYBtpZgI6339F5dgvm/e9B" crossorigin="anonymous">

    <style>
        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: "Helvetica Neue", Helvetica, Arial;
        }

        p {
            font-size: 11px;
            padding: 0px;
            margin: 0px;
        }

        a {
            color: white;
        }

        a:hover {
            color: grey;
            text-decoration: none;
        }

        #suggest_input {
            color: black;
        }

        #suggest_container {
            color: black;
            width: 100%;
            padding-left: 37px;
            margin-top: 12px;
            border: 0px red solid;
        }

        #header_div {
            width: 100%;
            height: 50px;
            border: 0px blue solid;
            background: rgb(0, 0, 0);
        }

        #logo_div {
            float: left;
            width: 300px;
            border: 0px pink solid;
        }

        #searchbar_div {
            float: right;
            width: calc(100% - 300px);
            height: 100%;
            border: 0px cyan solid;
        }

        #main_wrapper {
            width: 100%;
            height: calc(100% - 50px);
            border: 0px red solid;
        }

        #left_pane_wrapper {
            letter-spacing: 1px;
            font-weight: 200;
            font-size: 10px;
            color: white;
            background: #292929;
            float: left;
            width: 320px;
            height: 100%;
            border: 0px green solid;
        }

        #welcome_pane {
            border: 0px white solid;
            padding: 12px 20px 12px 20px;
        }

        #results_pane {
            display: none;
            border: 0px white solid;
            padding: 12px 20px 12px 20px;
        }

        #results_area {
            margin-left: 10px;
        }

        #at_address_pane {
            display: none;
            border: 0px white solid;
            border-top: 1px #545454 solid;
            padding: 10px 20px 12px 20px;
        }

        #at_geo_pane {
            display: none;
            border: 0px white solid;
            border-top: 1px #545454 solid;
            padding: 12px 20px 12px 20px;
        }

        #nearby_pane {
            display: none;
            border: 0px white solid;
            border-top: 1px #545454 solid;
            padding: 12px 20px 12px 20px;
        }

        #content_div {
            float: right;
            width: calc(100% - 320px);
            height: 100%;
            border: 0px orange solid;
        }

        #map {
            height: 100%;
            z-index: 0;
        }

        .pane_tree {
            padding: 7px 0px 0px 0px;
        }

        .results_h1 {
            margin-top: 5px;
            font-size: 14px;
        }

        .results {
            margin-left: 10px;
        }

        .modal-content {
            letter-spacing: 1px;
            font-weight: 200;
            font-size: 10px;
            color: white;
            background: #292929;
        }

        .modal-title {
            letter-spacing: 1px;
            font-weight: 200;
            font-size: 15px;
            font-family: "Helvetica Neue", Helvetica, Arial;
        }

        .modal-body {
            height: 60%;
            overflow-y: auto;
        }

        .btn-outline-secondary {
            font-size: 11px;
            font-family: "Helvetica Neue", Helvetica, Arial;
            letter-spacing: 1px;
            font-weight: 200;
            color: #d6d6d6;
            border-color: #d6d6d6;
        }

        .btn-outline-secondary:hover {
            background: transparent;
            color: #3bd869;
            border-color: #3bd869;
        }

        button:focus {
            outline-width: 0px;
        }

    </style>

</head>
<body>

<div id="header_div">
    <div id="logo_div">
        <a href="/loc8"><img src="../images/logo-macquarie-telecom.png"
                             style="height: 33px; margin: 8px 0px 0px 20px"></a>
        <div style=" float: right; margin: 6px 10px 0px 0px">
            <span style="color: #dedede; font-size: 18pt; font-weight: 100; font-style: normal; letter-spacing: 2px;">LOC-8</span>
        </div>
    </div>
    <div id="searchbar_div">
        <div id="suggest_container">

            <div class="input-group input-group-sm mb-3">
                <input id="suggest_input" type="text" class="form-control" aria-label="Sizing example input"
                       aria-describedby="inputGroup-sizing-sm" style="font-size: 11px; padding: 4px 12px;">
                <div class="input-group-append">
                    <div class="input-group-text" style="padding : 0px 5px; height: 26px;">
                        <span style="font-size: 9px; padding-right: 5px;">SHOW:</span>
                        <input id="subs_chk" type="checkbox" aria-label="checkbox" style="margin: 2px 3px 0px 0px;">
                        <span style="font-size: 9px; padding-right: 5px;">SUBs</span>
                        <input id="aliass_chk" type="checkbox" aria-label="checkbox" style="margin: 2px 3px 0px 0px;">
                        <span style="font-size: 9px; padding-right: 2px;">ALIASs</span>
                    </div>
                    <button id="suggest_button" class="btn btn-outline-secondary" type="button"
                            style="font-size: 11px; padding: 2px 10px; border-radius: 0px 14px 14px 0px;">search
                    </button>
                </div>
                <button id="bulk_button" class="btn btn-outline-secondary" type="button"
                        onclick="location.href='http://localhost/loc8/bulk';"
                        style="margin-left: 5px; font-size: 11px; padding: 2px 15px; border-radius: 14px;">bulk
                </button>
            </div>
        </div>
    </div>
</div>

<div id="main_wrapper">
    <div id="left_pane_wrapper" style="overflow: auto;">
        <div id="welcome_pane">
            <p style="font-size: 16px; padding-bottom: 10px;">Welcome</p>
            <p>LOC-8 assists in matching customer addresses to official (mostly NBN) servicable locations. Simply start
                typing in the search bar above to see match suggestions.</p>
            <br>
            <p>For help, questions, or feature suggestions you can contact michael from the locate team at;
                mhulowatyi@macquarietelecom.com.</p>
            <br>
            <p>All the best from the LOC-8 team.</p>
        </div>
        <div id="results_pane">
            <p style="font-size: 16px;">Search Results</p>
            <div id="results_area"></div>
        </div>
        <div id="at_address_pane">
            <p style="font-size: 16px;">At Found Address</p>
            <div id="address_tree" class="pane_tree"></div>
        </div>
        <div id="at_geo_pane">
            <p style="font-size: 16px;">At Found Geo-Location</p>
            <p>results at this geo location (not necessarily with the same base address) will go here.</p>
            <div id="geo_location_tree" class="pane_tree"></div>
        </div>
        <div id="nearby_pane">
            <p style="font-size: 16px;">Nearby Found Geo-Location</p>
            <div id="nearby_tree" class="pane_tree"></div>
        </div>
    </div>
    <div id="content_div">
        <div id="map"></div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="detailModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLongTitle"
         aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLongTitle">PLACE DETAILS</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true" style="font-size: 16px; color: white;">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    ...
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
    $(document).ready(function () {

        var dv_wth = $('#suggest_container').width() - 280;
        $('.easy-autocomplete').width(dv_wth);

        var modal_ht = $(window).height() - 280;
        $('.modal-body').height(modal_ht);

        $(window).resize(function () {
            var dv_wth = $('#suggest_container').width() - 280;
            $('.easy-autocomplete').width(dv_wth);

            var modal_ht = $(window).height() - 280;
            $('.modal-body').height(modal_ht);
        });

        // this is what gets done when the user hits the enter key
        $('#suggest_input').keypress(function (e) {
            if (e.which == 13) {
                if (($('#suggest_input').getItemData(0) != -1) && ($('#suggest_input').getItemData(1) == -1)) {
                    doSelectThings('enter'); // choose the only thing in the list
                }
                else {
                    doSubmitThings('button'); // nothing in the list, or nothing unique, so throw to google to find something
                }
            }
        });

        // this is what gets done when the user hits the search button
        $('#suggest_button').click(function () {
            doSubmitThings('button');
        });

        <?php
        if (isset($str)) {
            echo "  $('#suggest_input').val('" . $str . "').trigger('change');\n";
            echo "  $('#suggest_input').trigger( jQuery.Event( 'keyup', { keyCode: 8, which: 8 } ) );\n";
            echo "  setTimeout(function(){\n";
            if ($type == "id") {
                echo "      doSelectThings('enter');\n";
            } else {
                echo "      doSubmitThings('button');\n";
            }
            echo "	}, 600);\n";

        }
        ?>

    });

    var found_pin = [];
    var nearby_pins = [];
    var geo_loc;
    var map;
    var marker;

    var autocomplete_options = {
        url: function (phrase) {
            var search_type = 'def';
            if ($('#subs_chk').is(':checked')) {
                search_type += '|subs';
            }
            if ($('#aliass_chk').is(':checked')) {
                search_type += '|aliass';
            }
            uri_str = '/loc8/qry/' + safeUrl(phrase) + '/10/' + search_type;
            console.log(uri_str);
            return uri_str;
        },
        getValue: 'loc',
        list: {
            maxNumberOfElements: 10,
            onClickEvent: function () {
                console.log('click_press');
                doSelectThings('click');
            }
        }
    };

    $('#suggest_input').easyAutocomplete(autocomplete_options);

    function initMap() {
        var myLatLng = {lat: -27.863, lng: 135.044};

        map = new google.maps.Map(document.getElementById('map'), {
            zoom: 5,
            center: myLatLng,
            styles: [
                {
                    "elementType": "geometry.fill",
                    "stylers": [{
                        "saturation": -60
                    }]
                },
                {
                    "elementType": "labels.icon",
                    "stylers": [{
                        "saturation": -75
                    }]
                }
            ]
        });
    }

    function addPin(myLat, myLng, infoTxt, serv_class, tech, mtId, iconType) {

        var image1 = 'h../images/marker_black_filled.svg';
        if ((serv_class != 0) && (serv_class != 10) && (serv_class != 20) && (serv_class != 30)) {
            // colour coded marker if its in-service
            var image2 = '../images/marker_' + tech + '_filled.svg';
        }
        else {
            // hollow marker if not in service
            var image2 = '../images/marker_black_hollow.svg';
        }


        if (iconType == 1) { // main solid found marker
            var marker = new google.maps.Marker({
                position: {lat: parseFloat(myLat), lng: parseFloat(myLng)},
                map: map,
                draggable: true,
                optimized: false,
                zIndex: 9999999,
                icon: {
                    url: image1,
                    scaledSize: new google.maps.Size(32, 32),
                    anchor: new google.maps.Point(16, 32)
                }
            });

        }
        else if (iconType == 2) { // hollow nearby markers
            var marker = new google.maps.Marker({
                position: {lat: parseFloat(myLat), lng: parseFloat(myLng)},
                map: map,
                title: infoTxt,
                marker_id: mtId,
                icon: {
                    url: image2,
                    scaledSize: new google.maps.Size(24, 24),
                    anchor: new google.maps.Point(12, 24)
                }
            });
        }
        else {
            var marker = new google.maps.Marker({
                position: {lat: parseFloat(myLat), lng: parseFloat(myLng)},
                map: map,
                title: infoTxt,
                draggable: true,
                animation: google.maps.Animation.DROP
            });
        }

        marker.addListener('click', function () {
            console.log(marker.marker_id);

            $('#suggest_input').val(marker.marker_id).trigger('change');
            $('#suggest_input').trigger(jQuery.Event('keyup', {keyCode: 8, which: 8}));
            setTimeout(function () {
                doSelectThings('nearby');
            }, 300);

        });

        return marker;
    }

    // Sets the map on all markers in the array.
    function setMapOnAll(markers, map) {
        for (var i = 0; i < markers.length; i++) {
            markers[i].setMap(map);
        }
    }

    // Removes the markers from the map, but keeps them in the array.
    function clearMarkers(markers) {
        setMapOnAll(markers, null);
    }

    // the smooth zoom function
    function smoothZoom(map, max, cnt) {
        if (cnt >= max) {
            return;
        }
        else {
            z = google.maps.event.addListener(map, 'zoom_changed', function (event) {
                google.maps.event.removeListener(z);
                smoothZoom(map, max, cnt + 1);
            });
            setTimeout(function () {
                map.setZoom(cnt)
            }, 200); // 80ms sleep between each zoom for smooth overall zoom
        }
    }

    function safeUrl(phrase) {
        // this is required so that the '/' in eg 4/21 smith st does not cause an issue with the laravel router regex
        var safe_str = phrase.replace(/\//g, '__');
        return safe_str;
    }

    function updateFoundPin(geoLat, geoLon, searchType) {

        // put the pin on the pane and zoom in
        var foundLoc = new google.maps.LatLng(geoLat, geoLon);
        clearMarkers(found_pin);

        if (searchType == 'zoom') {
            map.setZoom(5);
            map.panTo(foundLoc); // using the global map variable:
            setTimeout(function () {
                smoothZoom(map, 19, map.getZoom())
            }, 150);
        }
        else {
            map.panTo(foundLoc);
        }

        found_pin[0] = addPin(geoLat, geoLon, '', '', '', '', 1);

        google.maps.event.addListener(found_pin[0], 'dragend', function () {
            var dragPos = found_pin[0].getPosition();
            var dragPos_txt = dragPos.lat() + ', ' + dragPos.lng();
            console.log(dragPos_txt);
            $('#suggest_input').val(dragPos_txt).trigger('change');
            doSubmitThings('drag');
            $('#suggest_input').blur();
        });
    }

    function updateResults(foundAddr, matchType) {

        // update the result pane
        foundAddr = foundAddr.replace(', ', ',<br>');
        var res_area_html = '<p class="results_h1">Found Address:</p><p class="results">' + foundAddr + '</p>';
        res_area_html += '<p class="results_h1">Match Type:</p><p class="results">' + matchType + '</p>';
        $('#results_pane').css('display', 'block');
        $('#results_area').html(res_area_html);
    }

    function updateAtAddr() {

        // update at_address pane - assume there could be up to 5000 sub-addresses at a single address
        var base_hash = $('#suggest_input').getSelectedItemData().hash;
        var base_ajax_url = '/loc8/base_qry/' + base_hash + '/5000';
        console.log(base_ajax_url);
        var at_addr_data = '';
        $.get(base_ajax_url, function (data, status) {

            $.each(data, function (key, val) {
                if (key == 0) {
                    at_addr_data += '[ { "name": "' + val.st_addr + ',<br>' + val.suburb + ' [' + (Object.keys(data).length - 1) + ']", "id": "001", "children": [';
                }
                else {
                    if (key != 1) {
                        at_addr_data += ', ';
                    }
                    at_addr_data += ' { "name": "' + orDash(val.sub_addr) + '", "id": "-' + val.mt_locid + '", "children": [ ';
                    at_addr_data += '{ "name": "MT-LOC: ' + val.mt_locid + '", "id": "1' + val.mt_locid + '" }, ';
                    at_addr_data += '{ "name": "CARRIER-LOC: ' + orDash(val.carrier_locid) + '", "id": "2' + val.mt_locid + '" }, ';
                    at_addr_data += '{ "name": "GNAF: ' + orDash(val.gnaf_locid) + '", "id": "3' + val.mt_locid + '" } ';
                    //at_addr_data += '{ "name": "TECH: '+ orDash(val.tech) +'", "id": "4' + val.mt_locid + '" }, ';
                    //at_addr_data += '{ "name": "RFS: '+ orDash(val.rfs) +'", "id": "5' + val.mt_locid + '" }, ';
                    //at_addr_data += '{ "name": "SERV-CLASS: '+ orDash(val.serv_class) +'", "id": "6' + val.mt_locid + '" }';
                    at_addr_data += '] } ';
                }
            });
            at_addr_data += ' ] } ]';

            $('#at_address_pane').css('display', 'block');
            $('#address_tree').tree('destroy'); // need to destroy or else the event bindings get duplicated
            $('#address_tree').tree({data: jQuery.parseJSON(at_addr_data), autoEscape: false});

            $('#address_tree').on(
                'tree.click',
                function (event) {
                    console.log('tree_clicked');
                    var mt_loc = event.node.id;

                    // nodes with an mt-id used to get details will be of the format num num dash eg. -MTLxxxxxxxxxxxx
                    if (mt_loc.charAt(0) == '-') {

                        var base_ajax_url = '/locs/details/' + mt_loc.substring(1);
                        console.log(base_ajax_url);
                        var at_addr_data = '';
                        $.get(base_ajax_url, function (data, status) {
                            var addr_detail = '<span style="font-size: 14px;">' + data[0].formatted_address_string + '</span><br><br>';
                            addr_detail += '<div style="font-size: 11px;">';
                            addr_detail += '<span>MT-LOC: ' + data[0].id + '</span><br>';
                            addr_detail += '<span>CARRIER-LOC: ' + data[0].nbn_locid + '</span><br>';
                            addr_detail += '<span>GNAF: ' + data[0].gnaf_persistent_identifier + '</span><br>';
                            addr_detail += '</div><br>';
                            addr_detail += '<div style="color: #3bd869;">----------------------------------------<br><br>';
                            $.each(data[0], function (key, val) {
                                addr_detail += key + ': ' + val + '<br>';
                            });
                            addr_detail += '</div>';

                            //alert(JSON.stringify(data));
                            $('.modal-body').html(addr_detail);
                            $('#detailModal').modal('show');

                        });
                    }
                }
            );

        });
    }

    function updateNearby(geoLat, geoLon) {

        // update nearby pane and nearby pins
        var nearby_ajax_url = '/loc8/nearby_qry/' + geoLat + '/' + geoLon + '/100';
        console.log(nearby_ajax_url);
        var nearby_data = '';
        $.get(nearby_ajax_url, function (data, status) {
            clearMarkers(nearby_pins);
            nearby_data += '[ { "name": "NEARBY [' + (Object.keys(data).length) + ']", "id": 1, "children": [';
            $.each(data, function (key, val) {
                if (key != 0) {
                    nearby_data += ', ';
                }
                nearby_data += ' { "mt": "' + val.mt + '", "name": "' + val.nbn_st_addr + ' [' + val.count + ' @ ' + val.dist + 'm]", "id": ' + (key + 10000) + ' } ';
                var title_str = val.nbn_st_addr + ' [' + val.count + ' @ ' + val.dist + 'm : ' + val.tech + ']';
                nearby_pins[key] = addPin(val.geo.lat, val.geo.lon, title_str, val.serv_class, val.tech, val.mt, 2);

            });
            nearby_data += ' ] } ]';

            $('#nearby_pane').css('display', 'block');
            $('#nearby_tree').tree('destroy'); // need to destroy or else the event bindings get duplicated
            $('#nearby_tree').tree({data: jQuery.parseJSON(nearby_data), autoEscape: false});

            $('#nearby_tree').on(
                'tree.click',
                function (event) {
                    console.log('tree_clicked');
                    var node = event.node;
                    console.log(node.mt);
                    $('#suggest_input').val(node.mt).trigger('change');
                    $('#suggest_input').trigger(jQuery.Event('keyup', {keyCode: 8, which: 8}));
                    setTimeout(function () {
                        doSelectThings('nearby');
                    }, 300);
                }
            );
        });
    }

    function clearWelcome() {

        $('#welcome_pane').css('display', 'none');
    }

    function clearAtAddr() {

        $('#at_address_pane').css('display', 'none');
        $('#address_tree').tree('loadData', '');
    }

    function doSubmitThings(searchType) {

        var value = $('#suggest_input').val();
        $('#suggest_input').blur();
        var geocode_url = 'https://maps.googleapis.com/maps/api/geocode/json?address=' + value + '&region=au&key=AIzaSyDcE2tHUuIsXqNLwIgtoJ16D-N5b1F7XFM';
        console.log(geocode_url);
        $.get(geocode_url, function (data, status) {

            if (data.status == 'OK') {

                var found_geo = data.results[0].geometry.location;
                var found_addr = data.results[0].formatted_address;

                clearWelcome();
                updateResults(found_addr, 'Google Match');
                clearAtAddr();
                updateNearby(found_geo.lat, found_geo.lng);

                if (searchType == 'drag') {
                    var dragPos = found_pin[0].getPosition();
                    updateFoundPin(dragPos.lat(), dragPos.lng(), '');
                }
                else {
                    updateFoundPin(found_geo.lat, found_geo.lng, 'zoom');
                }
            }
            else {
                alert('No match found\n\n..which is weird because google finds almost anything.\n\nMaybe check your spelling and try again.');
            }


        });
    }

    function doSelectThings(searchType) {

        // this is what gets done when the user selects an item from the autosuggest
        if ((searchType == 'enter') || (searchType == 'nearby')) {
            geo_loc = $('#suggest_input').getItemData(0).geo;
            loc_str = $('#suggest_input').getItemData(0).loc;
            $('#suggest_input').val(loc_str).trigger('change');
            $('#suggest_input').blur();
            zoom_type = 'zoom';
        }
        else {
            geo_loc = $('#suggest_input').getSelectedItemData().geo;
            loc_str = $('#suggest_input').getSelectedItemData().loc;
        }

        var zoom_type = 'zoom';
        if (searchType == 'nearby') {
            zoom_type = '';
        }

        console.log(loc_str);
        loc_str = loc_str.split('|')[0];
        clearWelcome();
        updateResults(loc_str, 'LOC8 DB Match');
        updateAtAddr();
        updateNearby(geo_loc.lat, geo_loc.lon);
        updateFoundPin(geo_loc.lat, geo_loc.lon, zoom_type);
    }

    function orDash(val) {
        if (val == '') {
            val = '-';
        }
        return val;
    }

    //https://maps.googleapis.com/maps/api/geocode/json?address=1600+Amphitheatre+Parkway,+Mountain+View,+CA&key=AIzaSyDcE2tHUuIsXqNLwIgtoJ16D-N5b1F7XFM
</script>
<script async defer
        src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDcE2tHUuIsXqNLwIgtoJ16D-N5b1F7XFM&callback=initMap"></script>
</body>
</html>

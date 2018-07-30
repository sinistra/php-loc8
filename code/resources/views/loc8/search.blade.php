<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    <script src="code.jquery.com/jquery-1.11.2.min.js"></script>
    <script type="text/javascript" src="{{ URL::asset('js/jquery.easy-autocomplete.js') }}"></script>
    <script type="text/javascript" src="{{ URL::asset('js/tree.jquery.js') }}"></script>

    <link rel="stylesheet"
          href="https://fonts.googleapis.com/css?family=Roboto:regular,bold,italic,thin,light,bolditalic,black,medium&amp;lang=en">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Montserrat">

    <link rel="stylesheet" href="{{ URL::asset('css/easy-autocomplete.css') }}"/>
    <link rel="stylesheet" href="{{ URL::asset('css/easy-autocomplete.themes.css') }}"/>
    <link rel="stylesheet" href="{{ URL::asset('css/jqtree.css') }}"/>

    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.2/css/bootstrap.min.css"
          integrity="sha384-Smlep5jCw/wG7hdkwQ/Z5nLIefveQRIY9nfy6xoR1uRYBtpZgI6339F5dgvm/e9B" crossorigin="anonymous">

    <style>
        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: "Open Sans", "Helvetica Neue", Helvetica, Arial, sans-serif;
        }

        p, span {
            font-size: 11px;
        }

        p {
            padding: 0px;
            margin: 0px;
        }

        h3 {
            position: absolute;
            top: -7px;
            right: 30px;
        }

        h6 {
            color: red;
        }

        #suggest_input {
            color: black;
        }

        #suggest_container {
            color: black;
            width: 90%;
            margin-left: 5%;
            margin-top: 12px;
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

        #result_pane {
            display: none;
            border: 0px white solid;
            padding: 12px 20px 12px 20px;
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

        .pane_tree {
            padding: 7px 0px 0px 0px;
        }

        /* Always set the map height explicitly to define the size of the div
         * element that contains the map. */
        #map {
            height: 100%;
        }

    </style>

</head>
<body>

<div id="header_div">
    <div id="logo_div">
        <a href="/loc8"><img src="../images/logo-macquarie-telecom.png" style="height: 33px; margin: 8px 0px 0px 20px"></a>
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
                            style="font-size: 13px; padding: 2px 10px; border-radius: 0px 14px 14px 0px;">search
                    </button>
                </div>
            </div>

        </div>
    </div>
</div>

<div id="main_wrapper">
    <div id="left_pane_wrapper" style="overflow: auto;">
        <div id="welcome_pane">
            <p style="font-size: 14px; padding-bottom: 10px;">Welcome</p>
            <p>LOC-8 (pronounced locate) is here to help you match customer addresses to official (mostly NBN)
                servicable locations. Simply start typing in the search bar above to see match suggestions.</p>
            <br>
            <p>For help, questions, or feature suggestions you can contact michael from the locate team at;
                mhulowatyi@macquarietelecom.com.</p>
            <br>
            <p>All the best from the LOC-8 team.</p>
            <br>
            <p>LOC-8 responsibly!</p>
        </div>
        <div id="result_pane">
            <p style="font-size: 14px;">Result Details</p>
            <p>results overview will go here.</p>
        </div>
        <div id="at_address_pane">
            <p style="font-size: 14px;">At Found Address</p>
            <div id="address_tree" class="pane_tree"></div>
        </div>
        <div id="at_geo_pane">
            <p style="font-size: 14px;">At Found Geo-Location</p>
            <p>results at this geo location (not necessarily with the same base address) will go here.</p>
            <div id="geo_location_tree" class="pane_tree"></div>
        </div>
        <div id="nearby_pane">
            <p style="font-size: 14px;">Nearby Found Geo-Location</p>
            <div id="nearby_tree" class="pane_tree"></div>
        </div>
    </div>
    <div id="content_div">
        <div id="map"></div>
    </div>
</div>
<script>
    $(document).ready(function () {

        var dv_wth = $("#suggest_container").width() - 200;
        $(".easy-autocomplete").width(dv_wth);

        $(window).resize(function () {
            var dv_wth = $("#suggest_container").width() - 200;
            $(".easy-autocomplete").width(dv_wth);
        });

        // this is what gets done when the user hits search
        $("#suggest_button").click(function () {
            var value = $("#suggest_input").val();

            var geocode_url = "https://maps.googleapis.com/maps/api/geocode/json?address=" + value + "&key=AIzaSyDcE2tHUuIsXqNLwIgtoJ16D-N5b1F7XFM";
            $.get(geocode_url, function (data, status) {

                //alert(found_addr+'\n'+found_geo.lat+', '+found_geo.lng);

                $("#welcome_pane").css("display", "none");
                $("#nearby_pane").css("display", "block");
                //$("#at_address_pane").css("display", "block");
                //$("#result_pane").css("display", "block");
                //$("#at_geo_pane").css("display", "block");

                var found_addr = data.results[0].formatted_address;
                var found_geo = data.results[0].geometry.location;

                updateFoundPin(found_geo.lat, found_geo.lng);
                updateNearby(found_geo.lat, found_geo.lng);
                //updateAtAddr();

            });

        });

        $('#address_tree').tree({data: '', autoEscape: false});
        $('#nearby_tree').tree({data: '', autoEscape: false});

    });

    var markers = [];
    var geo_loc;

    var options = {
        url: function (phrase) {
            var search_type = "def";
            if ($('#subs_chk').is(":checked")) {
                search_type += "|subs";
            }
            if ($('#aliass_chk').is(":checked")) {
                search_type += "|aliass";
            }
            uri_str = "loc8/qry/" + safeUrl(phrase) + "/10/" + search_type;
            console.log(uri_str);
            return uri_str;
        },
        getValue: "loc",
        list: {
            maxNumberOfElements: 10,
            onClickEvent: function () {

                $("#welcome_pane").css("display", "none");
                $("#nearby_pane").css("display", "block");
                $("#at_address_pane").css("display", "block");
                //$("#result_pane").css("display", "block");
                //$("#at_geo_pane").css("display", "block");

                geo_loc = $("#suggest_input").getSelectedItemData().geo;

                updateFoundPin(geo_loc.lat, geo_loc.lon);
                updateNearby(geo_loc.lat, geo_loc.lon);
                updateAtAddr();

            }
        }
    };

    $("#suggest_input").easyAutocomplete(options);
</script>
<script>
    // google maps js
    var map;
    var marker;

    function initMap() {
        var myLatLng = {lat: -27.863, lng: 135.044};

        map = new google.maps.Map(document.getElementById('map'), {
            zoom: 5,
            center: myLatLng
        });
    }

    function addMyMarker(myLat, myLng, infoTxt, iconType) {

        //if (marker == undefined) { // ie. if this is the initial marker since page load
        var image1 = '../images/marker_black_filled.svg';
        var image2 = '../images/marker_black_hollow.svg';
        if (iconType == 1) {
            marker = new google.maps.Marker({
                position: {lat: parseFloat(myLat), lng: parseFloat(myLng)},
                map: map,
                draggable: true,
                icon: {
                    url: image1,
                    scaledSize: new google.maps.Size(32, 32),
                    anchor: new google.maps.Point(16, 32)
                }
            });
        }
        else if (iconType == 2) {
            marker = new google.maps.Marker({
                position: {lat: parseFloat(myLat), lng: parseFloat(myLng)},
                map: map,
                title: infoTxt,
                icon: {
                    url: image2,
                    scaledSize: new google.maps.Size(32, 32),
                    anchor: new google.maps.Point(16, 32)
                }
            });
        }
        else {
            marker = new google.maps.Marker({
                position: {lat: parseFloat(myLat), lng: parseFloat(myLng)},
                map: map,
                title: infoTxt,
                draggable: true,
                animation: google.maps.Animation.DROP
            });
        }

        return marker;
    }

    // Sets the map on all markers in the array.
    function setMapOnAll(map) {
        for (var i = 0; i < markers.length; i++) {
            markers[i].setMap(map);
        }
    }

    // Removes the markers from the map, but keeps them in the array.
    function clearMarkers() {
        setMapOnAll(null);
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
        var safe_str = phrase.replace(/\//g, '__');
        //safe_str = safeUrl(safe_str);
        return safe_str;
    }

    function updateFoundPin(geoLat, geoLon) {

        // put the pin on the pane and zoom in
        var foundLoc = new google.maps.LatLng(geoLat, geoLon);
        clearMarkers();
        map.setZoom(5);
        map.panTo(foundLoc); // using global map variable:
        markers[0] = addMyMarker(geoLat, geoLon, "", 1);
        setTimeout(function () {
            smoothZoom(map, 19, map.getZoom())
        }, 150);

    }

    function updateAtAddr() {

        // update at_address pane - assume there could be up to 5000 sub-addresses at a single address
        var base_hash = $("#suggest_input").getSelectedItemData().hash;
        var base_ajax_url = "/loc8/base_qry/" + base_hash + "/5000";
        console.log(base_ajax_url);
        var at_addr_data = '';
        $.get(base_ajax_url, function (data, status) {

            $.each(data, function (key, val) {
                if (key == 0) {
                    at_addr_data += '[ { "name": "' + val.nbn_st_addr + ',<br>' + val.nbn_suburb + ' [' + (Object.keys(data).length - 1) + ']", "id": 1, "children": [';
                }
                else {
                    if (key != 1) {
                        at_addr_data += ', ';
                    }
                    if (val.nbn_sub_addr == '-') {
                        at_addr_data += ' { "name": "' + val.nbn_loc + '", "id": ' + (key + 10000) + ' } ';
                    }
                    else {
                        at_addr_data += ' { "name": "' + val.nbn_sub_addr + '", "id": ' + (key + 1) + ', "children": [ { "name": "' + val.nbn_loc + '", "id": ' + +(key + 10000) + ' } ] } ';
                    }
                }
            });
            at_addr_data += ' ] } ]';
            $('#address_tree').tree('loadData', jQuery.parseJSON(at_addr_data));

        });
    }

    function updateNearby(geoLat, geoLon) {

        // update nearby pane
        var nearby_ajax_url = "/loc8/nearby_qry/" + geoLat + "/" + geoLon + "/50";
        console.log(nearby_ajax_url);
        var nearby_data = '';
        $.get(nearby_ajax_url, function (data, status) {

            console.log(nearby_ajax_url);
            nearby_data += '[ { "name": "NEARBY [' + (Object.keys(data).length) + ']", "id": 1, "children": [';
            $.each(data, function (key, val) {
                if (key != 0) {
                    nearby_data += ', ';
                }
                nearby_data += ' { "name": "' + val.nbn_st_addr + ' [' + val.count + ' @ ' + val.dist + 'm]", "id": ' + (key + 10000) + ' } ';
                markers[key + 1] = addMyMarker(val.geo.lat, val.geo.lon, val.nbn_st_addr, 2);

            });
            nearby_data += ' ] } ]';
            //console.log('nearby:'+nearby_data);
            $('#nearby_tree').tree('loadData', jQuery.parseJSON(nearby_data));

        });
    }

    //https://maps.googleapis.com/maps/api/geocode/json?address=1600+Amphitheatre+Parkway,+Mountain+View,+CA&key=AIzaSyDcE2tHUuIsXqNLwIgtoJ16D-N5b1F7XFM
</script>
<script async defer
        src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDcE2tHUuIsXqNLwIgtoJ16D-N5b1F7XFM&callback=initMap"></script>
</body>
</html>

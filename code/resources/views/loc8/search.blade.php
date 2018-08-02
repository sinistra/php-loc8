<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    <script src="//code.jquery.com/jquery-1.11.2.min.js"></script>
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

        /* Always set the map height explicitly to define the size of the div
         * element that contains the map. */
        #map {
            height: 100%;
        }

    </style>

    <style>
        .modal {
            position: fixed;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            opacity: 0;
            visibility: hidden;
            transform: scale(1.1);
            transition: visibility 0s linear 0.25s, opacity 0.25s 0s, transform 0.25s;
        }
        .modal-content {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: white;
            padding: 1rem 1.5rem;
            width: 24rem;
            border-radius: 0.5rem;
        }
        .close-button {
            float: right;
            width: 1.5rem;
            line-height: 1.5rem;
            text-align: center;
            cursor: pointer;
            border-radius: 0.25rem;
            background-color: lightgray;
        }
        .close-button:hover {
            background-color: darkgray;
        }
        .show-modal {
            display: block;
            opacity: 1;
            visibility: visible;
            transform: scale(1.0);
            transition: visibility 0s linear 0s, opacity 0.25s 0s, transform 0.25s;
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
            <p style="font-size: 15px; padding-bottom: 10px;">Welcome</p>
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
        <div id="results_pane">
            <p style="font-size: 15px;">Search Results</p>
            <div id="results_area"></div>
            <button class="trigger">Click here to trigger the modal!</button>
        </div>
        <div id="at_address_pane">
            <p style="font-size: 15px;">At Found Address</p>
            <div id="address_tree" class="pane_tree"></div>
        </div>
        <div id="at_geo_pane">
            <p style="font-size: 15px;">At Found Geo-Location</p>
            <p>results at this geo location (not necessarily with the same base address) will go here.</p>
            <div id="geo_location_tree" class="pane_tree"></div>
        </div>
        <div id="nearby_pane">
            <p style="font-size: 15px;">Nearby Found Geo-Location</p>
            <div id="nearby_tree" class="pane_tree"></div>
        </div>
    </div>
    <div id="content_div">
        <div id="map"></div>
    </div>
</div>
<div class="modal">
    <div class="modal-content">
        <span class="close-button">×</span>
        <h1>Hello, I am a modal!</h1>
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

        // this is what gets done when the user hits the enter key
        $('#suggest_input').keypress(function (e) {
            if (e.which == 13) {

                loc_str = $("#suggest_input").getItemData(0).loc;
                doSelectThings('scripted');
            }
        });

        // this is what gets done when the user hits search
        $('#suggest_button').click(function () {
            doSubmitThings('text');
        });

        $('#address_tree').tree({data: '', autoEscape: false});
        $('#nearby_tree').tree({data: '', autoEscape: false});

        var modal = document.querySelector(".modal");
        var trigger = document.querySelector(".trigger");
        var closeButton = document.querySelector(".close-button");

        function toggleModal() {
            console.log("Showing Modal");
            modal.classList.toggle("show-modal");
        }

        function windowOnClick(event) {
            if (event.target === modal) {
                toggleModal();
            }
        }

        trigger.addEventListener("click", toggleModal);
        closeButton.addEventListener("click", toggleModal);
        window.addEventListener("click", windowOnClick);


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
            uri_str = "/loc8/qry/" + safeUrl(phrase) + "/10/" + search_type;
            console.log(uri_str);
            return uri_str;
        },
        getValue: "loc",
        list: {
            maxNumberOfElements: 10,
            onClickEvent: function () {
                doSelectThings('clicked');
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
                optimized: false,
                zIndex: 9999999,
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
                    scaledSize: new google.maps.Size(24, 24),
                    anchor: new google.maps.Point(12, 24)
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

    function updateFoundPin(geoLat, geoLon, searchType) {

        // put the pin on the pane and zoom in
        var foundLoc = new google.maps.LatLng(geoLat, geoLon);
        clearMarkers();

        if (searchType == 'pin_drag') {
            map.panTo(foundLoc);
        }
        else {
            map.setZoom(5);
            map.panTo(foundLoc); // using global map variable:
            setTimeout(function () {
                smoothZoom(map, 19, map.getZoom())
            }, 150);
        }

        markers[0] = addMyMarker(geoLat, geoLon, "", 1);

        google.maps.event.addListener(markers[0], 'dragend', function () {
            var dragPos = markers[0].getPosition();
            var dragPos_txt = dragPos.lat() + ', ' + dragPos.lng();
            console.log(dragPos_txt);
            $('#suggest_input').val(dragPos_txt);
            doSubmitThings('pin_drag');
            $("#suggest_input").blur();
        });
    }

    function updateResults(foundAddr, matchType) {

        // update the result pane
        foundAddr = foundAddr.replace(", ", ",<br>");
        var res_area_html = "<p class='results_h1'>Found Address:</p><p class='results'>" + foundAddr + "</p>";
        res_area_html += "<p class='results_h1'>Match Type:</p><p class='results'>" + matchType + "</p>"
        $("#results_pane").css("display", "block");
        $("#results_area").html(res_area_html);
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
            $("#at_address_pane").css("display", "block");
            $('#address_tree').tree('loadData', jQuery.parseJSON(at_addr_data));

        });
    }

    function updateNearby(geoLat, geoLon) {

        // update nearby pane and nearby pins
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
                nearby_data += ' { "mt": "' + val.mt + '", "name": "' + val.nbn_st_addr + ' [' + val.count + ' @ ' + val.dist + 'm]", "id": ' + (key + 10000) + ' } ';
                var title_str = val.nbn_st_addr + ' [' + val.count + ' @ ' + val.dist + 'm]';
                markers[key + 1] = addMyMarker(val.geo.lat, val.geo.lon, title_str, 2);

            });
            nearby_data += ' ] } ]';
            $("#nearby_pane").css("display", "block");
            $('#nearby_tree').tree('loadData', jQuery.parseJSON(nearby_data));

            $('#nearby_tree').on(
                'tree.click',
                function (event) {
                    var node = event.node;
                    $('#suggest_input').focus();
                    //$('#suggest_input').val('');
                    $('#suggest_input').val('3-5 webb').trigger("change");
                    $("#suggest_input").trigger( jQuery.Event( 'keyup', { keyCode: 8, which: 8 } ) );
                    // $("#suggest_input").easyAutocomplete('search', 'demo-value');
                    //$('#suggest_input').getItemData();
                    // $("#suggest_input").trigger("change");
                    // $("#suggest_input").easyAutocomplete(options);
                    // $("#suggest_input").keypress('3');
                    //$("#suggest_input").val('MT'+node.mt);
                    //$(window).keypress(" ");
                    //$(window).keypress("|");
                    //$("#suggest_input").getItems();
                    //$("#suggest_input").keypress();
                    //$("#suggest_input").trigger("change");
                    //$("#suggest_input").val('MT'+node.mt).trigger("change");
                    //$('#suggest_input').focus();
                    //loc_str = $("#suggest_input").getItemData(0).loc;
                    //setTimeout(function(){
                    //		loc_str = $("#suggest_input").getItemData(0).loc;
                    //		alert(loc_str);
                    //	$("#suggest_input").trigger("change");
                    //}, 500);

                    //simulateKeyPress("m");
                    //simulateKeyPress("t");

                    //alert(loc_str);
                    //$("#suggest_input").val('MT'+node.mt).trigger("change");
                    //setTimeout(function(){doSelectThings('scripted')}, 500);
                    //doSelectThings('scripted');
                }
            );

        });
    }

    function clearWelcome() {

        $("#welcome_pane").css("display", "none");
    }

    function clearAtAddr() {

        $("#at_address_pane").css("display", "none");
        $('#address_tree').tree('loadData', '');
    }

    function doSubmitThings(searchType) {

        var value = $("#suggest_input").val();
        var geocode_url = "https://maps.googleapis.com/maps/api/geocode/json?address=" + value + "&key=AIzaSyDcE2tHUuIsXqNLwIgtoJ16D-N5b1F7XFM";
        $.get(geocode_url, function (data, status) {

            var found_addr = data.results[0].formatted_address;
            var found_geo = data.results[0].geometry.location;

            clearWelcome();
            updateResults(found_addr, "Google Match");
            clearAtAddr();
            updateNearby(found_geo.lat, found_geo.lng);

            if (searchType == "pin_drag") {
                var dragPos = markers[0].getPosition();
                updateFoundPin(dragPos.lat(), dragPos.lng(), 'pin_drag');
            }
            else {
                updateFoundPin(found_geo.lat, found_geo.lng, 'txt_search');
            }
        });
    }

    function doSelectThings(searchType) {

        // this is what gets done when the user selects an item from the autosuggest
        if (searchType == "scripted") {
            geo_loc = $("#suggest_input").getItemData(0).geo;
            loc_str = $("#suggest_input").getItemData(0).loc;
            $("#suggest_input").val(loc_str).trigger("change");
            $('#suggest_input').blur();
        }
        else {
            geo_loc = $("#suggest_input").getSelectedItemData().geo;
            loc_str = $("#suggest_input").getSelectedItemData().loc;
        }

        loc_str = loc_str.split('|')[0];
        clearWelcome();
        updateResults(loc_str, "NBN PFL Match");
        updateAtAddr();
        updateNearby(geo_loc.lat, geo_loc.lon);
        updateFoundPin(geo_loc.lat, geo_loc.lon, 'txt_search');
    }

    //https://maps.googleapis.com/maps/api/geocode/json?address=1600+Amphitheatre+Parkway,+Mountain+View,+CA&key=AIzaSyDcE2tHUuIsXqNLwIgtoJ16D-N5b1F7XFM
</script>
<script async defer
        src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDcE2tHUuIsXqNLwIgtoJ16D-N5b1F7XFM&callback=initMap"></script>
</body>
</html>

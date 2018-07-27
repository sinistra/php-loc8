<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    <script src="//code.jquery.com/jquery-1.11.2.min.js"></script>
    <script type="text/javascript" src="{{ URL::asset('js/jquery.easy-autocomplete.js') }}"></script>
    <script type="text/javascript" src="{{ URL::asset('js/bootstrap-treeview.js') }}"></script>

    <link rel="stylesheet"
          href="https://fonts.googleapis.com/css?family=Roboto:regular,bold,italic,thin,light,bolditalic,black,medium&amp;lang=en">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Montserrat">

    <link rel="stylesheet" href="{{ URL::asset('css/easy-autocomplete.css') }}"/>
    <link rel="stylesheet" href="{{ URL::asset('css/easy-autocomplete.themes.css') }}"/>

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
            margin-left: 5%;
        }

        h3 {
            position: absolute;
            top: -7px;
            right: 30px;
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
            width: 320px;
            height: 98%;
            border: 0px pink solid;
        }

        #searchbar_div {
            float: right;
            width: calc(100% - 320px);
            height: 100%;
            border: 0px cyan solid;
        }

        #main_wrapper {
            width: 100%;
            height: calc(100% - 50px);
            border: 0px red solid;
        }

        #left_pane_div {
            font-family: Montserrat;
            font-size: 10px;
            color: white;
            background: #292929;
            float: left;
            width: 320px;
            height: 100%;
            border: 0px green solid;
        }

        #content_div {
            float: right;
            width: calc(100% - 320px);
            height: 100%;
            border: 0px orange solid;
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
        <img src="../images/logo-macquarie-telecom.png" style="height: 33px; margin: 8px 0px 0px 20px">
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
                    <div class="input-group-text" style="padding : 0px 3px; height: 26px;">
                        <input type="checkbox" aria-label="checkbox" style="margin-top: 3px;">
                        <span style="font-size: 9px;">&nbsp; sub locs</span>
                    </div>
                    <div class="input-group-text" style="padding : 0px 3px; height: 26px;">
                        <input type="checkbox" aria-label="checkbox" style="margin-top: 3px;">
                        <span style="font-size: 9px;">&nbsp; alias locs</span>

                    </div>
                    <button id="suggest_button" class="btn btn-outline-secondary" type="button"
                            style="font-size: 13px; padding: 2px 10px;">search
                    </button>
                </div>
            </div>

        </div>
    </div>
</div>

<div id="main_wrapper">
    <div id="left_pane_div" style="overflow: auto; padding-top: 10px;">
        <div style="padding: 20px 40px 10px 20px;">
            <p style="font-size: 14px;"">Welcome</p>
            <p>loc8 (pronounced locate) is here to help you match customer addresses to official (mostly NBN) servicable
                locations.</p>
            <p>start typing in the search bar above to see match suggestions.</p>
            <p>for help, questions, or suggestions you can contact michael from the locate team at;
                mhulowatyi@macquarietelecom.com.</p>
            <p>all the best from the loc8 team.</p>
            <p>loc8 responsibly!</p>
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

        $("#suggest_button").click(function () {
            var value = $("#suggest_input").val();
            alert(value);
        });
    });

    var options = {
        url: function (phrase) {
            var search_type = "base_addr";
            return "http://localhost/loc8/qry/" + phrase + "/10/" + search_type;
        },
        getValue: "loc",
        list: {
            onClickEvent: function () {
                var geo_loc = $("#suggest_input").getSelectedItemData().geo;
                var foundLoc = new google.maps.LatLng(geo_loc.lat, geo_loc.lon);

                // do google maps stuff
                map.setZoom(5);
                map.panTo(foundLoc); // using global map variable:
                var myMarker = addMyMarker(geo_loc.lat, geo_loc.lon, "123");
                setTimeout(function () {
                    smoothZoom(map, 19, map.getZoom())
                }, 150);

                // update side pane
                var base_hash = $("#suggest_input").getSelectedItemData().hash;
                var ajax_url = "/loc8/base_qry/" + base_hash + "/1000";
                var at_addr_str = "";
                $.get(ajax_url, function (data, status) {
                    $.each(data, function (key, val) {
                        console.log(Object.keys(data).length);
                        if (key == 0) {
                            at_addr_str += "<span style='padding-left: 10px'>" + val.nbn_addr + "</span><span> [" + (Object.keys(data).length - 1) + "]</span><ul>";
                        }
                        else {
                            at_addr_str += "<li><span style='color: white'>" + val.nbn_addr + "</span><span style='color: #ababab'> [" + val.nbn_loc + "]</span></li>";
                        }
                    });
                    at_addr_str += "</ul>"
                    $("#left_pane_div").html($(at_addr_str));
                });


            }
        }
    };

    $("#suggest_input").easyAutocomplete(options);
</script>
<script>

    var map;
    var marker;

    function initMap() {
        var myLatLng = {lat: -27.863, lng: 135.044};

        map = new google.maps.Map(document.getElementById('map'), {
            zoom: 5,
            center: myLatLng
        });
    }

    function addMyMarker(myLat, myLng, infoTxt) {
        if (marker == undefined) { // ie. if this is the initial marker since page load
            marker = new google.maps.Marker({
                position: {lat: parseFloat(myLat), lng: parseFloat(myLng)},
                map: map,
                draggable: true,
                animation: google.maps.Animation.DROP
            });
        }
        else {
            marker.setPosition(new google.maps.LatLng(parseFloat(myLat), parseFloat(myLng)));
        }
        return marker;
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
</script>

<script async defer
        src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDcE2tHUuIsXqNLwIgtoJ16D-N5b1F7XFM&callback=initMap"></script>
</body>
</html>

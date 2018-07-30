<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    <script src="code.jquery.com/jquery-1.11.2.min.js"></script>
    <script type="text/javascript" src="{{ URL::asset('js/jquery.easy-autocomplete.js') }}"></script>
    <link rel="stylesheet" href="{{ URL::asset('css/easy-autocomplete.css') }}"/>
    <link rel="stylesheet" href="{{ URL::asset('css/easy-autocomplete.themes.css') }}"/>
    <style>
        body {
            font-family: "Open Sans", "Helvetica Neue", Helvetica, Arial, sans-serif;
        }

        #suggest_input {
            width: 96%;
            margin-top: 10px;
        }

        #suggest_container {
            width: 92%;
            margin-left: 5%;
            margin-top: 15px;
        }

        p, span {
            font-size: 9pt;
        }

        p {
            margin-left: 5%;
        }

        h3 {
            position: absolute;
            top: -7px;
            right: 30px;
        }

        /* Always set the map height explicitly to define the size of the div
         * element that contains the map. */
        #map {
            height: 85%;
        }

        /* Optional: Makes the sample page fill the window. */
        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
        }
    </style>

</head>
<body>
<h3>loc8 demo</h3>
<div id="suggest_container">
    <select id="suggest_dropdown" class="form-control">
        <option value="base_addr">base address</option>
        <option value="default">default</option>
    </select><span>&nbsp; search type</span>
    <input id="suggest_input"/>
</div>
<br>
<div id="map"></div>

<script>
    var options = {
        url: function (phrase) {
            var search_type = $("#suggest_dropdown").val();
            return "loc8/qry/" + phrase + "/10/" + search_type;
        },
        getValue: "loc",
        list: {
            onClickEvent: function () {
                var value = $("#suggest_input").getSelectedItemData().loc;
                alert(value);
            }
        }
    };

    $("#suggest_input").easyAutocomplete(options);
</script>
<script>

    function initMap() {
        var myLatLng = {lat: -25.363, lng: 131.044};

        var map = new google.maps.Map(document.getElementById('map'), {
            zoom: 4,
            center: myLatLng
        });

        var marker = new google.maps.Marker({
            position: myLatLng,
            map: map,
            title: 'Hello World!'
        });
    }
</script>
<script async defer
        src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDcE2tHUuIsXqNLwIgtoJ16D-N5b1F7XFM&callback=initMap">
</script>
</body>
</html>

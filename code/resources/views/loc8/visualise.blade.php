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
            font-size: 11px;
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
    </style>

</head>
<body>

<div id="header_div">
    <div id="logo_div">
        <a href="/loc8"><img src="../images/logo-macquarie-telecom.png" style="height: 33px; margin: 8px 0px 0px 20px"></a>
        <div style=" float: right; margin: 6px 10px 0px 0px">
            <span style="color: #dedede; font-size: 18pt; font-weight: 200; font-style: normal; letter-spacing: 2px;">LOC-8</span>
        </div>
    </div>
    <div id="searchbar_div">
    </div>
</div>

<div id="main_wrapper">
    <iframe src=":5601/app/kibana#/visualize/create?embed=true&_g=()&_a=(filters:!(),linked:!f,query:(language:lucene,query:''),uiState:(mapCenter:!(-33.88951249697183,151.23504638671878),mapZoom:12),vis:(aggs:!((enabled:!t,id:'2',params:(),schema:metric,type:count),(enabled:!t,id:'3',params:(autoPrecision:!t,field:geo_location,isFilteredByCollar:!t,precision:6,useGeocentroid:!t),schema:segment,type:geohash_grid)),params:(addTooltip:!t,heatClusterSize:1.5,isDesaturated:!t,legendPosition:bottomright,mapCenter:!(0,0),mapType:'Scaled+Circle+Markers',mapZoom:2,wms:(baseLayersAreLoaded:(),enabled:!f,options:(format:image%2Fpng,transparent:!t),selectedTmsLayer:(attribution:'%3Cp%3E%26%23169;+%3Ca+href%3D%22http:%2F%2Fwww.openstreetmap.org%2Fcopyright%22%3EOpenStreetMap%3C%2Fa%3E+contributors+%7C+%3Ca+href%3D%22https:%2F%2Fwww.elastic.co%2Felastic-maps-service%22%3EElastic+Maps+Service%3C%2Fa%3E%3C%2Fp%3E%26%2310;',id:road_map,maxZoom:18,minZoom:0,subdomains:!(),url:'https:%2F%2Ftiles.maps.elastic.co%2Fv2%2Fdefault%2F%7Bz%7D%2F%7Bx%7D%2F%7By%7D.png%3Felastic_tile_service_tos%3Dagree%26my_app_name%3Dkibana%26my_app_version%3D6.2.3%26license%3D9f8f9972-bdb6-4144-8ed5-f9c4010c4d47'),tmsLayers:!((attribution:'%3Cp%3E%26%23169;+%3Ca+href%3D%22http:%2F%2Fwww.openstreetmap.org%2Fcopyright%22%3EOpenStreetMap%3C%2Fa%3E+contributors+%7C+%3Ca+href%3D%22https:%2F%2Fwww.elastic.co%2Felastic-maps-service%22%3EElastic+Maps+Service%3C%2Fa%3E%3C%2Fp%3E%26%2310;',id:road_map,maxZoom:18,minZoom:0,subdomains:!(),url:'https:%2F%2Ftiles.maps.elastic.co%2Fv2%2Fdefault%2F%7Bz%7D%2F%7Bx%7D%2F%7By%7D.png%3Felastic_tile_service_tos%3Dagree%26my_app_name%3Dkibana%26my_app_version%3D6.2.3%26license%3D9f8f9972-bdb6-4144-8ed5-f9c4010c4d47')))),title:'',type:tile_map))&indexPattern=001cbf60-840f-11e8-a2c7-e9968d18ffe2&type=tile_map"
            height="100%" width="100%">

    </iframe>
</div>


<script async defer
        src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDcE2tHUuIsXqNLwIgtoJ16D-N5b1F7XFM&callback=initMap"></script>
</body>
</html>

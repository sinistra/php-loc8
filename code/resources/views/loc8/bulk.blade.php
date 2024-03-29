<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.1.2/js/bootstrap.min.js"></script>
    <script type="text/javascript" src="{{ URL::asset('js/datatables.js') }}"></script>

    <?php
        function autoversion($url) {
            $filename = $url;
            if (file_exists($filename)) {
                // echo "$filename was last modified: " . date ("FdYHis", filemtime($filename));
                $date = "?v=" . date ("dYHis", filemtime($filename));
                echo URL::asset($filename) . $date;
            }
        }
    ?>

    <link rel="stylesheet" href="{{ autoversion('css/app.css') }}"/>

    <link rel="stylesheet"
          href="https://fonts.googleapis.com/css?family=Roboto:regular,bold,italic,thin,light,bolditalic,black,medium&amp;lang=en">
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700" rel="stylesheet">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.4.1/css/all.css"
          integrity="sha384-5sAR7xN1Nv6T6+dT2mhtzEpVJvfS3NScPQTrOxhwjIuvcA67KV2R5Jz6kr4abQsz" crossorigin="anonymous">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.2/css/bootstrap.min.css"
          integrity="sha384-Smlep5jCw/wG7hdkwQ/Z5nLIefveQRIY9nfy6xoR1uRYBtpZgI6339F5dgvm/e9B" crossorigin="anonymous">
    <link rel="stylesheet" href="{{ URL::asset('css/datatables.css') }}">



    <style>
        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: "HelveticaNeue", sans-serif;
            text-rendering: optimizeLegibility;
            -webkit-font-smoothing: subpixel-antialiased;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        table {
            font-size: 12px;
        }

        th {
            background: #292929;
            color: #ffffff;
            font-family: 'HelveticaNeue', sans-serif;
            letter-spacing: 1px;
            font-weight: 300;
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

        #map_btn {
            opacity: 0.75;
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

        .stats {
            display: inline-block;
            width: 85px;
            font-size: 16pt;
            font-weight: 300;
            font-style: normal;
            letter-spacing: 2px;
        }

        .stats_div {
            height: 32px;
        }

        #main_wrapper {
            width: 100%;
            height: 100%;
            border: 0px red solid;
        }

        #start_btn {
            width: 204px;
        }

        #left_pane_wrapper {
            letter-spacing: 1px;
            font-weight: 300;
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
            border: 0px white solid;
            border-top: 1px #545454 solid;
            padding: 10px 20px 12px 20px;
        }

        #results_area {
            margin-left: 10px;
        }

        #content_div {
            float: right;
            width: calc(100% - 320px);
            height: 100%;
            max-height: 100%;
            border: 0px orange solid;
            padding: 17px 20px;
            font-size: 13px;
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
            font-weight: 300;
            font-size: 11px;
            color: white;
            background: #292929;
        }

        .modal-title {
            letter-spacing: 1px;
            font-weight: 300;
            font-size: 15px;
            font-family: 'HelveticaNeue', sans-serif;
        }

        .modal-body {
            height: 60%;
            overflow-y: auto;
        }

        .side_btn {
            font-size: 11px;
            font-family: 'HelveticaNeue', sans-serif;
            letter-spacing: 1px;
            font-weight: 300;
            background: #292929;
            color: #ffffff;
            border-radius: 14px;
            padding: 3px 16px;
            margin-right: 5px;
        }

        .side_btn:hover {
            color: #3bd869;
            border-color: #3bd869;
        }

        #run_status {
            border-radius: 14px;
            border: 0px;
            padding: 3px 15px;
            width: 70px;
        }

        #input_form {
            font-size: 11px;
            font-family: 'HelveticaNeue', sans-serif;
            letter-spacing: 1px;
            font-weight: 400;
        }

        .btn {
            font-size: 11px;
            font-family: 'HelveticaNeue', sans-serif;
            letter-spacing: 1px;
            font-weight: 300;
            margin-right: 3px;
            border-color: #ced4da;
            border-radius: 14px;
            padding: 4px 10px;
            background: #ffffff;
            color: #000000;
        }

        .btn:hover {
            background: #ffffff;
            color: #3bd869;
            border-color: #3bd869;
        }

        .dropdown-item {
            font-size: 10px;
        }

        .grid_link {
            padding-left: 15px;
            color: black;
        }

        .grid_link:visited {
            color: #3bd869;
        }

        #grid_filter {
            right: 168px;
            top: 15px;
            position: absolute;
            width: 100px;
        }

        div.dataTables_wrapper div.dataTables_filter input {
            border-radius: 14px;
            padding: 1px 10px;
            width: 167px;
        }

        .btn-group > .btn-group:not(:last-child) > .btn, .btn-group > .btn:not(:last-child):not(.dropdown-toggle) {
            border-radius: 14px;
            margin-left: 0px;
        }

        .btn-group > .btn-group:not(:first-child) > .btn, .btn-group > .btn:not(:first-child) {
            border-radius: 14px;
            margin-left: 0px;
        }

        .dropdown-item.active {
            background: #e0e0e0;
            color: black;
        }

        .dropdown-item:active {
            background: #e0e0e0;
        }

        div.dt-button-collection-title {
            padding: 0px;
        }

        .dataTables_filter {
            font-size: 11px;
            font-family: 'HelveticaNeue', sans-serif;
            letter-spacing: 1px;
            font-weight: 300;
        }

        .score_100 {
            width: 50px;
            display: inline-block;
            color: black;
            border: 1px #3bd869 solid;
            color: #3bd869;
            border-radius: 14px;
            text-align: center;
        }

        .score_85 {
            width: 50px;
            display: inline-block;
            color: black;
            border: 1px #a5c300 solid;
            color: #a5c300;
            border-radius: 14px;
            text-align: center;
        }

        .score_60 {
            width: 50px;
            display: inline-block;
            color: black;
            border: 1px #cfd604 solid;
            color: #cfd604;
            border-radius: 14px;
            text-align: center;
        }

        .score_20 {
            width: 50px;
            display: inline-block;
            color: black;
            border: 1px #dc5405 solid;
            color: #dc5405;
            border-radius: 14px;
            text-align: center;
        }

        .score_0 {
            width: 50px;
            display: inline-block;
            color: black;
            border: 1px red solid;
            color: red;
            border-radius: 14px;
            text-align: center;
        }

        .FTTC_tech {
            width: 90px;
            display: inline-block;
            color: white;
            background: #ca8829;
            border-radius: 14px;
            text-align: center;
        }

        .Wireless_tech {
            width: 90px;
            display: inline-block;
            color: white;
            background: #247b35;
            border-radius: 14px;
            text-align: center;
        }

        .FTTN_tech {
            width: 90px;
            display: inline-block;
            color: white;
            background: #5a0e62;
            border-radius: 14px;
            text-align: center;
        }

        .FTTP_tech {
            width: 90px;
            display: inline-block;
            color: white;
            background: #dfd906;
            border-radius: 14px;
            text-align: center;
        }

        .HFC_tech {
            width: 90px;
            display: inline-block;
            color: white;
            background: #2e4294;
            border-radius: 14px;
            text-align: center;
        }

        .FTTB_tech {
            width: 90px;
            display: inline-block;
            color: white;
            background: #b6263a;
            border-radius: 14px;
            text-align: center;
        }

        .Hollow_tech {
            width: 90px;
            display: inline-block;
            color: black;
            border: 1px black solid;
            border-radius: 14px;
            text-align: center;
        }

        button:focus {
            outline-width: 0px;
        }

        input:focus {
            outline-width: 0px;
            border-color: #3bd869;
            box-shadow: none;
        }

        .form-control:focus {
            outline-width: 0px;
            border-color: #3bd869;
            -webkit-box-shadow: none;
            -moz-box-shadow: none;
            box-shadow: none;
        }

    </style>
    <style type="text/css">
        /* spinner css from http://tobiasahlin.com/spinkit/ */
        .spinner {
            margin: 10px auto;
            width: 40px;
            height: 40px;
            position: relative;
            text-align: center;

            -webkit-animation: sk-rotate 2.0s infinite linear;
            animation: sk-rotate 2.0s infinite linear;
        }

        .dot1, .dot2 {
            width: 60%;
            height: 60%;
            display: inline-block;
            position: absolute;
            top: 0;
            background-color: #eaeaea;
            border-radius: 100%;

            -webkit-animation: sk-bounce 2.0s infinite ease-in-out;
            animation: sk-bounce 2.0s infinite ease-in-out;
        }

        .dot2 {
            top: auto;
            bottom: 0;
            -webkit-animation-delay: -1.0s;
            animation-delay: -1.0s;
        }

        @-webkit-keyframes sk-rotate {
            100% {
                -webkit-transform: rotate(360deg)
            }
        }

        @keyframes sk-rotate {
            100% {
                transform: rotate(360deg);
                -webkit-transform: rotate(360deg)
            }
        }

        @-webkit-keyframes sk-bounce {
            0%, 100% {
                -webkit-transform: scale(0.0)
            }
            50% {
                -webkit-transform: scale(1.0)
            }
        }

        @keyframes sk-bounce {
            0%, 100% {
                transform: scale(0.0);
                -webkit-transform: scale(0.0);
            }
            50% {
                transform: scale(1.0);
                -webkit-transform: scale(1.0);
            }
        }
    </style>
</head>
<body>

<div id="main_wrapper">
    <div id="left_pane_wrapper">
        <div id="header_div">
            <div id="logo_div">
                <a href="/loc8"><img src="../images/logo-macquarie-telecom.png"
                                     style="height: 33px; margin: 8px 0px 0px 20px"></a>
                <div style=" float: right; margin: 6px 10px 0px 0px">
                    <span style="color: #dedede; font-size: 18pt; font-weight: 200; font-style: normal; letter-spacing: 2px;">LOC-8</span>
                </div>
            </div>
        </div>
        <div style="overflow: auto; height: calc(100% - 50px);">
            <div id="welcome_pane">
                <p style="font-size: 16px; padding-bottom: 10px;">Bulk Search</p>
                <br>
                <div>
                    <button type="button" id="show_input_btn" class="side_btn">input</button>
                    <button type="button" id="show_res_btn" class="side_btn">results</button>
                    <button type="button" id="stop_btn" class="side_btn">stop</button>
                </div>
                <br>
                <div>
                    <button type="button" id="start_btn" class="side_btn" style="padding-left: 86px; padding-right: 86px;">
                        start
                    </button>
                </div>
                <br><br>
                <div><input type="text" id="run_status" value="stopped" disabled></div>
                <br><br>
                <div id="loader_div" class="spinner" style="visibility: hidden;">
                    <div class="dot1"></div>
                    <div class="dot2"></div>
                </div>
            </div>
            <div id="results_pane">
                <p style="font-size: 16px;">Search Results</p>
                <div id="results_area">
                    <br>
                    <div class="stats_div"><span id="records" class="stats">0</span><span>records</span></div>
                    <div class="stats_div"><span id="perc_compl" class="stats">0</span><span>% complete</span></div>
                    <div class="stats_div"><span id="perc_base" class="stats">0</span><span>% base matched</span></div>
                    <div class="stats_div"><span id="perc_sub" class="stats">0</span><span>% sub matched</span></div>
                    <div class="stats_div"><span id="elapsed" class="stats">0</span><span>sec duration</span></div>
                    <button type="button" id="map_btn" class="side_btn" onclick="location.href='/loc8/';"
                            style=" position: absolute; left: 248px; bottom: 15px; border-radius: 14px 0px 0px 14px; padding: 4px 16px; ">
                        <i class="fas fa-angle-double-left"></i> map
                    </button>
                </div>
            </div>
        </div>
    </div>
    <div id="content_div">
        <div id="res_div">
            <table id="grid" class="table table-striped table-bordered table-hover"></table>
        </div>
        <div id="input_div">
            <div id="input_form" border="1" width="100%">
                <span>To match addresses in bulk simply paste your data in the box below and then hit 'start'.</span>
                <br><span>.. data can be either;</span>
                <ul>
                    <li>A list of UIDs and Addresses (<i>in that order - tab separated</i>)</li>
                    <li>A list of Addresses only</li>
                </ul>
                <textarea id="text_data"
                          style="font-family: courier; width:100%; min-height: calc(100vh - 120px); font-size: 10pt;"></textarea>
            </div>
        </div>
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
                    <button type="button" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
    $(document).ready(function () {

        var modal_ht = $(window).height() - 200;
        $('.modal-body').height(modal_ht);

        var grid_ht = $(window).height() - 135;
        $('.dataTables_scrollBody').height(grid_ht);

        $(window).resize(function () {
            var modal_ht = $(window).height() - 200;
            $('.modal-body').height(modal_ht);

            var grid_ht = $(window).height() - 135;
            $('.dataTables_scrollBody').height(grid_ht);
        });

        var textarea_pre = ' so much empty..\n\n\n <-- the start button is over there';
        $('#text_data').val(textarea_pre);


        $('#text_data').click(function () {
            if ($('#text_data').val() == textarea_pre) {
                $('#text_data').val('');
            }
        });

        $('#text_data').blur(function () {
            if ($('#text_data').val() == '') {
                $('#text_data').val(textarea_pre);
            }
        });

        $("#res_div").hide();

        $("#show_input_btn").click(function () {
            $("#res_div").hide();
            $("#input_div").show();
            $("#run_status").val("stopped");
            xhide($("#loader_div"));
        });

        $("#show_res_btn").click(function () {
            $("#input_div").hide();
            $("#res_div").show();
        });

        $("#stop_btn").click(function () {
            $("#run_status").val("stopped");
            xhide($("#loader_div"));
        });

        $("#start_btn").click(function () {
            if ($('#text_data').val() != textarea_pre) {
                $("#run_status").val("running");
                xshow($("#loader_div"));
                $("#input_div").hide();
                $("#res_div").show();

                // this is to reset the results next time start is clicked
                var table = $('#grid').DataTable();
                table.clear().draw();

                function doNext(row_id) {

                    if (bulk_data_arr[row_id].length > 0) {

                        var row_data_arr = bulk_data_arr[row_id].split('\t');
                        if (typeof(row_data_arr[1]) !== 'undefined') { // this means it has tab separated UIDs in first col
                            var row_addr = row_data_arr[1];
                            var row_uid = row_data_arr[0];
                        }
                        else {
                            var row_addr = row_data_arr[0];
                            var row_uid = '-';
                        }

                        var ajax_url = "/loc8/match/nbn/all/" + safeUrl(row_addr);

                        console.log(ajax_url);

                        $.get(ajax_url, function (data, status) {

                            var row_str = '{';
                            row_str += '"uid": "' + row_uid + '",';
                            row_str += '"search_str": "' + row_addr + '",';
                            row_str += '"found_base": "' + data.results.matchedBaseAddr.longName + '",';
                            row_str += '"base_score": "<span ' + scoreToClass(data.results.matchedBaseAddr.score) + '>' + data.results.matchedBaseAddr.score + '</span>&nbsp;<br>[' + data.results.matchedBaseAddr.matchMsg + ']",';
                            row_str += '"found_sub": "' + data.results.matchedSubAddr.longName + '",';
                            row_str += '"sub_score": "<span ' + scoreToClass(data.results.matchedSubAddr.score) + '>' + data.results.matchedSubAddr.score + '</span>&nbsp;<br>[' + data.results.matchedSubAddr.matchMsg + ']",';
                            if (data.results.matchedSubAddr.hasOwnProperty('sourceId')) {
                                row_str += '"source_id": "' + data.results.matchedSubAddr.sourceId + '",';
                                row_str += '"serv_class": "' + data.results.matchedSubAddr.servClass + '",';
                                row_str += '"tech": "<span ' + techToClass(data.results.matchedSubAddr.tech, data.results.matchedSubAddr.servClass) + '>' + data.results.matchedSubAddr.tech + '</span>",';
                                row_str += '"rfs_date": "' + data.results.matchedSubAddr.params.rfs_date + '",';
                                row_str += '"poi_name": "' + data.results.matchedSubAddr.params.poi_name + '",';
                                row_str += '"poi_code": "' + data.results.matchedSubAddr.params.poi_code + '",';
                                row_str += '"ada_code": "' + data.results.matchedSubAddr.params.ada_code + '",';
                                row_str += '"disc_date": "' + data.results.matchedSubAddr.params.disc_date + '",';

                                // add the extended address fields for bill payne. so much payne.
                                $.each(data.sourceDetails, function (key, val) {
                                    row_str += '"' + key + '": "' + val + '",';
                                });
                            }
                            row_str += '"detail": "<a href=\'/loc8/match/nbn/all/' + safeUrl(row_addr) + '\' target=\'_blank\' class=\'grid_link\'><i class=\'fas fa-align-left\'></i></a>",';

                            if (data.results.matchedSubAddr.hasOwnProperty('sourceId')) {
                                row_str += '"map": "<a href=\'/loc8/map/id/' + data.results.matchedSubAddr.sourceId + '\' target=\'_blank\' class=\'grid_link\'><i class=\'fa fa-map-marker-alt\'></a>"';
                            }
                            else {
                                row_str += '"map": "<a href=\'/loc8/map/str/' + safeUrl(row_addr) + '\' target=\'_blank\' class=\'grid_link\'><i class=\'fa fa-map-marker-alt\'></a>"';
                            }

                            if (data.results.matchedBaseAddr.score > 20) {
                                base_matches += 1;
                            }

                            if (data.results.matchedSubAddr.score > 20) {
                                sub_matches += 1;
                            }

                            row_str += '}';
                            var row_data = JSON.parse(row_str);

                            var table = $('#grid').DataTable();
                            table.row.add(row_data).draw(false);

                            // the table was not re-drawing. this hack kicks it in the guts and makes it re-draw
                            var grid_ht = $(window).height() - 135;
                            $('.dataTables_scrollBody').height(grid_ht);

                            // now update some stats
                            perc_complete = Math.round(((row_id + 1) * 100) / bulk_data_arr.length);
                            $("#perc_compl").text(perc_complete);

                            perc_base = Math.round(((base_matches + 1) * 100) / bulk_data_arr.length);
                            $("#perc_base").text(perc_base);

                            perc_sub = Math.round(((sub_matches + 1) * 100) / bulk_data_arr.length);
                            $("#perc_sub").text(perc_sub);

                            var now_time = new Date();
                            elapsed = Math.round((now_time - start_time) / 100) / 10;
                            $("#elapsed").text(elapsed);

                            // now do the next iteration or stop
                            row_id++;
                            var is_running = $("#run_status").val();

                            if ((row_id < bulk_data_arr.length) && (is_running == "running")) {
                                doNext(row_id);
                            }
                            else if (is_running == "running") {
                                console.log("finished");
                                $("#run_status").val("stopped");
                                xhide($("#loader_div"));
                            }

                        });

                    }
                    else {
                        row_str = '{}';
                        var row_data = JSON.parse(row_str);

                        var table = $('#grid').DataTable();
                        table.row.add(row_data).draw(false);

                        // the table was not re-drawing. this hack kicks it in the guts and makes it re-draw
                        var grid_ht = $(window).height() - 135;
                        $('.dataTables_scrollBody').height(grid_ht);

                        // now do the next iteration or stop
                        row_id++;
                        var is_running = $("#run_status").val();

                        if ((row_id < bulk_data_arr.length) && (is_running == "running")) {
                            doNext(row_id);
                        }
                        else if (is_running == "running") {
                            console.log("finished");
                            $("#run_status").val("stopped");
                            xhide($("#loader_div"));
                        }
                    }
                }

                var bulk_data = $.trim($("#text_data").val());
                var bulk_data_arr = bulk_data.split('\n');
                var perc_complete = 0;
                var base_matches = 0;
                var sub_matches = 0;
                var start_time = new Date();
                var elapsed = 0;

                $("#records").text(bulk_data_arr.length);

                doNext(0, bulk_data_arr[0]);
            }

        });

        var grid_data = [];

        var grid_cols = [
            {title: 'Detail', data: 'detail', width: '30px', defaultContent: '-'},
            {title: 'Map', data: 'map', width: '30px', defaultContent: '-'},
            {title: 'UID', data: 'uid', width: '50px', defaultContent: '-'},
            {title: 'Search Str', data: 'search_str', width: '170px', defaultContent: '-'},
            {title: 'Found Base', data: 'found_base', width: '170px', defaultContent: '-'},
            {title: 'Base Score', data: 'base_score', width: '100px', defaultContent: '-'},
            {title: 'Found Sub', data: 'found_sub', width: '170px', defaultContent: '-'},
            {title: 'Sub Score', data: 'sub_score', width: '100px', defaultContent: '-'},
            {title: 'Source ID', data: 'source_id', defaultContent: '-', orderable: true},
            {title: 'Serv Class', data: 'serv_class', defaultContent: '-'},
            {title: 'Tech', data: 'tech', defaultContent: '-'},
            {title: 'RFS Date', data: 'rfs_date', defaultContent: '-'},
            {title: 'POI Name', data: 'poi_name', width: '110px', defaultContent: '-'},
            {title: 'POI Code', data: 'poi_code', defaultContent: '-'},
            {title: 'ADA Code', data: 'ada_code', width: '80px', defaultContent: '-'},
            {title: 'Disc Date', data: 'disc_date', width: '80px', defaultContent: '-'},

            {title: 'unit_number', data: 'unit_number', defaultContent: '-'},
            {title: 'unit_type_code', data: 'unit_type_code', defaultContent: '-'},
            {title: 'level_number', data: 'level_number', defaultContent: '-'},
            {title: 'level_type_code', data: 'level_type_code', defaultContent: '-'},
            {title: 'address_site_name', data: 'address_site_name', defaultContent: '-'},
            {title: 'road_number_1', data: 'road_number_1', defaultContent: '-'},
            {title: 'road_number_2', data: 'road_number_2', defaultContent: '-'},
            {title: 'lot_number', data: 'lot_number', defaultContent: '-'},
            {title: 'road_name', data: 'road_name', defaultContent: '-'},
            {title: 'road_suffix_code', data: 'road_suffix_code', defaultContent: '-'},
            {title: 'road_type_code', data: 'road_type_code', defaultContent: '-'},
            {title: 'locality_name', data: 'locality_name', defaultContent: '-'},
            {title: 'secondary_complex_name', data: 'secondary_complex_name', defaultContent: '-'},
            {title: 'postcode', data: 'postcode', defaultContent: '-'},
            {title: 'state_territory_code', data: 'state_territory_code', defaultContent: '-'},
            {title: 'latitude', data: 'latitude', defaultContent: '-'},
            {title: 'longitude', data: 'longitude', defaultContent: '-'},
            {title: 'connectivity_servicing_area_identifier', data: 'connectivity_servicing_area_identifier', defaultContent: '-'},
            {title: 'connectivity_servicing_area_name', data: 'connectivity_servicing_area_name', defaultContent: '-'}
        ];

        $('#grid').DataTable({
            "scrollX": true,
            "scrollY": "100",
            "paging": false,
            "info": false,
            "colReorder": true, //this is to allow users to drag-drop to re-order cols
            "order": [], //this is to turn off default ordering
            "language": {
                "search": "filter results:"
            },
            dom: 'Bfrtip',
            buttons: [
                {
                    extend: "colvis",
                    text: "columns"
                },
                {
                    extend: "excel",
                    text: "excel",
                    //action: function ( dt ) {
                    //	console.log( 'excel clicked' );
                    //}
                },
                {
                    extend: "copyHtml5",
                    text: "copy"
                }
            ],
            data: grid_data,
            columns: grid_cols
        });
    });

    function safeUrl(phrase) {
        // this is required so that the '/' in eg 4/21 smith st does not cause an issue with the laravel router regex
        var safe_str = phrase.replace(/\//g, '__');
        return safe_str;
    }

    function updateResults(foundAddr, matchType) {

        // update the result pane
        foundAddr = foundAddr.replace(', ', ',<br>');
        var res_area_html = '<p class="results_h1">Found Address:</p><p class="results">' + foundAddr + '</p>';
        res_area_html += '<p class="results_h1">Match Type:</p><p class="results">' + matchType + '</p>';
        $('#results_pane').css('display', 'block');
        $('#results_area').html(res_area_html);
    }

    function clearWelcome() {

        $('#welcome_pane').css('display', 'none');
    }

    function clearAtAddr() {

        $('#at_address_pane').css('display', 'none');
        $('#address_tree').tree('loadData', '');
    }

    function orDash(val) {
        if (val == '') {
            val = '-';
        }
        return val;
    }

    function scoreToClass(score) {
        if (score == 100) {
            val = 'score_100';
        }
        else if (score >= 85) {
            val = 'score_85';
        }
        else if (score >= 60) {
            val = 'score_60';
        }
        else if (score >= 20) {
            val = 'score_20';
        }
        else {
            val = 'score_0';
        }
        return ' class = \'' + val + '\' ';
    }

    function techToClass(tech, serv_class) {
        if ((serv_class != 0) && (serv_class != 10) && (serv_class != 20) && (serv_class != 30)) {
            val = tech;
        }
        else {
            val = 'Hollow';
        }
        return ' class = \'' + val + '_tech\' ';
    }

    function xhide(elem) {
        elem.css("visibility", "hidden");
    };

    function xshow(elem) {
        elem.css("visibility", "visible");
    };

    history.pushState(null, null, location.href);
    window.addEventListener("popstate", function(event) {
        var r = confirm('Are you sure you want to leave the page?\nall results will be lost.!');
        if (r != true) {
            history.pushState(null, null, location.href);
        }
    });

</script>
</body>
</html>

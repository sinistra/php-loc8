<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.1.2/js/bootstrap.min.js"></script>
    <script type="text/javascript" src="{{ URL::asset('js/datatables.js') }}"></script>

    <link rel="stylesheet"
          href="https://fonts.googleapis.com/css?family=Roboto:regular,bold,italic,thin,light,bolditalic,black,medium&amp;lang=en">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Montserrat">
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
            font-family: "Helvetica Neue", Helvetica, Arial;
        }

        table {
            font-size: 12px;
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

        #main_wrapper {
            width: 100%;
            height: 100%;
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
            max-height: 100%;
            border: 0px orange solid;
            padding: 12px 20px;
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
    </style>
    <style type="text/css">
        /* spinner css from http://tobiasahlin.com/spinkit/ */
        .spinner {
            margin: 10px auto;
            width: 40px;
            height: 40px;
            position: relative;
            text-align: center;
            display: none;

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
    <div id="left_pane_wrapper" style="overflow: auto;">
        <div id="header_div">
            <div id="logo_div">
                <a href="/loc8"><img src="../images/logo-macquarie-telecom.png"
                                     style="height: 33px; margin: 8px 0px 0px 20px"></a>
                <div style=" float: right; margin: 6px 10px 0px 0px">
                    <span style="color: #dedede; font-size: 18pt; font-weight: 100; font-style: normal; letter-spacing: 2px;">LOC-8</span>
                </div>
            </div>
        </div>
        <div id="welcome_pane">
            <p style="font-size: 16px; padding-bottom: 10px;">Bulk Search</p>
            <p>Now with extra bulkiness, LOC-8 (pronounced locate) is here to help you match customer addresses to
                official (mostly NBN) servicable locations.</p>
            <br>
            <p><a href="/loc8/visualise">LOC-8</a> responsibly!</p>
            <br>
            <div>
                <button type="button" id="show_input_btn">input</button>&nbsp;<button type="button" id="show_res_btn">
                    results
                </button>
            </div>
            <br>
            <div>
                <button type="button" id="start_btn">start</button>&nbsp;<button type="button" id="stop_btn">stop
                </button>
            </div>
            <br>
            <div>status:<br><input type="text" id="run_status" value="stopped" disabled></div>
            <br><br>
            <div id="loader_div" class="spinner">
                <div class="dot1"></div>
                <div class="dot2"></div>
            </div>
        </div>
        <div id="results_pane">
            <p style="font-size: 16px;">Search Results</p>
            <div id="results_area"></div>
        </div>
        <div id="at_address_pane">
            <p style="font-size: 16px;">At Found Address</p>
            <div id="address_tree" class="pane_tree"></div>
        </div>
    </div>
    <div id="content_div">
        <div id="res_div">
            <table id="grid" class="table table-striped table-bordered table-hover"></table>
        </div>
        <div id="input_div">
            <div id="input_form" border="1" width="100%" style="font-size: 10pt;">
                <span>To match addresses in bulk simply paste your data in the box below and then hit 'Start'.</span>
                <br><span>.. data can be either;</span>
                <ul>
                    <li>A list of UIDs and Addresses (<i>in that order - tab separated</i>)</li>
                    <li>A list of Addresses only</li>
                </ul>
                <textarea id="text_data"
                          style="font-family: courier; width:100%; height: 400px; font-size: 10pt;"></textarea>
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

        //var input_form_str = "<textarea id=\"text_data\" style=\"font-family: courier; width:100%; height: 300px; font-size: 10pt;\"></textarea>";
        //$("#input_form").html($(input_form_str));

        //$("#loader_div").hide();
        $("#res_div").hide();

        $("#show_input_btn").click(function () {
            $("#res_div").hide();
            $("#input_div").show();
            $("#run_status").val("stopped");
            $("#loader_div").hide();
        });

        $("#show_res_btn").click(function () {
            $("#input_div").hide();
            $("#res_div").show();
        });

        $("#stop_btn").click(function () {
            $("#run_status").val("stopped");
            $("#loader_div").hide();
        });

        $("#start_btn").click(function () {
            $("#run_status").val("running");
            $("#loader_div").show();
            $("#input_div").hide();
            $("#res_div").show();

//				// this is to reset the results next time start is clicked
            var table = $('#grid').DataTable();
            table.clear().draw();

            function doNext(row_id) {

                if (bulk_data_arr[row_id].length > 0) {

                    var row_data_arr = bulk_data_arr[row_id].split('\t')
                    if (typeof(row_data_arr[1]) !== 'undefined') { // this means it has tab separated UIDs in first col
                        var row_addr = row_data_arr[1];
                        var row_uid = row_data_arr[0];
                    }
                    else {
                        var row_addr = row_data_arr[0];
                        var row_uid = '-';
                    }

                    var ajax_url = "/loc8/match/nbn/" + safeUrl(row_addr);
                    console.log(ajax_url);

                    $.get(ajax_url, function (data, status) {

                        var row_str = '{';
                        row_str += '"uid": "' + row_uid + '",';
                        row_str += '"search_str": "' + row_addr + '",';
                        row_str += '"found_base": "' + data.results.matched_base_addr.long_name + '",';
                        row_str += '"base_score": "' + data.results.matched_base_addr.match_score + ' [' + data.results.matched_base_addr.match_msg + ']",';
                        row_str += '"found_sub": "' + data.results.matched_sub_addr.long_name + '",';
                        row_str += '"sub_score": "' + data.results.matched_sub_addr.match_score + ' [' + data.results.matched_sub_addr.match_msg + ']"';
                        if (data.results.matched_sub_addr.hasOwnProperty('carrier_id')) {
                            row_str += ',"carrier_id": "' + data.results.matched_sub_addr.carrier_id + '",';
                            row_str += '"serv_class": "' + data.results.matched_sub_addr.serv_class + '",';
                            row_str += '"tech": "' + data.results.matched_sub_addr.tech + '",';
                            row_str += '"rfs_date": "' + data.results.matched_sub_addr.params.rfs_date + '",';
                            row_str += '"poi_name": "' + data.results.matched_sub_addr.params.poi_name + '",';
                            row_str += '"poi_code": "' + data.results.matched_sub_addr.params.poi_code + '",';
                            row_str += '"ada_code": "' + data.results.matched_sub_addr.params.ada_code + '",';
                            row_str += '"disc_date": "' + data.results.matched_sub_addr.params.disc_date + '"';
                        }
                        row_str += '}';
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
                            $("#loader_div").hide();
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
                        $("#loader_div").hide();
                    }
                }


            }

            var bulk_data = $.trim($("#text_data").val());
            var bulk_data_arr = bulk_data.split('\n');

            doNext(0, bulk_data_arr[0]);

        });

        var grid_data = [];

        var grid_cols = [
            {title: 'UID', data: 'uid', width: '50px', defaultContent: '-'},
            {title: 'Search Str', data: 'search_str', width: '170px', defaultContent: '-'},
            {title: 'Found Base', data: 'found_base', width: '170px', defaultContent: '-'},
            {title: 'Base Score', data: 'base_score', width: '100px', defaultContent: '-'},
            {title: 'Found Sub', data: 'found_sub', width: '170px', defaultContent: '-'},
            {title: 'Sub Score', data: 'sub_score', width: '100px', defaultContent: '-'},
            {title: 'Carrier ID', data: 'carrier_id', defaultContent: '-', orderable: true},
            {title: 'Serv Class', data: 'serv_class', defaultContent: '-'},
            {title: 'Tech', data: 'tech', defaultContent: '-'},
            {title: 'RFS Date', data: 'rfs_date', defaultContent: '-'},
            {title: 'POI Name', data: 'poi_name', width: '110px', defaultContent: '-'},
            {title: 'POI Code', data: 'poi_code', defaultContent: '-'},
            {title: 'ADA Code', data: 'ada_code', width: '80px', defaultContent: '-'},
            {title: 'Disc Date', data: 'disc_date', width: '80px', defaultContent: '-'}
        ];

        $('#grid').DataTable({
            "scrollX": true,
            "scrollY": "100",
            "paging": false,
            "info": false,
            "colReorder": true, //this is to allow users to drag-drop to re-order cols
            "order": [], //this is to turn off default ordering
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

</script>
</body>
</html>

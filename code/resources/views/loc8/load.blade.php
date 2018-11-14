<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script src="https://momentjs.com/downloads/moment.min.js"></script>

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
            background-color: #333;
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
<table border="1" width="100%" style="font-family: arial; font-size: 10pt;">
    <tr>
        <td width="25%" valign="top">
            <div style="height: 550px;">
                <br>
                <div>elastic data loader:</span></div>
                <br>
                <div>index type:<br>
                    <select id="val_type">
                        <option value="suggest">suggest</option>
                        <option value="lucky">lucky</option>
                        <option value="suggest,lucky" selected>suggest,lucky</option>
                    </select>
                </div>
                <br>
                <div>index source:<br>
                    <select id="val_source">
                        <option value="pfl" selected>pfl</option>
                    </select>
                </div>
                <br>
                <div>from:<br><input type="text" id="val_from"></div>
                <br>
                <div>to:<br><input type="text" id="val_to"></div>
                <br>
                <div>qty per insert:<br><input type="text" id="val_incr" value="1000"></div>
                <br>
                <div>status:<br><input type="text" id="run_status" value="stopped" disabled></div>
                <br>
                <div>duration:<br><span id="elapsed">0</span>&nbsp;<span>sec</span></div>
                <br>
                <br>
                <div>
                    <button type="button" id="start_btn">start</button>&nbsp;<button type="button" id="stop_btn">stop
                    </button>
                </div>
                <br><br>
                <div id="loader_div" class="spinner">
                    <div class="dot1"></div>
                    <div class="dot2"></div>
                </div>
            </div>
        </td>
        <td width="75%" valign="top">
            <div id="loader">
                <table id="log_tbl" border="1" width="100%">
                </table>
            </div>
        </td>
    <tr>
</table>

<script>
    $(document).ready(function () {

        $("#loader_div").hide();
        var log_tbl_str = "<tr id=\"hdr_row\"><td>index_type</td><td>index_source</td><td>time</td><td>result</td></tr>";
        $("#log_tbl").html($(log_tbl_str));

        $("#stop_btn").click(function () {
            $("#run_status").val("stopped");
            $("#loader_div").hide();
        });

        $("#start_btn").click(function () {
            $("#run_status").val("running");
            $("#loader_div").show();

            var val_from = parseInt($("#val_from").val());
            var val_to = parseInt($("#val_to").val());
            var val_incr = parseInt($("#val_incr").val());
            var val_type = $("#val_type").val();
            var val_source = $("#val_source").val();

            console.log('type='+val_type);
            console.log('source='+val_source);

            index_types_arr = val_type.split(',');
            index_sources_arr = val_source.split(',');

            $("#log_tbl").html($(log_tbl_str));

            function doNext() {

                var ajax_url = "/loc8/load/" + arr_b[j] + "/" + arr_a[i] + "/" + batch_from + "/" + batch_to;
                console.log(ajax_url);
                $.get(ajax_url, function (data, status) {

                    // do thing
                    batch_finish_time = moment().format("YYYY-MM-DD h:mm:ss");
                    var row_str = "<tr><td>" + index_types_arr[j] + "</td><td>" + index_sources_arr[i] + "</td><td>" + batch_finish_time + "</td><td>" + data + "</td></tr>";
                    $("#hdr_row").after($(row_str));

                    // update stats
                    var now_time = new Date();
                    elapsed = Math.round((now_time - start_time) / 100) / 10;
                    $("#elapsed").text(elapsed);

                    // increment vals
                    if (i < (arr_a.length-1)) {
                        i++;                       
                    }
                    else if (j < (arr_b.length-1)) {
                        i = 0;
                        j++;
                    }
                    else if (batch_from <= batch_to) {
                        i = 0;
                        j = 0;
                        batch_from += val_incr;
                        batch_to = Math.min((batch_from + val_incr - 1), val_to);
                    }

                    //recursively call next
                    var is_running = $("#run_status").val();
                    if ((batch_from <= val_to) && (is_running == "running") && (data.indexOf("[errors]") == -1)) {
                        doNext();
                    }
                    else if (is_running == "running") {
                        console.log("finished");
                        $("#run_status").val("stopped");
                        $("#loader_div").hide();
                    }

                });

            }

            var batch_from = val_from;
            var batch_to = Math.min((val_from + val_incr - 1), val_to);
            var start_time = new Date();
            var elapsed = 0;

            var arr_a = index_sources_arr;
            var arr_b = index_types_arr;

            var i = 0;
            var j = 0;
            var k = val_from;

            doNext();

        });
    });
</script>
</body>
</html>

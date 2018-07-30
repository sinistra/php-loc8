<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script src="https://momentjs.com/downloads/moment.min.js"></script>
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
            <div style="height: 350px;">
                <br>
                <div>
                    <button type="button" id="start_btn">start</button>
                </div>
                <br>
                <div>
                    <button type="button" id="stop_btn">stop</button>
                </div>
                <br>
                <div>from:<br><input type="text" id="val_from"></div>
                <br>
                <div>to:<br><input type="text" id="val_to"></div>
                <br>
                <div>status:<br><input type="text" id="run_status" value="stopped" disabled></div>
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
        var log_tbl_str = "<tr id=\"hdr_row\"><td>time</td><td>result</td></tr>";
        $("#log_tbl").html($(log_tbl_str));

        $("#stop_btn").click(function () {
            $("#run_status").val("stopped");
            $("#loader_div").hide();
        });

        $("#start_btn").click(function () {
            $("#run_status").val("running");
            $("#loader_div").show();
            var val_incr = 1000;
            var val_from = parseInt($("#val_from").val());
            var val_to = parseInt($("#val_to").val());

            $("#log_tbl").html($(log_tbl_str));

            function doNext(batch_from, batch_to) {

                console.log("batch: " + batch_from + " - " + batch_to + " starting");
                var ajax_url = "/loc8/load/" + batch_from + "/" + batch_to;
                $.get(ajax_url, function (data, status) {
                    console.log("batch: " + batch_from + " - " + batch_to + " returned");
                    batch_finish_time = moment().format("YYYY-MM-DD h:mm:ss");
                    var row_str = "<tr><td>" + batch_finish_time + "</td><td>" + data + "</td></tr>";
                    $("#hdr_row").after($(row_str));

                    batch_from += val_incr;
                    batch_to = Math.min((batch_from + val_incr - 1), val_to);
                    var is_running = $("#run_status").val();
                    if ((batch_from <= val_to) && (is_running == "running")) {
                        doNext(batch_from, batch_to);
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
            doNext(batch_from, batch_to);

        });
    });
</script>
</body>
</html>

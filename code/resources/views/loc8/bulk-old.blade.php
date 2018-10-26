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

		@-webkit-keyframes sk-rotate { 100% { -webkit-transform: rotate(360deg) }}
		@keyframes sk-rotate { 100% { transform: rotate(360deg); -webkit-transform: rotate(360deg) }}

		@-webkit-keyframes sk-bounce {
		  0%, 100% { -webkit-transform: scale(0.0) }
		  50% { -webkit-transform: scale(1.0) }
		}

		@keyframes sk-bounce {
		  0%, 100% { 
		    transform: scale(0.0);
		    -webkit-transform: scale(0.0);
		  } 50% { 
		    transform: scale(1.0);
		    -webkit-transform: scale(1.0);
		  }
		}
		</style>
    </head>
    <body>
        <table border="1" width="100%" style="font-family: arial; font-size: 10pt;">
        	<tr>
        		<td width="20%" valign="top">
        			<div style="height: 350px;">
        				<br><div>bulk address resolver:</span></div>
        				<br><div><button type="button" id="show_input_btn">show input</button>&nbsp;<button type="button" id="show_res_btn">show results</button></div>
        				<br><div>status:<br><input type="text" id="run_status" value="stopped" disabled></div>
        				<br><div><button type="button" id="start_btn">start</button>&nbsp;<button type="button" id="stop_btn">stop</button></div>
        				<br><br>
        					<div id="loader_div" class="spinner">
					  			<div class="dot1"></div>
					  			<div class="dot2"></div>
							</div>
        			</div>
        		</td>
        		<td width ="80%" valign="top">
        			<div id="res_div">
				        <table id="res_tbl" border="1" width="100%">
				        </table>
        			</div>
        			<div id="input_div">
				        <table id="input_form" border="1" width="100%">
				        </table>
        			</div>
        		</td>
			<tr>
        </table>

		<script>
		$(document).ready(function(){

		    var res_tbl_str = "<tr id=\"hdr_row\"><td>search_str</td><td>found_base</td><td>base_score</td><td>found_sub</td><td>sub_score</td><td>carrier id</td><td>serv class</td><td>tech</td><td>rfs_date</td><td>poi_name</td><td>poi_code</td><td>ada_code</td><td>disc_date</tr>";
		    $("#res_tbl").html($(res_tbl_str));

		    var input_form_str = "<textarea id=\"text_data\" rows=\"30\" style=\"font-family: courier; width:95%; font-size: 10pt;\"></textarea>";
		    $("#input_form").html($(input_form_str));

			$("#loader_div").hide();
			$("#res_div").hide();

			$("#show_input_btn").click(function() {
				$("#res_div").hide();
				$("#input_div").show();
				$("#run_status").val("stopped");
				$("#loader_div").hide();
			});

			$("#show_res_btn").click(function() {
				$("#input_div").hide();
				$("#res_div").show();
			});

			$("#stop_btn").click(function() {
				$("#run_status").val("stopped");
				$("#loader_div").hide();
			});

			$("#start_btn").click(function() {
				$("#run_status").val("running");
				$("#loader_div").show();
				$("#input_div").hide();
				$("#res_div").show();

				$("#res_tbl").html($(res_tbl_str)); // this is to reset the results next time start is clicked

				function doNext (row_id) {

					//console.log("row: "+row_id+" starting");

					if (bulk_data_arr[row_id].length > 0) {

						var ajax_url = "/loc8/match/nbn/"+safeUrl(bulk_data_arr[row_id]);
						console.log(ajax_url);

					    $.get(ajax_url, function(data, status){

							//console.log("row: "+row_id+" returned");

							// get the result and update the table
					        var row_str = "<tr>";
					        row_str += "<td>"+bulk_data_arr[row_id]+"</td>";
					        row_str += "<td>"+data.results.matched_base_addr.long_name+"</td>";
					        row_str += "<td>"+data.results.matched_base_addr.match_score+" ["+data.results.matched_base_addr.match_msg+"]</td>";
					        row_str += "<td>"+data.results.matched_sub_addr.long_name+"</td>";
					        row_str += "<td>"+data.results.matched_sub_addr.match_score+" ["+data.results.matched_sub_addr.match_msg+"]</td>";
					        if(data.results.matched_sub_addr.hasOwnProperty('carrier_id')) {
					        	row_str += "<td>"+data.results.matched_sub_addr.carrier_id+"</td>";
						        row_str += "<td>"+data.results.matched_sub_addr.serv_class+"</td>";
						        row_str += "<td>"+data.results.matched_sub_addr.tech+"</td>";
						        row_str += "<td>"+data.results.matched_sub_addr.params.rfs_date+"</td>";
						        row_str += "<td>"+data.results.matched_sub_addr.params.poi_name+"</td>";
						        row_str += "<td>"+data.results.matched_sub_addr.params.poi_code+"</td>";	
						        row_str += "<td>"+data.results.matched_sub_addr.params.ada_code+"</td>";
						        row_str += "<td>"+data.results.matched_sub_addr.params.disc_date+"</td>";	
					        } 
					        else {
					        	for (var i = 0; i < 8; i++) {
					        		row_str += "<td>-</td>";
					        	}
					        }      				       				       				        
					        row_str += "</tr>";

/////////
							//$("#res_tbl").append($(row_str));
							console.log(row_str);

							// now do the next iteration or stop
							row_id ++;
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
						row_str = "<tr><td>-</td></tr>";
						$("#res_tbl").append($(row_str));

						// now do the next iteration or stop
						row_id ++;
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

				doNext(0,bulk_data_arr[0]);

			});
		});

		function safeUrl(phrase) {
			// this is required so that the '/' in eg 4/21 smith st does not cause an issue with the laravel router regex
			var safe_str = phrase.replace(/\//g, '__');
			return safe_str;
		}

		function orDash(val) {
			if (val == '') { val = '-'; }
			return val;
		}
		</script>
    </body>
</html>

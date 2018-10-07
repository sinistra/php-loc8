<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('loc8.search');
});

Route::get('/loc8', function () {

    return view('loc8.search');
});

Route::get('/locs/{id_num}', function ($id_num) {

    $locs = DB::table('pfl_raw')
        ->where('NBN_LOCATION_IDENTIFIER', '=', $id_num)
        ->get();

    return view('locs.list', compact('locs'));

});

Route::get('/locs/{search_key}/{search_val}/{query_type}/{page_num}', function ($search_key,$search_val,$query_type,$page_num) {

    if (strtolower($search_key) == 'mt') { $search_key = 'UID'; }
	elseif (strtolower($search_key) == 'nbn') { $search_key = 'NBN_LOCID'; }
	elseif (strtolower($search_key) == 'addr') { $search_key = 'FORMATTED_ADDRESS_STRING'; }

	if (strtolower($query_type) == 'is') { $query_type = '='; }
	elseif (strtolower($query_type) == 'like') { 
        $query_type = 'like';
        $search_val = '%' . $search_val .'%';
	}
	elseif (strtolower($query_type) == 'sw') {
        $query_type = 'like';
		$search_val = $search_val .'%';
	}
	elseif (strtolower($query_type) == 'ew') {
        $query_type = 'like';
        $search_val = '%' . $search_val;
    }

    $locs = DB::table('pfl_lean')
        ->where($search_key, $query_type, $search_val)
        ->limit(10)
        ->get();

    //return $locs;
    return view('loc8.list', compact('locs'));

});

Route::get('/locs/details/{mt_locid}', function ($mt_locid) {

	$id_num = get_loc_num_nbn($mt_locid, "MTL");
    $locs = DB::table('pfl_raw')
        ->where('UID', '=', $id_num)
        ->limit(1)
        ->get();

    return $locs;
});

Route::get('/loc8/load/{load_from}/{load_to}', function ($load_from,$load_to) {

    // this grabs loc IDs from mySQL in a batch and puts them into elastic one at a time
    // once this is working the plan will be to optimise into a bulk load function
    // instead of one at a time.

    $search_key = 'UID';
    $query_type = '>=';
    $load_qty = $load_to - $load_from + 1;

    echo "records " . $load_from . " - " . $load_to;

    $locs = DB::table('pfl_raw')
        ->where($search_key, $query_type, $load_from)
        ->limit($load_qty)
        ->get();

    es_load_bulk($locs);

});

Route::get('/loc8/load', function () {

    return view('loc8.load');
});

Route::get('/loc8/match/{carrier}/{search_str}', function ($carrier,$search_str) {

	// this takes a carrier name and search string and returns the best addr match it can

	// first process the input so that we are comparing apples with apples as much as possible
	$processed_search_str = get_processed_addr($search_str);
	echo "user_str = " . $processed_search_str . "<br>";

	// try matching exactly what the user typed
	$match_type = "exact";
	$match_obj = find_processed_addr($processed_search_str);

	// if no match then get the base addr via google and try to match that
	if ($match_obj == null) {
		$match_type = "google-processed";
		$google_arr = get_google_arr($search_str);
		$processed_google_base_addr = get_processed_addr(get_base_addr_google($google_arr));
		echo "google_base_addr = " . $processed_google_base_addr . "<br>";
		$match_obj = find_processed_addr($processed_google_base_addr);
	}

	// if a match is found, need to get sub-address
	if ($match_obj != null) {

		echo "alias_addr = " . $match_obj->alias_address . "<br>";
		echo "official_addr = " . $match_obj->official_nbn_address . "<br>";
		echo "match_type = " . $match_type . "<br>";

		// if the match was with a base address need to see if there are sub-addresses
		if ($match_obj->alias_type == 5) {
			$token_match_obj = find_sub_addresses($match_obj->base_hash);
			if ($token_match_obj != null) { // if there were sub-addresses

			   	// now need to find user tokens
				$usr_sub_addr_str = get_sub_addr_usr($processed_search_str,$processed_google_base_addr,$google_arr);
				$usr_tokens = get_sub_addr_tokens($usr_sub_addr_str);
				echo "user_sub-addr = " . $usr_sub_addr_str . "<br>";
				echo "user_sub_addr_tokens = " . $usr_tokens . "<br>";

				// these are the tokens for each of the match sub-addresses
				foreach($token_match_obj as $key=>$val) {
					echo $val->_source->alias_address . "<br>";
					$token_score_results[$key] = score_token_matches($usr_tokens,$val->_source->alias_address);
			   	}
			   	print_r($token_score_results);
			   	$top_scores = array_keys($token_score_results,max($token_score_results));
			   	print_r($top_scores);
			   	if (count($top_scores) == 1) { // there was a winner so return the winner
			   		echo $token_match_obj[$top_scores[0]]->_source->official_nbn_address;
			   	}
			   	else {
			   		echo "multiple or no matches";
			   	}

			}
			else {
				echo "no sub-addresses";
			}

		}
		else { // match was with a sub-address so job done

			echo $match_obj->official_nbn_address;

		}

	}
	else {
		echo "no match";
	}

	// if if there is a match, then 

	// if there is a match so far, test to see if the match has is a sub-address or has sub-addresses


	// if no-sub-addresses, then end here

	// if there are sub-addresses then..
	// grab all sub-addresses at that base address
	// loop through all sub-addresses and score them
	// find the highest score

	// if there was a winner - return the winner and score

	//echo $match_str;

});

Route::get('/loc8/qry/{search_str}/{ret_limit}/{qry_type}', function ($search_str,$ret_limit,$qry_type) {

    // this takes a search string and returns the list of records from elastic
    // that match the search term. only based on formatted address for now

	$result = es_qry(qstring_decode($search_str),$ret_limit,$qry_type);
    $json = json_decode($result);

    //echo $result;
	//exit();

    //echo "took: " . $json->took . "ms<br>";

    $found_array = array();
    $hits = $json->hits->hits;
	foreach($hits as $hit) {
        $found_array[] = ["loc" => $hit->_source->alias_address, "geo" => $hit->_source->geo_location, "hash" => $hit->_source->base_hash];
    }

    header('Content-type: application/json');
    echo json_encode($found_array);

});

Route::get('/loc8/base_qry/{search_str}/{ret_limit}', function ($search_str,$ret_limit) {

    // this takes a basehash id and returns the list of records from elastic
    // that match the base hash. ie. for finding all sub addresses at a base address.

	$result = es_base_qry($search_str,$ret_limit);
    $json = json_decode($result);

    //echo "took: " . $json->took . "ms<br>";

    $found_array = array();
    $hits = $json->hits->hits;

	foreach($hits as $hit) {
		if ($hit->_source->alias_type == "1") { // add the base address here
			$base_addr_str = get_base_addr_nbn($hits[0]->_source->official_nbn_address);
			$found_array_1[] = ["nbn_st_addr" => get_st_addr_nbn($base_addr_str), "nbn_suburb" => get_suburb_nbn($base_addr_str)];
		}
		else { // add the sub-addresses here
			$nbn_addr = $hit->_source->official_nbn_address;
      		$found_array_2[] = ["nbn_sub_addr" => get_sub_addr_nbn($nbn_addr), "mt_locid" => $hit->_source->mt_locid, "geo" => $hit->_source->geo_location, "nbn_locid" => $hit->_source->nbn_locid, "gnaf_locid" => $hit->_source->gnaf_locid, "tech" => $hit->_source->tech, "rfs" => $hit->_source->rfs, "serv_class" => $hit->_source->serv_class];
   		}
       }
       
    asort($found_array_2);
    $found_array = array_merge($found_array_1, $found_array_2);

    header('Content-type: application/json');
    echo json_encode($found_array);
    //echo $result;

});

Route::get('/loc8/nearby_qry/{lat}/{lon}/{ret_limit}', function ($lat,$lon,$ret_limit) {

    // this takes a the co-ords for a point and returns the list of records from elastic
    // that are base addresses near the point. ie. for finding all nearby base addresses.

    //lat = "-33.378395";
    //$lon = "151.370702";

	$result = es_nearby_qry($lat,$lon,$ret_limit);

    $json = json_decode($result);

    //echo "took: " . $json->took . "ms<br>";

    $found_array = array();
    $hits = $json->hits->hits;

	foreach($hits as $hit) {
		$base_addr_str = get_base_addr_nbn($hit->_source->official_nbn_address);
		$dist = round($hit->sort[0]*1000);


        //echo $hit->_source->base_hash . "<br>";
		$subs_count_result = es_base_qry($hit->_source->base_hash,"1");
        $subs_count_json = json_decode($subs_count_result);

        $found_array[] = ["nbn_st_addr" => get_st_addr_nbn($base_addr_str), "nbn_suburb" => get_suburb_nbn($base_addr_str), "dist" => $dist, "count" => $subs_count_json->hits->total, "geo" => $hit->_source->geo_location, "mt" => $hit->_source->mt_locid];
    }

    header('Content-type: application/json');
    //echo $result;
    echo json_encode($found_array);

});

Route::get('/loc8/visualise', function () {

    return view('loc8.visualise');

});


/*
|--------------------------------------------------------------------------
| Skunky Functions
|--------------------------------------------------------------------------
|
| Here is where I have put a bunch of skunky functions - to be turned into
| to controllers when the skeleton is working.
|
*/

function hit_curl($meth,$curl_url,$post_data)
{
    $ch = curl_init();
	if ($ch === false) { throw new Exception('failed to initialize'); }

	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_URL, $curl_url);
	curl_setopt($ch, CURLOPT_HTTPHEADER,array('Content-Type: application/json'));

	if ($meth == "POST") {
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, strtoupper($meth));
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
	}

	$result = curl_exec($ch);

	if ($result === false) { throw new Exception(curl_error($ch), curl_errno($ch)); }

    curl_close($ch);

    return $result;
}

function es_load_bulk($locs)
{

    $curl_url = "laradock_elasticsearch_1:9200/pfl/_bulk";
    $curl_data = "";

    foreach ($locs as $loc) {

        $base_addr = get_base_addr_nbn($loc->FORMATTED_ADDRESS_STRING);
    	if ($base_addr != $loc->FORMATTED_ADDRESS_STRING) { $is_mdu = 1; }
        else { $is_mdu = 0; }
        
        $base_hash = substr(md5(get_base_addr_nbn($loc->FORMATTED_ADDRESS_STRING)), 0, 15);
		$mt_locid = "MTL" . sprintf("%'.012d", $loc->UID);
    	$search_addr_suffix = " | " . $loc->NBN_LOCID . " | L" . get_loc_num_nbn($loc->NBN_LOCID, "LOC") . " | " . $mt_locid;

        $search_addr = array();

        // alias type 1 = base address with other bits
    	$search_addr[1] = get_base_addr_nbn($loc->FORMATTED_ADDRESS_STRING) . $search_addr_suffix;

    	// alias type 5 = base address with underscores so that you can force it to find st-num and st-type and.. also with other bits
    	$search_addr[5] = get_processed_base_addr_nbn($loc->FORMATTED_ADDRESS_STRING) . $search_addr_suffix;

    	if ($is_mdu == 1) {

    		// alias type 2 = original formatted sub-address from nbn with other bits
    		$search_addr[2] = $loc->FORMATTED_ADDRESS_STRING . $search_addr_suffix;

	    	// alias type 3 = unit address as eg '4/ ' instead of 'unit 4,' plus base address with other bits
	    	if ($loc->UNIT_NUMBER != "") {
	    		$search_addr[3] = $loc->UNIT_NUMBER . "/ " . get_base_addr_nbn($loc->FORMATTED_ADDRESS_STRING) . $search_addr_suffix;
	    	}

	    	// alias type 4 = full-address with underscores so that you can force it to find st-num and st-type and.. also with other bits
	    	$search_addr[4] = get_processed_addr($loc->FORMATTED_ADDRESS_STRING) . $search_addr_suffix;

	    	// alias type 6 = string of sub-address tokens
	    	$search_addr[6] = get_sub_addr_tokens_nbn($loc->FORMATTED_ADDRESS_STRING);

        }
        
		for ($i = 1; $i <= 6; $i++) {

            //echo "<br>i= " . $i . "<br>";

            if (isset($search_addr[$i])) {

                // we want unique base addresses, so for them create the id based on a hash of the base address string
				// basically, if the base-hash is the id for the base addresses, when other LOCs with the same base
                // address get loaded, they will simply over-write the previous record with that id, so there will only
                // ever be a single record for each base address
                if (($i == 1) || ($i == 5)) { $rec_id = $i . $base_hash; }

				// for all other addresses base it off the MT LOPCID (ie. UID) which itself is based on an auto-increment in mysql
				else { $rec_id = $loc->UID + ($i * 100000000000); }

                $curl_data .= '{"index":{"_index":"pfl","_type":"doc","_id":"' . $rec_id . '"}}' . "\n";
                $curl_data .= '{ ';
                $curl_data .= '"alias_address" : "' . addslashes($search_addr[$i]) . '", ';
				$curl_data .= '"official_nbn_address" : "' . addslashes($loc->FORMATTED_ADDRESS_STRING) . '", ';
                $curl_data .= '"base_hash" : "' . $base_hash . '", ';
				$curl_data .= '"nbn_locid" : "' . $loc->NBN_LOCID . '", ';
                $curl_data .= '"mt_locid" : "' . $mt_locid . '", ';
				$curl_data .= '"gnaf_locid" : "' . $loc->GNAF_PERSISTENT_IDENTIFIER . '", ';
				$curl_data .= '"serv_class" : "' . $loc->SERVICE_CLASS . '", ';
				$curl_data .= '"tech" : "' . $loc->SERVICE_TYPE . '", ';
				$curl_data .= '"rfs" : "' . $loc->READY_FOR_SERVICE_DATE . '", ';
				$curl_data .= '"geo_location" : { "lat": "' . $loc->LATITUDE . '", "lon": "' . $loc->LONGITUDE . '" }, ';
                $curl_data .= '"alias_type" : "' . $i . '"';
                $curl_data .= ' }' . "\n";
            }
        }
    }

    //echo "<br>cd= <br>" . $curl_data . "<br>";
    $curl_result = hit_curl("POST", $curl_url, $curl_data);
    //echo "<br>cr= <br>" . $curl_result . "<br>";
    echo "  ...done";
}

function es_qry($search_str,$res_limit,$qry_type)
{
	$qry_array = explode("|",$qry_type);
    $curl_url = "laradock_elasticsearch_1:9200/pfl/_search/";

    // always show base addresses by default, then show other things based on what is ticked by the user
 
   //autosuggest types
   if (in_array("def",$qry_array)) { $alias_types = "1"; }
   if (in_array("subs",$qry_array)) { $alias_types .= ",2"; }
   if (in_array("aliass",$qry_array)) { $alias_types .= ",3,4"; }

   $qry_data = '{ "from" : 0, "size" : ' . $res_limit . ', "query": { "bool" : { "must": { "match": { "alias_address": { "query": "' . $search_str . '", "operator": "and" } } }, "filter": { "terms": { "alias_type": [' . $alias_types . '] } } } } } }';

    //echo $qry_data;

    $curl_result = hit_curl("POST", $curl_url, $qry_data);

    return $curl_result;
}

function es_base_qry($search_str,$res_limit)
{
    $curl_url = "laradock_elasticsearch_1:9200/pfl/_search/";
	$qry_data = '{ "from" : 0, "size" : ' . $res_limit . ', "query": { "bool" : { "must": { "match": { "base_hash": { "query": "' . $search_str . '", "operator": "and" } } }, "filter": { "terms": { "alias_type": [1,2] } } } } } }';
    $curl_result = hit_curl("POST", $curl_url, $qry_data);
    return $curl_result;
}

function es_nearby_qry($lat,$lon,$res_limit)
{
    // 0.05 degrees is approx 5km
    $bound_dist = 0.05;

    // top left bound
    $tl_lat = $lat + $bound_dist;
    $tl_lon = $lon - $bound_dist;

    // bottom right bound
    $br_lat = $lat - $bound_dist;
    $br_lon = $lon + $bound_dist;

    $curl_url = "laradock_elasticsearch_1:9200/pfl/_search/";
    //$qry_data = '{ "from" : 0, "size" : 30, "query": { "bool" : { "must": { "match": { "alias_type": { "query": "1",  "operator": "and" } } }, "filter": { "geo_bounding_box": { "type": "indexed", "geo_location": { "top_left": { "lat":  -32.864098, "lon": 150.608544 }, "bottom_right": { "lat":  -34.300274, "lon": 151.609537 } } } } } }, "sort": [ { "_geo_distance": { "geo_location": { "lat":  -33.378395, "lon": 151.370702 }, "order": "asc", "unit": "km", "distance_type": "plane" } } ] }';
    $qry_data = '{ "from" : 0, "size" : ' . $res_limit . ', "query": { "bool" : { "must": { "match": { "alias_type": { "query": "1",  "operator": "and" } } }, "filter": { "geo_bounding_box": { "type": "indexed", "geo_location": { "top_left": { "lat":  ' . $tl_lat . ', "lon": ' . $tl_lon . ' }, "bottom_right": { "lat":  ' . $br_lat . ', "lon": ' . $br_lon . ' } } } } } }, "sort": [ { "_geo_distance": { "geo_location": { "lat":  ' . $lat . ', "lon": ' . $lon . ' }, "order": "asc", "unit": "km", "distance_type": "plane" } } ] }';

    $curl_result = hit_curl("POST", $curl_url, $qry_data);
    return $curl_result;

}

function es_bulk_qry($search_str,$res_limit)
{
    $curl_url = "laradock_elasticsearch_1:9200/pfl/_search/";
	$qry_data = '{ "from" : 0, "size" : ' . $res_limit . ', "query": { "bool" : { "must": { "match": { "alias_address": { "query": "' . $search_str . '", "operator": "and" } } }, "filter": { "terms": { "alias_type": [4, 5] } } } } } }';
	$curl_result = hit_curl("POST", $curl_url, $qry_data);
    return $curl_result;
}

function es_bulk_base_qry($search_str,$res_limit)
{
    $curl_url = "laradock_elasticsearch_1:9200/pfl/_search/";
	$qry_data = '{ "from" : 0, "size" : ' . $res_limit . ', "query": { "bool" : { "must": { "match": { "base_hash": { "query": "' . $search_str . '", "operator": "and" } } }, "filter": { "terms": { "alias_type": [6] } } } } } }';
	$curl_result = hit_curl("POST", $curl_url, $qry_data);
    return $curl_result;
}

function delete_addr_suffix_nbn($addr_str)
{
    // remove any portions with brackets

    // /  - opening delimiter (necessary for regular expressions, can be any character that doesn't appear in the regular expression
    // \( - Match an opening parenthesis
    // [^)]+ - Match 1 or more character that is not a closing parenthesis
    // \) - Match a closing parenthesis
    // /  - Closing delimiter

	$ret_str = preg_replace("/\([^)]+\)/","",$addr_str);
    $ret_str = trim($ret_str);
    return $ret_str;
}

function delete_addr_prefix_nbn($addr_str)
{
	// take only the last 2 portions delimited with comma's - which should get rid of the sub-address and no more
    $addr_arr = array_reverse(explode(", ", $addr_str));
    $ret_str = $addr_arr[1] . ", " . $addr_arr[0];
    return $ret_str;
}

function get_base_addr_nbn($addr_str)
{
    $ret_str = delete_addr_suffix_nbn($addr_str);
    $ret_str = delete_addr_prefix_nbn($ret_str);
    return $ret_str;
}

function get_processed_base_addr_nbn($addr_str)
{
	$ret_str = get_base_addr_nbn($addr_str);
	$ret_str = get_processed_addr($ret_str);
    return $ret_str;
}

function get_snake_addr($addr_str)
{
	// this will process address strings for use at index and at search
	// so that they can be compared without having to worry about variants with commas without commas etc.

	$ret_str = strtoupper($addr_str);
    $strip_chars = array(",", "/", "(", ")", "[", "]");
	$ret_str = str_replace($strip_chars, "", $ret_str); // remove specific special chars
	$ret_str = str_replace(" ", "_", trim($ret_str)); // trim then add underscores
	$ret_str = preg_replace('/([_])\1+/', '$1', $ret_str); // find any repeat underscores and turn it into a single underscore
	$ret_str = "_" . $ret_str . "_";
	return $ret_str;
}

function get_normalised_addr($addr_str)
{

    $from_types = array(
    	"_STREET_", 
    	"_STR_", 
    	"_ROAD_", 
    	"_AVENUE_",
    	"_AVE_", 
    	"_PLACE_",
    	"_HIGHWAY_",
    	"_CIRCUIT_"
    );

    $to_types = array(
    	"_ST_", 
    	"_ST_", 
    	"_RD_", 
    	"_AV_", 
    	"_AV_", 
    	"_PL_",
    	"_HWY_",
    	"_CCT_"
    );

	$ret_str = str_replace($from_types, $to_types, $addr_str); 
    return trim($ret_str, '_');
}

function get_processed_addr($addr_str)
{
	$ret_str = get_snake_addr($addr_str); // set to uppercase and remove specific special chars and set to snake str
	$ret_str = get_normalised_addr($ret_str);
	return $ret_str;
}

function get_sub_addr_nbn($addr_str)
{
	$base_addr_str = get_base_addr_nbn($addr_str);

    $ret_str = str_replace($base_addr_str, " ", $addr_str);
	$ret_str = preg_replace('/([_])\1+/', '$1', $ret_str); // find any repeat whitespace and turn it into a single whitepace
    $ret_str = trim($ret_str);
    $ret_str = trim($ret_str, ',');
    $ret_str = trim($ret_str, '_');

    if ($ret_str == "") { $ret_str = "-"; }

    return $ret_str;
}

function get_sub_addr_usr($proc_usr_str,$proc_google_base_str,$google_arr)
{
	$ret_str = str_replace($proc_google_base_str, "_", $proc_usr_str); // remove the base address
	$ret_str = preg_replace('/([_])\1+/', '$1', $ret_str); // find any repeat whitespace and turn it into a single whitepace
    $ret_str = trim($ret_str);
    $ret_str = trim($ret_str, ',');
    $ret_str = trim($ret_str, '_');

    if ($ret_str == "") { // if there ends up being no sub-address that the user typed then return null
    	$ret_str = null;
    }
    elseif ($ret_str == $proc_usr_str) { // if there was no change - just grab the google sub-address
    	$ret_str = $google_arr["subpremise"];
    }

    return $ret_str;
}

function get_sub_addr_tokens($addr_str)
{
	$ret_str = "_" . get_processed_addr($addr_str) . "_";

	// now break at points between letters and numbers with a pipe
	$arr = preg_split('/(?<=[0-9])(?=[a-z]+)/i',$ret_str);
	$ret_str = implode("|", $arr);
	$arr = preg_split('/(?<=[a-z])(?=[0-9]+)/i',$ret_str);
	$ret_str = implode("|", $arr);

	// now need to find keywords and see what is next after them
	// replace keywords with trailing space with standard keyword with trailing ":"

    $token_keywords = [
    	"UNIT" => "UNIT",
    	"U" => "UNIT",
    	"LEVEL" => "LVL",
    	"LVL" => "LVL",
    	"FLOOR" => "FLR",
    	"FLR" => "FLR",
    	"ROOM" => "RM",
    	"RM" => "RM",
    	"TOWNHOUSE" => "TH",
    	"LOT" => "LOT",
    	"APPARTMENT" => "APPT",
		"APPT" => "APPT",    	
    	"SHOP" => "SHP",
    	"SUITE" => "SUITE",
    	"L" => "LVL"
    ];

    // now insert keywords separated by _key_
	foreach($token_keywords as $key => $val) {
		$from = "_" . $key . "_";
		$to = "_#" . $val . ":";
		$ret_str = str_replace($from, $to, $ret_str);
   	}

    // now insert keywords separated by |key_
	foreach($token_keywords as $key => $val) {
		$from = "|" . $key . "_";
		$to = "|#" . $val . ":";
		$ret_str = str_replace($from, $to, $ret_str);
   	}

    // now insert keywords separated by _key|
	foreach($token_keywords as $key => $val) {
		$from = "_" . $key . "|";
		$to = "_#" . $val . ":";
		$ret_str = str_replace($from, $to, $ret_str);
   	}

   	$ret_str = str_replace("|", "", $ret_str); // now remove the pipes and trim the ends
    $ret_str = trim($ret_str, '_'); // trim underscores off the start and end

   	// now add an X: to anything that is not already a tuple
   	$arr = explode("_", $ret_str);
	foreach($arr as $key=>$val) {
		if (strpos($val, ":") == false) {
			$arr[$key] = "X:" . $val;
		}
	}
	$ret_str = implode("_", $arr);
	
   	$ret_str = str_replace("#", "", $ret_str); // now remove the hashes
	$ret_str = preg_replace('/([:])\1+/', '$1', $ret_str); // find any repeat ":" and turn it into a single ":"

	return $ret_str;
}

function get_sub_addr_tokens_nbn($addr_str)
{
	$ret_str = get_sub_addr_nbn($addr_str);
	$ret_str = get_sub_addr_tokens($ret_str);
	return $ret_str;
}

function score_token_matches($usr_str,$db_str)
{
	// this iterates through the db sub-addr tokens and the usr sub-addr tokens
	// finds where the vals match, then gets what score it should give based
	// on how close the usr key (eg. TH) is to the db key (eg. TOWNHOUSE)

	$match_score = 0;
	$usr_tokens_arr = explode("_", $usr_str);
	$db_tokens_arr = explode("_", $db_str);

	foreach($usr_tokens_arr as $usr_token) {
		$usr_token_arr = explode(":", $usr_token);
		foreach($db_tokens_arr as $db_token) {
			$db_token_arr = explode(":", $db_token);
			if ((isset($usr_token_arr[1])) && (isset($db_token_arr[1]))) {
				if ($usr_token_arr[1] == $db_token_arr[1]) {
					$match_score += get_token_scores($usr_token_arr[0],$db_token_arr[0]);
				}
			}
			else {
				if ($usr_token == $db_token) {
					$match_score += 1; // give 1 point if there is a matching word
				}
			}
		}
	}

	return $match_score;
}

function get_token_scores($str1,$str2)
{
	if ($str1 == $str2) {
		$score = 5;
	}
	else {
		$translation_scores = [
			"SUITE_S" => 3,
			"LVL_L" => 3,
			"LEVEL_L" => 2
		];

		$translation = $str1 . "_" . $str2;

		if (array_key_exists($translation, $translation_scores)) {
			$score = $translation_scores[$translation];
		}
		else {
			$score = 0;
		}
	}

	return $score;
}

function get_st_addr_nbn($addr_str)
{
    $addr_arr = explode(", ", $addr_str);
    $ret_str = trim($addr_arr[0]);
    return $ret_str;
}

function get_suburb_nbn($addr_str)
{
    $addr_arr = explode(", ", $addr_str);
    $ret_str = trim($addr_arr[1]);
    return $ret_str;
}

function get_loc_num_nbn($loc_str,$loc_prefix)
{
    //get the number part of a loc id with leading zero's stripped
    // prefix  = LOC or MTL
    $base_loc = "";
    if (strstr($loc_str, $loc_prefix) != false) {
        $base_loc = substr($loc_str, 3);
        //$base_loc += 1;
        $base_loc = (int)$base_loc;
    }
    return $base_loc;
}

function qstring_decode($phrase)
{
    $ret_str = strtoupper($phrase);
    $ret_str = str_replace("__", "/", $ret_str);
    return $ret_str;
}

function find_processed_addr($addr_str)
{
	// expects a processed addr_str

	$result = es_bulk_qry($addr_str,"10");
	$json = json_decode($result);
	$hits = $json->hits->total;

	//echo $result;

	$ret_val = null;

	if ($hits == 1) {
		$ret_val = $json->hits->hits[0]->_source;
    }
    
	return $ret_val;
}

function find_sub_addresses($addr_str)
{
	// expects a base hash

	$result = es_bulk_base_qry($addr_str,"10");
	$json = json_decode($result);
	$hits = $json->hits->total;

	//echo $result;

	$ret_val = null;

	if ($hits > 1) {
		$ret_val = $json->hits->hits;
	}

	return $ret_val;
}

function get_google_arr($addr_str)
{
	$addr_str = str_replace("__", "/ ", $addr_str); 
	$curl_url = "https://maps.googleapis.com/maps/api/geocode/json?address=" . urlencode($addr_str) . "&region=au&key=AIzaSyDcE2tHUuIsXqNLwIgtoJ16D-N5b1F7XFM";
	$result = hit_curl("GET",$curl_url,"");

	$json = json_decode($result);
	$components = $json->results[0]->address_components;
	$g_addr = [];

	foreach($components as $val) {
      	$google_arr[$val->types[0]] = $val->short_name;
   	}

   	return $google_arr;
}

function get_base_addr_google($google_arr)
{
   	$ret_str = $google_arr["street_number"] . " " . $google_arr["route"] . " " . $google_arr["locality"] . " " . $google_arr["administrative_area_level_1"] . " " . $google_arr["postal_code"];
	return $ret_str;
}

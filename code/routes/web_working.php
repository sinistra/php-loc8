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

	$tasks = DB::table('tasks')->latest()->get();

    return view('welcome', compact('tasks'));
});

Route::get('/tasks', function () {

	$tasks = DB::table('tasks')->latest()->get();


    return view('tasks.index', compact('tasks'));
});

Route::get('/loc8', function () {

    return view('loc8.search', compact('locs'));

});

Route::get('/locs/{id_num}', function ($id_num) {

	$locs = DB::table('pfl_raw')
		->where('NBN_LOCATION_IDENTIFIER', '=', $id_num)
		->get();

    return view('locs.list', compact('locs'));

});

Route::get('/locs/{search_key}/{search_val}/{query_type}/{page_num}', function ($search_key,$search_val,$query_type,$page_num) {

	if (strtolower($search_key) == 'mt') { $search_key = 'MT_LOCID'; }
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

    return view('loc8.list', compact('locs'));

});

Route::get('/loc8/load/{load_from}/{load_to}', function ($load_from,$load_to) {

	// this grabs loc IDs from mySQL in a batch and puts them into elastic one at a time
	// once this is working the plan will be to optimise into a bulk load function
	// instead of one at a time.

	$search_key = 'MT_LOCID';
	$query_type = '>=';
	$load_qty = $load_to - $load_from + 1;

	echo "records " . $load_from . " - " . $load_to;

	$locs = DB::table('pfl_lean')
		->where($search_key, $query_type, $load_from)
		->limit($load_qty)
		->get();

	es_load_bulk($locs);

});

Route::get('/loc8/load', function () {

    return view('loc8.load');
});

Route::get('/loc8/qry/{search_str}/{ret_limit}/{qry_type}', function ($search_str,$ret_limit,$qry_type) {

	// this takes a search string and returns the list of records from elastic
	// that match the search term. only based on formatted address for now

	$result = es_qry(strtolower($search_str),$ret_limit,$qry_type);
	$json = json_decode($result);

	//echo "took: " . $json->took . "ms<br>";

	$found_array = array();
	$hits = $json->hits->hits;
	foreach($hits as $hit) {
      	$found_array[] = ["loc" => $hit->_source->alias_address, "geo" => $hit->_source->geo_location];
   	}

    header('Content-type: application/json');
    header('Access-Control-Allow-Origin: *');

	echo json_encode($found_array);

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

function hit_curl($meth,$post_url,$post_data)
{
	$ch = curl_init();

	if ($ch === false) { throw new Exception('failed to initialize'); }

	curl_setopt($ch, CURLOPT_URL, $post_url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, strtoupper($meth));
	curl_setopt($ch, CURLOPT_HTTPHEADER,array('Content-Type: application/json'));
	curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
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

		//$base_hash = hexdec( substr(sha1(get_base_address($loc->FORMATTED_ADDRESS_STRING)), 0, 15) );
		$base_hash = substr(md5(get_base_address($loc->FORMATTED_ADDRESS_STRING)), 0, 15);
    	$search_addr_suffix = " | " . $loc->NBN_LOCID . " | " . get_loc_num($loc->NBN_LOCID) . " | MT" . $loc->MT_LOCID;

    	$search_addr = array();

    	// alias type 1 = base address with other bits
    	$search_addr[1] = get_base_address($loc->FORMATTED_ADDRESS_STRING) . $search_addr_suffix;

    	// alias type 2 = original formatted address from nbn with other bits
    	$search_addr[2] = $loc->FORMATTED_ADDRESS_STRING . $search_addr_suffix;

		for ($i = 1; $i <= 2; $i++) {

			//echo "<br>i= " . $i . "<br>";

			// we want unique base addresses, so for them create the id based on a hash of the base address string
			// baseically, if the base-hash is the id for the base addresses, when other LOCs weith the same base
			// address get loaded, they will simply over-write the previous record with that id, so there will only
			// ever be a single record for each base address
			if ($i == 1) { $rec_id = $base_hash; }

			// for all other addresses base it off the MT_LOCID which itself is based on an auto-increment in mysql
			else { $rec_id = $loc->MT_LOCID + ($i * 100000000); }

			$curl_data .= '{"index":{"_index":"pfl","_type":"doc","_id":"' . $rec_id . '"}}' . "\n";
			$curl_data .= '{ ';
			$curl_data .= '"alias_address" : "' . addslashes($search_addr[$i]) . '", ';
			$curl_data .= '"official_nbn_address" : "' . addslashes($loc->FORMATTED_ADDRESS_STRING) . '", ';
			$curl_data .= '"base_hash" : "' . $base_hash . '", ';
			$curl_data .= '"nbn_locid" : "' . $loc->NBN_LOCID . '", ';
			$curl_data .= '"mt_locid" : "' . $loc->MT_LOCID . '", ';
			$curl_data .= '"gnaf_locid" : "' . $loc->GNAF_PERSISTENT_IDENTIFIER . '", ';
			$curl_data .= '"geo_location" : { "lat": "' . $loc->LATITUDE . '", "lon": "' . $loc->LONGITUDE . '" }, ';
			$curl_data .= '"alias_type" : "' . $i . '"';
			$curl_data .= ' }' . "\n";
			//echo "<br>cd= <br>" . $curl_data . "<br>";
			//echo "<br>cr= <br>" . $curl_result . "<br>";
		}
    }

    $curl_result = hit_curl("POST", $curl_url, $curl_data);
    //echo "<br>" . $curl_result . "<br>";
    echo "  ...done";
}

function es_load($locs)
{
    foreach ($locs as $loc) {

		//$base_hash = hexdec( substr(sha1(get_base_address($loc->FORMATTED_ADDRESS_STRING)), 0, 15) );
		$base_hash = substr(md5(get_base_address($loc->FORMATTED_ADDRESS_STRING)), 0, 15);
    	$search_addr_suffix = " | " . $loc->NBN_LOCID . " | " . get_loc_num($loc->NBN_LOCID) . " | MT" . $loc->MT_LOCID;

    	$search_addr = array();

    	// alias type 1 = base address with other bits
    	$search_addr[1] = get_base_address($loc->FORMATTED_ADDRESS_STRING) . $search_addr_suffix;

    	// alias type 2 = original formatted address from nbn with other bits
    	$search_addr[2] = $loc->FORMATTED_ADDRESS_STRING . $search_addr_suffix;

		for ($i = 1; $i <= 2; $i++) {

			//echo "<br>i= " . $i . "<br>";

			// we want unique base addresses, so for them create the id based on a hash of the base address string
			// baseically, if the base-hash is the id for the base addresses, when other LOCs weith the same base
			// address get loaded, they will simply over-write the previous record with that id, so there will only
			// ever be a single record for each base address
			if ($i == 1) { $rec_id = $base_hash; }

			// for all other addresses base it off the MT_LOCID which itself is based on an auto-increment in mysql
			else { $rec_id = $loc->MT_LOCID + ($i * 100000000); }

    		$curl_url = "laradock_elasticsearch_1:9200/pfl/doc/" . $rec_id;

			$curl_data  = '{ ';
			$curl_data .= '"alias_address" : "' . addslashes($search_addr[$i]) . '", ';
			$curl_data .= '"official_nbn_address" : "' . addslashes($loc->FORMATTED_ADDRESS_STRING) . '", ';
			$curl_data .= '"base_hash" : "' . $base_hash . '", ';
			$curl_data .= '"nbn_locid" : "' . $loc->NBN_LOCID . '", ';
			$curl_data .= '"mt_locid" : "' . $loc->MT_LOCID . '", ';
			$curl_data .= '"gnaf_locid" : "' . $loc->GNAF_PERSISTENT_IDENTIFIER . '", ';
			$curl_data .= '"geo_location" : { "lat": "' . $loc->LATITUDE . '", "lon": "' . $loc->LONGITUDE . '" }, ';
			$curl_data .= '"alias_type" : "' . $i . '"';
			$curl_data .= ' }';
			//echo "<br>cd= <br>" . $curl_data . "<br>";

			$curl_result = hit_curl("POST", $curl_url, $curl_data);
			//echo "<br>cr= <br>" . $curl_result . "<br>";
		}
    }

    echo "  ...done";
}

function es_qry($search_str, $res_limit,$qry_type)
{
    $curl_url = "laradock_elasticsearch_1:9200/pfl/_search/";
    if ($qry_type == "default") {
    	$qry_data = '{ "from" : 0, "size" : ' . $res_limit . ', "query": { "match": { "alias_address": { "query": "' . $search_str . '", "operator": "and" } } } }';
 	}
 	elseif ($qry_type == "base_addr") {
    	$qry_data = '{ "from" : 0, "size" : ' . $res_limit . ', "query": { "bool" : { "must": { "match": { "alias_address": { "query": "' . $search_str . '", "operator": "and" } } }, "filter": { "match": { "alias_type": { "query": "1", "operator": "and" } } } } } }';
	}
	$curl_result = hit_curl("POST", $curl_url, $qry_data);

    return $curl_result;
}

function delete_last_hint($addr_str)
{
	if (substr($addr_str,-1) == ")") { // if the last char is a ) then it must have a hint at the end
	    $ret_str = strstr(strrev($addr_str), "("); // remove everything after the last open brackets
	    $ret_str = substr(strrev($ret_str),0,-1);
	    $ret_str = trim($ret_str);
	}
    return $addr_str;
}

function delete_all_hints($addr_str)
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

function delete_sub_addr($addr_str)
{
	// take only the last 2 portions delimited with brackets - which should get rid of the sub-address and no more
    $addr_arr = array_reverse(explode(", ", $addr_str));
    $ret_str = $addr_arr[1] . ", " . $addr_arr[0];
    return $ret_str;
}

function get_base_address($addr_str)
{
    $ret_str = delete_all_hints($addr_str);
    $ret_str = delete_sub_addr($ret_str);
    return $ret_str;
}

function get_loc_num($loc_str)
{
	//get the number part of a loc id with leading zero's stripped	
	$base_loc = "";
	if (strstr($loc_str, "LOC") != false) {
	    $base_loc = substr($loc_str, 3);
	    $base_loc += 0;
	}
    return $base_loc;
}
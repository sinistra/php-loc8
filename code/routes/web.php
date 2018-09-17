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

    return view('loc8.search', compact('locs'));
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

Route::get('/locs/{search_key}/{search_val}/{query_type}/{page_num}', function ($search_key, $search_val, $query_type, $page_num) {

    if (strtolower($search_key) == 'mt') {
        $search_key = 'UID';
    } elseif (strtolower($search_key) == 'nbn') {
        $search_key = 'NBN_LOCID';
    } elseif (strtolower($search_key) == 'addr') {
        $search_key = 'FORMATTED_ADDRESS_STRING';
    }

    if (strtolower($query_type) == 'is') {
        $query_type = '=';
    } elseif (strtolower($query_type) == 'like') {
        $query_type = 'like';
        $search_val = '%' . $search_val . '%';
    } elseif (strtolower($query_type) == 'sw') {
        $query_type = 'like';
        $search_val = $search_val . '%';
    } elseif (strtolower($query_type) == 'ew') {
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

    $id_num = get_loc_num($mt_locid, "MTL");
    $locs = DB::table('pfl_raw')
        ->where('UID', '=', $id_num)
        ->limit(1)
        ->get();

    return $locs;
});

Route::get('/loc8/load/{load_from}/{load_to}', function ($load_from, $load_to) {

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

Route::get('/loc8/qry/{search_str}/{ret_limit}/{qry_type}', function ($search_str, $ret_limit, $qry_type) {

    // this takes a search string and returns the list of records from elastic
    // that match the search term. only based on formatted address for now

    $result = es_qry(qstring_decode($search_str), $ret_limit, $qry_type);
    $json = json_decode($result);

    //echo "took: " . $json->took . "ms<br>";

    $found_array = array();
    $hits = $json->hits->hits;
    foreach ($hits as $hit) {
        $found_array[] = ["loc" => $hit->_source->alias_address, "geo" => $hit->_source->geo_location, "hash" => $hit->_source->base_hash];
    }

    header('Content-type: application/json');
    echo json_encode($found_array);

});

Route::get('/loc8/base_qry/{search_str}/{ret_limit}', function ($search_str, $ret_limit) {

    // this takes a basehash id and returns the list of records from elastic
    // that match the base hash. ie. for finding all sub addresses at a base address.

    $result = es_base_qry($search_str, $ret_limit);
    $json = json_decode($result);

    //echo "took: " . $json->took . "ms<br>";

    $found_array = array();
    $hits = $json->hits->hits;

    $base_addr_str = get_base_address($hits[0]->_source->official_nbn_address);
    $found_array_1[] = ["nbn_st_addr" => get_st_address($base_addr_str), "nbn_suburb" => get_suburb($base_addr_str)];
    foreach ($hits as $hit) {
        $nbn_addr = $hit->_source->official_nbn_address;
        $found_array_2[] = ["nbn_sub_addr" => get_sub_address($nbn_addr), "mt_locid" => $hit->_source->mt_locid, "geo" => $hit->_source->geo_location, "nbn_locid" => $hit->_source->nbn_locid, "gnaf_locid" => $hit->_source->gnaf_locid, "tech" => $hit->_source->tech, "rfs" => $hit->_source->rfs, "serv_class" => $hit->_source->serv_class];
    }

    asort($found_array_2);
    $found_array = array_merge($found_array_1, $found_array_2);

    header('Content-type: application/json');
    echo json_encode($found_array);
    //echo $result;

});

Route::get('/loc8/nearby_qry/{lat}/{lon}/{ret_limit}', function ($lat, $lon, $ret_limit) {

    // this takes a the co-ords for a point and returns the list of records from elastic
    // that are base addresses near the point. ie. for finding all nearby base addresses.

    //lat = "-33.378395";
    //$lon = "151.370702";

    $result = es_nearby_qry($lat, $lon, $ret_limit);

    $json = json_decode($result);

    //echo "took: " . $json->took . "ms<br>";

    $found_array = array();
    $hits = $json->hits->hits;

    foreach ($hits as $hit) {
        $base_addr_str = get_base_address($hit->_source->official_nbn_address);
        $dist = round($hit->sort[0] * 1000);

        //echo $hit->_source->base_hash . "<br>";
        $subs_count_result = es_base_qry($hit->_source->base_hash, "1");
        $subs_count_json = json_decode($subs_count_result);

        $found_array[] = ["nbn_st_addr" => get_st_address($base_addr_str), "nbn_suburb" => get_suburb($base_addr_str), "dist" => $dist, "count" => $subs_count_json->hits->total, "geo" => $hit->_source->geo_location, "mt" => $hit->_source->mt_locid];
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

function hit_curl($meth, $post_url, $post_data)
{
    $ch = curl_init();

    if ($ch === false) {
        throw new Exception('failed to initialize');
    }

    curl_setopt($ch, CURLOPT_URL, $post_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, strtoupper($meth));
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
    $result = curl_exec($ch);

    if ($result === false) {
        throw new Exception(curl_error($ch), curl_errno($ch));
    }
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
        $mt_locid = "MTL" . sprintf("%'.012d", $loc->UID);
        $search_addr_suffix = " | " . $loc->NBN_LOCID . " | L" . get_loc_num($loc->NBN_LOCID, "LOC") . " | " . $mt_locid;

        $search_addr = array();

        // alias type 1 = base address with other bits
        $search_addr[1] = get_base_address($loc->FORMATTED_ADDRESS_STRING) . $search_addr_suffix;

        // alias type 2 = original formatted address from nbn with other bits
        $search_addr[2] = $loc->FORMATTED_ADDRESS_STRING . $search_addr_suffix;

        // alias type 3 = unit address as eg '4/'' instead of 'unit 4,' plus base address with other bits
        if ($loc->UNIT_NUMBER != "") {
            $search_addr[3] = $loc->UNIT_NUMBER . "/" . get_base_address($loc->FORMATTED_ADDRESS_STRING) . $search_addr_suffix;
        }

        // alias type 4 = base address with underscores so that you can force it to find st-num and st-type and.. also with other bits
        $search_addr[4] = get_snake_address($loc->FORMATTED_ADDRESS_STRING) . $search_addr_suffix;

        for ($i = 1; $i <= 4; $i++) {

            //echo "<br>i= " . $i . "<br>";

            if (isset($search_addr[$i])) {

                // we want unique base addresses, so for them create the id based on a hash of the base address string
                // basically, if the base-hash is the id for the base addresses, when other LOCs weith the same base
                // address get loaded, they will simply over-write the previous record with that id, so there will only
                // ever be a single record for each base address
                if (($i == 1) || ($i == 4)) {
                    $rec_id = $i . $base_hash;
                } // for all other addresses base it off the MT LOPCID (ie. UID) which itself is based on an auto-increment in mysql
                else {
                    $rec_id = $loc->UID + ($i * 100000000000);
                }

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

function es_qry($search_str, $res_limit, $qry_type)
{
    $qry_array = explode("|", $qry_type);
    $curl_url = "laradock_elasticsearch_1:9200/pfl/_search/";

    // always show base addresses by default, then show other things based on what is ticked by the user
    $alias_types = "1";
    if (in_array("subs", $qry_array)) {
        $alias_types .= ",2";
    }
    if (in_array("aliass", $qry_array)) {
        $alias_types .= ",3,4";
    }

    $qry_data = '{ "from" : 0, "size" : ' . $res_limit . ', "query": { "bool" : { "must": { "match": { "alias_address": { "query": "' . $search_str . '", "operator": "and" } } }, "filter": { "terms": { "alias_type": [' . $alias_types . '] } } } } } }';

    //echo $qry_data;

    $curl_result = hit_curl("POST", $curl_url, $qry_data);

    return $curl_result;
}

function es_base_qry($search_str, $res_limit)
{
    $curl_url = "laradock_elasticsearch_1:9200/pfl/_search/";
    $qry_data = '{ "from" : 0, "size" : ' . $res_limit . ', "query": { "bool" : { "must": { "match": { "base_hash": { "query": "' . $search_str . '", "operator": "and" } } }, "filter": { "match": { "alias_type": { "query": "2", "operator": "and" } } } } } }';

    $curl_result = hit_curl("POST", $curl_url, $qry_data);
    return $curl_result;
}

function es_nearby_qry($lat, $lon, $res_limit)
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

function delete_last_hint($addr_str)
{
    if (substr($addr_str, -1) == ")") { // if the last char is a ) then it must have a hint at the end
        $ret_str = strstr(strrev($addr_str), "("); // remove everything after the last open brackets
        $ret_str = substr(strrev($ret_str), 0, -1);
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

    $ret_str = preg_replace("/\([^)]+\)/", "", $addr_str);
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

function get_snake_address($addr_str)
{
    $ret_str = get_base_address($addr_str);
    $ret_str = str_replace(" ", "_", $ret_str);;
    return $ret_str;
}

function get_sub_address($addr_str)
{
    $sub_addr_str = get_base_address($addr_str);
    $sub_addr_str = str_replace($sub_addr_str, " ", $addr_str);
    $spaces = array("     ", "   ", "  ");
    $ret_str = str_replace($spaces, " ", $sub_addr_str);
    $ret_str = trim($ret_str);
    $ret_str = rtrim($ret_str, ',');
    if ($ret_str == "") {
        $ret_str = "-";
    }
    return $ret_str;
}

function get_st_address($addr_str)
{
    $addr_arr = explode(", ", $addr_str);
    $ret_str = trim($addr_arr[0]);
    return $ret_str;
}

function get_suburb($addr_str)
{
    $addr_arr = explode(", ", $addr_str);
    $ret_str = trim($addr_arr[1]);
    return $ret_str;
}

function get_loc_num($loc_str, $loc_prefix)
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

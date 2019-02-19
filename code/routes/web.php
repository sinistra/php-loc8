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

Route::get('/loc8/map/{type}/{str}', function ($type, $str) {

    return view('loc8.search', compact('type'), compact('str'));

});

Route::get('/locs/{id_num}', function ($id_num) {

    $locs = DB::table('pfl')
        ->where('nbn_locid', '=', $id_num)
        ->get();

    return view('locs.list', compact('locs'));

});

Route::get('/locs/{search_key}/{search_val}/{query_type}/{page_num}', function ($search_key, $search_val, $query_type, $page_num) {

    if (strtolower($search_key) == 'mt') {
        $search_key = 'id';
    } elseif (strtolower($search_key) == 'pfl') {
        $search_key = 'source_locid';
    } elseif (strtolower($search_key) == 'addr') {
        $search_key = 'formatted_address_string';
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

    $index_source = strtolower(substr($mt_locid, 3,3));
    $id_field = get_source_locid_field($index_source);
    $id_val = substr($mt_locid, 6);

    $locs = DB::table($index_source)
        ->where($id_field, '=', $id_val)
        ->limit(1)
        ->get();

    return $locs;
});

Route::get('/loc8/load/{index_type}/{index_source}/{load_from}/{load_to}', function ($index_type, $index_source, $load_from, $load_to) {

    // this grabs loc IDs from mySQL in a batch and puts them into elastic one at a time
    // once this is working the plan will be to optimise into a bulk load function
    // instead of one at a time.

    $search_key = 'id';
    $load_qty = $load_to - $load_from + 1;

    echo "records " . $load_from . " - " . $load_to;

    $locs = DB::table('pfl')
        ->whereBetween($search_key, [$load_from, $load_to])->get();

    es_load_bulk($index_type, $index_source, $locs);

});

Route::get('/loc8/load', function () {

    return view('loc8.load');
});

Route::get('/loc8/tester', function () {

    return view('loc8.tester');
});

Route::get('/loc8/mock', function () {

    return view('loc8.mock');
});

Route::get('/loc8/bulk', function () {

    return view('loc8.bulk');
});

Route::get('/loc8/bulk-old', function () {

    return view('loc8.bulk-old');
});

Route::get('/loc8/match/{provider}/{return_type}/{search_str}', function ($provider, $return_type, $search_str) {

    if ($provider == "nbn") { $index_source = "pfl"; }

    header("Access-Control-Allow-Origin: *");

    return get_source_match($provider, $return_type, $index_source, $search_str);

});

Route::get('/loc8/qry/{index_type}/{index_source}/{ret_limit}/{qry_type}/{search_str}', function ($index_type, $index_source, $ret_limit, $qry_type, $search_str) {

    // this takes a search string and returns the list of records from elastic
    // that match the search term. only based on formatted address for now

    $result = es_qry($index_type, $index_source, qstring_decode($search_str), $ret_limit, $qry_type);
    $json = json_decode($result);

    $found_array = array();
    $hits = $json->hits->hits;
    foreach ($hits as $hit) {
        $found_array[] = ["loc" => $hit->_source->alias_address, "geo" => $hit->_source->geo_location, "hash" => $hit->_source->base_hash, "alias" => $hit->_source->alias_type];
    }

    header('Content-type: application/json');
    echo json_encode($found_array, JSON_PRETTY_PRINT);

});

Route::get('/loc8/base_qry/{index_type}/{index_source}/{search_str}/{ret_limit}', function ($index_type, $index_source, $search_str, $ret_limit) {

    // this takes a basehash id and returns the list of records from elastic
    // that match the base hash. ie. for finding all sub addresses at a base address.

    $result = es_base_qry($index_type, $index_source, $search_str, $ret_limit);
    $json = json_decode($result);

    $found_array = array();
    $found_array_2 = array();
    $hits = $json->hits->hits;
    //print_r($hits);

    foreach ($hits as $hit) {
        if (in_array($hit->_source->alias_type, [1])) { // add the base address here - but only 1 at most incase the pfl has a base record and sub addresses
            $base_addr_str = get_base_addr_pfl($hits[0]->_source->official_address);
            $found_array_1[] = ["st_addr" => get_st_addr_pfl($base_addr_str), "suburb" => get_suburb_pfl($base_addr_str)];
        }
        if (in_array($hit->_source->alias_type, [2])) { // add the sub-addresses here
            $source_addr = $hit->_source->official_address;
            $found_array_2[] = ["sub_addr" => get_sub_addr_pfl($source_addr), "mt_locid" => $hit->_source->mt_locid, "geo" => $hit->_source->geo_location, "source_locid" => $hit->_source->source_locid, "alias" => $hit->_source->alias_type];
        }
    }

    asort($found_array_2);
    $found_array = array_merge($found_array_1, $found_array_2);

    header('Content-type: application/json');
    echo json_encode($found_array);

});

Route::get('/loc8/nearby_qry/{index_type}/{index_source}/{lat}/{lon}/{ret_limit}', function ($index_type, $index_source, $lat, $lon, $ret_limit) {

    // this takes a the co-ords for a point and returns the list of records from elastic
    // that are base addresses near the point. ie. for finding all nearby base addresses.
    $hashes_arr = [];
    $count_arr = [];
    $found_arr = [];

    $result_1 = es_nearby_qry($index_type, $index_source,$lat, $lon, $ret_limit);
    $json_1 = json_decode($result_1);
    $hits_1 = $json_1->hits->hits;

    // get the base hashes for what is nearby
    foreach ($hits_1 as $hit) {
        $hashes_arr[] = $hit->_source->base_hash;
    }
    $hashes_str = implode($hashes_arr, '","');

    // now count up how many results are at each base hash
    $result_2 = es_bases_qry($index_type, $index_source, $hashes_str, "10000");
    $json_2 = json_decode($result_2);
    $hits_2 = $json_2->hits->hits;

    foreach ($hits_2 as $hit) {
        if (array_key_exists($hit->_source->base_hash, $count_arr)) {
            $count_arr[$hit->_source->base_hash] += 1;
        }
        else {
           $count_arr[$hit->_source->base_hash] = 0;
        }
    }

    // now create the return json
    foreach ($hits_1 as $hit) {
        $base_addr_str = get_base_addr_pfl($hit->_source->official_address);
        $dist = round($hit->sort[0] * 1000);
        $subs_total = $count_arr[$hit->_source->base_hash];
        $found_arr[] = ["st_addr" => get_st_addr_pfl($base_addr_str), "suburb" => get_suburb_pfl($base_addr_str), "dist" => $dist, "count" => $subs_total, "geo" => $hit->_source->geo_location, "mt" => $hit->_source->mt_locid, "serv_class" => $hit->_source->serv_class, "tech" => $hit->_source->tech];
    }

    header('Content-type: application/json');
    echo json_encode($found_arr);

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

function hit_curl($meth, $curl_url, $post_data)
{
    $ch = curl_init();
    if ($ch === false) {
        throw new Exception('failed to initialize');
    }

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_URL, $curl_url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));

    if ($meth == "POST") {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, strtoupper($meth));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
    }

    $result = curl_exec($ch);

    if ($result === false) {
        throw new Exception(curl_error($ch), curl_errno($ch));
    }
    curl_close($ch);

    return $result;
}

function es_load_bulk($index_type, $index_source, $locs)
{
    $elasticHost = env('ELASTIC_HOST', '127.0.0.1') . ":9200";
    $elasticIndex = "loc8_" . $index_type . "_" . $index_source;
    $source_locid = get_source_locid_field($index_source);

    $curl_url = $elasticHost . "/" . $elasticIndex . "/_bulk";
    $curl_data = "";

    // types are;
    // suggest - for map based autosuggest
    // lucky - for i'm feeling lucky, for the bulk match api that returns a single result and score

    if (count($locs) > 0) {
        foreach ($locs as $loc) {

            $base_addr = get_base_addr_pfl($loc->formatted_address_string);
            if ($base_addr != $loc->formatted_address_string) {
                $is_mdu = 1;
            } else {
                $is_mdu = 0;
            }

            $simple_addr = " " . $loc->locality_name . " " . $loc->state_territory_code . " " . $loc->postcode;
            $st_addr = $loc->road_name . " " . $loc->road_type_code . $simple_addr;

            $base_hash = substr(md5(get_base_addr_pfl($loc->formatted_address_string)), 0, 15);

            $mt_locid = "MTL" . strtoupper($index_source) . $loc->$source_locid;
            $search_addr_suffix = " | " . $mt_locid . " | " . $loc->$source_locid;

            $search_addr = array();

            if ($index_type == "suggest") {

                // alias type 1 = dummy base address with other bits
                $search_addr[1] = get_base_addr_pfl($loc->formatted_address_string) . $search_addr_suffix;

                // alias type 2 = full-address from pfl with other bits
                $search_addr[2] = $loc->formatted_address_string . $search_addr_suffix;

                if ($is_mdu == 1) {

                    // alias type 3 = unit address as eg '4/ ' instead of 'unit 4,' plus base address with other bits
                    if ($loc->unit_number != "") {
                        $search_addr[3] = $loc->unit_number . "/ " . get_base_addr_pfl($loc->formatted_address_string) . $search_addr_suffix;
                    }
                }
            }
            else { // ie. if ($type == "lucky")

                // alias type 4 = processed full-address with underscores so that you can force it to find st-num and st-type and.. also with other bits
                $search_addr[4] = get_processed_addr($loc->formatted_address_string) . $search_addr_suffix;

                // alias type 5 = dummy processed base address with underscores so that you can force it to find st-num and st-type and.. also with other bits - specifically for MDUs
                $search_addr[5] = get_processed_base_addr_pfl($loc->formatted_address_string) . $search_addr_suffix;

                if ($is_mdu == 1) {

                    // alias type 6 = string of sub-address tokens
                    $search_addr[6] = get_sub_addr_tokens_pfl($loc->formatted_address_string);
                }

                if (($loc->unit_number == null) && ($loc->unit_type_code == null) && ($loc->lot_number != null)) {

                    // lot alias for bulk - for street numbers that have a lot number (not unit numbers that are named with a lot number)
                    $search_addr[7] = get_processed_addr("LOT " . $loc->lot_number . " " . $st_addr);
                }

                if (($loc->road_number_1 != null) && ($loc->road_number_2 != null)) {

                    // range addr aliases for num_first and num_last for bulk
                    $search_addr[8] = get_processed_addr($loc->road_number_1 . " " . $st_addr);
                    $search_addr[9] = get_processed_addr($loc->road_number_2 . " " . $st_addr);
                }

                if (get_processed_complex_addr($loc->address_site_name) != null) {

                    // site name aliases shopping centres, building names etc
                    $search_addr[10] = get_processed_addr(get_processed_complex_addr($loc->address_site_name) . " " . $simple_addr);
                }

                if ((get_processed_complex_addr($loc->secondary_complex_name) != null) && (strtoupper($loc->secondary_complex_name) != strtoupper($loc->address_site_name))) {

                    // complex name aliases for retirement villages, shopping centres etc (can be a duplicate of address_site_name in the raw data sometimes)
                    $search_addr[11] = get_processed_addr(get_processed_complex_addr($loc->secondary_complex_name) . " " . $simple_addr);
                }
            }


            for ($i = 1; $i <= 11; $i++) {

                if (isset($search_addr[$i])) {

                    $curl_formatted_addr = $loc->formatted_address_string;
                    $curl_serv_class = $loc->service_class;

                    if (in_array($i, [1, 5, 7, 8, 9])) { // if this is a base address

                        $rec_id = $i . $base_hash; // use base hash as record id for base addresses to avoid duplicates

                        if ($is_mdu == 1) { // if this is a base address for an mdu, then dont use the standard vals as this is basically a dummy record
                            $curl_formatted_addr = get_base_addr_pfl($loc->formatted_address_string);
                        }
                    } elseif ($i == 10) {
                        // there are often different entries for site name at the same sub address - we want to get all of them uniquely
                        $rec_id = $i . $base_hash . substr(md5($loc->address_site_name), 0, 10);
                    } elseif ($i == 11) {
                        // there are often different entries for complex name at the same sub address - we want to get all of them uniquely
                        $rec_id = $i . $base_hash . substr(md5($loc->secondary_complex_name), 0, 10);
                    } else { // for all other addresses base it off the MT LOPCID (ie. UID) which itself is based on an auto-increment in mysql
                        $rec_id = $loc->id + ($i * 100000000000);
                    }

                    $curl_data .= '{"index":{"_index":"' . $elasticIndex . '","_type":"doc","_id":"' . $rec_id . '"}}' . "\n";
                    $curl_data .= '{ ';
                    $curl_data .= safe_curl("alias_address", $search_addr[$i]) . ', ';
                    $curl_data .= safe_curl("official_address", $curl_formatted_addr) . ', ';
                    $curl_data .= '"base_hash" : "' . $base_hash . '", ';
                    $curl_data .= '"source_locid" : "' . $loc->$source_locid . '", ';
                    $curl_data .= '"source_name" : "' . $index_source . '", ';
                    $curl_data .= '"mt_locid" : "' . $mt_locid . '", ';
                    $curl_data .= '"tech": "' . get_proc_serv_type($loc->service_type) . '", ';
                    $curl_data .= '"serv_class": "' . $curl_serv_class . '", ';
                    if (in_array($i, [4, 5, 6])) { // only add params for alias's used for the bulk resolver
                        $curl_data .= '"params" : { ';
                        $curl_data .= safe_curl_date("rfs_date", $loc->ready_for_service_date) . ', ';
                        $curl_data .= '"poi_name": "' . $loc->poi_name . '", ';
                        $curl_data .= '"poi_code": "' . $loc->poi_identifier . '", ';
                        $curl_data .= '"ada_code": "' . $loc->distribution_region_identifier . '", ';
                        $curl_data .= safe_curl_date("disc_date", $loc->disconnection_date);
                        $curl_data .= '}, ';
                        $curl_data .= '"pg_id" : "' . $loc->id . '", ';
                    }
                    $curl_data .= '"geo_location" : { "lat": "' . $loc->latitude . '", "lon": "' . $loc->longitude . '" }, ';
                    $curl_data .= '"alias_type" : "' . $i . '"';
                    $curl_data .= ' }' . "\n";
                }
            }
        }

        $result = hit_curl("POST", $curl_url, $curl_data);
        //echo $result;
        $json = json_decode($result);

        if (isset($json->errors)) {
            if ($json->errors) {
                $ret_str = " [errors]";
                $ret_str .= "<br><br>" . $curl_data;
                $ret_str .= "<br><br>" . $result;
            } else {
                $ret_str = " [ok. took: " . $json->took . "ms]";
            }
        } else {
            $ret_str = " [n/a]";
        }
    } else {
        $ret_str = " [no records]";
    }


    echo $ret_str;
}

function es_qry($index_type, $index_source, $search_str, $res_limit, $qry_type)
{
    $elasticHost = env('ELASTIC_HOST', '127.0.0.1') . ":9200";

    $qry_array = explode("|", $qry_type);
    $curl_url = $elasticHost . "/loc8_" . $index_type . "_" . $index_source . "/_search/";

    // always show base addresses by default, then show other things based on what is ticked by the user
    if (is_loc_id($search_str)) {
        $alias_types = "1,2";
        $res_limit = "1";
    }
    else {
        $alias_types = "1";
        if (in_array("subs", $qry_array)) {
            $alias_types .= ",2";
        }
        if (in_array("aliass", $qry_array)) {
            $alias_types .= ",3,4";
        }
    }

    $qry_data = '{ "from" : 0, "size" : ' . $res_limit . ', "query": { "bool" : { "must": { "match": { "alias_address": { "query": "' . $search_str . '", "operator": "and" } } }, "filter": { "terms": { "alias_type": [' . $alias_types . '] } } } }';
    $qry_data .= ', "sort": { "alias_type": { "order": "asc" } }';
    $qry_data .= ' }';

    $curl_result = hit_curl("POST", $curl_url, $qry_data);

    return $curl_result;
}

function es_base_qry($index_type, $index_source, $search_str, $res_limit)
{
    $elasticHost = env('ELASTIC_HOST', '127.0.0.1') . ":9200";

    $curl_url = $elasticHost . "/loc8_" . $index_type . "_" .  $index_source . "/_search/";
    $qry_data = '{ "from" : 0, "size" : ' . $res_limit . ', "query": { "bool" : { "must": { "match": { "base_hash": { "query": "' . $search_str . '", "operator": "and" } } }, "filter": { "terms": { "alias_type": [1,2] } } } } } }';
    $curl_result = hit_curl("POST", $curl_url, $qry_data);
    return $curl_result;
}

function es_bases_qry($index_type, $index_source, $search_str, $res_limit)
{
    $elasticHost = env('ELASTIC_HOST', '127.0.0.1') . ":9200";

    $curl_url = $elasticHost . "/loc8_" . $index_type . "_" .  $index_source . "/_search/";
    $qry_data = '{ "from" : 0, "size" : ' . $res_limit . ', "_source": ["mt_locid","base_hash"], "query": { "bool" : { "must": { "terms": { "base_hash": ["' . $search_str . '"] } } , "filter": { "terms": { "alias_type": [1,2] } } } } } }';
    $curl_result = hit_curl("POST", $curl_url, $qry_data);
    return $curl_result;
}

function es_nearby_qry($index_type, $index_source, $lat, $lon, $res_limit)
{
    $elasticHost = env('ELASTIC_HOST', '127.0.0.1') . ":9200";

    // 0.05 degrees is approx 5km
    $bound_dist = 0.05;

    // top left bound
    $tl_lat = $lat + $bound_dist;
    $tl_lon = $lon - $bound_dist;

    // bottom right bound
    $br_lat = $lat - $bound_dist;
    $br_lon = $lon + $bound_dist;

    $curl_url = $elasticHost . "/loc8_" . $index_type . "_" .  $index_source . "/_search/";
    $qry_data = '{ "from" : 0, "size" : ' . $res_limit . ', "query": { "bool" : { "must": { "terms": { "alias_type": [1] } } , "filter": { "geo_bounding_box": { "type": "indexed", "geo_location": { "top_left": { "lat":  ' . $tl_lat . ', "lon": ' . $tl_lon . ' }, "bottom_right": { "lat":  ' . $br_lat . ', "lon": ' . $br_lon . ' } } } } } }, "sort": [ { "_geo_distance": { "geo_location": { "lat":  ' . $lat . ', "lon": ' . $lon . ' }, "order": "asc", "unit": "km", "distance_type": "plane" } } ] }';
    $curl_result = hit_curl("POST", $curl_url, $qry_data);
    return $curl_result;
}

function es_bulk_qry($index_source, $search_str, $res_limit, $alias_types)
{
    $elasticHost = env('ELASTIC_HOST', '127.0.0.1') . ":9200";

    $curl_url = $elasticHost . "/loc8_lucky_" .  $index_source . "/_search/";
    $qry_data = '{ "from" : 0, "size" : ' . $res_limit . ', "query": { "bool" : { "must": { "match": { "alias_address": { "query": "' . $search_str . '", "operator": "and" } } }, "filter": { "terms": { "alias_type": [' . $alias_types . '] } } } } } }';
    $curl_result = hit_curl("POST", $curl_url, $qry_data);
    return $curl_result;
}

function es_bulk_base_qry($index_source, $search_str, $res_limit, $alias_types)
{
    $elasticHost = env('ELASTIC_HOST', '127.0.0.1') . ":9200";

    $curl_url = $elasticHost . "/loc8_lucky_" .  $index_source . "/_search/";
    $qry_data = '{ "from" : 0, "size" : ' . $res_limit . ', "query": { "bool" : { "must": { "terms": { "base_hash": ["' . $search_str . '"] } } , "filter": { "terms": { "alias_type": [' . $alias_types . '] } } } } } }';
    $curl_result = hit_curl("POST", $curl_url, $qry_data);
    return $curl_result;
}

function es_bulk_id_qry($index_source, $search_str, $res_limit, $type)
{
    $elasticHost = env('ELASTIC_HOST', '127.0.0.1') . ":9200";

    // type is the type of ID eg. PFL, MT, etc.
    if ($type == "MTL") {
        $must = "mt_locid";
    } else {
        $must = "source_locid";
    }

    $curl_url = $elasticHost . "/loc8_lucky_" .  $index_source . "/_search/";
    $qry_data = '{ "from" : 0, "size" : ' . $res_limit . ', "query": { "bool" : { "must": { "match": { "' . $must . '": { "query": "' . $search_str . '", "operator": "and" } } }, "filter": { "terms": { "alias_type": [4] } } } } } }';
    $curl_result = hit_curl("POST", $curl_url, $qry_data);
    return $curl_result;
}

function es_bulk_complex_name_qry($index_source, $search_str, $res_limit)
{
    $elasticHost = env('ELASTIC_HOST', '127.0.0.1') . ":9200";

    // search str is a base hash
    // looking for all type 10 and 11 alias's for that base hash
    $curl_url = $elasticHost . "/loc8_lucky_" .  $index_source . "/_search/";
    $qry_data = '{ "from" : 0, "size" : ' . $res_limit . ', "query": { "bool" : { "must": { "match": { "base_hash": { "query": "' . $search_str . '", "operator": "and" } } }, "filter": { "terms": { "alias_type": [10,11] } } } } } }';
    $curl_result = hit_curl("POST", $curl_url, $qry_data);
    return $curl_result;
}

function es_bulk_complex_hash_qry($index_source, $search_str, $res_limit)
{
    $elasticHost = env('ELASTIC_HOST', '127.0.0.1') . ":9200";

    // search str is a list of complex names (complex as in shopping complex)
    // looking for all type 10 and 11 alias's for given complex names
    $curl_url = $elasticHost . "/loc8_lucky_" .  $index_source . "/_search/";
    $qry_data = '{ "from" : 0, "size" : ' . $res_limit . ', "query": { "bool" : { "must": { "terms": { "alias_address": ["' . $search_str . '","xxx"] } } , "filter": { "terms": { "alias_type": [10,11] } } } } }';

    $curl_result = hit_curl("POST", $curl_url, $qry_data);
    return $curl_result;
}

function delete_addr_suffix_pfl($addr_str)
{
    // remove any portions with brackets

    // /  - opening delimiter (necessary for regular expressions, can be any character that doesn't appear in the regular expression
    // \( - Match an opening parenthesis
    // [^)]+ - Match 1 or more character that is not a closing parenthesis
    // \) - Match a closing parenthesis
    // /  - Closing delimiter

    $ret_str = strrev($addr_str);
    $ret_str = preg_replace("/\)[^(]+\(/", "", $ret_str);
    $ret_str = trim($ret_str);
    $ret_str = strrev($ret_str);
    return $ret_str;
}

function delete_addr_prefix_pfl($addr_str, $type)
{
    // take only the last 2 portions delimited with comma's - which should get rid of the sub-address and no more
    $addr_arr_1 = array_reverse(explode(", ", $addr_str));
    $ret_str = $addr_arr_1[1] . ", " . $addr_arr_1[0];

    // lot-only addresses have an official address where the lot number has a comma after it as though its a sub-address
    // on the off chance that this is a lot-only address (no street number) add the next bit if it has a lot in it
    // if we dont do this, then the whole street gets indexed as though its an mdu and only 1 point shows on the map
    $addr_arr_2 = explode(" ", $addr_arr_1[1]); // this would be the '50' if it was '50 smith st'
    if (array_key_exists(2, $addr_arr_1)) {
        //echo $addr_arr_2[0] . "<br>";
        if ((strpos($addr_arr_1[2], "LOT") !== false) && ((preg_match("/[0-9]/", ($addr_arr_2[0]))) == false)) {
            if ($type == "raw") {
                $delim = ", "; // where we use this in get_sub_addr_pfl we need the comma back in
            }
            else {
                $delim = " ";
            } 
            $ret_str =  $addr_arr_1[2] . $delim . $ret_str;
        }
    }
    return $ret_str;
}

function get_base_addr_pfl($addr_str)
{
    $ret_str = delete_addr_suffix_pfl($addr_str);
    $ret_str = delete_addr_prefix_pfl($ret_str, "def");
    return $ret_str;
}

function get_base_addr_pfl_raw($addr_str)
{
    $ret_str = delete_addr_suffix_pfl($addr_str);
    $ret_str = delete_addr_prefix_pfl($ret_str, "raw");
    return $ret_str;
}

function get_processed_base_addr_pfl($addr_str)
{
    $ret_str = get_base_addr_pfl($addr_str);
    $ret_str = get_processed_addr($ret_str);
    return $ret_str;
}

function get_snake_addr($addr_str)
{
    // this will process address strings for use at index and at search
    // so that they can be compared without having to worry about variants with commas without commas etc.

    $ret_str = strtoupper($addr_str);
    $strip_chars = array(",", "/", "(", ")", "[", "]", ".");
    $ret_str = str_replace($strip_chars, "", $ret_str); // remove specific special chars
    $ret_str = str_replace(" ", "_", trim($ret_str)); // trim then add underscores
    $ret_str = preg_replace('/([_])\1+/', '$1', $ret_str); // find any repeat underscores and turn it into a single underscore
    $ret_str = trim($ret_str);
    $ret_str = trim($ret_str, ',');
    $ret_str = trim($ret_str, '_');

    $ret_str = "_" . $ret_str . "_";
    return $ret_str;
}

function get_denormalised_words($addr_str)
{

    $from_to_types = array(
        "_ALLY" => "ALY",
        "_AVENUE" => "_AV",
        "_STREET" => "ST",
        "_ROAD" => "RD",
        "_PLACE" => "PL",
        "_HIGHWAY" => "HWY",
        "_CIRCUIT" => "CCT",
        "_CRCT" => "CCT",
        "_DRIVE" => "DR",
        "_DRV" => "DR",
        "_CRESCENT" => "CR",
        "_CRS" => "CR",
        "_COURT" => "CT",
        "_BOULEVARDE" => "BVD",
        "_PARKWAY" => "PWY",
        "_PKWY" => "PWY",
        "_BLVD" => "BVD",
        "_PROMENADE" => "PDE",
        "_PARADE" => "PDE",
        "_PLAZA" => "PLZA",
        "_PLZ" => "PLZA",
        "_WHARF" => "PROM",
        "_TERRACE_" => "TCE",
        "_WY" => "WAY",
        "_ESPLANADE" => "ESP",
        "_LANE" => "LN",
        "_LNE" => "LN",
        "_WHARF" => "WHRF",
    );

    $addr_str = qstring_decode($addr_str);
    $addr_arr = explode(" ", $addr_str);

    foreach ($addr_arr as $key1 => $val1) {
        foreach ($from_to_types as $key2 => $val2) {
            $pos = strpos($key2, "_" . $val1);
            if (($pos !== false) && ($from_to_types[$key2] != $val1) && (strlen($val1) > 3)) {
                $addr_arr[] = $from_to_types[$key2];
            }
        }
    }

    $ret_str = implode(" ", $addr_arr);
    return $ret_str;
}

function get_normalised_addr($addr_str)
{

    $from_to_types = array(
        "_ALLY_" => "_ALY_",
        "_AVENUE_" => "_AV_",
        "_AVE_" => "_AV_",
        "_STREET_" => "_ST_",
        "_STR_" => "_ST_",
        "_ROAD_" => "_RD_",
        "_PLACE_" => "_PL_",
        "_HIGHWAY_" => "_HWY_",
        "_CIRCUIT_" => "_CCT_",
        "_CRCT_" => "_CCT_",
        "_DRIVE_" => "_DR_",
        "_DRV_" => "_DR_",
        "_CRESCENT_" => "_CR_",
        "_CRES_" => "_CR_",
        "_CRS_" => "_CR_",
        "_COURT_" => "_CT_",
        "_BOULEVARD_" => "_BVD_",
        "_BOULEVARDE_" => "_BVD_",
        "_PARKWAY_" => "_PWY_",
        "_PKWY_" => "_PWY_",
        "_BLVD_" => "_BVD_",
        "_PROMENADE_" => "_PDE_",
        "_PARADE_" => "_PDE_",
        "_PLAZA_" => "_PLZA_",
        "_PLZ_" => "_PLZA_",
        "_WHARF_" => "_PROM_",
        "_TERRACE_" => "_TCE_",
        "_WY_" => "_WAY_",
        "_ESPLANADE_" => "_ESP_",
        "_LANE_" => "_LN_",
        "_LNE_" => "_LN_",
        "_WHARF_" => "_WHRF_",
        "_CHARTERS_TOWERS_CITY_" => "_CHARTERS_TOWERS_",
        "_BRISBANE_CITY_" => "_BRISBANE_",
        "_PALMERSTON_CITY_" => "_PALMERSTON_",
        "_DARWIN_CITY_" => "_DARWIN_",
        "_ROCKHAMPTON_CITY_" => "_ROCKHAMPTON_",
        "_TOOWOOMBA_CITY_" => "_TOOWOOMBA_",
        "_TOWNSVILLE_CITY_" => "_TOWNSVILLE_",
        "_CAIRNS_CITY_" => "_CAIRNS_",
        "_MOUNT_ISA_CITY_" => "_MOUNT_ISA_",
        "_SHELLHARBOUR_CITY_CENTRE_" => "_SHELLHARBOUR_"
    );

    $ret_str = str_replace(array_keys($from_to_types), array_values($from_to_types), $addr_str);
    return $ret_str;
}

function get_processed_addr($addr_str)
{
    $ret_str = get_snake_addr($addr_str); // set to uppercase and remove specific special chars and set to snake str
    $ret_str = get_normalised_addr($ret_str);
    return $ret_str;
}

function get_sub_addr_pfl($addr_str)
{
    $base_addr_str = get_base_addr_pfl_raw($addr_str);

    $ret_str = str_replace($base_addr_str, " ", $addr_str);
    $ret_str = preg_replace('/([ ])\1+/', '$1', $ret_str); // find any repeat whitespace and turn them into a single whitepace
    $ret_str = preg_replace('/([_])\1+/', '$1', $ret_str); // find any repeat underscores and turn them into a single underscore
    $ret_str = trim($ret_str);
    $ret_str = trim($ret_str, ',');
    $ret_str = trim($ret_str, '_');

    if ($ret_str == "") {
        $ret_str = "-";
    }

    return $ret_str;
}

function get_base_addr_bits_usr($proc_usr_str, $proc_base_str)
{
    // remove the sub addr bits from the user string so that the base-addr bits should remain
    // need to make sure to only remove each bit once - so if there is unit 20, 20 smith st, one
    // this could mess up the order of the base address and is used for comparison/scoring purposes only

    $proc_sub_str = get_sub_addr_usr($proc_usr_str, $proc_base_str);

    $sub_arr = explode("_", $proc_sub_str);
    $ret_str = $proc_usr_str;

    foreach ($sub_arr as $val) {
        $pos = strpos($ret_str, "_" . $val . "_");
        if ($pos !== false) {
            $ret_str = substr_replace($ret_str, "_", $pos, strlen("_" . $val . "_")); // this will only replace a single occurence
        }
    }

    $ret_str = preg_replace('/([_])\1+/', '$1', $ret_str); // find any repeat underscores and turn them into a single underscore
    $ret_str = trim($ret_str);
    $ret_str = trim($ret_str, ',');
    $ret_str = trim($ret_str, '_');

    return "_" . $ret_str . "_";
}

function get_sub_addr_usr($proc_usr_str, $proc_base_str)
{
    // remove the base addr bits from the user string so that the sub-addr bits should remain
    // need to make sure to only remove each bit once - so if there is unit 20, 20 smith st, one
    // occurence of the 20 remains to make a subaddress token

    $base_arr = explode("_", $proc_base_str);
    $ret_str = $proc_usr_str;

    foreach ($base_arr as $val) {
        $pos = strpos($ret_str, "_" . $val . "_");
        if ($pos !== false) {
            $ret_str = substr_replace($ret_str, "_", $pos, strlen("_" . $val . "_")); // this will only replace a single occurence
        }
    }

    $ret_str = preg_replace('/([_])\1+/', '$1', $ret_str); // find any repeat underscores and turn them into a single underscore
    $ret_str = trim($ret_str);
    $ret_str = trim($ret_str, ',');
    $ret_str = trim($ret_str, '_');

    if ($ret_str == "") { // if there ends up being no sub-address that the user typed then return null
        $ret_str = null;
    }

    return $ret_str;
}


function get_sub_addr_tokens($addr_str)
{
    if ($addr_str != null) {
        $ret_str = get_processed_addr($addr_str);

        // now break at points between letters and numbers with a pipe
        $arr = preg_split('/(?<=[0-9])(?=[a-z]+)/i', $ret_str);
        $ret_str = implode("|", $arr);
        $arr = preg_split('/(?<=[a-z])(?=[0-9]+)/i', $ret_str);
        $ret_str = implode("|", $arr);

        // now need to find keywords and see what is next after them
        // replace keywords with trailing space with standard keyword with trailing ":"

        $token_keywords = [
            "UNIT" => "UNIT",
            "U" => "UNIT",
            "LVL" => "LVL",
            "LEVEL" => "LVL",
            "L" => "L", // could be level or lot - have to leave as-is
            "FLOOR" => "FLR",
            "FLR" => "FLR",
            "ROOM" => "RM",
            "RM" => "RM",
            "TH" => "TH",
            "TNHS" => "TH",
            "TOWNHOUSE" => "TH",
            "LOT" => "LOT",
            "APPARTMENT" => "APT",
            "APPT" => "APT",
            "APT" => "APT",
            "S" => "S", // could be suite or shop - have to leave as-is
            "SUITE" => "SUITE",
            "SH" => "SHOP",
            "SHP" => "SHOP",
            "SHOP" => "SHOP",
            "OFFC" => "OFFC",
            "OFFICE" => "OFFC",
            "OFC" => "OFFC",
            "TENNANCY" => "TNCY",
            "TNCY" => "TNCY",
            "KIOSK" => "KSK",
            "KSK" => "KSK"
        ];

        // now insert keywords separated by _key_
        foreach ($token_keywords as $key => $val) {
            $from = "_" . $key . "_";
            $to = "_#" . $val . ":";
            $ret_str = str_replace($from, $to, $ret_str);
        }

        // now insert keywords separated by |key_
        foreach ($token_keywords as $key => $val) {
            $from = "|" . $key . "_";
            $to = "|#" . $val . ":";
            $ret_str = str_replace($from, $to, $ret_str);
        }

        // now insert keywords separated by _key|
        foreach ($token_keywords as $key => $val) {
            $from = "_" . $key . "|";
            $to = "_#" . $val . ":";
            $ret_str = str_replace($from, $to, $ret_str);
        }

        $ret_str = str_replace("|", "", $ret_str); // now remove the pipes and trim the ends
        $ret_str = trim($ret_str, '_'); // trim underscores off the start and end

        // now add an X: to anything that is not already a tuple
        $arr = explode("_", $ret_str);
        foreach ($arr as $key => $val) {
            if (strpos($val, ":") == false) {
                $arr[$key] = "X:" . $val;
            }
        }

        // now get rid of any duplicates
        $arr = array_unique($arr);

        $ret_str = implode("_", $arr);

        $ret_str = str_replace("#", "", $ret_str); // now remove the hashes
        $ret_str = preg_replace('/([:])\1+/', '$1', $ret_str); // find any repeat ":" and turn it into a single ":"
    } else {
        $ret_str = null;
    }
    return $ret_str;
}

function get_token_scores($str1, $str2)
{
    if ($str1 == $str2) {
        $score = 5;
    } else {
        $translation_scores = [
            "L_LVL" => 4,
            "LVL_L" => 4,
            "L_LOT" => 3,
            "LOT_L" => 3,
            "APT_UNIT" => 4,
            "UNIT_APT" => 4,
            "S_SUITE" => 3,
            "S_SHOP" => 3,
            "TH_UNIT" => 4,
            "UNIT_TH" => 4,
            "UNIT_SHOP" => 2,
            "UNIT_SUITE" => 4,
            "SUITE_UNIT" => 4,
            "SHOP_UNIT" => 2,
            "UNIT_SHOP" => 3,
            "FLR_LVL" => 4,
            "LVL_FLR" => 4,
            "TNCY_SHOP" => 3
        ];

        $translation = $str1 . "_" . $str2;

        if (array_key_exists($translation, $translation_scores)) {
            $score = $translation_scores[$translation];
        } elseif ($str1 == "X") {
            $score = 2;
        } else {
            $score = 0;
        }
    }

    return $score;
}

function get_sub_addr_tokens_pfl($addr_str)
{
    $ret_str = get_sub_addr_pfl($addr_str);
    $ret_str = get_sub_addr_tokens($ret_str);
    return $ret_str;
}

function score_token_matches($usr_str, $db_str, $db_base, $match_base)
{
    // this iterates through the db sub-addr tokens and the usr sub-addr tokens
    // finds where the vals match, then gets what score it should give based
    // on how close the usr key (eg. TH) is to the db key (eg. TOWNHOUSE)

    $match_score = 0;

    if ($db_base == $match_base) {
        $match_score += 2;
    }

    $usr_tokens_arr = array_unique(explode("_", $usr_str));
    $db_tokens_arr = array_unique(explode("_", $db_str));

    foreach ($usr_tokens_arr as $usr_token) {
        $usr_token_arr = explode(":", $usr_token);
        foreach ($db_tokens_arr as $db_token) {
            $db_token_arr = explode(":", $db_token);
            if ((isset($usr_token_arr[1])) && (isset($db_token_arr[1]))) {
                if ($usr_token_arr[1] == $db_token_arr[1]) {
                    $match_score += get_token_scores($usr_token_arr[0], $db_token_arr[0]);
                }
            } else {
                if ($usr_token == $db_token) {
                    $match_score += 1; // give 1 point if there is a matching word
                }
            }
        }
    }

    return $match_score;
}

function get_st_addr_pfl($addr_str)
{
    $addr_arr = explode(", ", $addr_str);
    $ret_str = trim($addr_arr[0]);
    return $ret_str;
}

function get_suburb_pfl($addr_str)
{
    $addr_arr = explode(", ", $addr_str);
    $ret_str = trim($addr_arr[1]);
    return $ret_str;
}

function get_loc_num($loc_str, $index_source)
{
    //get the number part of a loc id with leading zero's stripped
    // prefix  = LOC or MTL	etc
    $base_loc = "";
    $loc_prefix = $index_source;

    if ($index_source == "pfl") {
        $loc_prefix = "LOC";
    }

    if (strstr($loc_str, $loc_prefix) != false) {
        $base_loc = substr($loc_str, 3);
        $base_loc = (int)$base_loc;
    }
    return $base_loc;
}

function qstring_decode($addr_str)
{
    $ret_str = strtoupper($addr_str);
    $ret_str = str_replace("__", "/", $ret_str);
    $ret_str = str_replace([".", ","], "", $ret_str);
    return $ret_str;
}

function find_processed_addr($index_source, $addr_str)
{
    // expects a processed addr_str
    $ret_val = null;
    $best_json = null;
    $i = 1;
    $addr_str = substr($addr_str, 0, 50); // we only create ngrams up to 50 chars long

    $try[1] = "4, 5, 7, 8, 9, 10, 11"; // first try for everything
    $try[2] = "5, 7, 8, 9, 10, 11"; // if we get multiple hits try for just base-address types (may not need this??)
    $try[3] = "5"; // there could be valid duplicates for site name or complex addresses or range addresses

    while ($i <= 3) { // keep trying for the smallest set result that has at least 1 record in it
        $result = es_bulk_qry($index_source, $addr_str, "10", $try[$i]);
        $json = json_decode($result);
        $hits = $json->hits->total;

        if ($hits == 0) {
            break; // dont bother continuing if you got no results
        } elseif ($hits == 1) {
            $best_json = json_decode($result);
            break; // if you got a unique result take it and stop
        } else { // if you did get a result, this is as good or better than the last because its with a reduced set
            $best_json = json_decode($result);
        }
        $i++;
    }

    if ($best_json != null) {
        $ret_val = $best_json->hits->hits[0]->_source; // can't do much more at this point so just take the first record
    }

    return $ret_val;
}

function find_processed_addr_by_id($index_source, $id_str)
{
    // expects an id such as LOC ID or MT ID
    $ret_val = null;
    $type = "";

    if (substr($id_str, 0, 3) == "MTL") {
        $type = "MTL";
    }

    $result = es_bulk_id_qry($index_source, $id_str, "10", $type); // try for an ID related match
    $json = json_decode($result);

    $hits = $json->hits->total;

    if ($hits > 0) { // if we got multiple hits for an actual MTL ID or LOC ID then they are the same so just take one
        $ret_val = $json->hits->hits[0]->_source;
    }

    return $ret_val;
}

function find_sub_addresses($index_source, $match_obj)
{
    // expects a match_obj
    $ret_val = null;
    $base_hash = $match_obj->base_hash;

    // unfortunately we first need to see if this is a complex (eg. shopping centre or university) spanning multiple base hashes
    // first need to get all the complex names and site names for records with the given base hash
    $result = es_bulk_complex_name_qry($index_source, $base_hash, "500");

    $json = json_decode($result);
    $hits = $json->hits->total;

    if ($hits > 0) {
        // if this is a complex, then we need to get all the base hashes it spans for all variations of its name
        foreach ($json->hits->hits as $val) {
            $complex_names_arr[$val->_source->alias_address] = substr($val->_source->alias_address, 0, 50); // only ngrams up to 50 chars
        }
        $complex_names = implode('","', $complex_names_arr);

        $result = es_bulk_complex_hash_qry($index_source, $complex_names, "100");
        $json = json_decode($result);

        foreach ($json->hits->hits as $val) {
            $complex_hashes_arr[$val->_source->base_hash] = $val->_source->base_hash;
        }
        $complex_hashes = implode('","', $complex_hashes_arr);
    } else {
        $complex_hashes = $base_hash;
    }

    $result = es_bulk_base_qry($index_source, $complex_hashes, "5000", "6");
    $json = json_decode($result);

    $hits = $json->hits->total;

    if ($hits > 0) { // first try to get the subaddress tokens
        $ret_val = $json->hits->hits;
    } else { // if no tokens its an sdu so just grab the base address
        $result = es_bulk_base_qry($index_source, $base_hash, "5000", "5");
        $json = json_decode($result);
        $hits = $json->hits->total;
//        dd("line ". __LINE__."=".var_export($result,true) . $index_source."|".$base_hash);
        if ($hits > 0) { // first try to get the subaddress tokens
            $ret_val = $json->hits->hits;
        }
    }

    return $ret_val;
}

function get_google_arr($addr_str)
{
    $addr_str = str_replace("__", "/ ", $addr_str);
    $curl_url = "https://maps.googleapis.com/maps/api/geocode/json?address=" . urlencode($addr_str) . "&region=au&key=AIzaSyDcE2tHUuIsXqNLwIgtoJ16D-N5b1F7XFM";
    $result = hit_curl("GET", $curl_url, "");

    $json = json_decode($result);
    $google_arr = [];

    if (isset($json->results[0]->address_components)) {
        $components = $json->results[0]->address_components;
        foreach ($components as $val) {
            $google_arr[$val->types[0]] = $val->short_name;
        }
    }

    return $google_arr;
}

function get_base_addr_google($google_arr)
{
    $ret_str = "";

    if (count($google_arr) > 0) {

        if (isset($google_arr["street_number"])) {
            $ret_str = $google_arr["street_number"] . " ";
        }

        if ((isset($google_arr["route"])) && (isset($google_arr["locality"]))) {
            $ret_str .= $google_arr["route"] . " " . $google_arr["locality"] . " " . $google_arr["administrative_area_level_1"] . " " . $google_arr["postal_code"];
        }
    }

    return $ret_str;
}

function get_google_check_str($google_arr)
{
    // create a processed string from the google response, so that we can check what the user typed vs that string
    $check_str = "";
    foreach ($google_arr as $key => $val) {
        if (!in_array($key, ["subpremise", "premise", "country", "administrative_area_level_2"])) {
            $check_str .= "_" . $val;
        }
    }

    $ret_str = get_processed_addr($check_str);
    return $ret_str;

}

function score_match_addr($search_str, $match_addr)
{
    ////// was intending to do a 2-way match but can't reliably get the users base address
    ////// so for now its just a score of how much of what the user typed was in the match

    $ret_arr[1] = score_strings($search_str, $match_addr);
    //$ret_arr[2] = score_strings($match_addr,$search_str);

    //$ret_arr[2] = score_strings($match_addr,get_base_addr_bits_usr($search_str,$match_addr));
    //$ret_arr["match_points"] = ($ret_arr[1]["score"]+$ret_arr[2]["score"]) . " of " . ($ret_arr[1]["total"]+$ret_arr[2]["total"]);
    //$ret_arr["match_score"] = round((($ret_arr[1]["score"]+$ret_arr[2]["score"])*100) / ($ret_arr[1]["total"]+$ret_arr[2]["total"]));

    $ret_arr["matchPoints"] = $ret_arr[1]["score"] . " of " . $ret_arr[1]["total"];
    $ret_arr["score"] = round(($ret_arr[1]["score"] * 100) / $ret_arr[1]["total"]);

    return $ret_arr;
}

function score_strings($str1, $str2)
{
    // idea is to go through what the user typed and see if that is what is in the base addr
    // this way if a user types a building name or something, they get points for that
    // but dont lose points if its in the base addr, but they didnt type it

    $score = 0;
    $max_score = 0;

    $str1_nums = preg_replace("/[^0-9]/", "_", $str1); // this is to check for near st num matches eg. 9 vs 9B = pass, 9 vs 90 = fail
    $ret_arr["search_str_nums"] = $str1_nums;

    // now turn what match_addr into an array with each bit and loop through that and compare to the user string
    $str2 = trim($str2, '_');
    $str2_arr = explode("_", $str2);
    foreach ($str2_arr as $key => $val) {
        if (strpos($val, "| MTL") == false) { // exclude the bits at the end of type-4 alias's
            $processed_val = "_" . $val . "_";
            if (strpos($str1, $processed_val) !== false) {
                $ret_arr[$processed_val] = ":) exact match";
                $score += 10;
            } elseif (strpos($str1_nums, $processed_val) !== false) {
                $ret_arr[$key] = ":| near match";
                $score += 5;
            } else {
                $ret_arr[$processed_val] = ":( no match";
            }
            $max_score += 10;
        }
    }

    $ret_arr["score"] = $score;
    $ret_arr["total"] = $max_score;
    return $ret_arr;
}

function match_score_to_msg($score, $addr_type, $match_type)
{
    $ret_str = "";

    if ($score == 100) {
        $str = "exact match";
    } elseif ($score >= 85) {
        $str = "very-near match";
    } elseif ($score >= 60) {
        $str = "near match";
    } else {
        $str = "poor match";
    }

    if ($score == 100) {
        $ret_str = $addr_type . "-address exact match";
    } elseif ($score >= 20) {
        $ret_str = $addr_type . "-address " . $str;
        if ($match_type == "google") {
            $ret_str .= " (via google).";
        }
    } elseif ($score == 1) {
        $ret_str = "multiple " . $addr_type . "-address matches found.";
    } else {
        $ret_str = "no " . $addr_type . "-address match found.";
    }

    return $ret_str;
}

function safe_curl($key, $val)
{
    // this is because elastic spits the dummy if
    // a blank or malformed value gets put into a date field

    if ($val == "") {
        $ret_str = '"' . $key . '": null';
    } else {
        $safe_str = addslashes($val);
        $safe_str = str_replace("\'", "'", $safe_str); // remove escape from apostrophe's
        $safe_str = str_replace("	", " ", $safe_str); // swap tabs for spaces
        $ret_str = '"' . $key . '": "' . $safe_str . '"';
    }
    return $ret_str;
}

function safe_curl_date($key, $val)
{
    // this is because some of the dates are dd/mm/yy and
    // es being the bitch that it is, spits the dummy ovder that too
    if (strpos($val, "/") != false) {

        $date_arr = explode("/", $val);

        $year = $date_arr[2];
        if ($year < 100) {
            $year += 2000;
        }

        $val = $year . "-" . sprintf('%02d', $date_arr[1]) . "-" . sprintf('%02d', $date_arr[0]);
    }

    $ret_str = safe_curl($key, $val);
    return $ret_str;
}

function recursive_match($index_source, $search_str)
{
    $working_search_str_1 = $search_str;
    $try = 1;
    $match_obj = null;

    while (($match_obj == null) && ($working_search_str_1 != "") && ($try < 200)) {

        $match_type = "string";
        $working_search_str_2 = $working_search_str_1;

        while (($match_obj == null) && ($working_search_str_2 != "") && ($try < 200)) {

            $ret_arr["tries"]["match_try_" . $try] = $working_search_str_2;
            $match_obj = find_processed_addr($index_source, $working_search_str_2);

            // do the range addresses as well if they are part of the bit about to be chopped
            $split = explode("_", $working_search_str_2, 3);
            if (strpos($split[1], "-") !== false) {
                $split2 = explode("-", $split[1]);
                if (($match_obj == null) && ($try < 200)) {
                    $try++;
                    $working_search_str_2a = "_" . $split2[0] . "_" . $split[2];
                    $ret_arr["tries"]["match_try_" . $try] = $working_search_str_2a;
                    $match_obj = find_processed_addr($index_source, $working_search_str_2a);
                }
                if (($match_obj == null) && ($try < 200)) {
                    $try++;
                    $working_search_str_2b = "_" . $split2[1] . "_" . $split[2];
                    $ret_arr["tries"]["match_try_" . $try] = $working_search_str_2b;
                    $match_obj = find_processed_addr($index_source, $working_search_str_2b);
                }
            }

            // check for the raw num address if that is the bit about to be chopped
            $raw_num = preg_replace("/[^0-9]/", "", $split[1]);
            if ($raw_num != $split[1]) { // if this portion is a mix of nums and letters
                if (($match_obj == null) && ($try < 200)) {
                    $try++;
                    $working_search_str_2c = "_" . $raw_num . "_" . $split[2];
                    $ret_arr["tries"]["match_try_" . $try] = $working_search_str_2c;
                    $match_obj = find_processed_addr($index_source, $working_search_str_2c);
                }
            }

            // now get ready for the next inner iteration or exit
            if (substr_count($working_search_str_2, '_') > 5) {
                $working_search_str_2 = "_" . $split[2];
                $try++;
            } else {
                break;
            }
        }

        // now get ready for the next outer iteration or exit
        if (substr_count($working_search_str_1, '_') > 5) {
            $split = explode("_", strrev($working_search_str_1), 3);
            $working_search_str_1 = strrev($split[2]) . "_";
            $try++;
        } else {
            break;
        }
    }

    $ret_arr["match"] = $match_obj;
    return $ret_arr;
}

function is_loc_id($search_str)
{
    $ret_val = false;

    if (in_array(substr($search_str, 0, 3), ["MTL", "LOC"])) { // if the string starts with a known prefix
        if (stripos($search_str, " ") == false) { // and its a single word - no spaces
            $ret_val = true;
        }
    }

    return $ret_val;
}

function get_proc_serv_type($serv_type)
{
    // stripos because the match is not case sensitive
    if (stripos($serv_type, "fibre") !== false) {
        $ret_str = "FTTP";
    } elseif (stripos($serv_type, "fttc") !== false) {
        $ret_str = "FTTC";
    } elseif (stripos($serv_type, "fttb") !== false) {
        $ret_str = "FTTB";
    } elseif (stripos($serv_type, "fttn") !== false) {
        $ret_str = "FTTN";
    } elseif (stripos($serv_type, "hfc") !== false) {
        $ret_str = "HFC";
    } elseif (stripos($serv_type, "wire") !== false) {
        $ret_str = "Wireless";
    } else {
        $ret_str = "tbc";
    }

    return $ret_str;
}

function get_source_locid_field($index_source) {

    $ret_val = null;

    if ($index_source == "pfl") {
        $ret_val = "nbn_locid";
    }

    return $ret_val;
}

function get_processed_complex_addr($str)
{
    $whitelist = false;
    $ret_str = get_processed_addr($str);
    $ret_arr = [];

    $whitelist_arr = [
        "_SHOPPING_",
        "_CENTRE_",
        "_COMPLEX_",
        "_MALL_",
        "_SCHOOL_",
        "_UNIVERSITY_",
        "_VILLAGE_",
        "_GARDENS_",
        "_TAFE_",
        "_COLLEGE_",
        "_APPARTMENTS_",
        "_PARK_",
        "_CASINO_",
        "_TOWERS_",
        "_TOWER_",
        "_ARCADE_",
        "_WESTFIELD_",
        "_WESTFIELDS_",
        "_HOSPITAL_",
        "_PLAZA_",
        "_COURT_"
    ];

    $strip_arr = [
        "-",
        "_BG",
        "_BLDG",
        "_BUILDING",
        "_BLOCK",
        "_ATM",
        "_ADMIN_",
        "_DISABLED_",
        "_WORKSHOP",
        "_FIRE_",
        "_PANEL_",
        "_FIP",
        "_CAR_PARK",
        "_VENDING",
        "_KIOSK",
        "_KSK",
        "_LIFT",
        "_PHONE",
        "_FLOOR",
        "_LEVEL",
        "_LVL",
        "_MACHINE",
        "_TENNIS_COURTS_",
        "_PASSENGER_",
        "_DWELLING",
        "_LOT",
        "_LBBY",
        "_LOBBY",
        "_POOL_",
        "_ROOM_",
        "_MDF",
        "_OFFICE_",
        "_SECURITY_",
        "_AND_",
        "_SITE",
        "_SHOP_"
    ];

    // only allow whitelisted complex names
    foreach ($whitelist_arr as $val) {
        if (stripos($ret_str, $val) !== false) {
            $whitelist = true;
        }
    }

    if ($whitelist == true) {
        $ret_str = str_replace($strip_arr, "_", $ret_str); // remove specific strings
        $ret_str = preg_replace('/([_])\1+/', '$1', $ret_str); // remove any repeat underscores
        if (strlen($ret_str) > 6) {
            $ret_str = "_" . trim($ret_str, '_') . "_";
            $arr = explode("_", $ret_str);
            foreach ($arr as $val) {
                if (strlen($val) > 3) {
                    $ret_arr[] = $val; // remove small words
                }
            }

            if (count($ret_arr) > 1) {
                $ret_str = "_" . implode("_", $ret_arr) . "_";
            } else {
                $ret_str = null;
            }
            else {
                $ret_str = null;
            }
        }
    }

    if ((strlen($ret_str) > 8) && ($whitelist == true)) {
        return $ret_str;
    } else {
        return null;
    }
}

function get_source_match($provider, $return_type, $index_source, $search_str) {

    // this takes a source name and search string and returns the best addr match it can
    $match_obj = null; // the object that comes back from es when a match in es is found
    $google_arr = [];
    $return_arr = array(); //the end object that is returned from the api
    $return_arr["traceData"]["rawUserStr"] = qstring_decode($search_str);

    if (is_loc_id($search_str)) {
        // first see if this matches an ID of some sort
        $match_type = "id";
        $match_obj = find_processed_addr_by_id($index_source, $search_str);
    }
    else {
        // process the input so that we are comparing apples with apples as much as possible
        $processed_search_str = get_processed_addr($search_str);
        $return_arr["traceData"]["processedUserStr"] = $processed_search_str;

        // try matching what the user typed, and iteratively take bits off the front
        // and then off the back to see if that matches anything
        if ($match_obj == null) {
            $match_type = "string";
            $recursive_match_arr = recursive_match($index_source, $processed_search_str);
            $match_obj = $recursive_match_arr["match"];
            $return_arr["traceData"]["directTries"] = $recursive_match_arr["tries"];
        }

        // if no match based on what the user typed then get the base addr via google and try to match that
        if ($match_obj == null) {
            $match_type = "google";
            $google_arr = get_google_arr($search_str);
            $return_arr["traceData"]["googleResponse"] = $google_arr;

            $google_check_str = get_google_check_str($google_arr);
            $return_arr["traceData"]["googleCheckStr"] = $google_check_str;
            $return_arr["traceData"]["googleMatch"] = score_match_addr(get_processed_addr($search_str), $google_check_str);
            $google_base_addr = get_processed_addr(get_base_addr_google($google_arr));

            $recursive_match_arr = recursive_match($index_source, $google_base_addr);
            $match_obj = $recursive_match_arr["match"];
            $return_arr["traceData"]["googleTries"] = $recursive_match_arr["tries"];
        }
    }

    if ($match_obj != null) { // yay - something matches what is in our database

        if ($match_type == "id") {
            $base_match_score = 100;
        } elseif ($match_type == "google") {
            $base_match_score = $return_arr["traceData"]["googleMatch"]["score"];
        } else {
            $return_arr["traceData"]["baseMatch"] = score_match_addr(get_processed_addr($search_str), $match_obj->alias_address);
            $base_match_score = $return_arr["traceData"]["baseMatch"]["score"];
        }

        $return_arr["traceData"]["matchType"] = $match_type;
        $return_arr["traceData"]["matchedAliasAddr"] = $match_obj->alias_address;
        $return_arr["traceData"]["matchedOfficialAddr"] = $match_obj->official_address;
        $return_arr["traceData"]["matchedAliasType"] = $match_obj->alias_type;
        $return_arr["results"]["provider"] = $provider;
        $return_arr["results"]["sourceName"] = $index_source;

        // first start returning the base-address data
        $matched_base_addr = get_base_addr_pfl($match_obj->official_address);
        $return_arr["results"]["matchedBaseAddr"]["longName"] = $matched_base_addr;
        $return_arr["results"]["matchedBaseAddr"]["geoLocation"] = $match_obj->geo_location;
        $return_arr["results"]["matchedBaseAddr"]["score"] = $base_match_score;

        $return_arr["results"]["matchedSubAddr"]["sourceId"] = $match_obj->source_locid; // overwrite this if there is a sub-address match
        $return_arr["results"]["matchedSubAddr"]["mtId"] = $match_obj->mt_locid; // overwrite this if there is a sub-address match

        $token_match_obj = find_sub_addresses($index_source, $match_obj); // we now need to get all sub-addresses no matter what for the return data

//        if ($token_match_obj == null) {
//            dd("index_source=".$index_source." & match_obj=".var_export($match_obj,true));
//        }

        if ($match_type != "id") {
            // now need to find user tokens based on the users base address

            if (in_array($match_obj->alias_type, [8, 9, 10, 11])) { // if the match was based on a base alias - work out the sub-address from that
                $processed_matched_base_addr = get_processed_addr($match_obj->alias_address);
            } else { // else work out the sub-address from the official base address
                $processed_matched_base_addr = get_processed_addr($matched_base_addr);
            }
            $usr_sub_addr_str = get_sub_addr_usr($processed_search_str, $processed_matched_base_addr);
            $return_arr["traceData"]["processedUsrBaseAddr"] = $processed_matched_base_addr;
        }

        // by default return the first sub-address record with a score of 0
        if ($token_match_obj) {
            $return_arr["results"]["matchedSubAddr"]["longName"] = $token_match_obj[0]->_source->official_address;
            $return_arr["results"]["matchedSubAddr"]["shortName"] = get_sub_addr_pfl($token_match_obj[0]->_source->official_address);
            $return_arr["results"]["matchedSubAddr"]["geoLocation"] = $token_match_obj[0]->_source->geo_location;
            $return_arr["results"]["matchedSubAddr"]["score"] = 0;
            $return_arr["results"]["matchedSubAddr"]["sourceId"] = $token_match_obj[0]->_source->source_locid;
            $return_arr["results"]["matchedSubAddr"]["mtId"] = $token_match_obj[0]->_source->mt_locid;
            $return_arr["results"]["matchedSubAddr"]["servClass"] = $token_match_obj[0]->_source->serv_class;
            $return_arr["results"]["matchedSubAddr"]["tech"] = $token_match_obj[0]->_source->tech;
            $return_arr["results"]["matchedSubAddr"]["params"] = $token_match_obj[0]->_source->params;

            if ($match_type == "id") {
                foreach ($token_match_obj as $key => $val) {
                    $token_vals[$key] = $val->_source->alias_address;
                }
                $sub_addr_i = 0;
            } else {
                // these are the usr tokens
                $usr_tokens = get_sub_addr_tokens($usr_sub_addr_str);
                $usr_tokens_count = substr_count($usr_tokens, '_') + 1;
                $return_arr["traceData"]["userSubAddrTokens"] = $usr_tokens;

                // these are the tokens for each of the match sub-addresses
                foreach ($token_match_obj as $key => $val) {
                    $token_vals[$key] = $val->_source->alias_address;
                    $token_scores[$key] = score_token_matches($usr_tokens, $val->_source->alias_address, get_base_addr_pfl($val->_source->official_address), get_base_addr_pfl($match_obj->official_address));
                    $return_arr["traceData"]["tokenScores"][$val->_source->alias_address] = $token_scores[$key];
                }

                arsort($return_arr["traceData"]["tokenScores"]);
                arsort($token_scores);

                // now print out the all sub-addr data with the highest scores at the top
                $sub_addr_i = 0;
                foreach ($token_scores as $key => $val) { // first print out the highest score-sub addresses
                    if ($val > 2) {
                        $return_arr["results"]["allSubAddr"][$sub_addr_i] = get_sub_addr_pfl($token_match_obj[$key]->_source->official_address) . " : " . $token_match_obj[$key]->_source->mt_locid;
                        $sub_addr_i++;
                    }
                }
            }

            asort($token_vals); // sort so we can show the higest scores first

            if ($sub_addr_i != 0) { // add a break between the top scores and all the rest of the subaddresses
                $return_arr["results"]["allSubAddr"][$sub_addr_i] = "-";
                $sub_addr_i++;
            }

            foreach ($token_vals as $key => $val) { // now print out all sub-addresses in sorted order
                $return_arr["results"]["allSubAddr"][$sub_addr_i] = get_sub_addr_pfl($token_match_obj[$key]->_source->official_address) . " : " . get_base_addr_pfl($token_match_obj[$key]->_source->official_address) . " : " . $token_match_obj[$key]->_source->mt_locid;
                $sub_addr_i++;
            }
        }

        if ($match_type == "id") { // then this is a direct match - use the initial object not the token obj
            $return_arr["results"]["matchedSubAddr"]["score"] = 100;
            $return_arr["results"]["matchedSubAddr"]["longName"] = $match_obj->official_address;
            $return_arr["results"]["matchedSubAddr"]["shortName"] = get_sub_addr_pfl($match_obj->official_address);
            $return_arr["results"]["matchedSubAddr"]["geoLocation"] = $match_obj->geo_location;
            $return_arr["results"]["matchedSubAddr"]["sourceId"] = $match_obj->source_locid;
            $return_arr["results"]["matchedSubAddr"]["mtId"] = $match_obj->mt_locid;
            $return_arr["results"]["matchedSubAddr"]["servClass"] = $match_obj->serv_class;
            $return_arr["results"]["matchedSubAddr"]["tech"] = $match_obj->tech;
            $return_arr["results"]["matchedSubAddr"]["params"] = $match_obj->params;
        } elseif (count($token_match_obj) == 1) { // this is an SDU
            $return_arr["results"]["matchedSubAddr"]["score"] = 100;
        } elseif (($usr_sub_addr_str == null) && (count($token_match_obj) > 1)) { // if its an mdu, but the user didnt type any sub-address details
            $return_arr["results"]["matchedSubAddr"]["longName"] = "-";
            $return_arr["results"]["matchedSubAddr"]["shortName"] = "-";
        } else {
            $top_scores = array_keys($token_scores, max($token_scores));
            if ((count($top_scores) > 0) && ($top_scores[0] > 0)) { // there were winners

                // now we have a better sub-address so return that instead
                $return_arr["results"]["matchedSubAddr"]["longName"] = $token_match_obj[$top_scores[0]]->_source->official_address;
                $return_arr["results"]["matchedSubAddr"]["shortName"] = get_sub_addr_pfl($token_match_obj[$top_scores[0]]->_source->official_address);
                $return_arr["results"]["matchedSubAddr"]["geoLocation"] = $token_match_obj[$top_scores[0]]->_source->geo_location;
                $return_arr["results"]["matchedSubAddr"]["sourceId"] = $token_match_obj[$top_scores[0]]->_source->source_locid;
                $return_arr["results"]["matchedSubAddr"]["mtId"] = $token_match_obj[$top_scores[0]]->_source->mt_locid;
                $return_arr["results"]["matchedSubAddr"]["servClass"] = $token_match_obj[$top_scores[0]]->_source->serv_class;
                $return_arr["results"]["matchedSubAddr"]["tech"] = $token_match_obj[$top_scores[0]]->_source->tech;
                $return_arr["results"]["matchedSubAddr"]["params"] = $token_match_obj[$top_scores[0]]->_source->params;

                $sub_addr_score = round(($token_scores[$top_scores[0]] * 100) / (($usr_tokens_count * 5) + 2));

                if (count($top_scores) == 1) { // single winner so return the score
                    $return_arr["results"]["matchedSubAddr"]["score"] = $sub_addr_score;
                } else { // multiple winners so return a score of 1
                    $return_arr["results"]["matchedSubAddr"]["score"] = 1;
                }
            } else { // there was no clear sub-address winner so return a match score of 0
                $return_arr["results"]["matchedSubAddr"]["longName"] = "-";
                $return_arr["results"]["matchedSubAddr"]["shortName"] = "-";
                $return_arr["results"]["matchedSubAddr"]["geoLocation"] = "-";
            }
        }
    } else {
        $return_arr["results"]["matchedBaseAddr"]["longName"] = "-";
        $return_arr["results"]["matchedBaseAddr"]["score"] = 0;
        $return_arr["results"]["matchedSubAddr"]["longName"] = "-";
        $return_arr["results"]["matchedSubAddr"]["shortName"] = "-";
        $return_arr["results"]["matchedSubAddr"]["score"] = 0;
    }

    $return_arr["results"]["matchedBaseAddr"]["msg"] = match_score_to_msg($return_arr["results"]["matchedBaseAddr"]["score"], "base", $match_type);
    $return_arr["results"]["matchedSubAddr"]["msg"] = match_score_to_msg($return_arr["results"]["matchedSubAddr"]["score"], "sub", "");
    if (isset($top_scores) && (get_base_addr_pfl($token_match_obj[$top_scores[0]]->_source->official_address) != get_base_addr_pfl($match_obj->official_address))) {
        $return_arr["results"]["matchedSubAddr"]["msg"] .= " (different base address, same complex)";
    }

    if ((isset($return_arr["results"]["matchedSubAddr"]["sourceId"])) && ($return_type == "all")) {

        $details = DB::table('pfl')
            ->where('nbn_locid', '=', $return_arr["results"]["matchedSubAddr"]["sourceId"])
            ->limit(1)
            ->get();
        $return_arr["sourceDetails"] = $details[0];
    }

    ksort($return_arr);
    header("Access-Control-Allow-Origin: *");
    header('Content-type: application/json');
    echo json_encode($return_arr, JSON_PRETTY_PRINT);

}

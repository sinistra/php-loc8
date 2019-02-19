<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script type="text/javascript" src="{{ URL::asset('js/jquery.easy-autocomplete.js') }}"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.1.2/js/bootstrap.min.js"></script>
    <script src="https://momentjs.com/downloads/moment.min.js"></script>

    <link rel="stylesheet" href="{{ URL::asset('css/easy-autocomplete.css') }}"/>
    <link rel="stylesheet" href="{{ URL::asset('css/easy-autocomplete.themes.css') }}"/>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.2/css/bootstrap.min.css" integrity="sha384-Smlep5jCw/wG7hdkwQ/Z5nLIefveQRIY9nfy6xoR1uRYBtpZgI6339F5dgvm/e9B" crossorigin="anonymous">

    <style type="text/css">
        .easy-autocomplete input { border-radius: 14px; padding-left: 5px; }
    </style>
</head>
<body>

<table border="0" width="100%" style="margin-top: 50px; font-family: arial; font-size: 10pt;">
    <tr>
        <td width="30%" align="right">qrid:</td>
        <td id="qrid_elem"></td>
    </tr>
    <tr height="30px">
        <td colspan="2"><hr></td>
    </tr>
    <tr>
        <td width="30%" align="right">carrier:</td>
        <td id="elem_carrier"></td>
    </tr>
    <tr>
        <td width="30%" align="right">speed:</td>
        <td id="elem_speed"></td>
    </tr>
    <tr height="30px">
        <td colspan="2"><hr></td>
    </tr>
    <tr>
        <td align="right">search-addr:</td>
        <td>
            <input id="input_suggest" type="text" style="width: 90%;">
        </td>
    </tr>
    <tr>
        <td align="right">found base-addr:</td>
        <td id="elem_base_addr_str"></td>
    </tr>
    <tr>
        <td align="right" style="padding-bottom: 10px;">base score:</td>
        <td id="elem_base_addr_score" style="padding-bottom: 10px;"></td>
    </tr>
    <tr>
        <td align="right">found sub-addr:</td>
        <td id="elem_sub_addr_str"></td>
    </tr>
    <tr>
        <td align="right">sub score:</td>
        <td id="elem_sub_addr_score"></td>
    </tr>
    <tr height="30px">
        <td colspan="2"><hr></td>
    </tr>
    <tr>
        <td align="right">product:</td>
            <td id="input_prod_str">
    </tr>
    <tr height="30px">
        <td colspan="2"><hr></td>
    </tr>
    <tr>
        <td align="right">param 1:</td>
        <td>param 1</td>
    </tr>
    <tr>
        <td align="right">param 2</td>
        <td>param 2</td>
    </tr>
</table>

<script>

    $(document).ready(function () {

        $('#qrid_elem').html(qrid_val);
        refreshQridData();

        function refreshQridData() {
            var base_ajax_url = api_env + '/quoteitem/' + qrid_val;
            //var base_ajax_url = '/loc8/mock';
            $.get(base_ajax_url, function (data, status) {
                var new_qrid_data = data;
                //var new_qrid_data = jQuery.parseJSON(data);

                if (JSON.stringify(new_qrid_data) != JSON.stringify(qrid_data)) {
                    console.log('refreshing ui');
                    updateQridDetails(new_qrid_data, qrid_data);
                }

                qrid_data = new_qrid_data;

                setTimeout(function () {
                    refreshQridData();
                }, 2000);

            });
        }

        var autocomplete_options = {
            url: function (phrase) {
                var search_type = 'def';
                uri_str = api_env + '/Addresses/suggest?carrier=nbn&address=' + safeUrl(phrase);
                console.log(uri_str);
                return uri_str;
            },
            getValue: 'addr',
            list: {
                maxNumberOfElements: 10,
                onClickEvent: function () {
                    console.log('click_press');
                    handleAddrChange('pick_sugg', qrid_val);
                }
            }
        };

        $('#input_suggest').easyAutocomplete(autocomplete_options);

        // now bind change events to each input element

        $(document).on('change', '#input_carrier', function() {
            handleItemChange('#'+this.id, 'provider', qrid_val); 
        });

        $(document).on('change', '#input_speed', function() {
            handleItemChange('#'+this.id, 'preferredSpeed', qrid_val); 
        });

        $(document).on('change', '#input_sub_addr_str', function() {
            handleItemChange('#'+this.id, 'siteAddress', qrid_val);
        });

        $(document).on('keypress', function(e) {
            if ((e.which == 13) && (e.target.id == 'input_suggest')) { // key 13 is enter
                handleAddrChange('hit_enter', qrid_val);
            }
        });

    });

    var api_env = 'http://loc8-api-sit.macquarieview.com';
    <?php echo "        var qrid_val = '" . $_GET['qrid'] . "';"; ?>
    var qrid_data = '';

    function fetchFromObject(obj, prop) {

        if (typeof obj === 'undefined') {
            return false;
        }

        var _index = prop.indexOf('.')
        if (_index > -1) {
            return fetchFromObject(obj[prop.substring(0, _index)], prop.substr(_index + 1));
        }

        if (typeof obj[prop] === 'undefined') {
            return '';
        }

        return obj[prop];
    }

    function fetchStrFromObject(obj, prop) {

        var ret_val = fetchFromObject(obj, prop);

        if (ret_val === false) {
            ret_val = '';
        }

        return ret_val;
    }

    function fetchDiff(new_data, data, prop) {

        var ret_val = false;
        var val = fetchFromObject(data, prop);
        var new_val = fetchFromObject(new_data, prop);

        if (((typeof new_val === 'object') && (JSON.stringify(val) !== JSON.stringify(new_val))) ||
           ((typeof new_val !== 'object') && (val !== new_val))) {
            if (new_val === false) {
                ret_val = '';
            }
            else {
                ret_val = new_val;
            }

            console.log('diff on ' + prop);
            console.log('types: ' + typeof val + ' - ' + typeof new_val);
            console.log('new val:'); 
            console.log(ret_val);
        }

        return ret_val;
    }

    function pipeFormatStr(var1, var2) {

            var ret_str = '';
            if (var1 !== false) { ret_str += var1; }
            if ((var1 !== false) && (var2 != false)) { ret_str += ' | '; }
            if (var2 !== false) { ret_str += var2; }

            return ret_str;
    }

    function updateQridDetails(new_data, data) {

        var data_prop = 'id';
        var new_val = fetchFromObject(new_data, data_prop);
        if (new_val !== false) { // dont do anything if there is no valid response

            // update carrier
            var data_prop = 'provider';
            var new_val = fetchDiff(new_data, data, data_prop);
            if (new_val !== false) {
                var picklist_obj = [{"val": "nbn"}, {"val": "other"}];
                var picklist_html = generatePicklist('input_carrier', new_val, new_val, picklist_obj, 'val', 'val');
                $('#elem_carrier').html(picklist_html);
            }

            // update preferred speed
            var data_prop = 'preferredSpeed';
            var new_val = fetchDiff(new_data, data, data_prop);
            if (new_val !== false) {
                var picklist_obj = [{"val": "12"}, {"val": "25"}, {"val": "50"}, {"val": "100"}, {"val": "1000"}];
                var picklist_html = generatePicklist('input_speed', new_val, new_val, picklist_obj, 'val', 'val');
                $('#elem_speed').html(picklist_html);
            }

            // update search address
            if (new_data.siteAddress != data.siteAddress) {
                $('#input_suggest').val(new_data.siteAddress);               
            }

            // update matched base-address
            var data_prop = 'matchedBaseAddr.longName';
            var new_val = fetchDiff(new_data, data, data_prop);
            if (new_val !== false) {
                $('#elem_base_addr_str').html(new_val);
            }

            // update base-address score
            var data_prop = 'matchedBaseAddr.score';
            var new_val = fetchDiff(new_data, data, data_prop);
            if (new_val !== false) {
                $('#elem_base_addr_score').html(new_val);
            }

            // update matched sub-address
            var data_prop_1 = 'matchedSubAddr';
            var new_val_1 = fetchDiff(new_data, data, data_prop_1);
            var data_prop_2 = 'subAddressPicklist';
            var new_val_2 = fetchDiff(new_data, data, data_prop_2);

            if ((new_val_1 !== false) || (new_val_2 !== false)) {

                var sub_addr_mtl = fetchFromObject(new_data, 'matchedSubAddr.mtId');
                var sub_addr_long = fetchFromObject(new_data, 'matchedSubAddr.longName');
                var sub_addr_short = fetchFromObject(new_data, 'matchedSubAddr.shortName');
                var sub_addr_val = pipeFormatStr(sub_addr_long, sub_addr_mtl);
                var sub_addr_name = pipeFormatStr(sub_addr_short, sub_addr_mtl);
                //console.log('sav = ' + )

                var picklist_sub_addr = generatePicklist2('input_sub_addr_str', sub_addr_val, sub_addr_name, new_val_2, 'baseAddress', 'subAddresses', 'value', 'name');
                $('#elem_sub_addr_str').html(picklist_sub_addr);
            }

            // update sub-address score
            var data_prop = 'matchedSubAddr.score';
            var new_val = fetchDiff(new_data, data, data_prop);
            if (new_val !== false) {
                $('#elem_sub_addr_score').html(new_val);
            }

            // update product
            var data_prop_1 = 'selectedProduct';
            var new_val_1 = fetchDiff(new_data, data, data_prop_1);

            var data_prop_2 = 'products';
            var new_val_2 = fetchDiff(new_data, data, data_prop_2);

            if ((new_val_1 !== false) || (new_val_2 !== false)) {
                var picklist_sub_addr = formatProdPicklist(new_val_1, new_val_2);
                $('#input_prod_str').html(picklist_sub_addr);
            }
        }
    }

    function sendChange(updateData, elem_qrid) {

            console.log('put = ' + updateData);

            $.ajax({
                url: api_env + '/quoteitem/' + elem_qrid,
                method: 'PUT',
                data: updateData,
                contentType: 'application/json',
                success: function() {
                    console.log('put successful');
                }
            });
    };

    function safeUrl(phrase) {
        // this is required so that the '/' in eg 4/21 smith st does not cause an issue with the laravel router regex
        var safe_str = phrase.replace(/\//g, '__');
        return encodeURIComponent(safe_str);
    }

    function formatSubAddrPicklist(data_sub_addr, data_sub_addr_picklist) {

        var ret_val = '<select id=\"input_sub_addr_str\">\n';
        var i = 0;

        $.each(data_sub_addr_picklist, function (key_1, val_1) {

            ret_val += '<optgroup label=\"' + val_1.baseAddress + '\">\n';

            $.each(val_1.subAddresses, function (key_2, val_2) {

                ret_val += '<option value=\"' + val_2.value + '\"'; 
                if (i == 0) { ret_val += ' selected'; }
                ret_val += '>' + val_2.name + '</option>\n';
                i ++;
            });

            ret_val += '</optgroup>\n';

        });

        ret_val += '</select>\n';

        return ret_val;
    }

    function generatePicklist2(elem_id, selected_val, selected_name, picklist_obj, group_prop, sub_group_prop, val_prop, name_prop) {

        var ret_val = '<select id=\"' + elem_id + '\">\n';
        ret_val += '  <optgroup label=\"SELECTED\">\n';
        ret_val += '    <option value=\"' + selected_val + '\" selected>' + selected_name + '</option>\n';
        ret_val += '  </optgroup>\n';

        $.each(picklist_obj, function (key_1, val_1) {

            ret_val += '  <optgroup label=\"' + fetchFromObject(val_1, group_prop) + '\">\n';

            $.each(fetchFromObject(val_1, sub_group_prop), function (key_2, val_2) {
                ret_val += '    <option value=\"' + fetchFromObject(val_2, val_prop) + '\">' + fetchFromObject(val_2, name_prop) + '</option>\n';
            });

            ret_val += '  </optgroup>\n';

        });

        ret_val += '</select>\n';

        return ret_val;
    }

    function generatePicklist(elem_id, selected_val, selected_name, picklist_obj, val_prop, name_prop) {

        var ret_val = '<select id=\"' + elem_id + '\">\n';
        ret_val += '  <option value=\"' + selected_val + '\" selected>' + selected_name + '</option>\n';

        $.each(picklist_obj, function (key, val) {
            ret_val += '  <option value=\"' + fetchFromObject(val, val_prop) + '\">' + fetchFromObject(val, name_prop) + '</option>\n';
        });

        ret_val += '</select>\n';

        return ret_val;
    }

    function formatProdPicklist(data_prod, data_prod_picklist) {

        var ret_val = '<select>\n';
        var i = 0;

        $.each(data_prod_picklist, function (key, val) {

            ret_val += '<option value=\"' + val.id + '\"';
            if (data_prod == val.id) { ret_val += ' selected'; }
            ret_val += '>' + val.productDescription + '</option>\n';
            i++;
        });

        ret_val += '</select>\n';

        return ret_val;
    }

    function handleItemChange(elem_id, elem_param, elem_qrid) {

            var newVal = $(elem_id).val();
            var updateData = '{"' + elem_param + '":"' + newVal + '"}';
            sendChange(updateData, elem_qrid);
    };

    function handleAddrChange(searchType, elem_qrid) {

        if (searchType == 'pick_sugg') {
            var addr_str = $('#input_suggest').getSelectedItemData().addr;
        }
        else if (searchType == 'hit_enter') {
            if (($('#input_suggest').getItemData(0) != -1) && ($('#input_suggest').getItemData(1) == -1)) { // unique item in the list
                var addr_str = $('#input_suggest').getItemData(0).addr;
                $('#input_suggest').val(addr_str).trigger('change');
                $('#input_suggest').blur();
            }
            else {
                var addr_str = $('#input_suggest').val();
                $('#input_suggest').blur();
            }
        }

        var updateData = '{"siteAddress":"' + addr_str + '"}';
        sendChange(updateData, elem_qrid);

    }

</script>
</body>
</html>

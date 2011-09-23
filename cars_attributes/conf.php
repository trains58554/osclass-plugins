<?php
/*
 *      OSCLass – software for creating and publishing online classified
 *                           advertising platforms
 *
 *                        Copyright (C) 2010 OSCLASS
 *
 *       This program is free software: you can redistribute it and/or
 *     modify it under the terms of the GNU Affero General Public License
 *     as published by the Free Software Foundation, either version 3 of
 *            the License, or (at your option) any later version.
 *
 *     This program is distributed in the hope that it will be useful, but
 *         WITHOUT ANY WARRANTY; without even the implied warranty of
 *        MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *             GNU Affero General Public License for more details.
 *
 *      You should have received a copy of the GNU Affero General Public
 * License along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
?>
<?php 

    $conn = getConnection() ;
    
    if(Params::getParam("plugin_action") != '') {
        switch(Params::getParam("plugin_action")) {
            case("make_delete"):    if(Params::getParam("id")!="") {
                                        $conn->osc_dbExec('DELETE FROM %st_item_car_model_attr WHERE fk_i_make_id = %d', DB_TABLE_PREFIX, Params::getParam("id"));
                                        $conn->osc_dbExec('DELETE FROM %st_item_car_make_attr WHERE pk_i_id = %d', DB_TABLE_PREFIX, Params::getParam("id"));
                                    }
            break;
            case("make_add"):       if(Params::getParam("make")!="") {
                                        $conn->osc_dbExec("INSERT INTO `%st_item_car_make_attr` ( `s_name`) VALUES ( '%s')", DB_TABLE_PREFIX, Params::getParam("make"));
                                    }
            break;
            case("make_edit"):      $make = Params::getParam("make");
                                    if(is_array($make)) {
                                        foreach($make as $k => $v) {
                                            $conn->osc_dbExec("UPDATE  `%st_item_car_make_attr` SET  `s_name` =  '%s' WHERE  `pk_i_id` = %d ;", DB_TABLE_PREFIX, $v, $k);
                                        }
                                    }
            break;
            case("model_delete"):   if(Params::getParam("id") != "") {
                                        $conn->osc_dbExec('DELETE FROM %st_item_car_model_attr WHERE pk_i_id = %d', DB_TABLE_PREFIX, Params::getParam("id"));
                                    }
            break;
            case("model_add"):      if(Params::getParam("makeId")!='' && Params::getParam("model")!='') {
                                        $conn->osc_dbExec("INSERT INTO `%st_item_car_model_attr` ( `fk_i_make_id`, `s_name`) VALUES ( %d, '%s')", DB_TABLE_PREFIX, Params::getParam("makeId"), Params::getParam("model"));
                                    }
            break;
            case("model_edit"):     $makeId = Params::getParam("makeId");
                                    $model  = Params::getParam("model");
                                    if($makeId != '' && is_array($model)) {
                                        foreach($model as $k => $v) {
                                            $conn->osc_dbExec("UPDATE  `%st_item_car_model_attr` SET  `s_name` =  '%s' WHERE  `pk_i_id` = %d AND `fk_i_make_id` = %d;", DB_TABLE_PREFIX, $v, $k, $makeId);
                                        }
                                    }
            break;
            case("type_delete"):    if(Params::getParam("id")!="") {
                                        $conn->osc_dbExec('DELETE FROM %st_item_car_vehicle_type_attr WHERE pk_i_id = %d', DB_TABLE_PREFIX, Params::getParam("id"));
                                    }
            break;
            case("type_add"):       $dataItem      = array();
                                    $requestParams = Params::getParamsAsArray();
                                    foreach ($requestParams as $k => $v) {
                                        if (preg_match('|(.+?)#(.+)|', $k, $m)) {
                                            $dataItem[$m[1]][$m[2]] = $v;
                                        }
                                    }
                                    // insert locales
                                    $lastId = $conn->osc_dbFetchResult('SELECT pk_i_id FROM %st_item_car_vehicle_type_attr ORDER BY pk_i_id DESC LIMIT 1', DB_TABLE_PREFIX) ;
                                    $lastId = $lastId['pk_i_id'] + 1 ;
                                    foreach ($dataItem as $k => $_data) {
                                        $conn->osc_dbExec("REPLACE INTO %st_item_car_vehicle_type_attr (pk_i_id, fk_c_locale_code, s_name) VALUES (%d, '%s', '%s')", DB_TABLE_PREFIX, $lastId, $k, $_data['car_type']);
                                    }
            break;
            case("type_edit"):      $car_type = Params::getParam("car_type");
                                    foreach($car_type as $k => $v) {
                                        foreach($v as $kj => $vj) {
                                            $conn->osc_dbExec("REPLACE INTO %st_item_car_vehicle_type_attr (pk_i_id, fk_c_locale_code, s_name) VALUES (%d, '%s', '%s')", DB_TABLE_PREFIX, $k, $kj, $vj );
                                        }
                                    }
        }
    }
    
    switch(Params::getParam("section")) {
        case("makes"): ?>
    
                            <div id="settings_form" style="border: 1px solid #ccc; background: #eee; ">
                                <div style="padding: 20px;">
                                    <div style="float: left; width: 50%;">
                                        <fieldset>
                                            <legend><?php _e('Makes', 'cars_attributes'); ?></legend>
                                            <form name="cars_form" id="cars_form" action="<?php echo osc_admin_base_url(true);?>" method="GET" enctype="multipart/form-data" >
                                                <input type="hidden" name="page" value="plugins" />
                                                <input type="hidden" name="action" value="renderplugin" />
                                                <input type="hidden" name="file" value="cars_attributes/conf.php" />
                                                <input type="hidden" name="section" value="makes" />
                                                <input type="hidden" name="plugin_action" value="make_edit" />
                                                <ul>
                                                <?php
                                                    $makes = $conn->osc_dbFetchResults('SELECT * FROM %st_item_car_make_attr ORDER BY s_name ASC', DB_TABLE_PREFIX);
                                                    foreach($makes as $make) {
                                                        echo '<li><input name="make[' . $make['pk_i_id'] . ']" id="' . $make['pk_i_id'] . '" type="text" value="' . $make['s_name'] . '" /> <a href="' . osc_admin_base_url(true) . '?page=plugins&action=renderplugin&file=cars_attributes/conf.php?section=makes&plugin_action=make_delete&id=' . $make['pk_i_id'] . '" ><button>' . __('Delete', 'cars_attributes') . '</button></a> </li>';
                                                    }
                                                ?>
                                                </ul>
                                                <button type="submit"><?php _e('Edit', 'cars_attributes'); ?></button>
                                            </form>
                                        </fieldset>
                                    </div>
                            
                                    <div style="float: left; width: 50%;">
                                        <fieldset>
                                            <legend><?php _e('Add new make', 'cars_attributes'); ?></legend>
                                            <form name="cars_form" id="cars_form" action="<?php echo osc_admin_base_url(true); ?>" method="GET" enctype="multipart/form-data" >
                                                <input type="hidden" name="page" value="plugins" />
                                                <input type="hidden" name="action" value="renderplugin" />
                                                <input type="hidden" name="file" value="cars_attributes/conf.php" />
                                                <input type="hidden" name="section" value="makes" />
                                                <input type="hidden" name="plugin_action" value="make_add" />
                                                <input name="make" id="make" value="" />
                                                <button type="submit" ><?php _e('Add new', 'cars_attributes'); ?></button>
                                            </form>
                                        </fieldset>
                                    </div>
                                    <div style="clear: both;"></div>					
                                </div>
                            </div>
        <?php 
        break;
        case ("models"):    $makeId = Params::getParam("makeId");
                         ?>
                            <div id="settings_form" style="border: 1px solid #ccc; background: #eee; ">
                                <div style="padding: 20px;">
                                    <div style="float: left; width: 50%;">
                                        <fieldset>
                                            <legend><?php _e('Models', 'cars_attributes'); ?></legend>
                                            <?php $make = $conn->osc_dbFetchResults('SELECT * FROM %st_item_car_make_attr ORDER BY s_name ASC', DB_TABLE_PREFIX); ?>
                                            <select name="make" id="make" onchange="location.href = '<?php echo osc_admin_base_url(true);?>?page=plugins&action=renderplugin&file=cars_attributes/conf.php?section=models&makeId=' + this.value" >
                                                <option value=""><?php _e('Select a make', 'cars_attributes'); ?></option>
                                                <?php foreach($make as $a) { ?>
                                                <option value="<?php echo $a['pk_i_id']; ?>" <?php if($makeId==$a['pk_i_id']) { echo 'selected'; } ?>><?php echo $a['s_name']; ?></option>
                                                <?php } ?>
                                            </select>
                                            <form name="cars_form" id="cars_form" action="<?php echo osc_admin_base_url(true);?>" method="GET" enctype="multipart/form-data" >
                                                <input type="hidden" name="page" value="plugins" />
                                                <input type="hidden" name="action" value="renderplugin" />
                                                <input type="hidden" name="file" value="cars_attributes/conf.php" />
                                                <input type="hidden" name="section" value="models" />
                                                <input type="hidden" name="plugin_action" value="model_edit" />
                                                <input type="hidden" name="makeId" value="<?php echo  $makeId;?>" />
                                                <ul>
                                                <?php
                                                    if($makeId != "") {
                                                        $models = $conn->osc_dbFetchResults('SELECT * FROM %st_item_car_model_attr WHERE fk_i_make_id = %d ORDER BY s_name ASC', DB_TABLE_PREFIX, $makeId);
                                                        foreach($models as $model) {
                                                            echo '<li><input name="model['.$model['pk_i_id'].']" id="'.$model['pk_i_id'].'" type="text" value="'.$model['s_name'].'" /> <a href="'.osc_admin_base_url(true).'?page=plugins&action=renderplugin&file=cars_attributes/conf.php?section=models&plugin_action=model_delete&makeId='.$makeId.'&id='.$model['pk_i_id'].'" ><button>'.__('Delete', 'cars_attributes').'</button></a> </li>';
                                                        }
                                                    } else {
                                                        echo '<li>Select a make first.</li>';
                                                    }
                                                ?>
                                                </ul>
                                                <button type="submit"><?php _e('Edit', 'cars_attributes'); ?></button>
                                            </form>
                                        </fieldset>
                                    </div>
                            
                                    <div style="float: left; width: 50%;">
                                        <fieldset>
                                            <legend><?php _e('Add new model', 'cars_attributes'); ?></legend>
                                            <form name="cars_form" id="cars_form" action="<?php echo osc_admin_base_url(true); ?>" method="GET" enctype="multipart/form-data" >
                                                <input type="hidden" name="page" value="plugins" />
                                                <input type="hidden" name="action" value="renderplugin" />
                                                <input type="hidden" name="file" value="cars_attributes/conf.php" />
                                                <input type="hidden" name="section" value="models" />
                                                <input type="hidden" name="plugin_action" value="model_add" />

                                                <?php if($makeId != '') { ?>
                                                    <input type="hidden" name="makeId" value="<?php echo $makeId;?>" />
                                                    <input name="model" id="model" value="" />
                                                    <button type="submit" ><?php _e('Add new', 'cars_attributes'); ?></button>
                                                <?php } ?>
                                            </form>
                                        </fieldset>
                                    </div>
                                    <div style="clear: both;"></div>									
                                </div>
                            </div>
        <?php 
        break;
        case("types"): ?>
                            <div id="settings_form" style="border: 1px solid #ccc; background: #eee; ">
                                <div style="padding: 20px;">
                                    <div style="float: left; width: 50%;">
                                        <fieldset>
                                            <legend><?php _e('Vehicle types', 'cars_attributes'); ?></legend>
                                            <div class="tabber">
                                                <?php $locales = osc_get_locales();
                                                    $car_type = $conn->osc_dbFetchResults('SELECT * FROM %st_item_car_vehicle_type_attr', DB_TABLE_PREFIX);
                                                    $data = array();
                                                    foreach ($car_type as $c) {
                                                        $data[$c['fk_c_locale_code']][] = array('pk_i_id' => $c['pk_i_id'], 's_name' => $c['s_name']);
                                                    }
                                                ?>
                                                <?php foreach($locales as $locale) {?>
                                                <div class="tabbertab">
                                                    <h2><?php echo $locale['s_name']; ?></h2>
                                                    <form name="cars_form" id="cars_form" action="<?php echo osc_admin_base_url(true); ?>" method="GET" enctype="multipart/form-data" >
                                                        <input type="hidden" name="page" value="plugins" />
                                                        <input type="hidden" name="action" value="renderplugin" />
                                                        <input type="hidden" name="file" value="cars_attributes/conf.php" />
                                                        <input type="hidden" name="section" value="types" />
                                                        <input type="hidden" name="plugin_action" value="type_edit" />
                                                        <ul>
                                                            <?php
                                                            if(count($data)>0) {
                                                                foreach($data[$locale['pk_c_code']] as $car_type) { ?>
                                                                <li>
                                                                    <input name="car_type[<?php echo  $car_type['pk_i_id']; ?>][<?php echo  $locale['pk_c_code']; ?>]" id="<?php echo  $car_type['pk_i_id']; ?>" type="text" value="<?php echo  $car_type['s_name']; ?>" /> <a href="<?php echo osc_admin_base_url(true); ?>?page=plugins&action=renderplugin&file=cars_attributes/conf.php?section=types&plugin_action=type_delete&id=<?php echo  $car_type['pk_i_id']; ?>" ><button><?php _e('Delete', 'cars_attributes'); ?></button></a>
                                                                </li>
                                                            <?php } 
                                                            } ?>
                                                        </ul>
                                                        <button type="submit"><?php _e('Edit', 'cars_attributes'); ?></button>
                                                    </form>
                                                </div>
                                                <?php } ?>
                                            </div>
                                        </fieldset>
                                    </div>
                            
                                    <div style="float: left; width: 50%;">
                                        <fieldset>
                                            <legend><?php _e('Add new car type', 'cars_attributes'); ?></legend>
                                            <form name="cars_form" id="cars_form" action="<?php echo osc_admin_base_url(true);?>" method="GET" enctype="multipart/form-data" >
                                                <input type="hidden" name="page" value="plugins" />
                                                <input type="hidden" name="action" value="renderplugin" />
                                                <input type="hidden" name="file" value="cars_attributes/conf.php" />
                                                <input type="hidden" name="section" value="types" />
                                                <input type="hidden" name="plugin_action" value="type_add" />

                                                <div class="tabber">
                                                    <?php $locales = osc_get_locales();
                                                        $car_type = $conn->osc_dbFetchResults('SELECT * FROM %st_item_car_vehicle_type_attr', DB_TABLE_PREFIX);
                                                        $data = array();
                                                        foreach ($car_type as $c) {
                                                            $data[$locale['pk_c_code']] = array('pk_i_id' => $c['pk_i_id'], 's_name' => $c['s_name']);
                                                        }
                                                    ?>
                                                    <?php foreach($locales as $locale) { ?>
                                                    <div class="tabbertab">
                                                        <h2><?php echo $locale['s_name']; ?></h2>
                                                        <input name="<?php echo  $locale['pk_c_code']; ?>#car_type" id="car_type" type="text" value="" />
                                                    </div>
                                                    <?php } ?>
                                                </div>
                                                <button type="submit" ><?php _e('Add new', 'cars_attributes'); ?></button>
                                            </form>
                                        </fieldset>
                                    </div>
                                    <div style="clear: both;"></div>									
                                </div>
                            </div>
        <?php 
        break;
    } ?>
    
<div id="settings_form" style="border: 1px solid #ccc; background: #eee; ">
    <div style="padding: 20px;">
        <div style="float: left; width: 100%;">
            <fieldset style="border: 1px solid #ff0000;">
            <legend><?php _e('Warning', 'cars_attributes'); ?></legend>
                <p>
                <?php _e("Deleting makes or models may end in errors. Some of those makes/models could be attached to some actual items", 'cars_attributes'); ?>.
                </p>
            </fieldset>
        </div>
        <div style="clear: both;"></div>
    </div>
</div>
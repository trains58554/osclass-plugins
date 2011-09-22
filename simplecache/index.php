<?php
/*
Plugin Name: Simple Cache
Plugin URI: http://www.osclass.org/
Description: A simple cache system for OSClass, make your web load faster!
Version: 1.0
Author: OSClass
Author URI: http://www.osclass.org/
Short Name: simplecache
*/


    function simplecache_install() {
        @mkdir(osc_content_path().'uploads/simplecache/');
        $conn= getConnection();
        osc_set_preference('upload_path', osc_content_path().'uploads/simplecache/', 'simplecache', 'STRING');
        osc_set_preference('hours', '1', 'simplecache', 'INTEGER');
        $conn->commit();
    }

    function simplecache_uninstall() {
        $conn= getConnection();
        osc_delete_preference('upload_path', 'simplecache');
        osc_delete_preference('hours', 'simplecache');
        $conn->commit();
        $files = glob(osc_get_preference('upload_path', 'simplecache')."*.cache");
        foreach($files as $f) {
            @unlink($f);
        }
        @rmdir(osc_get_preference('upload_path', 'simplecache'));
    }
    
    function simplecache_before_search() {
        if(!osc_is_web_user_logged_in()) {
            if(osc_is_search_page()) {
                $conn = getConnection();
                $params = Params::getParamsAsArray();
                $should_be_cached = true;
                $cat_str = '';
                $co_str = '';
                $re_str = '';
                $ci_str = '';
                $feed_str = '';
                $pg_str = '';
                $pgsz_str = '';
                $sa_str = '';
                if(@$params['sPattern']=='') { // SPEED HACK, sPattern is one of the most use filters, Check it first
                    unset($params['sPattern']);
                    foreach($params as $k => $v) {
                        if($v!='') {
                            if($k=='page') {
                            } else if($k=='sCategory') {
                                $tmp = explode(",", $v);
                                if(count($v)>1) {
                                    $should_be_cached = false;
                                    break;
                                } else {
                                    if(is_numeric($v)) {
                                        $cat_str = '_cat_'.$v;
                                    } else {
                                        $category = preg_replace('|/$|','',$v);
                                        $aCategory = explode('/', $category) ;
                                        $category = Category::newInstance()->find_by_slug($aCategory[count($aCategory)-1]) ;
                                        $cat_str = '_cat_'.$category['pk_i_id'];
                                    }
                                }
                            } else if($k=='sCountry') {
                                if(strlen($v)==2) {
                                    $co_str = '_co_'.$v;
                                } else {
                                    $tmp = $conn->osc_dbFetchResult("SELECT pk_c_code FROM %st_country WHERE s_name = '%s'", DB_TABLE_PREFIX, $v);
                                    $co_str = '_co_'.$tmp['pk_c_code'];
                                }
                            } else if($k=='sRegion') {
                                if(is_numeric($v)) {
                                    $re_str = '_re_'.$v;
                                } else {
                                    $tmp = $conn->osc_dbFetchResult("SELECT pk_i_id FROM %st_region WHERE s_name = '%s'", DB_TABLE_PREFIX, $v);
                                    $re_str = '_re_'.$tmp['pk_i_id'];
                                }
                            /*} else if($k=='sCity') {
                                if(is_numeric($v)) {
                                    $ci_str = '_ci_'.$v;
                                } else {
                                    $tmp = $conn->osc_dbFetchResult("SELECT pk_i_id FROM %st_city WHERE s_name = '%s'", DB_TABLE_PREFIX, $v);
                                    $ci_str = '_ci_'.$tmp['pk_i_id'];
                                }*/
                            } else if($k=='sFeed') {
                                $feed_str = '_feed_'.$v;
                            } else if($k=='iPage') {
                                $pg_str = '_pg_'.$v;
                            } else if($k=='iPageSize') {
                                $pgsz_str = '_pgsz_'.$v;
                            } else if($k=='sShowAs') {
                                $sa_str = '_sa_'.$v;
                            } else {
                                $should_be_cached = false;
                                break;
                            }
                        }

                    }
                }
                if($should_be_cached) {
                    $filename = "search".$cat_str.$co_str.$re_str.$ci_str.$feed_str.$pg_str.$pgsz_str.$sa_str.".cache";
                    View::newInstance()->_exportVariableToView("simplecache_filename", $filename);
                    if(!file_exists(osc_get_preference('upload_path', 'simplecache').$filename)) {
                        ob_start();
                    } else {
                        require_once(osc_get_preference('upload_path', 'simplecache').$filename);
                        die;
                    }
                }
            }
        }
    }
    
    function simplecache_before_html() {
        if(!osc_is_web_user_logged_in()) {
            if(osc_is_ad_page()) {
                if(!file_exists(osc_get_preference('upload_path', 'simplecache')."item_".Params::getParam('id').".cache")) {
                    ob_start();
                } else {
                    require_once(osc_get_preference('upload_path', 'simplecache')."item_".Params::getParam('id').".cache");
                    die;
                }
            } else if(osc_is_static_page()) {
                if(!file_exists(osc_get_preference('upload_path', 'simplecache')."page_".Params::getParam('id').".cache")) {
                    ob_start();
                } else {
                    require_once(osc_get_preference('upload_path', 'simplecache')."page_".Params::getParam('id').".cache");
                    die;
                }
            }
        }
    }
    
    function simplecache_after_html() {
        if(!osc_is_web_user_logged_in()) {
            if(osc_is_ad_page()) {
                if(!file_exists(osc_get_preference('upload_path', 'simplecache')."item_".Params::getParam('id').".cache")) {
                    $contents = ob_get_contents();
                    ob_end_flush();
                    $handle = fopen(osc_get_preference('upload_path', 'simplecache').'item_'.Params::getParam('id').'.cache', "w");
                    @fwrite($handle, $contents);
                    fclose($handle);
                }
            } else if(osc_is_static_page()) {
                if(!file_exists(osc_get_preference('upload_path', 'simplecache')."page_".Params::getParam('id').".cache")) {
                    $contents = ob_get_contents();
                    ob_end_flush();
                    $handle = fopen(osc_get_preference('upload_path', 'simplecache').'page_'.Params::getParam('id').'.cache', "w");
                    @fwrite($handle, $contents);
                    fclose($handle);
                }
            } else if(View::newInstance()->_get("simplecache_filename")!='') {
                $filename = View::newInstance()->_get("simplecache_filename");
                if(!file_exists(osc_get_preference('upload_path', 'simplecache').$filename)) {
                    $contents = ob_get_contents();
                    ob_end_flush();
                    $handle = fopen(osc_get_preference('upload_path', 'simplecache').$filename, "w");
                    @fwrite($handle, $contents);
                    fclose($handle);
                }
            }
        }
    }
    
    function simplecache_delete_file($cat_id, $item_id) {
        @unlink(osc_get_preference('upload_path', 'simplecache').'item_'.$item_id.'.cache');
    }
    
    function simplecache_delete_item($item) {
        @unlink(osc_get_preference('upload_path', 'simplecache').'item_'.$item['pk_i_id'].'.cache');
    }
    
    function simplecache_clear_items() {
        $files = glob(osc_get_preference('upload_path', 'simplecache')."item_*.cache");
        foreach($files as $f) {
            @unlink($f);
        }
    }
    
    function simplecache_clear_feeds() {
        $files = glob(osc_get_preference('upload_path', 'simplecache').'search*_feed_*.cache');
        foreach($files as $f) {
            @unlink($f);
        }
    }
    
    function simplecache_clear_search() {
        $files = glob(osc_get_preference('upload_path', 'simplecache').'search_pg_*.cache');
        foreach($files as $f) {
            @unlink($f);
        }
        $files = glob(osc_get_preference('upload_path', 'simplecache').'search_pgsz_*.cache');
        foreach($files as $f) {
            @unlink($f);
        }
        $files = glob(osc_get_preference('upload_path', 'simplecache').'search_sa_*.cache');
        foreach($files as $f) {
            @unlink($f);
        }
        @unlink(osc_get_preference('upload_path', 'simplecache').'search.cache');
    }
    
    function simplecache_clear_all() {
        $files = glob(osc_get_preference('upload_path', 'simplecache').'*.cache');
        foreach($files as $f) {
            @unlink($f);
        }
    }
    
    function simplecache_clear_category($id = '') {
        $files = glob(osc_get_preference('upload_path', 'simplecache').'search_cat_'.$id.'*.cache');
        foreach($files as $f) {
            @unlink($f);
        }
    }
    
    function simplecache_clear_country($id = '') {
        $files = glob(osc_get_preference('upload_path', 'simplecache').'search*_co_'.$id.'*.cache');
        foreach($files as $f) {
            @unlink($f);
        }
    }
    
    function simplecache_clear_region($id = '') {
        $files = glob(osc_get_preference('upload_path', 'simplecache').'search*_re_'.$id.'*.cache');
        foreach($files as $f) {
            @unlink($f);
        }
    }
    
    function simplecache_admin_menu() {
        echo '<h3><a href="#">Simple Cache</a></h3>
        <ul> 
            <li><a href="' . osc_admin_render_plugin_url(osc_plugin_folder(__FILE__) . 'conf.php') . '">&raquo; ' . __('Settings', 'simplecache') . '</a></li>
        </ul>';
    }
    
    function simplecache_configure_link() {
        osc_plugin_configure_view(osc_plugin_path(__FILE__) );
    }
    
    /**
     * ADD HOOKS
     */
    osc_register_plugin(osc_plugin_path(__FILE__), 'simplecache_install');
    osc_add_hook(osc_plugin_path(__FILE__)."_uninstall", 'simplecache_uninstall');
    osc_add_hook(osc_plugin_path(__FILE__)."_configure", 'simplecache_configure_link');
    
    osc_add_hook('before_search', 'simplecache_before_search');
    osc_add_hook('before_html', 'simplecache_before_html');
    osc_add_hook('after_html', 'simplecache_after_html');

    osc_add_hook('item_edit_post', 'simplecache_delete_file');
    osc_add_hook('delete_item', 'simplecache_delete_item');
    osc_add_hook('theme_activate', 'simplecache_change_theme');
    
    osc_add_hook('admin_menu', 'simplecache_admin_menu');
    
?>
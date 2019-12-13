<?php
/**
* glFusion CMS
*
* Indexer - Alternative Search for glFusion
*
* Plugin Upgrade Module
*
* @license GNU General Public License version 2 or later
*     http://www.opensource.org/licenses/gpl-license.php
*
*  Copyright (C) 2018-2019 by the following authors:
*   Mark R. Evans   mark AT glfusion DOT org
*
*  Based on the Searcher Plugin for glFusion
*  @copyright  Copyright (c) 2017-2018 Lee Garner - lee AT leegarner DOT com
*
*/

if (!defined ('GVERSION')) {
    die ('This file can not be used on its own.');
}

// this function is called by lib-plugin whenever the 'Upgrade' option is
// selected in the Plugin Administration screen for this plugin

function indexer_upgrade()
{
    global $_TABLES, $_CONF, $_INDEXER_CONF, $_DB_table_prefix;

    $currentVersion = DB_getItem($_TABLES['plugins'],'pi_version',"pi_name='indexer'");

    switch ($currentVersion) {
        case '0.0.1' :
            DB_query("ALTER TABLE `{$_TABLES['idxer_indexer']}` ADD INDEX `type` (`type`);",1);
            DB_query("ALTER TABLE `{$_TABLES['idxer_indexer']}` ADD INDEX `item_date` (`ts`);",1);
            DB_query("ALTER TABLE `{$_TABLES['idxer_indexer']}` ADD INDEX `author` (`author`);",1);

        case '0.0.2' :
            // added stopwords to configuration

        case '0.0.3' :
            // added per page and excerpt size to configuration

        case '0.0.4' :
            // added summarize discussions to configuration

        case '0.0.5' :
            // added stemmer

        case '0.0.6' :
            // caching support - now requires glFusion v2.0

        case '0.0.7' :
            DB_query("ALTER TABLE `{$_TABLES['idxer_index']}` CHANGE COLUMN `content` `content` MEDIUMTEXT;",1);

        case '0.0.8' :
            // clean up and now allow glFusion v1.7.5+ support

        case '0.0.9' :
            // default type
            // updated stop words

        case '1.0.0' :
            // no DB changes

        case '1.0.1' :
            // no db changes

        case '1.0.2' :
            // no db changes
        default:
            DB_query("UPDATE {$_TABLES['plugins']} SET pi_version='".$_INDEXER_CONF['pi_version']."',
                    pi_gl_version='".$_INDEXER_CONF['gl_version']."' WHERE pi_name='indexer' LIMIT 1");
            break;
    }

    indexer_update_config();

    CTL_clearCache();

    if ( DB_getItem($_TABLES['plugins'],'pi_version',"pi_name='indexer'") == $_INDEXER_CONF['pi_version']) {
        return true;
    } else {
        return false;
    }
}

function indexer_update_config()
{
    global $_CONF, $_INDEXER_CONF, $_TABLES;

    $c = config::get_instance();

    require_once $_CONF['path'].'plugins/indexer/sql/indexer_config_data.php';

    // remove stray items
    $result = DB_query("SELECT * FROM {$_TABLES['conf_values']} WHERE group_name='indexer'");
    while ( $row = DB_fetchArray($result) ) {
        $item = $row['name'];
        if ( ($key = _searchForIdKey($item,$indexerConfigData)) === NULL ) {
            DB_query("DELETE FROM {$_TABLES['conf_values']} WHERE name='".DB_escapeString($item)."' AND group_name='indexer'");
        } else {
            $indexerConfigData[$key]['indb'] = 1;
        }
    }
    // add any missing items
    foreach ($indexerConfigData AS $cfgItem ) {
        if (!isset($cfgItem['indb']) ) {
            _addConfigItem( $cfgItem );
        }
    }
    $c = config::get_instance();
    $c->initConfig();
    $tcnf = $c->get_config('indexer');
    // sync up sequence, etc.
    foreach ( $indexerConfigData AS $cfgItem ) {
        $c->sync(
            $cfgItem['name'],
            $cfgItem['default_value'],
            $cfgItem['type'],
            $cfgItem['subgroup'],
            $cfgItem['fieldset'],
            $cfgItem['selection_array'],
            $cfgItem['sort'],
            $cfgItem['set'],
            $cfgItem['group']
        );
    }
}

if ( !function_exists('_searchForId')) {
    function _searchForId($id, $array) {
       foreach ($array as $key => $val) {
           if ($val['name'] === $id) {
               return $array[$key];
           }
       }
       return null;
    }
}

if ( !function_exists('_searchForIdKey')) {
    function _searchForIdKey($id, $array) {
       foreach ($array as $key => $val) {
           if ($val['name'] === $id) {
               return $key;
           }
       }
       return null;
    }
}

if ( !function_exists('_addConfigItem')) {
    function _addConfigItem($data = array() )
    {
        global $_TABLES;

        $Qargs = array(
                       $data['name'],
                       $data['set'] ? serialize($data['default_value']) : 'unset',
                       $data['type'],
                       $data['subgroup'],
                       $data['group'],
                       $data['fieldset'],
                       ($data['selection_array'] === null) ?
                        -1 : $data['selection_array'],
                       $data['sort'],
                       $data['set'],
                       serialize($data['default_value']));
        $Qargs = array_map('DB_escapeString', $Qargs);

        $sql = "INSERT INTO {$_TABLES['conf_values']} (name, value, type, " .
            "subgroup, group_name, selectionArray, sort_order,".
            " fieldset, default_value) VALUES ("
            ."'{$Qargs[0]}',"   // name
            ."'{$Qargs[1]}',"   // value
            ."'{$Qargs[2]}',"   // type
            ."{$Qargs[3]},"     // subgroup
            ."'{$Qargs[4]}',"   // groupname
            ."{$Qargs[6]},"     // selection array
            ."{$Qargs[7]},"     // sort order
            ."{$Qargs[5]},"     // fieldset
            ."'{$Qargs[9]}')";  // default value

        DB_query($sql);
    }
}
?>
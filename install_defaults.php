<?php
/**
* glFusion CMS
*
* Indexer - Alternative Search Plugin
*
* Configuration Defaults
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

global $_INDEXER_CONF;

if (!isset($_INDEXER_CONF) || empty($_INDEXER_CONF)) {
    $_SRCH_CONF = array();
    require_once dirname(__FILE__) . '/indexer.php';
}

/**
*   Initialize Indexer plugin configuration
*
*   @return boolean             true: success; false: an error occurred
*/
function plugin_initconfig_indexer()
{
    global $_CONF;

    $c = config::get_instance();

    if (!$c->group_exists('indexer')) {
        require_once $_CONF['path'].'plugins/indexer/sql/indexer_config_data.php';

        foreach ( $indexerConfigData AS $cfgItem ) {
            $c->add(
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
     return true;
}

?>

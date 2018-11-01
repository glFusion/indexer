<?php
/**
* glFusion CMS
*
* Indexer - Alternative Search Plugin
*
* Auto Installation
*
* @license GNU General Public License version 2 or later
*     http://www.opensource.org/licenses/gpl-license.php
*
*  Copyright (C) 2018 by the following authors:
*   Mark R. Evans   mark AT glfusion DOT org
*
*  Based on the Searcher Plugin for glFusion
*  @copyright  Copyright (c) 2017-2018 Lee Garner - lee AT leegarner DOT com
*
*/

if (!defined ('GVERSION')) {
    die ('This file can not be used on its own.');
}

global $_DB_dbms;

require_once $_CONF['path'].'plugins/indexer/functions.inc';
require_once $_CONF['path'].'plugins/indexer/indexer.php';
require_once $_CONF['path'].'plugins/indexer/sql/mysql_install.php';


$INSTALL_plugin['indexer'] = array(
    'installer' => array('type' => 'installer', 'version' => '1', 'mode' => 'install'),

    'plugin' => array('type' => 'plugin',
            'name'      => $_INDEXER_CONF['pi_name'],
            'ver'       => $_INDEXER_CONF['pi_version'],
            'gl_ver'    => $_INDEXER_CONF['gl_version'],
            'url'       => $_INDEXER_CONF['pi_url'],
            'display'   => $_INDEXER_CONF['pi_display_name']
    ),
    array('type' => 'table',
            'table'     => $_TABLES['idxer_index'],
            'sql'       => $_SQL['idxer_index'],
    ),
    array('type' => 'table',
            'table'     => $_TABLES['idxer_stats'],
            'sql'       => $_SQL['idxer_stats'],
    ),

);


/**
*   Puts the datastructures for this plugin into the glFusion database
*   Note: Corresponding uninstall routine is in functions.inc
*
*   @return boolean     True if successful False otherwise
*/
function plugin_install_indexer()
{
    global $INSTALL_plugin, $_INDEXER_CONF;

    $pi_name            = $_INDEXER_CONF['pi_name'];
    $pi_display_name    = $_INDEXER_CONF['pi_display_name'];
    $pi_version         = $_INDEXER_CONF['pi_version'];

    COM_errorLog("Attempting to install the $pi_display_name plugin", 1);

    $ret = INSTALLER_install($INSTALL_plugin[$pi_name]);
    if ($ret > 0) {
        return false;
    }

    return true;
}


/**
*   Automatic removal function.
*
*   @return array       Array of items to be removed.
*/
function plugin_autouninstall_indexer()
{
    $out = array (
        'tables'    => array('idxer_index','idxer_stats'),
        'groups'    => array(),
        'features'  => array(),
        'php_blocks' => array(),
        'vars'      => array(),
    );
    return $out;
}


/**
*   Loads the configuration records for the Online Config Manager.
*
*   @return boolean     True = proceed, False = an error occured
*/
function plugin_load_configuration_indexer()
{
    require_once dirname(__FILE__) . '/install_defaults.php';
    return plugin_initconfig_indexer();
}

?>

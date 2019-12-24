<?php
/**
* glFusion CMS
*
* Indexer - Alternative Search
*
* Plugin configuration
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

global $_DB_table_prefix, $_TABLES;

$_INDEXER_CONF['pi_name']            = 'indexer';
$_INDEXER_CONF['pi_display_name']    = 'Indexer';
$_INDEXER_CONF['pi_version']         = '1.0.1';
$_INDEXER_CONF['gl_version']         = '1.7.6';
$_INDEXER_CONF['pi_url']             = 'https://www.glfusion.org';

$_TABLES['idxer_index']     = $_DB_table_prefix .  'idxer_index';
$_TABLES['idxer_stats']     = $_DB_table_prefix .  'idxer_stats';

?>

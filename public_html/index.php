<?php
/**
* glFusion CMS
*
* Indexer - Alternative Search Plugin
*
* Public search page
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

require_once '../lib-common.php';

if ($_INDEXER_CONF['replace_stock_search']) {
    echo COM_refresh($_CONF['site_url'].'/search.php');
}


$S = new Indexer\Indexer();

$results = $S->doSearch();

$display = COM_siteHeader('menu', $LANG09[11]);
$display .= $results;
$display .= COM_siteFooter();

echo $display;

?>

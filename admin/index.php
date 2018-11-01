<?php
/**
* glFusion CMS
*
* Indexer Admin Interface
*
* @license GNU General Public License version 2 or later
*     http://www.opensource.org/licenses/gpl-license.php
*
*  Copyright (C) 2018 by the following authors:
*   Mark R. Evans   mark AT glfusion DOT org
*
*  Based on the Searcher Plugin for glFusion
*  @copyright  Copyright (c) 2017 Lee Garner <lee@leegarner.com>
*
*/

require_once '../../../lib-common.php';
require_once '../../auth.inc.php';
require_once $_CONF['path'].'plugins/indexer/include/admin.inc.php';

USES_lib_admin();

$display = '';
$pi_title = $LANG_INDEXER_ADM['indexer_admin'];

// If user isn't a root user or if the backup feature is disabled, bail.
if (!SEC_inGroup('Root')) {
    COM_accessLog("User {$_USER['username']} tried to access the Indexer admin screen.");
    COM_404();
    exit;
}

/**
*   View the search queries made by guests.
*
*   @return string  Admin list of search terms and counts
*/
function INDEXER_admin_terms()
{
    global $_CONF, $_INDEXER_CONF, $_TABLES, $LANG_ADMIN, $LANG_INDEXER_ADM;

    $retval = '';
    $filter = '';

    $token = SEC_createToken();

    $header_arr = array(      # display 'text' and use table field 'field'
        array(
            'text' => $LANG_INDEXER_ADM['search_terms'],
            'field' => 'term',
            'sort' => true,
        ),
        array(
            'text' => $LANG_INDEXER_ADM['queries'],
            'field' => 'hits',
            'sort' => true,
            'align' => 'right',
        ),
        array(
            'text' => $LANG_INDEXER_ADM['results'],
            'field' => 'results',
            'sort' => true,
            'align' => 'right',
        ),
    );

    $defsort_arr = array('field' => 'hits', 'direction' => 'desc');

    $retval .= INDEXER_adminMenu('counters');

    $text_arr = array(
        'has_extras' => true,
        'form_url' => $_CONF['site_admin_url'].'/plugins/indexer/index.php?counters=x',
    );
    $filter .= '<button type="submit" name="clearcounters" style="float:left;" class="uk-button uk-button-danger">' .
        $LANG_INDEXER_ADM['clear_counters'] . '</button>';

    $query_arr = array('table' => 'idxer_counters',
        'sql' => "SELECT term, hits, results FROM {$_TABLES['idxer_stats']}",
        'query_fields' => array('term'),
        'default_filter' => 'WHERE 1=1',
    );
    $retval .= ADMIN_list('searcher', 'INDEXER_getListField_counters', $header_arr,
                    $text_arr, $query_arr, $defsort_arr, $filter, $token, '', '');

    return $retval;
}


/**
*   Get the value for list fields in admin lists.
*   For the search term list, just returns the field values.
*
*   @param  string  $fieldname  Name of field
*   @param  mixed   $fieldvalue Field value
*   @param  array   $A          Complete database record
*   @param  array   $icon_arr   Icon array (not used)
*   @param  string  $token      Admin token
*/
function INDEXER_getListField_counters($fieldname, $fieldvalue, $A, $icon_arr, $token)
{
    global $_CONF, $_INDEXER_CONF, $_USER, $LANG_ACCESS, $LANG_ADMIN;

    $retval = '';

    switch($fieldname) {
        case 'term':
            if ($_INDEXER_CONF['replace_stock_search']) {
                $url = $_CONF['site_url'].'/search.php?mode=search&amp;q=' . urlencode($fieldvalue).'&amp;nc=x';
            } else {
                $url = $_CONF['site_url'].'/indexer/index.php?q=' . urlencode($fieldvalue).'&amp;nc=x';
            }
            $retval = COM_createlink($fieldvalue,$url);
            break;
        default:
            $retval = $fieldvalue;
            break;
    }
    return $retval;
}


$view = '';
$action = '';
$expected = array(
    // Actions
    'genindex', 'clearcounters',
    // Views, no action
    'gen_all', 'counters',
);
foreach($expected as $provided) {
    if (isset($_POST[$provided])) {
        $action = $provided;
        break;
    } elseif (isset($_GET[$provided])) {
        $action = $provided;
        break;
    }
}

$content = '';
$message = '';
$view = '';
switch ($action) {
case 'genindex':
    if (!isset($_POST['pi']) || empty($_POST['pi'])) {
        break;
    }
    foreach ($_POST['pi'] as $pi_name=>$checked) {
        $func = 'indexer_IndexAll_' . $pi_name;
        if (function_exists($func)) {
            $count = $func();
            $message .= "<br />$pi_name: Indexed $count Items";
        }
    }
    break;
case 'clearcounters':
    DB_query("TRUNCATE {$_TABLES['idxer_stats']}");
    $view = 'counters';
    break;

default:
    $view = $action;
    break;
}

switch ($view) {
case 'gen_all':
    $content .= INDEXER_adminMenu('gen_all');
    $T = new Template($_CONF['path'].'private/plugins/indexer/templates');
    $T->set_file('admin', 'admin.thtml');
    $T->set_var(array(
        'pi_url'    => $_CONF['site_url'].'/indexer/index.php',
        'header'    => $_INDEXER_CONF['pi_display_name'],
        'version'   => $_INDEXER_CONF['pi_version'],
        'pi_icon'   => plugin_geticon_indexer(),
    ) );
    foreach ($_PLUGINS as $pi_name) {
        if (function_exists('indexer_IndexAll_' . $pi_name)) {
            $T->set_block('admin', 'plugins', 'pRow');
            $T->set_var('pi_name', $pi_name);
            $T->parse('pRow', 'plugins', true);
        }
    }
    $T->parse('output', 'admin');
    $content .= $T->finish($T->get_var('output'));
    break;
case 'counters':
default:
    $content .= INDEXER_admin_terms();
    break;
}

$display .= COM_siteHeader('menu', $pi_title);
$display .= $content;
$display .= $message;
$display .= COM_siteFooter();

echo $display;

?>

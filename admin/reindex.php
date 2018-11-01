<?php
/**
* glFusion CMS
*
* Indexer - Alternative Search Plugin
*
* Reindex Administrative Interface
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

require_once '../../../lib-common.php';
require_once '../../auth.inc.php';
require_once $_CONF['path'].'plugins/indexer/include/admin.inc.php';
require_once $_CONF['path'].'plugins/indexer/include/reindex.ajax.php';

USES_lib_admin();

function INDEXER_reindex()
{
    global $_CONF, $_INDEXER_CONF, $_PLUGINS, $LANG01, $LANG_ADMIN, $LANG_SRCH, $LANG_INDEXER_ADM, $_IMAGE_TYPE;

    $retval = '';

    $T = new \Template($_CONF['path'] . 'plugins/indexer/templates');
    $T->set_file('page','reindex.thtml');

    $retval .= INDEXER_adminMenu('reindex');

    $T->set_var('lang_title',$LANG_INDEXER_ADM['reindex_title']);

    $T->set_var('lang_conversion_instructions', $LANG_INDEXER_ADM['index_instructions']);

    $T->set_block('page', 'contenttypes', 'ct');

    $plugintypes = array();
    $plugintypes[] = 'article';
    $plugintypes = array_merge($plugintypes,$_PLUGINS);
    sort($plugintypes);

    $T->set_var('content_type','article');
    $T->parse('ct', 'contenttypes',true);
    foreach ($plugintypes as $pi_name) {
        if (function_exists('plugin_getiteminfo_' . $pi_name)) {
            $T->set_var('content_type',$pi_name);
            $T->parse('ct', 'contenttypes',true);
        }
    }
    $T->set_var('security_token',SEC_createToken());
    $T->set_var('security_token_name',CSRF_TOKEN);
    $T->set_var(array(
        'form_action'       => $_CONF['site_admin_url'].'/plugins/indexer/reindex.php',
        'lang_index'        => $LANG_INDEXER_ADM['reindex_button'],
        'lang_cancel'       => $LANG_ADMIN['cancel'],
        'lang_ok'           => $LANG01['ok'],
        'lang_empty'        => $LANG_INDEXER_ADM['empty_table'],
        'lang_indexing'     => $LANG_INDEXER_ADM['indexing'],
        'lang_success'      => $LANG_INDEXER_ADM['success'],
        'lang_ajax_status'  => $LANG_INDEXER_ADM['index_status'],
        'lang_retrieve_content_types' => $LANG_INDEXER_ADM['retrieve_content_types'],
        'lang_error_header' => $LANG_INDEXER_ADM['error_heading'],
        'lang_no_errors'    => $LANG_INDEXER_ADM['no_errors'],
        'lang_error_getcontenttypes' => $LANG_INDEXER_ADM['error_getcontenttypes'],
        'lang_current_progress' => $LANG_INDEXER_ADM['current_progress'],
        'lang_overall_progress' => $LANG_INDEXER_ADM['overall_progress'],
        'lang_remove_content_1' => $LANG_INDEXER_ADM['remove_content_1'],
        'lang_remove_content_2' => $LANG_INDEXER_ADM['remove_content_2'],
        'lang_content_type' => $LANG_INDEXER_ADM['content_type'],
        'lang_remove_fail'  => $LANG_INDEXER_ADM['remove_fail'],
        'lang_retrieve_content_list' => $LANG_INDEXER_ADM['retrieve_content_list'],
    ));

    $T->parse('output', 'page');
    $retval .= $T->finish($T->get_var('output'));

    return $retval;
}

// main driver
$action = '';
$expected = array('reindex','getcontenttypes','getcontentlist','index','removeoldcontent','complete','contentcomplete');
foreach($expected as $provided) {
    if (isset($_POST[$provided])) {
        $action = $provided;
    } elseif (isset($_GET[$provided])) {
	    $action = $provided;
    }
}

if ( isset($_POST['cancelbutton'])) COM_refresh($_CONF['site_admin_url'].'/plugins/indexer/index.php');

switch ($action) {
    case 'reindex':
        $pagetitle = $LANG_INDEX['reindex_title'];
        $page .= INDEXER_reindex();
        break;
    case 'getcontenttypes' :
        // return json encoded list of content types
        INDEXER_getContentTypesAjax();
        break;
    case 'removeoldcontent' :
        INDEXER_removeOldContentAjax();
        break;
    case 'getcontentlist' :
        // return list of all content type ids
        INDEXER_getContentListAjax();
        break;
    case 'index' :
        // index a content item via ajax
        INDEXER_indexContentItemAjax();
        break;
    case 'contentcomplete' :
        INDEXER_completeContentAjax();
        break;
    case 'complete' :
        INDEXER_completeAjax();
        break;
    default :
        $page = INDEXER_reindex();
        break;
}

$display  = COM_siteHeader('menu', $LANG_INDEXER_ADM['indexer_admin']);
$display .= $page;
$display .= COM_siteFooter();
echo $display;
?>

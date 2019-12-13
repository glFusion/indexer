<?php
/**
* glFusion CMS
*
* Indexer - Alternative Search Plugin
*
* glFusion API functions
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


if (!defined('GVERSION')) {
    die('This file can not be used on its own.');
}

$indexer_config = \config::get_instance();
$_INDEXER_CONF = $indexer_config->get_config('indexer');

/**
* Language file Include
*/
$langfile = $_CONF['path'] . 'plugins/indexer/language/' . $_CONF['language'] . '.php';

if (file_exists ($langfile)) {
    include_once $langfile;
} else {
    include_once $_CONF['path'] . 'plugins/indexer/language/english_utf-8.php';
}

/*
* Include Indexer config file
*/
require_once $_CONF['path'] . 'plugins/indexer/indexer.php';

if (!isset($_INDEXER_CONF['per_page'])) $_INDEXER_CONF['per_page'] = 20;
if (!isset($_INDEXER_CONF['excerpt_length'])) $_INDEXER_CONF['excerpt_length'] = 100;
if (!isset($_INDEXER_CONF['summarize_discussions'])) $_INDEXER_CONF['summarize_discussions'] = false;

if ( isset($_INDEXER_CONF['replace_stock_search']) && $_INDEXER_CONF['replace_stock_search'] == true ) {
    PLG_registerService('search','\\Indexer\\Indexer');
}

/* Auto loader */

spl_autoload_register('indexer_autoloader');

function indexer_autoloader($class) {
    global $_CONF;
    if (strpos($class, 'Indexer\\') === 0) {
        $class = str_replace('Indexer\\', '', $class);
        $path = $_CONF['path'].'plugins/indexer/classes/' . $class . '.php';
        if (file_exists($path)) {
            include $path;
        }
    }
}

/**
*   Get the currently installed version.
*
*   @return string  version number
*/
function plugin_chkVersion_indexer()
{
    global $_INDEXER_CONF;
    return $_INDEXER_CONF['pi_version'];
}


/**
*   Puts an option for the plugin in the command and control block.
*   Not used in glFusion versions > 1.6.0 since the database backup function
*   has been updated in the main DB Backup option.
*
*   @return   array  Array of (plugin name, admin url, url of plugin icon)
*/
function plugin_cclabel_indexer()
{
    global $LANG_INDEXER, $_CONF;

    if (SEC_inGroup('Root')) {
        return array(
            $LANG_INDEXER['menu_label'],
            $_CONF['site_admin_url'] . '/plugins/indexer/index.php',
            plugin_geticon_indexer(),
        );
    } else {
        return '';
    }
}


/**
*   Adds the plugin to the Admin menu.
*
*   @return array   Array(pi_name, admin url, # of items) or '' for none.
*/
function plugin_getadminoption_indexer()
{
    global $_CONF, $LANG_INDEXER;

    if (SEC_inGroup('Root')) {
        $retval = array(
            $LANG_INDEXER['menu_label'],
            $_CONF['site_admin_url'].'/plugins/indexer/index.php',
            0,
        );
    } else {
        $retval = '';
    }
    return $retval;
}


/**
*   Returns the URL of the plugin's icon.
*
*   @return string      URL of the icon
*/
function plugin_geticon_indexer()
{
    global $_CONF;
    return $_CONF['site_url'].'/indexer/images/indexer.png';
}


/**
*   Get the URL to the configuration help file.
*
*   @param  string  $option     Config option selected
*   @param  string  $doclang    Document language
*   @return string              URL to document
*/
function plugin_getconfigelementhelp_indexer($option, $doclang='english')
{
    global $_CONF, $_INDEXER_CONF;

    $file = 'config.html';

    $doc_url = $_CONF['site_url'] . '/indexer/docs/'.$doclang.'/config.html';

     if (empty($doc_url))
        return false;

    if (!empty($option)) {
        $doc_url .= '#desc_' . $option;
    }

    return array($doc_url, 0);
}


/**
* Called by the plugin Editor to run the SQL Update for a plugin update
*/
function plugin_upgrade_indexer() {
    global $_CONF, $_TABLES,$_INDEXER_CONF;

    require_once $_CONF['path'] . 'plugins/indexer/upgrade.php';

    return indexer_upgrade();
}


/**
*   Get additional CSS into the site header.
*   Allows for custom CSS to be placed by the site admin.
*
*   @return array   List of paths to CSS files
*/
function plugin_getheadercss_indexer()
{
    global $_CONF, $_INDEXER_CONF;

}


/**
*   Index an item when it is saved.
*   First deletes any existing index records, then creates newones.
*
*   @param  string  $id     Item ID
*   @param  string  $type   Item Type
*/
function plugin_itemsaved_indexer($id, $type, $old_id = '')
{
    global $_INDEXER_CONF, $_TABLES;

    if ($type == 'indexer') return;

    if (!function_exists('plugin_getiteminfo_' . $type)) {
        return;
    }

    if ($_INDEXER_CONF['summarize_discussions'] == true) {
        $contentInfo = PLG_getItemInfo(
            $type, $id,
            'summary,id,date,parent_type,parent_id,title,searchidx,author,author_name,hits,perms,search_index,status'
        );
    } else {
        $contentInfo = PLG_getItemInfo(
            $type, $id,
            'id,date,parent_type,parent_id,title,searchidx,author,author_name,hits,perms,search_index,status'
        );
    }

    if ($contentInfo !== false && is_array($contentInfo) && count($contentInfo) > 0) {
        if (!isset($contentInfo['perms']) || empty($contentInfo['perms'])) {
            $contentInfo['perms'] = array(
                'owner_id' => 2,
                'group_id' => 1,
                'perm_owner' => 3,
                'perm_group' => 2,
                'perm_members' => 2,
                'perm_anon' => 2,
            );
        }
        // If an "enabled" status isn't returned by the plugin, assume enabled
        if (!isset($contentInfo['status']) || is_null($contentInfo['status'])) {
            $contentInfo['status'] = 1;
        }

        $props = array(
            'item_id' => $contentInfo['id'],
            'type'  => $type,
            'author' => $contentInfo['author'],
            'author_name' => $contentInfo['author_name'],
            // Hack to avoid indexing comment titles which don't show anyway
            'title' => $type == 'comment' ? NULL : $contentInfo['title'],
            'content' => $contentInfo['searchidx'],
            'date' => $contentInfo['date'],
            'perms' => $contentInfo['perms'],
            'parent_id' => $contentInfo['parent_id'],
            'parent_type' => $contentInfo['parent_type'],
        );
        if ( $old_id != '' && $id != $old_id ) {
            \Indexer\Indexer::RemoveDoc($type, $old_id);
        }
        if ($contentInfo['status']) {
            // Index only if status is nonzero (i.e. not draft or disabled)
            \Indexer\Indexer::IndexDoc($props);
        }
    } else {
        // if we didn't find anything for this item - assume it is gone
        \Indexer\Indexer::RemoveDoc($type, $id);
    }
    $c = Cache::getInstance()->deleteItemsByTag('searchcache');
}


/**
*   Delete index records for a deleted item.
*
*   @param  string  $id     Item ID
*   @param  string  $type   Item Type, e.g. plugin name
*   @param  string  $children   Optional comma-separated values to delete
*/
function plugin_itemdeleted_indexer($id, $type, $children='')
{
    if ( $type == 'indexer' ) return;
    if (!empty($children)) {
        $id = explode(',', $children);
    }
    \Indexer\Indexer::RemoveDoc($type, $id);
    $c = \glFusion\Cache::getInstance()->deleteItemsByTag('searchcache');
}


/**
*   PLG function to index a single document
*
*   @param  array   $args       Args, including type, item_id, title, etc.
*   @return boolean     True on success, False on failure
*/
function plugin_indexDoc_indexer($args)
{
    // Check that the minimum required fields are set
    if (!isset($args['item_id']) || !isset($args['type']) ||
        (!isset($args['content']) && !isset($args['title']) && !isset($args['author']))
    ) {
        return false;
    }
    \Indexer\Indexer::RemoveDoc($args['type'], $args['item_id']);
    return \Indexer\Indexer::IndexDoc($args);
}


/**
*   PLG function to remove an item from the index.
*   Makes sure that a valid type and item_id are set to remove one item.
*
*   @param  array   $args       Array of item info.
*   @param  mixed   $output     Output to set (not used)
*   @param  mixed   $svc_msg    Service message (not used)
*   @return integer     PLG_RET_OK on success, PLG_RET_ERROR on error
*/
function plugin_RemoveDoc_indexer($type, $item_id)
{
    if (!\Indexer\Indexer::RemoveDoc($type, $item_id)) return false;
    \Indexer\Indexer::RemoveComments($type, $item_id);
    return true;
}


/**
*   PLG function to remove all items for a plugin from the index.
*   May be called during plugin removal.
*   Makes sure that a valid type is set to remove all items for a single type.
*
*   @param  array   $args       Array of item info.
*   @param  mixed   $output     Output to set (not used)
*   @param  mixed   $svc_msg    Service message (not used)
*   @return integer     PLG_RET_OK on success, PLG_RET_ERROR on error
*/
function plugin_removeAll_indexer($type)
{
    // Check that the minimum required fields are set. Don't allwow plugins
    // to accidentally delete all.
    if (empty($type) || $type == 'all') {
        return false;
    }
    if (!\Indexer\Indexer::RemoveAll($type)) return false;
    \Indexer\Indexer::RemoveComments($type);
    return true;
}


/**
*   Selection dropdown to pick the stemmer in the configuration manager.
*
*   @return Array Associative array of section_name=>section_id
*/
function plugin_configmanager_select_stemmer_indexer()
{
    global $LANG_SRCH;

    $A = array($LANG_SRCH['none'] => '');
    // Collect the available stemmers
    $results = @glob(__DIR__ . '/classes/stemmer/*.class.php');
    $installable = '';
    if (is_array($results)) {
        foreach ($results as $fullpath) {
            $parts = explode('/', $fullpath);
            list($class,$x1,$x2) = explode('.', $parts[count($parts)-1]);
            $A[$class] = $class;
        }
    }
    return $A;
}


/**
*   Reindex all comments for the given type
*
*   @param  string  $type   Content type, e.g. "article", "staticpages"
*   @param  mixed   $pid    Parent content item ID
*   @param  mixed   $perms  Permission array from content item, or NULL
*   @return integer     Count of articles indexed
*/
//plugin_IndexAll_comments
function indexer_IndexAll_comments($type, $pid, $perms=NULL)
{
    global $_TABLES;

    if ( ! \Indexer\Indexer::CommentsEnabled() ) {
        return 0;
    }

    $type = DB_escapeString($type);
    $pid = DB_escapeString($pid);
    $sql = "SELECT cid, sid, uid, title, comment, UNIX_TIMESTAMP(date) AS unixdate
        FROM {$_TABLES['comments']}
        WHERE type = '$type' AND sid = '$pid'";
    //echo $sql;die;
    $res = DB_query($sql);
    $count = 0;
    // Remove all existing index records for this content
    \Indexer\Indexer::RemoveComments($type, $pid);
    while ($A = DB_fetchArray($res, false)) {
        $count++;
        $props = array(
            'item_id' => $A['cid'],
            'parent_id' => $A['sid'],
            'parent_type' => $type,
            'type'  => 'comment',
            //'title' => $A['title'],
            'author' => $A['uid'],
            'content' => $A['comment'],
            'date' => $A['unixdate'],
        );
        if (is_array($perms)) {
            $props['perms'] = array(
                'owner_id' => $perms['owner_id'],
                'group_id' => $perms['group_id'],
                'perm_owner' => $perms['perm_owner'],
                'perm_group' => $perms['perm_group'],
                'perm_members' => $perms['perm_members'],
                'perm_anon' => $perms['perm_anon'],
            );
        }
        \Indexer\Indexer::IndexDoc($props);
    }
    return $count;
}

?>

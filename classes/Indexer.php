<?php
/**
* glFusion CMS
*
* Indexer - Alternative Search Plugin
*
* Indexer Interface
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

namespace Indexer;

if (!defined ('GVERSION')) {
    die ('This file can not be used on its own.');
}

include $_CONF['path'].'plugins/indexer/include/wordstemmer.php';

class Indexer
{
    /**
     * @var totalResults - total number of results from search
     */
    protected $totalResults = 0;

    /**
     * @var results - array of results from search
     */
    protected $results = array();

    /**
     * @var page - current page of displayed results
     */
    protected $page = 1;

    /**
     * @var itemType - item type filter
     */
    protected $itemType = '';

    /**
     * @var query - query string from user input
     */
    protected $query = '';

    /**
     * @var searchAuthor - uid of author to search - 0 = do not search by author
     */
    protected $searchAuthor  = 0;

    /**
     * @var searchDays
     */
    private $searchDays = 0;

    /**
     * @var searchType
     */
    private $searchType = 'any';

    /**
     * @var searchTime
     */
    private $searchTime = 0.0;

    /**
     * @var alteredSearch
     */
    private $alteredSearch = 0;


    /**
    *   Class constructor - sets current search criteria based on $_GET vars
    *
    *   @param  string  $query   search query
    */
    public function __construct($query = '')
    {
        global $_INDEXER_CONF;

        // ensure default search_type is set
        if (isset($_INDEXER_CONF['search_type'])) {
            $this->searchType = $_INDEXER_CONF['search_type'];
        } else {
            $_INDEXER_CONF['search_type'] = 'any';
        }


        // limit search to a specifc content type
        if (isset($_GET['type'])) {
            $this->setType($_GET['type']);
        }

        // set search type of all, any or phrase
        if (isset($_GET['keyType'])) {
            $this->searchType = $_GET['keyType'];
            if (!in_array($this->searchType,array('any','all','phrase'))) {
                $this->searchType = $_INDEXER_CONF['search_type'];
            }
        }

        // limit search by timeframe
        if (isset($_GET['st'])) {
            $this->searchDays = (int) COM_applyFilter($_GET['st'],true);
            if (!in_array($this->searchDays, array(0,1,7,14,30,60,90,180,365,730))) {
                $this->searchDays = 0;
            }
        }
        // check to see which search keys are enabled
        if (isset($_GET['author'])) {
            $this->searchAuthor = (int) COM_applyFilter($_GET['author'],true);
        }
        // Could pass in a query string, generally comes from $_GET
        if (!empty($query)) {
            $this->setQuery($query);
        } elseif (isset($_GET['q'])) {
            $this->setQuery($_GET['q']);
        } elseif (isset($_GET['query'])) {
            $this->setQuery($_GET['query']);
        }

        // get current page
        if (isset($_GET['page'])) {
            $this->setPage($_GET['page']);
        }
    }

    /**
    *   Set the search scope by item type (article, staticpage, etc)
    *
    *   @param  string  $type   Type of item
    */
    public function setType($type)
    {
        global $_PLUGINS;

        switch ($type) {
            case '':
            case 'all':
                $this->itemType = '';
                break;
            case 'stories' :
            case 'article':
                $this->itemType = 'article';
                break;
            case 'comment':
                $this->itemType = 'comment';
                break;
            default:
                if (in_array($type,$_PLUGINS)) {
                    $this->itemType = DB_escapeString($type);
                } else {
                    $this->itemType = '';
                }
            }
    }

    /**
    *   Set the search page number, minimum is "1"
    *
    *   @param  int $page   Page number
    */
    public function setPage($page = 1)
    {
        $this->page = $page > 0 ? (int)$page : 1;
    }

    /**
    *   Sets the query string
    *
    *   @param  string  $query  Query string
    */
    public function setQuery($query)
    {
        $this->query = $query;
    }


    /**
    *   Indexes a document / content
    *
    *   @param  string  $content  content to index
    */
    public static function IndexDoc($content)
    {
        global $_INDEXER_CONF, $_TABLES;

        // Remove autotags
        if (isset($content['content']) && !empty($content['content'])) {
            $content['content'] = self::removeAutoTags($content['content']);
        }

        if (!isset($content['title'])) $content['title'] = '';

        if ((!isset($content['author_name']) || empty($content['author_name']))
              && is_numeric($content['author']) && $content['author'] > 0
        ) {
            $content['author_name'] = COM_getDisplayName($content['author']);
        }
        $content['author_name'] = substr($content['author_name'],0,40);

        $indexContent = self::cleanString($content['content']);
        $indexTitle = self::cleanString($content['title']);

        $parent_id = isset($content['parent_id']) && !empty($content['parent_id']) ?
                $content['parent_id'] : $content['item_id'];

        $parent_type = isset($content['parent_type']) && !empty($content['parent_type']) ?
                $content['parent_type'] : $content['type'];

        $ts = isset($content['date']) ? (int)$content['date'] : time();

        $grp_access = 2;    // default to all users access if no perms sent
        if (isset($content['perms']) && is_array($content['perms'])) {
            if ($content['perms']['perm_anon'] == 2) {
                $grp_access = 2;    // anon users
            } elseif ($content['perms']['perm_members'] == 2) {
                $grp_access = 13;   // loged-in users
            } elseif (!empty($content['perms']['group_id'])) {
                // limit to specific group
                $grp_access = (int)$content['perms']['group_id'];
            }
        }

        $sql = "REPLACE INTO `{$_TABLES['idxer_index']}` (
            `item_id`,
            `type`,
            `content`,
            `parent_id`,
            `parent_type`,
            `ts`,
            `grp_access`,
            `title`,
            `owner_id`,
            `author`)
            VALUES
            (
                '".DB_escapeString($content['item_id'])."',
                '".DB_escapeString($content['type'])."',
                '".DB_escapeString($indexContent)."',
                '".DB_escapeString($parent_id)."',
                '".DB_escapeString($parent_type)."',
                '".DB_escapeString($ts)."',
                '".DB_escapeString($grp_access)."',
                '".DB_escapeString($indexTitle)."',
                ".(int) $content['author'].",
                '".DB_escapeString($content['author_name'])."'
            )
            ";

        DB_query($sql);

        return true;
    }

    /**
    *   Remove a document from the index
    *   Deletes all records that match $type and $item_id
    *
    *   @param  string  $type       Type of document
    *   @param  mixed   $item_id    Document ID, single or array
    *   @return boolean     True on success, False on failure
    */
    public static function RemoveDoc($type, $item_id)
    {
        global $_TABLES;

        if ($item_id == '*') {
            return self::RemoveAll($type);
        } elseif (is_array($item_id)) {
            $item_id_str = implode("','", array_map('DB_escapeString', $item_id));
        } else {
            $item_id_str = DB_escapeString($item_id);
        }
        $item_id_str = "'" . $item_id_str . "'";
        $type = DB_escapeString($type);

        $sql = "DELETE FROM {$_TABLES['idxer_index']}
                WHERE type = '$type'
                AND item_id IN ($item_id_str)";

        DB_query($sql);

        if (DB_error()) {
            COM_errorLog("Indexer: Error removing $type, ID $item_id_str");
            return false;
        } else {
            return self::RemoveComments($type, $item_id_str, true);
        }
    }

    /**
    *   Remove all index records, normally of a specific type.
    *   Specify "all" as the type to truncate the table.
    *
    *   @param  string  $type   Type (article, staticpages, etc.)
    *   @return boolean     True on success, False on DB error
    */
    public static function RemoveAll($type = 'all')
    {
        global $_TABLES;
        if ($type === 'all') {
            DB_query("TRUNCATE {$_TABLES['idxer_index']}");
        } else {
            DB_delete($_TABLES['idxer_index'], 'type', $type);
            self::RemoveComments($type);
        }
        return DB_error() ? false : true;
    }

    /**
    *   Remove all comments for a specific parent type and optional id
    *   $item_id may be a single value or an array, leave as NULL to remove
    *   all comments for all content items of type $type.
    *
    *   @param  string  $type       Type of content (article, staticpage, etc.)
    *   @param  mixed   $item_id    ID of article, page, etc.
    *   @param  boolean $sanitized  True if the item_id is already SQL-safe
    *   @return boolean             True on success, False on failure
    */
    public static function RemoveComments($parent_type, $item_id=NULL, $sanitized=false)
    {
        global $_TABLES;

        if (! self::CommentsEnabled()) {
            return true;
        }

        $sql = "DELETE FROM {$_TABLES['idxer_index']}
                WHERE type = 'comment'
                AND parent_type = '".DB_escapeString($parent_type)."' ";

        if (is_array($item_id)) {
            $item_id = implode("','", array_map('DB_escapeString', $item_id));
            $sql .= "AND item_id IN ('$item_id')";
        } elseif ($item_id !== NULL) {
            if (!$sanitized) $item_id = DB_escapeString($item_id);
            $sql .= "AND item_id IN ('$item_id')";
        }
        if (DB_error()) {
            COM_errorLog("Indexer RemoveComments Error: $parent_type, ID $item_id");
            return false;
        } else {
            return true;
        }
    }

    /**
    *   Removes stop words from the string
    *
    *   @param  string  $str        original string
    *   @return string              string with stop words removed
    */
    private static function stripStopwords($str = "")
    {
        global $_INDEXER_CONF;

        $stopWords = explode(",",$_INDEXER_CONF['stopwords']);
        $stdStopWords = array("\&gt;","\&lt;","!","\?","\.","\,","\*");

        $stopWords = array_merge($stdStopWords,$stopWords);

        return preg_replace('/\b('.implode('|',$stopWords).')\b/','',$str);
    }


    /**
    *   Remove autotags before indexing (optional) and before showing results.
    *   This option is to prevent search hits on hidden fields that don't
    *   actually appear in the content.
    *
    *   @param  string  $content    Content to examine
    *   @return string      Content withoug autotags
    */
    private static function removeAutoTags($content)
    {
        $result = preg_replace("/\[\w+:[^\]]*\]/", '', $content);
        return $result;
    }

    /**
    *   Remove puncuation and other special characters from strings
    *
    *   @param  string  $str    String to modify, e.g. content page
    *   @return string          Modified version
    */
    protected static function _remove_punctuation($str)
    {
        if (!is_string($str)) return '';
        $str = strip_tags($str);
        $str = html_entity_decode($str);
        $str = preg_replace("/[^\p{L}\p{N}]/", ' ', $str);
        $str = preg_replace('/\s\s+/', ' ', $str);
        return trim($str);
    }

   /**
    *   Check if the internal comment engine is used.
    *   Searcher doesn't index or search external comments on disqus, etc. so
    *   comments can be considered "disabled" if anything but the internal
    *   engine is used.
    *
    *   @return boolean     True if internal comments are enabled, False if not
    */
    public static function CommentsEnabled()
    {
        global $_CONF;

        return (isset($_CONF['comment_engine']) && $_CONF['comment_engine'] == 'internal');
    }


// search related functions


    /**
    *   Perform the search
    *
    *   @return array           Array of results (item_id, url, excerpt, etc.)
    */
    public function doSearch()
    {
        global $_TABLES, $_INDEXER_CONF, $_USER, $LANG_INDEXER;

        $retval = '';
        $keywords = array();

        $sql = '';

        $start = ($this->page - 1) * $_INDEXER_CONF['per_page'];

        if ($this->searchAuthor != 0 || !empty($this->query)) {
            if ($this->searchAuthor != 0) {

                $typeWhere = '';
                if ($this->itemType != '') {
                    $typeWhere = " AND type = '".DB_escapeString($this->itemType)."' ";
                }

                $sql = "SELECT * FROM {$_TABLES['idxer_index']} WHERE owner_id =".(int) $this->searchAuthor . $typeWhere . " "
                        . SEC_buildAccessSQL('AND') . " ORDER BY ts DESC LIMIT " .  $start . ", ".$_INDEXER_CONF['per_page'];
                $sqlTotal = "SELECT COUNT(*) AS count FROM {$_TABLES['idxer_index']} WHERE owner_id =
                        ".(int) $this->searchAuthor.$typeWhere." " .
                        SEC_buildAccessSQL('AND');
            } else {
                $query = self::cleanString($this->query);

                $keywords   = $this->_filterSearchKeys($query);
                $escQuery   = DB_escapeString($query);

                $stemmer = new \Libs_WordStemmer();

                $titleSQL = array();
                $postSQL = array();
                if (count($keywords) > 1 && !empty($escQuery)) {
                    $titleSQL[] = "if (title LIKE '%".$escQuery."%',5,0)";
                    $postSQL[] = "if (content LIKE '%".$escQuery."%',6,0)";
                }

                switch ($this->searchType) {

                    case 'phrase' :
                        if (count($keywords) == 1 && !empty($escQuery)) {
                            $titleSQL[] = "if (title LIKE '%".$escQuery."%',3,0)";
                            $postSQL[] = "if (content LIKE '%".$escQuery."%',4,0)";
                        }
                        break;

                    case 'all' :
                        $titleSearch = '';
                        $postSearch  = '';
                        $first = 1;
                        foreach($keywords as $key) {
                            if (!empty($key)) {
                                if ($first != 1) {
                                    $titleSearch .= ' AND ';
                                    $postSearch .= ' AND ';
                                }
                                $first = 0;

                                $titleSearch .= " title like '%".DB_escapeString($key)."%' ";
                                $postSearch .= " content like '%".DB_escapeString($key)."%' ";
                            }
                        }
                        if ($titleSearch != '') {
                            $titleSQL[] = "if (".$titleSearch.",3,0)";
                        }
                        if ($postSearch != '') {
                            $postSQL[] = "if (".$postSearch.",4,0)";
                        }
                        break;

                    case 'any' :

                    default :
                        foreach($keywords as $key) {
                            if (!empty($key)) {
                                $titleSQL[] = "if (title LIKE '%".DB_escapeString($key)."%',3,0)";
                                $postSQL[] = "if (content LIKE '%".DB_escapeString($key)."%',4,0)";
                            }
                        }
                        // stemmer
                        foreach($keywords as $key) {
                            if (!empty($key)) {
                                $key = $stemmer->stem($key);
                                if (!in_array($key,$keywords)) {
                                    $titleSQL[] = "if (title LIKE '%".DB_escapeString($key)."%',2,0)";
                                    $postSQL[] = "if (content LIKE '%".DB_escapeString($key)."%',3,0)";
                                }
                            }
                        }
                        break;

                }

                if (empty($titleSQL)) {
                    $titleSQL[] = 0;
                }
                if (empty($postSQL)) {
                    $postSQL[] = 0;
                }

                $limitType = '';
                if (!empty($this->itemType)) {
                    $limitType = " AND type = '".DB_escapeString($this->itemType)."' ";
                }

                if ($this->searchDays > 0) {
                    $daysback = time() - ($this->searchDays * 86400);
                    $limitType .= ' AND ts > ' . (int)$daysback;
                }

                $sql = "SELECT type,item_id,
                            (
                                (
                                ".implode(" + ", $titleSQL)."
                                )+
                                (
                                ".implode(" + ", $postSQL)."
                                )
                            ) as relevance
                            FROM {$_TABLES['idxer_index']}
                            " . SEC_buildAccessSQL('WHERE (') . "
                            ". $limitType.")
                            HAVING relevance > 0
                            ORDER BY relevance DESC, ts DESC
                            LIMIT " .  $start . ", ".$_INDEXER_CONF['per_page'];

                $sqlTotal = "SELECT COUNT(*) FROM (
                                SELECT *,
                                (
                                    (
                                    ".implode(" + ", $titleSQL)."
                                    )+
                                    (
                                    ".implode(" + ", $postSQL)."
                                    )
                                ) as relevance
                                FROM {$_TABLES['idxer_index']}
                                " . SEC_buildAccessSQL('WHERE (') . "
                                ".$limitType.")
                                HAVING relevance > 0
                            ) AS count";

            }

            // Start search timer
            $searchtimer = new \timerobject();
            $searchtimer->setPercision(4);
            $searchtimer->startTimer();

            $searchKey = md5($sql) . '_'.$start;
            $c = \glFusion\Cache::getInstance();
            if ($c->has($searchKey)) {
                $cacheResults = $c->get($searchKey);
                $this->totalResults = $cacheResults['totalresults'];
                $this->results      = $cacheResults['results'];
            } else {
                $cacheResults = array();
                // get total count of results
                $totalResults = DB_query($sqlTotal);
                $totalRecs = DB_fetchArray($totalResults);
                $this->totalResults = $totalRecs[0];

                // perform actual query
                $result = DB_query($sql);
                $this->results = array();

                while (($row = DB_fetchArray($result)) != NULL) {
                    $contentInfo = PLG_getItemInfo($row['type'],$row['item_id'],'id,date,url,title,searchidx,author,author_name,hits,perms,status');
                    if ($contentInfo !== NULL && is_array($contentInfo) && count($contentInfo) > 0 ) {
                        if (!isset($row['relevance'])) $row['relevance'] = 0;
                        if (!isset($contentInfo['hits'])) $contentInfo['hits'] = 0;
                        if (!isset($contentInfo['title'])) $contentInfo['title'] = 'Not defined';
                        $this->results[] = array(
                            'type' => $row['type'],
                            'disp_type' => ucfirst($row['type']),
                            'item_id' => $row['item_id'],
                            'title'  => $contentInfo['title'],
                            'relevance' => $row['relevance'],
                            'excerpt' => $this->_shortenText('', $contentInfo['searchidx'], $_INDEXER_CONF['excerpt_length']),
                            'author' => $contentInfo['author_name'],
                            'hits' => $contentInfo['hits'],
                            'uid' => $contentInfo['author'],
                            'url' => $contentInfo['url'],
                            'ts' => $contentInfo['date'],
                        );
                    }
                }
                $cacheResults['totalresults'] = $this->totalResults;
                $cacheResults['results'] = $this->results;
                $c->set($searchKey,$cacheResults,array('searchcache'),3600);
            }
            // only update counters for actual searches
            if ($this->searchAuthor == 0) {
                $this->updateCounter();
            }
            // Searches are done, stop timer
            $this->searchTime = $searchtimer->stopTimer();
        }

        // if not search author and searchtype = 'all' - try one more time with 'any'
        if (count($this->results) < 1 && $this->searchType == 'all' && $this->searchAuthor == 0) {
            $this->searchType = 'any';
            $this->alteredSearch = 1;
            return $this->doSearch();
        }

        if ($this->searchAuthor == 0) {
            $retval .= $this->showForm();
        } else {
            $retval .= '<h2>'.$LANG_INDEXER['all_posts_by'] . COM_getDisplayName($this->searchAuthor). '</h2>';
        }
        $retval .= $this->Display($sql);
        return $retval;
    }

    /**
    *   Update the search term counter table.
    */
    private function updateCounter()
    {
        global $_TABLES;

        // To count unique queries, only count if on the first page
        if (!isset($_GET['nc']) && $this->page == 1) {
            $results = $this->totalResults;
            $query = DB_escapeString($this->query);
            $sql = "INSERT INTO {$_TABLES['idxer_stats']}
                    (term, hits, results) VALUES ('".DB_escapeString($this->query)."', 1, $results)
                    ON DUPLICATE KEY UPDATE
                        hits = hits + 1,
                        results = $results";

            DB_query($sql);
        }
    }

    /**
    *   Display the search results
    *
    *   @return string      Results display
    */
    public function Display($sql = '')
    {
        global $_CONF, $_INDEXER_CONF, $LANG_ADMIN, $LANG09,$LANG05, $LANG_INDEXER;

        $retval = '';

        // get all template fields.
        $T = new \Template($_CONF['path'] . 'plugins/indexer/templates');
        $T->set_file ('page','searchresults.thtml');

        $T->set_var('query', urlencode($this->query));

        if (defined('DVLP_DEBUG')) {
            $T->set_var('sql',$sql);
        }

        if ($this->totalResults == 0) {
            if ($this->query == '' && $this->searchAuthor == 0) {
                $T->set_var('message', $LANG_INDEXER['new_search']);
            } else {
                $T->set_var('message', $LANG_ADMIN['no_results']);
            }
            $T->parse('output', 'page');
            return $T->finish($T->get_var('output'));
        }

        $T->set_var(array(
            'lang_on'   => $LANG_INDEXER['on'],
            'lang_by'   => $LANG_INDEXER['by'],
            'lang_hits' => $LANG_INDEXER['hits'],
        ));

        $dt = new \Date('now', $_CONF['timezone'],true);

        $T->set_block('page','searchresults','sr');

        $resultCounter = ($this->page * $_INDEXER_CONF['per_page']) - ($_INDEXER_CONF['per_page'] - 1);
        foreach ($this->results as $row) {
            $dt->setTimestamp($row['ts']);

            if ($_INDEXER_CONF['replace_stock_search']) {
                $type_url = $_CONF['site_url'].'/search.php?mode=search&amp;q=' . urlencode($this->query).'&amp;type='.urlencode($row['type']);
            } else {
                $type_url = $_CONF['site_url'].'/indexer/index.php?q=' . urlencode($this->query).'&amp;type='.urlencode($row['type']);
            }

            $T->set_var(array(
                'title'     => $row['title'],
                'excerpt'   => $this->_shortenText('',$row['excerpt'],50),
                'author'    => $row['author'],
                'uid'       => $row['uid'],
                'hits'      => COM_numberFormat($row['hits']),
                'item_url'  => $row['url'],
                'date'      => $row['ts'] ? $dt->format($_CONF['daytime'],true) : NULL,
                'src'       => $row['disp_type'],
                'type'      => $row['type'],
                'result_counter' => $resultCounter,
                'src_link'  => $this->searchAuthor == 0 ? $type_url : false,
            ));

            if (defined('DVLP_DEBUG') || SEC_inGroup('Root')) {
                $T->set_var(array('relevance' => $row['relevance']));
            } else {
                $T->unset_var('relevance');
            }
            $T->parse('sr','searchresults',true);

            $resultCounter++;
        }

        $num_pages = ceil($this->totalResults / $_INDEXER_CONF['per_page']);

        if ($_INDEXER_CONF['replace_stock_search']) {
            $base_url = $_CONF['site_url'].'/search.php?mode=search&amp;q=' . urlencode($this->query);
        } else {
            $base_url = $_CONF['site_url'].'/indexer/index.php?q=' . urlencode($this->query);
        }

        if ($this->itemType != "") {
            $base_url .= '&amp;type='.urlencode($this->itemType);
        }
        if ($this->searchAuthor > 0) {
            $base_url .= '&amp;author='.(int) $this->searchAuthor;
        }
        if ($this->searchDays != 0) {
            $base_url .= '&amp;st='.(int) $this->searchDays;
        }
        if ($this->searchType != 'any') {
            $base_url .= '&amp;keyType='.urlencode($this->searchType);
        }

        $pagination = COM_printPageNavigation($base_url, $this->page, $num_pages);
        $T->set_var('google_paging', $pagination);

        if ($this->totalResults > 0) {
            $first = (($this->page - 1) * $_INDEXER_CONF['per_page']) + 1;
            $last = min($first + $_INDEXER_CONF['per_page'] - 1, $this->totalResults);
            $T->set_var('showing_results',
                sprintf($LANG_INDEXER['showing_results'], COM_numberFormat($first), COM_numberFormat($last), COM_numberFormat($this->totalResults)));

            $T->set_var('search_time',$this->searchTime);

        }

        $T->parse('output', 'page');
        $retval .= $T->finish($T->get_var('output'));
        return $retval;
    }

    /**
    *   Shows search form.
    *   If the query_len is >=0 but less than min_word_len, then also show
    *   an error message.
    *
    *   @param  integer $query_len  Length of current query
    *   @return string  HTML output for form
    */
    public function showForm($query_len = -1)
    {
        global $_CONF, $_INDEXER_CONF, $LANG09, $LANG_INDEXER;

        // Verify current user my use the search form
        if (! self::SearchAllowed()) {
            return self::getAccessDeniedMessage();
        }

        $T = new \Template($_CONF['path'].'plugins/indexer/templates');
        $T->set_file('searchform', 'searchform.thtml');

        if ($this->alteredSearch == 1) {
            $T->set_var('altered_search_message', $LANG_INDEXER['altered_search']);
        }

        $T->set_var(array(
            'lang_search'           => $LANG_INDEXER['search'],
            'lang_search_site'      => sprintf($LANG09[1],$_CONF['site_name']),
            'query'                 => htmlspecialchars($this->query),
        ));

        $T->set_var(array(
            'dt_sel_' . $this->searchDays => 'selected="selected"',
            'key_' . $this->searchType .'_selected' => ' selected="selected" ',
            'show_adv' => isset($_GET['adv']) && $_GET['adv'] == 1 ? 1 : 0,
        ));

        if ($_INDEXER_CONF['replace_stock_search']) {
            $T->set_var('form_action',$_CONF['site_url'].'/search.php');
        } else {
            $T->set_var('form_action',$_CONF['site_url'].'/indexer/index.php');
        }

        $plugintypes = array(
            'article' => $LANG09[6],
        );
        if (self::CommentsEnabled()) {
            $plugintypes['comment'] = $LANG09[7];
        }
        $plugintypes = array_merge($plugintypes, PLG_getSearchTypes());
        asort($plugintypes);
        $plugintypes = array_merge(array('all'=>$LANG09[4]),$plugintypes);
        $T->set_block('searchform', 'PluginTypes', 'PluginBlock');
        foreach ($plugintypes as $key => $val) {
            $T->set_var(array(
                'pi_name'   => $key,
                'pi_text'   => $val,
                'selected'  => $this->itemType == $key ? 'selected="selected"' : '',
            ));
            $T->parse('PluginBlock', 'PluginTypes', true);
        }

        $T->parse('output', 'searchform');
        return $T->finish($T->get_var('output'));
    }


    /**
    *   Determines if user is allowed to perform a search
    *
    *   glFusion has a number of settings that may prevent
    *   the access anonymous users have to the search engine.
    *   This performs those checks
    *
    *   @return boolean True if search is allowed, otherwise false
    */
    public static function SearchAllowed()
    {
        global $_CONF;

        // The checks aren't expensive functions, but this gets called twice
        // so save the result.
        static $isAllowed = NULL;

        if ($isAllowed === NULL) {
            if (COM_isAnonUser() &&
                ($_CONF['loginrequired'] || $_CONF['searchloginrequired'])) {
                $isAllowed = false;
            } else {
                $isAllowed = true;
            }
        }
        return $isAllowed;
    }


    /**
    *   Remove unnecessary words from the search term and return them as an array
    *   Assumes a valid UTF-8 string
    *
    *   @param  string  $query      unprocessed query string
    *   @return array               array of valid search terms
    */
    private function _filterSearchKeys($query)
    {
        $query = preg_replace('/\s+/u', ' ',$query);
        $query = strip_tags($query);
        $query = self::stripStopwords($query);
        $query = str_replace("&nbsp;"," ",$query);
        $query = str_replace (array("\r\n", "\n", "\r"), ' ', $query);
        $query = preg_replace("#[[:punct:]]#", " ", $query);
        $query = str_replace("  "," ",$query);
        $query = trim(rtrim($query));

        $words = array();
        $c = 0;
        foreach (explode(" ", $query) as $key) {
            $words[] = $key;
            if ($c >= 4) {
                break;
            }
            $c++;
        }
        return $words;
    }

    /**
    *   Limit search query in number of characters
    *
    *   @param  string  $query      unprocessed query string
    *   @param  int     $limit      Number of characters allowed
    *   @return string              processed string
    */
    private function _limitChars($query, $limit = 200)
    {
        return substr($query, 0,$limit);
    }


    /**
    *   Truncates long text based on word count.
    *   UTF-8 safe
    *
    *   @param string $keyword
    *   @param string $text
    *   @param int    $num_words
    *   @return string
    */
    private function _shortenText($keyword, $text, $num_words = 7)
    {

        $text = strip_tags($text);
        $text = str_replace("&nbsp;"," ",$text);
        $text = str_replace (array("\r\n", "\n", "\r"), ' ', $text);
        $text = str_replace("  "," ",$text);
        $text = trim(rtrim($text));
        $text = $this->removeAutoTags($text);

        // parse some general bbcode / auto tags
        $bbcode = array(
            "/\[b\](.*?)\[\/b\]/is" => "$1",
            "/\[u\](.*?)\[\/u\]/is" => "$1",
            "/\[i\](.*?)\[\/i\]/is" => "$1",
            "/\[quote\](.*?)\[\/quote\]/is" => "$1",
            "/\[code\](.*?)\[\/code\]/is" => " $1 ",
            "/\[p\](.*?)\[\/p\]/is" => " $1 ",
            "/\[url\=(.*?)\](.*?)\[\/url\]/is" => "$2",
            "/\[wiki:(.*?) (.*?)[\]]/is" => "$2"
        );
        $text = @preg_replace(array_keys($bbcode), array_values($bbcode), $text);

        $words = explode(' ', $text);
        $word_count = count($words);
        if ($word_count <= $num_words) {
            return COM_highlightQuery($text, $keyword, 'b');
        }

        $rt = '';
        $pos = stripos($text, $keyword);
        if ($pos !== false) {
            $pos_space = utf8_strpos($text, ' ', $pos);
            if (empty($pos_space)) {
                // Keyword at the end of text
                $key = $word_count - 1;
                $start = 0 - $num_words;
                $end = 0;
                $rt = '<b>...</b> ';
            } else {
                $str = utf8_substr($text, $pos, $pos_space - $pos);
                $m = (int) (($num_words - 1) / 2);
                $key = $this->_arraySearch($keyword, $words);
                if ($key === false) {
                    // Keyword(s) not found - show start of text
                    $key = 0;
                    $start = 0;
                    $end = $num_words - 1;
                } elseif ($key <= $m) {
                    // Keyword at the start of text
                    $start = 0 - $key;
                    $end = $num_words - 1;
                    $end = ($key + $m <= $word_count - 1)
                         ? $key : $word_count - $m - 1;
                    $abs_length = abs($start) + abs($end) + 1;
                    if ($abs_length < $num_words) {
                        $end += ($num_words - $abs_length);
                    }
                } else {
                    // Keyword in the middle of text
                    $start = 0 - $m;
                    $end = ($key + $m <= $word_count - 1)
                         ? $m : $word_count - $key - 1;
                    $abs_length = abs($start) + abs($end) + 1;
                    if ($abs_length < $num_words) {
                        $start -= ($num_words - $abs_length);
                    }
                    $rt = '<b>...</b> ';
                }
            }
        } else {
            $key = 0;
            $start = 0;
            $end = $num_words - 1;
        }

        for ($i = $start; $i <= $end; $i++) {
            $rt .= $words[$key + $i] . ' ';
        }
        if ($key + $i != $word_count) {
            $rt .= ' <b>...</b>';
        }
        return COM_highlightQuery($rt, $keyword, 'b');
    }


    private function _arraySearch($needle, $haystack)
    {
        $keywords = explode(' ', $needle);
        $num_keywords = count($keywords);

        foreach ($haystack as $key => $value) {
            if (stripos($value, $keywords[0]) !== false) {
                if ($num_keywords == 1) {
                    return $key;
                } else {
                    $matched_all = true;
                    for ($i = 1; $i < $num_keywords; $i++) {
                        if (stripos($haystack[$key + $i], $keywords[$i]) === false) {
                            $matched_all = false;
                            break;
                        }
                    }
                    if ($matched_all) {
                        return $key;
                    }
                }
            }
        }

        return false;
    }

    private static function cleanString($str)
    {
        $str = utf8_strtolower($str);
        $str = strip_tags($str);
        $str = self::stripStopwords($str);
        $str = str_replace("&nbsp;"," ",$str);
        $str = str_replace (array("\r\n", "\n", "\r"), ' ', $str);
        $str = preg_replace("#[[:punct:]]#u", " ", $str);
        $str = preg_replace('/\s+/u', ' ',$str);
        $str = trim(rtrim($str));
        return $str;
    }
}
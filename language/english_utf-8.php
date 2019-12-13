<?php
/**
* glFusion CMS
*
* Indexer - Indexer Plugin for glFusion
*
* Language File - UTF-8
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

$LANG_INDEXER = array(
    'menu_label'    => 'Indexer',
    'all'           => 'All',
    'all_posts_by'  => 'All Posts By ',
    'by'            => 'by',
    'hits'          => 'Hits',
    'new_search'    => 'Please enter the search criteria above and select Search',
    'on'            => 'on',
    'one_day'       => '1 day',
    'one_month'     => '1 month',
    'one_year'      => '1 year',
    'search'        => 'Search',
    'seven_days'    => '7 days',
    'showing_results' => 'Showing %s - %s of %s Results',
    'six_months'    => '6 months',
    'three_months'  => '3 months',
    'two_weeks'     => '2 weeks',
    'two_years'     => '2 years',
    'seconds'       => 'secs',
    'search_placeholder' => 'Enter Your Search Request',
    'altered_search' => 'Using <b>all of these words</b> did not return any data so the search was modified to <b>any of these words</b>',
);

$LANG_INDEXER_ADM = array(
    'cancel'            => 'Cancel',
    'chk_unchk_all'     => 'Check/Uncheck All',
    'clear_counters' => 'Clear Counters',
    'content_type'      => 'Content Type',
    'current_progress'  => 'Current Progress',
    'empty_table'       => 'Remove all data from Indexer table prior to indexing',
    'error_getcontenttypes' => 'Unable to retrieve content types from glFusion',
    'error_heading'     => 'Errors',
    'hlp_counters'      => 'Here are the search queries made by site visitors, along with the number of times each query was made.',
    'hlp_gen_all'       => 'Re-generate all indexes for the selected content types. Use this option after installing the plugin, or after changing certain key configuration items such as the minimum word length or changing the stemmer.',
    'hlp_reindex'       => 'Re-indexing content will remove all existing search items for the content type and re-scan the content to rebuild the search word index. This can take a significant amount of time on large volume content types such as Forums.',
    'index_instructions'=> 'This will scan the selected content types and rebuild the Indexer index',
    'index_status'      => 'Indexing Status',
    'indexer_admin'     => 'Indexer Admin',
    'indexing'          => 'Indexing',
    'no_errors'         => 'No Errors',
    'overall_progress'  => 'Overall Progress',
    'queries'           => 'Queries',
    'reindex_button'    => 'Re-index',
    'reindex_title'     => 'Re-index Content',
    'remove_content_1'  => 'Removing existing index entries for ',
    'remove_content_2'  => ' - This can take several minutes....',
    'remove_fail'       => 'Failed to remove existing index entries.',
    'results'           => 'Results',
    'retrieve_content_list' => 'Retrieving content list for ',
    'retrieve_content_types'=> 'Retrieving Content Types',
    'search_terms'  => 'Search Terms',
    'submit'            => 'Submit',
    'success'           => 'Success',
);

$LANG_configsections['indexer'] = array(
    'label' => 'Indexer',
    'title' => 'Indexer Plugin Configuration',
);

$LANG_confignames['indexer'] = array(
    'excerpt_length'        => 'Excerpt Size',
    'per_page'              => 'Results Per Page',
    'replace_stock_search'  => 'Replace stock glFusion search?',
    'search_type'           => 'Default Search Type',
    'stopwords'             => 'Stopwords',
    'summarize_discussions' => 'Index Forum Posts by Parent',
);

$LANG_configsubgroups['indexer'] = array(
    'sg_main' => 'Settings',
);

$LANG_fs['indexer'] = array(
    'fs_main' => 'Settings',
);

$LANG_configSelect['indexer'] = array(
    0  => array(1 => 'True', 0 => 'False'),
    1  => array('all' => 'All of the words', 'any' => 'Any of the words', 'phrase' => 'Exact Phrase'),
);

// glFusion v1.7.x support
$LANG_configselects['indexer'] = array(
    0  => array('True' => 1, 'False' => 0 ),
    1  => array('All of the words' => 'all', 'Any of the words' => 'any', 'Exact Phrase' => 'phrase'),
);

?>

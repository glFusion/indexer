<?php
/**
* glFusion CMS
*
* Indexer - Alternative Search for glFusion
*
* Indexer Database Schema
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

$_SQL['idxer_index'] = "CREATE TABLE `{$_TABLES['idxer_index']}` (
  `item_id` varchar(128) NOT NULL DEFAULT '',
  `type` varchar(20) NOT NULL DEFAULT '',
  `content` MEDIUMTEXT,
  `parent_id` varchar(128) NOT NULL DEFAULT '',
  `parent_type` varchar(50) NOT NULL DEFAULT '',
  `ts` int(11) unsigned NOT NULL DEFAULT '0',
  `grp_access` mediumint(8) NOT NULL DEFAULT '2',
  `title` varchar(200) NOT NULL DEFAULT '',
  `owner_id` mediumint(9) NOT NULL DEFAULT '0',
  `author` varchar(40) NOT NULL DEFAULT '',
  PRIMARY KEY (`type`, `item_id`),
  INDEX `type` (`type`),
  INDEX `item_date` (`ts`),
  INDEX `author` (`author`)
) ENGINE=MyISAM";

$_SQL['idxer_stats'] = "CREATE TABLE `{$_TABLES['idxer_stats']}` (
  `term` varchar(200) NOT NULL,
  `hits` int(11) unsigned NOT NULL DEFAULT '1',
  `results` int(11) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`term`),
  KEY `hits` (`hits`)
) ENGINE=MyISAM";

?>

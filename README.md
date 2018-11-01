# Indexer Plugin for glFusion

## Overview

Indexer is an alternative search plugin for glFusion v1.7.6 or newer. Indexer consolidates all
searchable content into a single collection and performs searches against the consolidated
collection, providing results based on relevance. Relevance is determined using the following method:

  * Does the full search string appear in the content
  * Do any of the search terms appear in the content
  * Search terms in the actual content (i.e.; body or post) are considered more relevant than appearing in the title
  * Number of times the term(s) appear increase the relevance score
  * Stemmer support - search for 'watermarking' and Indexer will also search for 'watermark'

This approach does a very good job of returning relevant information, regardless of
content type.

For glFusion v2.0 or newer, search results will be cached for a short period of time to both
improve performance and decrease server load as users navigate between the search results
and the content.

Indexer **does not** support all the options available in the standard glFusion search,
such as limiting by author. The primary goal of Indexer is to return relevant information,
regardless of content type, quickly and efficiently.

## Preparation for Use

Once the plugin has been installed, you will need to perform the initial setup tasks
to get Indexer fully operational.

### Configuration Settings

Navigate to the Online Configuration to ensure the Indexer configuration meets your needs.
Specifically, validate the Index Forum Posts by Parent setting. This controls how the forum
plugin (if used) will be indexed.

### Initial Indexing

Before the Indexer plugin can be used, the initial index must be created.
After this is done any content changes will automatically update the index.

1. Go through the configuration options below and set them according to your preferences.
1. Create the initial index
    1. Navigate to Command & Control -> Indexer
    1. Select **Reindex Content** from the Indexer navigation menu
    1. Select the content types you would like to have indexed and used to search your site
    1. Select the "Reindex" button. This will take some time but uses AJAX to prevent browser timeouts.

## Searching
Visit `http(s)://www.yoursitehere.com/indexer/index.php` and enter a search phrase.

There are 3 options available to fine-tune the search results.

### Filter By Type
You can select to just search for a specific content type.

### Filter By Date
You can provide a date range to limit the search results

### Search Type

**Any of the words** will search for any of the search terms. For example,
if you entered 'FAQ plugin' as the search term, Indexer will search for the following:

* Exact match on FAQ plugin
* Match on content that contains 'FAQ'
* Match on content that contains 'Plugin'

**All of the words** will search for content items that contain all of the search terms.
For example, if you entered 'FAQ Plugin' as the search term, Indexer willl search for the following:

* Exact match on 'FAQ Plugin'
* Match on content that contains both FAQ and Plugin

**Exact Phrase** will search for content that contains the exact phrase as entered in the search terms.
For example, if you entered 'FAQ Plugin' as the search term, Indexer will search for the follwoing:

* Exact match on 'FAQ Plugin'

Once the plugin is confirmed to be working correctly, re-visit the global
configuration and set "Replace Stock glFusion Search" to "Yes".
At this point the default search box in the header as well as the standard `/search.php`
URL will utilize the Indexer plugin.

## Configuration

The Indexer plugin supports the following configuration options.

#### Stopwords
A comma separated list of common words to remove from the search index and queries. Words such as THE, IS, A, etc.

#### Default Search Types
Sets the default search type of All, Any or Phrase.

#### Results Per Page
Number of search results to show per page. For improved performance, keep this number reasonable around 20 or 25.

#### Excerpt Length
Number of words to include in the search results brief description.

#### Index Forum Posts by Parent
If enabled, Forum discussions will consolidate all posts by parent - resulting in only 1 search result per forum topic.

#### Replace Stock glFusion Search?

If selected then the Indexer plugin will be used by glFusion's search.php file,
replacing the stock search engine. If this is set to "No", then the normal
glFusion search will be used but the Search plugin can still be accessed at
`http(s)://www.yoursitehere.com/indexer/index.php`.

Default: False

### License

This program is free software; you can redistribute it and/or modify it under
the terms of the GNU General Public License as published by the Free Software
Foundation; either version 2 of the License, or (at your option) any later
version.

This plugin is based on the Searcher Plugin developed by Lee Garner - lee AT leegarner DOT com

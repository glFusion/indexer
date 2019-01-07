# Indexer Plugin ChangeLog

## v1.0.1
  - Fixed translation support for glFusion v2

## v1.0.0
  - Add option for default search mode (all, any, phrase)
  - Added search time to show the number of seconds to perform search
  - Modified the stop word list - added several words
  - Tweaks to look and feel

## v0.0.9
  - Fixed hard coded language tags
  - Removed glFusion v2.0 requirement
  - Add option to truncate index table during re-indexing

## v0.0.8
  - If replace default search - use /search.php for URLs
  - Modified content index field to mediumint to all up to 64mb in size
  - Ignore SQL Errors on replace in the event the data is too big
  - Fixed missing escape on SQL

## v0.0.7
  - Minify JS
  - General costmetic code cleanup
  - docs update

## v0.0.6
  - Added stemming feature

## v0.0.5
  - Added summarize discussions option to configuration to allow grouping forum posts by topic
  - Tweaked search query to return fewer columns

## v0.0.4
  - Additional configuration options for per page / excerpt size

## v0.0.3
  - make stopwords configurable

## v0.0.2
  - updated templates / look and feel

## v0.0.1
  - Initial Development
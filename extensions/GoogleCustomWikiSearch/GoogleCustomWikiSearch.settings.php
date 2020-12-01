<?php
###### Default settings for variables - change in LocalSettings #####
/**
 * Replace standard search?
 */
$wgGoogleCustomWikiSearchReplaceSearch = false;

/**
 * Append to standard search results? Only makes sense if $wgGoogleCustomWikiSearchReplaceSearch
 * is false.
 */
$wgGoogleCustomWikiSearchAppendToSearch = false;

/**
 * Google Custom Search ID - requires account with Google.
 * Note that if this is left blank (not set in LocalSettings), the result will be a site search.
 */
$wgGoogleCustomWikiSearchId = '';

/**
 * The custom search options may leave out customSearchControl.draw, though options will only
 * display then if the variable is named "options".
 * If this is set, $wgGoogleCustomWikiSearchId is ignored
 */
$wgGoogleCustomWikiSearchOptions = '';

/**
 * This may be overwitten by Control Panel settings in Google script Version 2.
 * Possible values are: 'DEFAULT', 'BUBBLEGUM', 'ESPRESSO', 'GREENSKY', 'MINIMALIST', 'SHINY'
 */
$wgGoogleCustomWikiSearchTheme = 'V2_DEFAULT';

/**
 * What version of Google's script we should use
 */
$wgGoogleCustomWikiSearchCodeVersion = 2;
#####################################################################

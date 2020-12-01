<?php
/**
 * WikiArticleFeeds.php - a MediaWiki extension for converting regular pages into feeds.
 * @author Jim R. Wilson, Thomas Gries
 * @maintainer Thomas Gries
 *
 * @version 0.74
 * @copyright Copyright © 2007 Jim R. Wilson
 * @copyright Copyright © 2012 Thomas Gries
 * @license The MIT License - http://www.opensource.org/licenses/mit-license.php
 *
 * Description
 *
 * This is a MediaWiki (https://www.mediawiki.org/) extension which adds support
 * for publishing RSS or Atom feeds generated from standard wiki articles.
 *
 * Requirements
 *
 * MediaWiki 1.25 or higher
 * PHP 5.x or higher
 *
 * Usage
 *
 * Once installed, you may utilize WikiArticleFeeds by invoking the 'feed' action of an article:
 * $wgScript?title=Some_Article&action=feed
 *
 * You may optionally supply a feed type.
 * Acceptable values include 'rss' and 'atom'.
 * If no feed type is specified, the default is 'atom'.
 *
 * Example:
 * $wgScript?title=Some_Article&action=feed&feed=atom
 *
 * Creating a Feed
 *
 * To delimit a section of an article as containing feed items, use the <startFeed />
 * and <endFeed /> tags respectively. These tags are merely flags, and any attributes
 * specified, or content inside the tags themselves will be ignored.
 *
 * Tagging a Feed item
 *
 * To tag a feed item, insert either the <itemTags> tag, or the a call to the {{#itemTags}} parser
 * function somewhere between the opening header of the item (== Item Title ==) and the header of
 * the next item. For example, to mark an item about dogs and cats, you could do any of the following:
 *
 * <itemTags>dogs, cats</itemTags>
 * {{#itemTags:dogs, cats}}
 * {{#itemTags:dogs|cats}}
 *
 * Versions
 *
 * 0.74    Slight code cleanup
 * 0.73    Changed the LinkEnd hook handler to generate valid HTML by prefixing
 *         the userpage-link and usertalkpage-link attributes with "data-"
 * 0.71    removed $wgWikiArticleFeedsTrackingCategory parameter for tracking category
 * 0.703   adds the feed icon to the bottom of the toolbox in Monobook or like-minded skins.
 * 0.701   version string constant renamed to make it wiki-unique
 * 0.700   rewritten into a four-file version with class
 *         auto-discovery rss feedlinks come with the page title in it
 * 0.672   changed certain !empty() to isset()
 * 0.671   added ob_start() to prevent headers-already sent problem
 *         added check for undefined variable
 *         removed host wiki $wgSitename from the RSS feed title.
 *         feed title has now the programmatic form 'Pagename - Feed' which looks nice."
 *         recode feed publication timestamps in UTC
 *         refactored the source code comment section layout
 *         introduced a new version numbering format
 * 0.6.6   Updated version requirement to MediaWiki 1.12 and up.
 * 0.6.5   Simplified many regular expression to get it working on MW 1.16
 * 0.6.4   Small fix for MW 1.14 in which section header anchors changed format.
 *         First version to be checked into wikimedia SVN.
 * 0.6.3   Wrapped extension points for versions less than 1.7 (old version compatibility)
 * 0.6.2   Added support for alternate signatures (when users are not in the User namespace)
 *         Tied purge of xml feeds to purge of page - helps with consumers of semantic and DPL
 *         Thanks to Charlie Huggard of charlie.huggardlee.net for these contributions)
 * 0.6.1   Fixed stale feed problem by expiring the feed cache when any of an article's transcluded pages change.
 * 0.6	   Added support for "tagging" feed items by way of <itemTags> or {{itemTags}}
 *         Added support for filtering generated feed based on item tags.
 *         Added global ($wgWikiArticleFeedsSkipCache) used for skipping object-cache while debugging.
 *         Fixed namespace restriction - now works for namespaces other than NS_MAIN
 * 0.5     Now supports offloading to FeedBurner (http://feedburner.com/) via <feedBurner> tag.
 *         Capitalized RSS and Atom links in the toolbox.
 * 0.4     Added wgForceArticleFeedSectionLinks to override default link behavior (set in LocalSettings).
 *         Feed generator now follows Article redirects.
 * 0.3     Added Version Notes.
 *         Fixed relative-links bug (all links in item descriptions are now fully qualified).
 *         Fixed date-overwrite bug (previously, items with the exact same timestamp would be ignored).
 *         Improved W3C validation. Feeds validate, but there are still warnings.
 * 0.2     Renamed from WikiFeeds.php to WikiArticleFeeds.php
 *         Corrected credits and copyright info.
 *         Numerous minor fixes and tweaks.
 *         Expanded support for versions 1.6.x, 1.8.x and 1.9.x.
 *         Improved return values from hooking functions (plays better with other extensions).
 * 0.1     Initial release.
 *
 * Copyright © 2007 Jim R. Wilson
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights to
 * use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of
 * the Software, and to permit persons to whom the Software is furnished to do
 * so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
 * OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
 * HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
 * WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 * FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
 * OTHER DEALINGS IN THE SOFTWARE.
 *
 */

# Create global instance
$wgAutoloadClasses['WikiArticleFeeds'] = __DIR__ . '/WikiArticleFeeds_body.php';

# Credits
$wgExtensionCredits['specialpage'][] = [
	'path' => __FILE__,
	'name' => 'WikiArticleFeeds',
	'license-name' => 'MIT',
	'author' => [ 'Jim Wilson', 'Thomas Gries' ],
	'url' => '//www.mediawiki.org/wiki/Extension:WikiArticleFeeds',
	'descriptionmsg' => 'wikiarticlefeeds-desc',
	'version' => WikiArticleFeeds::VERSION,
];

$wgMessagesDirs['WikiArticleFeeds'] = __DIR__ . '/i18n';
$wgExtensionMessagesFiles['WikiArticleFeedsMagic'] = __DIR__ . '/WikiArticleFeeds.i18n.magic.php';

# Tracking category listed on Special:TrackingCategories
$wgTrackingCategories[] = 'wikiarticlefeeds-tracking-category';

# Attach Hooks
$wgHooks['ParserFirstCallInit'][] = 'WikiArticleFeeds::setup';
$wgHooks['SkinTemplateToolboxEnd'][] = 'WikiArticleFeeds::addToolboxLinks';
$wgHooks['OutputPageBeforeHTML'][] = 'WikiArticleFeeds::addWikiFeedHeaders';
$wgHooks['LinkEnd'][] = 'WikiArticleFeeds::addSignatureMarker';
$wgHooks['UnknownAction'][] = 'WikiArticleFeeds::onUnknownAction';
$wgHooks['ArticlePurge'][] = 'WikiArticleFeeds::onArticlePurge';

$wgWikiArticleFeeds = new WikiArticleFeeds();
$wgHooks['ParserAfterTidy'][] = [ $wgWikiArticleFeeds, 'itemTagsPlaceholderCorrections' ];

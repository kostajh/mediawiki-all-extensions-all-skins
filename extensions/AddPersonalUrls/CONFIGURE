<!--
     page taken from
     https://www.mediawiki.org/wiki/Extension:AddPersonalUrls/Configuration
-->
== Configuration ==

=== Table of URLs ===

The additional URLs are defined in the global variable <code>$wgAddPersonalUrlsTable</code>, which you may customize in your <code>LocalSettings.php</code>. A default table is used if you do not provide a custom one. The table is an array where each entry assigns an ''ID'' to a (partial) ''URL'' as follows:

* URLs are inserted after the link to the user's home page, in the order in which they appear in the array.
* The ''ID'' is the name of a [[#Messages|system message]] used to display the link. AddPersonalUrls is shipped with default messages (in the languages de, en, fr and it) for the links in the default <code>$wgAddPersonalUrlsTable</code>.
* The ''URL'' is either a page name (without brackets), optionally followed by a '?' and a query string, or an external link. A ''URL'' is recognized an external link iff it contains the string '://'. I'm sure you'll find a better way, so please let me know your ideas.
* The string <code>$username</code> will be replaced with the current username. Note that in <code>LocalSettings.php</code>, you need to write the ''URL'' within single quotes to avoid that PHP already interprets <code>$username</code> while processing <code>LocalSettings.php</code>, in which case it would evaluate to an empty string.

The default content of <code>$wgAddPersonalUrlsTable</code> is currently as follows:

<syntaxhighlight lang="php">
$wgAddPersonalUrlsTable = array(
	'addpersonalurls-userpages'
	=> 'Special:PrefixIndex?prefix=$username&namespace=2',
	'addpersonalurls-home' => 'Special:Mypage/Home',
	'addpersonalurls-favorites' => 'Special:Mypage/Favorites',
	'addpersonalurls-sandbox' => 'Special:Mypage/Sandbox',
	'addpersonalurls-notes' => 'Special:Mypage/Notes'
);
</syntaxhighlight>

=== Messages ===

Since the extension defines a lot of messages which are constantly evolving with further development, they are not documented here. Please refer to <code>i18n/qqq.json</code>.

When a personal page does not exist, the URL opens the page for editing, and the content of the system messages <b>''ID''-preload</b> and '''addpersonalurls-preload''' is preloaded. This will also work for any URLs you add, provided that ''ID'' is equal to <b>addpersonalurls-''subpage_title_in_lowercase''</b>.

=== CSS ===

In the resulting HTML code, each link is wrapped into an &lt;li&gt; element having an id attribute with value <code>pt-''ID''</code>. This is a feature provided by MediaWiki and allows to format personal URLs individually via CSS.

The AddPersonalUrls exension uses this feature to add icons, taken from the [https://commons.wikimedia.org/wiki/Category:Nuvola_icons Wikicommons Nuvola icons], to the new links as well as to the existing ones provided by MedaWiki. You can override this with your own CSS.

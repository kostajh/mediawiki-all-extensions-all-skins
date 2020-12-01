<!--
     page taken from
     https://www.mediawiki.org/wiki/Extension:AddPersonalUrls/History
-->

== Version 1.2.1 ==

=== Configuration changes ===

Allow to deactivate in <code>$wgAddPersonalUrlsTable</code> items predefined in <code>extension.json</code>.

== Version 1.2.0 ==

=== Configuration changes ===

The extension is now loaded using the <code>wfLoadExtension()</code> mechanism introduced in MediaWiki 1.25. It is therefore incompatible with older MediaWiki versions.

== Version 1.1.1 ==

=== Bug fixes ===

* Check for existence of page-specific preload message now works.

== Version 1.1.0 ==

=== Configuration changes ===

* The messages are now stored in the [[mw:Localisation file format|new JSON format]].

== Version 1.0.0 ==

=== Other changes ===

* Preload text is now displayed within a comment so that the user can comfortably choose to leave it there.
* An active personal URL is now bold in all skins (it used to be in Monobook but not in Vector because that is what MediaWiki's CSS says).
* Also non-existing pages opened for editing are now formatted as active.

== Version 0.4.1 ==

=== Bug fixes ===

* When creating a new personal page, text is preloaded only if the relevant preload message exists.
* If the request contains already a preload text, that preload text is not changed.
* [[Manual:$wgExtensionFunctions|$wgExtensionFunctions]] is used so that [[mw-config]] now works.

== Version 0.4 ==

=== Configuration changes ===

A number of enhancements have been implemented which considerably reduce the need for customization by site administrators.

* Configuation is very different from version 0.31. See [[{{NAMESPACE}}:{{BASEPAGENAME}}#Configuration]] (or the file <tt>CONFIGURE</tt> in the distribution) for details.
* AddPersonalUrls is now shipped with an i18n messages file which provides default messages for the languages de, en, fr and it. Thus, unless you want to use other languages or to customize the messages, you don't need to setup custom messages any more.
* The extensions [[Extension:Call|Call]] and [[Extension:DynamicPageList (third-party)|DynamicPageList (third-party)]] are not needed any more.

=== Bug fixes ===

* In version 0.31, the person icon beneath the first URL used to disappear when using this extension. This has been corrected.

=== New features ===

* URLs can now contain external links. See [[{{NAMESPACE}}:{{BASEPAGENAME}}#Configuration]] (or the file <tt>CONFIGURE</tt> in the distribution) for details.
* The array of URLs is now indexed with a name, which allows applying CSS to each single item.
* In the Vector skin, icons are added to the new as well as to the existing icons.
* When a personal page does not exist, the URL opens the page for editing, and a text explaining the purpose of this page is preloaded.

=== Other changes ===

* The code has been reviewed in order to comply better with the guidelines on mediawiki.org.

== Version 0.31 ==

Adapted to the new version of [[Extension:DynamicPageList]] which does not have a dedicated special page any more.

== Version 0.3 ==

Configuration parameter is now a class member.

== Version 0.21 ==

Bugfix in mypages.

== Version 0.2 ==

Configure items via a customizable array.

== Version 0.1 ==

First version published.

CiteDrawer
----------

The extensions eases user interaction with the [Cite](https://www.mediawiki.org/wiki/Extension:Cite) extension by
introducing a floating drawer displayed at the bottom of the screen when a user clicks on a <ref> link.

# Requirements

* MediaWiki 1.29+
* [Cite](https://www.mediawiki.org/wiki/Extension:Cite) extension

# Installation

* Clone the repository into the `/extensions/CiteDrawer` folder
* Add `wfLoadExtension('CiteDrawer');` to the bottom of the `LocalSettings.php` file

# Configuration

* `$wgCiteDrawerEnableDesktop` - set to `false` to disable on desktops ( `true` by default )
* `$wgCiteDrawerEnableMobile` - set to `false` to disable on mobiles ( `true` by default )
* `$wgCiteDrawerTheme` - set to `light` or `dark` to change widget appearance ( `dark` by default )

# Development

* `npm i`
* `npm test`
* Also see https://www.mediawiki.org/wiki/Manual:JavaScript_unit_testing

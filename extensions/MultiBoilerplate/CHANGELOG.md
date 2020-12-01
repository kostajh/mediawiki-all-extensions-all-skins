Revision history for Extension:MultiBoilerplate
====================================================

All notable changes to this project will be documented in this file.
This project adheres (or attempts to adhere) to [Semantic Versioning](http://semver.org/).


## [2.1.2] - 2017-04-24
Document i18n messages.

## [2.1.1] - 2015-12-22
- Support using wikilinks inside MediaWiki:Multiboilerplates (patch by Jhf2442)
- Slight code beautification

## [2.1.0] - 2015-05-24

### Breaking changes
- No longer compatible with MW 1.24. Please upgrade or use version 2.0.0 of the extension.

### Changed
- Updated to use MW's new [ExtensionRegistry](https://www.mediawiki.org/wiki/Manual:Extension_registration).
- Thanks to the extension registration hook, `$wgMultiBoilerplateDiplaySpecialPage` no longer has to be placed
  before the inclusion of the extension.
- RELEASE-NOTES and HISTORY merged together into CHANGELOG.md


## [2.0.0] - 2015-03-18

### Breaking changes
- Not compatible with with MW < 1.24 anymore. If you need compatibility to an older version, you
  might have luck with version 1.8.0 of the extension.
- `$wgMultiBoilerplateDiplaySpecialPage` must now be place before the inclusion (`require_once`)
  of the extension, or it will have no effect.

### Added
- Handle `<onlyinclude>` tag in a boilerplate.
- Add support for optgroups (headers) in the dropdown list, by using level 2 headers (==)
  in `MediaWiki:MultiBoilerplate`, thanks to an old patch by an unknown author; see
  [T43788](https://phabricator.wikimedia.org/T43788) on Phabricator for more details.
- README, RELEASE-NOTES, HISTORY & LICENSE files.


### Changed
- Updated to work with MediaWiki >= 1.24
- Refactored to (hopefully) modern MediaWiki extension standards, e.g.:
    - File structure
    - JSON files for l10n
    - Reduce use of globals


## 1.8.0 (2009-07-31)
- Optional special page

## 1.7.0 (2009-04-08)
- Get boilerplates from content message when used, instead of user language message.


## 1.6.0 (2008-02-26)
- Added README.
- `ltrim()` now used instead of `preg_replace()`.

## 1.5.0 (2008-02-06)
- Now handles `<noinclude>` and `<includeonly>` tags.

## 1.4.0 (2008-02-06)
- It is now (optionally) possible to specify boilerplates in a MediaWiki namespace message
  instead of a `LocalSettings.php]` configuration variable.
- The extension now uses `descriptionmsg` (allowing description internationalisation).
- Part of the code that used direct database interfacing to check for article existence has been
  replaced with a call to the `$wgTitle` global.
- The `$wgMultiBoilerplateThings` global has been renamed to `$wgMultiBoilerplateOptions`
  for a greater level of self-explanation.
- The boilerplate selection box is no longer displayed if there are no options.

## 1.3.0 (2008-01-19)
- Use `Xml::` class methods instead of manually created HTML
- Convert all double-quotes to single-quotes,
- Add an optional message `multiboilerplate-label` to display a label before
  the drop-down box
- Rename `multiboilerplate-select` to `multiboilerplate-legend` and
  `multiboilerplate-load` to `multiboilerplate-submit` to be less ambiguous.

## 1.2.0 (2008-01-07)
- Refactor code, fix major bug that made the extension useless and add comments.

## 1.1.0 (2007-12-18)
- Original version.

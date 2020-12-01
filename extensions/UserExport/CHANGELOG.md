## UserExport extension

The UserExport extension to MediaWiki allows to export all user's account names together with the respective
e-mail address as a UTF-8 encoded CSV file.

This file documents the development changes.


## 2.1.1

Released: 2020-04-15

* Provided missing license information for Composer (by Mark A. Hershberger)


## 2.1.0

Released: 2020-04-12

* Removed support for < MW 1.33.0 (by Mark A. Hershberger)
* Added "userexport" permission by default to the bureaucrat group (by Mark A. Hershberger)
* General code maintenance and tidying to adhere to coding conventions (by Mark A. Hershberger)
* Added system message translations to many languages (by translatewiki.net translators)


## 2.0.0

Released: 2019-06-04

* Removed deprecated PHP entry point (by Gopala Krishna A)
* Removed the usage of global variable $wgUser, $wgRequest, $wgOut (by Ananthsubray)
* Removed setMethod( 'post' ) from HTMLForm Object (by Jayprakash-SE)
* Added system message translations to many languages (by translatewiki.net translators)


## 1.3.0

Released: 2018-06-20

* Converted to use extension registration (UserExport requires now MediaWiki 1.25 or higher) (by MarcoAurelio).
* Fixed deprecated SpecialPage::getTitle warnings (by MarcoAurelio).
* Some PHPCS fixes (by MarcoAurelio).
* Converted to PHP 5.4+ short array syntax (by MarcoAurelio).
* Spanish namespace aliases for Special:UserExport added (by MarcoAurelio).


### 1.2.1

Released: 2018-04-23

* Converted special page "Userexport" to use Object Oriented User Interface (OOUI) (by Jayprakash12345)
* Added support for translations by translatewiki.net (by Kghbln)
* Added system message translations to many languages (by translatewiki.net translators)


### 1.2.0

Released: 2017-07-03

* Added support for MW 1.26+
* Removed support for < MW 1.23.0
* Removed I18n shim for php message files (by Kghbln)
* Migrated registration of special pages to `SpecialPage::getGroupName`
* Fixed typos for special pages aliases
* Updated file documentation (by Kghbln)
* Updated README (by Kgbhln)


### 1.1.1

Released: 2017-01-21

* Migrated to `getEditToken` (by Reedy)
* Improved exception reporting (by Reedy)
* Fixed registration of special page aliases (by SamanthaNyuyen)
* Improved code formatting (by Kghbln and Reedy)
* Updated file documenation (by Kghbln)


### 1.1.0

Released: 2014-11-14

* Removed support for ≤ PHP 5.2
* Removed support for < MW 1.17.0
* Migrated to I18n served via json-files (by Kghbln)
* Migrated to new alias I18n inclusion (by Kghbln)
* Migrated deprecated `wfMsg` to `wfMessage` (by Kghbln)
* Improved wording of system messages (by Kghbln)
* Added missing system messages (by Kghbln)
* Apply several code tweaks (by Kghbln)
* Remove dead code (by Kghbln)
* Overhaul file documentation (by Kghbln)
* Add CHANGELOG (by Kghbln)
* Add COPYING (by Kghbln)


### 1.0.1

Released: 2014-01-02

* Improved formatting and file documentation (by Kghbln)


### 1.0

Released: 2009-08-09

* Initial public release (by Rodrigoprimo)

## PCR GUI Inserts

The PCR GUI Inserts extension lets you easily add pieces of HTML code at several useful places of the GUI.


### Compatibility

* PHP 5.3+
* MediaWiki 1.23+

See also the CHANGELOG.md file provided with the code.


### Installation

(1) Obtain the code from [GitHub](https://github.com/wikimedia/mediawiki-extensions-PCRGUIInserts/releases)

(2) Extract the files in a directory called `PCRGUIInserts` in your `extensions/` folder.

(3) Add the following code at the bottom of your "LocalSettings.php" file:
```
require_once "$IP/extensions/PCRGUIInserts/PCRGUIInserts.php";
```
(4) Configure this extension as needed for your wiki. See the [documentation](https://www.mediawiki.org/wiki/Extension:PCR_GUI_Inserts#Configuration).

(5) Go to "Special:Version" on your wiki to verify that the extension is successfully installed.

(6) Done.

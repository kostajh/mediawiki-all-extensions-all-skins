
# MediaWiki extension: GenerateMochaConfig

GenerateMochaConfig is a MediaWiki maintenance extension that generates a JSON
that can be used to run Mocha tests for all enabled extensions with a `tests/mocha`
directory.

To enable it, first download a copy of the GenerateMochaConfig directory and put it
into your extensions directory. Then put the following at the end of your
LocalSettings.php:

    wfLoadExtension ( 'GenerateMochaConfig' );
    
## Usage

To get a Mocha configuration JSON for all enabled extensions run:

	$ php extensions/GenerateMochaConfig/maintenance/generateMochaConfig.php

To merge the Mocha configuration JSON with an existing mocharc.json file run:

	$ php extensions/GenerateMochaConfig/maintenance/generateMochaConfig.php --config <filename>



# MetaMaster
A parser function that allows adding HTML <meta> tags to the page header

## Features
 * [Parser hook](https://www.mediawiki.org/wiki/Manual:Parser_functions) (MetaMaster/MetaMasterHooks.php)

## Development on Linux (OS X anyone?)
To take advantage of this automation, use the Makefile: `make help`. To start,
run `make install` and follow the instructions.

## Development on Windows
Since `Makefile` cannot be used on Windows, do the following:
 * Install nodejs, npm, and PHP composer
 * Change to the extension's directory
 * Run npm install and composer install

Once set up, running `npm test` and `composer test` will run automated code checks.

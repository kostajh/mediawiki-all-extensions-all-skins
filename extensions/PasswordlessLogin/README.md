# MediaWiki extension Passwordless Login

This extension can be used as an additional authentication provider in MediaWiki to allow users to login by only typing in their username and confirming their login using a pre-registered Smartphone.
This allows users to login easier and it also may be a good way to fight against bad passwords, as the user does not need a password at all.

The extension verifies the login to the MediaWiki application by answering a randomly generated challenge with a pre-shared secret key, hashed by a modern and secure hash algorithm.

This extension requires the [gd PHP extension](https://www.php.net/manual/de/book.image.php) to be installed in order to generate a QR Code for a user to pair their device.

## Android app

This extension requires an app being installed on the users smartphone.
There's an android app implementation, which can be found [here](https://github.com/FlorianSW/mediawiki-app-PasswordlessLogin).

## iOS app

There's no iOS app for now, feel free to create one ;)

## Demo

The extension can be seen live (on a local development environment) in [this YouTube video](https://youtu.be/7QXdG_Bl3k4).

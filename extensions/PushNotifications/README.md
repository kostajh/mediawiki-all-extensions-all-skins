## PushNotifications

A MediaWiki extension supporting interactions with external push notifications services.

### Installation

Clone this repository into your MediaWiki installation's `extensions` directory:

```
git clone https://gerrit.wikimedia.org/r/mediawiki/extensions/PushNotifications
```

Then, enable the extension in `LocalSettings.php`:

```
wfLoadExtension( 'PushNotifications' );
```

### Testing

Tests are run with composer and npm:

```
composer install
composer test

npm install
npm test
```

### More information

See the [extension page](https://www.mediawiki.org/wiki/Extension:PushNotifications) on mediawiki.org for more information.

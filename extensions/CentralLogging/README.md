# CentralLogging

CentralLogging is a MediaWiki extension that lets extensions send log entries to a central wiki.
It provides no functionality by itself, rather is a framework for other functions to use.

## Configuration

$wgCentralWiki should be set to the database name of the central wiki to log on.
Indivdual extensions will be able to override this if they choose to do so.
The default value is:

```php
$wgCentralWiki = 'metawiki';
```

When setting up an extension to use CentralLogging, you need to set a few global variables:

```php
$wgLogTypes[] = 'foo'; // You should do this anyways
$wgLogActionsHandlers['foo/*'] = 'CentralLogFormatter';
```

## Example code

Some example usage would look like:

```php
$entry = new CentralLogEntry( 'foo', 'bar' );
```

After this step, the next set of code is the same as ManualLogEntry.
See https://www.mediawiki.org/wiki/Manual:Logging_to_Special:Log for more details

```php
$entry->setTitle( $title );
$entry->setPerformer( $user );
$entry->setComment( 'comment' );
```

Now we need to queue the message rather than insert it

```php
$entry->queue( $dbname = null, $publish = true, $to = 'rcandudp' );
```

All parameters are optional:
* $dbname: if it is set to null, it defaults to $wgCentralWiki
* $publish: whether we should call ManualLogEntry::publish afterwards. By default this is true.
* $to: this will just be passed to ManualLogEntry::publish if it is called. This is 'rcandudp' by default.

Another implementation might look like:

```php
if ( class_exists( 'CentralLogEntry' ) ) {
	$entry = new CentralLogEntry( 'foo', 'bar' );
} else {
	$entry = new ManualLogEntry( 'foo', 'bar' );
}
	$entry->setTitle( $title );
	$entry->setPerformer( $user );
	$entry->setComment( 'comment ');
if ( $entry instanceof CentralLogEntry ) {
	$entry->queue();
} else {
	$logId = $entry->insert();
	$entry->publish( $logId );
}
```

This will let your extension take advantage of central logging if you have this extension enabled,
otherwise it falls back upon the local logging system.
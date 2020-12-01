# Wikispeech Speech Data Collector

## Namespaces

* MediaWiki\WikispeechSpeechDataCollector\CRUD
* MediaWiki\WikispeechSpeechDataCollector\Domain

## Domain

Data only objects, implements interface Persistent.
Includes a visitor pattern that can be used to introduce logic
without tainting the classes.

### Identities and primary keys

As of writing this text, all persistent classes use a 128 bit UUID
stored in a string as the instance identity.

The main reason for this is to allow future merging of data from
multiple separated installations of this extension.

The pattern support any possible identity, although we've only implemented
it to use a simple single field such as an auto incrementing integer or
a UUID. Not too many changes would be required to introduce a strategy
that support a complex composite identity spanning multiple fields that's
could, if you want, be represented as its own class in PHP.

## CRUD

Create, Read, Update, Delete.

Handles serialization and deserialization of domain objects.

```php
$crud = new UserCRUD();
$user = $crud->read( 'uuid' );

$user = new User();
$crud->create( $user );
$crud->update( $user );

$crud->delete( 'uuid' );
```

## CLUD

Create, Load, Update, Delete.

A convenience class for handling persistent instances no matter what
subclass of Persistent they are.

```php
$clud = new CLUD();
$user = new User();
$user->setIdentity( 'uuid' );
$clud->load( $user );

$clud->create( $user );
$clud->update( $user );

$clud->delete( $user );
```

The CLUD is indeed a few extra lines to load an instance compared
to using the CRUD. However, when working with a bunch of different
persistent classes you'll also have to keep track of one CRUD for
each subclass of Persistent.

If nothing else, it demonstrates how to use the visitor pattern.

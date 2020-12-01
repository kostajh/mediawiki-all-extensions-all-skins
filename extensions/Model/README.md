Mediawiki-Model
===============

Model is extension for mediawiki brings "M" from MVC pattern into MediaWiki.

![Model](http://i.imgur.com/QsakWVL.png)

What is Model?
==============

Very simple and lightweight thing.
Model allows you use model-objects over Mediawiki DatabaseBase class.

**With Model you can focus on work with objects, not SQL-queries.**
```php
 $user = new Model_User();
 $user->password = '1234';
 $user->save();
```
more examples can be found [here](https://github.com/vedmaka/Mediawiki-Model/wiki/Examples).

**To start using Model**:

- include it in LocalSettings.php
- create model classes based on class Model with your model description
- create proper database tables
- use models in your code.

Read [documentation](http://github.com/vedmaka/Mediawiki-Model/wiki) for additional information.

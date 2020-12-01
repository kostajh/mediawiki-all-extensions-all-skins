Mediawiki-Views
===============

![Views](http://i.imgur.com/nz1ipuH.png)

Views extension for Mediawiki. 
Brings some templating in dev process to separate html and php.

Instead of using inline html blocks in php, just use Views.

Example
=======

- Create folder named **views** in your extension folder.
- Put some php-template in this folder, for example **mytemplate.php** with some content:

```php
<div class="hello-div <?=$b?>">
<p> <?=a?> </p>
</div>
```

- Call Views. To feed template with some data push array to second parameter:

```php
$data = array( 'a' => 10, 'b' => 'hello' );
$html = Views::forge('mytemplate', $data);
```
  
Result will be stored in **$html** variable:

```
<div class="hello-div hello">
<p> 10 </p>
</div>
```

Thats it!

MW-MetaTitle
============

An fork of [Add HTML Meta and Title](https://www.mediawiki.org/wiki/Extension:Add_HTML_Meta_and_Title) extension for MediaWiki.

Instalation
=====
Copy Add_HTML_Meta_and_Title.php into 'extensions' directory.
Add following into your LocalSettings.php

```php
$wgSitename = "My Wiki";  
$wgAllowDisplayTitle = true;  
$wgRestrictDisplayTitle = false;  
require_once "$IP/extensions/Add_HTML_Meta_and_Title/Add_HTML_Meta_and_Title.php';
```

Usage
=====
```html
<seo title="word1,word2" metakeywords="word3,word4" metadescription="word5,word6" google-site-verification="123456789-abfd123456" />
```
...or the shorter...

```html
<seo title="word1,word2" metak="word3,word4" metad="word5,word6" google-site-verification="123456789-abfd123456" />
```
...these words are added to the HTML title and meta headers. This makes SEO (search engine optimization) with MediaWiki easier.

For example, the above would become:

```html
<title>Original title, word1,word2</title>         (the string ", word1,word2,..." is added)
<meta name="keywords" content="word3,word4" />
<meta name="description" content="word5,word6" />
<meta name="google-site-verification" content="123456789-abfd1234562 />
```
(These are new meta tags - existing meta tags are left untouched.)


Useful hint
===========
Create a template called Seo, insert following wikitext.


    {{DISPLAYTITLE:{{{pagetitle}}} }}
    <seo title={{{pagetitle}}} metakeywords="{{{meta_keywords}}}"/>
Now you can use more Wiki-flavoured style like following.


    {{seo
    |pagetitle=My Main Page
    |meta_keywords=super,long,increase,inches}}

Changelog
=========
08.06.2012 - version 0.5   - MediaWiki v1.19 support
17.05.2015 - version 0.5.2 - using new i18n system
17.05.2015 - version 0.6   - add support for google-site-verification

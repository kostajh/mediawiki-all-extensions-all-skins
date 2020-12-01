The extension adds a few parser functions to retrieve page id by
name and vice versa.

# Requirements:

* MediaWiki 1.30+

# Usage

Get page NAME by a page ID

```
{{FULLPAGENAMEFROMID:12345}} -> Namespace:Pagename
```

Get page ID by revision ID

```
{{PAGEIDFROMREVISIONID:3456}} -> 12345
```

Get page NAME by a revision ID

```
{{FULLPAGENAMEFROMREVISIONID:3456}} -> Namespace:Pagename
```

Note: you can also use MediaWiki built-in magic `{{PAGEID:page_name}}`
to get page ID from a page name, eg:

```
{{PAGEID:Namespace:Pagename}} -> 12345
```

# Post build changes

## CSS
Unfortunately the "reset" CSS of ExtJS can not be disabled by using the build
process of the theme.

Therefore the following CSS rules have been changed *manually* from the files
produced by the build process:

    In theme-mediawiki-touch_all1.css:

    .x-body{color:#000;font-size:15px;line-height:19px;font-weight:300;font-family:sans-serif;background:#fff} --> removed

	font-size:15px --> font-size:0.8rem
	"15px/" --> "0.8rem/"

## Sprite images
The following images have been replaced by custom ones to better match
MediaWiki UI:

    theme-mediawiki-touch/images/form/checkbox.png
    theme-mediawiki-touch/images/form/radio.png
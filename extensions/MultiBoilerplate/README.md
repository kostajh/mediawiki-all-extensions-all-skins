MultiBoilerplate extension for MediaWiki
========================================

The MultiBoilerplate extension allows a boilerplate to be selected from a drop
down box located above the edit form. By default this shows only on creation of
new pages. When loading a boilerplate, it will completely replace whatever text
is already in the edit form.

## Dependencies
Version 2.1.0 and later requires MediaWiki >= 1.25.

## Installation
See the regular installation instructions for MediaWiki extensions: 
  <https://www.mediawiki.org/wiki/Manual:Extensions#Installing_an_extension>

## Configuration

### Main configuration
The main configuration is of the available boilerplates; this is done *either*:
	
* through `$wgMultiBoilerplateOptions` in `LocalSettings.php`, by filling the
  $wgMultiBoilerplateOptions array with a list of boilerplate names that
  correspond to templates, like so:  
  ```$wgMultiBoilerplateOptions[ "My Boilerplate" ] = "Template:My Boilerplate";```
* Or through system message `MediaWiki:Multiboilerplate`, which uses the following
  format:  
  `My Boilerplate|Template:My Boilerplate`  
    * You can also create headers to separate boilerplates, which will be transformed
      to option groups in the edit form drop down. For example:
    
        == Pretty Templates ==
        * My Boilerplate|Template:My Boilerplate
        == Ugly Templates ==
            * Their Boilerplate|Template:Their Boilerplate
    * The template pages may also be represented by links, just for convenience, e.g.:
      `* My Boilerplate|[[Template:My Boilerplate]]`


### Additional configuration options

* `$wgMultiBoilerplateDiplaySpecialPage`: false by default. if set to true,
  will add to the wiki a page named `Special:MultiBoilerplates` that shows
  the currently configured boilerplates.

* `$wgMultiBoilerplateOverwrite`: false by default. If true, shows the
  boilerplates dropdown even on pre-existing page. The selected boilerplate 
  will completed overwrite the current contents.

## Change log
See [CHANGELOG.md](CHANGELOG.md) for a complete change log.

## Credits
Originally by Robert Leverington (minuteelectron), with additional contributions
by Al Maghi, Dror S \[FFS\] and Jhf2442.
See the [commit log](https://phabricator.wikimedia.org/diffusion/EMBP/history/master/)
for a full list of contributers.




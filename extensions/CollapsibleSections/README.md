# CollapsibleSections

This extension (for MediaWiki) makes it possible for users to collapse and expand content sections. This feature is always available to mobile users, and can be configured to be available to desktop users as well.

# Requirements

* MediaWiki 1.36+

# Installation

* Clone the repository into `extensions` folder
* Add `wfLoadExtension( 'CollapsibleSections' );` to the bottom of your `LocalSettings.php` file

# Configuration

* `$wgCollapsibleSectionsCollapsedByDefault` — (default is `true`) sets whether sections should have the collapsed state on page load
* `$wgCollapsibleSectionsEnableDesktop` — (default is `false`) sets whether extension should work on desktop views
* `$wgCollapsibleSectionsTag` — (default is `h2`) sets the root section tag to be used for finding collapsible content

# Development

* Run `npm i` to install development dependencies
* Run `npm test` to run tests

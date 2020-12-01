# BlueSpiceUserSidebar

## Installation
Execute

    composer require bluespice/usersidebar dev-REL1_31
within MediaWiki root or add `bluespice/usersidebar` to the
`composer.json` file of your project

## Activation
Add

    wfLoadExtension( 'BlueSpiceUserSidebar' );
to your `LocalSettings.php` or the appropriate `settings.d/` file.
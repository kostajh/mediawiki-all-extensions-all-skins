# LDAPSyncAll

This extension provides mechanism which synchronizes users in database and users in active directory

* If in a database there is no user, who is in LDAP => user will be added to the database

* If in a database there is user, who is not in LDAP => user will be disabled

## Installation
Execute

	composer require hallowelt/ldapsyncall dev-REL1_31
within MediaWiki root or add `hallowelt/ldapsyncall` to the
`composer.json` file of your project

## Activation
Add

	wfLoadExtension( 'LDAPSyncAll' );
to your `LocalSettings.php` or the appropriate `settings.d/` file.

## Usage

Extension provides maintenance script that you can simply run from your console `php maintenance/SyncLDAPUsers.php`
Also, there is RunJobsTriggerHandler that runs once a day.

## Configuration

You need to add the following line in your LocalSettings.php, don't forget to change "Admin" to username who has admin permissions.
This user is the guy who disables accounts that are not in LDAP

`$GLOBALS['LDAPSyncAllBlockExecutorUsername'] = 'Admin';`

You can specify usernames and usergroups that you want to exclude from disabling, for example:

`$GLOBALS['LDAPSyncAllExcludedUsernames'] = [ 'Bob', 'Emily' ];`

`$GLOBALS['LDAPSyncAllExcludedGroups'] = [ 'bot', 'editor' ];`
RevisionCommentSupplement
https://www.mediawiki.org/wiki/Extension:RevisionCommentSupplement


== Credit and License ==

This extension uses codes of MediaWiki.

Please view CREDITS for the contributors of MediaWiki.

This extension lisensed under the GNU General Public License,
version 2 or any later version (http://www.gnu.org/copyleft/gpl.html).


== Install and Update ==

This extension needs to run update.php on installation.
This needs to do on update from 0.1.x or 0.2.x to 0.3.x,
and from 0.1.x, 0.2.x or 0.3.x to 0.4.0.

=== Update from 0.2.x ===

You should remove supplement group before you update this extension from 0.2.x.
Or you should run
php extension/RevisionCommentSupplement/maintenance/fixUserGroupSupplement.php --log
or
php maintenance/migrateUserGroup.php --oldgroup supplement --newgroup supplementcomment
then.

=== Update to 0.4.0 ===

If you want to use history and to update from 0.3.x and older,
this also needs to run update.php.
If you changed history settings from false to true
and want to have later history, you should run
php extension/RevisionCommentSupplement/maintenance/buildHistory.php --log --supplement
then.

In 0.4.0, schema of log entries' log_params is modified
when saving supplementary comment.
If you want to update old log_params to new schema, you should run
php extension/RevisionCommentSupplement/maintenance/fixLogParams.php
then.

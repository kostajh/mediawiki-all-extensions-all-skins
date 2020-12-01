The Minimum Name Length extension to MediaWiki.

Copyright © 2007 Rob Church.
All rights reserved.

Please see the COPYING file for terms of use and redistribution.

== Introduction ==

This extension introduces a new configuration parameter called
$wgMinimumNameLength, which can be used to enforce a minimum
username length during user account creation.

This is often helpful in conjunction with other anti-spam measures.

== Requirements ==

This extension requires:

* PHP 5.3.3 or above
* MediaWiki 1.27.0 or above

See also the CHANGELOG for further information.

== Installation ==

To install this extension, please download the tarball and extract all
files or clone all files from the Git repository to the "MinimumNameLength"
directory within your MediaWiki extensions directory.

You then need to edit your "LocalSettings.php" file and add the following
line somewhere near the bottom:

 wfLoadExtension( 'MinimumNameLength' );

Access the "Special:Version" page on your wiki to verify the installation.

== Configuration ==

To configure the minimum username length, set the $wgMinimumUsernameLength
configuration parameter in your "LocalSettings.php" file *after* the call
to include the extension, e.g.

 wfLoadExtension( 'MinimumNameLength' );
 $wgMinimumUsernameLength = 8;

By default the minimum username length is set to 10.

<?php

// phpcs:ignoreFile
/**
 * This is an example configuration of Extension:LdapAuthentication by Ryan Lane
 * https://www.mediawiki.org/w/index.php?title=Extension:LDAP_Authentication/Configuration_Options&oldid=2534281
 */

// The names of one or more domains you wish to use
// These names will be used for the other options, it is freely choosable and not dependent
// on your system. These names will show in the Login-Screen, so it is important that the user
// understands the meaning.
//
// REQUIRED
//
// Default: none
$wgLDAPDomainNames = [
  'testADdomain',
  'testLDAPdomain',
];

// The fully qualified name of one or more servers per domain you wish to use. If you are
// going to use SSL or StartTLS, it is important that the server names provided here exactly
// match the name provided by the SSL certificate returned by the server; otherwise, you may
// have problems.
// REQUIRED
// Default: none
$wgLDAPServerNames = [
  'testADdomain' => 'testADserver.AD.example.com',
  'testLDAPdomain' => 'testLDAPserver.LDAP.example.com testLDAPserver2.LDAP.example.com',
];

// Allow the use of the local database as well as the LDAP database.
// Mostly for transitional purposes. Unless you *really* know what you are doing,
// don't use this option. It will likely cause you annoying problems, and
// it will cause me annoying support headaches.
// Warning: Using this option will allow MediaWiki to leak LDAP passwords into
// its local database. It's highly recommended that this setting not be used for
// anything other than transitional purposes.
// Default: false
$wgLDAPUseLocal = false;

// The type of encryption you would like to use when connecting to the LDAP server.
// Available options are 'tls', 'ssl', and 'clear'
// Default: tls
$wgLDAPEncryptionType = [
  'testADdomain' => 'tls',
  'testLDAPdomain' => 'clear',
];

// Custom LDAP configuration options; allows you to set options specified at
// http://www.php.net/manual/en/function.ldap-set-option.php
// Default: none
$wgLDAPOptions = [
  'testADdomain' => [ LDAP_OPT_DEREF, 0 ],
  'testLDAPdomain' => [ LDAP_OPT_DEREF, 1 ],
];

// Connect with a non-standard port
// Available in 1.2b+
// Default: 389 for clear/tls, 636 for ssl
$wgLDAPPort = [
  'testADdomain' => 1389,
  'testLDAPdomain' => 1636,
];

// The search string to be used for straight binds to the directory; USER-NAME will be
// replaced by the username of the user logging in.
// This option is not required (and shouldn't be provided) if you are using a proxyagent
// and proxyagent password.
// If you are using AD style binding (TDOMAIN\\USER-NAME or USER-NAME@TDOMAIN) and
// want to be able to use group syncing, preference pulling, etc., you'll need to set
// $wgLDAPBaseDNs and $wgLDAPSearchAttributes for the domain.
$wgLDAPSearchStrings = [
  'testADdomain' => "TDOMAIN\\USER-NAME",
  'testLDAPdomain' => 'uid=USER-NAME,ou=people,dc=LDAP,dc=example,dc=com',
];

// User and password used for proxyagent access.
// Please use a user with limited access, NOT your directory manager!
$wgLDAPProxyAgent = [
  'testLDAPdomain' => 'cn=proxyagent,ou=profile,dc=LDAP,dc=example,dc=com',
];
$wgLDAPProxyAgentPassword = [
  'testLDAPdomain' => 'S0M3L0ngP@$$w0r6ofS0meV@rie222y!',
];

// Search filter.
// These options are only needed if you want to search for users to bind with them. In otherwords,
// if you cannot do direct binds based upon $wgLDAPSearchStrings, then you'll need these two options.
// If you need a proxyagent to search, remember to set $wgLDAPProxyAgent, and $wgLDAPProxyAgentPassword.
// Anonymous searching is supported. To do an anonymous search, use SearchAttibutes and don't set a Proxy
// agent for the domain required.
$wgLDAPSearchAttributes = [
  'testADdomain' => 'sAMAccountName',
  'testLDAPdomain' => 'uid'
];

// Base DNs. Group and User base DNs will be used if available; if they are not defined, the search
// will default to $wgLDAPBaseDNs
$wgLDAPBaseDNs = [
  'testADdomain' => 'dc=AD,dc=example,dc=com',
  'testLDAPdomain' => 'dc=LDAP,dc=example,dc=com'
];
$wgLDAPGroupBaseDNs = [
  'testADdomain' => 'ou=Domain Groups,dc=AD,dc=example,dc=com',
  'testLDAPdomain' => 'ou=group,dc=LDAP,dc=example,dc=com'
];
$wgLDAPUserBaseDNs = [
  'testADdomain' => 'ou=Domain Users,dc=AD,dc=example,dc=com',
  'testLDAPdomain' => 'ou=people,dc=LDAP,dc=example,dc=com'
];

// User and password used for writing to the directory.
// Please use a user with limited access, NOT your directory manager!
// Defaults: none; disabled
$wgLDAPWriterDN = [
  'testLDAPdomain' => 'uid=priviledgedUser,ou=people,dc=LDAP,dc=example,dc=com'
];
$wgLDAPWriterPassword = [
  'testLDAPdomain' => 'S0M3L0ngP@$$w0r6ofS0meV@rie222y!'
];

// A location to add users to if you are using $wgLDAPSearchAttributes and $wgLDAPAddLDAPUsers.
// This option requires $wgLDAPWriterDN and $wgLDAPWriterPassword to be set.
// Default: none; disabled
$wgLDAPWriteLocation = [
  'testLDAPdomain' => 'ou=people,dc=LDAP,dc=example,dc=com'
];

// Options for adding users, and/or updating user preferences in LDAP. If you use these options
// you must set $wgLDAPWriterDN and $wgLDAPWriterPassword.
// Defaults: false
$wgLDAPAddLDAPUsers = [
  'testADdomain' => false,
  'testLDAPdomain' => true
];
$wgLDAPUpdateLDAP = [
  'testADdomain' => false,
  'testLDAPdomain' => true
];

// Change the hashing algorithm that is used when changing passwords or creating
// user accounts. The default (not setting this variable) will use a base64 encoded
// SHA encrypted password. I do not recommend setting this variable unless you need to
// store clear text or crypt passwords.
// Default: sha
$wgLDAPPasswordHash = [
  'testLDAPdomain' => 'crypt'
];

// Option for mailing temporary passwords to users
// (notice, this will store the temporary password in the local directory
// if you cannot write LDAP passwords because writing is turned off,
// this probably won't help you much since users will not be able to change
// their password)
// This option requires $wgLDAPWriterDN, $wgLDAPWriterPassword and $wgLDAPUpdateLDAP
// Default: false
$wgLDAPMailPassword = [
  'testLDAPdomain' => true
];

// Option for allowing the retreival of user preferences from LDAP.
// Only pulls a small amount of info currently.
// Default: false
// DEPRECATED in 1.2a
$wgLDAPRetrievePrefs = [
  'testADdomain' => true,
  'testLDAPdomain' => true
];

// Option for pulling specific preferences. Available options
// are 'email', 'realname', 'nickname', 'language'
// Ensure all attribute names given are in lower case.
// Default: none; disabled
// Available in 1.2a
$wgLDAPPreferences = [
  'testADdomain' => [ 'email' => 'mail','realname' => 'cn','nickname' => 'samaccountname' ],
  'testLDAPdomain' => [ 'email' => 'mail','realname' => 'displayname','nickname' => 'cn','language' => 'preferredlanguage' ]
];

// Don't automatically create an account for a user if the account exists in LDAP
// but not in MediaWiki.
// Default: false.
$wgLDAPDisableAutoCreate = [
  'testADdomain' => true
];

// Shortest password a user is allowed to login using. Notice that 1 is the minimum so that
// when using a local domain, local users cannot login as domain users (as domain user's
// passwords are not stored)
// Default: 0
$wgMinimalPasswordLength = 1;

// Option for getting debug output from the plugin. 1-3 available. 1 will show
// non-sensitive info, 2 will show possibly sensitive user info, 3+ will show
// sensitive system info. Setting this on a live public site is probably a bad
// idea.
// Default: 0
$wgLDAPDebug = 1;

// Whether the username in the group is a full DN (AD generally does this), or
// just the username (posix groups generally do this)
// Default: false
$wgLDAPGroupUseFullDN = [
  'testLDAPdomain' => true,
  'testADdomain' => true
];

// Munge the case of the username to lowercase when doing searches in groups
// Default: true
$wgLDAPLowerCaseUsername = [
  'testLDAPdomain' => true,
  'testADdomain' => false
];

// Use the exact name retrieved from LDAP after the user has authenticated to search for groups.
// This requires the SetUsernameAttributeFromLDAP hook to be used (see the smartcard section).
// Default: false
$wgLDAPGroupUseRetrievedUsername = [
  'testLDAPdomain' => false,
  'testADdomain' => false
];

// The objectclass of the groups we want to search for
$wgLDAPGroupObjectclass = [
  'testLDAPdomain' => 'groupofuniquenames',
  'testADdomain' => 'group',
];

// The attribute used for group members
$wgLDAPGroupAttribute = [
  'testLDAPdomain' => 'uniquemember',
  'testADdomain' => 'member',
];

// The naming attribute of the group
$wgLDAPGroupNameAttribute = [
  'testLDAPdomain' => 'cn',
  'testADdomain' => 'cn',
];

// Use the memberOf attribute to find groups.
// If memberOf is used, it will be the only method used for searching for groups.
// This means it will search $wgLDAPUserBaseDNs for the memberOf attribute and compare
// all results to $wgLDAPRequiredGroups and not take $wgLDAPGroupBaseDNs into account
// for limiting the search.
// Default: false
// Available in 1.2b+
$wgLDAPGroupsUseMemberOf = [
  'testLDAPdomain' => false,
  'testADdomain' => true,
];

// Pull LDAP groups a user is in, and update local wiki security group.
// Default: false
$wgLDAPUseLDAPGroups = [
  'testADdomain' => true,
  'testLDAPdomain' => true,
];

// A list of groups that won't automatically have their members
// removed, but will have them added. The sysop, bureaucrat, and bot
// groups are always considered locally managed.
$wgLDAPLocallyManagedGroups = [
  'testADdomain' => [ 'adtestgroup', 'adtestgroup2' ],
  'testLDAPdomain' => [ 'ldaptestgroup', 'ldaptestgroup2' ],
];

// Get every group from LDAP, and add it to $wgGroupPermissions. This
// is useful for plugins like Group Based Access Control. This is very
// resource intensive, and probably shouldn't be used in very large
// environments.
// Default: false
$wgLDAPGroupsPrevail = [
  'testADdomain' => true,
  'testLDAPdomain' => true
];

$wgLDAPRequiredGroups = [
  'testLDAPdomain' => [
	'cn=testgroup,ou=groups,dc=LDAP,dc=example,dc=com',
	'cn=testgroup2,ou=groups,dc=LDAP,dc=example,dc=com',
  ],
  'testADdomain' => [
	'cn=testgroup,ou=groups,dc=AD,dc=example,dc=com',
  ]
];

// An array of the groups the user cannot be a member of.
// Available in 1.2b+
$wgLDAPExcludedGroups = [
  'testLDAPdomain' => [
	  'cn=evilgroup,ou=groups,dc=LDAP,dc=example,dc=com',
	  'cn=evilgroup2,ou=groups,dc=LDAP,dc=example,dc=com',
	  ],
  'testADdomain' => [
	  'cn=evilgroup,ou=groups,dc=AD,dc=example,dc=com',
	  ]
];

// Whether or not the plugin should search in nested groups
// Not currently used for group synchronization
// Default: false
$wgLDAPGroupSearchNestedGroups = [
  'testLDAPdomain' => false,
  'testADdomain' => true,
];

// Whether or not to do group searches using an active directory
// optimized way.
// Available in 2.0e
// Default: false
$wgLDAPActiveDirectory = [
	'testLDAPDomain' => false,
	'testADLDAPDomain' => true,
];

// Used with a proxy search
// Require the following additional search string.
$wgLDAPAuthAttribute = [
  'testADdomain' => '!(userAccountControl:1.2.840.113556.1.4.803:=2)',
  'testLDAPdomain' => '!(nsaccountlock=true)',
];

// Enable smartcard authentication
// DEPRECATED in 1.2a
$wgLDAPAutoAuthMethod = 'smartcard';

// The domain that will be using smartcard authentication
// DEPRECATED in 1.2a
$wgLDAPSmartcardDomain = 'testADdomain-smartcard';

// The domain that will be using auto authentication
// Available in 1.2a
$wgLDAPAutoAuthDomain = 'testADdomain-auto';

// The attribute from the smartcard you wish to search LDAP for
// DEPRECATED in 1.2a
$wgLDAPSSLUsername = $_SERVER['SSL_CLIENT_S_DN_CN'];

// The attribute from the webserver you wish to search LDAP for
// Available in 1.2a
$wgLDAPAutoAuthUsername = $_SERVER['SSL_CLIENT_S_DN_DN'];

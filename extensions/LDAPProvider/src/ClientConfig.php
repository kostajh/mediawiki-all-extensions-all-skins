<?php

namespace MediaWiki\Extension\LDAPProvider;

class ClientConfig {
	public const DOMAINCONFIG_SECTION = 'connection';
	public const SERVER = 'server';
	public const USER = 'user';
	public const PASSWORD = 'pass';
	public const BASE_DN = 'basedn';
	public const GROUP_BASE_DN = 'groupbasedn';
	public const USER_BASE_DN = 'userbasedn';
	public const SEARCH_STRING = 'searchstring';
	public const OPTIONS = 'options';
	public const PORT = 'port';
	public const ENC_TYPE = 'enctype';
	public const USER_DN_SEARCH_ATTR = 'searchattribute';
	public const USERINFO_USERNAME_ATTR = 'usernameattribute';
	public const USERINFO_REALNAME_ATTR = 'realnameattribute';
	public const USERINFO_EMAIL_ATTR = 'emailattribute';
	public const NESTED_GROUPS = 'nestedgroups';
	public const GROUP_OBJECT_CLASS = 'groupobjectclass';
	public const GROUP_ATTRIBUTE = 'groupattribute';
}

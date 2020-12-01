# MediaWiki Shibboleth extension

MediaWiki Shibboleth extension eduID-hoz.

Tesztelt MediaWiki verzió: ** 1.29+ **

## Szükséges csomagok és beállítások

Kell lennie egy működő Shibboleth SP-nek és be kell kapcsolni a shib2 Apache modult.

* `sudo apt install libapache2-mod-shib2`
* `sudo a2enmod shib2`
* `sudo systemctl restart apache2`

### Apache vhost konfig:

```apache
<Location /index.php/*:PluggableAuthLogin>
	AuthType shibboleth
	ShibRequestSetting applicationId default
	ShibRequestSetting requireSession true
	Require valid-user
</Location>
```
### Apache vhost konfig FastCGI (FPM)

A `ShibRequestSetting applicationId default` helyett `ShibUseHeaders On` érdemes használni.

```apache
<Location /index.php>
  <If "%{QUERY_STRING} =~ /title=(.+):PluggableAuthLogin/">
  AuthType shibboleth
  ShibRequestSetting requireSession true
  Require valid-user
  ShibUseHeaders On
  </If>
</Location>
```

## Telepítés: PluggableAuth extension

[https://www.mediawiki.org/wiki/Extension:PluggableAuth](https://www.mediawiki.org/wiki/Extension:PluggableAuth)

Ahhoz, hogy a Shibboleth extension-t használni tudjuk telepíteni kell a PluggableAuth extension-t. Ez egy általános keretrendszert biztosít, a különbőző külső azonosítási rendszerekhez.

A MediaWiki gyökér könyvtárában, az alábbi parancsok kell kiadni:

* `git clone https://gerrit.wikimedia.org/r/mediawiki/extensions/PluggableAuth extensions`

A MediaWiki LocalSettings.php-ben, írjuk fájl végére:

```php
# Extension engedélyezése
wfLoadExtension( 'PluggableAuth' );

# Automatikus beléptetés kikapacsolása
$wgPluggableAuth_EnableAutoLogin = false;

# Helyi bejelentkezés letiltása
$wgPluggableAuth_EnableLocalLogin = false;

# Felhasználók beállításainak mentése
$wgPluggableAuth_EnableLocalProperties = true;

# Bejelentkezés gomb felirata
$wgPluggableAuth_ButtonLabelMessage = "eduID/eduGAIN";
```

## Telepítés: Shibboleth extension

A MediaWiki gyökér könyvtárában, az alábbi parancsok kell kiadni:

* `git clone https://dev.niif.hu/mediawiki/Shibboleth extensions`

A MediaWiki LocalSettings.php-ben, írjuk fájl végére:

```php
# Shibboleth engedélyezése
wfLoadExtension( 'Shibboleth' );

# felhasználónév attribútum
$wgShibboleth_Username = "eppn";

# E-mail attribútum
$wgShibboleth_Email = "mail";

# Teljes név attribútum
$wgShibboleth_DisplayName = "displayName";

# Csoportkezelés (opcionális)
$wgShibboleth_GroupMap = array(
  'attr_name' => 'affiliation',
  'sysop' => 'staff@niif.hu',
  'bureaucrat' => 'employee@niif.hu'
);

# Single Logout (SLO) base URL
$wgShibboleth_Logout_Base_Url = "https://wiki.example.org";

# Single Logout (SLO) target URL
$wgShibboleth_Logout_Target_Url = "https://wiki.example.org/index.php";

```

### Csoportkezelés

Lehetőségünk van, belépéskor a felhasználót csoportokhoz rendelni attribútumai alapján.

A fent említett $wgShibboleth_GroupMap tömb értékei:

 * `attr_name` az attribútum neve amiben vesszővel elválasztva találhatóak a kölünbőző szerepkörök
 * `sysop` az adminisztrácor (admin) csoporthoz tartozó érték
 * `bureaucrat` a bürokraták csoporthoz tartozó érték

A SAML bejelentkezéshez nem kötelező megadni csoportkezelést, ebben az esetben kézzel kell kiosztani a különbőző jogosultságokat.

### Single Logout (SLO)

Shibboleth Single Logout (SLO) URL felépítése

`$wgShibboleth_Logout_Base_Url . Shibboleth.sso/Logout?return= . $wgShibboleth_Logout_Target_Url`

`https://wiki.example.org/Shibboleth.sso/Logout?return=https://wiki.example.org/index.php`

## TODO

 * HEXAA attribútum

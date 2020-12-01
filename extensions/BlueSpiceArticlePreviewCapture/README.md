# Setup

When using `mod_auth_kerb` and "Extension:Auth_remoteuser" you need to do the following:

In apache's vhost config, make sure that calls from `127.0.0.1` are not blocked by `mod_auth_kerb`:

	<VirtualHost *:443>
		...
		<Directory /var/www/bluespice>
			...
			<RequireAny>
				AuthType Kerberos
				...
				Require ip 127.0.0.0/255.0.0.0 #PhantomJS
			</RequireAny>
		</Directory>
		...

Also set in `LocalSettings.php`:

	$bsgArticlePreviewCapturePhantomJSBaseUrl = 'http://localhost';

For "Extension:Auth_remoteuser" configure

	$wgAuthRemoteuserUserName = [
		isset( $_SERVER[ 'REMOTE_USER' ] ) ? $_SERVER[ 'REMOTE_USER' ] : '',
		$_SERVER[ 'REMOTE_ADDR' ] == '127.0.0.1' ? $_COOKIE['<cookieprefix>RemoteToken'] : '', //e.g. PhantomJS
		...
	];

Be aware that `<cookieprefix>` must be set to a valid value!

If you want to use `symfony-process` as a backend for PhantomJS, make sure that you have installed needed package. 
You can do it easily by adding `symfony/process` package into your composer.lock and run `composer install` 
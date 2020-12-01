<?php

if ( PHP_SAPI != 'cli' ) {
	die( "This script must be run from the command line." );
}

require_once __DIR__ . "/PwnedPasswords.class.php";

$password = $argv[1];

$pwnd = new PwnedPasswords();
$ispwned = $pwnd->checkPassword( $password );

if ( $ispwned ) {
	echo "The password «{$password}» has been listed on a data breach.\n" .
		"You should avoid using this string as a password anywhere.\n\n";
}

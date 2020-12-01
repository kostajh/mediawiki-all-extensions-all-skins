<?php

if ( PHP_SAPI != 'cli' ) {
	die( "This script must be run from the command line." );
}

require_once __DIR__ . "/PwnedPasswords.class.php";

$binary = false;
if ( $argv[1] == "--binary" ) {
	$binary = true;
	array_shift( $argv );
}

$input = fopen( $argv[1], "r" );
$output = null;
$prefix_file = "";

while ( ( $line = fgets( $input ) ) !== false ) {
	$hash = strtok( $line, ":" );
	$prefix = substr( $hash, 0, PwnedPasswords::PREFIX_LENGTH );
	$tail = substr( $hash, PwnedPasswords::PREFIX_LENGTH );

	if ( $binary ) {
		$tail = hex2bin( $tail );
	} else {
		$tail = "$tail\n";
	}

	if ( $prefix !== $prefix_file ) {
		$prefix_file = $prefix;
		$output = fopen( $prefix, "wb" );
	}

	fwrite( $output, $tail );
}

fclose( $input );
if ( $output ) {
	fclose( $output );
}

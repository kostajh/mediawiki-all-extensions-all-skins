<?php

namespace NotesLink;

class Extension {
	public static function onRegistration() {
		$GLOBALS['wgUrlProtocols'][] = 'notes://';

		if ( $GLOBALS['wgVersion'] >= '1.29' ) {
			return;
		}
		$GLOBALS['wgServiceWiringFiles'][] = $GLOBALS['wgExtensionDirectory']
			. "/NotesLink/includes/ServiceWiring.php";
	}
}

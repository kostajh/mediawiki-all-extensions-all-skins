<?php
/**
 * @file
 *
 * Show a click through for the PURGE page instead of just redirecting.
 *
 * This is useful so that I can see the $wgDebugComments that happen on purge.
 *
 * Consider this extension to be in the public domain.
 */

$wgExtensionCredits['other'][] = array(
	'path' => __FILE__,
	'name' => 'PurgeClickThrough',
	'version' => '0.1',
	'author' => 'Brian Wolff',
	'descriptionmsg' => 'purgeclickthrough-desc',
	'url' => 'https://www.mediawiki.org/wiki/Extension:PurgeClickThrough',
);

$wgMessagesDirs['PurgeClickThrough'] = __DIR__ . '/i18n';

$wgActions['purge'] = 'PurgeClickThrough';

class PurgeClickThrough extends PurgeAction {

        public function onSuccess() {
		$text = <<<WIKI
==Purge Succesful==
Continue on to the freshly purged page at 
WIKI;

		// Why is $this->redirectParams private instead of protected?

		$params = $this->getRequest()->getVal( 'redirectparams',
				wfArrayToCGI( array_diff_key(
					$this->getRequest()->getQueryValues(),
					array( 'title' => null, 'action' => null )
				))
			);

		$url = $this->getTitle()->getFullUrl( $params );
		$text .= '[' . $url . ' ' . $url . ']';
		$out = $this->getOutput();
		if ( method_exists( $out, 'addWikiTextAsInterface' ) ) {
			// MW 1.32+
		    $out->addWikiTextAsInterface( $text );
		} else {
		    $out->addWikiText( $text );
		}
        }
}

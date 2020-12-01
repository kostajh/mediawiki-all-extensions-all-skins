<?php
/**
 * Class file for the GoogleAdSense extension
 *
 * @file
 * @ingroup Extensions
 * @author Siebrand Mazeland
 * @license MIT
 */

class GoogleAdSense {
	static function GoogleAdSenseInSidebar( $skin, &$bar ) {
		global $wgGoogleAdSenseWidth, $wgGoogleAdSenseID,
			$wgGoogleAdSenseHeight, $wgGoogleAdSenseClient,
			$wgGoogleAdSenseLang, $wgGoogleAdSenseEncoding,
			$wgGoogleAdSenseSlot, $wgGoogleAdSenseSrc,
			$wgGoogleAdSenseAnonOnly;

		// Return $bar unchanged if not all values have been set.
		// @todo Signal incorrect configuration nicely?
		if ( $wgGoogleAdSenseClient == 'none' || $wgGoogleAdSenseSlot == 'none' || $wgGoogleAdSenseID == 'none' )
			return $bar;

		if ( $skin->getUser()->isLoggedIn() && $wgGoogleAdSenseAnonOnly ) {
			return $bar;
		}

		if ( !$wgGoogleAdSenseSrc ) {
			return $bar;
		}

		// Add CSS
		$skin->getOutput()->addModules( 'ext.googleadsense' );

		$bar['googleadsense-portletlabel'] = "<script type=\"text/javascript\"><!--
google_ad_client = \"$wgGoogleAdSenseClient\";
/* $wgGoogleAdSenseID */
google_ad_slot = \"$wgGoogleAdSenseSlot\";
google_ad_width = " . intval( $wgGoogleAdSenseWidth ) . ";
google_ad_height = " . intval( $wgGoogleAdSenseHeight ) . ";
google_language = \"$GoogleAdSenseLang\";
google_encoding = \"$GoogleAdSenseEncoding\";
// -->
</script>
<script type=\"text/javascript\"
src=\"$wgGoogleAdSenseSrc\">
</script>";

		return true;
	}
}

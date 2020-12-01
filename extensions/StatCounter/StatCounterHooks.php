<?php
/**
 * @file
 */
class StatCounterHooks {

	/**
	 * @param Skin $skin
	 * @param array &$bar
	 * @return true
	 */
	public static function onSkinBuildSidebar( $skin, &$bar ) {
		$projectId = (int)$skin->msg( 'statcounter-project-id' )->plain();
		$security = htmlspecialchars( $skin->msg( 'statcounter-security' )->plain(), ENT_QUOTES );
		// Quickie test to check whether this thing's been set up correctly or not
		if ( $skin->msg( 'statcounter-project-id' )->isDisabled() ) {
			return true;
		}

		$bar['statcounter'] = <<<CODE
<!-- Start of StatCounter Code -->
<script>
var sc_project={$projectId};
var sc_invisible=0;
var sc_security="{$security}";
var scJsHost = (("https:" == document.location.protocol) ? "https://secure." : "http://www.");
document.write("<sc"+"ript type='text/javascript' src='"+scJsHost +"statcounter.com/counter/counter.js'></"+"script>");
</script>
<noscript><div><a title="free hit counter" href="http://statcounter.com/free-hit-counter/" target="_blank"><img src="http://c.statcounter.com/{$projectId}/0/{$security}/0/" alt="free hit counter" /></a></div></noscript>
<!-- End of StatCounter Code -->
CODE;
		return true;
	}
}

<?php
namespace FundraisingTranslateWorkflow;

/**
 * Modifies message group permissions when the group ID matches configuration.
 */
class TranslateHooks {

	/**
	 * Translate:modifyMessageGroupStates hook handler
	 *
	 * @param string $groupId
	 * @param array &$conf
	 */
	public static function onModifyMessageGroupStates( $groupId, &$conf ) {
		global $wgFundraisingTranslateWorkflowPagePatterns,
			$wgFundraisingTranslateWorkflowPublishRight;

		foreach ( $wgFundraisingTranslateWorkflowPagePatterns as $pattern ) {
			if ( preg_match( $pattern, $groupId ) ) {
				$conf['published']['right'] = $wgFundraisingTranslateWorkflowPublishRight;
				break;
			}
		}
	}
}

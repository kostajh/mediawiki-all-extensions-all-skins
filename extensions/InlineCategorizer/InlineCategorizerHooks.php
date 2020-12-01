<?php
/**
 * Hooks for InlineCategorizer
 *
 * @file
 * @ingroup Extensions
 */

class InlineCategorizerHooks {

	/**
	 * @param OutputPage $out
	 */
	public static function beforePageDisplay( $out ) {
		global $wgInlineCategorizerNamespaces;

		// Only load if there are no restrictions, or if the current namespace
		// is in the array.
		if ( count( $wgInlineCategorizerNamespaces ) === 0
			|| in_array( $out->getTitle()->getNamespace(), $wgInlineCategorizerNamespaces )
		) {
			$out->addModules( 'ext.inlineCategorizer.core' );
		}
	}

}

<?php

namespace BlueSpice\Calumma\AssocLinksProvider;

use BlueSpice\Html\Descriptor\SimpleLink;

class CustomCallback extends SimpleLink {

	/**
	 *
	 * @param \IContextSource $context
	 * @param \Config $config
	 * @return ILink[]
	 */
	public static function factory( $context, $config ) {
		$callback = $config->get( 'BlueSpiceCalummaAssocLinksCustomCallback' );

		$links = [];
		if ( is_callable( $callback ) ) {
			$linkDefs = call_user_func_array( $callback, [ $context, $config ] );
			foreach ( $linkDefs as $linkDefKey => $linkDef ) {
				$links[$linkDefKey] = new static( $linkDef );
			}
		}

		return $links;
	}
}

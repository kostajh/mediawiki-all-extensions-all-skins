<?php

/**
 * File holding the SPSPageIterator class
 * 
 * @author Matt Yeh
 * @file
 * @ingroup SemanticPageSeries
 */
if ( !defined( 'SPS_VERSION' ) ) {
	die( 'This file is part of the SemanticPageSeries extension, it is not a valid entry point.' );
}

/**
 * The SPSPageIterator class.
 *
 * @ingroup SemanticPageSeries
 */
class SPSPageIterator extends SPSIterator {

	/**
	 * @return array An array containing the names of the parameters this iterator uses.
	 */
	function getParameterNames() {
		return array(
			'pagenames' => SPS_MANDATORY,
		);
	}

	/**
	 * @return an array of the values to be used in the target field of the target form
	 */
	function getValues( &$data ) {

		return explode(',', SPSUtils::fromArray( $data, 'pagenames' ));

	}

}

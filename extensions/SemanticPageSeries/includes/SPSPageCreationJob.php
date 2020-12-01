<?php

/**
 * File holding the SPSPageCreationJob class
 *
 * @author Stephan Gambke
 * @file
 * @ingroup SemanticPageSeries
 */
if ( !defined( 'SPS_VERSION' ) ) {
	die( 'This file is part of the SemanticPageSeries extension, it is not a valid entry point.' );
}

/**
 * The SPSPageCreationJob class.
 *
 * @ingroup SemanticPageSeries
 */
class SPSPageCreationJob extends Job {

	function __construct( $title, $params = '', $id = 0 ) {
		parent::__construct( 'spsCreatePage', $title, $params, $id );
	}

	/**
	 * Run the job
	 * @return boolean success
	 */
	function run() {

		global $wgUser;

		$oldUser = $wgUser;
		$wgUser = User::newFromId( $this->params['user'] );

		unset( $this->params['user'] );

		$this->params['form'] = $this->title->getText();
		$this->params['target'] = '';

		$handler = new SFAutoeditAPI( new ApiMain(), 'sfautoedit' );

		// TODO: Method is removed in SF 2.5 onwards. Remove the whole if-clause
		// when compatibility to SF pre2.5 is dropped
		if ( method_exists( $handler, 'isApiQuery' ) ) {
			$handler->isApiQuery( false );
		}

		$handler->setOptions( $this->params );

		// TODO: Method storeSemanticData is removed in SF 2.5 onwards. Clean this up
		// when compatibility to SF pre2.5 is dropped
		if ( method_exists( $handler, 'storeSemanticData' ) ) {
			$result = $handler->storeSemanticData( false );

			// wrap result in ok/error message
			if ( $result === true ) {
				$options = $handler->getOptions();
				$result = wfMessage( 'sf_autoedit_success', $options['target'], $options['form'] )->text();
			} else {
				$result = wfMsgReplaceArgs( '$1', array( $result ) );
			}
		} else {

			try {
				$handler->execute();
				$options = $handler->getOptions();
				$result = wfMessage( 'sf_autoedit_success', $options['target'], $options['form'] )->text();
			} catch ( MWException $e ) {
				$result = wfMsgReplaceArgs( '$1', array( $result ) );
			}
		}

		$this->params = array( 'result' => $result, 'user' => $wgUser->getName() );
		wfDebugLog( 'sps', 'Page Creation Job: ' . $result );

		$wgUser = $oldUser;

	}

}

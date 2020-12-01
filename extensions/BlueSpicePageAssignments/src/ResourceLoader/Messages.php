<?php

namespace BlueSpice\PageAssignments\ResourceLoader;

use MediaWiki\MediaWikiServices;

class Messages extends \ResourceLoaderModule {
	/**
	 * Get the messages needed for this module.
	 *
	 * To get a JSON blob with messages, use MessageBlobStore::get()
	 *
	 * @return array List of message keys. Keys may occur more than once
	 */
	public function getMessages() {
		$messages = parent::getMessages();
		$factory = MediaWikiServices::getInstance()->getService(
			'BSPageAssignmentsAssignableFactory'
		);
		foreach ( $factory->getRegisteredTypes() as $type ) {
			$assignable = $factory->factory( $type );
			if ( !$assignable ) {
				continue;
			}
			$messages[] = $assignable->getTypeMessageKey();
		}
		array_unique( $messages );
		return array_values( $messages );
	}

	/**
	 * Get target(s) for the module, eg ['desktop'] or ['desktop', 'mobile']
	 *
	 * @return array Array of strings
	 */
	public function getTargets() {
		return [ 'desktop', 'mobile' ];
	}

}

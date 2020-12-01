<?php

class PageBlockLogFormatter extends LogFormatter {

	protected function getMessageKey() {
		$key = parent::getMessageKey();
		$params = $this->extractParameters();
		if ( $params[4] ) {
			$key .= '-edit';
		}
		if ( $params[5] ) {
			$key .= '-move';
		}

		return $key;
	}

	protected function getMessageParameters() {
		if ( isset( $this->parsedParameters ) ) {
			return $this->parsedParameters;
		}

		parent::getMessageParameters();

		// @fixme the rest of the parameters need stuff done to them.
		$user = User::newFromName( $this->entry->getTarget()->getRootText() );
		$this->parsedParameters[2] = Message::rawParam( $this->makeUserLink( $user ) );
		$this->parsedParameters[3] = Message::rawParam( Linker::link( Title::newFromDBkey( $this->parsedParameters[3] ) ) );

		// Bad things happens if the numbers are not in correct order
		ksort( $this->parsedParameters );

		return $this->parsedParameters;
	}

}

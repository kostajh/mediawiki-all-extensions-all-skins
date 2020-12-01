<?php
/**
 * Implements FormSpecialPage and adds ability to show more messages on the form
 *
 * @file
 * @ingroup SpecialPage
 * @author Ike Hecht
 */
abstract class FormSpecialPageMessaged extends FormSpecialPage {

	/**
	 * Looks for a message of the form "{$this->getMessagePrefix}-{$messageSuffix}"
	 * If it exists, it returns the message, parsed as a block. If not, it returns
	 * the $messageSuffix function's parent
	 *
	 * @param string|null $messageSuffix The suffix of the message to search for. If null,
	 *     set to calling function's name
	 * @return string
	 */
	protected function getMessage( $messageSuffix = null ) {
		# http://stackoverflow.com/questions/190421/caller-function-in-php-5/190426#comment2248575_190426
		if ( $messageSuffix === null ) {
			list( , $caller ) = debug_backtrace( false );
			$messageSuffix = $caller['function'];
		}

		$message = $this->msg( $this->getMessagePrefix() . '-' . strtolower( $messageSuffix ) );
		if ( !$message->isDisabled() ) {
			return $message->parseAsBlock();
		}
		return parent::$messageSuffix();
	}

	protected function preText() {
		return $this->getMessage( __FUNCTION__ );
	}

	protected function postText() {
		return $this->getMessage( __FUNCTION__ );
	}

	public function onSuccess() {
		$this->getOutput()->wrapWikiMsg(
			"<div class=\"successbox\">\n$1\n</div><br clear=\"all\" />",
			$this->getMessagePrefix() . '-success'
		);

		$this->getOutput()->returnToMain();
	}
}

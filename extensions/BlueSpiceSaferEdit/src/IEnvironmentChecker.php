<?php

namespace BlueSpice\SaferEdit;

use IContextSource;
use Title;

interface IEnvironmentChecker {
	/**
	 * @param IContextSource $context
	 * @return IEnvironmentChecker
	 */
	public static function factory( IContextSource $context );

	/**
	 * Determines if current context is in edit mode
	 *
	 * @param bool &$result
	 * @return bool false to stop propagation
	 */
	public function isEditMode( &$result );

	/**
	 * Determines if edit warning should be shown in current context
	 *
	 * @param bool &$result
	 * @return bool false to stop propagation
	 */
	public function shouldShowWarning( &$result );

	/**
	 * Modify Title object that is being edited
	 *
	 * @param Title &$title
	 * @return bool
	 */
	public function getEditedTitle( &$title );
}

<?php
namespace BlueSpice\PageAssignments\Renderer;

use MediaWiki\MediaWikiServices;
use User;

class PageHeaderAssignmentUser extends PageHeaderAssignmentBase {
	const PARAM_ASSIGNMENT = 'assignment';

	/**
	 *
	 * @var IAssignment
	 */
	protected $assignment = null;

	/**
	 * Returns an array of arguments
	 * @return array
	 */
	public function getArgs() {
		$args = parent::getArgs();

		$user = User::newFromName( $this->assignment->getKey() );

		$util = MediaWikiServices::getInstance()->getService( 'BSUtilityFactory' );

		$userLink = $this->linkRenderer->makeLink(
			$user->getUserPage(),
			$util->getUserHelper( $user )->getDisplayName()
		);

		$args['text'] = $userLink;
		return $args;
	}
}

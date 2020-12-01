<?php
namespace BlueSpice\PageAssignments;

use Config;
use Title;
use User;

interface ITarget {
	/**
	 * @param Config $config
	 * @param array $assignments
	 * @param Title $title
	 * @return ITarget
	 */
	public static function factory( Config $config, array $assignments, Title $title );

	/**
	 *
	 * @return AssignmentFactory
	 */
	public function getFactory();

	/**
	 *
	 * @return IAssignment[]
	 */
	public function getAssignments();

	/**
	 *
	 * @return Title
	 */
	public function getTitle();

	/**
	 *
	 * @param User $user
	 * @return bool
	 */
	public function isUserAssigned( User $user );

	/**
	 *
	 * @return array - of user ids
	 */
	public function getAssignedUserIDs();

	/**
	 *
	 * @param User $user
	 * @return IAssignment[]
	 */
	public function getAssignmentsForUser( User $user );

	/**
	 *
	 * @param IAssignments[] $assignments1
	 * @param IAssignments[] $assignments2
	 * @return IAssignments[]
	 */
	public function diff( array $assignments1 = [], array $assignments2 = [] );

	/**
	 *
	 * @param IAssignment[] $assignments
	 */
	public function save( array $assignments = [] );

	/**
	 *
	 * @return boolean
	 */
	public function invalidate();
}

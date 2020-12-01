<?php
namespace BlueSpice\PageAssignments;

interface IAssignment {

	/**
	 * @return \BlueSpice\PageAssignments\Data\Record
	 */
	public function getRecord();

	/**
	 * @return array - of user ids that are assigned to this article
	 */
	public function getUserIds();

	/**
	 * @return string - identifier
	 */
	public function getId();

	/**
	 * @return string - type of the assignment
	 */
	public function getType();

	/**
	 * @return string - key of the assignment
	 */
	public function getKey();

	/**
	 * @return string - HTML, rendered anchor tag to the assignment
	 */
	public function getAnchor();

	/**
	 * @return string - Text of the assignment
	 */
	public function getText();

	/**
	 * @return \Title
	 */
	public function getTitle();

	/**
	 * @return integer
	 */
	public function getPosition();

	public function toStdClass();
}

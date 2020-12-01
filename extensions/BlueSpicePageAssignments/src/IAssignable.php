<?php
namespace BlueSpice\PageAssignments;

interface IAssignable {

	/**
	 * @return string
	 */
	public function getType();

	/**
	 * @return \BlueSpice\Data\IStore
	 */
	public function getStore();

	/**
	 * @return string
	 */
	public function getAssignmentClass();

	/**
	 * @return string
	 */
	public function getRendererKey();

	/**
	 * @return string
	 */
	public function getTypeMessageKey();
}

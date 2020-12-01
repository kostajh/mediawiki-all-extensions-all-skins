<?php

namespace BlueSpice\ContextMenu;

interface IMenuItem {

	/**
	 * @return \Message
	 */
	public function getLabelMessage();

	/**
	 * @return string
	 */
	public function getUrl();

	/**
	 *
	 * e.g. "bs.pageassignments.contextmenu.handler"
	 *
	 * @return string
	 */
	public function getJSHandler();

	/**
	 * @return string
	 */
	public function getIconClass();

	/**
	 * @return string
	 */
	public function getId();

	/**
	 * @return IMenuItem[]
	 */
	public function getChildren();

	/**
	 * @param \IContextSource $context
	 * @return bool
	 */
	public function shouldList( $context );

	/**
	 * @return int
	 */
	public function getPosition();
}

<?php

namespace BlueSpice\Calumma;

interface IPanel {

	/**
	 * @return string A unique Id. ATTENTION: It must be persisent across sessions and
	 * requests!
	 */
	public function getHtmlId();

	/**
	 * @return \Message
	 */
	public function getTitleMessage();

	/**
	 * @return string
	 */
	public function getBody();

	/**
	 * @return Panel\ITool[]
	 */
	public function getTool();

	/**
	 * @return Panel\IBadge[]
	 */
	public function getBadge();

	/**
	 * @return string[]
	 */
	public function getTriggerRLDependencies();

	/**
	 * @return string
	 */
	public function getTriggerCallbackFunctionName();

	/**
	 * @return string[]
	 */
	public function getContainerClasses();

	/**
	 * @return array
	 */
	public function getContainerData();

	/**
	 *
	 * @param \IContextSource $context
	 * @return bool
	 */
	public function shouldRender( $context );

	/**
	 *
	 * @return bool
	 */
	public function getPanelCollapseState();
}

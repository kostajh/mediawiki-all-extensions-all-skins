<?php

namespace BlueSpice\Calumma;

interface IFlyout {

	/**
	 * @return \Message
	 */
	public function getFlyoutTitleMessage();

	/**
	 * @return \Message
	 */
	public function getFlyoutIntroMessage();
}

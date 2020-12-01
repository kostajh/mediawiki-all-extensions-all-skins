<?php

namespace BlueSpice\Social\Profile;

interface IField {
	/**
	 * @return mixed
	 */
	public function getValue();

	/**
	 * @return string
	 */
	public function getName();

	/**
	 * @return string
	 */
	public function getLabel();

	/**
	 * @return boolean
	 */
	public function isHidden();
}

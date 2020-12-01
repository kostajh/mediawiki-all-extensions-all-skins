<?php

namespace BlueSpice\UserInfo;

interface IMetaData {
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
	 * @return bool
	 */
	public function isHidden();

	/**
	 * @return bool
	 */
	public function isTitle();

	/**
	 * @return bool
	 */
	public function isSubTitle();

	/**
	 * @return array
	 */
	public function toArray();
}

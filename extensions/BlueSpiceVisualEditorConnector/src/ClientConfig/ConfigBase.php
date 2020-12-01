<?php

namespace BlueSpice\VisualEditorConnector\ClientConfig;

abstract class ConfigBase implements IVisualEditorConnectorClientConfigVariable {

	/**
	 * @return static
	 */
	public static function factory() {
		return new static();
	}
}

<?php

namespace MediaWiki\Extension\FlexiSkin\Api;

use ApiUsageException;
use FormatJson;
use MediaWiki\Extension\FlexiSkin\IFlexiSkin;
use MWStake\MediaWiki\Component\CommonUserInterface\LessVars;

class Save extends FlexiSkinOperation {
	/**
	 * @inheritDoc
	 */
	protected function getAllowedParams() {
		return parent::getAllowedParams() + [
			'config' => [
				static::PARAM_TYPE => 'string',
				static::PARAM_REQUIRED => false,
				static::PARAM_HELP_MSG => 'apihelp-flexiskin-save-param-config',
			]
		];
	}

	protected function getParameterFromSettings( $name, $settings, $parseLimit ) {
		$value = parent::getParameterFromSettings( $name, $settings, $parseLimit );
		if ( $name === 'config' ) {
			$value = FormatJson::decode( $value, true );
			if ( !is_array( $value ) ) {
				$value = [];
			}
		}

		return $value;
	}

	/**
	 * @return bool
	 * @throws ApiUsageException
	 */
	protected function executeAction() {
		$flexiSkin = $this->getFlexiSkin();
		$newSkin = $this->replaceConfig( $flexiSkin, $this->getParameter( 'config' ) );
		$filteredConfig = $this->filterOutDefaults(
			$flexiSkin,
			$newSkin
		);

		$newSkin = $this->replaceConfig( $newSkin, $filteredConfig );

		return $this->executeOperationOnSkin( $newSkin );
	}

	/**
	 * @param IFlexiSkin $flexiSkin
	 * @return bool
	 */
	protected function executeOperationOnSkin( IFlexiSkin $flexiSkin ) {
		return $this->flexiSkinManager->save( $flexiSkin ) &&
			$this->flexiSkinManager->setActive( true );
	}

	/**
	 * @return bool
	 */
	protected function mustExist(): bool {
		return false;
	}

	/**
	 * Filter out all of the values that were no supposed to go to the skin
	 *
	 * @param IFlexiSkin $old
	 * @param IFlexiSkin $new
	 * @return array
	 */
	private function filterOutDefaults( IFlexiSkin $old, IFlexiSkin $new ) {
		$allVars = LessVars::getInstance()->getAllVars();
		$config = $new->getConfig();
		foreach ( $this->flexiSkinManager->getPlugins() as $pluginKey => $plugin ) {
			$map = $plugin->getLessVarsMap();
			$lessVars = $plugin->getLessVars( $new );
			foreach ( $lessVars as $var => $value ) {
				if (
					!$this->isVarPreviouslySet( $map[$var], $old ) &&
					$this->isDefaultValue( $var, $value, $allVars )
				) {
					$this->unsetFromConfig( $config, $map[$var] );
				}
			}
		}

		return $this->removeEmptyKeys( $config );
	}

	/**
	 * @param array &$config
	 * @param string $path
	 * @return void
	 */
	private function unsetFromConfig( array &$config, $path ) {
		$bits = explode( '/', $path );
		$i = 0;
		while ( $i < count( $bits ) - 1 ) {
			$bit = $bits[$i];
			if ( !is_array( $config ) || !array_key_exists( $bit, $config ) ) {
				return null;
			}
			$config = &$config[$bit];
			$i++;
		}
		$bit = end( $bits );
		unset( $config[$bit] );
	}

	/**
	 * @param array $config
	 * @return array
	 */
	private function removeEmptyKeys( array $config ) {
		foreach ( $config as $key => $value ) {
			if ( is_array( $value ) ) {
				if ( empty( $value ) ) {
					unset( $config[$key] );
				} else {
					$config[$key] = $this->removeEmptyKeys( $value );
				}
			}
		}

		return $config;
	}

	/**
	 * Check if the variable var already set
	 *
	 * @param string $path
	 * @param IFlexiSkin $skin
	 * @return bool
	 */
	private function isVarPreviouslySet( $path, IFlexiSkin $skin ) {
		$value = $skin->getValueForPath( $path );
		return $value !== null;
	}

	/**
	 * Check if the value set is the default value
	 *
	 * @param string $var
	 * @param mixed $value
	 * @param array $allVars
	 * @return bool
	 */
	private function isDefaultValue( $var, $value, array $allVars ) {
		return isset( $allVars[$var] ) && $allVars[$var] === $value;
	}
}

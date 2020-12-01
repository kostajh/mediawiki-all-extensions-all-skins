<?php

use MediaWiki\MediaWikiServices;

abstract class SpecialDashboard extends \BlueSpice\SpecialPage {

	/**
	 *
	 * @param string $sParameter
	 */
	public function execute( $sParameter ) {
		parent::execute( $sParameter );

		$this->checkForReadOnly();

		$this->getPortalConfig();
		$this->getOutput()->addJsConfigVars(
			'bsPortalConfigSavebackend', $this->getSaveBackend()
		);
		$this->getOutput()->addJsConfigVars(
			'bsPortalConfigLocation', $this->getLocation()
		);

		$this->addModules( $this->getOutput() );
		$this->getOutput()->addHTML(
			Html::element( 'div', [ 'id' => $this->getContainerId() ] )
		);
	}

	/**
	 * @throws Exception
	 */
	protected function getPortalConfig() {
		$oDbr = wfGetDB( DB_REPLICA );
		$res = $oDbr->select(
			'bs_dashboards_configs',
			'*',
			$this->getConds(),
			__METHOD__
		);

		$dbConfig = null;
		if ( $oDbr->numRows( $res ) > 0 ) {
			$row = $oDbr->fetchObject( $res );
			$dbConfig = FormatJson::decode( $row->dc_config, 1 );
		}

		$isDefault = true;
		$portalConfig = [
			[],
			[],
		];

		$hookContainer = MediaWikiServices::getInstance()->getHookContainer();
		$hookContainer->run( 'BSDashboardsUserDashboardPortalConfig', [
			$this,
			&$portalConfig,
			$isDefault
		] );

		$outputConfig = $this->balance( $portalConfig );
		if ( $dbConfig !== null ) {
			$outputConfig = $dbConfig;
		}

		$this->getOutput()->addJsConfigVars(
			'bsPortalDependencies', $this->extractModules( $dbConfig, $portalConfig )
		);
		$this->getOutput()->addJsConfigVars(
			'bsPortalConfig',
			$outputConfig
		);
	}

	/**
	 * Get conditions for the query
	 *
	 * @return array
	 */
	abstract protected function getConds(): array;

	/**
	 * @return bool
	 */
	private function checkForReadOnly() {
		if ( wfReadOnly() ) {
			global $wgReadOnly;
			$msg = $this->msg( 'bs-readonly', $wgReadOnly );
			$this->getOutput()->addHTML(
				'<script>var wgReadOnly = true; alert("' . $msg->escaped() . '");</script>'
			);

			return true;
		}

		return false;
	}

	/**
	 * Not used currently - please dont delete
	 *
	 * @param array $config
	 * @return array
	 */
	private function balance( array $config ) {
		if ( count( $config ) !== 2 ) {
			return $config;
		}
		if ( abs( count( $config[0] ) - count( $config[1] ) ) < 3 ) {
			return $config;
		}

		$greater = count( $config[0] ) > count( $config[1] ) ? 0 : 1;
		$smaller = (int)!$greater;
		$evenDiff = floor( ( count( $config[$greater] ) - count( $config[$smaller] ) ) / 2 );
		$config[$smaller] = array_merge(
			$config[$smaller], array_slice( $config[$greater], -$evenDiff, $evenDiff )
		);
		$config[$greater] = array_diff( $config[$greater], $config[$smaller] );

		return $config;
	}

	/**
	 * Get key for the save backend
	 * @return string
	 */
	abstract protected function getSaveBackend(): string;

	/**
	 * Get location key
	 * @return string
	 */
	abstract protected function getLocation(): string;

	/**
	 * Get HTML container id
	 *
	 * @return string
	 */
	abstract protected function getContainerId(): string;

	/**
	 * @param OutputPage $out
	 */
	protected function addModules( OutputPage $out ) {
		// STUB
	}

	/**
	 * Add modules from portlets
	 *
	 * @param array|null $dbConfig
	 * @param array $portalConfig
	 * @return array
	 */
	protected function extractModules( ?array $dbConfig, array $portalConfig ) {
		$modules = $this->getModulesFromConfig( $portalConfig );

		if ( $dbConfig !== null ) {
			$allModules = [];
			$dbPortlets = array_merge( ...$dbConfig );
			foreach ( $dbPortlets as $portlet ) {
				if ( isset( $portlet['type'] ) && isset( $modules[$portlet['type']] ) ) {
					$allModules = array_merge( $allModules, $modules[$portlet['type']] );
				}
			}
			return array_unique( $allModules );
		} else {
			$modules = array_values( $modules );
			return array_unique( array_merge( ...$modules ) );
		}
	}

	/**
	 * Get all required RL modules from portal config retrieved by hook
	 *
	 * @param array $portalConfig
	 * @return array
	 */
	protected function getModulesFromConfig( $portalConfig ) {
		$all = array_merge( ...$portalConfig );
		$modules = [];
		foreach ( $all as $portlet ) {
			if ( !isset( $portlet['modules'] ) || !isset( $portlet['type'] ) ) {
				continue;
			}
			$portletModules = $portlet['modules'];
			if ( !is_array( $portletModules ) ) {
				$portletModules = [ $portletModules ];
			}
			$modules[$portlet['type']] = $portletModules;
		}

		return $modules;
	}

	/**
	 * @param string $portletType
	 * @param array $defaults
	 * @return array|null
	 */
	protected function getPortletForType( $portletType, array $defaults ) {
		foreach ( $defaults as $col ) {
			foreach ( $col as $default ) {
				if ( $default['type'] === $portletType ) {
					return $default;
				}
			}
		}

		return null;
	}

}

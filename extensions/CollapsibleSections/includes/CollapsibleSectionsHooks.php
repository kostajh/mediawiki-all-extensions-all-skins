<?php

namespace MediaWiki\Extension\CollapsibleSections;

use Config;
use MediaWiki\Hook\BeforePageDisplayHook;
use MediaWiki\ResourceLoader\Hook\ResourceLoaderGetConfigVarsHook;
use OutputPage;
use Skin;

/**
 * @license MIT
 */
class CollapsibleSectionsHooks implements
	BeforePageDisplayHook,
	ResourceLoaderGetConfigVarsHook
{

	/**
	 * @param OutputPage $out
	 * @param Skin $skin
	 */
	public function onBeforePageDisplay( $out, $skin ) : void {
		$out->addModules( 'ext.collapsiblesections.main' );
	}

	/**
	 * @param array &$vars
	 * @param string $skin
	 * @param Config $config
	 */
	public function onResourceLoaderGetConfigVars( &$vars, $skin, Config $config ) : void {
		$vars['wgCollapsibleSectionsCollapsedByDefault'] = $config->get( 'CollapsibleSectionsCollapsedByDefault' );
		$vars['wgCollapsibleSectionsEnableDesktop'] = $config->get( 'CollapsibleSectionsEnableDesktop' );
		$vars['wgCollapsibleSectionsTag'] = $config->get( 'CollapsibleSectionsTag' );
	}

}

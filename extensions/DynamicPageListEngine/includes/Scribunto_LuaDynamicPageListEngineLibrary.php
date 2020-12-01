<?php

/**
 * @brief Class LuaDynamicPageListEngineLibrary.
 *
 * @file
 *
 * @ingroup Extensions
 * @ingroup Extensions-DynamicPageListEngine
 *
 * @author [RV1971](https://www.mediawiki.org/wiki/User:RV1971)
 *
 */

/**
 * @brief
 * [Scribunto](https://www.mediawiki.org/wiki/Extension:Scribunto) Lua
 * interface to DynamicPageListEngine.
 *
 * @ingroup Extensions
 * @ingroup Extensions-DynamicPageListEngine
 */
class Scribunto_LuaDynamicPageListEngineLibrary
extends Scribunto_LuaLibraryBase {

	/* == static methods == */

	/**
	 * @brief [ScribuntoExternalLibraries]
	 * (https://www.mediawiki.org/wiki/Extension:Scribunto/Lua_reference_manual#Library) hook.
	 *
	 * Register this library.
	 *
	 * @param Scribunto_LuaEngine $engine Scribunto engine.
	 *
	 * @param array &$extraLibraries Libraries to register.
	 *
	 * @return bool
	 */
	public static function onScribuntoExternalLibraries( $engine,
		array &$extraLibraries ) {
		$extraLibraries['mw.ext.dpl'] =
			'Scribunto_LuaDynamicPageListEngineLibrary';

		return true;
	}

	/* == magic methods == */

	/**
	 * @brief Constructor.
	 *
	 * @param Scribunto_LuaEngine $engine Scribunto engine.
	 */
	public function __construct( $engine ) {
		parent::__construct( $engine );
	}

	/* == special functions == */

	/// Register this library.
	public function register() {
		$lib = [
			'getFullpagenames' => [ $this, 'getFullpagenames' ],
			'getPagenames' => [ $this, 'getPagenames' ],
			'getPages' => [ $this, 'getPages' ]
		];

		$this->getEngine()->registerInterface(
			__DIR__ . '/../lua/DynamicPageListEngine.lua',
			$lib, [] );
	}

	/* == Functions to be called from Lua == */

	/**
	 * @brief Get an array of pages from the database.
	 *
	 * @param array $params Array of parameters.
	 *
	 * @param string $method Method to transform the result.
	 *
	 * @return array Result of $method applied to the query
	 * result. For the default value `toArrays`, return
	 * numerically-indexed array of associative arrays, each of which
	 * represents a page. See DpleFeatureResults::toArrays() for
	 * details.
	 */
	public function getPages( $params, $method = 'toArrays' ) {
		/** Increment the [expensive function count]
		 * (https://www.mediawiki.org/wiki/Manual:$wgExpensiveParserFunctionLimit).
		 */
		if ( !$this->getParser()->incrementExpensiveFunctionCount() ) {
			throw new Scribunto_LuaError( wfMessage(
					'dple-error-too-many-expensive-functions' )->text() );
		}

		/** Add the page to the [tracking category]
		 * (https://www.mediawiki.org/wiki/Help:Tracking_categories)
		 * `dple-tracking-category`.
		 */
		$this->getParser()->addTrackingCategory( 'dple-tracking-category' );

		$dpl = new DynamicPageListEngine( $params );

		$pages = $dpl->getResult( $method );

		/* Renumber the records starting with 1, to match the Lua
		 * convention. */
		return [ $pages
				 ? array_combine( range( 1, count( $pages ) ), $pages )
				 : null ];
	}

	/**
	 * @brief Get an array of full page names from the database.
	 *
	 * @param array $params Array of parameters.
	 *
	 * @return array Numerically-indexed array of full page names.
	 */
	public function getFullpagenames( $params ) {
		return $this->getPages( $params, 'toFullpagenames' );
	}

	/**
	 * @brief Get an array of page names from the database.
	 *
	 * @param array $params Array of parameters.
	 *
	 * @return array Numerically-indexed array of page names.
	 */
	public function getPagenames( $params ) {
		return $this->getPages( $params, 'toPagenames' );
	}
}

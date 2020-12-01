<?php

class PageToolsHooks {

	/**
	 * @param Parser &$parser
	 *
	 * @return bool
	 */
	public static function onPageToolsSetupParserFunction( Parser &$parser ) {
		$mgwords = [
			'pageincategory'    => 'PageTools::renderPageInCategory',
			'pagenumcategories' => 'PageTools::renderPageNumCategories',
			'pagecategory'      => 'PageTools::renderPageCategory',
			'pagetitle'         => 'PageTools::renderPageTitle',
			'pagetitleadd'      => 'PageTools::renderPageTitleAdd',
			'pagesubtitle'      => 'PageTools::renderPageSubtitle',
		];

		// Create function hooks associating the magic words with the render functions
		foreach ( $mgwords as $word => $handler ) {
			$parser->setFunctionHook( $word, $handler );
		}

		$parser->setHook( 'deflate', 'PageTools::renderDeflate' );

		// Return true so that MediaWiki continues to load extensions.
		return true;
	}
}

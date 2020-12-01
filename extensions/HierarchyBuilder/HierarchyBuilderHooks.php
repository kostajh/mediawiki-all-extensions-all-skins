<?php

class HierarchyBuilderHooks {

	/**
	 * @param $parser Parser
	 * @return bool
	 */
	public static function efHierarchyBuilderSetup ( & $parser ) {
		$parser->setFunctionHook( 'hierarchyBreadcrumb',
			'HierarchyBuilder::hierarchyBreadcrumb' );
		$parser->setFunctionHook( 'hierarchySectionNumber', 'HierarchyBuilder::hierarchySectionNumber' );
		$parser->setFunctionHook( 'hierarchyParent', 'HierarchyBuilder::hierarchyParent' );
		$parser->setFunctionHook( 'hierarchyChildren', 'HierarchyBuilder::hierarchyChildren' );
		$parser->setFunctionHook( 'hierarchySubtree', 'HierarchyBuilder::hierarchySubtree' );
		$parser->setFunctionHook( 'hierarchySelected', 'HierarchyBuilder::hierarchySelected' );

		$parser->setHook( 'hierarchy', 'HierarchyBuilder::renderHierarchy' );
		$parser->setHook( 'hierarchySelected', 'HierarchyBuilder::renderHierarchySelected' );
		global $wgPageFormsFormPrinter;
		$wgPageFormsFormPrinter->registerInputType( 'HierarchyFormInput' );
		$wgPageFormsFormPrinter->registerInputType( 'HierarchySelectFormInput' );
		return true;
	}

}

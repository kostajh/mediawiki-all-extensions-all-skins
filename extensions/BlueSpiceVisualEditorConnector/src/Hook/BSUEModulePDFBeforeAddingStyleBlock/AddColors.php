<?php

namespace BlueSpice\VisualEditorConnector\Hook\BSUEModulePDFBeforeAddingStyleBlock;

use BlueSpice\UEModulePDF\Hook\BSUEModulePDFBeforeAddingStyleBlocks;

class AddColors extends BSUEModulePDFBeforeAddingStyleBlocks {

	private $lessFilename = 'ext.bluespice.visualEditorConnector.colors.less';

	protected function doProcess() {
		$lessFilepathname = $this->makeLessFilePathname();
		$compiler = $this->getContext()->getOutput()->getResourceLoader()->getLessCompiler();
		$lessCode = file_get_contents( $lessFilepathname );
		$this->styleBlocks['VisualEditorConnector'] = $compiler->parse( $lessCode )->getCss();

		return true;
	}

	private function makeLessFilePathname() {
		$basepath = dirname( dirname( dirname( __DIR__ ) ) );
		return "$basepath/resources/{$this->lessFilename}";
	}

}

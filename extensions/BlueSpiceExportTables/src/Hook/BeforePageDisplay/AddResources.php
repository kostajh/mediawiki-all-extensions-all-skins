<?php

namespace BlueSpice\ExportTables\Hook\BeforePageDisplay;

class AddResources extends \BlueSpice\Hook\BeforePageDisplay {

	protected function doProcess() {
		$this->out->addModuleStyles(
			'ext.bluespice.exportTables.CRUDGridPanel.button.styles'
		);
		$this->out->addModuleStyles(
			'ext.bluespice.exportTables.contentTables.styles'
		);
		$this->out->addModules(
			'ext.bluespice.exportTables.CRUDGridPanel.button'
		);
		$this->out->addModules( 'ext.bluespice.exportTables.contentTables' );

		$this->out->addJsConfigVars(
			'bsgExportTablesMenuTargetSelector',
			$this->getConfig()->get( 'ExportTablesMenuTargetSelector' )
		);
	}

}

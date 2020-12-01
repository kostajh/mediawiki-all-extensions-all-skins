ve.ui.SmartListInspectorTool = function VeUiSmartListInspectorTool( toolGroup, config ) {
	ve.ui.SmartListInspectorTool.super.call( this, toolGroup, config );
};
OO.inheritClass( ve.ui.SmartListInspectorTool, ve.ui.FragmentInspectorTool );
ve.ui.SmartListInspectorTool.static.name = 'smartListTool';
ve.ui.SmartListInspectorTool.static.group = 'none';
ve.ui.SmartListInspectorTool.static.autoAddToCatchall = false;
ve.ui.SmartListInspectorTool.static.icon = 'smartlist'; //To be added
ve.ui.SmartListInspectorTool.static.title = OO.ui.deferMsg(
	'bs-smartlist-ve-smartlist-title'
);
ve.ui.SmartListInspectorTool.static.modelClasses = [ ve.dm.SmartListNode ];
ve.ui.SmartListInspectorTool.static.commandName = 'smartListCommand';
ve.ui.toolFactory.register( ve.ui.SmartListInspectorTool );

ve.ui.commandRegistry.register(
	new ve.ui.Command(
		'smartListCommand', 'window', 'open',
		{ args: [ 'smartListInspector' ], supportedSelections: [ 'linear' ] }
	)
);


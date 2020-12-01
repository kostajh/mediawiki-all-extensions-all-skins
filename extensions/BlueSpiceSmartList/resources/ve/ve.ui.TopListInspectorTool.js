ve.ui.TopListInspectorTool = function VeUiTopListInspectorTool( toolGroup, config ) {
	ve.ui.TopListInspectorTool.super.call( this, toolGroup, config );
};
OO.inheritClass( ve.ui.TopListInspectorTool, ve.ui.FragmentInspectorTool );
ve.ui.TopListInspectorTool.static.name = 'topListTool';
ve.ui.TopListInspectorTool.static.group = 'none';
ve.ui.TopListInspectorTool.static.autoAddToCatchall = false;
ve.ui.TopListInspectorTool.static.icon = 'toplist'; //To be added
ve.ui.TopListInspectorTool.static.title = OO.ui.deferMsg(
	'bs-smartlist-ve-toplist-title'
);
ve.ui.TopListInspectorTool.static.modelClasses = [ ve.dm.TopListNode ];
ve.ui.TopListInspectorTool.static.commandName = 'topListCommand';
ve.ui.toolFactory.register( ve.ui.TopListInspectorTool );

ve.ui.commandRegistry.register(
	new ve.ui.Command(
		'topListCommand', 'window', 'open',
		{ args: [ 'topListInspector' ], supportedSelections: [ 'linear' ] }
	)
);


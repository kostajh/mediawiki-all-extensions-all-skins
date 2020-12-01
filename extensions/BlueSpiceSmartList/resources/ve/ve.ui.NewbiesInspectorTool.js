ve.ui.NewbiesInspectorTool = function VeUiNewbiesInspectorTool( toolGroup, config ) {
	ve.ui.NewbiesInspectorTool.super.call( this, toolGroup, config );
};
OO.inheritClass( ve.ui.NewbiesInspectorTool, ve.ui.FragmentInspectorTool );
ve.ui.NewbiesInspectorTool.static.name = 'newbiesTool';
ve.ui.NewbiesInspectorTool.static.group = 'none';
ve.ui.NewbiesInspectorTool.static.autoAddToCatchall = false;
ve.ui.NewbiesInspectorTool.static.icon = 'newbies'; //To be added
ve.ui.NewbiesInspectorTool.static.title = OO.ui.deferMsg(
	'bs-smartlist-ve-newbies-title'
);
ve.ui.NewbiesInspectorTool.static.modelClasses = [ ve.dm.NewbiesNode ];
ve.ui.NewbiesInspectorTool.static.commandName = 'newbiesCommand';
ve.ui.toolFactory.register( ve.ui.NewbiesInspectorTool );

ve.ui.commandRegistry.register(
	new ve.ui.Command(
		'newbiesCommand', 'window', 'open',
		{ args: [ 'newbiesInspector' ], supportedSelections: [ 'linear' ] }
	)
);


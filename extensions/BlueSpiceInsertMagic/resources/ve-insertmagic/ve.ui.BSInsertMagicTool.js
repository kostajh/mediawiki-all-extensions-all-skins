/*!
 * VisualEditor UserInterface BSInsertMagicTool class.
 *
 * @copyright 2018 Hallo Welt! GmbH
 * @license GPL-3.0-only
 */

/**
 * MediaWiki UserInterface checklist tool.
 *
 * @class
 * @extends ve.ui.FragmentInspectorTool
 * @constructor
 * @param {OO.ui.ToolGroup} toolGroup
 * @param {Object} [config] Configuration options
 */

ve.ui.BSInsertMagicTool = function VeUiBSInsertMagicTool( toolGroup, config ) {
	ve.ui.BSInsertMagicTool.super.call( this, toolGroup, config );
};
OO.inheritClass( ve.ui.BSInsertMagicTool, ve.ui.FragmentWindowTool );
ve.ui.BSInsertMagicTool.static.name = 'bsInsertMagicTool';
ve.ui.BSInsertMagicTool.static.group = 'object';
ve.ui.BSInsertMagicTool.static.icon = 'insertmagic';
ve.ui.BSInsertMagicTool.static.title = OO.ui.deferMsg(
	'bs-insertmagic-ve-insertmagic-title'
);
//ve.ui.BSInsertMagicTool.static.modelClasses = [ ve.dm.BSChecklistNode ];
ve.ui.BSInsertMagicTool.static.commandName = 'bsInsertMagic';
ve.ui.toolFactory.register( ve.ui.BSInsertMagicTool );

/*
// Keyboard shortcut
ve.ui.sequenceRegistry.register(
	new ve.ui.Sequence( 'wikitextChecklist', 'bsChecklistCommand', 'check', 5 )
);

ve.ui.commandHelpRegistry.register( 'insert', 'checklist', {
	sequences: [ 'wikitextChecklist' ],
	label: OO.ui.deferMsg( 'score-visualeditor-mwscoreinspector-title' )
} );
*/
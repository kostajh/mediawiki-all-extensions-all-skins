var registry = bs.vec.registry.TableStyle.registry, key;
for( key in registry ) {
	if ( !registry.hasOwnProperty( key ) ) {
		continue;
	}
	ve.ui.commandRegistry.register(
		new ve.ui.Command(
			key, 'bs-vec-table', key,
			{ supportedSelections: [ 'table' ] }
		)
	);
}

ve.ui.commandRegistry.register(
	new ve.ui.Command(
		'duplicateRow', 'bs-vec-table', 'duplicate',
		{ supportedSelections: [ 'table' ], args: [ "row" ] }
	)
);

ve.ui.commandRegistry.register(
	new ve.ui.Command(
		'duplicateColumn', 'bs-vec-table', 'duplicate',
		{ supportedSelections: [ 'table' ], args: [ "col" ] }
	)
);

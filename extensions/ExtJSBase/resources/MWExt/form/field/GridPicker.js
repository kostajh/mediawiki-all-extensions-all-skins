Ext.define('MWExt.form.field.GridPicker', {
	extend: 'Ext.form.field.ComboBox',
	alias: 'widget.gridpicker',
	requires: ['Ext.grid.Panel', 'MWExt.form.field.GridPickerKeyNav'],
	/**
	 * Configuration object for the picker grid. It will be merged with
	 * {@link #defaultGridConfig} before creating the grid with
	 * {@link #createPicker}.
	 *
	 * @cfg {Object}
	 */
	gridConfig: null,
	defaultGridConfig: {
		minWidth: 70,
		minHeight: 70,
		maxHeight: 250,
		hideMode: 'offsets',
		initComponent: function() {
			Ext.grid.Panel.prototype.initComponent.apply(this, arguments);
			var store = this.getStore();
			this.query('pagingtoolbar').forEach(function(pagingToolbar) {
				pagingToolbar.bindStore(store);
			});
		}
	},
	/**
	 * @method
	 * Creates and returns the component to be used as this field's picker. Must be implemented by subclasses.
	 * The current field should also be passed as a configuration option to the picker component as the pickerField
	 * property.
	 */
	createPicker: function() {
		var me = this,
			grid,
			gridCfg = Ext.apply({
				xtype: 'grid',
				hideHeaders: true,
				autoScroll: true,
				floating: true,
				hidden: true,
				focusOnToFront: false,
				rowLines: false,
				pickerField: me,
				selModel: {
					mode: me.multiSelect ? 'SIMPLE' : 'SINGLE'
				},
				width: me.getWidth(),
				store: me.store,
				columns: [{
					dataIndex: this.displayField || this.valueField,
					flex: 1
				}]
			}, me.gridConfig, me.defaultGridConfig);
		grid = me.picker = Ext.widget(gridCfg);

		/**
		 * 2018-03-20, rvogel
		 * HACK: ExtJS6 requires the `Ext.Component` returned by this method to
		 * implement the `refresh` method. But this is more of a `Ext.view.View`
		 * functionality. Therefore we create this simple proxy method
		 * on-the-fly
		 */
		grid.refresh = function() {
			return grid.getView().refresh.apply( grid.getView(), arguments );
		};

		this.bindPicker(grid);
		return this.picker = grid;
	},
	/**
	 * @private
	 * Enables the key nav for the gridPicker when it is expanded.
	 */
	onExpand: function() {
		var me = this,
			keyNav = me.listKeyNav,
			selectOnTab = me.selectOnTab;

		if (keyNav) {
			keyNav.enable();
		} else {
			keyNav = me.listKeyNav = new MWExt.form.field.GridPickerKeyNav( {
				target: this.inputEl,
				forceKeyDown: true,
				pickerField: this,
				grid: this.getPicker()
			});
		}
		if (selectOnTab) {
			me.ignoreMonitorTab = true;
		}
		Ext.defer(keyNav.enable, 1, keyNav); //wait a bit so it doesn't react to the down arrow opening the picker
		me.inputEl.focus();
		//this.focusWithoutSelection(10);
	},

	/**
	 * Binds the specified grid to this picker.
	 *
	 * @param {Ext.grid.Panel}
	 * @private
	 */
	bindPicker: function(grid) {
		grid.ownerCt = this;
		grid.registerWithOwnerCt();
		this.mon(grid, {
			scope: this,
			itemclick: this.onItemClick,
			beforeselect: this.onBeforeSelect,
			beforedeselect: this.onBeforeDeselect
		});
		this.mon( grid.getView(), {
			scope: this,
			refresh: this.onListRefresh,
			selectionchange: this.onListSelectionChange
		} );

		// Prevent deselectAll, that is called liberally in combo box code, to
		// actually deselect
		// the current value
		var me = this,
			sm = grid.getSelectionModel(),
			uber = sm.deselectAll;
		sm.deselectAll = function() {
			if (!me.ignoreSelection) {
				uber.apply(this, arguments);
			}
		};
	},

	onItemClick: function ( grid, record, item, index, e, eOpts ) {
		if( record instanceof Ext.data.Model === false ) {
			return;
		}
		this.setValue( record );
		this.collapse();
		this.inputEl.focus();
	},

	onListSelectionChange: function( gridView, selected, eOpts ) {
		if( selected instanceof Ext.data.Model === false ) {
			return;
		}
		this.setValue( selected );
		this.collapse();
		this.inputEl.focus();
	},

	onListRefresh: function( gridView, eOpts ) {
		// Picker will be aligned during the expand call
		if (!this.expanding) {
			this.alignPicker();
		}
	},

	// @private
	onTypeAhead: function() {
		var me = this,
			displayField = me.displayField,
			record = me.store.findRecord(displayField, me.getRawValue()),
			newValue, len, selStart;

		if (record) {
			newValue = record.get(displayField);
			len = newValue.length;
			selStart = me.getRawValue().length;
			this.highlightAt(record);
			if (selStart !== 0 && selStart !== len) {
				me.setRawValue(newValue);
				me.selectText(selStart, newValue.length);
			}
		}
	},
	/**
	 * @private
	 * If the autoSelect config is true, and the picker is open, highlights the first item.
	 */
	doAutoSelect: function() {
		var me = this,
			picker = me.picker,
			lastSelected;
		if (picker && me.autoSelect && me.store.getCount() > 0) {
			// Highlight the last selected item and scroll it into view
			lastSelected = picker.getSelectionModel().getLastSelected();
			if (picker.store.indexOf(lastSelected) != -1) {
				this.highlightAt(lastSelected);
			}
		}
	},
	//Overridden to ignore selectionchange in grid on query
	doQuery: function() {
		this.ignoreSelection++;
		this.callParent(arguments);
		this.ignoreSelection--;
	},
	//Overridden to scroll selection into view
	afterQuery: function(queryPlan) {
		var sm = this.getPicker().getSelectionModel()
		this.callParent(arguments);
		if (sm.hasSelection()) {
			this.highlightAt(sm.getSelection()[0]);
		}
	},
	/**
	 * Highlight the record at the specified index.
	 *
	 * @param {Integer} or {@link Ext.data.Model}
	 *            index
	 * @private
	 */
	highlightAt: function(index) {
		var grid = this.getPicker(),
			sm = grid.getSelectionModel(),
			view = grid.getView(),
			node = view.getNode(index),
			plugins = grid.plugins,
			bufferedPlugin = plugins && plugins.filter(function(p) {
				return p instanceof Ext.grid.plugin.BufferedRenderer
			})[0];
		if (!(typeof index === "number")) {
			index = grid.store.indexOf(index);
		}
		sm.select(index, false, true);

		if (node) {
			Ext.fly(node).scrollIntoView(view.el, false);
		} else if (bufferedPlugin) {
			bufferedPlugin.scrollTo(index);
		}
	}
});

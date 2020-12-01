Ext.define( 'BS.PageAssignments.flyout.model.AssigneeModel', {
	extend: 'Ext.data.Model',
	fields: [
		{ name: 'text', type: 'string' },
		{ name: 'id', type: 'string' },
		{ name: 'anchor', type: 'string' },
		{ name: 'pa_assignee_type', type: 'string' },
		{ name: 'pa_assignee_key', type: 'string' },
		{ name: 'pa_page_id', type: 'number' },
		{ name: 'pa_position', type: 'number' }
	]
} );

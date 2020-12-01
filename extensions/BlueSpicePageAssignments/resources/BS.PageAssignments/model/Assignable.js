Ext.define( 'BS.PageAssignments.model.Assignable', {
	extend: 'Ext.data.Model',
	 fields: [
		{ name: 'id',   type: 'string' },
		{ name: 'text', type: 'string' },
		{ name: 'anchor', type: 'string' },
		{ name: 'pa_assignee_type', type: 'string' },
		{ name: 'pa_assignee_key', type: 'string' },
		{ name: 'pa_position', type: 'integer' }
	]
});
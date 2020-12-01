# Defining a new component

In your extension add a file called `UserNameAndEmail.js` in the directory
`resources/MyExtension/dialog/` with the following content:

	Ext.define( 'MyExtension.dialog.UserNameAndEmail', {
		extend: 'MWExt.Dialog',
		makeItems: function() {
			return [
				new Ext.form.field.Text({
					name: 'name',
					fieldLabel: 'Name',
					allowBlank: false
				}),
				new Ext.form.field.Text({
					name: 'email',
					fieldLabel: 'Email Address',
					vtype: 'email'
			})];
		},

		makeFooterButtons: function() {
			return [
				{ text: 'Help' },
				'->',
				{ cls: 'mwext-destructive',	text: 'Bad'	},
				{ cls: 'mwext-progressive',	text: 'Good' }
			]
		},

		makeDefaultOkStep: function() {
			var dfd = $.Deferred();
			var task = new Ext.util.DelayedTask(function(){
				console.log( 'DefaultStep done!');
				dfd.resolve();
			});
			task.delay( 500 );
			return dfd.promise();
		}
	});

# Using a component

In your client side code use the following code to create and show your
component

	mw.loader.using( 'ext.extjsbase.MWExt' ).done( function() {
		( function ( mw, $, d, undefined ) {
			var basePath = mw.config.get( "wgExtensionAssetsPath" );
			Ext.Loader.setPath(	'MyExtension', basePath + '/MyExtension/resources/MyExtension' );
		})( mediaWiki, jQuery, document );

		var myDialog = new Ext.create( "MyExtension.dialog.UserNameAndEmail", {
			title: "Please provide username and e-mail"
		} );

		myDialog.on( 'ok', function( sender, data, steps ) {
			var dfd = $.Deferred();
			var task = new Ext.util.DelayedTask(function(){
				console.log( 'ExternalStep done!');
				dfd.resolve();
			});
			task.delay( 1000 );
			steps.push(  dfd.promise() );
		} );

		myDialog.show();
	});

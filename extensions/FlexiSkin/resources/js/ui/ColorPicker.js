flexiskin.ui.widget.ColorPicker = function ( cfg ) {
	cfg = cfg || {};
	cfg = $.extend( {
		colors: [
			{
				code: "#fdce05",
				name: "#fdce05"
			},
			{
				code: "#f4912c",
				name: "#f4912c"
			},
			{
				code: "#d50023",
				name: "#d50023"
			},
			{
				code: "#a12089",
				name: "#a12089"
			},
			{
				code: "#08529d",
				name: "#08529d"
			},
			{
				code: "#00834c",
				name: "#00834c"
			},
			{
				code: "#fff2bb",
				name: "#fff2bb"
			},
			{
				code: "#feb6c2",
				name: "#feb6c2"
			},
			{
				code: "#dc9dd0",
				name: "#dc9dd0"
			},
			{
				code: "#a7c6e6",
				name: "#a7c6e6"
			},
			{
				code: "#ade4a9",
				name: "#ade4a9"
			},
			{
				code: "#ffffff",
				name: "#ffffff"
			},
			{
				code: "#000000",
				name: "#000000"
			},
			{
				code: "#636363",
				name: "#636363"
			},
			{
				code: "#d0d0d0",
				name: "#d0d0d0"
			}
		],
		enableCustomPicker: true
	}, cfg );
	flexiskin.ui.widget.ColorPicker.parent.call( this, cfg );
};

OO.inheritClass( flexiskin.ui.widget.ColorPicker, OOJSPlus.ui.widget.HexColorPickerWidget );

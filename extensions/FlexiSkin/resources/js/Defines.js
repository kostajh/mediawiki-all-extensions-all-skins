window.flexiskin = window.flexiskin || {};
window.flexiskin.ui = window.flexiskin.ui || {};
window.flexiskin.ui.widget = window.flexiskin.ui.widget || {};
window.flexiskin.ui.plugin = window.flexiskin.ui.plugin || {};
window.flexiskin.ui.dialog = window.flexiskin.ui.dialog || {};
window.flexiskin.registry = window.flexiskin.registry || {};

flexiskin.ui.plugin.Registry = function () {
	flexiskin.ui.plugin.Registry.parent.call( this );
};

OO.inheritClass( flexiskin.ui.plugin.Registry, OO.Registry );

flexiskin.registry.Plugin = new flexiskin.ui.plugin.Registry();

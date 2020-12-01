# General workflow
FlexiSkin only sets less variables. All variables that are set by FlexiSkin must be declared in the base lib
https://github.com/hallowelt/mwstake-mediawiki-component-commonuserinterface

Each plugin defines number of less vars that it wants to configure over the FlexiSkin, and provides controls to do so.
After user sets those variables, FlexiSkin will apply them and make them available to all RL modules
that use the library's RLmodule class.

Not all plugins need to override less variables, some might configure different kind of item, eg. logo.
In this case, code that declared the plugin must retrieve the FlexiSkin object and apply the values on its own.

# Declaring plugins
Each plugin consists of several components:
- PHP file that implements IPlugin (most likely extends `PluginBase`)
- JS file that adds the plugin to `flexiskin.registry.Plugin` registry and declares actual controls. It must extend `flexiskin.ui.plugin.Plugin`
- Optional: define `IFlexiSkinSubsciber`
- Optional: stylesheet (.css) that adds styling to contols provided by the plugin

After those components are added, plugin must be registered in extension attribute registry `FlexiSkinPluginRegistry`

## IPlugin
Main purpose of this file is to provide JS and possible CSS files for the plugin.

It also provides the mapping of less var names to the path in the plugin control structure. It might also
provide the default values for the controls.

## IFlexiSkinSubscriber
Main purpose of this file is to say which RL module need to be reloaded when previewing the skin.
Register in `FlexiSkinSubscriberRegistry`

## flexiskin.ui.plugin.Plugin
This file provides the control structure for the UI. Structure can be defined in nested objects to provide sections.
This example will declare colors "section", with "background" and "foreground" subsections.

```
flexiskin.ui.plugin.Colors = function () {
	flexiskin.ui.plugin.Colors.parent.call( this );
};

OO.inheritClass( flexiskin.ui.plugin.Colors, flexiskin.ui.plugin.Plugin );

flexiskin.registry.Plugin.register( 'colors', flexiskin.ui.plugin.Colors );

flexiskin.ui.plugin.Colors.prototype.provideControls = function () {
	return {
        // Main section
		colors: {
            // Label for the main seciton
			label: mw.message( 'flexiskin-ui-plugin-colors-label' ).text(),
            // Subsection
			background: {
                // Label for subsection
				label: mw.message( 'flexiskin-ui-plugin-colors-background-label' ).text(),
                // Final level in the nested object (one that actually declares controls) must declare "items" member
				items: {
                    // Individual controls
					primary: {
                        // Label for the control
						label: mw.message( 'flexiskin-ui-plugin-colors-background-primary-label' ).text(),
                        // Control widget
						widget: new flexiskin.ui.widget.ColorPicker()
					},
					secondary: {
						label: mw.message( 'flexiskin-ui-plugin-colors-background-secondary-label' ).text(),
						widget: new flexiskin.ui.widget.ColorPicker()
					}
				}
			},
			foreground: {
				label: mw.message( 'flexiskin-ui-plugin-colors-foreground-label' ).text(),
				items: {
					primary: {
						label: mw.message( 'flexiskin-ui-plugin-colors-foreground-primary-label' ).text(),
						widget: new flexiskin.ui.widget.ColorPicker()
					},
					secondary: {
						label: mw.message( 'flexiskin-ui-plugin-colors-foreground-secondary-label' ).text(),
						widget: new flexiskin.ui.widget.ColorPicker()
					}
				}
			}
		}
	};
};

```

Resulting values, after configuring the skin, will be something like:
```
$result = [
    'colors' => [
        'background' => [
            'primary' => #304982,
            'secondary' => #FFFFFF
        ],
        'foregound' => [ ... ]
    ],
    // Values from other plugins
    ...
]
```

Less var mapping might look like:
```
return [
    'primary-background' => 'colors/background/primary',
    'primary-foreground' => 'colors/foreground/primary',
];
```

FlexiSkin will take care of converting values from the structure to less vars and settings them.

Plugins can also add controls to existing plugins. Let's say that you are implementing a plugin that should control `body-background`. It would make most sense to put it in the existing color section.
Your new plugin, in its `provideControls` can do something like this:
```
flexiskin.ui.plugin.MyPlugin.prototype.provideControls = function () {
	return {
		colors: {
            background: {
                items: {
                    body_background: {
                        label: 'Body background',
                        widget: new flexiskin.ui.widget.ColorPicker()
                }
            }
        }
    };
};
```

This will add control for body background to "background colors" section, in the UI as well as in the result structure

### Advanced
Plugin can "listen" to several events in order to interfere with different processes. These are called `actionCallbacks` and are declared on control-level (same level as `widget` is declared)

```
widget: ...,
actionCallback: {
    preview: this.onPreview, // args: Object data, string ItemId; return Promise
    save: this.onSave, // args: Object data, string ItemId; return Promise
    setValue: this.onSetValue,  // args: Object value, string ItemId; return Promise
    init: this.onInit // args: flexiskin.ui.Configurator configurator, string ItemId; return void
}
```



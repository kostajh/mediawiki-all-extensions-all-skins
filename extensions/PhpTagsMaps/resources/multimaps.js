/**
 * JavaScript for MultiMaps extension.
 *
 * @see https://www.mediawiki.org/wiki/Extension:MultiMaps
 *
 * @author Pavel Astakhov < pastakhov@yandex.ru >
 */

mediaWiki.MultiMaps = {
	fillByGlobalOptions: function ( globalOptions, elementname, elementoptions ) {
		if ( globalOptions.title && !elementoptions.title ) {
			elementoptions.title = globalOptions.title;
		}
		if ( globalOptions.text && !elementoptions.text ) {
			elementoptions.text = globalOptions.text;
		}

		switch ( elementname ) {
			case 'marker':
				if ( globalOptions.icon && !elementoptions.icon ) {
					elementoptions.icon = globalOptions.icon;
				}
				if ( globalOptions.icon_size && !elementoptions.size ) {
					elementoptions.size = globalOptions.icon_size;
				}
				if ( globalOptions.icon_anchor && !elementoptions.anchor ) {
					elementoptions.anchor = globalOptions.icon_anchor;
				}
				if ( globalOptions.icon_shadow && !elementoptions.shadow ) {
					elementoptions.shadow = globalOptions.icon_shadow;
				}
				if ( globalOptions.icon_sh_size && !elementoptions.sh_size ) {
					elementoptions.sh_size = globalOptions.icon_sh_size;
				}
				if ( globalOptions.icon_sh_anchor && !elementoptions.sh_anchor ) {
					elementoptions.sh_anchor = globalOptions.icon_sh_anchor;
				}
				break;
			case 'polygon':
			case 'circle':
			case 'rectangle':
				if ( globalOptions.fillcolor && !elementoptions.fillcolor ) {
					elementoptions.fillcolor = globalOptions.fillcolor;
				}
				if ( globalOptions.fillopacity && !elementoptions.fillopacity ) {
					elementoptions.fillopacity = globalOptions.fillopacity;
				}
				if ( globalOptions.fill && !elementoptions.fill ) {
					elementoptions.fill = globalOptions.fill;
				}
				// break is not necessary here
				/* falls through */
			case 'line':
				if ( globalOptions.color && !elementoptions.color ) {
					elementoptions.color = globalOptions.color;
				}
				if ( globalOptions.weight && !elementoptions.weight ) {
					elementoptions.weight = globalOptions.weight;
				}
				if ( globalOptions.opacity && !elementoptions.opacity ) {
					elementoptions.opacity = globalOptions.opacity;
				}
				break;
		}
		return elementoptions;
	}
};

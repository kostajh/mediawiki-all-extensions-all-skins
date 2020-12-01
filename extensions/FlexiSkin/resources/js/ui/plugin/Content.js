flexiskin.ui.plugin.Content = function () {
	flexiskin.ui.plugin.Content.parent.call( this );
};

OO.inheritClass( flexiskin.ui.plugin.Content, flexiskin.ui.plugin.Plugin );

flexiskin.registry.Plugin.register( 'content', flexiskin.ui.plugin.Content );

flexiskin.ui.plugin.Content.prototype.provideControls = function () {
	var h1ColorWidget = new flexiskin.ui.widget.ColorPicker(),
		h2ColorWidget = new flexiskin.ui.widget.ColorPicker(),
		h3ColorWidget = new flexiskin.ui.widget.ColorPicker(),
		h4ColorWidget = new flexiskin.ui.widget.ColorPicker(),
		h5ColorWidget = new flexiskin.ui.widget.ColorPicker(),
		h6ColorWidget = new flexiskin.ui.widget.ColorPicker();

	return {
		content: {
			label: mw.message( 'flexiskin-ui-plugin-content-label' ).text(),
			content_colors: {
				label: mw.message( 'flexiskin-ui-plugin-content-colors-label' ).text(),
				items: {
					background: {
						label: mw.message( 'flexiskin-ui-plugin-colors-background-content-label' ).text(),
						widget: new flexiskin.ui.widget.ColorPicker()
					},
					foreground: {
						label: mw.message( 'flexiskin-ui-plugin-colors-foreground-content-label' ).text(),
						widget: new flexiskin.ui.widget.ColorPicker()
					},
					primary_link: {
						label: mw.message( 'flexiskin-ui-plugin-content-colors-link-label' ).text(),
						widget: new flexiskin.ui.widget.ColorPicker()
					},
					new_link: {
						label: mw.message( 'flexiskin-ui-plugin-content-colors-new-link-label' ).text(),
						widget: new flexiskin.ui.widget.ColorPicker()
					}
				}
			},
			font: {
				label: mw.message( 'flexiskin-ui-plugin-content-font-label' ).text(),
				items: {
					family: {
						label: mw.message( 'flexiskin-ui-plugin-content-font-family-label' ).text(),
						widget: new flexiskin.ui.widget.FontPicker()
					},
					font_size: {
						label: mw.message( 'flexiskin-ui-plugin-content-header-font-size-label' ).text(),
						widget: new flexiskin.ui.StyleSizeWidget( {
							allowedUnits: [ 'px', 'rem', 'em', 'pt' ]
						} )
					},
					weight: {
						label: mw.message( 'flexiskin-ui-plugin-content-font-weight-label' ).text(),
						widget: new flexiskin.ui.widget.FontWeightPicker()
					}
				}
			},
			layout: {
				label: 'Layout',
				width: {
					label: mw.message( 'flexiskin-ui-plugin-layout-content-width-label' ).text(),
					widget: new flexiskin.ui.StyleSizeWidget()
				}
			},
			header: {
				label: mw.message( 'flexiskin-ui-plugin-content-header-label' ).text(),
				h1: {
					label: mw.message( 'flexiskin-ui-plugin-content-header-h1-label' ).text(),
					items: {
						color: {
							label: mw.message( 'flexiskin-ui-plugin-content-header-color-label' ).text(),
							widget: h1ColorWidget
						},
						font_size: {
							label: mw.message( 'flexiskin-ui-plugin-content-header-font-size-label' ).text(),
							widget: new flexiskin.ui.StyleSizeWidget( {
								allowedUnits: [ 'px', 'rem', 'em', 'pt' ]
							} )
						},
						border: {
							label: mw.message( 'flexiskin-ui-plugin-content-header-underline-label' ).text(),
							widget: new flexiskin.ui.widget.BorderWidget( { currentColor: h1ColorWidget } )
						}
					}
				},
				h2: {
					label: mw.message( 'flexiskin-ui-plugin-content-header-h2-label' ).text(),
					items: {
						color: {
							label: mw.message( 'flexiskin-ui-plugin-content-header-color-label' ).text(),
							widget: h2ColorWidget
						},
						font_size: {
							label: mw.message( 'flexiskin-ui-plugin-content-header-font-size-label' ).text(),
							widget: new flexiskin.ui.StyleSizeWidget( {
								allowedUnits: [ 'px', 'rem', 'em', 'pt' ]
							} )
						},
						border: {
							label: mw.message( 'flexiskin-ui-plugin-content-header-underline-label' ).text(),
							widget: new flexiskin.ui.widget.BorderWidget( { currentColor: h2ColorWidget } )
						}
					}
				},
				h3: {
					label: mw.message( 'flexiskin-ui-plugin-content-header-h3-label' ).text(),
					items: {
						color: {
							label: mw.message( 'flexiskin-ui-plugin-content-header-color-label' ).text(),
							widget: h3ColorWidget
						},
						font_size: {
							label: mw.message( 'flexiskin-ui-plugin-content-header-font-size-label' ).text(),
							widget: new flexiskin.ui.StyleSizeWidget( {
								allowedUnits: [ 'px', 'rem', 'em', 'pt' ]
							} )
						},
						border: {
							label: mw.message( 'flexiskin-ui-plugin-content-header-underline-label' ).text(),
							widget: new flexiskin.ui.widget.BorderWidget( { currentColor: h3ColorWidget } )
						}
					}
				},
				h4: {
					label: mw.message( 'flexiskin-ui-plugin-content-header-h4-label' ).text(),
					items: {
						color: {
							label: mw.message( 'flexiskin-ui-plugin-content-header-color-label' ).text(),
							widget: h4ColorWidget
						},
						font_size: {
							label: mw.message( 'flexiskin-ui-plugin-content-header-font-size-label' ).text(),
							widget: new flexiskin.ui.StyleSizeWidget( {
								allowedUnits: [ 'px', 'rem', 'em', 'pt' ]
							} )
						},
						border: {
							label: mw.message( 'flexiskin-ui-plugin-content-header-underline-label' ).text(),
							widget: new flexiskin.ui.widget.BorderWidget( { currentColor: h4ColorWidget } )
						}
					}
				},
				h5: {
					label: mw.message( 'flexiskin-ui-plugin-content-header-h5-label' ).text(),
					items: {
						color: {
							label: mw.message( 'flexiskin-ui-plugin-content-header-color-label' ).text(),
							widget: h5ColorWidget
						},
						font_size: {
							label: mw.message( 'flexiskin-ui-plugin-content-header-font-size-label' ).text(),
							widget: new flexiskin.ui.StyleSizeWidget( {
								allowedUnits: [ 'px', 'rem', 'em', 'pt' ]
							} )
						},
						border: {
							label: mw.message( 'flexiskin-ui-plugin-content-header-underline-label' ).text(),
							widget: new flexiskin.ui.widget.BorderWidget( { currentColor: h5ColorWidget } )
						}
					}
				},
				h6: {
					label: mw.message( 'flexiskin-ui-plugin-content-header-h6-label' ).text(),
					items: {
						color: {
							label: mw.message( 'flexiskin-ui-plugin-content-header-color-label' ).text(),
							widget: h6ColorWidget
						},
						font_size: {
							label: mw.message( 'flexiskin-ui-plugin-content-header-font-size-label' ).text(),
							widget: new flexiskin.ui.StyleSizeWidget( {
								allowedUnits: [ 'px', 'rem', 'em', 'pt' ]
							} )
						},
						border: {
							label: mw.message( 'flexiskin-ui-plugin-content-header-underline-label' ).text(),
							widget: new flexiskin.ui.widget.BorderWidget( { currentColor: h6ColorWidget } )
						}
					}
				}
			}
		}
	};
};

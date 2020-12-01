<?php
/**
 * Contains class with hook functions for the TranslateSvg extension
 *
 * @file
 * @author Harry Burt
 * @copyright Copyright © 2012-2014, Harry Burt
 * @license GPL-2.0-or-later
 */

use MediaWiki\MediaWikiServices;

/**
 * Some hooks for TranslateSvg extension.
 */
class TranslateSvgHooks {
	/**
	 * Called right after the extension is registered
	 */
	public static function onRegistration() {
		global $wgReservedUsernames, $wgTranslateSvgBotName, $wgTranslateMessageNamespaces;

		$wgTranslateMessageNamespaces[] = NS_FILE;
		// @fixme This ref-assignment is scary.
		$wgReservedUsernames[] = & $wgTranslateSvgBotName;
	}

	/**
	 * Function used to add a translation helper box via the TranslateGetBoxes hook
	 *
	 * @param MessageGroup $group The MessageGroup of the page being translated
	 * @param MessageHandle $handle The MessageHandle of the page being translated
	 * @param array &$boxes The array to which the thumbnail helper is added
	 */
	public static function addThumbnail( MessageGroup $group, MessageHandle $handle, &$boxes ) {
		global $wgLang;

		if ( !( $group instanceof SVGMessageGroup ) ) {
			return;
		}

		$title = Title::newFromText( $group->getLabel(), NS_FILE );
		$file = MediaWikiServices::getInstance()->getRepoGroup()->findFile( $title );
		$thumbnail = Linker::makeThumbLinkObj(
			$title, $file, wfMessage( 'translate-svg-js-thumbnail' ), '', $wgLang->alignEnd(),
			[ 'width' => 275, 'height' => 275 ]
		);
		$boxes = array_merge( [ 'thumbnail' => $thumbnail ], $boxes );
	}

	/**
	 * Function used to remove the QQQ (documentation) translation helper box via
	 * the TranslateGetBoxes hook
	 *
	 * @param MessageGroup $group The message group to which the message being translated belongs
	 * @param MessageHandle $handle The MessageHandle of the message being translated
	 * @param array &$boxes The array from which the thumbnail helper is removed
	 */
	public static function removeQQQ( MessageGroup $group, MessageHandle $handle, &$boxes ) {
		if ( !( $group instanceof SVGMessageGroup ) ) {
			return;
		}

		unset( $boxes['documentation'] );
	}

	/**
	 * Function used to remove the translation memory suggestions helper box via
	 * the TranslateGetBoxes hook
	 *
	 * @todo Replace this with a better helper rather than no helper
	 * @param MessageGroup $group The message group to which the message being translated belongs
	 * @param MessageHandle $handle The MessageHandle of the message being translated
	 * @param array &$boxes The array from which the thumbnail helper is removed
	 */
	public static function removeSuggestions( MessageGroup $group, MessageHandle $handle, &$boxes ) {
		if ( !( $group instanceof SVGMessageGroup ) ) {
			return;
		}

		unset( $boxes['translation-memory'] );
	}

	/**
	 * Function used to add modules to the ResourceLoader via the
	 * TranslateBeforeAddModules hook
	 *
	 * @param string[] &$modules
	 */
	public static function addModules( &$modules ) {
		$modules[] = 'ext.translatesvg';
	}

	/**
	 * Function used to preload properties via the TranslatePrefillTranslation hook
	 *
	 * @param string &$properties To be filled with the preloaded property string
	 * @param MessageHandle $handle $handle
	 */
	public static function getDefaultPropertiesFromGroup( &$properties, MessageHandle $handle ) {
		if ( !$handle->isValid() ) {
			return;
		}

		$group = $handle->getGroup();
		if ( !( $group instanceof SVGMessageGroup ) || $properties !== null ) {
			return;
		}
		$properties = $group->getProperties(
			$handle->getKey(),
			$group->getSourceLanguage()
		);
	}

	/**
	 * Function used to convert a message that includes a property string
	 * to one that does not, then using the extracted properties to generate
	 * a series of HTML form elements (extraInputs). Used for the
	 * TranslateGetExtraInputs hook.
	 *
	 * @param string &$message Input/output translation string, passed by reference
	 * @param string &$extraInputs Extra form elements required
	 */
	public static function propertiesToExtraInputs( &$message, &$extraInputs ) {
		global $wgTranslateSvgTypefaces, $wgTranslateSvgColors;
		$allowedUnits = [ 'px', 'pt', '%', 'em' ];

		if ( !TranslateSvgUtils::hasPropertyString( $message ) ) {
			return;
		}

		$propertyString = TranslateSvgUtils::extractPropertyString( $message );
		$message = TranslateSvgUtils::stripPropertyString( $message );

		preg_match_all( '/\| *([a-z-]+) *= *([^|}]*)/', $propertyString, $parameters );
		$count = count( $parameters[0] );
		for ( $i = 0; $i < $count; $i++ ) {
			$index = $parameters[1][$i];
			$currentValue = trim( $parameters[2][$i] );
			switch ( $index ) {
				case 'x':
				case 'y':
				case 'font-size':
					$extraInputs .= Xml::inputLabel(
						wfMessage( 'translate-js-label-' . $index )->text(),
						'mw-translate-prop-' . $index, 'mw-translate-prop-' . $index, 2, $currentValue,
						[ 'type' => 'number', 'step' => 'any', 'style' => 'width:3em;' ]
					) . "&#160;";
					break;
				case 'font-family':
					$typefaces = $wgTranslateSvgTypefaces;
					if ( $currentValue == 'inherit' ) {
						$currentValue = wfMessage( 'translate-js-font-family-inherit' )->text();
					}
					if ( !in_array( $currentValue, $typefaces ) ) {
						$typefaces[] = $currentValue;
					}
					$extraInputs .= Xml::label(
							wfMessage( 'translate-js-label-' . $index )->text(), 'mw-translate-prop-' . $index
						) .
							"&#160;" . Xml::listDropDown(
							'mw-translate-prop-' . $index, implode( "\n", $wgTranslateSvgTypefaces ),
							wfMessage( 'translate-js-font-family-inherit' )->text(), $currentValue
						) . "&#160;";
					break;
				case 'units':
					$extraInputs .= Xml::label(
							wfMessage( 'translate-js-label-' . $index )->text(),
							'mw-translate-prop-' . $index
						) .
						"&#160;" . Xml::listDropDown(
							'mw-translate-prop-' . $index, implode( "\n", $allowedUnits ),
							'', $currentValue
						) . "&#160;";
					break;
				case 'bold':
				case 'italic':
				case 'underline':
					$checked = ( $currentValue === 'yes' );
					$extraInputs .= Html::openElement( 'span', [ 'style' => 'white-space:nowrap;' ] )
						. Xml::checkLabel(
							wfMessage( 'translate-js-label-' . $index )->text(),
							'mw-translate-prop-' . $index, 'mw-translate-prop-' . $index, $checked
						) . Html::closeElement( 'span' ) . " ";
					break;
				case 'color':
					$colors = $wgTranslateSvgColors;
					$colors[] = $currentValue;
					$extraInputs .= Xml::label(
							wfMessage( 'translate-js-label-' . $index )->text(), 'mw-translate-prop-' . $index
						) .
						"&#160;" . Xml::listDropDown(
							'mw-translate-prop-' . $index, implode( "\n", $colors ),
							'', $currentValue
						) . "&#160;";
					break;
			}
		}

		$extraInputs = Xml::fieldset(
			wfMessage( 'translate-js-properties-legend' ),
			$extraInputs
		);
	}

	/**
	 * Function that replaces a message with a no-property-string version.
	 * Used for the TranslateFormatMessageBeforeTable hook
	 *
	 * @param string &$message Message which may or may not include a property string
	 * @param ThinMessage $m The source message object
	 * @param MessageGroup $group The source message group
	 * @param string $targetLang The language for which translations are being shown
	 * @param array &$attrs The set of attributes to apply to the row (not used)
	 */
	public static function stripPropertyString(
		&$message,
		ThinMessage $m,
		MessageGroup $group,
		$targetLang,
		&$attrs
	) {
		// TODO: mark as .justtranslated if not yet exported
		if ( !( $group instanceof SVGMessageGroup ) ) {
			return;
		}

		$message = TranslateSvgUtils::stripPropertyString( $message );
	}

	/**
	 * Function used to expose the $wgTranslateSvgTemplateName global to
	 * JavaScript via the MakeGlobalVariablesScript MediaWiki hook
	 *
	 * @param array &$vars of variables to be exposed to JavaScript
	 * @param OutputPage $out Contextual OutputPage instance
	 */
	public static function exposeTranslateSvgTemplateName( &$vars, OutputPage $out ) {
		global $wgTranslateSvgTemplateName;
		if ( $out->getTitle()->isSpecial( 'Translate' ) ) {
			$vars['wgTranslateSvgTemplateName'] = $wgTranslateSvgTemplateName;
		}
	}

	/**
	 * Function used to make "export as SVG" the default export option for SVG files
	 * Used with the TranslateGetSpecialTranslateOptions hook
	 *
	 * @param array &$defaults Associative array of so-called "default" values supplied by Translate
	 * @param array &$nondefaults Associative array of so-called "non-default" values supplied by Translate
	 */
	public static function makeExportAsSvgOptionDefault( &$defaults, &$nondefaults ) {
		if ( isset( $nondefaults['group'] )
			&& MessageGroups::getGroup( $nondefaults['group'] ) instanceof SVGMessageGroup
			&& isset( $nondefaults['taction'] )
			&& $nondefaults['taction'] == 'export'
		) {
			$nondefaults['task'] = 'export-as-svg';
		}
	}

	/**
	 * Define the thumbnail property for use with the mgprop parameter of
	 * action=query&meta=messagegroups API queries.
	 * Used with the TranslateGetAPIMessageGroupsPropertyDescs hook
	 *
	 * @param string[] &$properties An associative array of properties (name => details)
	 */
	public static function addAPIProperties( &$properties ) {
		$properties['thumbnail'] = ' thumbnail    - Include URL of up-to-date thumbnail (SVG message groups only)';
	}

	/**
	 * Function used to add TranslateSvg's schema update to update.php via
	 * MediaWiki's 'LoadExtensionSchemaUpdates' hook.
	 *
	 * @param DatabaseUpdater $updater The MediaWiki-provided updater instance
	 */
	public static function schemaUpdates( $updater ) {
		$dir = __DIR__ . '/sql';

		$updater->addExtensionUpdate( [ 'addTable', 'translate_svg', "$dir/translate_svg.sql", true ] );
	}

	/**
	 * Function used to add modules via the resource loader on
	 * the file pages of SVG files via the BeforePageDisplay MediaWiki hook
	 *
	 * @param OutputPage $out Contextual OutputPage instance
	 */
	public static function updateFileDescriptionPages( OutputPage $out ) {
		$title = $out->getTitle();
		if ( TranslateSvgUtils::isSVGFilePage( $title ) ) {
			$out->addModules( 'ext.translatesvg.filepage' );
		}
	}

	/**
	 * Process the thumbnail property for use with the mgprop parameter of
	 * action=query&meta=messagegroups API queries.
	 * Used with the TranslateProcessAPIMessageGroupsProperties hook
	 *
	 * @param array &$a Associative array of the properties of $group that will be returned
	 * @param array $props Associative array ($name => true) of properties the user has specifically requested
	 * @param array $params Parameter input by the user (unprefixed name => value)
	 * @param MessageGroup $g The group in question
	 */
	public static function processAPIProperties( &$a, $props, $params, MessageGroup $g ) {
		if ( !( $g instanceof SVGMessageGroup ) ) {
			return;
		}

		if ( isset( $props['thumbnail'] ) ) {
			$language = $params['language'] ?? $g->getSourceLanguage();

			$inProgressTranslations = [];
			if ( isset( $params['inprogress'] ) ) {
				$inProgressTranslations = json_decode( $params['inprogress'], true );
			}

			$writer = new SVGFormatWriter( $g, $inProgressTranslations );
			$a['thumbnail'] = $writer->thumbnailExport( $language );
		}
	}

	/**
	 * Define mginprogress and mglanguage parameters for use with
	 * action=query&meta=messagegroups API queries.
	 * Used with the TranslateGetAPIMessageGroupsParameterList hook
	 *
	 * @param array[] &$params An associative array of possible parameters (name => details)
	 */
	public static function addAPIParams( &$params ) {
		$params['inprogress'] = [
			ApiBase::PARAM_TYPE => 'string'
		];
		$params['language'] = [
			ApiBase::PARAM_TYPE => 'string'
		];
	}

	/**
	 * Document the mginprogress and mglanguage parameters
	 * Used with the TranslateGetAPIMessageGroupsParameterDescs hook
	 *
	 * @param string[] &$paramDescs An associative array of parameters, name => description.
	 * @param string $p The prefix for action=query&meta=messagegroups (unused)
	 */
	public static function addAPIParamDescs( &$paramDescs, $p ) {
		$paramDescs['inprogress'] =
			'Possible array of in-progress translations (unsaved translations that should'
			. ' take preference over saved ones). SVG message groups only.';
		$paramDescs['language'] =
			'Language to render the thumbnail in. SVG message groups only.';
	}

	/**
	 * Function used to load groups registered by TranslateSvg into the
	 * lists generated by Translate. Uses the 'TranslatePostInitGroups' hook.
	 *
	 * @param array &$list Array of groups to append to.
	 * @param array &$deps Not used at present.
	 * @param array &$autoload Not used at present.
	 */
	public static function loadSVGGroups( &$list, &$deps, &$autoload ) {
		$dbr = wfGetDB( DB_MASTER );

		$tables = [ 'translate_svg', 'page' ];
		$col = '*';
		$conds = [ 'page_id = ts_page_id' ];
		$res = $dbr->select( $tables, $col, $conds, __METHOD__ );

		foreach ( $res as $r ) {
			// Get a sanitised, normalised form of the title
			$group = Title::newFromRow( $r )->getText();
			$list[$group] = new SVGMessageGroup( $group );
		}
	}

	/**
	 * Function used to expose various new globals to the
	 * JavaScript of the file description pages of SVG files
	 * via the MakeGlobalVariablesScript MediaWiki hook.
	 *
	 * @param array &$vars Array of variables to be exposed to JavaScript
	 * @param OutputPage $out Contextual OutputPage instance
	 */
	public static function makeFilePageGlobalVariables( &$vars, OutputPage $out ) {
		$title = $out->getTitle();
		if ( !TranslateSvgUtils::isSVGFilePage( $title ) ) {
			return;
		}

		$user = $out->getUser();
		$vars['wgUserLanguageName'] = Language::fetchLanguageName(
			$user->getOption( 'language' )
		);
		$vars['wgUserCanTranslate'] = $user->isAllowed( 'translate' );

		$id = $title->getText();
		$messageGroup = new SVGMessageGroup( $id );
		$svg = SVGFile::newFromMessageGroup( $messageGroup );
		$vars['wgFileCanBeTranslated'] = ( $svg->isTranslationReady() );
		if ( !$vars['wgFileCanBeTranslated'] || MessageGroups::getGroup( $id ) === null ) {
			// Not translatable or not yet translated, let's save time and return immediately
			$vars['wgFileTranslationStarted'] = false;
			$vars['wgFileFullTranslations'] = [];
			$vars['wgFilePartialTranslations'] = [];
			return;
		}

		$languages = $svg->getSavedLanguagesFiltered();
		$full = [];
		$partial = [];
		foreach ( $languages['full'] as $language ) {
			array_push(
				$full, [
					'name' => Language::fetchLanguageName( $language ),
					'code' => $language
				]
			);
		}
		foreach ( $languages['partial'] as $language ) {
			array_push(
				$partial, [
					'name' => Language::fetchLanguageName( $language ),
					'code' => $language
				]
			);
		}
		$vars['wgFileFullTranslations'] = $full;
		$vars['wgFilePartialTranslations'] = $partial;
		$vars['wgFileTranslationStarted'] = true;
	}
}

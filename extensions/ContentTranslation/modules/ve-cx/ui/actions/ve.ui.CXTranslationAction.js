'use strict';

/*!
 * Content Translation UserInterface TranslationAction class.
 * @copyright See AUTHORS.txt
 * @license GPL-2.0-or-later
 */

/**
 * Translation action.
 *
 * @class
 * @extends ve.ui.Action
 * @constructor
 * @param {ve.ui.Surface} surface Surface to act on
 */
ve.ui.CXTranslationAction = function VeUiCXTranslationAction() {
	// Parent constructor
	ve.ui.CXTranslationAction.super.apply( this, arguments );
	this.beforeTranslationData = {};
};

/* Inheritance */

OO.inheritClass( ve.ui.CXTranslationAction, ve.ui.Action );

/* Static Properties */

ve.ui.CXTranslationAction.static.name = 'translation';

/**
 * List of allowed methods for the action.
 *
 * @static
 * @property
 */
ve.ui.CXTranslationAction.static.methods = [ 'translate' ];

/* Methods */

/**
 * Find the currently active section and request to change the source.
 *
 * @param {string} source Selected MT provider or `source` or `scratch`
 * @return {boolean} False if action is cancelled.
 */
ve.ui.CXTranslationAction.prototype.translate = function ( source ) {
	var section, promise, originalSource,
		target = ve.init.target,
		selection = this.surface.getModel().getSelection();

	if ( !( selection instanceof ve.dm.LinearSelection ) ) {
		return false;
	}

	section = mw.cx.getParentSectionForSelection( this.surface, selection );

	if ( !section ) {
		mw.log.error( '[CX] Could not find a CX Section as parent for the context.' );
		return false;
	}

	originalSource = section.getOriginalContentSource();

	this.beforeTranslate( section );

	if ( source === 'reset-translation' ) {
		promise = target.changeContentSource( section, null, originalSource, { noCache: true } );
	} else {
		promise = target.changeContentSource( section, originalSource, source );
	}

	promise
		.always( function () {
			// Recalculate the section, since the instance got distroyed in content change
			section = target.getTargetSectionNode( section.getSectionId() );
			if ( section ) {
				this.afterTranslate( section );
			}
		}.bind( this ) ).fail( function () {
			mw.notify( mw.msg( 'cx-mt-failed ' ) );
			this.surface.getModel().emit( 'contextChange' );
		}.bind( this ) );
};

/**
 * Pre-translate handler
 *
 * @param {ve.dm.CXSectionNode} section
 */
ve.ui.CXTranslationAction.prototype.beforeTranslate = function ( section ) {
	// Save scroll position before changing section content
	this.beforeTranslationData.scrollTop = this.surface.view.$window.scrollTop();
	section.emit( 'beforeTranslation' );
};

/**
 * Post-translate handler
 *
 * @param {ve.dm.CXSectionNode} section
 */
ve.ui.CXTranslationAction.prototype.afterTranslate = function ( section ) {
	// Restore scroll position after changing content
	this.surface.view.$window.scrollTop( this.beforeTranslationData.scrollTop );
	section.emit( 'afterTranslation' );
};

/* Registration */

ve.ui.actionFactory.register( ve.ui.CXTranslationAction );

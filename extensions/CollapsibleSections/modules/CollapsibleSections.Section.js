/**
 * MIT License
 *
 * Copyright (c) 2020 WikiWorks team
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

( function () {

	/**
	 * @class
	 * @param {Node} element
	 * @constructor
	 */
	function CollapsibleSection( element ) {
		var elements;
		this.$element = $( element );
		this.$content = null;

		// Find and wrap all contents until next section
		// eslint-disable-next-line no-jquery/variable-pattern
		elements = this.$element.nextUntil( this.$element.get( 0 ).tagName.toLowerCase() );
		if ( elements.length ) {
			this.$element.addClass( 'collapsible-header' );
			elements.wrapAll( '<div class="collapsible-header-content" />' );
			this.$content = this.$element.next();
			this.$element.bind( 'click', this.toggleState.bind( this ) );
			if ( mw.config.get( 'wgCollapsibleSectionsCollapsedByDefault' ) ) {
				this.collapse();
			} else {
				this.expand();
			}
		}
	}

	/**
	 * Section click event handler
	 * @param {Event} event
	 */
	CollapsibleSection.prototype.toggleState = function ( event ) {
		// Ignore edit section links
		if ( event.target.tagName === 'A' ) {
			return;
		}

		// eslint-disable-next-line no-jquery/no-class-state
		if ( this.$element.hasClass( 'collapsible-header--expanded' ) ) {
			this.collapse();
		} else {
			this.expand();
		}
	};

	/**
	 * Collapses section content block
	 */
	CollapsibleSection.prototype.collapse = function () {
		this.$element.removeClass( 'collapsible-header--expanded' );
		// eslint-disable-next-line no-jquery/no-slide
		this.$content.slideUp( 'fast' );
	};

	/**
	 * Expands section content block
	 */
	CollapsibleSection.prototype.expand = function () {
		this.$element.addClass( 'collapsible-header--expanded' );
		// eslint-disable-next-line no-jquery/no-slide
		this.$content.slideDown( 'fast' );
	};

	/**
	 * @type {CollapsibleSection}
	 */
	mw.CollapsibleSection = CollapsibleSection;

}() );

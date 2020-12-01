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

	// eslint-disable-next-line no-jquery/no-global-selector
	var $mwContentsBlock = $( '.mw-parser-output' ),
		$headings = $( $mwContentsBlock ).find( mw.config.get( 'wgCollapsibleSectionsTag' ) );

	/**
	 * Check whether a mobile device is used. The `navigator` object global contains
	 * information about the browser. See: https://www.w3schools.com/jsref/obj_navigator.asp.
	 *
	 * @return {Boolean} Whether the user agent matched that of a mobile browser.
	 */
	function isMobile() {
		return navigator.userAgent.match( /Android|BlackBerry|iPhone|iPad|iPod|Opera Mini|IEMobile/i );
	}

	if ( !isMobile() && !mw.config.get( 'wgCollapsibleSectionsEnableDesktop' ) ) {
		return;
	}

	$headings.each( function ( index, element ) {
		if ( $( element ).find( 'span.mw-headline' ).length ) {
			new mw.CollapsibleSection( element );
		}
	} );
}() );

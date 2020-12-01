/* This modification prevents table cell editing with a single click in IE11.
 * When editing with single click (aka in TableSelection), IE moves the characters
 * from front to back.
 *
 * Bug: T220984 ERM#14844
 */

ve.ce.Surface.prototype._onDocumentKeyPress = ve.ce.Surface.prototype.onDocumentKeyPress;

ve.ce.Surface.prototype.onDocumentKeyPress = function ( e ) {
	if (
		this.model.selection instanceof ve.dm.TableSelection &&
		$.client.profile().name === 'msie'
	) {
		return;
	}
	return this._onDocumentKeyPress( e );
}

ve.ce.Surface.prototype._onPaste = ve.ce.Surface.prototype.onPaste;

ve.ce.Surface.prototype.onPaste = function ( e ) {
	if (
		this.model.selection instanceof ve.dm.TableSelection &&
		$.client.profile().name === 'msie'
	) {
		return;
	}
	return this._onPaste( e );
}
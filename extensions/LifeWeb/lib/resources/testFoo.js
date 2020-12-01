/**
 * @licence GNU GPL v2+
 * @author Simon A. Eugster <simon.eu@gmail.com>
 */

var Foo = {};
Foo.sayHello = function ( $element ) {
	$element.append( '<p>Hello Module!</p>' );
};
window.Foo = Foo;

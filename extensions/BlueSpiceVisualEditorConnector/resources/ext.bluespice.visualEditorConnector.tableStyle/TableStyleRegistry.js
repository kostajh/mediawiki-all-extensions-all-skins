bs.util.registerNamespace( 'bs.vec.registry' );

bs.vec.TableStyleRegistry = function() {
	OO.Registry.call( this );
};

OO.inheritClass( bs.vec.TableStyleRegistry, OO.Registry );

bs.vec.registry.TableStyle = new bs.vec.TableStyleRegistry();

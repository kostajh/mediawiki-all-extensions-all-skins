<?php
namespace PhpTagsObjects;

/**
 * Description of WikiW
 * @property \Endpoint $value
 * @author pastakhov
 */
class SparqlEndpoint extends \PhpTags\GenericObject {

	public static function getConstantValue( $constantName ) {
		switch ( $constantName ) {
			case 'PHPTAGS_SPARQL_VERSION':
				return \ExtensionRegistry::getInstance()->getAllThings()['PhpTags SPARQL']['version'];
		}
		parent::getConstantValue( $constantName );
	}

	public function m___construct( $endpoint = null ) {
		global $wgPhpTagsSPARQLDefaultEndpoint, $wgPhpTagsSPARQLEndpointsThatUseGetHttpMethod;

		if ( !$endpoint ) {
			$endpoint = $wgPhpTagsSPARQLDefaultEndpoint;
		}

		$object = new \Endpoint( $endpoint, true, false );

		if ( isset( $wgPhpTagsSPARQLEndpointsThatUseGetHttpMethod[$endpoint] ) ) {
			$object->setMethodHTTPRead( 'GET' );
		}

		$this->value = $object;
		return true;
	}

	public function m_query( $query ) {
		return $this->value->query( $query );
	}

	public function m_getErrors() {
		return $this->value->GetErrors();
	}

	public function m_resetErrors() {
		return $this->value->ResetErrors();
	}

}

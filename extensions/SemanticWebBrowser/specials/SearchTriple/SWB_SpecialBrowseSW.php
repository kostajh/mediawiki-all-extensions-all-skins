<?php
/**
* @file
* @ingroup SMWSpecialPage
* @ingroup SpecialPage
*
* A factbox like view on an article, implemented by a special page.
*
* 
*/

/**
* A factbox view on one specific article, showing all the Semantic data about it
*@author Anna Kantorovitch
*@author Benedikt Kämpgen
*@author Andreas Adler
*
* @ingroup SpecialPage
*/
if ( !defined( 'MEDIAWIKI' ) ) {
	exit( 1 );
}

global $swbgIP;

set_include_path($swbgIP . 'lib/');
require_once ($swbgIP . 'lib/EasyRdf.php');

class SWBSpecialBrowseSW extends SpecialPage {
	/// int How  many incoming values should be asked for
	static public $incomingvaluescount = 8;
	/// int  How many incoming properties should be asked for
	static public $incomingpropertiescount = 21;
	/// SMWDataValue  Topic of this page
	private $subject = null;
	/// Text to be set in the query form
	private $articletext = "";
	/// bool  To display outgoing values?
	private $showoutgoing = true;
	/// bool  To display incoming values?
	private $showincoming = false;
	/// int  At which incoming property are we currently?
	private $offset = 0;
	

	/**
	* Constructor
	*/
	public function __construct() {
		global $smwgBrowseShowAll;
		parent::__construct( 'BrowseSW', '', true, false, 'default', true );

		if ( $smwgBrowseShowAll ) {
			SWBSpecialBrowseSW::$incomingvaluescount = 21;
			SWBSpecialBrowseSW::$incomingpropertiescount = - 1;
		}
	}

	/**
	* Main entry point for Special Pages
	*
	* @param[in] $query string  Given by MediaWiki
	*/
	public function execute( $query ) {
		global $wgRequest, $wgOut, $smwgBrowseShowAll;
		$this->setHeaders();
		// get the GET parameters
		$this->articletext = $wgRequest->getVal( 'article' );				
		// no GET parameters? Then try the URL
		if ( is_null( $this->articletext ) ) {
			$params = SMWInfolink::decodeParameters( $query, false );
			reset( $params );
			$this->articletext = current( $params );
		}
		
		$this->subject = SMWDataValueFactory::newTypeIDValue( '_wpg', $this->articletext );	
		$offsettext = $wgRequest->getVal( 'offset' );
		$this->offset = ( is_null( $offsettext )) ? 0 : intval( $offsettext );
		
		$dir = $wgRequest->getVal( 'dir' );

		if ( $smwgBrowseShowAll ) {
			$this->showoutgoing = true;
			$this->showincoming = true;
		}
		if ( ( $dir == 'both' ) || ( $dir == 'in' ) ) $this->showincoming = true;
		if ( $dir == 'in' ) $this->showoutgoing = false;
		if ( $dir == 'out' ) $this->showincoming = false;

		// print OutputPage
		
		$wgOut->addHTML( $this->displayBrowse() );
		SMWOutputs::commitToOutputPage( $wgOut ); // make sure locally collected output data is pushed to the output!

	}

	/**
	* Create and output HTML including the complete factbox, based on the extracted
	* parameters in the execute comment.
	*
	* @return string  A HTML string with the factbox
	*/
	private function displayBrowse() {
		global $wgContLang, $wgOut;
		$html = "\n";
		$leftside = !( $wgContLang->isRTL() ); // For right to left languages, all is mirrored
		
		
		if ( $this->subject->isValid() && (count( $this->subject->getErrors())==0) ) {

			/** Here, we can distinguish
			* 1. We have an existing page + any number of equivalent URIs
			* 2. We have a non-existing page, which is a URI
			*/			

			$html .= $this->displayHead();
			
			$data = smwfGetStore()->getSemanticData( $this->subject->getDataItem() ); // $data is of type SMWSemanticData
			
			if ( $this->showoutgoing ) {
				$html .= $this->displayData( $data, $leftside );
				$html .= $this->displayCenter();
			}
			
			if ( $this->showincoming ) {
				list( $indata, $more ) = $this->getInData();
				global $smwgBrowseShowInverse;
				
				if ( !$smwgBrowseShowInverse ) {
					$leftside = !$leftside;
				}

				$html .= $this->displayData( $indata, $leftside, true );
				$html .= $this->displayBottom( $more );
				
				// We need to switch browse inverse, again
				$leftside = !$leftside;
			}
			
			
			// Now, we can display data from the Semantic Web. Until this point the code equals the code from SMW_SprecialBrowse
			/** Two possibilities: 
			* 1. Existing page with equivalent uris
			* 2. Non-existing page with URL
			*/ 

			$equivalentURI   = new SMWDIProperty( "_URI" );           
			if (isset($data)){
				$arr_equi_values = $data->getPropertyValues($equivalentURI);
			}
			
			// If no equivalentURIs, then maybe the article itself
			if (empty($arr_equi_values)) {
				
				
				// we have to use the articletext because it will include underscores
				$info = parse_url ($this->articletext );
				
				(!isset( $info['scheme'])  ) ? $scheme   = "" : $scheme   = $info['scheme'];
				// Info: In front of host, we had // before, but those seem not needed, any more.
				// (!isset( $info['host'])    ) ? $host     = "" : $host     = "//".$info['host'];
				(!isset( $info['host'])    ) ? $host     = "" : $host     = $info['host'];
				(!isset( $info['path'])    ) ? $path     = "" : $path     = $info['path'];
				(!isset( $info['query'])   ) ? $query    = "" : $query    = $info['query'];
				(!isset( $info['fragment'])) ? $fragment = "" : $fragment = $info['fragment'];
				
				if($scheme=="" || $host.$path==""){
					// in this case SMWDIUri becomes Exception	
				}
				else{					
					$arr_equi_values[] = new SMWDIUri($scheme, $host.$path, $query, $fragment);				
				}				
			}
			
			$html.= $this-> loadGraphAndGetHtml ($arr_equi_values, $leftside);
			
		}
		
		else {
			foreach ($this->subject->getErrors() as $subjectError)
			$html.=$subjectError;
			$html.="<br/>";
		}

		// Add a bit space between the factbox and the query form
		if ( !$this->including() ) {
			$html .= "<p> &#160; </p>\n";
		}
		
		if ( !$this->including() ) {
			$html .= $this->queryForm();
		}
		
		$wgOut->addHTML( $html );
		
	}
	
	/**
	* Load the graph with EasyRDF and return a HTML string with the results for each URI
	* @param $uri_array SMWDIUri array with all URIs
	* @param $leftside
	* @return string  A HTML string with the linked data graph
	*/	
	private function loadGraphAndGetHtml ($uri_array, $leftside){
		
		$html="";
		
		foreach ($uri_array as $uri) {
			// Two possibilities: 1. No URL 2. URL
			
			if( $uri === null ){

			}
			else{
				
				// Build the graph
				$uriprint = $uri->getURI();
				
				if( !isset($uriprint) ){

				}
				else {				
					
				
					//create object for graph
					try {
						$graph = new EasyRdf_Graph($uri->getURI());	
						$graph->load();					
						$html .= $this->displayGraph($graph, $uri->getURI(), $leftside);
					}

					catch (Exception $e1) {
						
						// alternatively use the following code to create a new graph object and load the graph explicitly with rdfxml
						try{
							$graph = EasyRdf_Graph::newAndLoad($uri->getURI(), "rdfxml");
							$html .= $this->displayGraph($graph, $uri->getURI(), $leftside);								
						}
						catch (Exception $e2){							
							$html .= "<b>".wfMessage( 'swb_browse_error' )->text(). "</b> ".$e1->getMessage().". Interpretation as RDF/XML also failed (".$e2->getMessage().")<br/>";
						}
					}
					
				} 
			}	
		}
		
		return $html;
		
	}

	/**
	* Create and output HTML including the complete factbox, based on the extracted
	* parameters in the execute comment.
	* for one Graph object
	* @return string  A HTML string with the factbox
	* @return leftside in parameter
	*/
	public function displayGraph($graph, $uri, &$leftside){
		
		$html  = "";		
		// Now, we resolve this URI and store the rdf
		$html .= $this->displaySemanticHead( $uri );


		if ($this->showoutgoing ) {			
			$swdata = $this->getSemanticWebData( $graph, $uri ); // $swData contains only DataItems
			$html .= $this->displaySemanticWebData( $swdata, $leftside );
			$html .= $this->displayCenter();
		}

		
		if ( $this->showincoming ) {
			list( $indata, $more ) = $this->getSemanticWebInData( $graph, $uri );
			global $smwgBrowseShowInverse;
			if ( !$smwgBrowseShowInverse ) $leftside = !$leftside;
			// formerly subsequent code line used
			//$html .= $this->displaySemanticWebData( $indata, $leftside, true );
			$html .= $this->displaySemanticWebData( $indata, $leftside );		
			$html .= $this->displayBottom( $more );
			// We need to switch browse inverse, again
			$leftside = !$leftside;
		}
		
		return $html;
	}
	
	/**
	*
	* Similar to getInData(), but in this case regarding the Semantic Web.
	*/
	private function getSemanticWebInData( $graph, $uri ) {
		$indata = new SMWSemanticData( $this->subject->getDataItem() );
		$options = new SMWRequestOptions();
		$options->sort = true;
		$options->limit = SWBSpecialBrowseSW::$incomingpropertiescount;
		if ( $this->offset > 0 ) $options->offset = $this->offset;

		$triples = $this->getSemanticInfos( $graph, null, null, $uri );
		if ( count( $triples ) >= SWBSpecialBrowseSW::$incomingpropertiescount ) {
			$more = true;
			array_pop( $triples ); // drop the last one
		} else {
			$more = false;
		}
		
		//get each triple with subject, property, object . All are strings
		foreach ( $triples as $triple ) {
			list( $subject, $property, $object ) = $triple;
			
			// $this->debug ($subject);
			
			// replace blacklisted rdf(s) and owl properties
			$uri_blacklist = explode("\n", wfMessage('smw_uri_blacklist')->inContentLanguage()->text());
			
			foreach ($uri_blacklist as $uri) {

				$uri = trim($uri);
				if ($uri == mb_substr($property, 0, mb_strlen($uri))) { // disallowed URI!						

					if (strcmp($uri, "http://www.w3.org/1999/02/22-rdf-syntax-ns#") == 0) {
						$property= str_replace($uri, "rdf:", $property);
					} 
					else if (strcmp($uri, "http://www.w3.org/2000/01/rdf-schema#") == 0) {
						$property = str_replace($uri, "rdfs:", $property);
					} 
					else if (strcmp($uri, "http://www.w3.org/2002/07/owl#") == 0) {
						$property = str_replace($uri, "owl:", $property);
					}
				}
			}

			$propertyPageName = $this->getInternalMapping( $property );			
			
			
			$dataProperty = null;
			if( !isset($propertyPageName) || $propertyPageName == null || !strpos("Property:",$propertyPageName )===0){
	
				$dataProperty = SMWDIProperty::newFromUserLabel( $property );
				
			}
				
			else{
				//the namespace has to be removed before creating a new SMWDIProperty		
				$pageNameWithoutNamespace = str_replace ("Property:","",$propertyPageName);
				$dataProperty = SMWDIProperty::newFromUserLabel( $pageNameWithoutNamespace);
			}
			
			
			$subjectPageName = $this->getInternalMapping( $subject );
			$wikipage = null;
			if( !isset( $subjectPageName ) || $subjectPageName == null){
				// formerly subsequent code line used
				// $wikipage = new SMWDIWikiPage( $subject, NS_MAIN, '');
				$wikipage = SMWDataValueFactory::newTypeIDValue( '_uri', $subject, $dataProperty );
				
			}else{
				// formerly subsequent code line used
				// $wikipage = new SMWDIWikiPage( $subjectPageName, NS_MAIN, '');
				$wikipage = SMWDataValueFactory::newTypeIDValue( '_wpg', $subjectPageName, $dataProperty );				
			}
			
			// formerly subsequent code line used
			// $indata->addPropertyObjectValue( $dataProperty, $wikipage);
			
			if( !( get_class( $wikipage->getDataItem() ) == "SMWDIError" ) ){
					$indata->addPropertyObjectValue( $dataProperty, $wikipage->getDataItem() );
			}	
		}
		return array( $indata, $more );
	}
	
	/**
	* Get an array of all properties for which there is some subject that
	* relates to the given value. The result is an array of SMWDIProperty
	* objects.
	*/	
	private function getSemanticInProperties( $graph, $uri, $requestoptions = null ) {
		$arr_objs  = array();
		$arr_props = array();
		// Now, ask for all incoming uris
		$theIncomingProperties = $graph->reversePropertyUris( $uri );
		foreach ( $theIncomingProperties as $inProp ) {	
			
			//getArraySubject :: get all subjects (from RDF) which have the needed property and 
			//its uri is a reference to given object
			$inPropResult = $this->getArraySubjects( $graph, $inProp, $uri ); 
			foreach( $inPropResult as $inPropSubject ){
				$uriPageName = $this->getInternalMapping( $inProp );
				$label = $inPropSubject['value'];
				$dataProperty = SMWDIProperty::newFromUserLabel( $label );
				$arr_objs[] = $dataProperty;       
				$arr_props[] = $inProp;             		
			}              
		}
		return array ( $arr_objs, $arr_props );
	}
	
	/**
	* Finds all subjects, properties and objects which are equal to needed subject, property and object
	* The result is an array each element has subject, property, object (all string)
	*/
	private function getSemanticInfos( $graph,$sSubject, $sProperty, $sObject,$requestoptions = null ) {
		$arr_triples = array();		
		$subjects = $graph->toArray();	
		
		foreach ( $subjects as $subject=>$properties ){
			if( $sSubject == null || $subject == $sSubject ){
				foreach( $properties as $property => $values ){
					if( $sProperty == null || $sProperty == $property ){
						foreach( $values as $object ){
							if( $this->isURI( $object['value'] ) ){
								if( $sObject==null || $object['value'] == $sObject ){
									$arr_triples[] = array( $subject, $property, $object['value'] );

									
								}
							}	 
						}
						
					}
				}
			}                    
		}	
		return $arr_triples;
	}
	
	/**
	* Check uri, uri as http://...
	* @param string $uri
	* return true if uri in normal format, else in other cases
	*/
	public static function isURI( $uri ){
		$info = parse_url( $uri );
		( !isset( $info['scheme'] ) ) ? $scheme   = "" : $scheme   = $info['scheme'];
		// Info: In front of host, we had // before, but those seem not needed, any more.
		//(!isset( $info['host'])    ) ? $host     = "" : $host     = "//".$info['host'];
		( !isset( $info['host']   ) ) ? $host     = "" : $host     = $info['host'];
		( !isset( $info['path']   ) ) ? $path     = "" : $path     = $info['path'];
		( !isset( $info['query']  ) ) ? $query    = "" : $query    = $info['query'];
		( !isset($info['fragment']) ) ? $fragment = "" : $fragment = $info['fragment'];
		if( $scheme == "" || $host.$path == "" )return false;
		else return true;
	}
	
	/**
	* Similar to getSemanticData(), but in this case regarding the Semantic Web.
	* @param String $uri
	*/
	private function getSemanticWebData( $graph, $uri ) {
		// Several possibilities: URI with redirect to RDF, URL with RDFa (but talking about what?),...

		// $data is of type SMWSemanticData
		$semanticDataResult = new SMWSemanticData( $this->subject->getDataItem() );

		// I want to show all incoming and outcoming links
		// ...ideally in the same style
		// Get the representation of the URI
		$theResource = $graph->resource( $uri );
		// Outgoing
		$theOutgoingProperties = $graph->propertyUris( $theResource );
		// for each, ask for the objects
		foreach ( $theOutgoingProperties as $outProp ) {

			$outPropResult = $this->getArrayObjects( $graph, $theResource, $outProp );
			
			// now, we have the subject, the property, the object (uri/literal)
			// replace blacklisted rdf(s) and owl properties
			$uri_blacklist = explode("\n", wfMessage('smw_uri_blacklist')->inContentLanguage()->text());
			
			foreach ($uri_blacklist as $uri) {

				$uri = trim($uri);
				if ($uri == mb_substr($outProp, 0, mb_strlen($uri))) { // disallowed URI!						

					if (strcmp($uri, "http://www.w3.org/1999/02/22-rdf-syntax-ns#") == 0) {
						$outProp = str_replace($uri, "rdf:", $outProp);
					} 
					else if (strcmp($uri, "http://www.w3.org/2000/01/rdf-schema#") == 0) {
						$outProp = str_replace($uri, "rdfs:", $outProp);
					} 
					else if (strcmp($uri, "http://www.w3.org/2002/07/owl#") == 0) {
						$outProp = str_replace($uri, "owl:", $outProp);
					}
				}
			}
			
			
			foreach ( $outPropResult as $outPropObject ) {
				/*
					* The question now is, what kind of propert.
					* If there is a page in the wiki, we simply use it as property.
					* Otherwise, we need to invent a new page with the URI as name
					*/
				
				// $this->debug($outPropObject["value"]);
				
				$uriPageName = $this->getInternalMapping( $outProp );
				$dataProperty = null;
				if (!isset( $uriPageName ) || $uriPageName == null || !strpos("Property:",$uriPageName )===0) {
					// There is no, we create a new property page
					/*
						* TODO: maybe register new property type that can display the property more
						* conveniently, e.g., with browse further: smwInitProperties
						*/
					$dataProperty = SMWDIProperty::newFromUserLabel( $outProp );
				} 
				
				
				else {					
					//the namespace has to be removed before creating a new SMWDIProperty
					$pageNameWithoutNamespace = str_replace ("Property:","",$uriPageName);
					$dataProperty = SMWDIProperty::newFromUserLabel( $pageNameWithoutNamespace );
				}


				// SMWDataItem, we only distinguish uri and literal
				// TODO: Maybe distinguish more, later, e.g., language
				$dataValue = null;

				if ( $outPropObject["type"] == "uri" ) {
					/*
						* If there is a page in the wiki with the value as equivalent URI, we
						* just use this page.
						*/
					$uriPageName = $this->getInternalMapping( $outPropObject["value"] );
					
					if ( !isset( $uriPageName ) && $uriPageName == null ) {
						// URI value
						$dataValue = SMWDataValueFactory::newTypeIDValue( '_uri', $outPropObject["value"], $dataProperty );
					} 		
					else {
						$dataValue = SMWDataValueFactory::newTypeIDValue( '_wpg', $uriPageName, $dataProperty );
					}
				} 
				else {
					// literal
					// $this->debug($outPropObject["value"],"vis:");
					$dataValue = SMWDataValueFactory::newTypeIDValue( '_txt', $outPropObject["value"], $dataProperty );
					//$dataItem = new SMWDIString($outPropObject["value"]);
				}

				// some objects have invalid type and print warning triangle instead of object info
				//in this case object has class SMWDIError
				// in this case this object wouldn't be printed
				if( !( get_class( $dataValue->getDataItem() ) == "SMWDIError" ) ){
					$semanticDataResult->addPropertyObjectValue( $dataProperty, $dataValue->getDataItem() );
				}	
			}
		}
		
		return $semanticDataResult;
	}

	/**
	* Checks if the URI is known and an equivalent URI to any of the already
	* existing pages. If so, it returns the name of the page, otherwise null.
	*
	* @param string $uri Identifier for an entity
	* @return string The name of the page describing the entity, otherwise null
	*/
	public function getInternalMapping( $uri ) {

		// Watch out correct spelling: [[equivalent URI::XXX]]
		$equivalentURI = new SMWDIProperty( "_URI" );		
		$urivalue = SMWDataValueFactory::newPropertyObjectValue( $equivalentURI, $uri );
		
		// $values = smwfGetStore()->getPropertySubjects( $property, $this->subject->getDataItem(), $valoptions );
		
		if ($urivalue->isValid()) {
			$results = smwfGetStore()->getPropertySubjects( $equivalentURI, $urivalue->getDataItem() );
		}
		else {
			$results = array();
		}		

		$mappings = array();
		foreach( $results as $result ) {
			//$mappings[] = $result->getWikiValue();
			$mappings[] = $result->getTitle()->getFullText();
			
		}
		if ( count( $mappings ) === 0) return null;
		return $mappings[0]; // TODO Only returns one. There never should be more than one.
	}

	private function getArrayObjects( $graph, $resource, $property ) {

		// $this->debug("parameters: ".$resource." and ".$property);	

		$arr_objects = array();

		// TODO: ignore bnodes, language tags, for now.

		// For each outgoing uri, get the resources/literals

		$theOutgoingUriValues = $graph->allResources( $resource, "<".$property.">" );
		
		foreach ( $theOutgoingUriValues as $uri ) {
			// only non-bnodes
			
			if ( !$uri->isBnode() ) {
				$res = array( "type" => "uri", "value" => $uri->getUri() );
				$arr_objects[] = $res;
			}
		}

		$theOutgoingLiteralValues =  $graph->allLiterals( $resource, "<".$property.">");
		
		foreach ( $theOutgoingLiteralValues as $literal ) {
			if ( $literal instanceof EasyRdf_Literal_Date || $literal instanceof EasyRdf_Literal_DateTime ) {
				$res = array( "type" => "literal", "value" => $literal->dumpValue( false ) );
			} else {
				$res = array( "type" => "literal", "value" => $literal->getValue() );
			}

			$arr_objects[] = $res;
		}

		return $arr_objects;
	}
	
	/**
	* 
	* get all subjects (from RDF) which have the needed $property and its uri is a reference to needed object 
	* 
	* @param unknown_type $graph
	* @param unknown_type $property
	* @param unknown_type $object
	*/
	private function getArraySubjects( $graph, $property, $object ) {

		$arr_subjects = array();

		// For each incoming uri, get the resources (
		/*
		* easyRDF is only storing whether incoming property, but not who it is
		* This means, we need to go through all other subjects and check
		* whether outgoing link is our subject
		*/
		$allResources = $graph->resources();

		// for each resource, get the values for each of the incoming properties
		foreach ( $allResources as $aResource ) {
			$allSpecResources = $graph->allResources( $aResource, "<".$property.">" );

			// For each resource, check if our $object
			foreach ( $allSpecResources as $aSpecResource ) {
				if ( !$aSpecResource->isBnode() ) {
					if ( $aSpecResource->getUri() == $object ) {
						$res = array( "type" => "uri", "value" => $aSpecResource->getUri() );
						$arr_subjects[] = $res;
					}
				}
			}
		}

		return $arr_subjects;
	}

	/**
	* Creates the HTML table displaying the Semantic Web data of one uri
	*
	* @param SMWSemanticData $data
	* @param boolean $left Should properties be displayed on the left side?
	* @param unknown_type $incoming Is this an incoming? Or an outgoing? Just important for displaying.
	*
	* @return A string containing the HTML with the factbox
	*/
	private function displaySemanticWebData ( SMWSemanticData $data, $left = true, $incoming = false ) {
		// Some of the CSS classes are different for the left or the right side.
		// In this case, there is an "i" after the "smwb-". This is set here.
		$ccsPrefix = $left ? 'smwb-' : 'smwb-i';
		
		$html = "<table class=\"{$ccsPrefix}factbox\" cellpadding=\"0\" cellspacing=\"0\">\n";

		$diProperties = $data->getProperties();
		$noresult = true;
		foreach ( $diProperties as $diProperty ) {
		
			$dvProperty = SMWDataValueFactory::newDataItemValue( $diProperty, null );
			
			if ( $dvProperty->isVisible() ) {
				$dvProperty->setCaption( $this->getPropertyLabel( $dvProperty, $incoming ) );
				$proptext = $dvProperty->getShortHTMLText( smwfGetLinker() ) . "\n";				
			} elseif ( $diProperty->getKey() == '_INST' ) {
				$proptext = smwfGetLinker()->specialLink( 'Categories' );
			} elseif ( $diProperty->getKey() == '_REDI' ) {
				$proptext = smwfGetLinker()->specialLink( 'Listredirects', 'isredirect' );
			} else {
				continue; // skip this line
			}

			
			$label = $this->getPropertyLabel( $dvProperty, $incoming );
			
			// blacklisted URIs are replaced in method getSemanticWebData. 
			// In case of replacement, they shall not be linked to pages but shown as strings
			if ((0 === strpos($label, 'rdf:')) || (0 === strpos($label, 'rdfs:')) || (0 === strpos($label, 'owl:'))){
				$head  = "<th>" . $label . "</th>\n";
			}
			else {
				$head  = "<th>" . $proptext . "</th>\n";
			}
			
			$body  = "<td>\n";

			$values = $data->getPropertyValues( $diProperty );
			
			if ( $incoming && ( count( $values ) >= SWBSpecialBrowseSW::$incomingvaluescount ) ) {
				$moreIncoming = true;
				array_pop( $values );
			} else {
				$moreIncoming = false;
			}

			$first = true;
			foreach ( $values as /* SMWDataItem */ $di ) {
				if ( $first ) {
					$first = false;
				} else {
					$body .= ', ';
				}
				
				if ( $incoming ) {
					$dv = SMWDataValueFactory::newDataItemValue( $di, null );
				} else {
					//slightly different from SMW_SpecialBrowse
					$dv = SMWDataValueFactory::newDataItemValue( $di, null );
				}

				$body .= "<span class=\"{$ccsPrefix}value\">" .
				//slightly different from SMW_SpecialBrowse
				$this->displaySemanticValue( $dvProperty, $dv, $incoming ) . "</span>\n";
			}

			if ( $moreIncoming ) { // link to the remaining incoming pages:
				$body .= Html::element(
				'a',
				array(
				'href' => SpecialPage::getSafeTitleFor( 'SearchByProperty' )->getLocalURL( array(
				'property' => $dvProperty->getWikiValue(), 
				'value' => $this->subject->getWikiValue()
				) )
				),
				wfMessage( 'smw_browse_more' )->text()
				);

			}

			$body .= "</td>\n";

			// display row
			$html .= "<tr class=\"{$ccsPrefix}propvalue\">\n" .
			( $left ? ( $head . $body ):( $body . $head ) ) . "</tr>\n";
			$noresult = false;
		} // end foreach properties

		if ( $noresult ) {
			$html .= "<tr class=\"smwb-propvalue\"><th> &#160; </th><td><em>" .
			wfMessage( $incoming ? 'swb_browse_no_incoming':'swb_browse_no_outgoing' )->text(). "</em></td></tr>\n";
		}
		$html .= "</table>\n";
		
		return $html;
	}

	/**
	* Creates the HTML table displaying the data of one subject.
	*
	* @param[in] $data SMWSemanticData  The data to be displayed
	* @param[in] $left bool  Should properties be displayed on the left side?
	* @param[in] $incoming bool  Is this an incoming? Or an outgoing?
	*
	* @return A string containing the HTML with the factbox
	*/
	private function displayData( SMWSemanticData $data, $left = true, $incoming = false ) {
		// Some of the CSS classes are different for the left or the right side.
		// In this case, there is an "i" after the "smwb-". This is set here.
		$ccsPrefix = $left ? 'smwb-' : 'smwb-i';

		$html = "<table class=\"{$ccsPrefix}factbox\" cellpadding=\"0\" cellspacing=\"0\">\n";

		$diProperties = $data->getProperties();
		$noresult = true;
		foreach ( $diProperties as $key => $diProperty ) {
			$dvProperty = SMWDataValueFactory::newDataItemValue( $diProperty, null );

			if ( $dvProperty->isVisible() ) {
				$dvProperty->setCaption( $this->getPropertyLabel( $dvProperty, $incoming ) );
				$proptext = $dvProperty->getShortHTMLText( smwfGetLinker() ) . "\n";
			} elseif ( $diProperty->getKey() == '_INST' ) {
				$proptext = smwfGetLinker()->specialLink( 'Categories' );
			} elseif ( $diProperty->getKey() == '_REDI' ) {
				$proptext = smwfGetLinker()->specialLink( 'Listredirects', 'isredirect' );
			} else {
				continue; // skip this line
			}

			$head  = '<th>' .  $proptext . "</th>\n";

			$body  = "<td>\n";

			$values = $data->getPropertyValues( $diProperty );
			
			if ( $incoming && ( count( $values ) >= SMWSpecialBrowse::$incomingvaluescount ) ) {
				$moreIncoming = true;
				array_pop( $values );
			} else {
				$moreIncoming = false;
			}
			
			$first = true;
			foreach ( $values as /* SMWDataItem */ $di ) {
				if ( $first ) {
					$first = false;
				} else {
					$body .= ', ';
				}

				if ( $incoming ) {
					$dv = SMWDataValueFactory::newDataItemValue( $di, null );
				} else {
					$dv = SMWDataValueFactory::newDataItemValue( $di, $diProperty );
				}
				
				$body .= "<span class=\"{$ccsPrefix}value\">" .
				$this->displayValue( $dvProperty, $dv, $incoming ) . "</span>\n";
			}

			if ( $moreIncoming ) { // link to the remaining incoming pages:
				$body .= Html::element(
				'a',
				array(
				'href' => SpecialPage::getSafeTitleFor( 'SearchByProperty' )->getLocalURL( array(
				'property' => $dvProperty->getWikiValue(),
				'value' => $this->subject->getWikiValue()
				) )
				),
				wfMessage( 'smw_browse_more' )->text()
				);

			}

			$body .= "</td>\n";

			// display row
			$html .= "<tr class=\"{$ccsPrefix}propvalue\">\n" .
			( $left ? ( $head . $body ):( $body . $head ) ) . "</tr>\n";
			$noresult = false;
		} // end foreach properties

		if ( $noresult ) {
			$html .= "<tr class=\"smwb-propvalue\"><th> &#160; </th><td><em>" .
			wfMessage( $incoming ? 'smw_browse_no_incoming':'smw_browse_no_outgoing' )->text() . "</em></td></tr>\n";
		}
		$html .= "</table>\n";
		return $html;
	}

	/**
	* Displays a value, including all relevant links (browse and search by property)
	*
	* @param[in] $property SMWPropertyValue  The property this value is linked to the subject with
	* @param[in] $value SMWDataValue  The actual value
	* @param[in] $incoming bool  If this is an incoming or outgoing link
	*
	* @return string  HTML with the link to the article, browse, and search pages
	*/
	private function displaySemanticValue( SMWPropertyValue $property, SMWDataValue $dataValue, $incoming ) {
		$linker = smwfGetLinker();
		$html = $dataValue->getLongHTMLText( $linker );

		SMWInfolink::decodeParameters();
		if ( $dataValue->getTypeID() == '_wpg' ) {
			$html .= "&#160;" . SWBInfolink::newBrowsingLink( '+', $dataValue->getLongWikiText() )->getHTML( $linker );
		} elseif ( $incoming && $property->isVisible() ) {
			$html .= "&#160;" . SWBInfolink::newInversePropertySearchLink( '+', $dataValue->getTitle(), $property->getDataItem()->getLabel(), 'smwsearch' )->getHTML( $linker );
		} else {
			$html .= $dataValue->getInfolinkText( SMW_OUTPUT_HTML, $linker );

			if ($dataValue->getTypeID() == "_uri") {
				// Provide link for browsing
				$html .= "&#160;" . SWBInfolink::newBrowsingLink( '+', $dataValue->getLongWikiText() )->getHTML( $linker );
			}
		}

		return $html;
	}

	/**
	* Displays a value, including all relevant links (browse and search by property)
	*
	* @param[in] $property SMWPropertyValue  The property this value is linked to the subject with
	* @param[in] $value SMWDataValue  The actual value
	* @param[in] $incoming bool  If this is an incoming or outgoing link
	*
	* @return string  HTML with the link to the article, browse, and search pages
	*/
	private function displayValue( SMWPropertyValue $property, SMWDataValue $dataValue, $incoming ) {
		$linker = smwfGetLinker();

		$html = $dataValue->getLongHTMLText( $linker );

		// TODO: How to I trigger autoload if extends?
		SMWInfolink::decodeParameters();
		if ( $dataValue->getTypeID() == '_wpg' ) {
			$html .= "&#160;" . SWBInfolink::newBrowsingLink( '+', $dataValue->getLongWikiText() )->getHTML( $linker );
		} elseif ( $incoming && $property->isVisible() ) {
			$html .= "&#160;" . SWBInfolink::newInversePropertySearchLink( '+', $dataValue->getTitle(), $property->getDataItem()->getLabel(), 'smwsearch' )->getHTML( $linker );
		} else {
			$html .= $dataValue->getInfolinkText( SMW_OUTPUT_HTML, $linker );
		}

		return $html;
	}

	/**
	* Displays the subject that is currently being browsed to.
	*
	* @return A string containing the HTML with the subject line
	*/
	private function displayHead() {
		global $wgOut;

		$wgOut->setHTMLTitle( $this->subject->getTitle() );
		$html = "<table class=\"smwb-factbox\" cellpadding=\"0\" cellspacing=\"0\">\n" .
		"<tr class=\"smwb-title\"><td colspan=\"2\">\n" .
		$this->subject->getLongHTMLText( smwfGetLinker() ) . "\n" .
		"</td></tr>\n</table>\n";
		return $html;
	}
	
	/**
	* Displays the equivalent URI that is currently being browsed to.
	*
	* @return A string containing the HTML with the subject line
	*/
	private function displaySemanticHead($uri) {
		global $wgOut;

		$wgOut->setHTMLTitle( $this->subject->getTitle() );
		$html  = "<table class=\"smwb-factbox\" cellpadding=\"0\" cellspacing=\"0\">\n";
		$html .= "<tr class=\"smwb-title\"><td colspan=\"2\">\n";
		// TODO: No link but full URI should be shown
		$html .= $uri. "\n"; // @todo Replace makeLinkObj with link as soon as we drop MW1.12 compatibility
		$html .= "</td></tr>\n";
		$html .= "</table>\n";

		return $html;
	}

	/**
	* Creates the HTML for the center bar including the links with further navigation options.
	*
	* @return string  HTMl with the center bar
	*/
	private function displayCenter() {
		return "<a name=\"smw_browse_incoming\"></a>\n" .
		"<table class=\"smwb-factbox\" cellpadding=\"0\" cellspacing=\"0\">\n" .
		"<tr class=\"smwb-center\"><td colspan=\"2\">\n" .
		( $this->showincoming ?
		$this->linkHere( wfMessage( 'smw_browse_hide_incoming' )->text(), true, false, 0 ):
		$this->linkHere( wfMessage( 'smw_browse_show_incoming' )->text(), true, true, $this->offset ) ) .
		"&#160;\n" . "</td></tr>\n" . "</table>\n";
	}

	/**
	* Creates the HTML for the bottom bar including the links with further navigation options.
	*
	* @param[in] $more bool  Are there more inproperties to be displayed?
	* @return string  HTMl with the bottom bar
	*/
	private function displayBottom( $more ) {
		$html  = "<table class=\"smwb-factbox\" cellpadding=\"0\" cellspacing=\"0\">\n" .
		"<tr class=\"smwb-center\"><td colspan=\"2\">\n";
		global $smwgBrowseShowAll;
		if ( !$smwgBrowseShowAll ) {
			if ( ( $this->offset > 0 ) || $more ) {
				$offset = max( $this->offset - SMWSpecialBrowse::$incomingpropertiescount + 1, 0 );
				$html .= ( $this->offset == 0 ) ? wfMessage( 'smw_result_prev' )->text():
				$this->linkHere( wfMessage( 'smw_result_prev' )->text(), $this->showoutgoing, true, $offset );
				$offset = $this->offset + SMWSpecialBrowse::$incomingpropertiescount - 1;
				// @todo FIXME: i18n patchwork.
				$html .= " &#160;&#160;&#160;  <strong>" . wfMessage( 'smw_result_results' )->text() . " " . ( $this->offset + 1 ) .
				" – " . ( $offset ) . "</strong>  &#160;&#160;&#160; ";
				$html .= $more ? $this->linkHere( wfMessage( 'smw_result_next' )->text(), $this->showoutgoing, true, $offset ):wfMessage( 'smw_result_next' )->text();
			}
		}
		$html .= "&#160;\n" . "</td></tr>\n" . "</table>\n";
		return $html;
	}

	/**
	* Creates the HTML for a link to this page, with some parameters set.
	*
	* @param[in] $text string  The anchor text for the link
	* @param[in] $out bool  Should the linked to page include outgoing properties?
	* @param[in] $in bool  Should the linked to page include incoming properties?
	* @param[in] $offset int  What is the offset for the incoming properties?
	*
	* @return string  HTML with the link to this page
	*/
	private function linkHere( $text, $out, $in, $offset ) {	
		$frag = ( $text == wfMessage( 'smw_browse_show_incoming' )->text() ) ? '#smw_browse_incoming' : '';

		return Html::element(
		'a',
		array(
		'href' => SpecialPage::getSafeTitleFor( 'BrowseSW' )->getLocalURL( array(
		'offset' => $offset,
		'dir' => $out ? ( $in ? 'both' : 'out' ) : 'in',
		'article' => $this->subject->getLongWikiText()
		) ) . $frag
		),
		$text
		);
	}

	/**
	* Creates a Semantic Data object with the incoming properties instead of the
	* usual outproperties.
	*
	* @return array(SMWSemanticData, bool)  The semantic data including all inproperties, and if there are more inproperties left
	*/
	private function getInData() {
		$indata = new SMWSemanticData( $this->subject->getDataItem() );
		$options = new SMWRequestOptions();
		$options->sort = true;
		$options->limit = SWBSpecialBrowseSW::$incomingpropertiescount;
		if ( $this->offset > 0 ) $options->offset = $this->offset;

		$inproperties = smwfGetStore()->getInProperties( $this->subject->getDataItem(), $options );

		if ( count( $inproperties ) == SWBSpecialBrowseSW::$incomingpropertiescount ) {
			$more = true;
			array_pop( $inproperties ); // drop the last one
		} else {
			$more = false;
		}

		$valoptions = new SMWRequestOptions();
		$valoptions->sort = true;
		$valoptions->limit = SWBSpecialBrowseSW::$incomingvaluescount;

		foreach ( $inproperties as $property ) {
			$values = smwfGetStore()->getPropertySubjects( $property, $this->subject->getDataItem(), $valoptions );
			foreach ( $values as $value ) {
				$indata->addPropertyObjectValue( $property, $value );
			}
		}

		return array( $indata, $more );
	}

	/**
	* Figures out the label of the property to be used. For outgoing ones it is just
	* the text, for incoming ones we try to figure out the inverse one if needed,
	* either by looking for an explicitly stated one or by creating a default one.
	*
	* @param[in] $property SMWPropertyValue  The property of interest
	* @param[in] $incoming bool  If it is an incoming property
	*
	* @return string  The label of the property
	*/
	private function getPropertyLabel( SMWPropertyValue $property, $incoming = false ) {
		global $smwgBrowseShowInverse;

		if ( $incoming && $smwgBrowseShowInverse ) {
			$oppositeprop = SMWPropertyValue::makeUserProperty( wfMessage( 'swb_inverse_label_property' )->text() );
			$labelarray = &smwfGetStore()->getPropertyValues( $property->getDataItem()->getDiWikiPage(), $oppositeprop->getDataItem() );
			$rv = ( count( $labelarray ) > 0 ) ? $labelarray[0]->getLongWikiText():
			wfMessage( 'swb_inverse_label_default', $property->getWikiValue() )->text();
		} else {
			$rv = $property->getWikiValue();
		}

		return $this->unbreak( $rv );
	}

	/**
	* Creates the query form in order to quickly switch to a specific article.
	*
	* @return A string containing the HTML for the form
	*/	
	private function queryForm() {
		
		self::addAutoComplete();		
		
		SMWOutputs::requireResource( 'ext.smw.browse' );
		$title = SpecialPage::getTitleFor( 'BrowseSW' );
		return '  <form name="smwbrowse" action="' . htmlspecialchars ($title->getLocalURL()) . '" method="get">' . "\n" .
		'    <input type="hidden" name="title" value="' . $title->getPrefixedText() . '"/>' .
		wfMessage( 'swb_browse_article' )->text() . "<br />\n" .
		'    <input type="text" name="article" id="page_input_box" value="' . htmlspecialchars( $this->articletext ) . '" />' . "\n" .
		'    <input type="submit" value="' . wfMessage( 'swb_browse_go' )->text(). "\"/>\n" .
		"  </form>\n";
	}

	/**
	* Creates the JS needed for adding auto-completion to queryForm(). Uses the
	* MW API to fetch suggestions.
	*/
	private static function addAutoComplete(){
		SMWOutputs::requireResource( 'jquery.ui.autocomplete' );

		$javascript_autocomplete_text = <<<END
		<script type="text/javascript">
		jQuery(document).ready(function(){
			jQuery("#page_input_box").autocomplete({
			minLength: 3,
			source: function(request, response) {
					jQuery.getJSON(wgScriptPath+'/api.php?action=opensearch&limit=10&namespace=0&format=jsonfm&search='+request.term, function(data){
						response(data[1]);
					});
				}
			});
		});
		</script>
END;

		SMWOutputs::requireScript( 'smwAutocompleteSpecialBrowse', $javascript_autocomplete_text );
	}
	
	/**
	* Creates the JS needed for 
	*/
	private static function setStartMenu(){
		SMWOutputs::requireResource( 'jquery.ui.autocomplete' );

		$javascript_autocomplete_text = <<<END
		<script type="text/javascript">
		jQuery(document).ready(function(){
			var xxx="hallo";
			echo "xxx=".xxx;
			druck = window.open ('', 'fenster', xxx);
			druck.print();
			
		});
		</script>
END;

		SMWOutputs::requireScript( 'smwAutocompleteSpecialBrowse', $javascript_autocomplete_text );
	}
	
	/**
	* Replace the last two space characters with unbreakable spaces for beautification.
	*
	* @param[in] $text string  Text to be transformed. Does not need to have spaces
	* @return string  Transformed text
	*/
	private function unbreak( $text ) {
		$nonBreakingSpace = html_entity_decode( '&#160;', ENT_NOQUOTES, 'UTF-8' );
		$text = preg_replace( '/[\s]/u', $nonBreakingSpace, $text, - 1, $count );
		return $count > 2 ? preg_replace( '/($nonBreakingSpace)/u', ' ', $text, max( 0, $count - 2 ) ):$text;
	}

	/* can be used for testing
	* 1.parameter is the text to display 
	* 2.parameter is the name of the text
	*/ 
	public static function debug( $displaytext,$name=""){
		echo $name;
		echo $name."='".$displaytext."' "."<br />";
	}

	protected function getGroupName() {
		return 'smw_group';
	}
}


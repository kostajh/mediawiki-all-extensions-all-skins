<?php

namespace MultiMaps;

class LeafletTest extends \PHPUnit\Framework\TestCase {

	/**
	 * @var Leaflet
	 */
	protected $object;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 */
	protected function setUp() : void {
		$this->object = new Leaflet();
	}

	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 */
	protected function tearDown() : void {
	}

	public function testParseMarkerInZerro() {
		$this->assertMapDataEquals(
			\FormatJson::encode( $this->object->getMapData( [ '0,0', 'service=leaflet' ] ) ),
			'{"markers":[{"pos":[{"lat":0,"lon":0}]}],"zoom":14,"center":{"lat":0,"lon":0}}'
		);
	}

	public function testParseOneMarker() {
		$this->assertMapDataEquals(
			\FormatJson::encode( $this->object->getMapData( [ '55.7557860, 37.6176330', 'service=leaflet' ] ) ),
			'{"markers":[{"pos":[{"lat":55.755786,"lon":37.617633}]}],"zoom":14,"center":{"lat":55.755786,"lon":37.617633}}'
		);
	}

	public function testParseMarkers() {
		$this->assertMapDataEquals(
			\FormatJson::encode( $this->object->getMapData( [ '52.429222277955134,13.359375; 53.38332836757156,3.33984375; 52.5897007687178,2.548828125; 52.855864177853995,3.515625; 53.33087298301704,1.7578125', 'service=leaflet' ] ) ),
			'{"markers":[{"pos":[{"lat":52.429222277955,"lon":13.359375}]},{"pos":[{"lat":53.383328367572,"lon":3.33984375}]},{"pos":[{"lat":52.589700768718,"lon":2.548828125}]},{"pos":[{"lat":52.855864177854,"lon":3.515625}]},{"pos":[{"lat":53.330872983017,"lon":1.7578125}]}],"bounds":{"ne":{"lat":53.383328367572,"lon":13.359375},"sw":{"lat":52.429222277955,"lon":1.7578125}}}'
		);
	}

	public function testParseOneMarkerTextOnly() {
		$this->assertMapDataEquals(
			\FormatJson::encode( $this->object->getMapData( [ '55.7557860, 37.6176330~~This is text', 'service=leaflet' ] ) ),
			'{"markers":[{"pos":[{"lat":55.755786,"lon":37.617633}],"text":"This is text"}],"zoom":14,"center":{"lat":55.755786,"lon":37.617633}}'
		);
	}

	public function testParseOneMarkerTitleOnly() {
		$this->assertMapDataEquals(
			\FormatJson::encode( $this->object->getMapData( [ '55.7557860, 37.6176330~This is title ~', 'service=leaflet' ] ) ),
			'{"markers":[{"pos":[{"lat":55.755786,"lon":37.617633}],"title":"This is title"}],"zoom":14,"center":{"lat":55.755786,"lon":37.617633}}'
		);
	}

	public function testParseOneMarkerTitleText() {
		$this->assertMapDataEquals(
			\FormatJson::encode( $this->object->getMapData( [ '55.7557860, 37.6176330~This is title ~ This is text ', 'service=leaflet' ] ) ),
			'{"markers":[{"pos":[{"lat":55.755786,"lon":37.617633}],"title":"This is title","text":"This is text"}],"zoom":14,"center":{"lat":55.755786,"lon":37.617633}}'
		);
	}

	public function testParseOneMarkerNamedTextOnly() {
		$this->assertMapDataEquals(
			\FormatJson::encode( $this->object->getMapData( [ '55.7557860, 37.6176330~Text = This is text', 'service=leaflet' ] ) ),
			'{"markers":[{"pos":[{"lat":55.755786,"lon":37.617633}],"text":"This is text"}],"zoom":14,"center":{"lat":55.755786,"lon":37.617633}}'
		);
	}

	public function testParseOneMarkerNamedTitleOnly() {
		$this->assertMapDataEquals(
			\FormatJson::encode( $this->object->getMapData( [ '55.7557860, 37.6176330~ title=This is title ~', 'service=leaflet' ] ) ),
			'{"markers":[{"pos":[{"lat":55.755786,"lon":37.617633}],"title":"This is title"}],"zoom":14,"center":{"lat":55.755786,"lon":37.617633}}'
		);
	}

	public function testParseOneMarkerNamedTextAndTile() {
		$this->assertMapDataEquals(
			\FormatJson::encode( $this->object->getMapData( [ '55.7557860, 37.6176330~tExT = This is text ~This is title ', 'service=leaflet' ] ) ),
			'{"markers":[{"pos":[{"lat":55.755786,"lon":37.617633}],"text":"This is text","title":"This is title"}],"zoom":14,"center":{"lat":55.755786,"lon":37.617633}}'
		);
	}

	public function testParseOneLine() {
		$this->assertMapDataEquals(
			\FormatJson::encode( $this->object->getMapData( [ ' ', 'lines = 53.225768435790194 , 23.466796875 : 53.9560855309879,29.1796875', 'service=leaflet' ] ) ),
			'{"lines":[{"pos":[{"lat":53.22576843579,"lon":23.466796875},{"lat":53.956085530988,"lon":29.1796875}]}],"bounds":{"ne":{"lat":53.956085530988,"lon":29.1796875},"sw":{"lat":53.22576843579,"lon":23.466796875}}}'
		);
	}

	public function testParseLines() {
		$this->assertMapDataEquals(
			\FormatJson::encode( $this->object->getMapData( [ ' ', 'lines=53.225768435790194,23.466796875:53.9560855309879,29.1796875:56.46249048388979,31.11328125:59.31076795603884,30.673828125:60.84491057364915,27.333984375:60.930432202923335,21.26953125:59.17592824927136,16.611328125:56.218923189166624,16.259765625:54.1109429427243,19.599609375:53.225768435790194,23.466796875', 'service=leaflet' ] ) ),
			'{"lines":[{"pos":[{"lat":53.22576843579,"lon":23.466796875},{"lat":53.956085530988,"lon":29.1796875},{"lat":56.46249048389,"lon":31.11328125},{"lat":59.310767956039,"lon":30.673828125},{"lat":60.844910573649,"lon":27.333984375},{"lat":60.930432202923,"lon":21.26953125},{"lat":59.175928249271,"lon":16.611328125},{"lat":56.218923189167,"lon":16.259765625},{"lat":54.110942942724,"lon":19.599609375},{"lat":53.22576843579,"lon":23.466796875}]}],"bounds":{"ne":{"lat":60.930432202923,"lon":31.11328125},"sw":{"lat":53.22576843579,"lon":16.259765625}}}'
		);
	}

	public function testParseOnePolygon() {
		$this->assertMapDataEquals(
			\FormatJson::encode( $this->object->getMapData( [ ' ', 'polygons=  62.103882522897855,   5.09765625 :   58.309488840677645,5.712890625 : 58.95000823335702,10.8984375', 'service=leaflet' ] ) ),
			'{"polygons":[{"pos":[{"lat":62.103882522898,"lon":5.09765625},{"lat":58.309488840678,"lon":5.712890625},{"lat":58.950008233357,"lon":10.8984375}]}],"bounds":{"ne":{"lat":62.103882522898,"lon":10.8984375},"sw":{"lat":58.309488840678,"lon":5.09765625}}}'
		);
	}

	public function testParsePolygons() {
		$this->assertMapDataEquals(
			\FormatJson::encode( $this->object->getMapData( [ 'polygons=62.103882522897855,5.09765625:58.309488840677645,5.712890625:58.95000823335702,10.8984375:61.68987220046001,12.83203125:63.35212928507874,11.865234375:64.1297836764257,13.974609375:65.58572002329473,14.326171875:68.56038368664157,18.28125:68.52823492039876,20.126953125:69.2249968541159,20.56640625:69.2249968541159,21.708984375:68.6245436634471,23.466796875:69.00567519658819,25.400390625:69.96043926902487,26.630859375:70.1403642720717,28.564453125:69.25614923150721,28.828125:69.83962194067463,31.201171875:70.75796562654924,29.8828125:71.10254274232307,25.576171875:70.72897946208789,22.67578125:69.99053495947653,18.10546875:67.84241647327927,13.095703125:68.33437594128185,15.908203125:63.704722429433225,9.4921875:63.782486031165014,8.61328125', 'service=leaflet' ] ) ),
			'{"polygons":[{"pos":[{"lat":62.103882522898,"lon":5.09765625},{"lat":58.309488840678,"lon":5.712890625},{"lat":58.950008233357,"lon":10.8984375},{"lat":61.68987220046,"lon":12.83203125},{"lat":63.352129285079,"lon":11.865234375},{"lat":64.129783676426,"lon":13.974609375},{"lat":65.585720023295,"lon":14.326171875},{"lat":68.560383686642,"lon":18.28125},{"lat":68.528234920399,"lon":20.126953125},{"lat":69.224996854116,"lon":20.56640625},{"lat":69.224996854116,"lon":21.708984375},{"lat":68.624543663447,"lon":23.466796875},{"lat":69.005675196588,"lon":25.400390625},{"lat":69.960439269025,"lon":26.630859375},{"lat":70.140364272072,"lon":28.564453125},{"lat":69.256149231507,"lon":28.828125},{"lat":69.839621940675,"lon":31.201171875},{"lat":70.757965626549,"lon":29.8828125},{"lat":71.102542742323,"lon":25.576171875},{"lat":70.728979462088,"lon":22.67578125},{"lat":69.990534959477,"lon":18.10546875},{"lat":67.842416473279,"lon":13.095703125},{"lat":68.334375941282,"lon":15.908203125},{"lat":63.704722429433,"lon":9.4921875},{"lat":63.782486031165,"lon":8.61328125}]}],"bounds":{"ne":{"lat":71.102542742323,"lon":31.201171875},"sw":{"lat":58.309488840678,"lon":5.09765625}}}'
		);
	}

	public function testParseOneRectangle() {
		$this->assertMapDataEquals(
			\FormatJson::encode( $this->object->getMapData( [ ' ', ' ', ' ', 'rectangles  =51.83577752045248  ,33.837890625 : 46.37725420510028 ,23.37890625 ', 'service=leaflet' ] ) ),
			'{"rectangles":[{"pos":[{"lat":51.835777520452,"lon":33.837890625},{"lat":46.3772542051,"lon":23.37890625}]}],"bounds":{"ne":{"lat":51.835777520452,"lon":33.837890625},"sw":{"lat":46.3772542051,"lon":23.37890625}}}'
		);
	}

	public function testParseRectangles() {
		$this->assertMapDataEquals(
			\FormatJson::encode( $this->object->getMapData( [ ' ', 'rectangles  =51.83577752045248  ,33.837890625 : 46.37725420510028 ,23.37890625 ', 'rectangle= 10°10\'10", 10°10\'10": 40°, 40°', 'service=leaflet' ] ) ),
			'{"rectangles":[{"pos":[{"lat":51.835777520452,"lon":33.837890625},{"lat":46.3772542051,"lon":23.37890625}]},{"pos":[{"lat":10.169444444444,"lon":10.169444444444},{"lat":40,"lon":40}]}],"bounds":{"ne":{"lat":51.835777520452,"lon":40},"sw":{"lat":10.169444444444,"lon":10.169444444444}}}'
		);
	}

	public function testParseFalseRectangle() {
		$badrectanglecoord = '10°10°10", 10°10\'10"';
		$this->assertMapDataEquals(
			\FormatJson::encode( $this->object->getMapData( [ ' ', 'rectangles  =51.83577752045248  ,33.837890625 : 46.37725420510028 ,23.37890625 ', "rectangle=$badrectanglecoord: 40, 40", 'service=leaflet' ] ) ),
			'{"rectangles":[{"pos":[{"lat":51.835777520452,"lon":33.837890625},{"lat":46.3772542051,"lon":23.37890625}]}],"bounds":{"ne":{"lat":51.835777520452,"lon":33.837890625},"sw":{"lat":46.3772542051,"lon":23.37890625}}}'
		);

		$rectangle = new Rectangle();
		$this->assertEquals(
			$this->object->getErrorMessages(),
			[
				\wfMessage( 'multimaps-unable-parse-coordinates', $badrectanglecoord )->escaped(),
				\wfMessage( 'multimaps-unable-create-element', $rectangle->getElementName() )->escaped(),
			]
		);

		$this->assertMapDataEquals(
			\FormatJson::encode( $this->object->getMapData( [ '; ', 'rectangles  =51.83577752045248  ,33.837890625 : 46.37725420510028 ,23.37890625 ', "rectangle=40, 40:$badrectanglecoord", 'service=leaflet' ] ) ),
			'{"rectangles":[{"pos":[{"lat":51.835777520452,"lon":33.837890625},{"lat":46.3772542051,"lon":23.37890625}]}],"bounds":{"ne":{"lat":51.835777520452,"lon":33.837890625},"sw":{"lat":46.3772542051,"lon":23.37890625}}}'
		);
		$this->assertEquals(
			$this->object->getErrorMessages(),
			[
				\wfMessage( 'multimaps-unable-parse-coordinates', $badrectanglecoord )->escaped(),
				\wfMessage( 'multimaps-unable-create-element', $rectangle->getElementName() )->escaped(),
			]
		);

		$this->assertMapDataEquals(
			\FormatJson::encode( $this->object->getMapData( [ 'rectangles  =51.83577752045248  ,33.837890625 : 46.37725420510028 ,23.37890625 ', "rectangle=40, 40 : $badrectanglecoord : 20,20", 'service=leaflet' ] ) ),
			'{"rectangles":[{"pos":[{"lat":51.835777520452,"lon":33.837890625},{"lat":46.3772542051,"lon":23.37890625}]}],"bounds":{"ne":{"lat":51.835777520452,"lon":33.837890625},"sw":{"lat":46.3772542051,"lon":23.37890625}}}'
		);
		$this->assertEquals(
			$this->object->getErrorMessages(),
			[
				\wfMessage( 'multimaps-square-wrong-number-points', 3 )->escaped(),
				\wfMessage( 'multimaps-unable-create-element', $rectangle->getElementName() )->escaped(),
			]
		);
	}

	public function testParseOneCircle() {
		$this->assertMapDataEquals(
			\FormatJson::encode( $this->object->getMapData( [ 'circle=57.42129439209404,23.90625 : 326844.60518253763;', 'service=leaflet' ] ) ),
			'{"circles":[{"radius":[326844.60518254],"pos":[{"lat":57.421294392094,"lon":23.90625}]}],"bounds":{"ne":{"lat":60.362317927612,"lon":29.843589207253},"sw":{"lat":54.480270856576,"lon":18.852584498495}}}'
		);
	}

	public function testParseCircles() {
		$this->assertMapDataEquals(
			\FormatJson::encode( $this->object->getMapData( [ 'circle=57.42129439209404,23.90625 : 326844.60518253763;', 'circles=40,40:400000', 'service=leaflet' ] ) ),
			'{"circles":[{"radius":[326844.60518254],"pos":[{"lat":57.421294392094,"lon":23.90625}]},{"radius":[400000],"pos":[{"lat":40,"lon":40}]}],"bounds":{"ne":{"lat":60.362317927612,"lon":44.961831660938},"sw":{"lat":36.400707261023,"lon":18.852584498495}}}'
		);
	}

	public function testParseFalseCircle() {
		$badradius = 'one km';
		$badcoord = '10°10°10", 10°10\'10"';

		$this->assertMapDataEquals(
			\FormatJson::encode( $this->object->getMapData( [ 'circle=57.42129439209404,23.90625 : 326844.60518253763;', "circles=40,40:$badradius", 'service=leaflet' ] ) ),
			'{"circles":[{"radius":[326844.60518254],"pos":[{"lat":57.421294392094,"lon":23.90625}]}],"bounds":{"ne":{"lat":60.362317927612,"lon":29.843589207253},"sw":{"lat":54.480270856576,"lon":18.852584498495}}}'
		);

		$circle = new Circle();
		$this->assertEquals(
			$this->object->getErrorMessages(),
			[
				\wfMessage( 'multimaps-unable-parse-radius', $badradius )->escaped(),
				\wfMessage( 'multimaps-unable-create-element', $circle->getElementName() )->escaped(),
			]
		);

		$this->assertMapDataEquals(
			\FormatJson::encode( $this->object->getMapData( [ 'circle=57.42129439209404,23.90625 : 326844.60518253763;', "circles=$badcoord:$badradius", 'service=leaflet' ] ) ),
			'{"circles":[{"radius":[326844.60518254],"pos":[{"lat":57.421294392094,"lon":23.90625}]}],"bounds":{"ne":{"lat":60.362317927612,"lon":29.843589207253},"sw":{"lat":54.480270856576,"lon":18.852584498495}}}'
		);
		$this->assertEquals(
			$this->object->getErrorMessages(),
			[
				\wfMessage( 'multimaps-unable-parse-coordinates', $badcoord )->escaped(),
				\wfMessage( 'multimaps-unable-create-element', $circle->getElementName() )->escaped(),
			]
		);

		$this->assertMapDataEquals(
			\FormatJson::encode( $this->object->getMapData( [ 'circle=57.42129439209404,23.90625 : 326844.60518253763;', "circles=40,40", 'service=leaflet' ] ) ),
			'{"circles":[{"radius":[326844.60518254],"pos":[{"lat":57.421294392094,"lon":23.90625}]}],"bounds":{"ne":{"lat":60.362317927612,"lon":29.843589207253},"sw":{"lat":54.480270856576,"lon":18.852584498495}}}'
		);
		$this->assertEquals(
			$this->object->getErrorMessages(),
			[
				\wfMessage( 'multimaps-circle-radius-not-defined' )->escaped(),
				\wfMessage( 'multimaps-unable-create-element', $circle->getElementName() )->escaped(),
			]
		);

		$this->assertMapDataEquals(
			\FormatJson::encode( $this->object->getMapData( [ 'circle=57.42129439209404,23.90625 : 326844.60518253763;', "circles=40,40:40000:8888", 'service=leaflet' ] ) ),
			'{"circles":[{"radius":[326844.60518254],"pos":[{"lat":57.421294392094,"lon":23.90625}]}],"bounds":{"ne":{"lat":60.362317927612,"lon":29.843589207253},"sw":{"lat":54.480270856576,"lon":18.852584498495}}}'
		);
		$this->assertEquals(
			$this->object->getErrorMessages(),
			[
				\wfMessage( 'multimaps-circle-wrong-number-parameters', 3 )->escaped(),
				\wfMessage( 'multimaps-unable-create-element', $circle->getElementName() )->escaped(),
				]
		);
	}

	public function testParseAllElements() {
		$this->assertMapDataEquals(
			\FormatJson::encode( $this->object->getMapData( [
				'52.429222277955134,13.359375; 53.38332836757156,3.33984375; 52.5897007687178,2.548828125;
52.855864177853995,3.515625; 53.33087298301704,1.7578125',
				'circle=57.42129439209404,23.90625:326844.60518253763',
				'PoLyGoNs=62.103882522897855,5.09765625:58.309488840677645,5.712890625:58.95000823335702,10.8984375
:61.68987220046001,12.83203125:63.35212928507874,11.865234375:64.1297836764257,13.974609375:65.58572002329473,14.326171875:
68.56038368664157,18.28125:68.52823492039876,20.126953125:69.2249968541159,20.56640625:69.2249968541159,21.708984375:
68.6245436634471,23.466796875:69.00567519658819,25.400390625:69.96043926902487,26.630859375:70.1403642720717,28.564453125:
69.25614923150721,28.828125:69.83962194067463,31.201171875:70.75796562654924,29.8828125:71.10254274232307,25.576171875:
70.72897946208789,22.67578125:69.99053495947653,18.10546875:67.84241647327927,13.095703125:68.33437594128185,15.908203125:
63.704722429433225,9.4921875:63.782486031165014,8.61328125',
				'LINES=53.225768435790194,23.466796875:53.9560855309879,29.1796875:56.46249048388979,31.11328125
:59.31076795603884,30.673828125:60.84491057364915,27.333984375:60.930432202923335,21.26953125:59.17592824927136,16.611328125:
56.218923189166624,16.259765625:54.1109429427243,19.599609375:53.225768435790194,23.466796875',
				'rectangles=51.83577752045248,33.837890625:46.37725420510028,23.37890625',
				'height=500px',
				'service=leaflet'
			] ) ),
			'{"markers":[{"pos":[{"lat":52.429222277955,"lon":13.359375}]},{"pos":[{"lat":53.383328367572,"lon":3.33984375}]},{"pos":[{"lat":52.589700768718,"lon":2.548828125}]},{"pos":[{"lat":52.855864177854,"lon":3.515625}]},{"pos":[{"lat":53.330872983017,"lon":1.7578125}]}],"lines":[{"pos":[{"lat":53.22576843579,"lon":23.466796875},{"lat":53.956085530988,"lon":29.1796875},{"lat":56.46249048389,"lon":31.11328125},{"lat":59.310767956039,"lon":30.673828125},{"lat":60.844910573649,"lon":27.333984375},{"lat":60.930432202923,"lon":21.26953125},{"lat":59.175928249271,"lon":16.611328125},{"lat":56.218923189167,"lon":16.259765625},{"lat":54.110942942724,"lon":19.599609375},{"lat":53.22576843579,"lon":23.466796875}]}],"polygons":[{"pos":[{"lat":62.103882522898,"lon":5.09765625},{"lat":58.309488840678,"lon":5.712890625},{"lat":58.950008233357,"lon":10.8984375},{"lat":61.68987220046,"lon":12.83203125},{"lat":63.352129285079,"lon":11.865234375},{"lat":64.129783676426,"lon":13.974609375},{"lat":65.585720023295,"lon":14.326171875},{"lat":68.560383686642,"lon":18.28125},{"lat":68.528234920399,"lon":20.126953125},{"lat":69.224996854116,"lon":20.56640625},{"lat":69.224996854116,"lon":21.708984375},{"lat":68.624543663447,"lon":23.466796875},{"lat":69.005675196588,"lon":25.400390625},{"lat":69.960439269025,"lon":26.630859375},{"lat":70.140364272072,"lon":28.564453125},{"lat":69.256149231507,"lon":28.828125},{"lat":69.839621940675,"lon":31.201171875},{"lat":70.757965626549,"lon":29.8828125},{"lat":71.102542742323,"lon":25.576171875},{"lat":70.728979462088,"lon":22.67578125},{"lat":69.990534959477,"lon":18.10546875},{"lat":67.842416473279,"lon":13.095703125},{"lat":68.334375941282,"lon":15.908203125},{"lat":63.704722429433,"lon":9.4921875},{"lat":63.782486031165,"lon":8.61328125}]}],"rectangles":[{"pos":[{"lat":51.835777520452,"lon":33.837890625},{"lat":46.3772542051,"lon":23.37890625}]}],"circles":[{"radius":[326844.60518254],"pos":[{"lat":57.421294392094,"lon":23.90625}]}],"bounds":{"ne":{"lat":71.102542742323,"lon":33.837890625},"sw":{"lat":46.3772542051,"lon":1.7578125}}}'
		);
		$this->assertEquals( $this->object->getErrorMessages(),	[] );
	}

	public function testParseAllElementsWithProperties() {
		$this->assertMapDataEquals(
			\FormatJson::encode( $this->object->getMapData( [
				'52.429222277955134,13.359375~Capital of Germany~Crazy people here!; 53.38332836757156,3.33984375; 52.5897007687178,2.548828125;
52.855864177853995,3.515625; 53.33087298301704,1.7578125',
				'circle=57.42129439209404,23.90625:326844.60518253763~I\'m a circle~of doom!',
				'PoLyGoNs=62.103882522897855,5.09765625:58.309488840677645,5.712890625:58.95000823335702,10.8984375
:61.68987220046001,12.83203125:63.35212928507874,11.865234375:64.1297836764257,13.974609375:65.58572002329473,14.326171875:
68.56038368664157,18.28125:68.52823492039876,20.126953125:69.2249968541159,20.56640625:69.2249968541159,21.708984375:
68.6245436634471,23.466796875:69.00567519658819,25.400390625:69.96043926902487,26.630859375:70.1403642720717,28.564453125:
69.25614923150721,28.828125:69.83962194067463,31.201171875:70.75796562654924,29.8828125:71.10254274232307,25.576171875:
70.72897946208789,22.67578125:69.99053495947653,18.10546875:67.84241647327927,13.095703125:68.33437594128185,15.908203125:
63.704722429433225,9.4921875:63.782486031165014,8.61328125~Meanwhile in Norway~ ~#0B4173~ ~ ~#3373CC',
				'LINES=53.225768435790194,23.466796875:53.9560855309879,29.1796875:56.46249048388979,31.11328125
:59.31076795603884,30.673828125:60.84491057364915,27.333984375:60.930432202923335,21.26953125:59.17592824927136,16.611328125:
56.218923189166624,16.259765625:54.1109429427243,19.599609375:53.225768435790194,23.466796875~You\'re serrounded!~ ~#B0920C',
				'rectangles=51.83577752045248,33.837890625:46.37725420510028,23.37890625~I\'m a square',
				'height=500px',
				'service=leaflet'
			] ) ),
			'{"markers":[{"pos":[{"lat":52.429222277955,"lon":13.359375}],"title":"Capital of Germany","text":"Crazy people here!"},{"pos":[{"lat":53.383328367572,"lon":3.33984375}]},{"pos":[{"lat":52.589700768718,"lon":2.548828125}]},{"pos":[{"lat":52.855864177854,"lon":3.515625}]},{"pos":[{"lat":53.330872983017,"lon":1.7578125}]}],"lines":[{"pos":[{"lat":53.22576843579,"lon":23.466796875},{"lat":53.956085530988,"lon":29.1796875},{"lat":56.46249048389,"lon":31.11328125},{"lat":59.310767956039,"lon":30.673828125},{"lat":60.844910573649,"lon":27.333984375},{"lat":60.930432202923,"lon":21.26953125},{"lat":59.175928249271,"lon":16.611328125},{"lat":56.218923189167,"lon":16.259765625},{"lat":54.110942942724,"lon":19.599609375},{"lat":53.22576843579,"lon":23.466796875}],"title":"You\'re serrounded!","color":"#B0920C"}],"polygons":[{"pos":[{"lat":62.103882522898,"lon":5.09765625},{"lat":58.309488840678,"lon":5.712890625},{"lat":58.950008233357,"lon":10.8984375},{"lat":61.68987220046,"lon":12.83203125},{"lat":63.352129285079,"lon":11.865234375},{"lat":64.129783676426,"lon":13.974609375},{"lat":65.585720023295,"lon":14.326171875},{"lat":68.560383686642,"lon":18.28125},{"lat":68.528234920399,"lon":20.126953125},{"lat":69.224996854116,"lon":20.56640625},{"lat":69.224996854116,"lon":21.708984375},{"lat":68.624543663447,"lon":23.466796875},{"lat":69.005675196588,"lon":25.400390625},{"lat":69.960439269025,"lon":26.630859375},{"lat":70.140364272072,"lon":28.564453125},{"lat":69.256149231507,"lon":28.828125},{"lat":69.839621940675,"lon":31.201171875},{"lat":70.757965626549,"lon":29.8828125},{"lat":71.102542742323,"lon":25.576171875},{"lat":70.728979462088,"lon":22.67578125},{"lat":69.990534959477,"lon":18.10546875},{"lat":67.842416473279,"lon":13.095703125},{"lat":68.334375941282,"lon":15.908203125},{"lat":63.704722429433,"lon":9.4921875},{"lat":63.782486031165,"lon":8.61328125}],"title":"Meanwhile in Norway","color":"#0B4173","fill":true,"fillcolor":"#3373CC"}],"rectangles":[{"pos":[{"lat":51.835777520452,"lon":33.837890625},{"lat":46.3772542051,"lon":23.37890625}],"title":"I\'m a square"}],"circles":[{"radius":[326844.60518254],"pos":[{"lat":57.421294392094,"lon":23.90625}],"title":"I\'m a circle","text":"of doom!"}],"bounds":{"ne":{"lat":71.102542742323,"lon":33.837890625},"sw":{"lat":46.3772542051,"lon":1.7578125}}}'
		);
		$this->assertEquals( $this->object->getErrorMessages(),	[] );
	}

	public function testMessageUnknownParameter() {
		$badparam = 'thisisabadparameter';
		$badvalue = 'thisisabadvalue';

		$this->assertMapDataEquals(
			\FormatJson::encode( $this->object->getMapData( [ '55.7557860, 37.6176330', "$badparam=$badvalue",'service=leaflet' ] ) ),
			'{"markers":[{"pos":[{"lat":55.755786,"lon":37.617633}]}],"zoom":14,"center":{"lat":55.755786,"lon":37.617633}}'
		);

		$this->assertEquals(
			$this->object->getErrorMessages(),
			[ \wfMessage( 'multimaps-unknown-parameter', $badparam )->escaped() ]
		);
	}

	public function testMessageElementMoreParameters() {
		$marker = new Marker();

		$this->assertMapDataEquals(
			\FormatJson::encode( $this->object->getMapData( [ '52.429222277955134,13.359375~Capital of Germany~Crazy people here!~ ~ ~Berlin;', 'service=leaflet' ] ) ),
			'{"markers":[{"pos":[{"lat":52.429222277955,"lon":13.359375}],"title":"Capital of Germany","text":"Crazy people here!"}],"zoom":14,"center":{"lat":52.429222277955,"lon":13.359375}}'
		);

		$this->assertEquals(
			$this->object->getErrorMessages(),
			[
				\wfMessage( 'multimaps-element-more-parameters', $marker->getElementName() )->escaped(),
				\wfMessage( 'multimaps-element-parameters-not-processed', '"' . implode( '", "', [ ' ', 'Berlin' ] ) . '"' )->escaped(),
			]
		);
	}

	public function testMessageMarkerIncorrectIcon() {
		$badicon = 'this is a bad icon';

		$this->assertMapDataEquals(
			\FormatJson::encode( $this->object->getMapData( [ "55.7557860, 37.6176330~icon=$badicon", 'service=leaflet' ] ) ),
			'{"markers":[{"pos":[{"lat":55.755786,"lon":37.617633}]}],"zoom":14,"center":{"lat":55.755786,"lon":37.617633}}'
		);

		$this->assertEquals(
			$this->object->getErrorMessages(),
			[ \wfMessage( 'multimaps-marker-incorrect-icon', $badicon )->escaped() ]
		);
	}

	public function testParseGeocoderMarker() {
		$this->markTestSkipped( 'There is no code that can resolve city names to coordinates' );

		$this->assertRegExp(
			'{"markers":\[{"pos":\[{"lat":[0-9\.]+,"lon":[0-9\.]+}\]}\],"bounds":{"ne":{"lat":[0-9\.]+,"lon":[0-9\.]+},"sw":{"lat":[0-9\.]+,"lon":[0-9\.]+}}}',
			\FormatJson::encode( $this->object->getMapData( [ 'Moscow', 'service=leaflet' ] ) )
		);
	}

	public function testParseGeocoderRectangle() {
		$this->markTestSkipped( 'There is no code that can resolve city names to coordinates' );

		$this->assertRegExp(
			'{"rectangles":\[{"pos":\[{"lat":[0-9\.]+,"lon":[0-9\.]+},{"lat":[0-9\.]+,"lon":[0-9\.]+}\]}\],"bounds":{"ne":{"lat":[0-9\.]+,"lon":[0-9\.]+},"sw":{"lat":[0-9\.]+,"lon":[0-9\.]+}}}',
			\FormatJson::encode( $this->object->getMapData( [ 'rectangle=Moscow', 'service=leaflet' ] ) )
		);
	}

	public function testParseGeocoderRectangles() {
		$this->markTestSkipped( 'There is no code that can resolve city names to coordinates' );

		$this->assertRegExp(
			'{"rectangles":\[{"pos":\[{"lat":[0-9\.]+,"lon":[0-9\.]+},{"lat":[0-9\.]+,"lon":[0-9\.]+}\]},{"pos":\[{"lat":[0-9\.]+,"lon":[0-9\.]+},{"lat":[-0-9\.]+,"lon":[-0-9\.]+}]}],"bounds":{"ne":{"lat":[0-9\.]+,"lon":[0-9\.]+},"sw":{"lat":[0-9\.]+,"lon":[-0-9\.]+}}}',
			\FormatJson::encode( $this->object->getMapData( [ 'rectangle=Moscow;London', 'service=leaflet' ] ) )
		);
	}

	public function testParseGeocoderCircle() {
		$this->markTestSkipped( 'There is no code that can resolve city names to coordinates' );

		$this->assertRegExp(
			'{"circles":\[{"radius":\[[0-9\.]+\],"pos":\[{"lat":[0-9\.]+,"lon":[0-9\.]+}\]}\],"bounds":{"ne":{"lat":[0-9\.]+,"lon":[0-9\.]+},"sw":{"lat":[0-9\.]+,"lon":[0-9\.]+}}}',
			\FormatJson::encode( $this->object->getMapData( [ 'circle=Moscow', 'service=leaflet' ] ) )
		);
	}

	public function testParseGeocoderObjectPolygon() {
		$this->markTestSkipped( 'There is no code that can resolve city names to coordinates' );

		$this->assertRegExp(
			'{"polygons":\[\{"pos":\[(\{"lat":[0-9\.]+,"lon":[0-9\.]+\},?)+\]\}\],"bounds":{"ne":{"lat":[0-9\.]+,"lon":[0-9\.]+},"sw":{"lat":[0-9\.]+,"lon":[0-9\.]+}}}',
			\FormatJson::encode( $this->object->getMapData( [ 'polygon=Astana', 'service=leaflet' ] ) )
		);
	}

	private function assertMapDataEquals( $actual, $expected ) {
		// Normalize floating point numbers to get rid of any IEEE rounding error
		$pattern = '/\b\d+\.\d+\b/';
		$callback = function ( $matches ) {
			// We are talking about coordinates here. 9 decimal places are a millimeter!
			return (string)round( $matches[0], 9 );
		};
		$this->assertSame(
			preg_replace_callback( $pattern, $callback, $expected ),
			preg_replace_callback( $pattern, $callback, $actual )
		);
	}

}

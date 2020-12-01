<?php
namespace PhpTags;

class PhpTagsWidgets_Slick_Test extends \MediaWikiTestCase {

	public function testRun_property_style() {
		$this->assertEquals(
				Runtime::runSource( '$s = new Slick( null, ["sTyLe"=>"myStyle"] ); echo $s->StYlE;' ),
				array( 'myStyle' )
			);
	}

	public function testRun_property_unset_1() {
		$this->assertEquals(
				Runtime::runSource( '$s = new Slick( null, ["style"=>"myStyle"] ); echo $s->style; $s->sTyle = null; echo $s->style;' ),
				array( 'myStyle', null )
			);
	}
	public function testRun_property_unset_2() {
		$this->assertEquals(
				Runtime::runSource( '$s = new Slick( null, ["dotS"=>"it must be boolean true"] ); echo $s->dOts; $s->Dots = null; echo $s->doTs;' ),
				array( true, null )
			);
	}

	public function testRun_property_rtl() {
		$this->assertEquals(
				Runtime::runSource( '$s = new Slick( null, ["rTl"=>"it must be boolean true"] ); echo $s->RtL, $s->diR;' ),
				array( true, 'rtl' )
			);
	}

	public function testRun_property_asNavFor() {
		$this->assertEquals(
				Runtime::runSource( '
$one = new Slick(); echo $one->asNavFor;
$two = new Slick(); echo $two->asNavFor;
$one->asNavFor = $two;
echo $one->asNavFor === $two ? "true1" : "false1", $two->asNavFor === $one ? "true2" : "false2";
$two->asNavFor = null;
echo $one->asNavFor === null ? "true1" : "false1", $two->asNavFor === null ? "true2" : "false2";' ),
				array( null, null, 'true1', 'true2', 'true1', 'true2' )
			);
	}

}

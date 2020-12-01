<?php

namespace PasswordlessLogin;

use MediaWikiTestCase;
use OutputPage;
use RequestContext;
use Title;

class HooksTest extends MediaWikiTestCase {
	/**
	 * @covers \PasswordlessLogin\Hooks::onBeforePageDisplay
	 */
	public function testNotAddingLinksOnNonLoginPage() {
		$out = new OutputPage( RequestContext::getMain() );
		$skin = RequestContext::getMain()->getSkin();
		$out->setTitle( Title::makeTitle( NS_SPECIAL, 'NoLoginPage' ) );

		Hooks::onBeforePageDisplay( $out, $skin );

		$this->assertFalse( isset( $out->getJsConfigVars()['PLEnableApiVerification'] ) );
		$this->assertEquals( [], $out->getModules() );
	}

	/**
	 * @covers \PasswordlessLogin\Hooks::onBeforePageDisplay
	 */
	public function testNotAddingLoginOnNonLoginPage() {
		$out = new OutputPage( RequestContext::getMain() );
		$skin = RequestContext::getMain()->getSkin();
		$out->setTitle( Title::makeTitle( NS_SPECIAL, 'NoLoginPage' ) );

		Hooks::onBeforePageDisplay( $out, $skin );

		$this->assertFalse( isset( $out->getJsConfigVars()['PLEnableApiVerification'] ) );
		$this->assertEquals( [], $out->getModules() );
		$this->assertEquals( [], $out->getModuleStyles() );
	}

	/**
	 * @covers \PasswordlessLogin\Hooks::onBeforePageDisplay
	 */
	public function testNotAddingLoginIfNotRequested() {
		$out = new OutputPage( RequestContext::getMain() );
		$skin = RequestContext::getMain()->getSkin();
		$out->setTitle( Title::makeTitle( NS_SPECIAL, 'UserLogin' ) );

		Hooks::$addFrontendModules = false;
		Hooks::onBeforePageDisplay( $out, $skin );

		$this->assertFalse( isset( $out->getJsConfigVars()['PLEnableApiVerification'] ) );
		$this->assertEquals( [], $out->getModules() );
		$this->assertEquals( [], $out->getModuleStyles() );
	}

	/**
	 * @covers \PasswordlessLogin\Hooks::onBeforePageDisplay
	 */
	public function testAddsLoginModule() {
		$this->setMwGlobals( [
			'wgPLEnableApiVerification' => true,
		] );
		$out = new OutputPage( RequestContext::getMain() );
		$skin = RequestContext::getMain()->getSkin();
		$out->setTitle( Title::makeTitle( NS_SPECIAL, 'UserLogin' ) );

		Hooks::$addFrontendModules = true;
		Hooks::onBeforePageDisplay( $out, $skin );

		$this->assertTrue( $out->getJsConfigVars()['PLEnableApiVerification'] );
		$this->assertEquals( [ 'ext.PasswordlessLogin.login' ], $out->getModules() );
		$this->assertEquals( [ 'ext.PasswordlessLogin.login.styles' ], $out->getModuleStyles() );
	}

	/**
	 * @covers \PasswordlessLogin\Hooks::onBeforePageDisplay
	 */
	public function testAddsLoginModuleForAnotherLanguage() {
		$this->setMwGlobals( [
			'wgPLEnableApiVerification' => true,
			'wgLanguageCode' => 'de'
		] );
		$out = new OutputPage( RequestContext::getMain() );
		$skin = RequestContext::getMain()->getSkin();
		$out->setTitle( Title::makeTitle( NS_SPECIAL, 'Anmelden' ) );

		Hooks::$addFrontendModules = true;
		Hooks::onBeforePageDisplay( $out, $skin );

		$this->assertTrue( $out->getJsConfigVars()['PLEnableApiVerification'] );
		$this->assertEquals( [ 'ext.PasswordlessLogin.login' ], $out->getModules() );
		$this->assertEquals( [ 'ext.PasswordlessLogin.login.styles' ], $out->getModuleStyles() );
	}
}

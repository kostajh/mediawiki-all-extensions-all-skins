<?php

namespace MediaWiki\Extensions\EmailAuth;

use HashBagOStuff;
use MediaWiki\Auth\AuthenticationRequest;
use MediaWiki\Auth\AuthenticationResponse;
use MediaWiki\Auth\AuthManager;
use MediaWikiTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;
use User;
use Wikimedia\TestingAccessWrapper;

/**
 * @covers \MediaWiki\Extensions\EmailAuth\EmailAuthSecondaryAuthenticationProvider
 */
class EmailAuthSecondaryAuthenticationProviderTest extends MediaWikiTestCase {
	/** @var EmailAuthSecondaryAuthenticationProvider|MockObject */
	protected $provider;
	/** @var AuthManager|MockObject */
	protected $manager;
	/** @var HashBagOStuff */
	protected $session;
	/** @var LoggerInterface|MockObject */
	protected $logger;

	protected function setUp() : void {
		parent::setUp();

		$this->setTemporaryHook( 'EmailAuthRequireToken', function () {
		} );

		$this->provider = new EmailAuthSecondaryAuthenticationProvider();

		$this->logger = $this->getMockBuilder( LoggerInterface::class )->getMockForAbstractClass();
		$this->provider->setLogger( $this->logger );

		$this->manager = $this->getMockBuilder( AuthManager::class )->disableOriginalConstructor()
			->getMock();
		$this->session = new HashBagOStuff();
		$this->manager->expects( $this->any() )->method( 'setAuthenticationSessionData' )
			->willReturnCallback( function ( $key, $value ) {
				$this->session->set( $key, $value );
			} );
		$this->manager->expects( $this->any() )->method( 'getAuthenticationSessionData' )
			->willReturnCallback( function ( $key ) {
				return $this->session->get( $key );
			} );
		$this->provider->setManager( $this->manager );
	}

	public function testGetAuthenticationRequests() {
		$reqs = $this->provider->getAuthenticationRequests( AuthManager::ACTION_LOGIN,
			[ 'username' => 'Foo' ] );
		$this->assertSame( [], $reqs );
	}

	public function testSecondaryAuthentication() {
		// no action by default
		$user = $this->getMockUser( true );
		$response = $this->provider->beginSecondaryAuthentication( $user, [] );
		$this->assertSame( AuthenticationResponse::PASS, $response->status );

		// require token  when instructed by hook
		$this->setTemporaryHook( 'EmailAuthRequireToken', function ( $user, &$verificationRequired ) {
			$verificationRequired = true;
		} );
		$this->session->clear();
		$user = $this->getMockUser( true );
		$user->expects( $this->once() )->method( 'sendMail' )
			->willReturnCallback( function ( $s, $b ) use ( &$body ) {
				$body = $b;
			} );
		$response = $this->provider->beginSecondaryAuthentication( $user, [] );
		$this->assertSame( AuthenticationResponse::UI, $response->status );
		$response = $this->provider->continueSecondaryAuthentication( $user,
			AuthenticationRequest::loadRequestsFromSubmission( $response->neededRequests,
				[ 'token' => '123abc' ] ) );
		$this->assertSame( AuthenticationResponse::UI, $response->status );
		$this->assertSame( 'error', $response->messageType );
		$response = $this->provider->continueSecondaryAuthentication( $user,
			AuthenticationRequest::loadRequestsFromSubmission( $response->neededRequests,
				[ 'token' => $body->getParams()[1] ] ) );
		$this->assertSame( AuthenticationResponse::PASS, $response->status );

		// abort after 4 failed attempts
		$this->session->clear();
		$user = $this->getMockUser( true );
		$response = $this->provider->beginSecondaryAuthentication( $user, [] );
		$this->assertSame( AuthenticationResponse::UI, $response->status );
		$this->assertSame( 'warning', $response->messageType );
		foreach ( range( 1, 3 ) as $i ) {
			$response = $this->provider->continueSecondaryAuthentication( $user,
				AuthenticationRequest::loadRequestsFromSubmission( $response->neededRequests,
					[ 'token' => 'false' ] ) );
			$this->assertSame( AuthenticationResponse::UI, $response->status );
			$this->assertSame( 'error', $response->messageType );
		}
		$response = $this->provider->continueSecondaryAuthentication( $user,
			AuthenticationRequest::loadRequestsFromSubmission( $response->neededRequests,
				[ 'token' => 'false' ] ) );
		$this->assertSame( AuthenticationResponse::FAIL, $response->status );

		// ignore users with no email address
		$this->session->clear();
		$user = $this->getMockUser( false );
		$response = $this->provider->beginSecondaryAuthentication( $user, [] );
		$this->assertSame( AuthenticationResponse::PASS, $response->status );

		// messages can be changed
		$this->setTemporaryHook( 'EmailAuthRequireToken', function ( $user, &$verificationRequired,
			&$formMessage, &$subjectMessage, &$bodyMessage
		) {
			$verificationRequired = true;
			$formMessage = wfMessage( 'form' );
			$subjectMessage = wfMessage( 'subject' );
			$bodyMessage = wfMessage( 'body' );
		} );
		$this->session->clear();
		$user = $this->getMockUser( true );
		$user->expects( $this->once() )->method( 'sendMail' )
			->willReturnCallback( function ( $s, $b ) use ( &$subject2, &$body2 ) {
				$subject2 = $s;
				$body2 = $b;
			} );
		$response = $this->provider->beginSecondaryAuthentication( $user, [] );
		$this->assertSame( AuthenticationResponse::UI, $response->status );
		$this->assertSame( 'form', $response->message->getKey() );
		$this->assertSame( 'subject', $subject2->getKey() );
		$this->assertSame( 'body', $body2->getKey() );
		$this->assertCount( 1, $body2->getParams() );
	}

	public function testBeginSecondaryAccountCreation() {
		$response = $this->provider->beginSecondaryAccountCreation( User::newFromName( 'Foo' ),
			User::newFromName( 'Bar' ), [] );
		$this->assertThat( $response->status, $this->logicalOr( $this->identicalTo(
			AuthenticationResponse::PASS ), $this->identicalTo( AuthenticationResponse::ABSTAIN ) ) );
	}

	protected function getMockUser( $isEmailConfirmed ) {
		$user = $this->getMockBuilder( User::class )
			->setMethods( [ 'isEmailConfirmed', 'sendMail' ] )->getMock();
		$user->expects( $this->any() )->method( 'isEmailConfirmed' )->willReturn( $isEmailConfirmed );
		/** @var User $user */
		$user->mName = 'Foo';
		$user->mFrom = 'name';
		TestingAccessWrapper::newFromObject( $user )->setItemLoaded( 'name' );
		return $user;
	}
}

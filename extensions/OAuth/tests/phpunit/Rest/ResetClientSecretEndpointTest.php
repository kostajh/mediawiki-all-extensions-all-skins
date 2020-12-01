<?php

namespace MediaWiki\Extensions\OAuth\Tests\Rest;

use MediaWiki\Extensions\OAuth\Backend\Consumer;
use MediaWiki\Extensions\OAuth\Backend\Utils;
use MWRestrictions;
use User;
use WikiMap;

/**
 * @covers \MediaWiki\Extensions\OAuth\Rest\Handler\ResetClientSecret
 * @group Database
 * @group OAuth
 */
class ResetClientSecretEndpointTest extends EndpointTest {

	/**
	 * @var array
	 */
	protected $consumerData = [
		'id' => null,
		'consumerKey' => null,
		'name' => 'rc_test_name',
		'userId' => null,
		'version' => '1',
		'callbackUrl' => 'https://test.com',
		'callbackIsPrefix' => null,
		'description' => 'test_description',
		'email' => 'test@test.com',
		'emailAuthenticated' => 1577836800,
		'oauthVersion' => 1,
		'developerAgreement' => 1,
		'ownerOnly' => false,
		'wiki' => '*',
		'grants' => '["test"]',
		'registration' => 1577836800,
		'secretKey' => 'sk111111111111111111111111111111',
		'rsaKey' => '',
		'restrictions' => '{"IPAddresses": ["127.0.0.1"]}',
		'stage' => 1,
		'stageTimestamp' => 1577836800,
		'deleted' => 0,
		'oauth2IsConfidential' => 1,
		'oauth2GrantTypes' => null,
	];

	/**
	 * @throws \Exception
	 */
	protected function setUp() : void {
		parent::setUp();

		$this->setMwGlobals( [
			'wgMWOAuthCentralWiki' => WikiMap::getCurrentWikiId(),
			'wgGroupPermissions' => [
				'*' => [ 'mwoauthupdateownconsumer' => true ]
			],
		] );
		$this->tablesUsed[] = 'oauth_registered_consumer';
	}

	/**
	 * @return array
	 */
	public function provideTestViaRouter() {
		return [
			'Unsupported Media Type' => [
				[
					'method' => 'POST',
					'uri' => self::makeUri( '/oauth2/client/INVALID_CLIENT_KEY/reset_secret' ),
					'postParams' => []
				],
				[
					'statusCode' => 415,
					'reasonPhrase' => 'Unsupported Media Type',
					'protocolVersion' => '1.1'
				]
			],
			'Unsupported Method' => [
				[
					'method' => 'GET',
					'uri' => self::makeUri( '/oauth2/client/INVALID_CLIENT_KEY/reset_secret' ),
					'postParams' => []
				],
				[
					'statusCode' => 405,
					'reasonPhrase' => 'Method Not Allowed',
					'protocolVersion' => '1.1'
				]
			],
			'Missing Content-Type header' => [
				[
					'method' => 'POST',
					'uri' => self::makeUri( '/oauth2/client/11111111111111111111111111111111/reset_secret' ),
					'postParams' => [
						'client_key' => '11111111111111111111111111111111',
						'reason' => 'Test reason'
					],
					'headers' => [],
				],
				[
					'statusCode' => 415,
					'reasonPhrase' => 'Unsupported Media Type',
					'protocolVersion' => '1.1'
				]
			],
			'Invalid client key' => [
				[
					'method' => 'POST',
					'uri' => self::makeUri( '/oauth2/client/22222222222222222222222222222222/reset_secret' ),
					'postParams' => [
						'client_key' => '11111111111111111111111111111111',
						'reason' => 'Test reason'
					],
					'headers' => [
						'Content-Type' => 'application/json'
					],
				],
				[
					'statusCode' => 400,
					'reasonPhrase' => 'Bad Request',
					'protocolVersion' => '1.1'
				],
			],
			'Deleted Consumer Request' => [
				[
					'method' => 'POST',
					'uri' => self::makeUri( '/oauth2/client/11111111111111111111111111111111/reset_secret' ),
					'postParams' => [
						'client_key' => '11111111111111111111111111111111',
						'reason' => 'Test reason'
					],
					'headers' => [
						'Content-Type' => 'application/json'
					],
				],
				[
					'statusCode' => 401,
					'reasonPhrase' => 'Unauthorized',
					'protocolVersion' => '1.1'
				],
				function () {
					$user = User::createNew( 'ResetClientSecretTestUser1' );
					$centralId = Utils::getCentralIdFromUserName( $user->getName() );
					$db = Utils::getCentralDB( DB_MASTER );

					$this->consumerData['userId'] = $centralId;
					$this->consumerData['consumerKey'] = '11111111111111111111111111111111';
					$this->consumerData['deleted'] = true;

					if ( isset( $this->consumerData['restrictions'] ) ) {
						$this->consumerData['restrictions'] =
							MWRestrictions::newFromJson( $this->consumerData['restrictions'] );
					}

					Consumer::newFromArray( $this->consumerData )->save( $db );

					return $user;
				}
			],
			'User Mismatch Request' => [
				[
					'method' => 'POST',
					'uri' => self::makeUri( '/oauth2/client/22222222222222222222222222222222/reset_secret' ),
					'postParams' => [
						'client_key' => '22222222222222222222222222222222',
						'reason' => 'Test reason'
					],
					'headers' => [
						'Content-Type' => 'application/json'
					],
				],
				[
					'statusCode' => 400,
					'reasonPhrase' => 'Bad Request',
					'protocolVersion' => '1.1'
				],
				function () {
					$user = User::createNew( 'ResetClientSecretTestUser2' );
					$db = Utils::getCentralDB( DB_MASTER );

					$this->consumerData['userId'] = 999;
					$this->consumerData['consumerKey'] = '22222222222222222222222222222222';
					$this->consumerData['deleted'] = false;
					$this->consumerData['name'] = 'test_name_user_mismatch';

					if ( isset( $this->consumerData['restrictions'] ) ) {
						$this->consumerData['restrictions'] =
							MWRestrictions::newFromJson( $this->consumerData['restrictions'] );
					}

					Consumer::newFromArray( $this->consumerData )->save( $db );

					return $user;
				}
			],
			'Successful Request OAuth 1' => [
				[
					'method' => 'POST',
					'uri' => self::makeUri( '/oauth2/client/33333333333333333333333333333333/reset_secret' ),
					'postParams' => [
						'client_key' => '33333333333333333333333333333333',
						'reason' => 'Test reason'
					],
					'headers' => [
						'Content-Type' => 'application/json'
					],
				],
				[
					'statusCode' => 200,
					'reasonPhrase' => 'OK',
					'protocolVersion' => '1.1'
				],
				function () {
					$user = User::createNew( 'ResetClientSecretTestUser3' );
					$centralId = Utils::getCentralIdFromUserName( $user->getName() );
					$db = Utils::getCentralDB( DB_MASTER );

					$this->consumerData['userId'] = $centralId;
					$this->consumerData['consumerKey'] = '33333333333333333333333333333333';
					$this->consumerData['name'] = 'test_name_user_successful';

					if ( isset( $this->consumerData['restrictions'] ) ) {
						$this->consumerData['restrictions'] =
							MWRestrictions::newFromJson( $this->consumerData['restrictions'] );
					}

					Consumer::newFromArray( $this->consumerData )->save( $db );

					return $user;
				}
			],
			'Successful Request OAuth 2' => [
				[
					'method' => 'POST',
					'uri' => self::makeUri( '/oauth2/client/44444444444444444444444444444444/reset_secret' ),
					'postParams' => [
						'client_key' => '44444444444444444444444444444444',
						'reason' => 'Test reason'
					],
					'headers' => [
						'Content-Type' => 'application/json'
					],
				],
				[
					'statusCode' => 200,
					'reasonPhrase' => 'OK',
					'protocolVersion' => '1.1'
				],
				function () {
					$user = User::createNew( 'ResetClientSecretTestUser4' );
					$centralId = Utils::getCentralIdFromUserName( $user->getName() );
					$db = Utils::getCentralDB( DB_MASTER );

					$this->consumerData['userId'] = $centralId;
					$this->consumerData['consumerKey'] = '44444444444444444444444444444444';
					$this->consumerData['name'] = 'test_name_user_successful';
					$this->consumerData['oauthVersion'] = '2';

					if ( isset( $this->consumerData['restrictions'] ) ) {
						$this->consumerData['restrictions'] =
							MWRestrictions::newFromJson( $this->consumerData['restrictions'] );
					}

					Consumer::newFromArray( $this->consumerData )->save( $db );

					return $user;
				}
			],
		];
	}
}

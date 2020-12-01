<?php

abstract class ApiPushBase extends ApiBase {
	/**
	 * Associative array containing CookieJar objects (values) to be passed in
	 * order to authenticate to the targets (keys).
	 *
	 * @since 0.4
	 *
	 * @var array
	 */
	protected $cookieJars = [];

	/** @inheritDoc */
	public function needsToken() {
		return 'csrf';
	}

	/**
	 * Logs in into a target wiki using the provided username and password.
	 *
	 * @since 0.4
	 *
	 * @param string $user
	 * @param string $password
	 * @param string $domain
	 * @param string $target
	 * @param string|null $token
	 * @param null $cookieJar
	 * @param int $attemtNr
	 * @throws ApiUsageException
	 */
	protected function doLogin(
		$user, $password, $domain, $target, $token = null, $cookieJar = null, $attemtNr = 0
	) {
		$requestData = [
			'action' => 'login',
			'format' => 'json',
			'lgname' => $user,
			'lgpassword' => $password,
		];
		if ( $domain != false ) {
			$requestData['lgdomain'] = $domain;
		}

		if ( $token !== null ) {
			$requestData['lgtoken'] = $token;
		}

		$req = MWHttpRequest::factory( $target,
			[
				'postData' => $requestData,
				'method' => 'POST',
				'timeout' => 'default'
			],
			__METHOD__
		);

		if ( $cookieJar !== null ) {
			$req->setCookieJar( $cookieJar );
		}

		$status = $req->execute();

		$attemtNr++;

		if ( !$status->isOK() ) {
			$this->dieWithError(
				wfMessage( 'push-err-authentication', $target, '' )->parse(),
				'authentication-failed'
			);
		}

		$response = FormatJson::decode( $req->getContent() );

		if ( !property_exists( $response, 'login' ) || !property_exists( $response->login, 'result' ) ) {
			$this->dieWithError(
				wfMessage( 'push-err-authentication', $target, '' )->parse(),
				'authentication-failed'
			);
		}

		if ( $response->login->result == 'NeedToken' && $attemtNr < 3 ) {
			$loginToken = $response->login->token ?? $this->getToken( $target, 'login' );
			$this->doLogin(
				$user,
				$password,
				$domain,
				$target,
				$loginToken,
				$req->getCookieJar(),
				$attemtNr
			);
		} elseif ( $response->login->result == 'Success' ) {
			$this->cookieJars[$target] = $req->getCookieJar();
		} else {
			$this->dieWithError(
				wfMessage( 'push-err-authentication', $target, '' )->parse(),
				'authentication-failed'
			);
		}
	}

	/**
	 * Obtains the needed token by making an HTTP GET request
	 * to the remote wikis API.
	 *
	 * @param string $target
	 * @param string $type
	 * @return string|false
	 * @throws ApiUsageException
	 * @since 0.3
	 *
	 */
	protected function getToken( string $target, string $type ) {
		$requestData = [
			'action' => 'query',
			'format' => 'json',
			'meta' => 'tokens',
			'type' => $type,
		];

		$req = MWHttpRequest::factory( wfAppendQuery( $target, $requestData ),
			[
				'method' => 'GET',
				'timeout' => 'default'
			],
			__METHOD__
		);

		if ( array_key_exists( $target, $this->cookieJars ) ) {
			$req->setCookieJar( $this->cookieJars[$target] );
		}

		$status = $req->execute();

		$response = $status->isOK() ? FormatJson::decode( $req->getContent() ) : null;

		$token = false;
		$tokenKey = $type . 'token';
		if (
			$response === null
			|| !property_exists( $response, 'query' )
			|| !property_exists( $response->query, 'tokens' )
			|| !property_exists( $response->query->tokens, $tokenKey )
		) {
			$this->dieWithError(
				wfMessage( 'push-special-err-token-failed' )->text(),
				'token-request-failed'
			);
		}

		if ( property_exists( $response->query->tokens, $tokenKey ) ) {
			$token = $response->query->tokens->{$tokenKey};
		} elseif (
			$response !== null
			&& property_exists( $response, 'query' )
			&& property_exists( $response->query, 'error' )
		) {
			$this->dieWithError( $response->query->error->message, 'token-request-failed' );
		} else {
			$this->dieWithError(
				wfMessage( 'push-special-err-token-failed' )->text(),
				'token-request-failed'
			);
		}

		return $token;
	}

	/**
	 * @throws ApiUsageException
	 */
	public function execute() {
		$pushConfig = new GlobalVarConfig( 'egPush' );
		$pushLoginUser = $pushConfig->get( 'LoginUser' );
		$pushLoginPass = $pushConfig->get( 'LoginPass' );
		$pushLoginUsers = $pushConfig->get( 'LoginUsers' );
		$pushLoginPasswords = $pushConfig->get( 'LoginPasswords' );
		$pushLoginDomain = $pushConfig->get( 'LoginDomain' );
		$pushLoginDomains = $pushConfig->get( 'LoginDomains' );
		$pushTargets = $pushConfig->get( 'Targets' );

		$this->checkUserRightsAny( 'push' );
		$block = $this->getUser()->getBlock();
		if ( $block ) {
			$this->dieBlocked( $block );
		}

		$params = $this->extractRequestParams();

		PushFunctions::flipKeys( $pushLoginUsers, 'users' );
		PushFunctions::flipKeys( $pushLoginPasswords, 'passwds' );
		PushFunctions::flipKeys( $pushLoginDomains, 'domains' );

		$targetsForProcessing = [];
		foreach ( $params['targets'] as &$target ) {
			if ( !in_array( $target, $pushTargets ) ) {
				// We have to process defined targets only for security reasons
				continue;
			}
			$user = false;
			$pass = false;
			$domain = false;

			if (
				array_key_exists( $target, $pushLoginUsers )
				&& array_key_exists( $target, $pushLoginPasswords )
			) {
				$user = $pushLoginUsers[$target];
				$pass = $pushLoginPasswords[$target];
			} elseif ( $pushLoginUser !== '' && $pushLoginPass !== '' ) {
				$user = $pushLoginUser;
				$pass = $pushLoginPass;
			}
			if ( array_key_exists( $target, $pushLoginDomains ) ) {
				$domain = $pushLoginDomains[$target];
			} elseif ( $pushLoginDomain !== '' ) {
				$domain = $pushLoginDomain;
			}

			if ( substr( $target, -1 ) !== '/' ) {
				$target .= '/';
			}

			$target .= 'api.php';

			if ( $user !== false ) {
				$this->doLogin( $user, $pass, $domain, $target );
			}

			$targetsForProcessing[] = $target;
		}

		$this->doModuleExecute( $targetsForProcessing );
	}

	/**
	 * @param array $targetsForProcessing We have to process defined targets only for security reasons
	 */
	abstract protected function doModuleExecute( array $targetsForProcessing );
}

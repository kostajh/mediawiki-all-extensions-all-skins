<?php

namespace BlueSpice\WhoIsOnline;

use BlueSpice\Data\FieldType;
use BlueSpice\Data\Filter;
use BlueSpice\Data\Filter\Date;
use BlueSpice\Data\ReaderParams;
use BlueSpice\Data\RecordSet;
use BlueSpice\Timestamp;
use BlueSpice\UtilityFactory;
use BlueSpice\WhoIsOnline\Data\Record;
use BlueSpice\WhoIsOnline\Data\Tracer\Store;
use Config;
use IContextSource;
use ReadOnlyMode;
use Wikimedia\Rdbms\LoadBalancer;

class Tracer {
	const SESSION_LOG_TS = 'BlueSpiceWhoIsOnline::lastLoggedTime';
	const SESSION_LOG_HASH = 'BlueSpiceWhoIsOnline::lastLoggedPageHash';

	/**
	 *
	 * @var RecordSet[]
	 */
	protected $trancedRecords = [];

	/**
	 *
	 * @var LoadBalancer
	 */
	protected $lb = null;

	/**
	 *
	 * @var Config
	 */
	protected $config = null;

	/**
	 *
	 * @var ReadOnlyMode
	 */
	protected $readOnly = null;

	/**
	 *
	 * @var UtilityFactory
	 */
	protected $util = null;

	/**
	 *
	 * @var string
	 */
	protected $hash = '';

	/**
	 *
	 * @param LoadBalancer $lb
	 * @param Config $config
	 * @param ReadOnlyMode $readOnly
	 * @param UtilityFactory $util
	 */
	public function __construct( LoadBalancer $lb, Config $config,
		ReadOnlyMode $readOnly, UtilityFactory $util ) {
		$this->lb = $lb;
		$this->config = $config;
		$this->readOnly = $readOnly;
		$this->util = $util;
	}

	/**
	 *
	 * @param int|false $maxIdleSeconds
	 * @return RecordSet
	 */
	public function getTracedRecords( $maxIdleSeconds = false ) {
		if ( !$maxIdleSeconds ) {
			$idleSeconds = $this->config->get( 'WhoIsOnlineMaxIdleTime' );
		}
		if ( isset( $this->trancedRecords[$maxIdleSeconds] ) ) {
			return $this->trancedRecords[$maxIdleSeconds];
		}
		$maxTS = Timestamp::getInstance();
		$maxTS->timestamp->modify( "- $idleSeconds seconds" );
		$readerParams = new ReaderParams(
			[
				ReaderParams::PARAM_FILTER => [ [
					Filter::KEY_COMPARISON => Date::COMPARISON_LOWER_THAN,
					Filter::KEY_PROPERTY => Record::TIMESTAMP,
					Filter::KEY_VALUE => $maxTS->getTimestamp( TS_MW ),
					Filter::KEY_TYPE => FieldType::DATE
				]
			] ], [
				ReaderParams::PARAM_LIMIT => ReaderParams::LIMIT_INFINITE
			]
		);
		$this->trancedRecords[$maxIdleSeconds] = ( new Store() )->getReader()->read(
			$readerParams
		);
		return $this->trancedRecords[$maxIdleSeconds];
	}

	/**
	 *
	 * @param IContextSource $context
	 * @return bool
	 */
	public function trace( IContextSource $context ) {
		if ( !$this->shouldLog( $context ) ) {
			return false;
		}
		$ts = Timestamp::getInstance();

		$recordSet = new RecordSet( [ new Record( (object)[
			Record::USER_ID => $context->getUser()->getId(),
			Record::USER_NAME => $context->getUser()->getName(),
			Record::USER_REAL_NAME => $this->util
				->getUserHelper( $context->getUser() )->getDisplayName(),
			Record::PAGE_ID => $context->getTitle()->getArticleID(),
			Record::PAGE_NAMESPACE => $context->getTitle()->getNamespace(),
			Record::PAGE_TITLE => $context->getTitle()->getText(),
			Record::TIMESTAMP => $ts->getTimestamp( TS_MW ),
			Record::ACTION => $context->getRequest()->getVal( 'action', 'view' ),
		] ) ] );

		( new Store() )->getWriter()->write( $recordSet );

		$context->getRequest()->setSessionData(
			static::SESSION_LOG_HASH,
			$this->hash
		);
		$context->getRequest()->setSessionData(
			static::SESSION_LOG_TS,
			$ts->getTimestamp( TS_MW )
		);

		return true;
	}

	/**
	 *
	 * @param IContextSource $context
	 * @return string
	 */
	protected function makeHash( IContextSource $context ) {
		return md5( implode( '-', [
			$context->getTitle()->getArticleID(),
			$context->getTitle()->getNamespace(),
			$context->getTitle()->getText()
		] ) );
	}

	/**
	 *
	 * @param IContextSource $context
	 * @return bool
	 */
	protected function shouldLog( IContextSource $context ) {
		if ( !empty( $this->hash ) || $this->readOnly->isReadOnly() ) {
			return false;
		}
		if ( defined( 'MW_NO_SESSION' ) ) {
			return false;
		}
		if ( $context instanceof \ResourceLoaderContext ) {
			return false;
		}
		if ( $context->getRequest()->getVal( 'action', 'view' ) === 'raw' ) {
			return false;
		}
		if ( !$context->getUser() || $context->getUser()->isAnon() ) {
			return false;
		}
		if ( !$context->getTitle() ) {
			return false;
		}
		$this->hash = $this->makeHash( $context );

		$lastLoggedPageHash = $context->getRequest()->getSessionData(
			static::SESSION_LOG_HASH
		);

		if ( $lastLoggedPageHash != $this->hash ) {
			return true;
		}

		$lastLoggedTime = $context->getRequest()->getSessionData(
			static::SESSION_LOG_TS
		);

		$idleSeconds = $this->config->get( 'WhoIsOnlineMaxIdleTime' )
			+ $this->config->get( 'WhoIsOnlineInterval' )
			+ ( $this->config->get( 'WhoIsOnlineMaxIdleTime' ) * 0.1 );

		$nextLogTs = Timestamp::getInstance( $lastLoggedTime )->timestamp;
		$nextLogTs->modify( "+$idleSeconds seconds" );
		$tsNow = Timestamp::getInstance()->timestamp;
		if ( $nextLogTs > $tsNow ) {
			return false;
		}
		return true;
	}
}

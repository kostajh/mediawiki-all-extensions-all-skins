<?php

namespace MediaWiki\Extension\AbuseFilter;

use BagOStuff;
use MediaWiki\Block\BlockUserFactory;
use MediaWiki\Config\ServiceOptions;
use MediaWiki\Extension\AbuseFilter\ChangeTags\ChangeTagger;
use MediaWiki\Extension\AbuseFilter\Consequence\Block;
use MediaWiki\Extension\AbuseFilter\Consequence\BlockAutopromote;
use MediaWiki\Extension\AbuseFilter\Consequence\Degroup;
use MediaWiki\Extension\AbuseFilter\Consequence\Disallow;
use MediaWiki\Extension\AbuseFilter\Consequence\Parameters;
use MediaWiki\Extension\AbuseFilter\Consequence\RangeBlock;
use MediaWiki\Extension\AbuseFilter\Consequence\Tag;
use MediaWiki\Extension\AbuseFilter\Consequence\Throttle;
use MediaWiki\Extension\AbuseFilter\Consequence\Warn;
use MediaWiki\Session\Session;
use MediaWiki\User\UserGroupManager;
use Psr\Log\LoggerInterface;

class ConsequencesFactory {
	public const SERVICE_NAME = 'AbuseFilterConsequencesFactory';

	public const CONSTRUCTOR_OPTIONS = [
		'AbuseFilterCentralDB',
		'AbuseFilterIsCentral',
		'AbuseFilterRangeBlockSize',
		'BlockCIDRLimit',
	];

	/** @var ServiceOptions */
	private $options;

	/** @var LoggerInterface */
	private $logger;

	/** @var BlockUserFactory */
	private $blockUserFactory;

	/** @var UserGroupManager */
	private $userGroupManager;

	/** @var BagOStuff */
	private $mainStash;

	/** @var ChangeTagger */
	private $changeTagger;

	/** @var BlockAutopromoteStore */
	private $blockAutopromoteStore;

	/** @var FilterUser */
	private $filterUser;

	/** @var Session */
	private $session;

	/** @var string */
	private $requestIP;

	/**
	 * @todo This might drag in unwanted dependencies. The alternative is to use ObjectFactory, but that's harder
	 *   to understand for humans and static analysis tools, so do that only if the dependencies list starts growing.
	 * @param ServiceOptions $options
	 * @param LoggerInterface $logger
	 * @param BlockUserFactory $blockUserFactory
	 * @param UserGroupManager $userGroupManager
	 * @param BagOStuff $mainStash
	 * @param ChangeTagger $changeTagger
	 * @param BlockAutopromoteStore $blockAutopromoteStore
	 * @param FilterUser $filterUser
	 * @param Session $session
	 * @param string $requestIP
	 */
	public function __construct(
		ServiceOptions $options,
		LoggerInterface $logger,
		BlockUserFactory $blockUserFactory,
		UserGroupManager $userGroupManager,
		BagOStuff $mainStash,
		ChangeTagger $changeTagger,
		BlockAutopromoteStore $blockAutopromoteStore,
		FilterUser $filterUser,
		Session $session,
		string $requestIP
	) {
		$options->assertRequiredOptions( self::CONSTRUCTOR_OPTIONS );
		$this->options = $options;
		$this->logger = $logger;
		$this->blockUserFactory = $blockUserFactory;
		$this->userGroupManager = $userGroupManager;
		$this->mainStash = $mainStash;
		$this->changeTagger = $changeTagger;
		$this->blockAutopromoteStore = $blockAutopromoteStore;
		$this->filterUser = $filterUser;
		$this->session = $session;
		$this->requestIP = $requestIP;
	}

	// Each class has its factory method for better type inference and static analysis

	/**
	 * @param Parameters $params
	 * @param string $expiry
	 * @param bool $preventsTalk
	 * @return Block
	 */
	public function newBlock( Parameters $params, string $expiry, bool $preventsTalk ) : Block {
		return new Block( $params, $expiry, $preventsTalk, $this->blockUserFactory, $this->filterUser );
	}

	/**
	 * @param Parameters $params
	 * @param string $expiry
	 * @return RangeBlock
	 */
	public function newRangeBlock( Parameters $params, string $expiry ) : RangeBlock {
		return new RangeBlock(
			$params,
			$expiry,
			$this->blockUserFactory,
			$this->filterUser,
			$this->options->get( 'AbuseFilterRangeBlockSize' ),
			$this->options->get( 'BlockCIDRLimit' ),
			$this->requestIP
		);
	}

	/**
	 * @param Parameters $params
	 * @param \AbuseFilterVariableHolder $vars
	 * @return Degroup
	 */
	public function newDegroup( Parameters $params, \AbuseFilterVariableHolder $vars ) : Degroup {
		return new Degroup( $params, $vars, $this->userGroupManager, $this->filterUser );
	}

	/**
	 * @param Parameters $params
	 * @param int $duration
	 * @return BlockAutopromote
	 */
	public function newBlockAutopromote( Parameters $params, int $duration ) : BlockAutopromote {
		return new BlockAutopromote( $params, $duration, $this->blockAutopromoteStore, $this->logger );
	}

	/**
	 * @param Parameters $params
	 * @param array $throttleParams
	 * @phan-param array{id:int|string,count:int,period:int,groups:string[]} $throttleParams
	 * @return Throttle
	 */
	public function newThrottle( Parameters $params, array $throttleParams ) : Throttle {
		return new Throttle(
			$params,
			$throttleParams,
			$this->mainStash,
			$this->logger,
			$this->requestIP,
			$this->options->get( 'AbuseFilterIsCentral' ),
			$this->options->get( 'AbuseFilterCentralDB' )
		);
	}

	/**
	 * @param Parameters $params
	 * @param string $message
	 * @return Warn
	 */
	public function newWarn( Parameters $params, string $message ) : Warn {
		return new Warn( $params, $message, $this->session );
	}

	/**
	 * @param Parameters $params
	 * @param string $message
	 * @return Disallow
	 */
	public function newDisallow( Parameters $params, string $message ) : Disallow {
		return new Disallow( $params, $message );
	}

	/**
	 * @param Parameters $params
	 * @param string|null $accountName
	 * @param string[] $tags
	 * @return Tag
	 */
	public function newTag( Parameters $params, ?string $accountName, array $tags ) : Tag {
		return new Tag( $params, $accountName, $tags, $this->changeTagger );
	}
}

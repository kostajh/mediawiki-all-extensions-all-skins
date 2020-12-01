<?php
namespace BS\UsageTracker;

class CollectorResult {
	/**
	 *
	 * @var int
	 */
	public $count = 0;

	/**
	 *
	 * @var string
	 */
	public $descriptionKey = '';

	/**
	 *
	 * @var string
	 */
	public $identifier = '';

	/**
	 *
	 * @var string
	 */
	public $type = '';

	/**
	 *
	 * @var string
	 */
	public $updateDate = '';

	/**
	 *
	 * @param \BS\UsageTracker\Collectors\Base|null $oCollector
	 */
	public function __construct( $oCollector = null ) {
		if ( is_object( $oCollector ) && ( $oCollector instanceof Collectors\Base ) ) {
			$this->descriptionKey = $oCollector->getDescriptionKey();
			$this->identifier = $oCollector->getIdentifier();
			$this->updateDate = wfTimestamp();
			$this->type = get_class( $oCollector );
		}
	}

	/**
	 *
	 * @param \Wikimedia\Rdbms\ResultWrapper $oRow
	 * @return CollectorResult
	 */
	public static function newFromDBRow( $oRow ) {
		$oResult = new self();
		$oCollector = new $oRow->ut_type();
		$oResult->descriptionKey = $oCollector->getDescriptionKey();
		$oResult->identifier = $oRow->ut_identifier;
		$oResult->type = $oRow->ut_type;
		$oResult->updateDate = $oRow->ut_timestamp;
		$oResult->count = $oRow->ut_count;
		unset( $oCollector );
		return $oResult;
	}

	/**
	 *
	 * @return string|bool
	 */
	public function getUpdateDate() {
		return $this->updateDate;
	}

	/**
	 *
	 * @return string
	 */
	public function getDescription() {
		return wfMessage(
			$this->descriptionKey,
			wfMessage( $this->identifier )->exists()
				? wfMessage( $this->identifier )->text()
				: $this->identifier
			)->text();
	}
}

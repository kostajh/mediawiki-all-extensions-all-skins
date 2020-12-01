<?php

namespace MediaWiki\WikispeechSpeechDataCollector\Tests\Domain;

use Hamcrest\Core\IsSame;
use MediaWiki\WikispeechSpeechDataCollector\Domain\GetPersistentClassName;
use MediaWiki\WikispeechSpeechDataCollector\Domain\Persistent;
use MWTimestamp;
use PHPUnit\Framework\Constraint\Constraint;
use PHPUnit\Framework\ExpectationFailedException;
use SebastianBergmann\Exporter\Exporter;

/**
 * @package MediaWiki\WikispeechSpeechDataCollector\Tests\Domain
 * @since 0.1.0
 */
abstract class PersistentEqualsContraint extends Constraint {

	/** @var Persistent */
	private $expected;

	/** @var bool */
	private $success = true;

	/** @var array */
	private $failedFields = [];

	/**
	 * @since 0.1.0
	 * @param Persistent $expected
	 */
	public function __construct( Persistent $expected ) {
		$this->expected = $expected;
	}

	/**
	 * @since 0.1.0
	 * @param Persistent $actual
	 * @param string $description
	 * @param bool|null $returnResult
	 * @return bool Whether or not $actual passed assertion, or null if $returnResult is false.
	 * @throws ExpectationFailedException if expected is not equal and $returnResult is false.
	 */
	final public function evaluate(
		$actual,
		string $description = '',
		bool $returnResult = false
	) {
		if ( $actual === null && $this->expected === null ) {
			return $this->returnSuccessOrFail( $actual, $returnResult, $description );
		}
		if ( $actual !== null && $this->expected === null ) {
			if ( !$returnResult ) {
				$this->fail( $actual, $description . "\nExpected to be null." );
			}
			return false;
		}
		if ( $actual === null && $this->expected !== null ) {
			if ( !$returnResult ) {
				$this->fail( $actual, $description . "\nExpected to be not null." );
			}
			return false;
		}
		if ( !$actual->accept( new PersistentIsInstanceOfSame( $this->expected ) ) ) {
			if ( !$returnResult ) {
				$this->fail( $actual, $description .
					"\nExpected to be an instance of " .
					$this->expected->accept( new GetPersistentClassName() ) . '.' );
			}
			return false;

		}

		$identity = new IsSame( $this->expected->getIdentity() );
		if ( !$identity->matches( $actual->getIdentity() ) ) {
			$this->failField( 'identity', $this->expected->getIdentity(), $actual->getIdentity() );
		}

		$this->evaluateNonIdentityFields( $this->expected, $actual );
		return $this->returnSuccessOrFail( $actual, $returnResult, $description );
	}

	/**
	 * Evaluate all but the identity fields.
	 *
	 * At this point both $expected and $actual
	 * are known to be two non null instances of the same subclass of Persistent.
	 *
	 * @since 0.1.0
	 * @param Persistent $expected
	 * @param Persistent $actual
	 * @throws ExpectationFailedException if expected is not equal and $returnResult is false.
	 */
	abstract protected function evaluateNonIdentityFields(
		$expected,
		$actual
	);

	/**
	 * @since 0.1.0
	 * @param string $field
	 * @param mixed|null $expected
	 * @param mixed|null $actual
	 */
	protected function failField(
		string $field,
		$expected,
		$actual
	) {
		$this->success = false;
		array_push( $this->failedFields, [
			'field' => $field,
			'expected' => $expected,
			'actual' => $actual
		] );
	}

	/**
	 * @since 0.1.0
	 * @param Persistent|null $actual
	 * @param bool $returnResult
	 * @param string $description
	 * @return bool Whether or not $actual passed assertion, or null if $returnResult is false.
	 * @throws ExpectationFailedException if expected is not equal and $returnResult is false.
	 */
	protected function returnSuccessOrFail(
		?Persistent $actual,
		bool $returnResult,
		string $description
	): ?bool {
		if ( $returnResult ) {
			return $this->success;
		}
		if ( !$this->success ) {
			$message = "$description \n";
			foreach ( $this->failedFields as $failedField ) {
				$message .= $failedField['field'] . ' does not match expected ' .
					$failedField['expected'] . ', is ' .
					$failedField['actual'] . "\n";
			}
			$exporter = new Exporter();
			$message .= "\nin object\n" . $exporter->export( $this->expected );
			$this->fail( $actual, $message );
		}
		return null;
	}

	/**
	 * @since 0.1.0
	 * @return string
	 */
	public function toString(): string {
		return $this->expected->accept( new GetPersistentClassName() ) . ' equals';
	}

	/**
	 * @since 0.1.0
	 * @param string $field
	 * @param mixed|null $expected
	 * @param mixed|null $actual
	 */
	protected function matchIsSame(
		string $field,
		$expected,
		$actual
	) {
		$matcher = new IsSame( $expected );
		if ( !$matcher->matches( $actual ) ) {
			$this->failField( $field, $expected, $actual );
		}
	}

	/**
	 * @since 0.1.0
	 * @param string $field
	 * @param MWTimestamp|null $expected
	 * @param MWTimestamp|null $actual
	 */
	protected function matchIsTimestampSame(
		string $field,
		?MWTimestamp $expected,
		?MWTimestamp $actual
	) {
		$matcher = new IsMWTimestampSame( $expected );
		if ( !$matcher->matches( $actual ) ) {
			$this->failField( $field, $expected, $actual );
		}
	}

}

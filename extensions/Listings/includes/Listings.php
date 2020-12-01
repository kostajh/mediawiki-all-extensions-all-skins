<?php

class Listings {

	/**
	 * Register parser hooks
	 *
	 * @param Parser $parser
	 */
	public static function setupHooks( Parser $parser ) {
		$parser->setHook( 'buy', [ 'Listings', 'buyListings' ] );
		$parser->setHook( 'do', [ 'Listings', 'doListings' ] );
		$parser->setHook( 'drink', [ 'Listings', 'drinkListings' ] );
		$parser->setHook( 'eat', [ 'Listings', 'eatListings' ] );
		$parser->setHook( 'listing', [ 'Listings', 'otherlistings' ] );
		$parser->setHook( 'see', [ 'Listings', 'seeListings' ] );
		$parser->setHook( 'sleep', [ 'Listings', 'sleepListings' ] );
	}

	/**
	 * @param string $input
	 * @param array $args
	 * @param Parser $parser
	 * @return string
	 */
	public static function buyListings( $input, array $args, $parser ) {
		return self::listingsTag( 'buy', $input, $args, $parser );
	}

	/**
	 * @param string $input
	 * @param array $args
	 * @param Parser $parser
	 * @return string
	 */
	public static function doListings( $input, array $args, $parser ) {
		return self::listingsTag( 'do', $input, $args, $parser );
	}

	/**
	 * @param string $input
	 * @param array $args
	 * @param Parser $parser
	 * @return string
	 */
	public static function drinkListings( $input, array $args, $parser ) {
		return self::listingsTag( 'drink', $input, $args, $parser );
	}

	/**
	 * @param string $input
	 * @param array $args
	 * @param Parser $parser
	 * @return string
	 */
	public static function eatListings( $input, array $args, $parser ) {
		return self::listingsTag( 'eat', $input, $args, $parser );
	}

	/**
	 * @param string $input
	 * @param array $args
	 * @param Parser $parser
	 * @return string
	 */
	public static function otherListings( $input, array $args, $parser ) {
		return self::listingsTag( 'listing', $input, $args, $parser );
	}

	/**
	 * @param string $input
	 * @param array $args
	 * @param Parser $parser
	 * @return string
	 */
	public static function seeListings( $input, array $args, $parser ) {
		return self::listingsTag( 'see', $input, $args, $parser );
	}

	/**
	 * @param string $input
	 * @param array $args
	 * @param Parser $parser
	 * @return string
	 */
	public static function sleepListings( $input, array $args, $parser ) {
		return self::listingsTag( 'sleep', $input, $args, $parser );
	}

	/**
	 * @param string $aType
	 * @param string $input
	 * @param array $args
	 * @param Parser $parser
	 * @return string
	 */
	private static function listingsTag( $aType, $input, $args, $parser ) {
		/*
		 * if a {{listings}} template exists,
		 * feed tag name and parameter list to template verbatim and exit
		 */
		$ltemplate = '';
		if ( !wfMessage( 'listings-template' )->inContentLanguage()->isDisabled() ) {
			$ltemplate = wfMessage( 'listings-template' )->inContentLanguage()->text();
		}
		if ( $ltemplate != '' ) {
			$inputtext = '{{' . $ltemplate . '|type=' . $aType;
			foreach ( $args as $key => $value ) {
				$inputtext .= '|' . $key . '=' . $value;
			}
			$inputtext .= '|' . $input . '}}';
			$out = $parser->internalParse( $inputtext );
			return $out;
		}

		/*
		 * if no pre-defined template exists, generate listing from parameters normally
		 */

		// @todo Should the args be made safe HTML?
		if ( isset( $args['name'] ) ) {
			$name = $args['name'];
		} else {
			$name = wfMessage( 'listings-unknown' )->inContentLanguage()->text();
		}
		if ( isset( $args['alt'] ) ) {
			$alt = $parser->internalParse( $args['alt'] );
		} else {
			$alt = '';
		}
		if ( isset( $args['address'] ) ) {
			$address = $parser->internalParse( $args['address'] );
		} else {
			$address = '';
		}
		if ( isset( $args['directions'] ) ) {
			$directions = $parser->internalParse( $args['directions'] );
		} else {
			$directions = '';
		}
		if ( isset( $args['phone'] ) ) {
			$phone = $args['phone'];
		} else {
			$phone = '';
		}
		if ( isset( $args['tollfree'] ) ) {
			$tollfree = $args['tollfree'];
		} else {
			$tollfree = '';
		}
		if ( isset( $args['email'] ) ) {
			$email = $args['email'];
		} else {
			$email = '';
		}
		if ( isset( $args['fax'] ) ) {
			$fax = $args['fax'];
		} else {
			$fax = '';
		}
		if ( isset( $args['url'] ) ) {
			$url = $args['url'];
		} else {
			$url = '';
		}
		if ( isset( $args['hours'] ) ) {
			$hours = $args['hours'];
		} else {
			$hours = '';
		}
		if ( isset( $args['price'] ) ) {
			$price = $args['price'];
		} else {
			$price = '';
		}
		if ( isset( $args['checkin'] ) ) {
			$checkin = $args['checkin'];
		} else {
			$checkin = '';
		}
		if ( isset( $args['checkout'] ) ) {
			$checkout = $args['checkout'];
		} else {
			$checkout = '';
		}
		if ( isset( $args['lat'] ) && is_numeric( $args['lat'] ) ) {
			$lat = $args['lat'];
		} else {
			$lat = 361;
		}
		if ( isset( $args['long'] ) && is_numeric( $args['long'] ) ) {
			$long = $args['long'];
		} else {
			$long = 361;
		}

		$position = '';
		if ( $lat < 361 && $long < 361 ) { // @fixme: incorrect validation
			if ( !wfMessage( 'listings-position-template' )->inContentLanguage()->isDisabled() ) {
				$positionTemplate = wfMessage( 'listings-position-template', $lat, $long )
					->inContentLanguage()->text();
				if ( $positionTemplate != '' ) {
					$parsedTemplate = $parser->internalParse( '{{' . $positionTemplate . '}}' );
					// @todo FIXME: i18n issue (hard coded colon/space)
					$position = wfMessage( 'listings-position' )->inContentLanguage()
						->rawParams( $parsedTemplate )->escaped();
				}
			}
		}

		// @fixme: a lot of localisation-unfriendly patchwork below
		$out = Html::element( 'strong', [], $name );
		if ( $url != '' ) {
			$sanitizedHref = Sanitizer::validateAttributes(
				[ 'href' => $url ],
				[ 'href' => true ]
			);
			if ( isset( $sanitizedHref['href'] ) ) {
				$out = Html::rawElement( 'a',
					$sanitizedHref + [ 'class' => 'external text', 'rel' => 'nofollow', 'title' => $name ],
					$out
				);
			}
		}
		if ( $alt != '' ) {
			// @todo FIXME: i18n issue (hard coded parentheses)
			$out .= ' (<em>' . $alt . '</em>)';
		}
		if ( ( $address != '' ) || ( $directions != '' ) || ( $position != '' ) ) {
			$out .= ', ' . $address;
			if ( ( $directions != '' ) || ( $position != '' ) ) {
				// @todo FIXME: i18n issue (hard coded parentheses)
				$out .= ' (<em>' . $directions;
				if ( ( $directions != '' ) && ( $position != '' ) ) {
					// @todo FIXME: i18n issue (hard coded comma list or list to text)
					$out .= ', ';
				}
				if ( $position != '' ) {
					$out .= $position;
				}
				$out .= '</em>)';
			}
		}

		$phoneSymbol = $parser->internalParse(
			wfMessage( 'listings-phone-symbol' )->inContentLanguage()->text() );
		if ( $phoneSymbol != '' ) {
			$phoneSymbol = '<abbr title="' .
				wfMessage( 'listings-phone' )->inContentLanguage()->escaped() .
				'">' . $phoneSymbol . '</abbr>';
		} else {
			$phoneSymbol = wfMessage( 'listings-phone' )->inContentLanguage()->escaped();
		}
		$faxSymbol = $parser->internalParse(
			wfMessage( 'listings-fax-symbol' )->inContentLanguage()->text() );
		if ( $faxSymbol != '' ) {
			$faxSymbol = '<abbr title="' .
				wfMessage( 'listings-fax' )->inContentLanguage()->escaped() .
				'">' . $faxSymbol . '</abbr>';
		} else {
			$faxSymbol = wfMessage( 'listings-fax' )->inContentLanguage()->escaped();
		}
		$emailSymbol = $parser->internalParse(
			wfMessage( 'listings-email-symbol' )->inContentLanguage()->text() );
		if ( $emailSymbol != '' ) {
			$emailSymbol = '<abbr title="' .
				wfMessage( 'listings-email' )->inContentLanguage()->escaped() .
				'">' . $emailSymbol . '</abbr>';
		} else {
			$emailSymbol = wfMessage( 'listings-email' )->inContentLanguage()->escaped();
		}
		$tollfreeSymbol = $parser->internalParse(
			wfMessage( 'listings-tollfree-symbol' )->inContentLanguage()->text() );
		if ( $tollfreeSymbol != '' ) {
			$tollfreeSymbol = '<abbr title="' .
				wfMessage( 'listings-tollfree' )->inContentLanguage()->escaped() .
				'">' . $tollfreeSymbol . '</abbr>';
		} else {
			$tollfreeSymbol = wfMessage( 'listings-tollfree' )->inContentLanguage()->escaped();
		}

		if ( ( $phone != '' ) || ( $tollfree != '' ) ) {
			// @todo FIXME: i18n issue (hard coded comma list, space)
			$out .= ', ' . $phoneSymbol . ' ' . htmlspecialchars( $phone );
			if ( $tollfree != '' ) {
				// @todo FIXME: i18n issue (hard coded parentheses)
				$out .= ' (' . $tollfreeSymbol . ': ' . htmlspecialchars( $tollfree ) . ')';
			}
		}
		if ( $fax != '' ) {
			// @todo FIXME: i18n issue (hard coded comma list, colon/space)
			$out .= ', ' . $faxSymbol . ': ' . htmlspecialchars( $fax );
		}
		if ( $email != '' ) {
			// @todo FIXME: i18n issue (hard comma list, coded colon/space)
			$out .= ', ' . $emailSymbol . ': '
				. Html::element( 'a', [ 'class' => 'email', 'href' => "mailto:$email" ], $email );
		}
		// @todo FIXME: i18n issue (hard coded text)
		$out .= '. ';

		if ( $hours != '' ) {
			$out .= htmlspecialchars( $hours ) . '. ';
		}
		if ( ( $checkin != '' ) || ( $checkout != '' ) ) {
			if ( $checkin != '' ) {
				$out .= wfMessage( 'listings-checkin', $checkin )->inContentLanguage()->escaped();
				if ( $checkout != '' ) {
					// @todo FIXME: i18n issue (hard coded comma list)
					$out .= ', ';
				}
			}
			if ( $checkout != '' ) {
				$out .= wfMessage( 'listings-checkout', $checkout )->inContentLanguage()->escaped();
			}
			// @todo FIXME: i18n issue (hard coded wut?)
			$out .= '. ';
		}
		if ( $price != '' ) {
			// @todo FIXME: i18n issue (hard coded text)
			$out .= htmlspecialchars( $price ) . '. ';
		}

		$out .= $parser->internalParse( $input );

		return $out;
	}
}

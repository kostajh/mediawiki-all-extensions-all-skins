<?php
/**
 * A special page to show a message with all language names.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * @file
 * @author Amir E. Aharoni
 * @copyright Copyright © 2014, Amir E. Aharoni
 * @license GPL-2.0-or-later
 */

/**
 * A form for testing a message and a grammar form with all language names
 *
 * @ingroup SpecialPage TranslateSpecialPage
 */

class SpecialTestLanguageNameGrammar extends SpecialPage {
	public function __construct() {
		parent::__construct( 'TestLanguageNameGrammar' );
	}

	public function execute( $parameters ) {
		$this->setHeaders();
		$this->outputHeader();

		$context = $this->getContext();
		$form = new HtmlForm(
			$this->getDataModel(),
			$context,
			'testlanguagenamegrammar'
		);
		$form->setId( 'testlanguagenamegrammar-form' );
		$form->setSubmitText( $context->msg( 'testlanguagenamegrammar-submit' )->text() );
		$form->setSubmitID( 'testlanguagenamegrammar-submit' );
		$form->setSubmitCallback( [ $this, 'formSubmit' ] );

		$form->show();

		/*
		$out = $this->getOutput();

		$out->addHtml( $this->getFormsTable(
			'ru',
			'languageadverb',
			'Она говорит {{GRAMMAR:languageadverb|$1}}'
		) );
		*/

		return;
	}

	private function getDataModel() {
		$model = [];

		$model['language'] = [
			'type' => 'text',
			'label-message' => 'testlanguagenamegrammar-language',
		];

		$model['grammarform'] = [
			'type' => 'text',
			'label-message' => 'testlanguagenamegrammar-grammarform',
		];

		return $model;
	}

	public function formSubmit( $formData ) {
		$language = $formData['language'];
		$grammarForm = $formData['grammarform'];

		$out = $this->getOutput();

		$out->addHtml( $this->getFormsTable( $language, $grammarForm ) );
	}

	public function getFormsTable( $langCode, $form ) {
		$languageNames = Language::fetchLanguageNames( $langCode );
		$rows = '';
		$lang = Language::factory( $langCode );
		$dir = $lang->getDir();
		$out = $this->getOutput();

		foreach ( array_keys( $languageNames ) as $outputCode ) {
			$codeCell = Html::element(
				'td',
				[],
				$outputCode
			);

			$nameCell = Html::element(
				'td',
				[
					'dir' => $dir,
					'lang' => $langCode,
				],
				$languageNames[$outputCode]
			);

			$currentMessage = "{{GRAMMAR:$form|$languageNames[$outputCode]}}";

			$messageCell = Html::rawElement(
				'td',
				[
					'dir' => $dir,
					'lang' => $langCode,
				],
				$out->parseAsInterface( $currentMessage, false, false, $lang )
			);

			$rows .= Html::rawElement( 'tr', [], $codeCell . $nameCell . $messageCell );
		}

		$table = Html::rawElement(
			'table',
			[ 'class' => 'wikitable' ],
			$rows
		);

		return $table;
	}
}

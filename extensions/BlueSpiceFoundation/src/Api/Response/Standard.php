<?php

namespace BlueSpice\Api\Response;

/**
 * This response should implement the ExtJS standard format for serverside
 * form validations:
 * http://docs.sencha.com/extjs/4.2.2/#!/api/Ext.form.action.Submit
 *
 * TODO: do a clean implemenation with gettes and setters
 */
class Standard {
	const ERRORS = 'errors';
	const SUCCESS = 'success';
	const MESSAGE = 'message';
	const PAYLOAD = 'payload';
	const PAYLOAD_COUNT = 'payload_count';

	public $errors = [];
	public $success = false;

	// Custom fields
	public $message = '';
	public $payload = [];
	public $payload_count = 0;
}

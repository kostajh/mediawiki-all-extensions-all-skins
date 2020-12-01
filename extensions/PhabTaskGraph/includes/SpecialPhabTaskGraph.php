<?php

require_once __DIR__ . '/../arcanist/src/__phutil_library_init__.php';

class SpecialPhabTaskGraph extends IncludableSpecialPage {

	function __construct() {
		parent::__construct( 'PhabTaskGraph' );
	}

	private $client = null;
	private $nodes = [];
	private $links = [];
	private $projects = [];
	private $people = [];

	public function execute( $parser ) {
		$request = $this->getRequest();
		$output = $this->getOutput();
		$this->setHeaders();

		$this->client = new ConduitClient( $GLOBALS['wgPhabTaskGraphPhabURL'] );
		$api_token = $GLOBALS['wgPhabTaskGraphConduitAPIToken'];
		$this->client->setConduitToken( $api_token );

		$width = $request->getInt( 'width' );
		if ( $width == 0 ) {
			$width = 800;
		}

		$height = $request->getInt( 'height' );
		if ( $height == 0 ) {
			$height = 800;
		}

		$statusarray = $request->getArray( 'status' );
		if ( !$statusarray ) {
			$size = 0;
		} else {
			$size = count( $statusarray );
		}
		if ( $size === 0 ) {
			$statusarray = [ 'open', 'stalled' ];
		} elseif ( $size === 1 ) {
			$statusarray = array_map( 'trim', explode( ',', $statusarray[0] ) );
		}

		$tasks = $request->getText( 'tasks' );
		if ( $tasks != '' ) {
			$taskarray = array_unique( array_map( 'trim', explode( ',', $tasks ) ) );
			foreach ( $taskarray as $i => $task ) {
				if ( $task[0] === 'T' ) {
					$taskarray[$i] = substr( $task, 1 );
				}
			}
			$this->getTasks( $taskarray, $statusarray );
		} else {
			$taskarray = [];
		}

		$projects = $request->getText( 'projects' );
		if ( $projects != '' ) {
			$projectarray =
				array_unique( array_map( 'trim', explode( ',', $projects ) ) );
			$this->getProjects( $projectarray, $statusarray );
			$mappedprojectarray = [];
			foreach ( $this->projects as $project ) {
				if ( in_array( $project['name'], $projectarray ) ) {
					$mappedprojectarray[] = $project['phid'];
				}
			}
			$projectarray = $mappedprojectarray;
		} else {
			$projectarray = [];
		}

		$nodearray = [];
		$index = 0;
		foreach ( $this->nodes as $n => $node ) {
			$nodearray[$index] = $node;
			$this->nodes[$n]['index'] = $index;
			$index++;
		}

		$linkarray = [];
		$index = 0;
		foreach ( $this->links as $link ) {
			$newlink = [];
			$newlink['source'] = $this->nodes[$link['source']]['id'];
			$newlink['target'] = $this->nodes[$link['target']]['id'];
			$linkarray[$index] = $newlink;
			$index++;
		}

		if ( $nodearray != [] ) {
			$output->addModules( 'ext.PhabTaskGraph' );

			$graphName = 'PhabTaskGraphDiv';
			$data = [
				'id' => $graphName,
				'selected_tasks' => $taskarray,
				'selected_projects' => $projectarray,
				'nodes' => $nodearray,
				'links' => $linkarray,
				'projects' => $this->projects,
				'people' => $this->people,
				'url' => $GLOBALS['wgPhabTaskGraphPhabURL'],
				'width' => $width,
				'height' => $height
			];
			$output->addJsConfigVars( 'PhabTaskGraphConfig', $data );

			$html = Html::element( 'div',
				[
					'id' => $graphName
				] );

			$output->addHTML( $html );
		}

		if ( !$this->including() ) {

			$formDescriptor = [
				'tasksfield' => [
					'label-message' => 'phabtaskgraph-tasks-field-label',
					'help-message' => 'phabtaskgraph-tasks-field-help',
					'class' => 'HTMLTextField',
					'default' => $tasks,
					'name' => 'tasks'
				],
				'projectsfield' => [
					'label-message' => 'phabtaskgraph-projects-field-label',
					'help-message' => 'phabtaskgraph-projects-field-help',
					'class' => 'HTMLTextField',
					'default' => $projects,
					'name' => 'projects'
				],
				'statusfield' => [
					'label-message' => 'phabtaskgraph-status-field-label',
					'type' => 'multiselect',
					'options' => [
						$this->msg( 'phabtaskgraph-status-open' )->escaped() => 'open',
						$this->msg( 'phabtaskgraph-status-stalled' )->escaped() => 'stalled',
						$this->msg( 'phabtaskgraph-status-resolved' )->escaped() => 'resolved',
						$this->msg( 'phabtaskgraph-status-invalid' )->escaped() => 'invalid',
						$this->msg( 'phabtaskgraph-status-declined' )->escaped() => 'declined',
						$this->msg( 'phabtaskgraph-status-duplicate' )->escaped() => 'duplicate'
					],
					'default' => $statusarray,
					'name' => 'status'
				],
				'widthfield' => [
					'label-message' => 'phabtaskgraph-width-field-label',
					'help-message' => 'phabtaskgraph-width-field-help',
					'class' => 'HTMLTextField',
					'default' => $width,
					'name' => 'width'
				],
				'heightfield' => [
					'label-message' => 'phabtaskgraph-height-field-label',
					'help-message' => 'phabtaskgraph-height-field-help',
					'class' => 'HTMLTextField',
					'default' => $height,
					'name' => 'height'
				]
			];

			$htmlForm =
				HTMLForm::factory( 'ooui', $formDescriptor, $this->getContext() );

			$htmlForm->setMethod( 'get' );

			$htmlForm->prepareForm()->displayForm( false );
		}
	}

	private function getTasks( $tasks, $statusarray ) {
		$tasklist = [];
		foreach ( $tasks as $task ) {
			$t = intval( $task );
			if ( !isset( $this->nodes[$t] ) ) {
				$tasklist[] = $t;
			}
		}
		$params = [
			'constraints' => [
				'ids' => $tasklist,
				'statuses' => $statusarray
			],
			'attachments' => [
				'projects' => [
					true
				]
			]
		];
		$result = $this->client->callMethodSynchronous( 'maniphest.search',
			$params );
		$data = $result['data'];
		foreach ( $data as $datum ) {
			$this->parseTask( $datum, $statusarray );
		}
	}

	private function getProjects( $projects, $statusarray ) {
		$params = [
			'constraints' => [
				'projects' => $projects,
				'statuses' => $statusarray
			],
			'attachments' => [
				'projects' => [
					true
				],
				'columns' => [
					true
				]
			]
		];
		$result = $this->client->callMethodSynchronous( 'maniphest.search',
			$params );
		$data = $result['data'];
		foreach ( $data as $datum ) {
			$this->parseTask( $datum, $statusarray );
		}
	}

	private function parseTask( $data, $statusarray ) {
		$taskID = $data['id'];
		if ( isset( $this->nodes[$taskID] ) ) {
			return;
		}
		$task = [];
		$task['id'] = $taskID;
		$task['phid'] = $data['phid'];
		$task['taskid'] = 'T' . $data['id'];
		$task['name'] = $data['fields']['name'];
		$task['status'] = $data['fields']['status']['value'];
		$task['color'] = $data['fields']['priority']['color'];
		if ( $data['fields']['authorPHID'] ) {
			$this->getPerson( $data['fields']['authorPHID'] );
			$task['author'] = $data['fields']['authorPHID'];
		} else {
			$task['author'] = null;
		}
		if ( $data['fields']['ownerPHID'] ) {
			$this->getPerson( $data['fields']['ownerPHID'] );
			$task['owner'] = $data['fields']['ownerPHID'];
		} else {
			$task['owner'] = null;
		}
		$task['projects'] = [];
		foreach ( $data['attachments']['projects']['projectPHIDs'] as $projphID ) {
			$this->getProject( $projphID );
			if ( array_key_exists( 'columns', $data['attachments'] ) &&
				array_key_exists( $projphID,
				$data['attachments']['columns']['boards'] ) ) {
				$column = ' (' . $data['attachments']['columns']['boards'][$projphID]
					['columns'][0]['name'] . ')';
			} else {
				$column = '';
			}
			$task['projects'][$projphID] = $this->projects[$projphID]['name'] . $column;
		}
		$this->nodes[$taskID] = $task;
		$this->getSubtasks( $taskID, $statusarray );
	}

	private function getSubtasks( $parentTaskID, $statusarray ) {
		$params = [
			'constraints' => [
				'parentIDs' => [
					$parentTaskID
				],
				'statuses' => $statusarray
			],
			'attachments' => [
				'projects' => [
					true
				],
				'columns' => [
					true
				]
			]
		];
		$data = $this->client->callMethodSynchronous( 'maniphest.search',
			$params );
		foreach ( $data['data'] as $task ) {
			$taskID = $task['id'];
			$this->parseTask( $task, $statusarray );
			$link = [];
			$link['source'] = $parentTaskID;
			$link['target'] = $taskID;
			$this->links[] = $link;
		}
	}

	private function getProject( $projphID ) {
		if ( isset( $this->projects[$projphID] ) ) {
			return;
		}
		$params = [
			'constraints' => [
				'phids' => [
					$projphID
				],
			]
		];
		$result = $this->client->callMethodSynchronous( 'project.search',
			$params );
		$data = $result['data'];
		if ( count( $data ) > 0 ) {
			$this->parseProject( $data[0] );
		}
	}

	private function parseProject( $data ) {
		$projphID = $data['phid'];
		if ( isset( $this->projects[$projphID] ) ) {
			return;
		}
		$project = [];
		$project['id'] = $data['id'];
		$project['phid'] = $projphID;
		$project['name'] = $data['fields']['name'];
		$project['color'] = $data['fields']['color']['key'];
		$this->projects[$projphID] = $project;
	}

	private function getPerson( $personphID ) {
		if ( isset( $this->people[$personphID] ) ) {
			return;
		}
		$params = [
			'constraints' => [
				'phids' => [
					$personphID
				],
			]
		];
		$result = $this->client->callMethodSynchronous( 'user.search',
			$params );
		$data = $result['data'];
		if ( count( $data ) > 0 ) {
			$this->parsePerson( $data[0] );
		}
	}

	private function parsePerson( $data ) {
		$personphID = $data['phid'];
		if ( isset( $this->people[$personphID] ) ) {
			return;
		}
		$person = [];
		$person['id'] = $data['id'];
		$person['phid'] = $personphID;
		$person['username'] = $data['fields']['username'];
		$person['realname'] = $data['fields']['realName'];
		$this->people[$personphID] = $person;
	}
}

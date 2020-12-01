#!/usr/bin/php
<?php
/**
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 * http://www.gnu.org/copyleft/gpl.html
 *
 * @file
 * @category Maintenance
 * @package  ImportPhabData
 * @author   Cindy Cicalese (ccicalese@wikimedia.org)
 * @license  http://www.gnu.org/copyleft/gpl.html GPL-2.0-or-later
 * @link     https://www.mediawiki.org/wiki/Extension:PhabGraph
 *
 */
// @codingStandardsIgnoreStart
$IP = getenv( "MW_INSTALL_PATH" ) ? getenv( "MW_INSTALL_PATH" ) : __DIR__ . "/../../..";
if ( !is_readable( "$IP/maintenance/Maintenance.php" ) ) {
	die( "MW_INSTALL_PATH needs to be set to your MediaWiki installation.\n" );
}
require_once ( "$IP/maintenance/Maintenance.php" );
// @codingStandardsIgnoreEnd

/**
 * Maintenance script that imports data from Phabricator into a wiki
 *
 * @ingroup Maintenance
 * @SuppressWarnings(StaticAccess)
 * @SuppressWarnings(LongVariable)
 */
class ImportPhabData extends Maintenance {
	private $client = null;
	private $verbose = false;
	private $minimal = false;
	private $dry_run = false;
	private $create = false;
	private $save_info = false;
	private $preload_task = false;
	private $delay = false;
	private $phabTasks = [];
	private $projects = [];
	private $columns = [];
	private $users = [];

	public function __construct() {
		parent::__construct();
		$this->addDescription( "CLI utility to import Phabricator data into a wiki." );
		$this->addArg( "project",
			"Phabricator project(s) to search for tasks (comma-separated, no spaces).", true );
		$this->addArg( "category",
			"Category of task pages (default: Phabricator Tasks).", false );
		$this->addArg( "task template",
			"Template for Phabricator tasks (default: Phabricator Task).",
			false );
		$this->addArg( "project template",
			"Template for Phabricator projects (default: Phabricator Project).",
			false );
		$this->addArg( "user template",
			"Template for Phabricator users (default: Phabricator User).",
			false );
		$this->addArg( "transition template",
			"Template for column transitions (default: Transition).",
			false );
		$this->addOption( "verbose", "Verbose output",
			false, false, 'v' );
		$this->addOption( "minimal",
			"Only get tasks in listed projects and their subtasks (ignore category)",
			false, false, 'm' );
		$this->addOption( "dry-run", "Don't edit the wiki pages.",
			false, false, 'n' );
		$this->addOption( "create", "Create wiki pages for tasks that don't exist",
			false, false, 'c' );
		$this->addOption( "save-info", "Save import information to wiki page",
			false, true, 's' );
		$this->addOption( "preload-task", "Name of preload page for new tasks",
			false, true, 'p' );
		$this->addOption( "delay", "Delay in seconds before each Conduit API call (default: 0)",
			false, true, 'd' );
		$this->requireExtension( 'PhabTaskGraph' );
	}

	/**
	 * @inheritDoc
	 */
	public function execute() {
		$this->verbose = $this->getOption( 'verbose' );
		$this->minimal = $this->getOption( 'minimal' );
		$this->dry_run = $this->getOption( 'dry-run' );
		$this->create = $this->getOption( 'create' );
		$this->save_info = $this->getOption( 'save-info' );
		$this->preload_task = $this->getOption( 'preload-task' );
		$this->delay = intval( $this->getOption( 'delay', 0 ) );

		$projectNames = $this->getArg( 0 );
		$namespaceName = $this->getArg( 1, '0' );
		$categoryName = $this->getArg( 2, 'Phabricator Tasks' );
		$taskTemplateName = $this->getArg( 3, 'Phabricator Task' );
		$projectTemplateName = $this->getArg( 4, 'Phabricator Project' );
		$userTemplateName = $this->getArg( 5, 'Phabricator User' );
		$transitionTemplateName = $this->getArg( 6, 'Transition' );
		if ( $this->verbose ) {
			echo 'Project(s): ' . $projectNames . PHP_EOL;
			echo 'Namespace: ';
			if ( $namespaceName === '0' ) {
				echo '(main)';
			} else {
				echo $namespaceName;
			}
			echo PHP_EOL;
			echo 'Category: ' . $categoryName . PHP_EOL;
			echo 'Task Template: ' . $taskTemplateName . PHP_EOL;
			echo 'Project Template: ' . $projectTemplateName . PHP_EOL;
			echo 'User Template: ' . $userTemplateName . PHP_EOL;
			if ( $this->save_info ) {
				echo 'Save Info to Page: ' . $this->save_info . PHP_EOL;
			}
			if ( $this->preload_task ) {
				echo 'Preload Task Page: ' . $this->preload_task . PHP_EOL;
			}
		}

		require_once __DIR__ . '/../arcanist/src/__phutil_library_init__.php';
		$phabURL = $GLOBALS['wgPhabTaskGraphPhabURL'];
		$this->client = new ConduitClient( $phabURL );
		$api_token = $GLOBALS['wgPhabTaskGraphConduitAPIToken'];
		$this->client->setConduitToken( $api_token );

		foreach ( explode( ',', $projectNames ) as $projectName ) {
			$this->getPhabricatorTasksFromProject( $projectName );
		}
		$wikiTasks = [];

		if ( $this->create || $this->minimal ) {
			foreach ( $this->phabTasks as $taskID => $task ) {
				$title = $taskID;
				if ( $title[0] !== 'T' ) {
					$title = 'T' . $title;
				}
				if ( $namespaceName !== '0' ) {
					$title = $namespaceName . ':' . $title;
				}
				$title = Title::newFromText( $title );
				if ( $title !== null ) {
					$wikiTasks[$taskID] = $title;
				}
			}
		}

		if ( !$this->minimal ) {
			$category = Category::newFromName( $categoryName );
			$titles = $category->getMembers();
			if ( $this->verbose ) {
				echo PHP_EOL . 'Found ' . count( $titles ) . ' tasks in category ' .
					$categoryName . PHP_EOL;
			}
			foreach ( $titles as $title ) {
				$taskID = $title->getText();
				if ( $taskID[0] === 'T' ) {
					$taskID = substr( $taskID, 1 );
				}
				$wikiTasks[$taskID] = $title;
			}
		}

		foreach ( $wikiTasks as $taskID => $title ) {
			if ( !isset( $this->phabTasks[$taskID] ) ) {
				$this->getPhabricatorTask( $taskID );
			}
		}

		$columns = [];
		foreach ( $this->columns as $columnPHID => $column ) {
			if ( $column === true ) {
				$columns[] = $columnPHID;
			}
		}
		$this->getColumns( $columns );

		if ( !$this->dry_run ) {
			if ( $this->verbose ) {
				echo PHP_EOL;
			}
			foreach ( $wikiTasks as $taskID => $title ) {
				if ( isset( $this->phabTasks[$taskID] ) ) {
					$formattedTask = $this->formatTask( $taskID,
						$this->phabTasks[$taskID], $taskTemplateName, $projectTemplateName,
						$userTemplateName, $transitionTemplateName );
					$this->editTask( $title, $taskTemplateName, $formattedTask );
				}
			}
		}

		if ( ( $this->verbose || ( $this->save_info && !$this->dry_run ) ) &&
			!$this->minimal ) {
			$info = 'Count of wiki pages in category ' . $categoryName . ': ' .
				count( $wikiTasks ) . PHP_EOL . PHP_EOL;
			$info .= 'Count of Phabricator tasks in project(s) ' . $projectNames .
				' and all subtasks: ' . count( $this->phabTasks ) .
				PHP_EOL . PHP_EOL;
			$info .= 'Open Phabricator tasks in project(s) ' . $projectNames .
				' that did not have wiki pages:' . PHP_EOL;
			foreach ( $this->phabTasks as $taskID => $task ) {
				if ( !isset( $wikiTasks[$taskID] ) && $task['fromProject'] &&
					$task['status'] === 'open' ) {
					$info .= '* ' . $phabURL . '/T' . $taskID .
						' (<span class="plainlinks">[{{fullurl:';
					$pagename = 'T' . $taskID;
					if ( $namespaceName !== '0' ) {
						$pagename = $namespaceName . ':' . $pagename;
					}
					$info .= $pagename;
					if ( $this->preload_task ) {
						$info .= '|action=edit&preload={{urlencode:' .
							$this->preload_task . '}}';
					}
					$info .= '}} ' . $pagename . ']</span>)';
					$info .= ' {{#ifexist:' . $pagename . '| - created}}' . PHP_EOL;
				}
			}

			if ( $this->verbose ) {
				echo PHP_EOL . $info;
			}

			if ( $this->save_info && !$this->dry_run ) {
				$title = Title::newFromText( $this->save_info );
				if ( $title ) {
					$wikiPage = new WikiPage( $title );
					$edit_summary = 'updated info from Phabricator';
					$flags = EDIT_MINOR;
					$new_content = new WikitextContent( $info );
					$wikiPage->doEditContent( $new_content, $edit_summary, $flags, false,
						User::newSystemUser( 'Maintenance script' ) );
				} else {
					echo 'Invalid page title: ' . $this->save_info . PHP_EOL;
				}
			}
		}

		if ( $this->verbose && $this->minimal ) {
			echo PHP_EOL;
		}
	}

	private function callAPI( $api, $params ) {
		$after = null;
		$allData = [];
		do {
			if ( $this->delay > 0 ) {
				sleep( $this->delay );
			}
			if ( $after === null ) {
				$p = $params;
			} else {
				$a = [
					"after" => $after
				];
				$p = $params + $a;
			}
			$results = $this->client->callMethodSynchronous( $api, $p );
			$resultData = $results['data'];
			foreach ( $resultData as $data ) {
				$allData[] = $data;
			}
			$after = $results['cursor']['after'];
		} while ( $after !== null );
		return $allData;
	}

	private function callOldAPI( $api, $params ) {
		if ( $this->delay > 0 ) {
			sleep( $this->delay );
		}
		$results = $this->client->callMethodSynchronous( $api, $params );
		return $results;
	}

	private function getPhabricatorTasksFromProject( $projectName ) {
		$params = [
			'constraints' => [
				'projects' => [
					$projectName
				],
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
		$resultData = $this->callAPI( 'maniphest.search', $params );
		if ( $this->verbose ) {
			echo PHP_EOL . 'Found ' . count( $resultData ) . ' tasks in project ' .
				$projectName . PHP_EOL;
		}
		foreach ( $resultData as $data ) {
			$this->parseTask( $data, true );
		}
	}

	private function getPhabricatorTask( $taskID ) {
		$params = [
			'constraints' => [
				'ids' => [
					intval( $taskID ),
				],
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
		$resultData = $this->callAPI( 'maniphest.search', $params );
		foreach ( $resultData as $data ) {
			$this->parseTask( $data, false );
		}
	}

	private function parseTask( $data, $fromProject ) {
		$taskID = $data['id'];
		if ( isset( $this->phabTasks[$taskID] ) ) {
			if ( $fromProject ) {
				$this->phabTasks[$taskID]['fromProject'] = $fromProject;
			}
			return;
		}
		if ( $this->verbose ) {
			echo 'P'; // let the user know it is working (parsing)
		}
		$task = [];
		$task['name'] = $data['fields']['name'];
		$task['dateCreated'] = $data['fields']['dateCreated'];
		$task['dateModified'] = $data['fields']['dateModified'];
		$task['dateClosed'] = $data['fields']['dateClosed'];
		$task['status'] = $data['fields']['status']['value'];
		$task['color'] = $data['fields']['priority']['color'];
		$task['points'] = $data['fields']['points'];
		if ( $data['fields']['authorPHID'] ) {
			$this->getUser( $data['fields']['authorPHID'] );
			$task['author'] = $this->users[$data['fields']['authorPHID']];
		} else {
			$task['author'] = null;
		}
		if ( $data['fields']['ownerPHID'] ) {
			$this->getUser( $data['fields']['ownerPHID'] );
			$task['owner'] = $this->users[$data['fields']['ownerPHID']];
		} else {
			$task['owner'] = null;
		}

		$taskTransactionData = $this->getTaskTransactions( $taskID );
		$task['transitions'] = [];
		foreach ( $taskTransactionData as $transactionData ) {
			if ( array_key_exists( 'transactionType', $transactionData ) &&
				$transactionData['transactionType'] === 'core:columns' &&
				isset( $transactionData['newValue'] ) &&
				array_key_exists( 'columnPHID', $transactionData['newValue'][0] ) ) {

				// column entry
				$transition = [];
				$columnPHID = $transactionData['newValue'][0]['columnPHID'];
				$transition['date'] = $transactionData['dateCreated'];
				$transition['type'] = 'Entered Column';
				$transition['item'] = $columnPHID;
				$task['transitions'][] = $transition;
				if ( !isset( $this->columns[$columnPHID] ) ) {
					$this->columns[$columnPHID] = true;
				}

				// column exit
				foreach ( $transactionData['newValue'][0]['fromColumnPHIDs'] as $exitColumnPHID ) {
					$transition = [];
					$transition['date'] = $transactionData['dateCreated'];
					$transition['type'] = 'Exited Column';
					$transition['item'] = $exitColumnPHID;
					$task['transitions'][] = $transition;
					if ( !isset( $this->columns[$exitColumnPHID] ) ) {
						$this->columns[$exitColumnPHID] = true;
					}
				}
			} elseif ( array_key_exists( 'transactionType', $transactionData ) &&
				$transactionData['transactionType'] === 'core:edge' ) {
				foreach ( $transactionData['oldValue'] as $projectPHID ) {
					if ( is_string( $projectPHID ) && substr( $projectPHID, 0, 9 ) === 'PHID-PROJ' ) {
						$this->getProject( $projectPHID );
						$transition = [];
						$transition['date'] = $transactionData['dateCreated'];
						$transition['type'] = 'Removed Project';
						$transition['item'] = $this->projects[$projectPHID]['fullname'];
						$task['transitions'][] = $transition;
					}
				}
				foreach ( $transactionData['newValue'] as $projectPHID ) {
					if ( is_string( $projectPHID ) && substr( $projectPHID, 0, 9 ) === 'PHID-PROJ' ) {
						$this->getProject( $projectPHID );
						$transition = [];
						$transition['date'] = $transactionData['dateCreated'];
						$transition['type'] = 'Added Project';
						$transition['item'] = $this->projects[$projectPHID]['fullname'];
						$task['transitions'][] = $transition;
					}
				}
			}
		}

		$task['projects'] = [];
		foreach ( $data['attachments']['projects']['projectPHIDs'] as $projectPHID ) {
			$project = [];
			$project['entryDate'] = $task['dateCreated'];
			$this->getProject( $projectPHID );
			if ( isset( $this->projects[$projectPHID] ) ) {
				$project['name'] = $this->projects[$projectPHID]['fullname'];
				if ( array_key_exists( 'columns', $data['attachments'] ) &&
					array_key_exists( $projectPHID,
					$data['attachments']['columns']['boards'] ) ) {
					$project['column'] =
						$data['attachments']['columns']['boards'][$projectPHID]['columns'][0]['name'];
					$columnID = $data['attachments']['columns']['boards'][$projectPHID]['columns'][0]['phid'];
					$this->columns[$columnID] = [
						'project' => $project['name'],
						'column' => $project['column']
					];
					$project['entryDate'] =
						$this->parseColumnEntryDate( $taskTransactionData, $columnID, $task['dateCreated'] );
				}
				$task['projects'][$this->projects[$projectPHID]['name'] . $projectPHID] = $project;
			}
		}
		$task['fromProject'] = $fromProject;
		$task['subtasks'] = [];
		$this->phabTasks[$taskID] = $task;
		$this->getSubtasks( $taskID );
	}

	private function getTaskTransactions( $taskID ) {
		$params = [
			'ids' => [
				intval( $taskID )
			]
		];
		$resultData = $this->callOldAPI( 'maniphest.gettasktransactions', $params );
		return $resultData[$taskID];
	}

	private function getColumns( $columnIDs ) {
		$params = [
			'constraints' => [
				'phids' => $columnIDs
			]
		];
		$resultData = $this->callAPI( 'project.column.search', $params );
		foreach ( $resultData as $data ) {
			$projectPHID = $data['fields']['project']['phid'];
			if ( isset( $this->projects[$projectPHID] ) ) {
				$this->columns[$data['phid']] = [
					'project' => $this->projects[$projectPHID]['fullname'],
					'column' => $data['fields']['name']
				];
			} else {
				$this->columns[$data['phid']] = [
					'project' => $data['fields']['project']['name'],
					'column' => $data['fields']['name']
				];
			}
		}
	}

	private function parseColumnEntryDate( $taskTransactionData, $columnID, $taskCreationDate ) {
		$latestDate = $taskCreationDate;
		foreach ( $taskTransactionData as $data ) {
			if ( array_key_exists( 'transactionType', $data ) &&
				$data['transactionType'] === 'core:columns' ) {
				if ( isset( $data['newValue'] ) && array_key_exists( 'columnPHID', $data['newValue'][0] ) &&
					$data['newValue'][0]['columnPHID'] === $columnID ) {
					if ( $latestDate === null || $latestDate < $data['dateCreated'] ) {
						$latestDate = $data['dateCreated'];
					}
				}
			}
		}
		return $latestDate;
	}

	private function getSubtasks( $parentTaskID ) {
		$params = [
			'constraints' => [
				'parentIDs' => [
					$parentTaskID
				],
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
		$resultData = $this->callAPI( 'maniphest.search', $params );
		foreach ( $resultData as $data ) {
			$this->parseTask( $data, false );
			$this->phabTasks[$parentTaskID]['subtasks'][] = $data['id'];
		}
	}

	private function getProject( $projectPHID ) {
		if ( isset( $this->projects[$projectPHID] ) ) {
			return;
		}
		$params = [
			'constraints' => [
				'phids' => [
					$projectPHID
				],
			]
		];
		$resultData = $this->callAPI( 'project.search', $params );
		if ( count( $resultData ) > 0 ) {
			$this->parseProject( $resultData[0] );
		}
	}

	private function parseProject( $data ) {
		$projectPHID = $data['phid'];
		if ( isset( $this->projects[$projectPHID] ) ) {
			return;
		}
		$project = [];
		$project['name'] = $data['fields']['name'];
		$project['color'] = $data['fields']['color']['key'];
		if ( isset( $data['fields']['parent']['name'] ) ) {
			$project['parent-name'] = $data['fields']['parent']['name'];
			$project['fullname'] = $project['parent-name'] .
						' (' . $project['name'] . ')';
		} else {
			$project['fullname'] = $project['name'];
		}
		$this->projects[$projectPHID] = $project;
	}

	private function getUser( $userPHID ) {
		if ( isset( $this->users[$userPHID] ) ) {
			return;
		}
		$params = [
			'constraints' => [
				'phids' => [
					$userPHID
				],
			]
		];
		$resultData = $this->callAPI( 'user.search', $params );
		if ( count( $resultData ) > 0 ) {
			$this->parseUser( $resultData[0] );
		}
	}

	private function parseUser( $data ) {
		$userPHID = $data['phid'];
		if ( isset( $this->users[$userPHID] ) ) {
			return;
		}
		$user = [];
		$user['username'] = $data['fields']['username'];
		$user['realName'] = $data['fields']['realName'];
		$this->users[$userPHID] = $user;
	}

	private function fixName( $name ) {
		$old = [
			'|',
			'{',
			'}',
			'[',
			']'
		];
		$new = [
			'&#124;',
			'&#123;',
			'&#125;',
			'&#91;',
			'&#93;'
		];
		return str_replace( $old, $new, $name );
	}

	private function formatTask( $taskID, $task, $taskTemplateName,
		$projectTemplateName, $userTemplateName, $transitionTemplateName ) {
		$formattedTask = '{{' . $taskTemplateName . PHP_EOL;
		$formattedTask .=
			'|name=' . $this->fixName( $task['name'] ) . PHP_EOL;
		$formattedTask .=
			'|status=' . $task['status'] . PHP_EOL;
		$formattedTask .=
			'|color=' . $task['color'] . PHP_EOL;
		$formattedTask .=
			'|points=' . $task['points'] . PHP_EOL;
		$formattedTask .= '|dateCreated=' . $task['dateCreated'] . PHP_EOL;
		if ( $task['dateModified'] !== null ) {
			$formattedTask .= '|dateModified=' . $task['dateModified'] . PHP_EOL;
		}
		if ( $task['dateClosed'] !== null ) {
			$formattedTask .= '|dateClosed=' . $task['dateClosed'] . PHP_EOL;
		}
		if ( $task['author'] !== null ) {
			$formattedTask .=
				'|author=' . $this->formatUser( 'author', $task['author'],
				$userTemplateName ) . PHP_EOL;
		}
		if ( $task['owner'] !== null ) {
			$formattedTask .=
				'|owner=' . $this->formatUser( 'owner', $task['owner'],
				$userTemplateName ) . PHP_EOL;
		}
		if ( count( $task['projects'] ) > 0 ) {
			$formattedTask .=
				'|projects=';
			ksort( $task['projects'] );
			foreach ( $task['projects'] as $project ) {
				$formattedTask .=
					$this->formatProject( $project, $projectTemplateName );
			}
			$formattedTask .= PHP_EOL;
		}
		if ( isset( $task['subtasks'] ) && count( $task['subtasks'] ) > 0 ) {
			$formattedTask .=
				'|subtasks=' . implode( ',', $task['subtasks'] ) . PHP_EOL;
		}
		if ( isset( $task['transitions'] ) && count( $task['transitions'] ) > 0 ) {
			$formattedTask .= '|transitions=' .
				$this->formatTransitions( $task['transitions'], $transitionTemplateName ) . PHP_EOL;
		}
		$formattedTask .= '}}' . PHP_EOL;
		return $formattedTask;
	}

	private function formatUser( $type, $user, $userTemplateName ) {
		$formattedUser = '{{' . $userTemplateName . PHP_EOL;
		$formattedUser .=
			'|type=' . $type . PHP_EOL;
		$formattedUser .=
			'|username=' . $user['username'] . PHP_EOL;
		$formattedUser .=
			'|realName=' . $user['realName'] . PHP_EOL;
		$formattedUser .= '}}';
		return $formattedUser;
	}

	private function formatTransitions( $transitions, $transitionTemplateName ) {
		$formattedTransitions = '';
		foreach ( $transitions as $transition ) {
			$formattedTransitions .= '{{' . $transitionTemplateName . PHP_EOL .
				'|date=' . $transition['date'] . PHP_EOL .
				'|type=' . $transition['type'] . PHP_EOL;
			if ( $transition['type'] === 'Entered Column' ||
				$transition['type'] === 'Exited Column' ) {
				$column = $this->columns[$transition['item']];
				$formattedTransitions .=
					'|project=' . $column['project'] . PHP_EOL .
					'|column=' . $column['column'] . PHP_EOL;
			} elseif ( $transition['type'] === 'Added Project' ||
				$transition['type'] === 'Removed Project' ) {
				$formattedTransitions .=
					'|project=' . $transition['item'] . PHP_EOL;
			}
			$formattedTransitions .= '}}';
		}
		return $formattedTransitions;
	}

	private function formatProject( $project, $projectTemplateName ) {
		$column = null;
		if ( isset( $project['column'] ) ) {
			$name = $project['name'];
			$column = $project['column'];
		} else {
			$pos = strpos( $project['name'], ' (' );
			if ( $pos !== false ) {
				$name = substr( $project['name'], 0, $pos );
				$column = substr( $project['name'], $pos + 2, -1 );
			} else {
				$name = $project['name'];
			}
		}
		$formattedProject = '{{' . $projectTemplateName . PHP_EOL;
		$formattedProject .= '|name=' . $this->fixName( $name ) . PHP_EOL;
		if ( $column !== null ) {
			$formattedProject .= '|column=' . $column . PHP_EOL;
		}
		$formattedProject .= '|entryDate=' . $project['entryDate'] . PHP_EOL;
		$formattedProject .= '}}';
		return $formattedProject;
	}

	private function editTask( $title, $taskTemplateName, $formattedTask ) {
		if ( $this->verbose ) {
			echo 'E'; // let the user know it is working (editing)
		}
		if ( $title->getContentModel() !== CONTENT_MODEL_WIKITEXT ) {
			echo $title->getPrefixedText() . ' is not a wikitext page.' . PHP_EOL;
			return;
		}
		$wikiPage = new WikiPage( $title );
		$articleText = '';

		if ( $title->exists() ) {

			$wikiPageContent = $wikiPage->getContent();
			$articleText = $wikiPageContent->getNativeData();

			$pos = strpos( $articleText, '{{' . $taskTemplateName . PHP_EOL );
			if ( $pos !== false ) {
				$articleText = substr( $articleText, 0, $pos );
			}

		}

		$articleText .= $formattedTask;
		$edit_summary = 'updated task from Phabricator';
		$flags = EDIT_MINOR;
		$new_content = new WikitextContent( $articleText );
		$wikiPage->doEditContent( $new_content, $edit_summary, $flags, false,
			User::newSystemUser( 'Maintenance script' ) );
	}
}

$maintClass = "ImportPhabData";
require_once RUN_MAINTENANCE_IF_MAIN;

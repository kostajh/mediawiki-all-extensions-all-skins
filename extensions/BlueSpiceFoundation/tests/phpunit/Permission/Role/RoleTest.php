<?php

namespace BlueSpice\Tests\Permission\Role;

use BlueSpice\Permission\PermissionRegistry;
use BlueSpice\Permission\RoleFactory;
use MediaWikiTestCase;

/** @covers \BlueSpice\Permission\Role\Role */
class RoleTest extends MediaWikiTestCase {
	public static function setUpBeforeClass() : void {
		parent::setUpBeforeClass();
		$GLOBALS['bsgPermissionConfig']['read'] = [ 'roles' => [ 'dummy', 'admin' ] ];
	}

	protected function getFreshRoleFactory() {
		return new RoleFactory( [],
			PermissionRegistry::newInstance(
				$GLOBALS['bsgPermissionConfigDefault'],
				$GLOBALS['bsgPermissionConfig']
			)
		);
	}

	public function testRoleCreation() {
		$roleFactory = $this->getFreshRoleFactory();
		$role = $roleFactory->makeRole( 'dummy' );
		$this->assertSame(
			'dummy', $role->getName(),
			'Role name should be the same as the one passed'
		);

		$this->assertArrayEquals( [ 'read' ], $role->getPermissions() );
	}

	public function testAddingPermissions() {
		$roleFactory = $this->getFreshRoleFactory();
		$role = $roleFactory->makeRole( 'dummy' );
		$role->addPermission( 'edit' );
		$role->addPermission( 'dummypermission' );

		$this->assertTrue(
			in_array( 'edit', $role->getPermissions() ),
			'Role should have edit permission'
		);
		$this->assertTrue(
			in_array( 'dummypermission', $role->getPermissions() ),
			'Role should have dummypermission permission'
		);
	}

	public function testRemovingPermissions() {
		$roleFactory = $this->getFreshRoleFactory();
		$role = $roleFactory->makeRole( 'dummy' );
		$role->removePermission( 'edit' );
		$role->removePermission( 'dummypermission' );

		$this->assertFalse(
			in_array( 'edit', $role->getPermissions() ),
			'Role should not have edit permission'
		);
		$this->assertFalse(
			in_array( 'dummypermission', $role->getPermissions() ),
			'Role should not have dummypermission permission'
		);
	}
}

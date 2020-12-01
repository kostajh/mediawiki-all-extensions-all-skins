<?php

namespace MediaWiki\Extension\WikibaseManifest;

use NamespaceInfo;
use PHPUnit\Framework\TestCase;
use Wikibase\DataAccess\EntitySource;

class LocalSourceEntityNamespacesFactoryTest extends TestCase {

	public function testGetEntityNamespaces() {
		$itemNamespaceId = 0;
		$propertyNamespaceId = 123;
		$itemNamespaceName = '';
		$propertyNamespaceName = 'Property';
		$entityNamespaceIds = [
			'item' => $itemNamespaceId,
			'property' => $propertyNamespaceId,
		];

		$localEntitySource = $this->createMock( EntitySource::class );
		$localEntitySource->expects( $this->once() )->method( 'getEntityNamespaceIds' )->willReturn(
				$entityNamespaceIds
			);

		$namespaceInfo = $this->createMock( NamespaceInfo::class );
		$namespaceInfo->expects( $this->any() )->method( 'getCanonicalName' )->willReturnMap(
				[
					[ $itemNamespaceId, $itemNamespaceName ],
					[ $propertyNamespaceId, $propertyNamespaceName ],
				]
			);

		$entityNamespacesFactory = new LocalSourceEntityNamespacesFactory(
			$localEntitySource, $namespaceInfo
		);

		$this->assertEquals(
			[
				'item' => [
					'namespace_id' => $itemNamespaceId,
					'namespace_name' => $itemNamespaceName,
				],
				'property' => [
					'namespace_id' => $propertyNamespaceId,
					'namespace_name' => $propertyNamespaceName,
				],
			],
			$entityNamespacesFactory->getEntityNamespaces()->toArray()
		);
	}
}

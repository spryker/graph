<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Shared\Graph;

use Codeception\Test\Unit;
use Spryker\Shared\Graph\Graph;
use Spryker\Shared\Graph\GraphAdapterInterface;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Shared
 * @group Graph
 * @group GraphTest
 * Add your own group annotations below this line
 */
class GraphTest extends Unit
{
    /**
     * @var string
     */
    public const GRAPH_NAME = 'graph name';

    /**
     * @var string
     */
    public const NODE_A = 'node A';

    /**
     * @var string
     */
    public const NODE_B = 'node B';

    /**
     * @var string
     */
    public const GROUP_NAME = 'group name';

    /**
     * @var string
     */
    public const CLUSTER_NAME = 'cluster name';

    /**
     * @var array
     */
    public const ATTRIBUTES = ['attribute' => 'value', 'html attribute' => '<h1>Html Value</h1>'];

    public function testCreateInstance(): void
    {
        $this->assertInstanceOf(Graph::class, $this->getGraph(static::GRAPH_NAME));
    }

    public function testCreateInstanceWithAttributes(): void
    {
        $this->assertInstanceOf(Graph::class, $this->getGraph(static::GRAPH_NAME, static::ATTRIBUTES));
    }

    public function testCreateInstanceUnDirectedGraph(): void
    {
        $this->assertInstanceOf(Graph::class, $this->getGraph(static::GRAPH_NAME, [], false));
    }

    public function testCreateInstanceTolerantGraph(): void
    {
        $this->assertInstanceOf(Graph::class, $this->getGraph(static::GRAPH_NAME, [], true, false));
    }

    public function testAddNode(): void
    {
        $this->assertInstanceOf(Graph::class, $this->getGraph()->addNode(static::NODE_A));
    }

    public function testAddNodeWithAttributes(): void
    {
        $this->assertInstanceOf(Graph::class, $this->getGraph()->addNode(static::NODE_A, static::ATTRIBUTES));
    }

    public function testAddNodeWithGroup(): void
    {
        $this->assertInstanceOf(Graph::class, $this->getGraph()->addNode(static::NODE_A, [], static::GROUP_NAME));
    }

    public function testAddEdge(): void
    {
        $adapter = $this->getGraphWithNodes();

        $this->assertInstanceOf(Graph::class, $adapter->addEdge(static::NODE_A, static::NODE_B));
    }

    public function testAddEdgeWithAttributes(): void
    {
        $adapter = $this->getGraphWithNodes();

        $this->assertInstanceOf(Graph::class, $adapter->addEdge(static::NODE_A, static::NODE_B, static::ATTRIBUTES));
    }

    public function testAddCluster(): void
    {
        $this->assertInstanceOf(Graph::class, $this->getGraph()->addCluster(static::CLUSTER_NAME));
    }

    public function testAddClusterWithAttributes(): void
    {
        $this->assertInstanceOf(Graph::class, $this->getGraph()->addCluster(static::CLUSTER_NAME, static::ATTRIBUTES));
    }

    public function testRender(): void
    {
        $this->assertIsString($this->getGraph()->render('svg'));
    }

    public function testRenderWithFileName(): void
    {
        $this->assertIsString($this->getGraph()->render('svg', 'filename'));
    }

    private function getGraph(string $name = self::GRAPH_NAME, array $attributes = [], bool $directed = true, bool $strict = true): Graph
    {
        $adapterMock = $this->createAdapterMock();

        return new Graph($adapterMock, $name, $attributes, $directed, $strict);
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|\Spryker\Shared\Graph\GraphAdapterInterface
     */
    private function createAdapterMock(): GraphAdapterInterface
    {
        $adapterMock = $this->getMockBuilder(GraphAdapterInterface::class)->onlyMethods(['create', 'addNode', 'addEdge', 'addCluster', 'render'])->getMock();
        $adapterMock->method('render')->willReturn('');

        return $adapterMock;
    }

    private function getGraphWithNodes(): Graph
    {
        $adapter = $this->getGraph();
        $adapter->addNode(static::NODE_A);
        $adapter->addNode(static::NODE_B);

        return $adapter;
    }
}

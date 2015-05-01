<?php

namespace YvesUnit\SprykerFeature\Yves\Assets;

use SprykerFeature\Yves\Assets\Model\AssetUrlBuilder;
use SprykerFeature\Yves\Assets\Model\CacheBusterInterface;

class AssetUrlBuilderTest extends \PHPUnit_Framework_TestCase
{
    private $host;

    protected function setUp()
    {
        parent::setUp();
        $this->host = 'example.de';
    }

    /**
     * @group Asset
     */
    public function testAssetUrl()
    {
        $provider = new AssetUrlBuilder(
            $this->host,
            $this->getCacheBusterMock()
        );

        $this->assertEquals('//' . $this->host . '/media.css', $provider->buildUrl('media.css'));
    }

    /**
     * @group Asset
     */
    public function testMediaUrlWithTrailingSlashes()
    {
        $provider = new AssetUrlBuilder(
            $this->host,
            $this->getCacheBusterMock()
        );


        $this->assertEquals('//' . $this->host . '/media.css', $provider->buildUrl('/media.css'));
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|CacheBusterInterface
     */
    private function getCacheBusterMock()
    {
        $mock = $this->getMock('\SprykerFeature\Yves\Assets\Model\CacheBusterInterface');
        $mock->expects($this->any())
            ->method('addCacheBust')
            ->will($this->returnArgument(0));

        return $mock;
    }
}
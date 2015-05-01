<?php

namespace SprykerFeature\Yves\Assets\Model;

abstract class AbstractUrlBuilder
{
    /**
     * @var string
     */
    private $staticHost;

    /**
     * @param string $staticHost
     */
    public function __construct($staticHost)
    {
        $this->staticHost = $staticHost;
    }

    /**
     * @param string $assetPath
     *
     * @return string
     * @throws \Exception
     */
    public function buildUrl($assetPath)
    {
        $mediaPath = $this->getStaticHost();

        return '//' . $mediaPath . $this->addTrailingSlash($assetPath) . $assetPath;
    }

    /**
     * @return string
     */
    public function getStaticHost()
    {
        return $this->staticHost;
    }

    /**
     * @param string $urlPart
     *
     * @return string
     */
    private function addTrailingSlash($urlPart)
    {
        return (($urlPart[0] === '/') ? '' : '/');
    }
}
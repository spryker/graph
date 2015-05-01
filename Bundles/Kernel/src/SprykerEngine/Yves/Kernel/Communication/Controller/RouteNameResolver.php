<?php

namespace SprykerEngine\Yves\Kernel\Communication\Controller;

use SprykerEngine\Shared\Kernel\Communication\RouteNameResolverInterface;

class RouteNameResolver implements RouteNameResolverInterface
{

    /**
     * @var string
     */
    private $path;

    /**
     * @param $path
     */
    public function __construct($path)
    {
        $this->path = $path;
    }

    /**
     * @return string
     */
    public function resolve()
    {
        return $this->path;
    }

}
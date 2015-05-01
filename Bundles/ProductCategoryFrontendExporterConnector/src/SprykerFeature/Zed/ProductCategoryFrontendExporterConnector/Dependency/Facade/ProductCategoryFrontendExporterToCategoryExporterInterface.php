<?php

namespace SprykerFeature\Zed\ProductCategoryFrontendExporterConnector\Dependency\Facade;

/**
 * Class ProductCategoryFrontendExporterToCategoryExporterInterface
 * @package SprykerFeature\Zed\ProductCategoryFrontendExporterConnector\Dependency\Facade
 */
interface ProductCategoryFrontendExporterToCategoryExporterInterface
{
    /**
     * @param array  $data
     * @param string $idsField
     * @param string $namesField
     * @param string $urlsField
     * @return array
     */
    public function explodeGroupedNodes(array $data, $idsField, $namesField, $urlsField);
}
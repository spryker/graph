<?php

/**
 * (c) Spryker Systems GmbH copyright protected
 */

namespace SprykerFeature\Client\CategoryExporter\Model;

use SprykerFeature\Client\Storage\StorageClientInterface;
use SprykerFeature\Shared\Collector\Code\KeyBuilder\KeyBuilderInterface;

class Navigation
{

    /**
     * @var StorageClientInterface
     */
    private $keyValueReader;

    /**
     * @var KeyBuilderInterface
     */
    private $urlBuilder;

    /**
     * @param StorageClientInterface $keyValueReader
     * @param KeyBuilderInterface $urlBuilder
     */
    public function __construct(StorageClientInterface $keyValueReader, KeyBuilderInterface $urlBuilder)
    {
        $this->keyValueReader = $keyValueReader;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * @param string $locale
     *
     * @return array
     */
    public function getCategories($locale)
    {
        $urlKey = $this->urlBuilder->generateKey([], $locale);
        $categories = $this->keyValueReader->get($urlKey);
        if ($categories) {
            return $categories;
        }

        return [];
    }

}
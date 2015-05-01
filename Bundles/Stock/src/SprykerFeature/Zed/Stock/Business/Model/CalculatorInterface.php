<?php

namespace SprykerFeature\Zed\Stock\Business\Model;

interface CalculatorInterface
{
    /**
     * @param string $sku
     * @return int
     */
    public function calculateStockForProduct($sku);
}
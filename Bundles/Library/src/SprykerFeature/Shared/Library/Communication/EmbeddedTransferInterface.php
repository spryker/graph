<?php
namespace SprykerFeature\Shared\Library\Communication;

use SprykerFeature\Shared\Library\TransferObject\TransferInterface;

interface EmbeddedTransferInterface
{
    /**
     * @param TransferInterface $transferObject
     * @return $this
     */
    public function setTransfer(TransferInterface $transferObject);

    /**
     * @return TransferInterface
     */
    public function getTransfer();
}
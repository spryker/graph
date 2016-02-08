<?php

/**
 * (c) Spryker Systems GmbH copyright protected
 */

namespace Spryker\Zed\Graph\Communication\Exception;

class InvalidGraphAdapterNameException extends AbstractGraphAdapterException
{

    /**
     * @param string $message
     * @param int $code
     * @param \Exception $previous
     */
    public function __construct($message = "", $code = 0, \Exception $previous = null)
    {
        $message = sprintf('Invalid GraphAdapterName provided. "%s" can not be instanced.', $message)
            . PHP_EOL
            . self::MESSAGE;

        parent::__construct($message, $code, $previous);
    }

}
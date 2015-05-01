<?php

namespace SprykerFeature\Zed\Application\Communication\Plugin\TransferObject;

use SprykerEngine\Zed\Kernel\Communication\AbstractPlugin;
use SprykerFeature\Shared\ZedRequest\Client\RequestInterface;
use Symfony\Component\HttpFoundation\Request as HttpRequest;

class Repeater extends AbstractPlugin
{

    /**
     * @var bool
     */
    protected static $isRepeatInProgress = false;

    /**
     * @param null $mvc
     * @return string
     */
    public static function getRepeatData($mvc = null)
    {
        self::$isRepeatInProgress = true;
        if (!is_null($mvc)) {
            return \SprykerFeature_Shared_Library_Log::getFlashInFile('last_yves_request_' . $mvc . '.log');
        } else {
            return \SprykerFeature_Shared_Library_Log::getFlashInFile('last_yves_request.log');
        }
    }

    /**
     * @param RequestInterface $transferObject
     * @param HttpRequest $httpRequest
     */
    public static function setRepeatData(RequestInterface $transferObject, HttpRequest $httpRequest)
    {
        if (self::$isRepeatInProgress) {
            return;
        }

        if (\SprykerFeature_Shared_Library_Environment::isNotDevelopment()) {
            return;
        }

        $repeatData = array(
            'module' => $httpRequest->attributes->get('module'),
            'controller' => $httpRequest->attributes->get('controller'),
            'action' => $httpRequest->attributes->get('action'),
            'params' => $transferObject->toArray(false),
        );

        $mvc = $httpRequest->attributes->get('module') . '_' . $httpRequest->attributes->get('controller') . '_' . $httpRequest->attributes->get('action');
        \SprykerFeature_Shared_Library_Log::setFlashInFile($repeatData, 'last_yves_request_' . $mvc . '.log');
        \SprykerFeature_Shared_Library_Log::setFlashInFile($repeatData, 'last_yves_request.log');
    }
}
<?php

namespace SprykerFeature\Zed\Auth\Business;

use Generated\Zed\Ide\AutoCompletion;
use Generated\Zed\Ide\FactoryAutoCompletion\AuthBusiness;
use SprykerEngine\Zed\Kernel\Business\AbstractDependencyContainer;
use SprykerFeature\Zed\Auth\Business\Client\StaticToken;
use SprykerFeature\Zed\Auth\Business\Model\Auth;
use SprykerFeature\Zed\User\Business\UserFacade;

/**
 * @method AuthBusiness getFactory()
 */
class AuthDependencyContainer extends AbstractDependencyContainer
{
    /**
     * @return AuthFacade
     */
    public function locateAuthFacade()
    {
        return $this->getLocator()->auth()->facade();
    }

    /**
     * @return UserFacade
     */
    public function locateUserFacade()
    {
        return $this->getLocator()->user()->facade();
    }

    /**
     * @return Auth
     */
    public function createAuthModel()
    {
        return $this->getFactory()->createModelAuth(
            $this->getLocator(),
            $this->getLocator()->application()->pluginSession(),
            $this->getLocator()->user()->facade(),
            $this->getConfig(),
            $this->createStaticTokenClient()
        );
    }

    /**
     * @return StaticToken
     */
    public function createStaticTokenClient()
    {
        return $this->getFactory()->createClientStaticToken();
    }
}
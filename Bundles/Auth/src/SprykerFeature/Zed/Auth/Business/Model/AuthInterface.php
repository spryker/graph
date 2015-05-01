<?php

namespace SprykerFeature\Zed\Auth\Business\Model;

use SprykerFeature\Shared\User\Transfer\User;
use SprykerFeature\Zed\Auth\Business\Exception\UserNotLoggedException;
use SprykerFeature\Zed\User\Business\Exception\UserNotFoundException;

interface AuthInterface
{
    /**
     * @param string $username
     * @param string $password
     *
     * @return bool
     */
    public function authenticate($username, $password);

    /**
     * @param User $user
     *
     * @return string
     */
    public function generateToken(User $user);

    /**
     * @return bool
     */
    public function logout();

    /**
     * @param string $token
     *
     * @return bool
     */
    public function isAuthorized($token);

    /**
     * @return string
     */
    public function getCurrentUserToken();

    /**
     * @return bool
     */
    public function hasCurrentUser();

    /**
     * @param string $hash
     *
     * @return User
     * @throws UserNotFoundException
     */
    public function getSystemUserByHash($hash);

    /**
     * @param string $token
     *
     * @return mixed
     * @throws UserNotLoggedException
     */
    public function getCurrentUser($token);

    /**
     * @param string $token
     *
     * @return bool
     */
    public function userTokenIsValid($token);

    /**
     * @param string $bundle
     * @param string $controller
     * @param string $action
     *
     * @return bool
     */
    public function isIgnorablePath($bundle, $controller, $action);
}
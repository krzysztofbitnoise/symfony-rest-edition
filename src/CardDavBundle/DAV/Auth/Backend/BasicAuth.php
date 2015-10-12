<?php

namespace CardDavBundle\DAV\Auth\Backend;

use Sabre\DAV\Auth\Backend\AbstractBasic;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * HTTP Basic auth backend.
 *
 */
class BasicAuth extends AbstractBasic {

    /** var $userProvider UserProviderInterface */
    protected $userProvider;

    /**
     * Creates the backend.
     *
     *
     * @param UserProviderInterface $userProvider
     * @return void
     */
    function __construct(UserProviderInterface $userProvider) {
        $this->userProvider = $userProvider;
    }

    /**
     * Validates a username and password
     *
     *
     * @param string $username
     * @param string $password
     * @return bool
     */
    protected function validateUserPass($username, $password) {
        $user = $this->userProvider->loadUserByUsername($username);

        return $user && $password ? $user->verifyPassword($password) : false;
    }

}


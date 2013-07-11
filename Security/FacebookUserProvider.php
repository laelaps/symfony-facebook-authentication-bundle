<?php

namespace Laelaps\Bundle\FacebookAuthentication\Security;

use Laelaps\Bundle\Facebook\FacebookAdapterAwareInterface;
use Laelaps\Bundle\Facebook\FacebookAdapterAwareTrait;
use Laelaps\Bundle\FacebookAuthentication\Exception\InvalidUser as InvalidUserException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

abstract class FacebookUserProvider implements FacebookAdapterAwareInterface, UserProviderInterface
{
    use FacebookAdapterAwareTrait;

    /**
     * @var bool
     */
    private $shouldCreateUserByDefault = true;

    /**
     * @var array
     */
    private $usernameShouldCreateStatus = [];

    /**
     * @var array
     */
    private static $cachedFacebookData = [];

    /**
     * @param string $username
     * @return null|\Symfony\Component\Security\Core\User\UserInterface
     */
    abstract protected function doLoadUserByUsername($username);

    /**
     * @param array $facebookData
     * @return void
     */
    abstract public function createUserByFacebookData(array $facebookData);

    /**
     * @param string $username
     * @return array
     */
    protected function getCurrentUserFacebookData($username)
    {
        if (!isset(self::$cachedFacebookData[$username])) {
            self::$cachedFacebookData[$username] = $this->getFacebookAdapter()->api('/me');
        }

        return self::$cachedFacebookData[$username];
    }

    /**
     * @param string $username The username
     * @return \Symfony\Component\Security\Core\User\UserInterface
     * @throws \Symfony\Component\Security\Core\Exception\UsernameNotFoundException
     * @throws \Laelaps\Bundle\FacebookAuthentication\Exception\InvalidUser
     */
    public function loadUserByUsername($username)
    {
        $user = $this->doLoadUserByUsername($username);

        if ($user instanceof UserInterface) {
            return $user;
        }

        if ($user) {
            throw new InvalidUserException($user);
        }

        if ($this->shouldCreateUser($username)) {
            $this->createUserByFacebookData($this->getCurrentUserFacebookData($username));
            $this->setShouldCreateUser($username, false);

            return $this->loadUserByUsername($username);
        }

        throw new UsernameNotFoundException(sprintf('Failed to load user by username: "%s"', $username));
    }

    /**
     * @param \Symfony\Component\Security\Core\User\UserInterface $user
     * @return \Symfony\Component\Security\Core\User\UserInterface
     * @throws \Symfony\Component\Security\Core\Exception\UnsupportedUserException
     */
    public function refreshUser(UserInterface $user)
    {
        $userClass = get_class($user);
        $username = $user->getUsername();

        if (!($this->supportsClass($userClass))) {
            throw new UnsupportedUserException(sprintf('User "%s" identified by username "%s" is not supported here.', $userClass, $username));
        }

        $this->setShouldCreateUser($username, false);

        return $this->loadUserByUsername($username);
    }

    /**
     * @param bool $shouldCreateUserByDefault
     * @return void
     */
    public function setShouldCreateUserByDefault($shouldCreateUserByDefault)
    {
        $this->shouldCreateUserByDefault = (bool) $shouldCreateUserByDefault;
    }

    /**
     * @param string $username
     * @return bool
     */
    public function shouldCreateUser($username)
    {
        if (isset($this->usernameShouldCreateStatus[$username])) {
            return $this->usernameShouldCreateStatus[$username];
        }

        return $this->shouldCreateUserByDefault();
    }

    /**
     * @param string $class
     * @return bool
     */
    public function supportsClass($class)
    {
        return true;
    }
}

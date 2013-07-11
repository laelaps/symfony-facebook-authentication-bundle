<?php

namespace Laelaps\Bundle\FacebookAuthentication\Security;

use BadMethodCallException;
use Laelaps\Bundle\Facebook\FacebookAdapterAwareInterface;
use Laelaps\Bundle\Facebook\FacebookAdapterAwareTrait;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Symfony\Component\Security\Http\Firewall\ListenerInterface;

class FacebookFirewallListener implements FacebookAdapterAwareInterface, ListenerInterface
{
    use FacebookAdapterAwareTrait;

    /**
     * @var string
     */
    const SESSION_USER_FACEBOOK_ID = 'user_facebook_id';

    /**
     * @var \Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface
     */
    private $authenticationFailureHandle;

    /**
     * @var \Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface
     */
    private $authenticationManager;

    /**
     * @var \Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface
     */
    private $authenticationSuccessHandle;

    /**
     * @var \Symfony\Component\Security\Core\SecurityContextInterface
     */
    private $securityContext;

    /**
     * @return \Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface
     */
    public function getAuthenticationManager()
    {
        if (!($this->authenticationManager instanceof AuthenticationManagerInterface)) {
            throw new BadMethodCallException('AuthenticationManager is not set.');
        }

        return $this->authenticationManager;
    }

    /**
     * @return \Symfony\Component\Security\Core\SecurityContextInterface
     */
    public function getSecurityContext()
    {
        if (!($this->securityContext instanceof SecurityContextInterface)) {
            throw new BadMethodCallException('SecurityContext is not set.');
        }

        return $this->securityContext;
    }

    /**
     * @param \Symfony\Component\HttpKernel\Event\GetResponseEvent $event
     * @return void
     */
    public function handle(GetResponseEvent $event)
    {
        $userFacebookId = $this->getFacebookAdapter()->getUser();

        if (!$userFacebookId) {
            return;
        }

        $token = new FacebookUserToken($userFacebookId);

        try {
            $authToken = $this->getAuthenticationManager()
                ->authenticate($token)
            ;
        } catch (AuthenticationException $failed) {
            $this->securityContext->setToken(null);

            throw $failed;
        }

        $this->securityContext->setToken($authToken);

        return $authToken;
    }

    /**
     * @param \Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface $authenticationFailureHandler
     */
    public function setAuthenticationFailureHandler(AuthenticationFailureHandlerInterface $authenticationFailureHandler)
    {
        $this->authenticationFailureHandler = $authenticationFailureHandler;
    }

    /**
     * @param \Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface $authenticationManager
     * @return void
     */
    public function setAuthenticationManager(AuthenticationManagerInterface $authenticationManager)
    {
        $this->authenticationManager = $authenticationManager;
    }

    /**
     * @param \Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface $authenticationFailureHandler
     */
    public function setAuthenticationSuccessHandler(AuthenticationSuccessHandlerInterface $authenticationSuccessHandler)
    {
        $this->authenticationSuccessHandler = $authenticationSuccessHandler;
    }

    /**
     * @param \Symfony\Component\Security\Core\SecurityContextInterface $securityContext
     * @return void
     */
    public function setSecurityContext(SecurityContextInterface $securityContext)
    {
        $this->securityContext = $securityContext;
    }
}
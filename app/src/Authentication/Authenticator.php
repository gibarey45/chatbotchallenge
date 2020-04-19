<?php


namespace App\Authentication;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Guard\JWTTokenAuthenticator as BaseAuthenticator;
use Symfony\Component\HttpFoundation\Request;

class Authenticator extends BaseAuthenticator
{
    /**
     * Does the authenticator support the given Request?
     *
     * If this returns false, the authenticator will be skipped.
     *
     * @param Request $request
     * @return bool
     */
    public function supports(Request $request): bool
    {
        return false !== $this->getTokenExtractor()->extract($request);
    }
}
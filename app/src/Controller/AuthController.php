<?php

namespace App\Controller;
use App\Services\AuthService;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AuthController extends AbstractFOSRestController
{
    /**
     * @var AuthService
     */
    private $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }


    /**
     * @Route("/auth/token", name="get_token", methods={"GET","POST"})
     */

    public function getTokenAction()
    {
    }

    /**
     * @Route("/auth/register", name="user_register", methods={"POST"})
     * @param Request $request
     * @return JsonResponse
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function registerNewUser(Request $request): JsonResponse
    {
        $result = $this->authService->register($request);
        if(!is_null($result)){
            return new JsonResponse(
                ["result" => $result->getName()],
                Response::HTTP_OK
            );
        }
        else{
            return new JsonResponse(
                'user register failure',
                Response::HTTP_PRECONDITION_FAILED
            );
        }
    }
}
<?php

namespace App\Controller;

use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Psr\Cache\InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use App\Services\ChatbotService;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

/**
 * Class ChatbotController
 * @package App\Controller
 */
class ChatbotController extends AbstractController
{
    /**
     * @var ChatbotService
     */
    private $chatbotService;

    /**
     * ChatbotController constructor.
     * @param ChatbotService $chatbotService
     */
    public function __construct(ChatbotService $chatbotService)
    {
        $this->chatbotService = $chatbotService;
    }

    /**
     * @Route("/exchange", name="currency_exchange", methods={"GET"})
     * @param Request $request
     * @return JsonResponse
     * @throws InvalidArgumentException
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function currencyExchange(Request $request): JsonResponse
    {
        $result = $this->chatbotService->exchange($request);
        if (!is_null($result)){
            return new JsonResponse(
                ["result" => $result],
                Response::HTTP_OK
            );
        } else{
            return new JsonResponse(
                'currency conversion failure',
                Response::HTTP_CONFLICT
            );
        }
    }

    /**
     * @Route("/deposit", name="deposit_money", methods={"POST"})
     * @param Request $request
     * @return JsonResponse
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws InvalidArgumentException
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function depositMoney(Request $request): JsonResponse
    {
        $result = ($this->chatbotService->deposit($request));
        if(!is_null($result)){
            return new JsonResponse(['User'=>$result->getUserId()->getUsername(),
                'balance'=>$result->getBalance(),
                'transaction'=>$result->getTransaction(),
                'currency'=>$result->getUserId()->getDefaultCurrency()],
                Response::HTTP_OK
            );
        }
        else{
            return new JsonResponse(
                'Problems depositing money',
                Response::HTTP_CONFLICT
            );
        }
    }

    /**
     * @Route("/withdraw", name="withdraw_money", methods={"POST"})
     * @param Request $request
     * @return JsonResponse
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws InvalidArgumentException
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function withdrawMoney(Request $request): JsonResponse
    {
        $result = $this->chatbotService->withdraw($request);
        if(!is_null($result)){
            return new JsonResponse(['User'=>$result->getUserId()->getUsername(),
                'balance'=>$result->getBalance(),
                'transaction'=>$result->getTransaction(),
                'currency'=>$result->getUserId()->getDefaultCurrency()],
                Response::HTTP_OK
            );
        }
        else{
            return new JsonResponse(
                'Problems withdrawing money',
                Response::HTTP_CONFLICT
            );
        }
    }

    /**
     * @Route("/currency/change", name="change_default_currency", methods={"POST"})
     * @param Request $request
     * @return JsonResponse
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws InvalidArgumentException
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function changeDefaultCurrency(Request $request): JsonResponse
    {
        $result = $this->chatbotService->changeCurrency($request);
        if(!is_null($result)){
            return new JsonResponse(
                ["result" => $result],
                Response::HTTP_OK
            );
        }
        else{
            return new JsonResponse(
                'Problems changing default currency, check your balance',
                Response::HTTP_CONFLICT
            );
        }
    }

    /**
     * @Route("/currency/get", name="obtain_default_currency", methods={"GET"})
     * @return JsonResponse
     */
    public function obtainDefaultCurrency(): JsonResponse
    {
        $result = $this->chatbotService->obtainCurrency();
        if(!is_null($result)){
            return new JsonResponse(
                ["result" => $result],
                Response::HTTP_OK
            );
        }
        else{
            return new JsonResponse(
                'Problems with default currency',
                Response::HTTP_CONFLICT
            );
        }
    }

    /**
     * @Route("/balance", name="show_balance", methods={"GET"})
     * @param Request $request
     * @return JsonResponse
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws InvalidArgumentException
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function showBalance(Request $request): JsonResponse
    {
        $result = $this->chatbotService->balance($request);
        if(!is_null($result)){
            return new JsonResponse(
                ["result" => $result],
                Response::HTTP_OK
            );
        }
        else{
            return new JsonResponse(
                'Problems showing balance',
                Response::HTTP_CONFLICT
            );
        }
    }

    /**
     * @Route("/", name="index", methods={"GET"})
     * @return Response
     */
    public function index()
    {
        return  $this->render('index.html.twig');
    }
}

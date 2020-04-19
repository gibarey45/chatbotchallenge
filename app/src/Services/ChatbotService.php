<?php


namespace App\Services;

use App\Entity\UserTransaction;
use App\Repository\UserRepository;
use App\Repository\UserTransactionRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Exception;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Class ChatbotService
 * @package App\Services
 */
class ChatbotService
{
    /**
     * @var ParameterBagInterface
     */
    private $parameterBag;
    /**
     * @var HttpClientInterface
     */
    private $httpClient;
    /**
     * @var AdapterInterface
     */
    private $adapter;

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @var UserTransactionRepository
     */
    private $userTransactionRepository;

    /**
     * @var UserRepository
     */
    private $userReporistory;

    /**
     * ChatbotService constructor.
     * @param ParameterBagInterface $parameterBag
     * @param HttpClientInterface $httpClient
     * @param AdapterInterface $adapter
     * @param TokenStorageInterface $tokenStorage
     * @param UserTransactionRepository $userTransactionRepository
     * @param UserRepository $userReporistory
     */
    public function __construct(ParameterBagInterface $parameterBag, HttpClientInterface $httpClient, AdapterInterface $adapter, TokenStorageInterface $tokenStorage,
                                UserTransactionRepository $userTransactionRepository, UserRepository $userReporistory)
    {
        $this->parameterBag = $parameterBag;
        $this->httpClient = $httpClient;
        $this->adapter = $adapter;
        $this->tokenStorage = $tokenStorage;
        $this->userTransactionRepository = $userTransactionRepository;
        $this->userReporistory = $userReporistory;
    }


    /**
     * @param Request $request
     * @return float
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws InvalidArgumentException
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function exchange(Request $request): ?float
    {
        $result = null;
        $data = $request->query->all();
        $currencyFrom = $data['currencyFrom'];
        $currencyTo = $data['currencyTo'];
        $amount = $data['amount'];
        if (floatval($amount) > 0 ){
            $validFrom = $this->verifyCurrency($currencyFrom);
            $validTo = $this->verifyCurrency($currencyTo);
            if ($validFrom && $validTo){
                $result =$this->convertCurrency($currencyFrom, $currencyTo, $amount);
            }
        }
        return $result;
    }

    /**
     * @param Request $request
     * @return UserTransaction|null
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws InvalidArgumentException
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function deposit(Request $request): ?UserTransaction
    {
        $result = null;
        $data = $request->query->all();
        $currency = $data['currency'];
        $amount = $data['amount'];

        $userToken = $this->tokenStorage->getToken() ? $this->tokenStorage->getToken()->getUser() : null;
        $defaultCurrency = $userToken->getDefaultCurrency();
        $userTransaction = $this->userTransactionRepository->findOneBy(['user_id' => $userToken->getId()]);

        if (floatval($amount) > 0 ){
            $validCurrency = $this->verifyCurrency($currency);
            if ($validCurrency && ($currency !== $defaultCurrency)){
                $amount = $this->convertCurrency($currency, $defaultCurrency, $amount);
            }
            $userTransaction->setBalance($userTransaction->getBalance() + $amount);
            $userTransaction->setTransaction('Deposit');
            $userTransaction->setCurrency(mb_strtoupper($currency));
            $result = $this->userTransactionRepository->update($userTransaction, true);
        }
        return $result;
    }

    /**
     * @param Request $request
     * @return UserTransaction|null
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws InvalidArgumentException
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function withdraw(Request $request): ?UserTransaction
    {
        $result = null;
        $data = $request->query->all();
        $currency = $data['currency'];
        $amount = $data['amount'];

        $userToken = $this->tokenStorage->getToken() ? $this->tokenStorage->getToken()->getUser() : null;
        $defaultCurrency = $userToken->getDefaultCurrency();
        $userTransaction = $this->userTransactionRepository->findOneBy(['user_id' => $userToken->getId()]);

        if (floatval($amount) > 0 && floatval($amount)<= $userTransaction->getBalance()){
            $validCurrency = $this->verifyCurrency($currency);
            if ($validCurrency && ($currency !== $defaultCurrency)){
                $amount = $this->convertCurrency($currency, $defaultCurrency, $amount);
            }
            $userTransaction->setBalance($userTransaction->getBalance() - $amount);
            $userTransaction->setTransaction('Withdraw');
            $userTransaction->setCurrency(mb_strtoupper($currency));
            $result = $this->userTransactionRepository->update($userTransaction, true);

        }
        return $result;
    }

    /**
     * @param Request $request
     * @return string|null
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws InvalidArgumentException
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function changeCurrency(Request $request) : ?string
    {
        $result = null;
        $data = $request->query->all();
        $currency = $data['currency'];
        $validCurrency = $this->verifyCurrency($currency);

        $userToken = $this->tokenStorage->getToken() ? $this->tokenStorage->getToken()->getUser() : null;
        $actualDefaultCurrency = $userToken->getDefaultCurrency();
        $userTransaction = $this->userTransactionRepository->findOneBy(['user_id' => $userToken->getId()]);

        if ($validCurrency && (mb_strtoupper($currency)!== $actualDefaultCurrency)){
            $newBalance = $this->convertCurrency($actualDefaultCurrency, mb_strtoupper($currency), $userTransaction->getBalance());
            $userTransaction->setBalance($newBalance);
            $userTransaction->setTransaction('Change Currency');
            $userTransaction->setCurrency(mb_strtoupper($currency));
            $userToken->setDefaultCurrency(mb_strtoupper($currency));
            $this->userTransactionRepository->update($userTransaction, true);
            $updateUser = $this->userReporistory->update($userToken);
            $result = $updateUser->getDefaultCurrency();
        }
    return $result;
    }

    /**
     * @return string
     */
    public function obtainCurrency(): string
    {
        $userToken = $this->tokenStorage->getToken() ? $this->tokenStorage->getToken()->getUser() : null;
        return $userToken->getDefaultCurrency();
    }

    /**
     * @param Request $request
     * @return float|null
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws InvalidArgumentException
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function balance(Request $request)
    {
        $result = null;
        $data = $request->query->all();
        $currency = $data['currency'];
        $validCurrency = $this->verifyCurrency($currency);

        $userToken = $this->tokenStorage->getToken() ? $this->tokenStorage->getToken()->getUser() : null;
        $actualDefaultCurrency = $userToken->getDefaultCurrency();
        $userTransaction = $this->userTransactionRepository->findOneBy(['user_id' => $userToken->getId()]);

        if ($validCurrency && (mb_strtoupper($currency) === $actualDefaultCurrency))
        {
            $result = $userTransaction->getBalance();
            $userTransaction->setTransaction('Show Balance');
            $this->userTransactionRepository->update($userTransaction, true);
        }
        else if($validCurrency && (mb_strtoupper($currency) !== $actualDefaultCurrency)){
            $result = $this->convertCurrency($actualDefaultCurrency, mb_strtoupper($currency), $userTransaction->getBalance());
            $userTransaction->setTransaction('Show Balance');
            $this->userTransactionRepository->update($userTransaction, true);
        }
        return $result;
    }

    /**
     * @param $currency
     * @return bool
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws InvalidArgumentException
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function verifyCurrency($currency) : bool
    {
        $result = true;
        $currency = mb_strtoupper($currency);
        $request = [
            'headers' => [
                'x-rapidapi-host' => 'fixer-fixer-currency-v1.p.rapidapi.com',
                'x-rapidapi-key' => 'fe8234dda2mshff811bf5d90c3f6p1cf7ffjsn3ca8a0e42da6'
            ]
        ];

        $adaptedCurrency = $this->adapter->getItem($currency);
        if (!$adaptedCurrency->isHit()) {
            try {
                $response = $this->httpClient->request('GET', $this->parameterBag->get('fixerIOSymbols'), $request);
                if (Response::HTTP_OK === $response->getStatusCode()) {
                    $content = $response->toArray();
                    if (in_array(mb_strtoupper($currency), array_keys($content['symbols']))) {
                        $adaptedCurrency->set($currency);
                        $this->adapter->save($adaptedCurrency);
                        return $result;
                    }
                    return false;
                }
            } catch (Exception $e) {
                throw new HttpException(
                    Response::HTTP_CONFLICT,
                    $e->getMessage()
                );
            }
        }
        return true;
    }

    /**
     * @param $currencyFrom
     * @param $currencyTo
     * @param $amount
     * @return float
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function convertCurrency($currencyFrom, $currencyTo, $amount) : float
    {
        $result = null;
        $currencyFrom = mb_strtoupper($currencyFrom);
        $currencyTo = mb_strtoupper($currencyTo);

        $request = [
            'headers' => [
                'x-rapidapi-host' => 'fixer-fixer-currency-v1.p.rapidapi.com',
                'x-rapidapi-key' => 'fe8234dda2mshff811bf5d90c3f6p1cf7ffjsn3ca8a0e42da6'
            ],
            'query' => [
                'from' => $currencyFrom,
                'to' => $currencyTo,
                'amount' => $amount
            ]
        ];

        try {
            $response =  $this->httpClient->request('GET', $this->parameterBag->get('fixerIOConvert'), $request );
            if (Response::HTTP_OK === $response->getStatusCode()) {
                $convertToArray = $response->toArray();
                $result = $convertToArray['result'];
            }
        } catch (Exception $e) {
            throw new HttpException(
                Response::HTTP_CONFLICT,
                $e->getMessage()
            );
        }
        return $result;
    }
}
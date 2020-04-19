<?php


namespace App\Services;


use App\Entity\User;
use App\Entity\UserTransaction;
use App\Repository\UserTransactionRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use App\Repository\UserRepository;

class AuthService
{
    /**
     * @var ValidatorInterface
     */
    private $validator;
    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @var UserPasswordEncoderInterface
     */
    private $passwordEncoder;

    /**
     * @var ParameterBagInterface
     */
    private $parameterBag;

    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * @var UserTransactionRepository
     */
    private $userTransactionRepository;

    /**
     * AuthController constructor.
     * @param ValidatorInterface $validator
     * @param SerializerInterface $serializer
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @param ParameterBagInterface $parameterBag
     * @param UserRepository $userRepository
     * @param UserTransactionRepository $userTransactionRepository
     */
    public function __construct(ValidatorInterface $validator, SerializerInterface $serializer, UserPasswordEncoderInterface $passwordEncoder, ParameterBagInterface $parameterBag, UserRepository $userRepository,
                                UserTransactionRepository $userTransactionRepository)
    {
        $this->validator = $validator;
        $this->serializer = $serializer;
        $this->passwordEncoder = $passwordEncoder;
        $this->parameterBag = $parameterBag;
        $this->userRepository = $userRepository;
        $this->userTransactionRepository = $userTransactionRepository;
    }

    /**
     * @param Request $request
     * @return User|null
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function register(Request $request): ?User
    {
        $userRegistered = null;
        $data = $request->query->all();
        $serializeData = $this->serializer->serialize($data, 'json');
        $deserializeData = $this->serializer->deserialize($serializeData, User::class, 'json');
        $validateErrors = $this->validator->validate($deserializeData);

        if (empty($validateErrors->count())){
            $deserializeData->setPlainPassword($deserializeData->getPassword());
            $deserializeData->setPassword($this->passwordEncoder->encodePassword($deserializeData, $deserializeData->getPassword()));
            $deserializeData->setDefaultCurrency($this->parameterBag->get('defaultCurrency'));
            $existingUsers = $this->userRepository->findOneBy(['user_name' => $deserializeData->getUserName()]);
            if (!$existingUsers){
                $userRegistered = $this->userRepository->insert($deserializeData, true);
                $this->registerFirstTransaction($deserializeData);
            }
        }

        return $userRegistered;
    }

    /**
     * @param User $deserializeData
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function registerFirstTransaction(User $deserializeData): void
    {
        $userTransaction = new UserTransaction();
        $userTransaction->setUserId($deserializeData);
        $userTransaction->setBalance(0);
        $userTransaction->setTransaction('Register');
        $userTransaction->setCurrency($this->parameterBag->get('defaultCurrency'));

        $this->userTransactionRepository->insert($userTransaction, true);
    }

}
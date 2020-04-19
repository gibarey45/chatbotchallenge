<?php


namespace App\DataFixtures;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use App\Entity\User;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AppFixtures extends Fixture
{
    private $passwordEncoder;
    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }

    public function load(ObjectManager $manager)
    {
        $user = new User();

        $user->setName('Chatbot User Name');
        $user->setUsername('ch_user');
        $user->setPlainPassword('chatbotpass');
        $user->setPassword($this->passwordEncoder->encodePassword($user, $user->getPlainPassword()
        ));
        $user->setDefaultCurrency('USD');
        $user->setemail('user@chatbot.com');
        $user->setRoles('ROLE_USER');

        $manager->persist($user);
        $manager->flush();
    }
}
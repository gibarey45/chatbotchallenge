<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserTransactionRepository")
 */
class UserTransaction
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="userTransaction", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $user_id;

    /**
     * @ORM\Column(type="float")
     */
    private $balance;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $transaction;

    /**
     * @ORM\Column(type="string", length=3)
     */
    private $currency;

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return User|null
     */
    public function getUserId(): ?User
    {
        return $this->user_id;
    }

    /**
     * @param User|null $user_id
     * @return UserTransaction
     */
    public function setUserId(?User $user_id): self
    {
        $this->user_id = $user_id;

        return $this;
    }

    /**
     * @return float|null
     */
    public function getBalance(): ?float
    {
        return $this->balance;
    }

    /**
     * @param float $balance
     * @return UserTransaction
     */
    public function setBalance(float $balance): self
    {
        $this->balance = $balance;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getTransaction(): ?string
    {
        return $this->transaction;
    }

    /**
     * @param string $transaction
     * @return UserTransaction
     */
    public function setTransaction(string $transaction): self
    {
        $this->transaction = $transaction;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getCurrency(): ?string
    {
        return $this->currency;
    }

    /**
     * @param string $currency
     * @return UserTransaction
     */
    public function setCurrency(string $currency): self
    {
        $this->currency = $currency;

        return $this;
    }
}

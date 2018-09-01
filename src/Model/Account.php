<?php

namespace Bunq\DoGood\Model;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class Account
 * @package Bunq\DoGood\Model
 * @ORM\Entity
 */
class Account
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", unique=true, length=255)
     */
    private $username;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $password_hash;

    /**
     * @ORM\Column(type="json", nullable=true)
     */
    private $bunq_data = '';

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $api_token;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id): void
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @param mixed $username
     */
    public function setUsername($username): void
    {
        $this->username = $username;
    }

    /**
     * @return mixed
     */
    public function getPasswordHash()
    {
        return $this->password_hash;
    }

    /**
     * @param mixed $password_hash
     */
    public function setPasswordHash($password_hash): void
    {
        $this->password_hash = $password_hash;
    }

    /**
     * @return mixed
     */
    public function getBunqData()
    {
        return $this->bunq_data;
    }

    /**
     * @return string
     */
    public function getBunqDataString()
    {
        return json_encode($this->bunq_data);
    }

    /**
     * @param mixed $bunq_data
     */
    public function setBunqData(array $bunq_data): void
    {
        $this->bunq_data = $bunq_data;
    }

    /**
     * @return string
     */
    public function getApiToken()
    {
        return $this->api_token;
    }

    /**
     * @param mixed $bunq_data
     */
    public function setApiToken(string $api_token): void
    {
        $this->api_token = $api_token;
    }
}

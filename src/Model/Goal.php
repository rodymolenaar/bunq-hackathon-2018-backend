<?php

namespace Bunq\DoGood\Model;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class Goals
 * @package Bunq\DoGood\Model
 * @ORM\Entity
 */
class Goal
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /** @ORM\Column(type="string", columnDefinition="ENUM('LESS', 'MORE', 'EQUAL')") */
    private $condition;

    /** @ORM\Column(type="integer") */
    private $amount;

    /** @ORM\Column(type="integer") */
    private $transactionId;

    /** @ORM\ManyToOne(targetEntity="Bunq\DoGood\Model\Account") */
    private $account;

    /** @ORM\Column(type="string", columnDefinition="ENUM('WEEK', 'MONTH', 'YEAR')") */
    private $period;

}

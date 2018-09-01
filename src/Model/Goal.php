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
     * Possible operator values
     */
    const OPERATOR_LESS = 'LESS';
    const OPERATOR_MORE = 'MORE';
    const OPERATOR_EQUAL = 'EQUAL';

    /**
     * Possible period values
     */
    const PERIOD_WEEK = 'WEEK';
    const PERIOD_MONTH = 'MONTH';
    const PERIOD_YEAR = 'YEAR';

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /** @ORM\Column(type="string") */
    private $operator;

    /** @ORM\Column(type="integer") */
    private $amount;

    /** @ORM\Column(type="integer") */
    private $transactionId;

    /** @ORM\ManyToOne(targetEntity="Bunq\DoGood\Model\Account") */
    private $account;

    /** @ORM\Column(type="string") */
    private $period;

    /**
     * List of possible operators
     *
     * @return array
     */
    public static function listOperators()
    {
        return [
            self::OPERATOR_LESS,
            self::OPERATOR_MORE,
            self::OPERATOR_EQUAL
        ];
    }

    /**
     * Check if operator value is valid
     *
     * @param string $operator
     * @return bool
     */
    public static function isValidOperator(string $operator)
    {
        if (in_array($operator, self::listOperators())) {
            return true;
        }

        return false;
    }

    /**
     * List of possible periods
     *
     * @return array
     */
    public static function listPeriods()
    {
        return [
            self::PERIOD_WEEK,
            self::PERIOD_MONTH,
            self::PERIOD_YEAR
        ];
    }

    /**
     * Check if period value is valid
     *
     * @param string $period
     * @return bool
     */
    public static function isValidPeriod(string $period)
    {
        if (in_array($period, self::listPeriods())) {
            return true;
        }

        return false;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getOperator()
    {
        return $this->operator;
    }

    /**
     * @param string $operator
     * @throws \Exception
     */
    public function setOperator($operator): void
    {
        if (!self::isValidOperator($operator)) {
            throw new \Exception("Invalid value, options: " . implode(',', self::listOperators()));
        }

        $this->operator = $operator;
    }

    /**
     * @return mixed
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param mixed $amount
     */
    public function setAmount($amount): void
    {
        $this->amount = $amount;
    }

    /**
     * @return mixed
     */
    public function getTransactionId()
    {
        return $this->transactionId;
    }

    /**
     * @param mixed $transactionId
     */
    public function setTransactionId($transactionId): void
    {
        $this->transactionId = $transactionId;
    }

    /**
     * @return mixed
     */
    public function getAccount()
    {
        return $this->account;
    }

    /**
     * @param mixed $account
     */
    public function setAccount($account): void
    {
        $this->account = $account;
    }

    /**
     * @return mixed
     */
    public function getPeriod()
    {
        return $this->period;
    }

    /**
     * @param mixed $period
     * @throws \Exception
     */
    public function setPeriod($period): void
    {
        if (!self::isValidPeriod($period)) {
            throw new \Exception("Invalid value, options: " . implode(',', self::listPeriods()));
        }

        $this->period = $period;
    }
}

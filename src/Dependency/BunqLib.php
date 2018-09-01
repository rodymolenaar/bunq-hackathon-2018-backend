<?php

namespace Bunq\DoGood\Dependency;

use bunq\Context\ApiContext;
use bunq\Context\BunqContext;
use bunq\Http\Pagination;
use bunq\Model\Generated\Endpoint\MonetaryAccountBank;
use bunq\Model\Generated\Endpoint\Payment;
use bunq\Util\BunqEnumApiEnvironmentType;

/**
 * Class BunqLib
 * @package Bunq\DoGood\Dependency
 */
class BunqLib
{

    /**
     * Create an Bunq API context, used to store auth info
     *
     * @param string $apiKey
     *
     * @return ApiContext
     */
    public function createContextProduction($apiKey = '')
    {
        $environmentType   = BunqEnumApiEnvironmentType::PRODUCTION();
        $deviceDescription = 'Do Good';

        return ApiContext::create(
            $environmentType,
            $apiKey,
            $deviceDescription,
            []
        );
    }

    /**
     * Parse json data to Api context
     *
     * @param string $json
     * @return ApiContext
     */
    public function loadContextFromJson(string $json)
    {
        $apiContext = ApiContext::fromJson($json);
        BunqContext::loadApiContext($apiContext);

        return $apiContext;
    }

    public function getCardPaymentLocations()
    {
        $allMonetaryAccount   = MonetaryAccountBank::listing()->getValue();
        $firstMonetaryAccount = $allMonetaryAccount[0];

        $pagination = new Pagination();
        $pagination->setCount(200);
        $payments = Payment::listing(
            $firstMonetaryAccount->getId(),
            $pagination->getUrlParamsCountOnly()
        )->getValue();

        $cardPayments = array_filter($payments, function ($payment) {
            return $payment->getType() == 'MASTERCARD';
        });

        $formattedCardPaymentLocations = array_map(function ($cardPayment) {
            $counterpartyAlias = $cardPayment->getCounterpartyAlias();
            $avatar            = $counterpartyAlias->getAvatar();

            return [
                'id'          => $cardPayment->getId(),
                'name'        => $counterpartyAlias->getDisplayName(),
                'description' => $cardPayment->getDescription(),
            ];
        }, $cardPayments);

        $output = [];
        foreach ($formattedCardPaymentLocations as $formattedCardPaymentLocation) {
            if (!$this->cardPaymentLocationInArray($output, $formattedCardPaymentLocation)) {
                $output[] = $formattedCardPaymentLocation;
            }
        }

        return $output;
    }

    private function cardPaymentLocationInArray($array, $cardPayment)
    {
        return count(array_filter($array, function ($elm) use ($cardPayment) {
            return $this->isSameCardPaymentLocation($cardPayment, $elm);
        })) > 0;
    }

    private function isSameCardPaymentLocation($a, $b)
    {
        return $a['name'] == $b['name'] && $a['description'] == $b['description'];
    }
}

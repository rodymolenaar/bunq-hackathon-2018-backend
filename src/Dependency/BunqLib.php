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
final class BunqLib
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

    /**
     * @param string $url
     */
    public function setBankAccountTrigger(string $url)
    {
        $accountBankId = 198594;
        $accountBank = MonetaryAccountBank::get($accountBankId)->getValue();

//        $notificationFilters = $accountBank->getNotificationFilters();

        $notificationFilters = [
            'notification_delivery_method' => "URL",
            'notification_target' => $url,
            'category' => "PAYMENT"
        ];

        // to do: update $notificationFilters

        MonetaryAccountBank::update(
            $accountBankId,
            $accountBank->getDescription(),
            $accountBank->getDailyLimit(),
            $accountBank->getAvatar()->getUuid(),
            $accountBank->getStatus(),
            $accountBank->getSubStatus(),
            $accountBank->getReason(),
            $accountBank->getReasonDescription(),
            $notificationFilters,
            $accountBank->getSetting(),
            []
        );
    }

    /**
     * Returns an list of payment locations
     *
     * @return array
     */
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
                'transaction_id'    => $cardPayment->getId(),
                'name'              => $counterpartyAlias->getDisplayName(),
                'description'       => $cardPayment->getDescription(),
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

    /**
     * Compare the location of payments
     *
     * @param array $array
     * @param $cardPayment
     * @return bool
     */
    private function cardPaymentLocationInArray($array, $cardPayment)
    {
        $compareMethod = function($a, $b) {
            return $a['name'] == $b['name'] && $a['description'] == $b['description'];
        };

        return count(array_filter($array, function ($elm) use ($cardPayment, $compareMethod) {
            return $compareMethod($cardPayment, $elm);
        })) > 0;
    }
}

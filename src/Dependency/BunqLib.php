<?php

namespace Bunq\DoGood\Dependency;

use bunq\Context\ApiContext;
use bunq\Context\BunqContext;
use bunq\Http\Pagination;
use bunq\Model\Generated\Endpoint\MonetaryAccountBank;
use bunq\Model\Generated\Endpoint\Payment;
use bunq\Util\BunqEnumApiEnvironmentType;
use bunq\Model\Generated\Endpoint\AttachmentPublicContent;

/**
 * Class BunqLib
 * @package Bunq\DoGood\Dependency
 */
final class BunqLib
{

    const accountBankId = 198594;

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

    public function setBankAccountTrigger()
    {
        $accountBank = MonetaryAccountBank::get(self::accountBankId)->getValue();

        // $notificationFilters = $accountBank->getNotificationFilters();
        // to do check for existing records

        $notificationFilters = [
            'notification_filters' =>
                [
                    'notification_delivery_method' => "URL",
                    'notification_target' => "https://bunq-api.testservers.nl/bunq/trigger",
                    'category' => "PAYMENT"
                ],
                [
                    'notification_delivery_method' => "URL",
                    'notification_target' => "https://requestbin.fullcontact.com/1giacen1",
                    'category' => "CARD_TRANSACTION_SUCCESSFUL"
                ]
        ];

        MonetaryAccountBank::update(
            self::accountBankId,
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

        $accountBank = MonetaryAccountBank::get(self::accountBankId)->getValue();
        return $accountBank->getNotificationFilters();
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

            return [
                'id'    => $this->paymentToMerchantId($cardPayment),
                'name'              => $counterpartyAlias->getDisplayName(),
                'description'       => $cardPayment->getDescription(),
                'image_url' => 'https://bunq-api.testservers.nl/merchants/' . $cardPayment->getId() . '/image'
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

    /**
     * Fetch a PNG image for a transaction
     *
     * @param int $transactionId
     * @return null|string
     */
    public function getMerchantImageForTransaction($transactionId) {
        try {
            $payment = $this->getPayment($transactionId);
            $counterpartyAlias = $payment->getCounterpartyAlias();
            $avatar = $counterpartyAlias->getAvatar();

            if ($avatar) {
                $images = $avatar->getImage();
                if (count($images)) {
                    return AttachmentPublicContent::listing($images[0]->getAttachmentPublicUuid())->getValue();
                }
            }

            return null;
        } catch (\Exception $e) {
            return null;
        }
    }

    // Returns the payment for given ID
    public function getPayment($id) {
        return Payment::get($id)->getValue();
    }

    // Returns unique string for merchant name and description combination
    public function paymentToMerchantId($payment): String {
        return sha1(trim($payment->getCounterpartyAlias()->getDisplayName()) . trim($payment->getDescription()));
    }
}

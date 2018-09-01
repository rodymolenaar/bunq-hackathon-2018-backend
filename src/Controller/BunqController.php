<?php

namespace Bunq\DoGood\Controller;

use Bunq\DoGood\Dependency\BunqLib;
use bunq\Model\Generated\Endpoint\Payment;
use bunq\Model\Generated\Object\Amount;
use bunq\Model\Generated\Object\Pointer;
use Slim\Http\Request;
use Slim\Http\Response;

use Bunq\DoGood\Model\Transaction;

/**
 * Class BunqController
 * @package Bunq\DoGood\controller
 */
final class BunqController extends BaseController
{
    /**
     * Notification Filters listener
     *
     * @param Request $request
     * @param Response $response
     * @param array $args
     *
     * @return Response
     */
    public function trigger(Request $request, Response $response, array $args) {
        $data = $request->getParsedBody();

        $this->makePaymentToCharity();

//        $payment = $data['NotificationUrl']['object']['Payment'];
//        $this->logTransaction($payment);
    }


    private function makePaymentToCharity()
    {
        $pickRandomCharity = function(array $ids) {
            $index = rand(0, count($ids) - 1);
            return $ids[$index];
        };

        $entityManager = $this->get('entityManager');
        $account = $entityManager->getRepository('Bunq\DoGood\Model\Account')->findOneBy([
            'email' => 'rickvdl@me.com'
        ]);

        $charityIds = $account->getCharityIds();

        $charity = $entityManager->getRepository('Bunq\DoGood\Model\Charity')->findOneBy([
            'id' => $pickRandomCharity($charityIds)
        ]);

        // make payment
        $bunq = new BunqLib();
        $bunq->loadContextFromJson($account->getBunqDataString());

        Payment::create(
            new Amount(0.01, 'EUR'),
            new Pointer('IBAN', $charity->getIban(), $charity->getName()),
            "Do good",
            BunqLib::accountBankId
        )->getValue();
    }

    /**
     * Create an transaction record in the database
     *
     * @param array $payment
     */
    private function logTransaction(array $payment)
    {
        $merchantIdFromData = function($paymentData) {
            return sha1(trim($paymentData['counterparty_alias']['display_name']) . trim($paymentData['description']));
        };

        $parseAmount = function($amount) {
            return (int) str_replace('.', '', str_replace('-', '', $amount));
        };

        $transaction = new Transaction();
        $transaction->setTransactionId((int) $payment['id']);
        $transaction->setMerchantId($merchantIdFromData($payment));
        $transaction->setAmount($parseAmount($payment['amount']['value']));
        $transaction->setCreatedAt(new \DateTime);

        $entityManager = $this->get('entityManager');
        $entityManager->persist($transaction);
        $entityManager->flush();
    }
}
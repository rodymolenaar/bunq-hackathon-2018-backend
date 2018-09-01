<?php

namespace Bunq\DoGood\Controller;

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

        $payment = $data['NotificationUrl']['object']['Payment'];

        $entityManager = $this->get('entityManager');
        $transaction = new Transaction();
        $transaction->setTransactionId((int) $payment['id']);
        $transaction->setMerchantId($this->merchantIdFromData($payment));
        $transaction->setAmount($this->parseAmount($payment['amount']['value']));
        $transaction->setCreatedAt(new \DateTime);

        $entityManager->persist($transaction);
        $entityManager->flush();
    }

    private function merchantIdFromData($paymentData) {
        return sha1(trim($paymentData['counterparty_alias']['display_name']) . trim($paymentData['description']));
    }

    private function parseAmount($amount) {
        return (int) str_replace('.', '', str_replace('-', '', $amount));
    }
}
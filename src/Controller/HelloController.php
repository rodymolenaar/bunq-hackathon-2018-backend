<?php

namespace Bunq\DoGood\Controller;

use bunq\Http\Pagination;
use bunq\Model\Generated\Endpoint\MonetaryAccountBank;
use bunq\Model\Generated\Endpoint\Payment;

use Slim\Http\Request;
use Slim\Http\Response;

/**
 * Class HelloController
 * @package Bunq\DoGood\Controller
 */
final class HelloController extends BaseController
{
    /**
     * Hello world?!
     *
     * @param Request $request
     * @param Response $response
     * @param array $args
     *
     * @return Response
     */
    public function world(Request $request, Response $response, array $args) {
        return $this->successJsonResponseMessage($response, "Hello World");
    }

    /**
     * MonetaryAccountBank
     *
     * @param Request $request
     * @param Response $response
     * @param array $args
     *
     * @return Response
     */
    public function bank(Request $request, Response $response, array $args) {
        $postData = $request->getParsedBody();

        if (!isset($postData['email'])) {
            return $this->errorJsonResponse($response, "Field 'email' missing");
        }

        $entityManager = $this->get('entityManager');
        $account = $entityManager->getRepository('Bunq\DoGood\Model\Account')->findOneBy(['email' => $postData['email']]);

        if ($account === null) {
            return $this->errorJsonResponse($response, "Account not found, check email");
        }

        if($account->getBunqDataString() == '""') {
            return $this->errorJsonResponse($response, "API context missing");
        }

        $bunqLib = $this->get('bunqLib');
        $bunqLib->loadContextFromJson($account->getBunqDataString());

        $allMonetaryAccount = MonetaryAccountBank::listing()->getValue();
        $betaal = $allMonetaryAccount[0];

        $pagination = new Pagination();
        $pagination->setCount(2);
        $transactions = Payment::listing(
            $betaal->getId(),
            $pagination->getUrlParamsCountOnly()
        )->getValue();

       return $this->successJsonResponsePayload($response, $transactions);
    }
}
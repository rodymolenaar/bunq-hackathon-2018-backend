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

        $bunqLib = $this->get('bunqLib');
        $bunqLib->loadContextFromJson($this->get('user')->getBunqDataString());

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
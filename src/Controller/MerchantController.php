<?php

namespace Bunq\DoGood\Controller;

use Slim\Http\Request;
use Slim\Http\Response;

/**
 * Class BunqController
 * @package Bunq\DoGood\controller
 */
final class MerchantController extends BaseController
{

  /**
   * @param Request $request
   * @param Response $response
   * @param array $args
   * @return Response
   */
  public function getMerchants(Request $request, Response $response, array $args) {
      $bunq = $this->get('bunqLib');

      try {

        // Fetch the merchants from bunq
        $merchants = $bunq->getCardPaymentLocations();

        return $this->successJsonResponsePayload($response, $merchants);
      } catch(BadRequestException $e) {
        return $this->errorJsonResponse($response, "An error occured while fetching the merchants");
      }
  }
}

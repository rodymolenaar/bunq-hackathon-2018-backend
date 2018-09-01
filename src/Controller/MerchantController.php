<?php

namespace Bunq\DoGood\Controller;

use bunq\Exception\BadRequestException;

use Slim\Http\Request;
use Slim\Http\Response;

use bunq\Model\Generated\Endpoint\Payment;

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
      } catch(\Exception $e) {
        return $this->errorJsonResponse($response, "An error occured while fetching the merchants");
      }
  }

  /**
   * @param Request $request
   * @param Response $response
   * @return Response
   */
  public function getMerchantImage(Request $request, Response $response, array $args) {
    try {
        $bunq = $this->get('bunqLib');
        $imageData = $bunq->getMerchantImageForTransaction($args['transaction_id']);
        
        $response->write($imageData);
        $response = $response->withHeader('Content-Type', 'image/png');
        
        return $response;
    } catch (\Exception $e) {}
  }
}

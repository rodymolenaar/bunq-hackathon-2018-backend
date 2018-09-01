<?php

namespace Bunq\DoGood\Controller;

use Slim\Http\Request;
use Slim\Http\Response;

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
        $bunq = $this->get('bunqLib');

        return $this->successJsonResponsePayload($response, []);
    }
}
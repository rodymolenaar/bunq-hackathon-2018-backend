<?php

namespace Bunq\DoGood\controller;

use Slim\Http\Request;
use Slim\Http\Response;

/**
 * Class BunqController
 * @package Bunq\DoGood\controller
 */
class BunqController extends BaseController
{
    /**
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return Response
     */
    public function currentUser(Request $request, Response $response, array $args) {
        $bunq = $this->get('bunqLib');
        $user = $bunq->getCurrentUser();

        return $this->successJsonResponse($response, [
            'id' => $user->getId(),
            'name' => $user->getDisplayName()
        ]);
    }
}
<?php

namespace Bunq\DoGood\Controller;

use bunq\Model\Generated\Endpoint\User;
use bunq\Model\Generated\Endpoint\UserPerson;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * Class BunqController
 * @package Bunq\DoGood\controller
 */
class BunqController extends BaseController
{
    /**
     *
     *
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return Response
     */
    public function currentUser(Request $request, Response $response, array $args) {
        $bunq = $this->get('bunqLib');
        $user = $bunq->getCurrentUser();

        return $this->successJsonResponsePayload($response, [
            'id' => $user->getId(),
            'name' => $user->getDisplayName()
        ]);
    }
}
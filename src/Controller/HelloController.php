<?php

namespace Bunq\DoGood\Controller;

use Slim\Http\Request;
use Slim\Http\Response;

use Doctrine\ORM\EntityManager;

/**
 * Class HelloController
 * @package Bunq\DoGood\Controller
 */
class HelloController extends BaseController
{
    /**
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return Response
     */
    public function world(Request $request, Response $response, array $args) {
        $entityManger = $this->get('entityManager');

        return $response->withJson(['status' => 'success', 'message' => "Hello World"]);
    }
}
<?php

namespace Bunq\DoGood\Controller;

use Bunq\DoGood\Model\Account;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * Class HelloController
 * @package Bunq\DoGood\Controller
 */
class HelloController extends BaseController
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
        return $response->withJson(['status' => 'success', 'message' => "Hello World"]);
    }
}
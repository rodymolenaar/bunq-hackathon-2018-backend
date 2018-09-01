<?php

namespace Bunq\DoGood\Controller;

use Slim\Http\Response;
use Slim\Http\Request;

/**
 * Class CharityController
 * @package Bunq\DoGood\Controller
 */
final class CharityController extends BaseController
{
    /**
     * Full list of available
     *
     * @param Request $request
     * @param Response $response
     * @param array $args
     *
     * @return Response
     */
    public function getList(Request $request, Response $response, array $args) {
        $path = realpath(__DIR__ . "/../../var/charities.json");

        if ($path === false) {
            return $this->errorJsonResponse($response, 'Charity data missing');
        }

        $charities = json_decode(file_get_contents($path));

        return $this->successJsonResponsePayload($response, $charities->categories);
    }
}
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
        $headers = $request->getHeaders();
        $body = $request->getAttributes();
        $data = $request->getParsedBody();

        $json = json_encode([
            'headers' => $headers,
            'body' => $body,
            'data' => $data
        ]) . PHP_EOL . PHP_EOL;

        $path = realpath(__DIR__ . "/../../var/");
        $file = $path . '/trigger.txt';

        file_put_contents($path . '/trigger.txt', $json);
        return;
    }
}
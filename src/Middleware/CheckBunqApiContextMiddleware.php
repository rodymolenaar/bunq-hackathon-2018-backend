<?php

namespace Bunq\DoGood\Middleware;

class CheckBunqApiContextMiddleware {

    protected $container;

    public function __construct($container) {
        $this->container = $container;
    }

    public function __invoke($request, $response, $next) {
        $bunqApiContext = $this->container->get('user')->getBunqDataString();

        if (empty($apiContext) || $apiContext == '""') {
            return $response->withJson(['status' => 'error', 'message' => 'bunq api key not setup'], 412);
        }
        
        return $next($request, $response);
    }
}
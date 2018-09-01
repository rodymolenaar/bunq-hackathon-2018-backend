<?php

namespace Bunq\DoGood\Middleware;

class CheckBunqApiContextMiddleware {

    protected $container;

    public function __construct($container) {
        $this->container = $container;
    }

    public function __invoke($request, $response, $next) {
        if (!isset($this->container['user'])) {
            return $next($request, $response);
        }

        $bunqApiContext = $this->container->get('user')->getBunqDataString();

        $skipRoutes = [
            '/accounts',
            '/account',
            '/token',
        ];

        if (!in_array($request->getUri()->getPath(), $skipRoutes)) {
            if (empty($bunqApiContext) || $bunqApiContext == '""') {
                return $response->withJson(['status' => 'error', 'message' => 'bunq api key not setup'], 412);
            }
        }
        
        return $next($request, $response);
    }
}
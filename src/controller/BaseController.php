<?php

namespace Bunq\DoGood\Controller;

use Psr\Container\ContainerInterface;

/**
 * Class BaseController
 * @package Bunq\DoGood\controller
 */
abstract class BaseController
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    // constructor receives container instance
    public function __construct(ContainerInterface $container) {
        $this->container = $container;
    }

    /**
     * Get service
     *
     * @param string $id Identifier of the entry to look for.
     * @return mixed
     */
    protected function get($id)
    {
        return $this->container->get($id);
    }
}
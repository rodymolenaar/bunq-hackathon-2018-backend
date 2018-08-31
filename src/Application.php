<?php

namespace Bunq\DoGood;

use Slim\App;
use Slim\Exception\MethodNotAllowedException;
use Slim\Exception\NotFoundException;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\Yaml\Yaml;

/**
 * Class Application
 * @package Bunq\DoGood
 */
class Application
{
    /**
     * @var App
     */
    private $instance;

    /**
     * @var string
     */
    private $configPath = __DIR__ . '/../config';

    /**
     * Application constructor.
     *
     * @param array $settings
     */
    public function __construct($settings = [])
    {
        $this->instance = new \Slim\App($settings);

        $this->loadControllers();
    }

    /**
     * Load controllers and register in slim instance
     *
     * @throws \Exception
     */
    private function loadControllers()
    {
        $config = $this->loadYamlData('controller.yaml');

        if (!isset($config['controllers'])) {
            throw new \Exception('Invalid controller config');
        }

        array_map(function ($controller) {
            $callable = 'Bunq\DoGood\Controller\\' . $controller['controller'] . ":". $controller['method'];
            $this->instance->get($controller['path'], $callable);
        }, $config['controllers']);
    }

    /**
     * Load an specific yaml file from the config dir
     *
     * @param string $name
     * @return array
     */
    private function loadYamlData($name)
    {
        $fileLocator = new FileLocator([$this->configPath]);

        $config = $fileLocator->locate($name, false, false);
        return Yaml::parseFile($config[0]);
    }

    /**
     * Light it up !
     */
    public function run()
    {
        try {
            $this->instance->run();
        } catch (MethodNotAllowedException $e) {
            dump($e);
        } catch (NotFoundException $e) {
            dump($e);
        } catch (\Exception $e) {
            dump($e);
        }
    }
}
<?php

namespace Bunq\DoGood;

use Bunq\DoGood\Dependency\BunqLib;
use bunq\Util\BunqEnumApiEnvironmentType;

use Slim\App;
use Slim\Container;
use Slim\Exception\MethodNotAllowedException;
use Slim\Exception\NotFoundException;
use Slim\Middleware\TokenAuthentication;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\Yaml\Yaml;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Cache\FilesystemCache;

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

        $this->loadDependencies();
        $this->loadControllers();
        $this->addMiddlewares();
    }

    /**
     *
     * @throws \Exception
     */
    private function loadDependencies()
    {
        $container = $this->instance->getContainer();

        // load dependencies config
        $config = $this->loadYamlData('dependencies.yaml');

        if (!isset($config['dependencies'])) {
            throw new \Exception('Invalid dependencies config');
        }

        // Doctrine 2 Entity Manager
        $container['entityManager'] = function (Container $container) use ($config): EntityManager {
            $metaDataDirs = array_map(function($dir) {
                return __DIR__ . "/../" . $dir;
            }, $config['dependencies']['doctrine']['metadata_dirs']);


            $doctrineConfig = Setup::createAnnotationMetadataConfiguration(
                $metaDataDirs,
                $config['dependencies']['doctrine']['dev_mode']
            );

            $doctrineConfig->setMetadataDriverImpl(
                new AnnotationDriver(
                    new AnnotationReader,
                    $config['dependencies']['doctrine']['metadata_dirs']
                )
            );

            $doctrineConfig->setMetadataCacheImpl(
                new FilesystemCache(
                     __DIR__ . "/../" . $config['dependencies']['doctrine']['cache_dir']
                )
            );

            return EntityManager::create(
                $config['dependencies']['doctrine']['connection'],
                $doctrineConfig
            );
        };

        // Bunq library
        $container['bunqLib'] = function (Container $container) use ($config): BunqLib {
            return new BunqLib(BunqEnumApiEnvironmentType::SANDBOX());
        };
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
            $callable = 'Bunq\DoGood\Controller\\' . $controller['controller'];
            $this->instance->map($controller['method'], $controller['path'], $callable);
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
     * Adds the required middlewares to the slim instance
     * @return  void
     */
    private function addMiddlewares() {

        $container = $this->getContainer();

        $authenticator = function($request, TokenAuthentication $tokenAuth) use ($container) {

            // Workaround: can't whitelist an endpoint based on HTTP method, so whitelisting POST /accounts would also whitelist GET /accounts and therefore bypassing the token check.
            if ($request->getUri()->getPath() == '/accounts' && $request->getMethod() == 'POST') {
                return true;
            }

            // Search for token on header, parameter, cookie or attribute
            $token = $tokenAuth->findToken($request);

            // Find account for token
            $entityManager = $container->get('entityManager');
            $account = $entityManager->getRepository('Bunq\DoGood\Model\Account')->findOneBy(['api_token' => $token]);

            // dirty hack
            $container['user'] = $account;

            if (!$account) {
                $tokenAuth->setResponseMessage('Invalid token');
            }

            return $account != null;
        };

        $error = function($request, $response, TokenAuthentication $tokenAuth) {
            return $response->withJson(['status' => 'error', 'message' => $tokenAuth->getResponseMessage()], 401);
        };

        // Add token middleware with params
        $this->instance->add(new TokenAuthentication([
            'path' => '/',
            'authenticator' => $authenticator,
            'header' => 'Authorization',
            'regex' => '/Bearer\s+(.*)$/i',
            'error' => $error,
            'passthrough' => ['/token'],
            'secure' => false // behind cloudflare
        ]));
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

    public function getContainer()
    {
        return $this->instance->getContainer();
    }

}
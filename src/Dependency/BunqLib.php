<?php

namespace Bunq\DoGood\Dependency;

use bunq\Context\ApiContext;
use bunq\Context\BunqContext;
use bunq\Util\BunqEnumApiEnvironmentType;
/**
 * Class BunqLib
 * @package Bunq\DoGood\Dependency
 */
class BunqLib
{

    public function createContextProduction($apiKey = '')
    {
        $environmentType = BunqEnumApiEnvironmentType::PRODUCTION();
        $deviceDescription = 'Do Good';

        return ApiContext::create(
            $environmentType,
            $apiKey,
            $deviceDescription,
            []
        );
    }

    public function loadContextFromJson(string $json)
    {
        $apiContext = ApiContext::fromJson($json);
        BunqContext::loadApiContext($apiContext);

        return $apiContext;
    }
}

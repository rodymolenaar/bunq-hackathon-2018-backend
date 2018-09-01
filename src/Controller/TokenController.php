<?php

namespace Bunq\DoGood\Controller;

use Slim\Http\Response;
use Slim\Http\Request;

/**
 * Class TokenController
 * @package Bunq\DoGood\Controller
 */
class TokenController extends BaseController
{
    /**
     * Request a token
     *
     * @param Request $request
     * @param Response $response
     * @param array $args
     *
     * @return Response
     */
    public function getToken(Request $request, Response $response, array $args) {
        $postData = $request->getParsedBody();

        if (!isset($postData['username'])) {
            return $this->errorJsonResponse($response, "Field 'username' missing");
        }

        if (!isset($postData['password'])) {
            return $this->errorJsonResponse($response, "Field 'password' missing");
        }

        $entityManager = $this->get('entityManager');
        $account = $entityManager->getRepository('Bunq\DoGood\Model\Account')->findOneBy(['username' => $postData['username']]);

        if ($account === null) {
            return $this->errorJsonResponse($response, "Account not found", 401);
        }

        if (!password_verify($postData['password'], $account->getPasswordHash())) {
          return $this->errorJsonResponse($response, "Password incorrect", 401);
        }

        // Best effort unique token
        $token = sha1(uniqid('php_', true));

        // Update account with new token
        $account->setApiToken($token);
        $entityManager->persist($account);
        $entityManager->flush();

        return $this->successJsonResponsePayload($response, ['token' => $token]);
    }
}
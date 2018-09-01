<?php

namespace Bunq\DoGood\Controller;

use Slim\Http\Request;
use Slim\Http\Response;

use Bunq\DoGood\Model\Account;

/**
 * Class AccountController
 * @package Bunq\DoGood\Controller
 */
class AccountController extends BaseController
{
    /**
     * POST: Create Account
     *
     * @param Request $request
     * @param Response $response
     * @param array $args
     *
     * @return Response
     */
    public function createAccount(Request $request, Response $response, array $args) {
        $entityManager = $this->get('entityManager');

        $postData = $request->getParsedBody();

        if (!isset($postData['username'])) {
            return $this->errorJsonResponse($response, "Field 'username' missing");
        }

        if (!isset($postData['password'])) {
            return $this->errorJsonResponse($response, "Field 'password' missing");
        }

        // create new account
        $account = new Account();
        $account->setUsername($postData['username']);
        $account->setPasswordHash($postData['password']);
        $account->setBunqData([]);

        $entityManager->persist($account);
        $entityManager->flush();

        return $this->successJsonResponse($response, []);
    }

    /**
     * PATCH: Update Account
     *
     * @param Request $request
     * @param Response $response
     * @param array $args
     *
     * @return Response
     */
    public function updateAccount(Request $request, Response $response, array $args) {
        $entityManager = $this->get('entityManager');

        $postData = $request->getParsedBody();

        if (!isset($postData['username'])) {
            return $this->errorJsonResponse($response, "Field 'username' missing");
        }

        if (!isset($postData['api_key'])) {
            return $this->errorJsonResponse($response, "Field 'api_key' missing");
        }

        $account = $entityManager->getRepository('Bunq\DoGood\Model\Account')->findOneBy(['username' => $postData['username']]);

        if ($account === null) {
            return $this->errorJsonResponse($response, "Account not found, check username");
        }

        // TO DO bunq lib afmaken
        $apiKey = $postData['api_key'];
        $account->setBunqData([]);

        $entityManager->merge($account);
        $entityManager->flush();

        return $this->successJsonResponse($response, []);
    }
}
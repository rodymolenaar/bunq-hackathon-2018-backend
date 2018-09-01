<?php

namespace Bunq\DoGood\Controller;

use Slim\Http\Request;
use Slim\Http\Response;

use Bunq\DoGood\Model\Account;
use bunq\Exception\BadRequestException;

/**
 * Class AccountController
 * @package Bunq\DoGood\Controller
 */
final class AccountController extends BaseController
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
        // ensure username and api_key exist
        $postData = $request->getParsedBody();

        if (!isset($postData['email'])) {
            return $this->errorJsonResponse($response, "Field 'email' missing");
        }

        if (!isset($postData['password'])) {
            return $this->errorJsonResponse($response, "Field 'password' missing");
        }

        // create new account
        $account = new Account();
        $account->setUsername($postData['email']);
        $account->setPasswordHash($postData['password']);

        $entityManager = $this->get('entityManager');
        $entityManager->persist($account);
        $entityManager->flush();

        return $this->successJsonResponseMessage($response, 'Account created');
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

        // ensure username and api_key exist
        $postData = $request->getParsedBody();

        if (!isset($postData['email'])) {
            return $this->errorJsonResponse($response, "Field 'email' missing");
        }

        if (!isset($postData['api_key'])) {
            return $this->errorJsonResponse($response, "Field 'api_key' missing");
        }

        /**
         * fetch user from db (by username)
         * @var Account $account
         */
        $account = $entityManager->getRepository('Bunq\DoGood\Model\Account')->findOneBy(['username' => $postData['username']]);

        if ($account === null) {
            return $this->errorJsonResponse($response, "Account not found, check username");
        }

        // create api context based on api key
        $bunqLib = $this->get('bunqLib');

        try {
            $context = $bunqLib->createContextProduction($postData['api_key']);
        } catch(BadRequestException $e) {
            return $this->errorJsonResponse($response, "API key invalid");
        }

        // save context to account
        $account->setBunqData(json_decode($context->toJson()));

        $entityManager->merge($account);
        $entityManager->flush();

        return $this->successJsonResponseMessage($response, 'Account updated with bunq API context');
    }
}
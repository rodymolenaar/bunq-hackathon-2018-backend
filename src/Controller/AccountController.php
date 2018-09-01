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
     * GET: Get Account
     *
     * @param Request $request
     * @param Response $response
     * @param array $args
     *
     * @return Response
     */
    public function getAccount(Request $request, Response $response) {
        $data = ['hasApiKey' => false];
        
        $bunqData = $this->get('user')->getBunqData();
        if (!empty($bunqData)) {
            $data['hasApiKey'] = true;
            $data['apiKey'] = $bunqData['api_key'];
        }

        return $this->successJsonResponsePayload($response, $data);
    }

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

        // ensure email and api_key exist
        $postData = $request->getParsedBody();

        if (!isset($postData['email']) || empty($postData['email'])) {
            return $this->errorJsonResponse($response, "Field 'email' missing or empty");
        }

        if (!isset($postData['password']) || empty($postData['password'])) {
            return $this->errorJsonResponse($response, "Field 'password' missing or empty");
        }
        
        if (strlen($postData['password']) < 3) {
            return $this->errorJsonResponse($response, 'Password should be 3 or more characters');
        }

        // ensure username isn't taken
        $existingAccount = $entityManager->getRepository('Bunq\DoGood\Model\Account')->findOneBy([
            'email' => $postData['email']
        ]);

        if ($existingAccount !== null) {
            return $this->errorJsonResponse($response, 'Username already taken');
        }


        // create new account
        $account = new Account();
        $account->setEmail($postData['email']);
        $account->setPasswordHash(password_hash($postData['password'], PASSWORD_BCRYPT));

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

        // ensure api_key exist
        $postData = $request->getParsedBody();

        if (!isset($postData['api_key'])) {
            return $this->errorJsonResponse($response, "Field 'api_key' missing");
        }

        // create api context based on api key
        $bunqLib = $this->get('bunqLib');

        try {
            $context = $bunqLib->createContextProduction($postData['api_key']);
        } catch(BadRequestException $e) {
            return $this->errorJsonResponse($response, "bunq API key invalid");
        }

        // save context to account
        $account = $this->get('user');
        $account->setBunqData(json_decode($context->toJson(), true));

        $entityManager->merge($account);
        $entityManager->flush();

        return $this->successJsonResponseMessage($response, 'Account updated with bunq API context');
    }
}
<?php

namespace Bunq\DoGood\Controller;

use Bunq\DoGood\Model\Account;
use Bunq\DoGood\Model\Goal;

use Slim\Http\Request;
use Slim\Http\Response;

/**
 * Class GoalController
 * @package Bunq\DoGood\Controller
 */
final class GoalController extends BaseController
{
    /**
     * POST: Create a new goal for specific merchants
     *
     * @param Request $request
     * @param Response $response
     * @param array $args
     *
     * @return Response
     * @throws \Exception
     */
    public function create(Request $request, Response $response, array $args) {
        $postData = $request->getParsedBody();

        // validate fields
        if (!isset($postData['amount'])) {
            return $this->errorJsonResponse($response, "Field 'amount' missing");
        }

        if (!isset($postData['transaction_id'])) {
            return $this->errorJsonResponse($response, "Field 'transaction_id' missing");
        }

        if (!isset($postData['operator']) || !Goal::isValidOperator($postData['operator'])) {
            return $this->errorJsonResponse($response, "Field 'condition' missing or invalid: " . implode(',', Goal::listOperators()));
        }

        if (!isset($postData['period']) || !Goal::isValidPeriod($postData['period'])) {
            return $this->errorJsonResponse($response, "Field 'period' missing or invalid: " . implode(',', Goal::listPeriods()));
        }

        // create new instance
        $goal = new Goal();
        $goal->setAccount($this->get('user'));
        $goal->setAmount((int) $postData['amount']);
        $goal->setTransactionId((int) $postData['transaction_id']);
        $goal->setOperator($postData['operator']);
        $goal->setPeriod($postData['period']);

        // save
        $entityManager = $this->get('entityManager');
        $entityManager->persist($goal);
        $entityManager->flush();


        return $this->successJsonResponseMessage($response, 'Goal created');
    }

    /**
     * GET: Returns your created goals with the merchant's and the business rule
     *
     * @param Request $request
     * @param Response $response
     * @param array $args
     *
     * @return Response
     */
    public function list(Request $request, Response $response, array $args) {
        return $this->successJsonResponsePayload($response, []);
    }

    /**
     * PATCH: Update your created goal with new merchants or change the business rule
     *
     * @param Request $request
     * @param Response $response
     * @param array $args
     *
     * @return Response
     */
    public function update(Request $request, Response $response, array $args) {
        $id = $args['id'];

        return $this->successJsonResponsePayload($response, []);
    }

    /**
     * DELETE: Delete a created goal
     *
     * @param Request $request
     * @param Response $response
     * @param array $args
     *
     * @return Response
     */
    public function delete(Request $request, Response $response, array $args) {
        $id = $args['id'];

        return $this->successJsonResponsePayload($response, []);
    }
}
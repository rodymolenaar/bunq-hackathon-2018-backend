<?php

namespace Bunq\DoGood\Controller;

use Bunq\DoGood\Model\Account;
use Bunq\DoGood\Model\Goal;

use bunq\Model\Generated\Object\Amount;
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

        if (!isset($postData['merchant_id'])) {
            return $this->errorJsonResponse($response, "Field 'merchant_id' missing");
        }

        if (!isset($postData['operator']) || !Goal::isValidOperator($postData['operator'])) {
            return $this->errorJsonResponse($response, "Field 'operator' missing or invalid: " . implode(',', Goal::listOperators()));
        }

        if (!isset($postData['period']) || !Goal::isValidPeriod($postData['period'])) {
            return $this->errorJsonResponse($response, "Field 'period' missing or invalid: " . implode(',', Goal::listPeriods()));
        }

        // create new instance
        $goal = new Goal();
        $goal->setAccount($this->get('user'));
        $goal->setAmount((int) $postData['amount']);
        $goal->setMerchantId((string) $postData['merchant_id']);
        $goal->setOperator($postData['operator']);
        $goal->setPeriod($postData['period']);

        // save
        $entityManager = $this->get('entityManager');
        $entityManager->persist($goal);
        $entityManager->flush();

        return $this->successJsonResponseMessage($response, 'Goal created');

        return $this->errorJsonResponse($response, 'Something went wrong while adding the goal');
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
        $entityManager = $this->get('entityManager');

        $goals = $entityManager->getRepository('Bunq\DoGood\Model\Goal')->findBy([
            'account' => $this->get('user')
        ]);

        return $this->successJsonResponsePayload($response, $goals);
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
        $postData = $request->getParsedBody();
        $id = $args['id'];

        $goal = $this->getGoalByIdAndAccount($id, $this->get('user'));

        if ($goal === null) {
            return $this->errorJsonResponse($response, 'User does not have the correct permissions to delete this goal');
        }

        if (isset($postData['amount']) && $postData['amount'] != $goal->getAmount()) {
            $goal->setAmount($postData['amount']);
        }

        if (isset($postData['transaction_id']) && $postData['transaction_id'] != $goal->getMerchantId()) {
            $goal->setMerchantId($postData['transaction_id']);
        }

        if (isset($postData['operator']) && $postData['operator'] != $goal->getOperator()) {
            $goal->setOperator($postData['operator']);
        }

        if (isset($postData['period']) && $postData['period'] != $goal->getPeriod()) {
            $goal->setPeriod($postData['period']);
        }

        // update
        $entityManager = $this->get('entityManager');
        $entityManager->merge($goal);
        $entityManager->flush();

        return $this->successJsonResponseMessage($response, 'Goal updated');
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

        $goal = $this->getGoalByIdAndAccount($id, $this->get('user'));

        if ($goal === null) {
            return $this->errorJsonResponse($response, 'User does not have the correct permissions to delete this goal');
        }

        $entityManager = $this->get('entityManager');
        $entityManager->remove($goal);
        $entityManager->flush();

        return $this->successJsonResponseMessage($response, 'Goal deleted');
    }

    /**
     * Find an goal instance if account has enough permissions
     *
     * @param int $goalId
     * @param Account $account
     * @return mixed
     */
    private function getGoalByIdAndAccount(int $goalId, Account $account)
    {
        $entityManager = $this->get('entityManager');

        return $entityManager->getRepository('Bunq\DoGood\Model\Goal')->findOneBy([
            'id' => $goalId,
            'account' => $account
        ]);
    }
}
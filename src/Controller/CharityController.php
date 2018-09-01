<?php

namespace Bunq\DoGood\Controller;

use Bunq\DoGood\Model\Charity;
use Bunq\DoGood\Model\CharityCategory;
use Slim\Http\Response;
use Slim\Http\Request;

/**
 * Class CharityController
 * @package Bunq\DoGood\Controller
 */
final class CharityController extends BaseController
{
    /**
     * Full list of available
     *
     * @param Request $request
     * @param Response $response
     * @param array $args
     *
     * @return Response
     */
    public function getList(Request $request, Response $response, array $args) {
        $entityManager = $this->get('entityManager');

        $charityIds = $this->get('user')->getCharityIds();

        $categories = $entityManager->getRepository('Bunq\DoGood\Model\CharityCategory')->findAll();
        $data = array_map(function ($category) use ($entityManager, $charityIds) {
            $charities = $entityManager->getRepository('Bunq\DoGood\Model\Charity')->findBy([
                'category' => $category
            ]);

            $charities = array_map(function($charity) use ($charityIds) {
                return $charity->jsonSerialize($charityIds);
            }, $charities);

            $categoryData = $category->jsonSerialize();
            $categoryData['charities'] = $charities;

            return $categoryData;
        }, $categories);

        return $this->successJsonResponsePayload($response, $data);
    }

    /**
     * Feed database with data from json
     *
     * @return bool
     */
    private function fillDatabase()
    {
        $path = realpath(__DIR__ . "/../../var/charities.json");

        if ($path === false) {
            return false;
        }

        $charities = json_decode(file_get_contents($path));

        $entityManager = $this->get('entityManager');
        foreach($charities->categories as $index => $data) {
            $category = new CharityCategory();
            $category->setName($data->name);

            $entityManager->persist($category);
            $entityManager->flush();

            foreach($data->charities as $data) {
                $charity = new Charity();
                $charity->setName($data->name);
                $charity->setIban($data->iban);
                $charity->setImageUrl($data->image_url);
                $charity->setCategory($category);

                $entityManager->persist($charity);
                $entityManager->flush();
            }
        }

        return true;
    }

}
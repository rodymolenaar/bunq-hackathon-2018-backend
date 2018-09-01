<?php

namespace Bunq\DoGood\Controller;

use Slim\Http\Request;
use Slim\Http\Response;

use Bunq\DoGood\Model\Transaction;

/**
 * Class BunqController
 * @package Bunq\DoGood\controller
 */
final class BunqController extends BaseController
{
    /**
     * Notification Filters listener
     *
     * @param Request $request
     * @param Response $response
     * @param array $args
     *
     * @return Response
     */
    public function trigger(Request $request, Response $response, array $args) {
        $string = '{"data": {
        "NotificationUrl": {
            "target_url": "https:\/\/bunq-api.testservers.nl\/bunq\/trigger",
            "category": "PAYMENT",
            "event_type": "PAYMENT_CREATED",
            "object": {
                "Payment": {
                    "id": 95426011,
                    "created": "2018-09-01 14:33:35.264022",
                    "updated": "2018-09-01 14:33:35.264022",
                    "monetary_account_id": 198594,
                    "amount": {
                        "currency": "EUR",
                        "value": "-0.01"
                    },
                    "description": "Emte Harmelen HARMELEN, NL",
                    "type": "MASTERCARD",
                    "merchant_reference": null,
                    "maturity_date": "2018-09-01",
                    "alias": {
                        "iban": "NL61BUNQ2290063916",
                        "is_light": false,
                        "display_name": "R.H.G. Van der Linden",
                        "avatar": {
                            "uuid": "0a9e296d-0c22-4ff3-bd50-888b99a8d5fd",
                            "image": [{
                                "attachment_public_uuid": "45bd6101-e668-438e-ac00-9cc407796739",
                                "height": 271,
                                "width": 271,
                                "content_type": "image\/jpeg"
                            }],
                            "anchor_uuid": null
                        },
                        "label_user": {
                            "uuid": "2a72622d-cd27-4693-86b3-03c0c9c60981",
                            "display_name": "R.H.G. Van der Linden",
                            "country": "NL",
                            "avatar": {
                                "uuid": "77ec0288-3268-4f8e-b9b6-482a983e7584",
                                "image": [{
                                    "attachment_public_uuid": "258c85c2-0d4d-4135-affb-c1829f3eb77c",
                                    "height": 589,
                                    "width": 589,
                                    "content_type": "image\/jpeg"
                                }],
                                "anchor_uuid": "2a72622d-cd27-4693-86b3-03c0c9c60981"
                            },
                            "public_nick_name": "Rick"
                        },
                        "country": "NL"
                    },
                    "counterparty_alias": {
                        "iban": null,
                        "is_light": false,
                        "display_name": "Emte Harmelen",
                        "avatar": {
                            "uuid": "8379d04b-d38a-47fa-a6fa-7d79cbfca5ff",
                            "image": [{
                                "attachment_public_uuid": "5c262860-ecb2-49ec-aeed-103c33e05898",
                                "height": 1024,
                                "width": 1024,
                                "content_type": "image\/jpeg"
                            }],
                            "anchor_uuid": null
                        },
                        "label_user": {
                            "uuid": "2fb9963d-317d-483f-9158-15d111008f2f",
                            "display_name": "Dani\u00ebl",
                            "country": "NL",
                            "avatar": {
                                "uuid": "9682d4d5-7c9c-4ad8-ace6-af9500ba93a3",
                                "image": [{
                                    "attachment_public_uuid": "00fd5720-beab-40b3-94c7-31585506a6b9",
                                    "height": 534,
                                    "width": 534,
                                    "content_type": "image\/jpeg"
                                }],
                                "anchor_uuid": "2fb9963d-317d-483f-9158-15d111008f2f"
                            },
                            "public_nick_name": "Dani\u00ebl"
                        },
                        "country": "NL"
                    },
                    "attachment": [],
                    "geolocation": {
                        "latitude": 52.387641803673,
                        "longitude": 4.8327161175967,
                        "altitude": -0.00062157539651,
                        "radius": 65
                    },
                    "batch_id": null,
                    "conversation": null,
                    "allow_chat": true,
                    "scheduled_id": null,
                    "address_billing": null,
                    "address_shipping": null,
                    "sub_type": "PAYMENT",
                    "status": "SETTLED",
                    "request_reference_split_the_bill": []
                }
            }
        }
    }}';

        // $data = $request->getParsedBody();
        $data = json_decode($string, true);

        $payment = $data['data']['NotificationUrl']['object']['Payment'];

        $entityManager = $this->get('entityManager');
        $transaction = new Transaction();
        $transaction->setTransactionId((int) $payment['id']);
        $transaction->setMerchantId($this->merchantIdFromData($payment));
        $transaction->setAmount($this->parseAmount($payment['amount']['value']));
        $transaction->setCreatedAt(new \DateTime);

        $entityManager->persist($transaction);
        $entityManager->flush();
    }

    private function merchantIdFromData($paymentData) {
        return sha1(trim($paymentData['counterparty_alias']['display_name']) . trim($paymentData['description']));
    }

    private function parseAmount($amount) {
        return (int) str_replace('.', '', str_replace('-', '', $amount));
    }
}
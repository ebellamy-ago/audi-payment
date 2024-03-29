<?php

namespace Tests\functional\AppBundle\Controller;

use Codeception\Example;
use Codeception\Scenario;
use Mullenlowe\PayPluginBundle\Model\AbstractTransaction;

class PaymentControllerCest
{
    /**
     * @dataProvider getToUnset
     */
    public function tryToRetrievePaymentInformationsForMagellanWithBadParameters(\FunctionalTester  $I, Scenario $scenario, Example $example)
    {
        $parameters = [
            'currency' => 'EUR',
            'provider' => 'magellan',
            'reference_id' => '126460319',
            'amount' => '100',
            'lastname' => 'JOHN',
            'phone' => '0102030405',
            'name' => 'DOE',
            'merchant_login' => 'Log1',
            'merchant_pwd' => 'Pwd1',
            'merchant_id' => 'Id1',
            'url_cancel' => 'https://audi-url.com/cancel1',
            'url_post_data' => 'https://audi-url.com/post_data1',
            'url_receipt' => 'https://audi-url.com/receipt1',
            'origin' => 'etron',
            'vin' => 'WAUZZZ4B65N001755'
        ];

        unset($parameters[$example[0]]);

        $expected = file_get_contents(__DIR__.'/../MockResponse/payment_01.html');
        $I->amOnPage('/');
        $I->sendPOST('/', $parameters);
        $I->seeResponseCodeIs(400);
    }

    /**
     * @dataProvider updateRequestParameters
     */
    public function testUpdateTransaction(\FunctionalTester  $I, Scenario $scenario, Example $example)
    {
        $parameters = [
            'currency' => 'EUR',
            'provider' => 'magellan',
            'reference_id' => 123456789,
            'transaction_id' => 111111111,
            'amount' => '100',
            'lastname' => 'JOHN',
            'phone' => '0102030405',
            'name' => 'DOE',
            'merchant_login' => 'Log1',
            'merchant_pwd' => 'Pwd1',
            'merchant_id' => 'Id1',
            'url_cancel' => 'https://audi-url.com/cancel1',
            'url_post_data' => 'https://audi-url.com/post_data1',
            'url_receipt' => 'https://audi-url.com/receipt1',
            'origin' => 'etron',
            'vin' => 'WAUZZZ4B65N001755'
        ];

        $I->amOnPage('/');
        $I->sendPOST('/', $parameters);

        $I->amOnPage('/transaction/magellan-status');
        $I->seeResponseIsJson('{"message": "The transaction has been successfully completed"}');
    }

    public function updateRequestParameters()
    {
        return [
            [
                'reference_id' => 123456789,
                'result_label' => 'OK',
                'transaction_id' => 111111111,
                'auth_code' => '',
                'result_code' => 'OK',
            ]
        ];
    }

    public function getToUnset()
    {
        return [
            ['currency'],
            ['provider'],
            ['reference_id'],
            ['amount'],
            ['lastname'],
            ['phone'],
            ['name'],
            ['merchant_login'],
            ['merchant_pwd'],
            ['merchant_id'],
            ['url_cancel'],
            ['url_post_data'],
            ['url_receipt'],
        ];
    }

    public function getPaymentParameters()
    {
        return [
            [
                'file' => 'payment_01.html',
                'currency' => 'EUR',
                'provider' => 'magellan',
                'reference_id' => '126460319',
                'amount' => '100',
                'lastname' => 'JOHN',
                'phone' => '0102030405',
                'name' => 'DOE',
                'merchant_login' => 'Log1',
                'merchant_pwd' => 'Pwd1',
                'merchant_id' => 'Id1',
                'url_cancel' => 'https://audi-url.com/cancel1',
                'url_post_data' => 'https://audi-url.com/post_data1',
                'url_receipt' => 'https://audi-url.com/receipt1',
                'origin' => 'etron',
                'vin' => 'WAUZZZ4B65N001755'
            ],
            [
                'file' => 'payment_02.html',
                'currency' => 'EUR',
                'provider' => 'magellan',
                'reference_id' => '426464319',
                'amount' => '40',
                'lastname' => 'JOHNNY',
                'phone' => '0202070405',
                'name' => 'DOEP',
                'merchant_login' => 'Log2',
                'merchant_pwd' => 'Pwd2',
                'merchant_id' => 'Id2',
                'url_cancel' => 'https://audi-url.com/cancel2',
                'url_post_data' => 'https://audi-url.com/post_data2',
                'url_receipt' => 'https://audi-url.com/receipt2',
                'origin' => 'etron',
                'vin' => 'WAUZZZ4B65N001755'
            ]
        ];
    }
}

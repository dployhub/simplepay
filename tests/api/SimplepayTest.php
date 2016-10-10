<?php

namespace Dploy\Simplepay\Tests;

use Dploy\Simplepay\Models\CreateTokenRequest;
use Dploy\Simplepay\Models\CreateTokenPaymentRequest;
use Dploy\Simplepay\Models\DeleteTokenRequest;
use Dploy\Simplepay\Models\CreateSyncPaymentRequest;

class SimplepayTest extends TestBase
{
    /**
     * Test CreateTokenRequest
     * Fields can be nested multi-levels deep
     *
     * @return void
     */
    public function testCreateTokenRequestShouldValidateAndSerializeProperly()
    {
        $req = new CreateTokenRequest([
          'paymentBrand' => 'VISA',
          'card' => [
            'holder' => 'Andrew',
            'number' => '4111111111111111',
            'expiryMonth' => '06',
            'expiryYear' => '19',
            'cvv' => '123',
          ],
        ]);

        $this->assertEquals($req->paymentBrand, 'VISA');
        $this->assertEquals($req->get('card.holder'), 'Andrew');
        $this->assertEquals($req->get('card.number'), '4111111111111111');
        $this->assertEquals($req->validate(), true);
        $this->assertEquals($req->toDataString(), 'paymentBrand=VISA&card.holder=Andrew&card.number=4111111111111111&card.expiryMonth=06&card.expiryYear=19&card.cvv=123');

        $req->paymentBrand = 'Invalid Brand';
        $this->assertEquals($req->validate(), false);
        $this->assertEquals(count($req->getErrors()), 1);
        $this->assertEquals('Invalid payment brand', end($req->getErrors()));

        $req->set('card.expiryMonth', 'as');
        $this->assertEquals($req->validate(), false);
        $this->assertEquals(count($req->getErrors()), 2);
        $this->assertEquals('Please enter a valid credit card expiry month', end($req->getErrors()));

        $req->set('card.expiryYear', '15');
        $this->assertEquals($req->validate(), false);
        $this->assertEquals(count($req->getErrors()), 3);
        $this->assertEquals('Please enter a valid credit card expiry year', end($req->getErrors()));

        $req->set('card.cvv', '');
        $this->assertEquals($req->validate(), false);
        $this->assertEquals(count($req->getErrors()), 4);
        $this->assertEquals('Please enter a valid cvv', end($req->getErrors()));
    }

    /**
     * Test DeleteTokenRequest
     *
     * @return void
     */
    public function testDeleteTokenRequestShouldValidateAndSerializeProperly()
    {
        $req = new DeleteTokenRequest([
          'registrationId' => '8a82944a576a798201576b139ad917bd',
        ]);

        $this->assertEquals($req->registrationId, '8a82944a576a798201576b139ad917bd');
        $this->assertEquals($req->validate(), true);
        $this->assertEquals($req->toDataString(), 'registrationId=8a82944a576a798201576b139ad917bd');

        $req->registrationId = '';
        $this->assertEquals($req->validate(), false);
        $this->assertEquals(count($req->getErrors()), 1);
        $this->assertEquals('Please enter registrationId', end($req->getErrors()));
    }

    /**
     * Test CreateTokenPaymentRequest
     *
     * @return void
     */
    public function testCreateTokenPaymentRequestShouldValidateAndSerializeProperly()
    {
        $req = new CreateTokenPaymentRequest([
          'registrationId' => '8a82944a576a798201576b139ad917bd',
          'paymentType' => 'CP',
          'currency' => 'AUD',
          'amount' => 10,
          'merchantTransactionId' => '123456',
        ]);

        $this->assertEquals($req->registrationId, '8a82944a576a798201576b139ad917bd');
        $this->assertEquals($req->validate(), true);
        $this->assertEquals($req->toDataString(), 'registrationId=8a82944a576a798201576b139ad917bd&paymentType=CP&currency=AUD&amount=10&merchantTransactionId=123456');

        $req->registrationId = '';
        $this->assertEquals($req->validate(), false);
        $this->assertEquals(count($req->getErrors()), 1);
        $this->assertEquals('Please enter registrationId', end($req->getErrors()));

        $req->paymentType = 'Invalid Type';
        $this->assertEquals($req->validate(), false);
        $this->assertEquals(count($req->getErrors()), 2);
        $this->assertEquals('Invalid payment type', end($req->getErrors()));

        $req->currency = 'USD';
        $this->assertEquals($req->validate(), false);
        $this->assertEquals(count($req->getErrors()), 3);
        $this->assertEquals('Currency is not supported', end($req->getErrors()));

        $req->amount = 'asd';
        $this->assertEquals($req->validate(), false);
        $this->assertEquals(count($req->getErrors()), 4);
        $this->assertEquals('Please enter a valid transaction amount', end($req->getErrors()));
    }

    /**
     * Test CreateSyncPaymentRequest
     *
     * @return void
     */
    public function testCreateSyncPaymentRequestShouldValidateAndSerializeProperly()
    {
        $req = new CreateSyncPaymentRequest([
          'paymentBrand' => 'VISA',
          'paymentType' => 'CP',
          'currency' => 'AUD',
          'amount' => 10,
          'card' => [
            'holder' => 'Andrew',
            'number' => '4111111111111111',
            'expiryMonth' => '06',
            'expiryYear' => '19',
            'cvv' => '123',
          ],
        ]);

        $this->assertEquals($req->paymentBrand, 'VISA');
        $this->assertEquals($req->validate(), true);
        $this->assertEquals($req->toDataString(), 'paymentBrand=VISA&paymentType=CP&currency=AUD&amount=10&card.holder=Andrew&card.number=4111111111111111&card.expiryMonth=06&card.expiryYear=19&card.cvv=123');

        // Test create token payment along with payment
        $req->createRegistration = 'true';
        $this->assertEquals($req->toDataString(), 'paymentBrand=VISA&paymentType=CP&currency=AUD&amount=10&card.holder=Andrew&card.number=4111111111111111&card.expiryMonth=06&card.expiryYear=19&card.cvv=123&createRegistration=true');

        $req->paymentBrand = '';
        $this->assertEquals($req->validate(), false);
        $this->assertEquals(count($req->getErrors()), 1);
        $this->assertEquals('Invalid payment brand', end($req->getErrors()));

        $req->paymentType = 'Invalid Type';
        $this->assertEquals($req->validate(), false);
        $this->assertEquals(count($req->getErrors()), 2);
        $this->assertEquals('Invalid payment type', end($req->getErrors()));

        $req->currency = 'USD';
        $this->assertEquals($req->validate(), false);
        $this->assertEquals(count($req->getErrors()), 3);
        $this->assertEquals('Currency is not supported', end($req->getErrors()));

        $req->amount = 'asd';
        $this->assertEquals($req->validate(), false);
        $this->assertEquals(count($req->getErrors()), 4);
        $this->assertEquals('Please enter a valid transaction amount', end($req->getErrors()));
    }

    /**
     * Test API createTokenRequest
     *
     * @return void
     */
    /*
    public function testCreateTokenRequestShouldReturnResult()
    {
      $req = new CreateTokenRequest([
        'paymentBrand' => 'VISA',
        'card' => [
          'holder' => 'Andrew',
          'number' => '4111111111111111',
          'expiryMonth' => '06',
          'expiryYear' => '2019',
          'cvv' => '123',
        ],
      ]);
      $response = $this->api->createToken($req);
      $this->assertEquals($response->isSuccess(), true);
      $this->assertEquals($response->isPending(), false);
      $this->assertEquals($response->isError(), false);
      $this->assertEquals($response->id != '', true);
    }
    */
}

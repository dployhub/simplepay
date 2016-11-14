<?php
namespace Dploy\Simplepay;

use Monolog\Logger;
use Dploy\Simplepay\Exceptions\SimplepayException;
use Dploy\Simplepay\Models\SimplepayResponse;
use Dploy\Simplepay\Models\SimplepayRequest;
use Dploy\Simplepay\Models\CapturePreauthPaymentRequest;
use Dploy\Simplepay\Models\CreateAsyncPaymentRequest;
use Dploy\Simplepay\Models\CreateSyncPaymentRequest;
use Dploy\Simplepay\Models\CreateRecurringPaymentRequest;
use Dploy\Simplepay\Models\CreateTokenRequest;
use Dploy\Simplepay\Models\CreateTokenPaymentRequest;
use Dploy\Simplepay\Models\DeleteTokenRequest;
use Dploy\Simplepay\Models\GetPaymentStatusRequest;
use Dploy\Simplepay\Models\IssueCreditRequest;
use Dploy\Simplepay\Models\RefundPaymentRequest;
use Dploy\Simplepay\Models\ReversePaymentRequest;

class Simplepay {

	protected $config;
	protected $endpoints;
	protected $version;
	protected $env;
	protected $ssl_verifier;
	protected $log;

	public function __construct($config = [], Logger $log = null)
	{
		$this->config = $config;
		$this->endpoints = [
			'test' => rtrim($config['testEndpoint'], '/'),
			'live' => rtrim($config['liveEndpoint'], '/'),
		];
		$this->env = $config['environment'];
		$this->version = $config['version'];
		$this->ssl_verifier = $this->env == 'live';
		$this->log = $log;
	}

	public function getEndpoint(){
    return isset($this->endpoints[$this->env]) ? $this->endpoints[$this->env] : '';
	}

  public function getUrl(){
    return $this->getEndpoint() . '/' . $this->version;
  }

	public function getVersion(){
		return $this->version;
	}

	public function getEnvironment(){
		return $this->env;
	}

	public function setVersion($version){
		return $this->version = $version;
	}

	public function setEnvironment($environment){
		return $this->env = $environment; //live or
	}

  /**
	 * Method to make curl requests using post & get methods
	 * Requires:
	 * @param string url
	 * @param string data
	 * @param string requestMethod
	 * @param string requestType
	 */
  public function curl_request($url, $requestMethod = 'GET', $data = null){
		$url = $this->getUrl() . '/' . $url;
		$requestMethod = strtoupper($requestMethod);

		$this->log('info', $requestMethod . ': ' . $url);

  	$ch = curl_init();
		if ($requestMethod == 'POST') {
			if ($data instanceof SimplepayRequest) {
				$data = $data->toDataString();
			}

			$this->log('info', $this->sanitize($data));

			$data = sprintf('authentication.userId=%s&authentication.password=%s&authentication.entityId=%s',
				$this->config['userId'],
        $this->config['password'],
        $this->config['entityId']
			) . '&' . $data;

			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		} elseif (in_array($requestMethod, ['DELETE'])) {
    	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $requestMethod);
		}

		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $this->ssl_verifier);// this should be set to true in production
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$response = curl_exec($ch);
		if(curl_errno($ch)) {
			$error = new SimplepayException(sprintf('Could not connect to Simplepay: %s', curl_error($ch)));
			curl_close($ch);
			throw $error;
		}
		curl_close($ch);
		return $response;

    }
	//----------------- tokenization ---------------------------
  /**
	 * Method to create token of user's credit card without making payment
	 * Requires:
	 * @param string userId
	 * @param string password
	 * @param string entityId
	 * @param string paymentBrand
	 * @param int cardNumber
	 * @param string cardHolder
	 * @param int cardExpiryMonth
	 * @param int cardExpiryYear
	 * @param int cardcvv
   */
  public function createToken(CreateTokenRequest $request)
	{
		$this->validateRequest($request);
		$response = $this->curl_request('registrations', 'POST', $request);
		return $this->response($response);
	}

	/**
	* Method to make call for deleting the already existing user token
	* Once stored, a token can be deleted against the registration.id:
	* Requires:
	* @param string userId
	* @param string entityId
	* @param string password
	* @param int registrationId
	*/
	public function deleteToken(DeleteTokenRequest $request)
	{
		$this->validateRequest($request);
		$response = $this->curl_request('registrations/' . $request->registrationId, 'DELETE', $request);
		return $this->response($response);
	}

	/*
	* One-Click payment :
		This method reqires 3 steps:
		1. Authenticate user
		2. Show Checkout
		3. Send Payment

		Step 1: Authenticate user
			You will need a method to authenticate the customer against your records in order to obtain their respective registration.id (token) associated with their account.  This can be achieved by asking the customer to log in for example, however you may find other ways that are applicable to your system.

			The information that you might want to store, per customer, in order to execute a One-Click payment includes:

			    registration.id (token): You can use 'createToken' method to store customer's card details (without making paymnet) or use 'createSyncPayment' method, and set createRegistration to true, to get the registrationId for user's card.
			    account brand: brand of customer's card
			    last four digits of account number
			    expiry date (if applicable)

		Step 2: Show Checkout Form:
			Create a form, to show user's all stored cards (You need to create form similar to this  https://docs.simplepays.com/sites/default/files/one-click-checkout.png) and show the list of all the cards you have stored. You can take example of html from page "https://docs.simplepays.com/tutorials/server-to-server/one-click-payment-guide".

		Step 3: Send Payment
		 	When user click on pay button use method 'createTokenPayment' with the mentioned paramteres to complete the payment procedure.
	*/

	/**
	* Method to make payment in One Click
	* Requires:
	* @param string userId
	* @param string entityId
	* @param string password
	* @param float amount
	* @param string currency
	* @param string paymentType
	* @param int registrationId
	*/
	public function createTokenPayment(CreateTokenPaymentRequest $request)
	{
		$this->validateRequest($request);
		$response = $this->curl_request('registrations/' . $request->registrationId . '/payments', 'POST', $request);
		return $this->response($response);
	}

	/*
	* ::: Using Stored Payment Data (Token) :::
	*/

	/**
	* Method to create token and make payment synchronously.
	* Requires:
	* @param string userId
	* @param string entityId
	* @param string password
	* @param float amount
	* @param string currency
	* @param string paymentBrand
	* @param string paymentType
	* @param int cardNumber
	* @param string cardHolder
	* @param int cardExpiryMonth
	* @param int cardExpiryYear
	* @param string cardcvv
	*/
	public function createInitialRecurringPayment(CreateRecurringPaymentRequest $request)
	{
		$request->createRegistration = true;
		$request->recurringType = 'INITIAL';
		return $this->createSyncPayment($request);
	}

	/**
	* Method to create token and make payment synchronously.
	* Requires:
	* @param string userId
	* @param string entityId
	* @param string password
	* @param float amount
	* @param string currency
	* @param string paymentBrand
	* @param string paymentType
	* @param int cardNumber
	* @param string cardHolder
	* @param int cardExpiryMonth
	* @param int cardExpiryYear
	* @param string cardcvv
	*/
	public function createRepeatedRecurringPayment(CreateRecurringPaymentRequest $request)
	{
		$request->recurringType = 'REPEATED';
		$this->validateRequest($request);
		$response = $this->curl_request('registrations/' . $request->registrationId . '/payments', 'POST', $request);
		return $this->response($response);
	}

	/**
	* Method for making a preauthorization request
	* Requires:
	* @param string userId
	* @param string entityId
	* @param string password
	* @param float amount
	* @param string currency
	* @param string paymentBrand
	* @param string paymentType
	* @param int cardNumber
	* @param string cardHolder
	* @param int cardExpiryMonth
	* @param int cardExpiryYear
	* @param string cardcvv
	*/
	public function createPreauthPayment(CreateSyncPaymentRequest $request)
	{
		$request->paymentType = 'PA';
		return $this->createSyncPayment($request);
	}

	/**
	* Method for capturing a previously preauthorized payment
	* Requires:
	* @param string userId
	* @param string entityId
	* @param string password
	* @param string id
	* @param float amount
	* @param string currency
	* @param string paymentBrand
	* @param string paymentType
	*/
	public function capturePreauthPayment(CapturePreauthPaymentRequest $request)
	{
		$request->paymentType = 'CP';
		$this->validateRequest($request);
		$response = $this->curl_request('payments/' . $request->id, 'POST', $request);
		return $this->response($response);
	}

	/**
	* Method for reversing / voiding a payment (e.g. preauthorization / debit)
	* Requires:
	* @param string userId
	* @param string entityId
	* @param string password
	* @param string id
	* @param string paymentType
	*/
	public function reversePayment(ReversePaymentRequest $request)
	{
		$request->paymentType = 'RV';
		$this->validateRequest($request);
		$response = $this->curl_request('payments/' . $request->id, 'POST', $request);
		return $this->response($response);
	}

	/**
	* Method for issuing a credit that is not associated to an existing payment
	* Requires:
	* @param string userId
	* @param string entityId
	* @param string password
	* @param float amount
	* @param string currency
	* @param string paymentBrand
	* @param string paymentType
	* @param int cardNumber
	* @param string cardHolder
	* @param int cardExpiryMonth
	* @param int cardExpiryYear
	* @param string cardcvv
	*/
	public function issueCredit(IssueCreditRequest $request)
	{
		$request->paymentType = 'CD';
		return $this->createSyncPayment($request);
	}

	/**
	* Method for making a refund against an existing payment
	* Requires:
	* @param string userId
	* @param string entityId
	* @param string password
	* @param string id
	* @param float amount
	* @param string currency
	* @param string paymentBrand
	* @param string paymentType
	*/
	public function refundPayment(RefundPaymentRequest $request)
	{
		$request->paymentType = 'RF';
		$this->validateRequest($request);
		$response = $this->curl_request('payments/' . $request->id, 'POST', $request);
		return $this->response($response);
	}

	/**
	* Method for making payment in a single step using server-to-server and receive the payment response synchronously.
	* Requires:
	* @param string userId
	* @param string entityId
	* @param string password
	* @param float amount
	* @param string currency
	* @param string paymentBrand
	* @param string paymentType
	* @param int cardNumber
	* @param string cardHolder
	* @param int cardExpiryMonth
	* @param int cardExpiryYear
	* @param string cardcvv
	*/
	public function createSyncPayment(CreateSyncPaymentRequest $request)
	{
		$this->validateRequest($request);
		$response = $this->curl_request('payments', 'POST', $request);
		return $this->response($response);
	}

	/*
	 * After asynchronous payment, you need to follow the following guideline:
	 *
	 * The next step is to redirect the account holder. To do this you must parse the 'redirect_url' from the Initial
	 * Payment response along with any parameters. If parameters are present they should be POST in the redirect,
	 * otherwise a straight forward redirect to the 'redirect_url' is sufficient.
	 */
	/**
	* Method to request for sending Initial Payment Request via Async method
	* Requires:
	* @param string userId
	* @param string entityId
	* @param string password
	* @param float amount
	* @param string currency
	* @param string paymentBrand
	* @param string shopperResultUrl
	* @param string paymentType
	*/
	public function createAsyncPayment(CreateAsyncPaymentRequest $request)
	{
		$this->validateRequest($request);
		$response = $this->curl_request('payments', 'POST', $request);
		return $this->response($response);
	}

	/**
	* Method to make request for payment status of both Async and Sync payments
	* Requires:
	* @param string userId
	* @param string entityId
	* @param string password
	* @param string id
	*/
	public function getPaymentStatus(GetPaymentStatusRequest $request)
	{
		$this->validateRequest($request);
		$response = $this->curl_request('payments/' . $request->id, 'GET');
		return $this->response($response);
	}

  /* ---- Protected Methods -- */
	protected function validateRequest(SimplepayRequest $request)
	{
		if (!$request->validate()) {
			throw new SimplepayException($request);
		}
	}

	protected function response($responseJson)
	{
		$response = new SimplepayResponse($responseJson);
		if ($response->isError()) {
			$this->log('error', $responseJson);
			throw new SimplepayException($response);
		}
		return $response;
	}

	protected function log($severity, $msg)
	{
		if ($this->log) $this->log->$severity($msg);
	}

  /**
   * As per PCI compliance, we do not want to log any credit card numbers
   */
  protected function sanitize($data, $mask = 'X')
  {
    $qs = explode('&', $data);
    $query = [];
    foreach($qs as $q) {
      list($k, $v) = explode('=', $q);
      $query[$k] = $v;
    }

    $ccFields = ['card.number'];
    foreach($ccFields as $field) {
      if (isset($query[$field])) {
        $query[$field] = $this->maskCc($query[$field]);
      }
    }
    $parts = [];
    foreach($query as $k => $v) {
      $parts[] = implode('=', [$k, $v]);
    }
    return implode('&', $parts);
  }

  protected function maskCc($cc, $mask = 'X')
  {
    return substr($cc, 0, 4) . str_repeat($mask, strlen($cc) - 8) . substr($cc, -4);
  }
}

<?php namespace Dploy\Simplepay\Models;

class SimplepayResponse {

	/**
	*  @var string original response data
	*/
	protected $responseData;

	/**
	*  @var string JSON decoded response
	*/
	protected $response;

	/**
	*  @var object result outcome for the API call
	*/
	protected $result;

	public function __construct($responseData){
		$this->responseData = $responseData;
		$this->response = json_decode($responseData);
		if (!is_null($this->response) && isset($this->response->result)) {
			$this->result = Validation::checkResult($this->response->result);
		}
	}

	public function getResponse()
	{
		return $this->response;
	}

	public function getResponseData()
	{
		return $this->responseData;
	}

	public function getResult()
	{
		return $this->result;
	}

	public function getMessage()
	{
		return ($this->result ? (isset($this->result->message) ? $this->result->message : '') : 'Error connecting to Simplepay server');
	}

	public function isSuccess()
	{
		return ($this->result && $this->result->state == 1);
	}

	public function isPending()
	{
		return ($this->result && $this->result->state == 2);
	}

	public function isError()
	{
		return (!$this->result || ($this->result && $this->result->state == 0));
	}

	public function toJson()
	{
		return $this->responseData;
	}

	public function __get($value)
	{
		return ($this->response && isset($this->response->$value)) ? $this->response->$value : null;
	}

}

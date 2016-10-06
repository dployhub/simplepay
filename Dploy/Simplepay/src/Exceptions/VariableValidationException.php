<?php namespace Dploy\Simplepay\Exceptions;

use Dploy\Simplepay\Exceptions\Exception;

class VariableValidationException extends Exception
{
	 /**
     * Constructor.
     *
     * @param int        $code
     * 
     */
	
	public function __construct($message)
    {
        parent::__construct($message);       
    }
}
?>
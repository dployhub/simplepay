<?php namespace Dploy\Simplepay\Exceptions;

use Dploy\Simplepay\Exceptions\Exception;

class SimplepayException extends Exception
{
    /**
     * Constructor.
     *
     * @param int        $code
     * 
     */
	
	public function __construct($message = null)
    {
        parent::__construct($message);
    }
}
?>
<?php

namespace Dploy\Simplepay\Models;

class Validation {

  protected static $errors = [
    'paymentBrand' => 'Invalid payment brand',
    'paymentType' => 'Invalid payment type',
    'amount' => 'Please enter a valid transaction amount',
    'currency' => 'Currency is not supported',
    'card.number' => 'Please enter a valid credit card number',
    'card.expiryMonth' => 'Please enter a valid credit card expiry month',
    'card.expiryYear' => 'Please enter a valid credit card expiry year',
    'card.cvv' => 'Please enter a valid cvv',
    'virtualAccount.accountId' => 'Please enter a valid virtual account ID',
    'bankAccount.holder' => 'Please enter a name of bank account holder',
    'recurringType' => 'Invalid recurring type',
  ];

  protected static $validPaymentBrands = ['VISA','MASTER','AMEX','ALIPAY','CHINAUNIONPAY'];
  protected static $validPaymentTypes = ['PA','DB','CD','CP','RV','RF'];
  protected static $validCurrencies = ['AUD'];
  protected static $validRecurringTypes = ['INITIAL','REPEATED'];

  protected static $resultCodes = [
    'success' => ['/^(000\.000\.|000\.100\.1|000\.[36])/','state' => 1],
    'successShouldBeManuallyReviewed' => ['/^(000\.400\.0|000\.400\.100)/','state' => 1],
    'pendingStatusMayChangeInHalfHour' => ['/^(000\.200)/','state' => 2],
    'pendingStatusMayChangeInFewDays' => ['/^(800\.400\.5|100\.400\.500)/','state' => 2],
    'rejected' => ['/^(000\.400\.[1][0-9][1-9]|000\.400\.2)/','state' => 0],
    'rejectionBranch' => ['/^(800\.[17]00|800\.800\.[123])/','state' => 0],
    'rejectionViaCommunication' => ['/^(900\.[1234]00)/','state' => 0],
    'rejectionViaSystemError' => ['/^(800\.5|999\.|600\.1|800\.800\.8)/','state' => 0],
    'errorInAsyncFlow' => ['/^(100\.39[765])/','state' => 0],
    'errorExternalRiskSystem' => ['/^(100\.400|100\.38|100\.370\.100|100\.370\.11])/','state' => 0],
    'rejectForAddressValidation' => ['/^(800\.400\.1)/','state' => 0],
    'reject3dSecure' => ['/^(800\.400\.2|100\.380\.4|100\.390)/','state' => 0],
    'rejectBlacklistValidation' => ['/^(100\.100\.701|800\.[32])/','state' => 0],
    'rejectRiskValidation' => ['/^(800\.1[123456]0)/','state' => 0],
    'rejectConfigValidation' => ['/^(600\.2|500\.[12]|800\.121)/','state' => 0],
    'rejectRegistrationValidation' => ['/^(100\.[13]50)/','state' => 0],
    'rejectJobValidation' => ['/^(100\.250|100\.360)/','state' => 0],
    'rejectReferenceValidation' => ['/^(700\.[1345][05]0)/','state' => 0],
    'rejectFormatValidation' => ['/^(200\.[123]|100\.[53][07]|800\.900|100\.[69]00\.500)/','state' => 0],
    'rejectAddressValidation' => ['/^(100\.800)/','state' => 0],
    'rejectContactValidation' => ['/^(100\.[97]00)/','state' => 0],
    'rejectAccountValidation' => ['/^(100\.100|100.2[01])/','state' => 0],
    'rejectAmountValidation' => ['/^(100\.55)/','state' => 0],
    'rejectRiskManagement' => ['/^(100\.380\.[23]|100\.380\.101)/','state' => 0],
    'chargeback' => ['/^(000\.100\.2)/','state' => 0],
  ];

  public static function get($key)
  {
    return isset(self::$errors[$key]) ? self::$errors[$key] : 'Please enter ' . $key;
  }

  public static function validate($key, $value)
  {
    $func = self::getValidateFuncName($key);
    if (is_null($value) || $value == '') return false;
    if (method_exists(self::class, $func)) {
      return self::$func($value);
    }
    return true;
  }

  public static function checkResult($result)
  {
    if (isset($result->code)) {
      $code = $result->code;
      foreach(self::$resultCodes as $key => $data){
         if(preg_match($data[0], $code)) {
            return (object)[
              'id' => $key,
              'code' => $code,
              'state' => $data['state'],
              'message' => isset($result->description) ? $result->description : '',
            ];
         }
      }
    }
    return (object)['id' => 'unknown', 'code' => null, 'state' => 0, 'message' => 'Unknown error'];
  }

  protected static function getValidateFuncName($key)
  {
    return 'validate' . str_replace(' ', '', ucfirst(camel_case($key)));
  }

  protected static function validatePaymentBrand($value)
  {
    return in_array($value, self::$validPaymentBrands);
  }

  protected static function validatePaymentType($value)
  {
    return in_array($value, self::$validPaymentTypes);
  }

  protected static function validateAmount($value)
  {
    return ((float)$value != 0);
  }

  protected static function validateCurrency($value)
  {
    return in_array($value, self::$validCurrencies);
  }

  protected static function validateCardCvv($value)
  {
    $len = strlen($value);
    return ((string)(int)$value == (string)$value && $len > 2 && $len < 5);
  }

  protected static function validateCardNumber($value)
  {
    $value = preg_replace('/[^0-9]/', '', $value);
    return (string)(int)$value == (string)$value;
  }

  protected static function validateCardExpiryMonth($value)
  {
    $value = (int)ltrim($value, '0');
    return ($value > 0 && $value < 13);
  }

  protected static function validateCardExpiryYear($value)
  {
    $value = (int)$value;
    if (strlen($value) == 2) $value += 2000;
    return $value >= date('Y');
  }

  protected static function validateRecurringType($value)
  {
    return in_array($value, self::$validRecurringTypes);
  }
}

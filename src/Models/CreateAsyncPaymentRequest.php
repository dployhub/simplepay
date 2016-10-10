<?php

namespace Dploy\Simplepay\Models;

class CreateAsyncPaymentRequest extends SimplepayRequest {

  protected $validation = [
    'paymentBrand', 'paymentType', 'currency', 'amount', 'card.number', 'card.expiryMonth', 'card.expiryYear', 'card.cvv', 'shopperResultUrl',
  ];

  public function validate()
  {
    if (!parent::validate()) return false;

    if (!in_array($this->paymentBrand, ['ALIPAY','CHINAUNIONPAY'])) {
      $this->errors[] = 'Payment brand not supported';
    }

    return !count($this->errors);
  }

}

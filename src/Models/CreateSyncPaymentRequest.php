<?php

namespace Dploy\Simplepay\Models;

class CreateSyncPaymentRequest extends SimplepayRequest {

  protected $validation = [
    'paymentBrand', 'paymentType', 'currency', 'amount', 'card.number', 'card.expiryMonth', 'card.expiryYear', 'card.cvv',
  ];

  public function validate()
  {
    if (!parent::validate()) return false;

    if (!in_array($this->paymentBrand, ['VISA','MASTER','AMEX'])) {
      $this->errors[] = 'Payment brand not supported';
    }

    return !count($this->errors);
  }

}

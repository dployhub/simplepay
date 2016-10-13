<?php

namespace Dploy\Simplepay\Models;

class ReversePaymentRequest extends SimplepayRequest {

  protected $validation = [
    'id', 'paymentType',
  ];

  protected $excludeFields = [
    'id',
  ];

}

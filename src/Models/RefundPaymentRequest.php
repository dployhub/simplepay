<?php

namespace Dploy\Simplepay\Models;

class RefundPaymentRequest extends SimplepayRequest {

  protected $validation = [
    'id', 'paymentType', 'currency', 'amount',
  ];

  protected $excludeFields = [
    'id',
  ];

}

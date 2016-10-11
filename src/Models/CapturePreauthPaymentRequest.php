<?php

namespace Dploy\Simplepay\Models;

class CapturePreauthPaymentRequest extends SimplepayRequest {

  protected $validation = [
    'id', 'paymentType', 'currency', 'amount',
  ];

}

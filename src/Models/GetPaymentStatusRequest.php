<?php

namespace Dploy\Simplepay\Models;

class GetPaymentStatusRequest extends SimplepayRequest {

  protected $validation = [
    'id',
  ];

}

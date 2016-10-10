<?php

namespace Dploy\Simplepay\Models;

class CreateTokenPaymentRequest extends SimplepayRequest {

  protected $validation = [
    'registrationId', 'paymentType', 'currency', 'amount',
  ];

}

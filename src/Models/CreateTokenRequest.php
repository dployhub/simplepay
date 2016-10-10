<?php

namespace Dploy\Simplepay\Models;

class CreateTokenRequest extends SimplepayRequest {

  protected $validation = [
    'paymentBrand', 'card.number', 'card.expiryMonth', 'card.expiryYear', 'card.cvv',
  ];

}

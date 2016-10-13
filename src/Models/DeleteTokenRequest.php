<?php

namespace Dploy\Simplepay\Models;

class DeleteTokenRequest extends SimplepayRequest {

  protected $validation = [
    'registrationId',
  ];

  protected $excludeFields = [
    'registrationId',
  ];

}

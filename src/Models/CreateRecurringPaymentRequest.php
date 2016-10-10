<?php

namespace Dploy\Simplepay\Models;

class CreateRecurringPaymentRequest extends CreateSyncPaymentRequest {

  protected $validation = [
    'paymentBrand', 'paymentType', 'currency', 'amount', 'card.number', 'card.expiryMonth', 'card.expiryYear', 'card.cvv', 'recurringType',
  ];

}

<?php

namespace App\Enums;

enum AccountTypes: string
{
    case cash = "cash";
    case bank_account = "bankAccount";
    case credit_card = "creditCard";
    case digital_wallet = "digitalWallet";
}

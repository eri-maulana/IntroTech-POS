<?php
namespace App\Enums;

enum PaymentMethod: string
{
    case CASH = 'cash';
    case BANK_TRANSFER = 'bank_transfer';

    public function getLabel(): ?string
    {
        return str(
            str($this->value)->replace('_', ' ')
        )->title();
    }
} 
<?php
namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum PaymentMethod: string implements HasLabel
{
    case TUNAI = 'tunai';
    case TRANSFER = 'transfer';

    public function getLabel(): ?string
    {
        return str(
            str($this->value)->replace('_', ' ')
        )->title();
    }
} 
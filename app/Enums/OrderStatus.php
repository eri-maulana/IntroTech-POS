<?php
namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum OrderStatus: string implements HasLabel
{
    case MENUNGGU = 'menunggu';
    case SELESAI = 'selesai';
    case DIBATALKAN = 'dibatalkan';

    public function getLabel(): ?string
    {
        return str($this->value)->title();
    }

    public function getColor(): string
    {
        return match ($this) {
            self::MENUNGGU => 'warning',
            self::SELESAI => 'success',
            self::DIBATALKAN => 'danger',
        };
    }
}
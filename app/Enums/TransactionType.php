<?php

namespace App\Enums;

enum TransactionType: int
{
    case DEPOSIT = 0;
    case WITHDRAWAL = 1;
    case TRANSFER = 2;

    public static function options(): array
    {
        return array_values((new \ReflectionClass(self::class))->getConstants());
    }

    public function label(): string
    {
        return match ($this) {
            self::DEPOSIT => 'deposit',
            self::WITHDRAWAL => 'withdrawal',
            self::TRANSFER => 'transfer',
        };
    }
}

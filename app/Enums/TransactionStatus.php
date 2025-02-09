<?php

namespace App\Enums;

enum TransactionStatus: int
{
    case PENDING = 0;
    case COMPLETED = 1;
    case FAILED = 2;
    case REFUNDED = 3;
    case CANCELED = 4;
    case PROCESSING = 5;
    case ONHOLD = 6;
    case CHARGEBACK = 7;

    public static function options(): array
    {
        return array_values(self::cases()); // Use self::cases() to get all enum cases
    }

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'pending',
            self::COMPLETED => 'completed',
            self::FAILED => 'failed',
            self::REFUNDED => 'refunded',
            self::CANCELED => 'canceled',
            self::PROCESSING => 'processing',
            self::ONHOLD => 'onHold',
            self::CHARGEBACK => 'chargeBack',
        };
    }
}

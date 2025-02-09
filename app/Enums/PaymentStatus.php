<?php

namespace App\Enums;

enum PaymentStatus: int
{
    case PENDING = 0;
    case COMPLETED = 1;
    case FAILED = 2;
    case REFUNDED = 3;
    case CANCELED = 4;
    case PROCESSING = 5;
    case ONHOLD = 6;
    case CHARGEBACK = 7;

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Pending',
            self::COMPLETED => 'Completed',
            self::FAILED => 'Failed',
            self::REFUNDED => 'Refunded',
            self::CANCELED => 'Canceled',
            self::PROCESSING => 'Processing',
            self::ONHOLD => 'On Hold',
            self::CHARGEBACK => 'Charge Back',
        };
    }
}

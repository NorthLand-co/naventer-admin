<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum OrderStatus: int implements HasColor, HasIcon, HasLabel
{
    case REGISTERED = 0;      // Order is just Added
    case PENDING = 1;      // Order is pending
    case PAID = 2;   // Order is being processed
    case PROCESSING = 3;   // Order is being processed
    case SHIPPED = 4;      // Order has been shipped
    case DELIVERED = 5;    // Order has been delivered
    case CANCELLED = 6;    // Order has been cancelled
    case REFUNDED = 7;     // Order has been refunded

    public function label(): string
    {
        return match ($this) {
            self::REGISTERED => 'Registered',
            self::PENDING => 'Pending',
            self::PAID => 'Paid',
            self::PROCESSING => 'Processing',
            self::SHIPPED => 'Shipped',
            self::DELIVERED => 'Delivered',
            self::CANCELLED => 'Cancelled',
            self::REFUNDED => 'Refunded',
        };
    }

    public function getLabel(): ?string
    {
        return match ($this) {
            self::REGISTERED => 'Registered',
            self::PENDING => 'Pending',
            self::PAID => 'Paid',
            self::PROCESSING => 'Processing',
            self::SHIPPED => 'Shipped',
            self::DELIVERED => 'Delivered',
            self::CANCELLED => 'Cancelled',
            self::REFUNDED => 'Refunded'
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::REGISTERED => 'lime',
            self::PENDING => 'cyan',
            self::PAID => 'sky',
            self::PROCESSING => 'teal',
            self::SHIPPED => 'violet',
            self::DELIVERED => 'green',
            self::CANCELLED => 'rose',
            self::REFUNDED => 'fuchsia'
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::REGISTERED => 'solar-document-line-duotone',
            self::PENDING => 'solar-hourglass-line-duotone',
            self::PAID => 'solar-check-circle-line-duotone',
            self::PROCESSING => 'solar-settings-line-duotone',
            self::SHIPPED => 'solar-city-line-duotone',
            self::DELIVERED => 'solar-check-circle-line-duotone',
            self::CANCELLED => 'solar-close-circle-line-duotone',
            self::REFUNDED => 'solar-wad-of-money-line-duotone',
        };
    }

    /**
     * Get the next status in the sequence.
     */
    public function next(): self
    {
        $statuses = self::cases();
        $currentIndex = array_search($this, $statuses);

        // Check if the current status is the last one
        if ($currentIndex === false || $currentIndex === array_key_last($statuses)) {
            return $this; // Return the current status if it's the last one
        }

        return $statuses[$currentIndex + 1];
    }
}

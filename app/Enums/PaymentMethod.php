<?php

namespace App\Enums;

enum PaymentMethod: int
{
    case WALLET = 0;
    case SEP = 1;
    case ZIBAL = 2;
    case BEHPARDAKHT = 3;
    case ZARINPAL = 4;

    public static function options(): array
    {
        return array_values((new \ReflectionClass(self::class))->getConstants());
    }

    public static function values(): array
    {
        return array_map(fn ($method) => $method->value, self::cases());
    }

    public static function names(): ?array
    {
        return array_column(self::cases(), 'name', 'value');
    }

    public static function driver(): array
    {
        return [
            self::WALLET->value => 'wallet',
            self::SEP->value => 'sep',
            self::ZIBAL->value => 'zibal',
            self::BEHPARDAKHT->value => 'behpardakht',
            self::ZARINPAL->value => 'zarinpal',
        ];
    }

    public static function getDriverName(int $value): string
    {
        if (! array_key_exists($value, self::driver())) {
            throw new \InvalidArgumentException("Invalid payment method value: $value");
        }

        return self::driver()[$value];
    }

    public static function getValueByDriverName(string $driverName): ?int
    {
        $drivers = array_flip(self::driver()); // Flip the driver array to get values by driver names

        return $drivers[$driverName] ?? null; // Return the corresponding value or null if not found
    }
}

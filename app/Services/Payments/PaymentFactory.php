<?php

namespace App\Services\Payments;

class PaymentFactory
{
    protected array $drivers;

    /**
     * Available drivers.
     */
    protected array $map = [
        'zibal' => Drivers\Zibal::class,
        'zarinpal' => Drivers\Zarinpal::class,
    ];

    public function __construct()
    {
        $this->drivers = config('payment')['drivers'];
    }

    /**
     * Return a config base on driver default config or provided config.
     */
    private function getConfig(string $driverName, array $config): array
    {
        return count($config) === 0 ? $this->drivers[$driverName] : $config;
    }

    /**
     * Get a driver instance by name.
     *
     * @throws \Exception
     */
    public function getDriver(string $driverName, array $config = []): PaymentInterface
    {
        // Check if driver exists in the map
        if (! isset($this->map[$driverName])) {
            throw new \Exception("No driver mapped for [$driverName].");
        }

        // Get the driver class name from the map
        $driverClass = $this->map[$driverName];

        // Check if the class exists
        if (! class_exists($driverClass)) {
            throw new \Exception("Driver class [$driverClass] does not exist.");
        }

        // Instantiate and return the driver with the provided config
        return new $driverClass($this->getConfig($driverName, $config));
    }
}

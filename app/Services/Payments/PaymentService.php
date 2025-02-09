<?php

namespace App\Services\Payments;

class PaymentService
{
    protected PaymentFactory $driverManager;

    public function __construct(?PaymentFactory $driverManager = null)
    {
        $this->driverManager = $driverManager ?? new PaymentFactory;
    }

    /**
     * Inquire a trackId from a specific driver.
     *
     * @return mixed
     */
    public function inquire(string $trackId, string $driverName, array $config = [])
    {
        $driver = $this->driverManager->getDriver($driverName, $config);

        return $driver->inquire($trackId);
    }
}

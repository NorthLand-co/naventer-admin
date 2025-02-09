<?php

namespace App\Services\Payments;

interface PaymentInterface
{
    /**
     * Inquire the track ID and return the result.
     */
    public function inquire(string $trackId): bool;
}

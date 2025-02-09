<?php

namespace App\Services\Payments\Drivers;

use GuzzleHttp\Client;

class BaseDriver
{
    /**
     * Zibal Client.
     *
     * @var array
     */
    protected $config;

    /**
     * Http Client.
     *
     * @var object
     */
    protected $client;

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->client = new Client;
    }
}

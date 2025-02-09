<?php

namespace App\Services\Payments\Drivers;

use App\Services\Payments\PaymentInterface;
use Illuminate\Support\Facades\Log;

class Zarinpal extends BaseDriver implements PaymentInterface
{
    public function inquire(string $trackId): bool
    {
        $data = [
            'merchant' => $this->config['merchantId'], // required
            'authority' => $trackId, // required
        ];

        try {
            $response = $this->client->request(
                'POST',
                $this->config['apiInquiryUrl'],
                ['json' => $data, 'http_errors' => false]
            );

            $body = json_decode($response->getBody()->getContents(), false);

            // Ensure $body and $body->data exist
            if (! isset($body->data)) {
                Log::error('Zarinpal API response missing "data" property.', [
                    'response' => $body,
                ]);

                return false;
            }

            if ($body->data->code != 100) {
                Log::warning('Zarinpal API returned an error code.', [
                    'code' => $body->data->code,
                    'response' => $body,
                ]);

                return false;
            }

            if ($body->data->status === 'PAID') {
                return true;
            }

            return false;
        } catch (\Exception $e) {
            // Log any exceptions that occur during the API call
            Log::error('Error during Zarinpal API inquiry.', [
                'exception' => $e->getMessage(),
                'trackId' => $trackId,
            ]);

            return false;
        }
    }
}

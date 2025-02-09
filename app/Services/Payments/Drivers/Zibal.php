<?php

namespace App\Services\Payments\Drivers;

use App\Services\Payments\PaymentInterface;
use Illuminate\Support\Facades\Log;

class Zibal extends BaseDriver implements PaymentInterface
{
    public function inquire(string $trackId): bool
    {
        $data = [
            'merchant' => $this->config['merchantId'], // required
            'trackId' => $trackId, // required
        ];

        try {
            $response = $this->client->request(
                'POST',
                $this->config['apiInquiryUrl'],
                ['json' => $data, 'http_errors' => false]
            );

            $body = json_decode($response->getBody()->getContents(), false);

            // Ensure $body exists and has the expected properties
            if (! isset($body->result)) {
                Log::error('Zibal API response missing "result" property.', [
                    'response' => $body,
                ]);

                return false;
            }

            if ($body->result != 100) {
                Log::warning('Zibal API returned an error result.', [
                    'result' => $body->result,
                    'response' => $body,
                ]);

                return false;
            }

            if (isset($body->message) && $body->message === 'success') {
                return true;
            }

            Log::info('Zibal API response did not indicate success.', [
                'message' => $body->message ?? 'No message provided',
            ]);

            return false;
        } catch (\Exception $e) {
            // Log any exceptions that occur during the API call
            Log::error('Error during Zibal API inquiry.', [
                'exception' => $e->getMessage(),
                'trackId' => $trackId,
            ]);

            return false;
        }
    }
}

<?php

namespace App\Console\Commands;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Payment;
use App\Services\Payments\PaymentService;
use App\Services\PaymentService as ServicesPaymentService;
use Carbon\Carbon;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class SiteMaintenance extends Command implements ShouldQueue
{
    use Queueable;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ecommerce:maintenance';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Periodically checks and updates models as necessary';

    protected $paymentService;

    public function __construct(ServicesPaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
        parent::__construct();
    }

    public function handle()
    {
        $this->info('Initiating abandoned payment checks...');

        try {
            $pendingPayments = Payment::where('status', PaymentStatus::PENDING)
                ->whereTime('created_at', '<', Carbon::now()->subHour())
                ->get();

            if ($pendingPayments->isEmpty()) {
                $this->info('No abandoned payments found.');
            } else {
                $this->info(sprintf('%d payments require processing.', $pendingPayments->count()));

                foreach ($pendingPayments as $payment) {
                    try {
                        $this->handleSinglePayment($payment);
                    } catch (Exception $exception) {
                        $this->error("Error processing payment ID {$payment->id}: {$exception->getMessage()}");
                        Log::error('Payment processing failed', ['payment_id' => $payment->id, 'exception' => $exception]);
                    }
                }
            }

            Payment::where('status', PaymentStatus::PENDING)
                ->whereTime('created_at', '<', Carbon::now()->subHour())
                ->update(['status' => PaymentStatus::CANCELED]);

            $this->info('Processed and updated abandoned payments.');
        } catch (Exception $exception) {
            $this->error('Error encountered during payment checks: '.$exception->getMessage());
            Log::error('Failed to process abandoned payments.', ['exception' => $exception]);
        }
    }

    /**
     * Handle a single payment.
     *
     * @return void
     */
    private function handleSinglePayment(Payment $payment)
    {
        try {
            $this->info("Inspecting payment ID {$payment->id}...");

            if (! is_null($payment->bank_transaction_id)) {

                $paymentService = new PaymentService;
                $driver = PaymentMethod::getDriverName($payment->method->value);

                $inquiryResult = $paymentService->inquire($payment->bank_transaction_id, $driver);
                $this->info("Payment inquiry result for ID {$payment->id}: {$inquiryResult}");

                if ($inquiryResult) {
                    $user = $payment->user;
                    $order = $payment->paymentable;

                    if (! $user || ! $order) {
                        $this->warn("Missing user or order for payment ID {$payment->id}.");

                        return;
                    }

                    $this->paymentService->complete($user->wallet, $payment, $order->final_price, PaymentStatus::COMPLETED);
                    $order->update(['status' => OrderStatus::PAID]);

                    $this->info("Payment ID {$payment->id} completed and order marked as paid.");
                }
            }
        } catch (Exception $exception) {
            throw $exception;
        }
    }
}

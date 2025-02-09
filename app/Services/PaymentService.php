<?php

namespace App\Services;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\TransactionStatus;
use App\Models\Payment as ModelsPayment;
use App\Models\Wallet;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Shetabit\Multipay\Invoice;
use Shetabit\Payment\Facade\Payment;
use Symfony\Component\HttpFoundation\Response;

class PaymentService
{
    protected $transactionService;

    public function __construct(TransactionService $transactionService)
    {
        $this->transactionService = $transactionService;
    }

    public function create(float $amount, ?string $driver = null, $paymentable = null)
    {
        $driver = $driver ?? env('DEFAULT_PAYMENT_DRIVER', 'zibal');
        try {
            $payment = $this->createPayment($paymentable, $amount, $driver);
            $invoice = $this->createInvoice($amount, $driver, $paymentable->paymentable_id);
            $payment = $this->processPayment($invoice, $payment);

            return $payment;
        } catch (Exception $th) {
            throw new Exception('Create Payment failed: '.$th->getMessage());
        }
    }

    /**
     * Create a payment for the deposit.
     */
    public function createPayment($paymentable, $amount, $method): ModelsPayment
    {
        return ModelsPayment::create([
            'paymentable_id' => $paymentable->id,
            'paymentable_type' => get_class($paymentable),
            'amount' => $amount,
            'method' => PaymentMethod::getValueByDriverName($method),
            'status' => PaymentStatus::PENDING,
        ]);
    }

    /**
     * Confirm a payment result
     */
    public function verified(Wallet $wallet, ModelsPayment $payment, $amount, $status)
    {
        if ($amount !== $payment->amount) {
            abort(Response::HTTP_BAD_REQUEST);
        }

        try {
            $transaction = $this->transactionService->deposit($wallet, $amount);
            if ($status == PaymentStatus::COMPLETED) {
                $transaction->status = TransactionStatus::COMPLETED;
                $transaction->save();
            }

            $payment->status = $status;
            $payment->save();

            return $payment;
        } catch (\Throwable $th) {
            throw new Exception('Verify Payment failed: '.$th->getMessage());
        }
    }

    /**
     * Complete a payment
     */
    public function complete(Wallet $wallet, ModelsPayment $payment, $amount, $status)
    {

        try {
            $transaction = $this->transactionService->withdrawal($wallet, $amount);

            return $payment::updated([
                'transaction_id' => $transaction->id,
                'status' => $status,
            ]);
        } catch (\Throwable $th) {
            throw new Exception('Complete Payment failed: '.$th->getMessage());
        }
    }

    /**
     * Create an invoice for the payment.
     */
    private function createInvoice(float $amount, string $driver, ?string $orderId = null): Invoice
    {

        /** @var \App\Models\User $user */
        $user = Auth::user();

        $invoice = (new Invoice)->amount($amount);
        $invoice->detail([
            'mobile' => $user->phone,
            'orderId' => $orderId,
        ]);
        $invoice->via($driver);

        return $invoice;
    }

    /**
     * Process the payment through the specified driver.
     */
    private function processPayment(Invoice $invoice, ModelsPayment $payment)
    {

        try {
            $details = Payment::via($invoice->getDriver())->purchase($invoice, function ($driver, $transactionId) use ($payment) {
                $payment->update([
                    'bank_transaction_id' => $transactionId,
                ]);
            })->pay()->toJson();

            $payment->update([
                'details' => $details,
            ]);

            return $details;
        } catch (\Throwable $th) {
            Log::error('Error in creating bank error: '.$th->getMessage());
        }
    }
}

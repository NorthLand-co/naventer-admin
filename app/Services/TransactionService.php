<?php

namespace App\Services;

use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Models\Transaction;
use App\Models\Wallet;
use Exception;
use Symfony\Component\HttpFoundation\Response;

class TransactionService
{
    /**
     * Deposit an amount into the wallet.
     *
     * @throws Exception
     */
    public function deposit(Wallet $wallet, $amount): Transaction
    {
        try {
            $transaction = $this->createTransaction($wallet, $amount, TransactionType::DEPOSIT, TransactionStatus::PENDING);
            $wallet->balance += $amount;
            $wallet->save();

            return $transaction;
        } catch (Exception $e) {
            // Handle or log the error appropriately
            // You can throw the exception or return a structured error response
            throw new Exception('Deposit failed: '.$e->getMessage());
        }
    }

    public function withdrawal(Wallet $wallet, $amount, $details = null): Transaction
    {

        if ($wallet->total < $amount) {
            return response()->json(['error' => 'Insufficient balance'], Response::HTTP_PAYMENT_REQUIRED);
        }

        $transaction = $this->createTransaction($wallet, $amount, TransactionType::WITHDRAWAL, TransactionStatus::COMPLETED, $details);

        $wallet->balance -= $amount;
        $wallet->save();

        return $transaction;
    }

    public function transfer(Wallet $source, Wallet $destination, $amount) {}

    /**
     * Create a transaction for the deposit.
     */
    private function createTransaction(Wallet $wallet, $amount, $type, $status, $details = null): Transaction
    {
        return Transaction::create([
            'wallet_id' => $wallet->id,
            'details' => $details,
            'amount' => $amount,
            'type' => $type,
            'status' => $status,
        ]);
    }
}

<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TransactionService
{
    public function __construct(
        protected StockService $stockService,
        protected AuditService $auditService
    ) {
    }

    /**
     * Create a new transaction.
     */
    public function createTransaction(int $userId, array $items, string $paymentMethod, float $paidAmount): Transaction
    {
        return DB::transaction(function () use ($userId, $items, $paymentMethod, $paidAmount) {
            if (empty($items)) {
                throw new Exception("Transaction items cannot be empty.");
            }

            $totalAmount = 0;
            $processedItems = [];

            foreach ($items as $item) {
                $product = Product::findOrFail($item['product_id']);
                
                if (!$product->is_active) {
                    throw new Exception("Product {$product->name} is not active.");
                }

                if ($product->stock < $item['quantity']) {
                    throw new Exception("Insufficient stock for product: {$product->name}");
                }

                $subtotal = $product->price * $item['quantity'];
                $totalAmount += $subtotal;

                $processedItems[] = [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'price' => $product->price,
                    'quantity' => $item['quantity'],
                    'subtotal' => $subtotal,
                ];
            }

            if ($paidAmount < $totalAmount) {
                throw new Exception("Paid amount is less than total amount.");
            }

            $changeAmount = $paidAmount - $totalAmount;

            $transaction = Transaction::create([
                'transaction_code' => 'TXN-' . strtoupper(Str::random(10)),
                'user_id' => $userId,
                'total_amount' => $totalAmount,
                'paid_amount' => $paidAmount,
                'change_amount' => $changeAmount,
                'payment_method' => $paymentMethod,
                'status' => 'completed',
                'transaction_date' => now(),
            ]);

            foreach ($processedItems as $pItem) {
                $transaction->items()->create($pItem);
                $this->stockService->decreaseStock($pItem['product_id'], $pItem['quantity'], 'transaction', $transaction->id);
            }

            $this->auditService->log($userId, 'create', 'transactions', $transaction->id, "Created transaction {$transaction->transaction_code}");

            return $transaction;
        });
    }

    /**
     * Cancel a transaction.
     */
    public function cancelTransaction(int $transactionId): Transaction
    {
        return DB::transaction(function () use ($transactionId) {
            $transaction = Transaction::findOrFail($transactionId);

            if ($transaction->status !== 'completed') {
                throw new Exception("Only completed transactions can be cancelled.");
            }

            $transaction->update(['status' => 'cancelled']);

            foreach ($transaction->items as $item) {
                $this->stockService->increaseStock($item->product_id, $item->quantity, 'transaction', $transaction->id, "Restored stock from cancelled transaction: {$transaction->transaction_code}");
            }

            $this->auditService->log($transaction->user_id, 'cancel', 'transactions', $transaction->id, "Cancelled transaction {$transaction->transaction_code}");

            return $transaction;
        });
    }

    /**
     * Refund a transaction.
     */
    public function refundTransaction(int $transactionId): Transaction
    {
        return DB::transaction(function () use ($transactionId) {
            $transaction = Transaction::findOrFail($transactionId);

            if ($transaction->status !== 'completed') {
                throw new Exception("Only completed transactions can be refunded.");
            }

            $transaction->update(['status' => 'refunded']);

            foreach ($transaction->items as $item) {
                $this->stockService->increaseStock($item->product_id, $item->quantity, 'transaction', $transaction->id, "Restored stock from refunded transaction: {$transaction->transaction_code}");
            }

            $this->auditService->log($transaction->user_id, 'refund', 'transactions', $transaction->id, "Refunded transaction {$transaction->transaction_code}");

            return $transaction;
        });
    }
}

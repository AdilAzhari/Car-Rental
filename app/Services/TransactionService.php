<?php

namespace App\Services;

use Closure;
use Illuminate\Database\DeadlockException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TransactionService
{
    private const int MAX_RETRIES = 3;

    private const int RETRY_DELAY_MS = 100;

    public function executeWithRetry(Closure $callback, int $maxRetries = self::MAX_RETRIES): mixed
    {
        $attempt = 0;

        while ($attempt < $maxRetries) {
            try {
                return DB::transaction($callback);
            } catch (DeadlockException $e) {
                $attempt++;

                if ($attempt >= $maxRetries) {
                    Log::error('Transaction failed after maximum retries', [
                        'attempts' => $attempt,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                    ]);
                    throw $e;
                }

                Log::warning('Deadlock detected, retrying transaction', [
                    'attempt' => $attempt,
                    'max_retries' => $maxRetries,
                    'error' => $e->getMessage(),
                ]);

                // Exponential backoff with jitter
                $delay = self::RETRY_DELAY_MS * 2 ** ($attempt - 1) + random_int(0, 50);
                usleep($delay * 1000);
            }
        }

        throw new \RuntimeException('Transaction failed after all retry attempts');
    }

    public function safeExecute(Closure $callback): mixed
    {
        return $this->executeWithRetry($callback, 1);
    }

    public function executeWithLock(string $lockName, Closure $callback, int $timeout = 10): mixed
    {
        return DB::transaction(function () use ($lockName, $callback, $timeout) {
            $lockAcquired = DB::select('SELECT GET_LOCK(?, ?) as acquired', [$lockName, $timeout]);

            if (! $lockAcquired[0]->acquired) {
                throw new \RuntimeException("Could not acquire lock: {$lockName}");
            }

            try {
                return $callback();
            } finally {
                DB::select('SELECT RELEASE_LOCK(?)', [$lockName]);
            }
        });
    }

    public function batchProcess(array $items, Closure $processor, int $batchSize = 100): array
    {
        $results = [];
        $chunks = array_chunk($items, $batchSize);

        foreach ($chunks as $chunk) {
            $chunkResults = $this->executeWithRetry(fn (): array => array_map($processor, $chunk));

            $results = array_merge($results, $chunkResults);
        }

        return $results;
    }
}

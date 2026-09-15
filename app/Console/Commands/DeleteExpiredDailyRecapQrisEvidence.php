<?php

namespace App\Console\Commands;

use App\Models\DailyRecapQrisTransaction;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class DeleteExpiredDailyRecapQrisEvidence extends Command
{
    protected $signature = 'daily-recaps:delete-expired-qris-evidence';

    protected $description = 'Delete expired QRIS evidence photos while keeping transaction amounts.';

    public function handle(): int
    {
        $deleted = 0;

        DailyRecapQrisTransaction::query()
            ->whereNotNull('evidence_path')
            ->whereNull('evidence_deleted_at')
            ->where('evidence_expires_at', '<=', now())
            ->orderBy('id')
            ->chunkById(100, function ($transactions) use (&$deleted) {
                foreach ($transactions as $transaction) {
                    Storage::disk('local')->delete($transaction->evidence_path);

                    $transaction->update([
                        'evidence_path' => null,
                        'evidence_mime_type' => null,
                        'evidence_size' => null,
                        'evidence_deleted_at' => now(),
                    ]);

                    $deleted++;
                }
            });

        $this->info("Deleted {$deleted} expired QRIS evidence photo(s).");

        return self::SUCCESS;
    }
}

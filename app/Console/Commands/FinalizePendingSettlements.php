<?php

namespace App\Console\Commands;

use App\Services\PaymentService;
use Illuminate\Console\Command;

class FinalizePendingSettlements extends Command
{
    protected $signature = 'settlements:finalize';

    protected $description = 'Finalize held NFT settlements whose release time has passed';

    public function handle(PaymentService $paymentService): int
    {
        $count = $paymentService->finalizeMaturedSettlements();
        $this->info("Finalized settlements: {$count}");

        return self::SUCCESS;
    }
}

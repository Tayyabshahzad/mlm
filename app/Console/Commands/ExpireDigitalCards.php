<?php

namespace App\Console\Commands;

use App\Services\DigitalCardService;
use Illuminate\Console\Command;

class ExpireDigitalCards extends Command
{
    protected $signature = 'cards:expire';

    protected $description = 'Expire unused digital cards past their expiry date and refund the generators';

    public function handle(DigitalCardService $cards): int
    {
        $count = $cards->expireDueCards();
        $this->info("{$count} digital card(s) expired and refunded.");

        return self::SUCCESS;
    }
}

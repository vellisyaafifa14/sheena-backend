<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\Api\ShopeeSyncController;
use App\Services\ShopeeService;

class SyncShopeeProducts extends Command
{
    protected $signature = 'sync:shopee-products';

    protected $description = 'Sync Shopee products automatically';

    public function handle()
    {
        $controller = new ShopeeSyncController(new ShopeeService());

        $response = $controller->syncProducts();

        $this->info('Shopee products synced successfully');

        $this->line($response->getContent());

        return Command::SUCCESS;
    }
}
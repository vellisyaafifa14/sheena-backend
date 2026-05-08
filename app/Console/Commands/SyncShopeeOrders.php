<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\Api\ShopeeSyncController;
use App\Services\ShopeeService;

class SyncShopeeOrders extends Command
{
    protected $signature = 'sync:shopee-orders';

    protected $description = 'Sync Shopee orders automatically';

    public function handle()
    {
        $controller = new ShopeeSyncController(new ShopeeService());

        $response = $controller->syncOrders();

        $this->info('Shopee orders synced successfully');

        $this->line($response->getContent());

        return Command::SUCCESS;
    }
}
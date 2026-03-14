<?php

namespace Database\Seeders;

use App\Models\Channel;
use Illuminate\Database\Seeder;

class ChannelSeeder extends Seeder
{
    public function run(): void
    {
        Channel::updateOrCreate(
            ['channel_name' => 'Shopee'],
            ['channel_name' => 'Shopee']
        );

        Channel::updateOrCreate(
            ['channel_name' => 'TikTok Shop'],
            ['channel_name' => 'TikTok Shop']
        );
    }
}
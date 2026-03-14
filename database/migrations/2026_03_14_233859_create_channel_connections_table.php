<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('channel_connections', function (Blueprint $table) {
            $table->id('id_connection');
            $table->unsignedBigInteger('id_channel');
            $table->string('shop_id')->nullable();
            $table->string('shop_name')->nullable();
            $table->text('access_token')->nullable();
            $table->text('refresh_token')->nullable();
            $table->enum('status_connection', ['connected', 'disconnected', 'expired'])
                ->default('disconnected');
            $table->timestamp('last_product_synced_at')->nullable();
            $table->timestamp('last_order_synced_at')->nullable();
            $table->timestamps();

            $table->foreign('id_channel')
                ->references('id_channel')
                ->on('channels')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('channel_connections');
    }
};
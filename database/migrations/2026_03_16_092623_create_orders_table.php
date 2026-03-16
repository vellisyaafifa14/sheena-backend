<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id('id_order');

            $table->unsignedBigInteger('id_connection');

            $table->string('channel_order_id')->unique();
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->string('order_status', 50)->default('pending');
            $table->timestamp('ordered_at_channel')->nullable();
        
            $table->timestamps();

            $table->foreign('id_connection')
                ->references('id_connection')
                ->on('channel_connections')
                ->cascadeOnDelete();  
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};

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
        Schema::create('order_items', function (Blueprint $table) {
            $table->id('id_order_item');

            $table->unsignedBigInteger('id_order');
            $table->unsignedBigInteger('id_listing');

            $table->decimal('price_snapshot', 15, 2)->default(0);
            $table->string('title_snapshot');
            $table->integer('quantity')->default(1);

            $table->timestamps();

             $table->foreign('id_order')
                ->references('id_order')
                ->on('orders')
                ->cascadeOnDelete();

            $table->foreign('id_listing')
                ->references('id_listing')
                ->on('product_listings')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};

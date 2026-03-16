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
        Schema::create('product_listings', function (Blueprint $table) {
            $table->id('id_listing');
            $table->timestamps();

            $table->unsignedBigInteger('id_product');
            $table->unsignedBigInteger('id_connection');

            $table->string('channel_product_id')->nullable();
            $table->string('variant_id')->nullable();
            $table->string('variant_name')->nullable();
            $table->string('channel_sku')->nullable();

            $table->decimal('price', 15, 2)->default(0);
            $table->integer('stock')->default(0);

            $table->string('product_url')->nullable();
            $table->string('listing_status', 50)->default('active');
            
            $table->foreign('id_product')
                ->references('id_product')
                ->on('products')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_listings');
    }
};

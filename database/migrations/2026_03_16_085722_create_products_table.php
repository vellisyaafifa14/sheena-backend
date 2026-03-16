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
        Schema::create('products', function (Blueprint $table) {
            $table->id('id_product');
            $table->unsignedBigInteger('id_category')->nullable();

            $table->string('product_name', 150);
            $table->string('product_slug')->unique();
            $table->text('product_desc')->nullable();
            $table->string('product_image')->nullable();

            $table->boolean('is_published')->default(true);
            $table->timestamps();

            $table->foreign('id_category')
                ->references('id_category')
                ->on('categories')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};

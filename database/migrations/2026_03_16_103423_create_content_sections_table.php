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
        Schema::create('content_sections', function (Blueprint $table) {
            $table->id('id_section');
            $table->unsignedBigInteger('id_page');

            $table->string('section_name', 100);
            $table->string('section_key')->nullable();
            $table->text('section_content')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->foreign('id_page')
                ->references('id_page')
                ->on('site_pages')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('content_sections');
    }
};

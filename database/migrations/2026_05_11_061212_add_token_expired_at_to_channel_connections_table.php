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
    Schema::table('channel_connections', function (Blueprint $table) {
        $table->timestamp('access_token_expired_at')->nullable()->after('access_token');
        $table->timestamp('refresh_token_expired_at')->nullable()->after('refresh_token');
    });
}

public function down(): void
{
    Schema::table('channel_connections', function (Blueprint $table) {
        $table->dropColumn([
            'access_token_expired_at',
            'refresh_token_expired_at',
        ]);
    });
}
};

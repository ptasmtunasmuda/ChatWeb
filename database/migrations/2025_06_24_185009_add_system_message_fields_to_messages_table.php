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
        Schema::table('messages', function (Blueprint $table) {
            $table->boolean('is_system')->default(false)->after('is_edited');
            $table->string('system_type')->nullable()->after('is_system');
            $table->json('system_data')->nullable()->after('system_type');
            
            // Make user_id nullable for system messages
            $table->foreignId('user_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropColumn(['is_system', 'system_type', 'system_data']);
            
            // Revert user_id to not nullable (but keep nullable for system messages)
            // $table->foreignId('user_id')->nullable(false)->change();
        });
    }
};

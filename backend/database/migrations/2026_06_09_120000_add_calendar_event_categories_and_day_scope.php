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
        Schema::table('calendar', function (Blueprint $table): void {
            $table->foreignId('event_id')->nullable()->constrained('events')->nullOnDelete();
            $table->string('categories', 255)->default('All');
            $table->string('day_scope', 20)->default('full_day');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('calendar', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('event_id');
            $table->dropColumn(['categories', 'day_scope']);
        });
    }
};

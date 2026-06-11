<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table): void {
            $table->string('event_type', 40)->default('event');
            $table->date('event_start_date')->nullable();
            $table->date('event_end_date')->nullable();
            $table->string('day_scope', 20)->default('full_day');
        });

        DB::table('events')
            ->whereNull('event_start_date')
            ->update([
                'event_start_date' => DB::raw('event_date'),
                'event_end_date' => DB::raw('event_date'),
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table): void {
            $table->dropColumn(['event_type', 'event_start_date', 'event_end_date', 'day_scope']);
        });
    }
};

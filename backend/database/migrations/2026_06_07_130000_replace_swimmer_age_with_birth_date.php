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
        Schema::table('swimmers', function (Blueprint $table) {
            if (!Schema::hasColumn('swimmers', 'birth_date')) {
                $table->date('birth_date')->nullable()->after('last_name');
            }
        });

        if (Schema::hasColumn('swimmers', 'age')) {
            DB::statement(
                "UPDATE swimmers SET birth_date = MAKEDATE(YEAR(CURDATE()) - age, 1) WHERE birth_date IS NULL AND age IS NOT NULL"
            );

            Schema::table('swimmers', function (Blueprint $table) {
                $table->dropColumn('age');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('swimmers', function (Blueprint $table) {
            if (!Schema::hasColumn('swimmers', 'age')) {
                $table->unsignedTinyInteger('age')->nullable()->after('last_name');
            }
        });

        if (Schema::hasColumn('swimmers', 'birth_date')) {
            DB::statement(
                'UPDATE swimmers SET age = TIMESTAMPDIFF(YEAR, birth_date, CURDATE()) WHERE age IS NULL AND birth_date IS NOT NULL'
            );

            Schema::table('swimmers', function (Blueprint $table) {
                $table->dropColumn('birth_date');
            });
        }
    }
};

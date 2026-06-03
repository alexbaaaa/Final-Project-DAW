<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The database connection that should be used by the migration.
     */
    protected $connection = 'auth_pgsql';

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('user_admins', function (Blueprint $table) {
            $table->id();
            $table->string('first_name', 100);
            $table->string('last_name', 100);
            $table->string('password');
            $table->unsignedBigInteger('swimmer_id')->nullable();
            $table->timestamps();

            $table->index('swimmer_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_admins');
    }
};

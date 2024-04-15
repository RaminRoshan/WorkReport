<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->datetime('done_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {

    }
};

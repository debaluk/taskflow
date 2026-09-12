<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->foreignId('space_id')
                ->nullable()
                ->change();

            $table->foreignId('list_id')
                ->nullable()
                ->change();
        });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->foreignId('space_id')
                ->nullable(false)
                ->change();

            $table->foreignId('list_id')
                ->nullable(false)
                ->change();
        });
    }
};
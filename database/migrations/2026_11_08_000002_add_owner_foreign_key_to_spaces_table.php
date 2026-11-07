<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('spaces', function (Blueprint $table) {
            $table->foreign('owner_id')
                ->references('id')
                ->on('users')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('spaces', function (Blueprint $table) {
            $table->dropForeign(['owner_id']);
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('spaces', function (Blueprint $table) {
            $table->unsignedBigInteger('owner_id')
                ->nullable()
                ->after('workspace_id');

            $table->index('owner_id');
        });
    }

    public function down(): void
    {
        Schema::table('spaces', function (Blueprint $table) {
            $table->dropIndex(['owner_id']);
            $table->dropColumn('owner_id');
        });
    }
};
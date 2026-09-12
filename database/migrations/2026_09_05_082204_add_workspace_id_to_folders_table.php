<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('folders', function (Blueprint $table) {
            $table->foreignId('workspace_id')
                ->nullable()
                ->after('uuid')
                ->constrained('workspaces')
                ->nullOnDelete();

            $table->index(['workspace_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::table('folders', function (Blueprint $table) {
            $table->dropForeign(['workspace_id']);
            $table->dropIndex(['workspace_id', 'is_active']);
            $table->dropColumn('workspace_id');
        });
    }
};
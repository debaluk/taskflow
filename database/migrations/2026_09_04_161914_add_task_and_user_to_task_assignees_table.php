<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('task_assignees', function (Blueprint $table) {
            $table->foreignId('task_id')
                ->after('id')
                ->constrained('tasks')
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->after('task_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->unique(['task_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::table('task_assignees', function (Blueprint $table) {
            $table->dropForeign(['task_id']);
            $table->dropForeign(['user_id']);
            $table->dropUnique(['task_assignees_task_id_user_id_unique']);
            $table->dropColumn(['task_id', 'user_id']);
        });
    }
};

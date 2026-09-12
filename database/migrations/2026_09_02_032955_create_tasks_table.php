<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            // Hierarchy
            $table->foreignId('workspace_id')
                ->constrained('workspaces')
                ->cascadeOnDelete();

            $table->foreignId('space_id')
                ->constrained('spaces')
                ->cascadeOnDelete();

            $table->foreignId('folder_id')
                ->nullable()
                ->constrained('folders')
                ->nullOnDelete();

            $table->foreignId('list_id')
                ->constrained('task_lists')
                ->cascadeOnDelete();

            $table->foreignId('parent_id')
                ->nullable()
                ->constrained('tasks')
                ->nullOnDelete();

            // Basic
            $table->string('title');
            $table->text('description')->nullable();

            $table->enum('task_type', ['task', 'milestone'])
                ->default('task');

            // Status & priority
            $table->string('status')->default('open');
            $table->string('priority')->default('normal');

            // Schedule
            $table->dateTime('start_date')->nullable();
            $table->dateTime('due_date')->nullable();

            // Progress
            $table->unsignedTinyInteger('progress')->default(0);

            // Time tracking
            $table->unsignedInteger('estimated_minutes')->nullable();
            $table->unsignedInteger('actual_minutes')->nullable();

            // Sorting
            $table->unsignedInteger('position')->default(0);

            // Audit
            $table->foreignId('created_by')
                ->constrained('users')
                ->restrictOnDelete();

            $table->foreignId('updated_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->dateTime('completed_at')->nullable();

            // Optimistic locking
            $table->unsignedInteger('version')->default(1);

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['workspace_id', 'list_id']);
            $table->index(['workspace_id', 'status']);
            $table->index(['workspace_id', 'priority']);
            $table->index(['start_date', 'due_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('task_lists', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            $table->foreignId('space_id')
                ->constrained('spaces')
                ->cascadeOnDelete();

            $table->foreignId('folder_id')
                ->nullable()
                ->constrained('folders')
                ->nullOnDelete();

            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->unsignedInteger('position')->default(0);
            $table->boolean('is_active')->default(true);

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['space_id', 'slug']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('task_lists');
    }
};
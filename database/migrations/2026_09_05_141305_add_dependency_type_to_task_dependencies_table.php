<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
	{
		Schema::table('task_dependencies', function (Blueprint $table) {
			$table->string('dependency_type', 2)
				->default('FS')
				->after('depends_on_task_id');
		});
	}

    /**
     * Reverse the migrations.
     */
    public function down(): void
	{
		Schema::table('task_dependencies', function (Blueprint $table) {
			$table->dropColumn('dependency_type');
		});
	}
};

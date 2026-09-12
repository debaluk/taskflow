<?php

namespace App\Console\Commands;

use App\Models\Space;
use App\Models\TaskList;
use App\Models\Workspace;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ImportIpas extends Command
{
    protected $signature = 'task:import-ipas
                            {file : Path CSV import}
                            {--workspace=WIPAS v2 : Nama workspace}';

    protected $description = 'Import struktur IPAS: Workspace → Space → List';

    public function handle(): int
    {
        $file = $this->argument('file');
        $workspaceName = $this->option('workspace');

        if (! file_exists($file)) {
            $this->error("File CSV tidak ditemukan: {$file}");
            return self::FAILURE;
        }

        $this->info('Menghapus data import IPAS lama...');

        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        try {
            DB::table('task_assignees')->truncate();
            DB::table('tasks')->truncate();
            DB::table('task_lists')->truncate();
            DB::table('folders')->truncate();
            DB::table('spaces')->truncate();
        } finally {
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        }

        $this->info('Data lama berhasil dikosongkan.');

        /*
        |--------------------------------------------------------------------------
        | OWNER
        |--------------------------------------------------------------------------
        */

        $owner = User::query()->orderBy('id')->first();

        if (! $owner) {
            $this->error('Tidak ada user di database.');
            return self::FAILURE;
        }

        /*
        |--------------------------------------------------------------------------
        | WORKSPACE
        |--------------------------------------------------------------------------
        */

        $workspace = Workspace::withTrashed()
            ->where('name', $workspaceName)
            ->first();

        if ($workspace) {
            if ($workspace->trashed()) {
                $workspace->restore();
            }

            $workspace->update([
                'is_active' => true,
                'owner_id' => $owner->id,
            ]);
        } else {
            $workspace = Workspace::create([
                'uuid' => (string) Str::uuid(),
                'name' => $workspaceName,
                'slug' => Str::slug($workspaceName),
                'description' => 'IPAS v2',
                'owner_id' => $owner->id,
                'is_active' => true,
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | WORKSPACE MEMBER
        |--------------------------------------------------------------------------
        */

        DB::table('workspace_members')->updateOrInsert(
            [
                'workspace_id' => $workspace->id,
                'user_id' => $owner->id,
            ],
            [
                'is_active' => true,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | READ CSV
        |--------------------------------------------------------------------------
        */

        $handle = fopen($file, 'r');

        if (! $handle) {
            $this->error('CSV tidak dapat dibuka.');
            return self::FAILURE;
        }

        $header = fgetcsv($handle);

        if (! $header) {
            fclose($handle);

            $this->error('CSV kosong.');
            return self::FAILURE;
        }

        $header = array_map(
            fn ($value) => trim((string) $value),
            $header
        );

        $column = array_flip($header);

        foreach ([
            'List Name',
            'Task Name',
            'Task Content',
            'Status',
            'Priority',
            'Tags',
        ] as $required) {
            if (! array_key_exists($required, $column)) {
                fclose($handle);

                $this->error("Kolom CSV tidak ditemukan: {$required}");

                return self::FAILURE;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | IMPORT
        |
        | CSV:
        |
        | List Name = SPACE
        | Task Name = LIST
        |
        |--------------------------------------------------------------------------
        */

        $spaces = [];
        $positions = [];

        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) < count($header)) {
                continue;
            }

            $spaceName = trim((string) ($row[$column['List Name']] ?? ''));
            $listName = trim((string) ($row[$column['Task Name']] ?? ''));

            if ($spaceName === '' || $listName === '') {
                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | SPACE
            |--------------------------------------------------------------------------
            */

            if (! isset($spaces[$spaceName])) {
                $position = $positions['space'] ?? 0;

                $space = Space::create([
                    'uuid' => (string) Str::uuid(),
                    'workspace_id' => $workspace->id,
                    'name' => $spaceName,
                    'slug' => Str::slug($spaceName),
                    'description' => 'IPAS v2 - ' . $spaceName,
                    'position' => $position,
                    'is_active' => true,
                ]);

                $spaces[$spaceName] = $space;
                $positions['space'] = $position + 1;
                $positions['list'][$spaceName] = 0;
            }

            $space = $spaces[$spaceName];

            /*
            |--------------------------------------------------------------------------
            | LIST
            |--------------------------------------------------------------------------
            */

            $exists = TaskList::query()
                ->where('space_id', $space->id)
                ->where('name', $listName)
                ->exists();

            if ($exists) {
                continue;
            }

            $listPosition = $positions['list'][$spaceName] ?? 0;

            TaskList::create([
                'uuid' => (string) Str::uuid(),
                'space_id' => $space->id,
                'folder_id' => null,
                'name' => $listName,
                'slug' => Str::slug($listName),
                'description' => trim(
                    (string) ($row[$column['Task Content']] ?? '')
                ),
                'position' => $listPosition,
                'is_active' => true,
            ]);

            $positions['list'][$spaceName] = $listPosition + 1;
        }

        fclose($handle);

        /*
        |--------------------------------------------------------------------------
        | REMOVE DEFAULT / ORPHAN GENERAL LIST
        |--------------------------------------------------------------------------
        */

        TaskList::query()
            ->where('name', 'General')
            ->delete();

        /*
        |--------------------------------------------------------------------------
        | SESSION WORKSPACE
        |--------------------------------------------------------------------------
        */

        session(['workspace_id' => $workspace->id]);

        /*
        |--------------------------------------------------------------------------
        | RESULT
        |--------------------------------------------------------------------------
        */

        $spaceCount = Space::where(
            'workspace_id',
            $workspace->id
        )->count();

        $listCount = TaskList::whereIn(
            'space_id',
            Space::where('workspace_id', $workspace->id)
                ->pluck('id')
        )->count();

        $this->newLine();

        $this->info('========================================');
        $this->info('IPAS IMPORT SELESAI');
        $this->info('========================================');
        $this->info("Workspace : {$workspace->name}");
        $this->info("Spaces    : {$spaceCount}");
        $this->info("Lists     : {$listCount}");
        $this->info('Tasks     : 0');
        $this->info('Folders   : 0');
        $this->info('========================================');

        return self::SUCCESS;
    }
}

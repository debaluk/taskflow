<?php

namespace App\Http\Controllers;

use App\Models\Folder;
use App\Models\Space;
use App\Models\Workspace;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class FolderController extends Controller
{
    public function index(
        Workspace $workspace,
        Space $space
    ) {
        $this->authorizeSpaceView($workspace, $space);

        abort_unless(
            $space->workspace_id === $workspace->id,
            404
        );

        $folders = $space->folders()
            ->withCount('children')
            ->withCount('taskLists')
            ->whereNull('parent_id')
            ->orderBy('name')
            ->get();

        return view('folders.index', compact(
            'workspace',
            'space',
            'folders'
        ));
    }

    public function store(
        Request $request,
        Workspace $workspace,
        Space $space
    ) {
        $this->authorizeSpaceManagement($workspace, $space);

        abort_unless(
            $space->workspace_id === $workspace->id,
            404
        );

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'parent_id' => ['nullable', 'integer'],
        ]);

        $parent = null;

        if (! empty($data['parent_id'])) {
            $parent = Folder::query()
                ->where('id', $data['parent_id'])
                ->where('workspace_id', $workspace->id)
                ->where('space_id', $space->id)
                ->firstOrFail();
        }

        Folder::create([
            'uuid' => (string) Str::uuid(),
            'workspace_id' => $workspace->id,
            'space_id' => $space->id,
            'parent_id' => $parent?->id,
            'name' => $data['name'],
            'slug' => $this->uniqueFolderSlug($space, $data['name']),
            'description' => $data['description'] ?? null,
            'is_active' => true,
        ]);

        return back()->with(
            'success',
            'Folder berhasil ditambahkan.'
        );
    }

    public function update(
        Request $request,
        Workspace $workspace,
        Space $space,
        Folder $folder
    ) {
        $this->authorizeSpaceManagement($workspace, $space);

        $this->authorizeFolder(
            $workspace,
            $space,
            $folder
        );

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'parent_id' => ['nullable', 'integer'],
        ]);

        $parentId = null;

        if (! empty($data['parent_id'])) {
            abort_if(
                (int) $data['parent_id'] === (int) $folder->id,
                422,
                'Folder tidak dapat menjadi parent dirinya sendiri.'
            );

            $parent = Folder::query()
                ->where('id', $data['parent_id'])
                ->where('workspace_id', $workspace->id)
                ->where('space_id', $space->id)
                ->firstOrFail();

            $parentId = $parent->id;
        }

        $folder->update([
            'parent_id' => $parentId,
            'name' => $data['name'],
            'slug' => $this->uniqueFolderSlug($space, $data['name'], $folder->id),
            'description' => $data['description'] ?? null,
        ]);

        return back()->with(
            'success',
            'Folder berhasil diperbarui.'
        );
    }

    public function toggle(
        Workspace $workspace,
        Space $space,
        Folder $folder
    ) {
        $this->authorizeSpaceManagement($workspace, $space);

        $this->authorizeFolder(
            $workspace,
            $space,
            $folder
        );

        $folder->update([
            'is_active' => ! $folder->is_active,
        ]);

        return back()->with(
            'success',
            'Status Folder berhasil diubah.'
        );
    }

    public function destroy(
        Workspace $workspace,
        Space $space,
        Folder $folder
    ) {
        $this->authorizeSpaceManagement($workspace, $space);

        $this->authorizeFolder(
            $workspace,
            $space,
            $folder
        );

        $folder->delete();

        return back()->with(
            'success',
            'Folder berhasil dihapus.'
        );
    }

    /**
     * Buat slug unik dalam Space agar duplikasi nama tidak menjadi SQL error.
     */
    private function uniqueFolderSlug(
        Space $space,
        string $name,
        ?int $ignoreFolderId = null
    ): string {
        $baseSlug = Str::slug($name);

        if ($baseSlug === '') {
            $baseSlug = 'folder';
        }

        $slug = $baseSlug;
        $counter = 2;

        while (
            Folder::query()
                ->where('space_id', $space->id)
                ->where('slug', $slug)
                ->when($ignoreFolderId, function ($query) use ($ignoreFolderId) {
                    $query->where('id', '!=', $ignoreFolderId);
                })
                ->exists()
        ) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    private function authorizeSpaceView(
        Workspace $workspace,
        Space $space
    ): void {
        abort_unless(
            (int) $workspace->id === 1 &&
            (int) $space->workspace_id === (int) $workspace->id &&
            $space->isAccessibleBy((int) auth()->id()),
            403
        );
    }

    private function authorizeSpaceManagement(
        Workspace $workspace,
        Space $space
    ): void {
        $this->authorizeSpaceView($workspace, $space);

        abort_unless(
            $space->isOwnedBy((int) auth()->id()),
            403
        );
    }

    private function authorizeFolder(
        Workspace $workspace,
        Space $space,
        Folder $folder
    ): void {
        abort_unless(
            $space->workspace_id === $workspace->id,
            404
        );

        abort_unless(
            $folder->workspace_id === $workspace->id &&
            $folder->space_id === $space->id,
            404
        );
    }
}
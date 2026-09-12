<?php

namespace App\Http\Controllers;

use App\Models\Space;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SpaceController extends Controller
{
    private int $workspaceId = 1;

    public function index()
    {
        $spaces = Space::query()
            ->where('workspace_id', $this->workspaceId)
            ->withCount('folders')
            ->withCount('taskLists')
            ->orderBy('name')
            ->get();

        return view('spaces.index', compact('spaces'));
    }

    public function show(Space $space)
    {
        abort_unless(
            (int) $space->workspace_id === $this->workspaceId,
            404
        );

        return redirect()->route('tasks.index', [
            'space_id' => $space->id,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        $baseSlug = Str::slug($data['name']);

        if ($baseSlug === '') {
            $baseSlug = 'space';
        }

        do {
            $slug = $baseSlug . '-' . Str::lower(Str::random(6));

            $exists = Space::query()
                ->where('workspace_id', $this->workspaceId)
                ->where('slug', $slug)
                ->exists();

        } while ($exists);

        Space::create([
            'uuid' => (string) Str::uuid(),
            'workspace_id' => $this->workspaceId,
            'owner_id' => auth()->id(),
            'name' => $data['name'],
            'slug' => $slug,
            'description' => $data['description'] ?? null,
            'is_active' => true,
        ]);

        return back()->with(
            'success',
            'Space berhasil ditambahkan.'
        );
    }

    public function update(Request $request, Space $space)
    {
        $this->authorizeSpace($space);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        /*
         * Nama boleh sama.
         *
         * Slug hanya dibuat ulang jika nama berubah.
         * Jika nama tidak berubah, slug lama tetap digunakan.
         */
        $slug = $space->slug;

        if ($space->name !== $data['name']) {

            $baseSlug = Str::slug($data['name']);

            if ($baseSlug === '') {
                $baseSlug = 'space';
            }

            do {
                $slug = $baseSlug . '-' . Str::lower(Str::random(6));

                $exists = Space::query()
                    ->where('workspace_id', $this->workspaceId)
                    ->where('slug', $slug)
                    ->where('id', '!=', $space->id)
                    ->exists();

            } while ($exists);
        }

        $space->update([
            'name' => $data['name'],
            'slug' => $slug,
            'description' => $data['description'] ?? null,
        ]);

        return back()->with(
            'success',
            'Space berhasil diperbarui.'
        );
    }

    public function toggle(Space $space)
    {
        $this->authorizeSpace($space);

        $space->update([
            'is_active' => ! $space->is_active,
        ]);

        return back()->with(
            'success',
            'Status Space berhasil diubah.'
        );
    }

    public function destroy(Space $space)
    {
        $this->authorizeSpace($space);

        $taskCount = \App\Models\Task::query()
            ->where('space_id', $space->id)
            ->count();

        if ($taskCount > 0) {
            return back()->with(
                'error',
                "Space \"{$space->name}\" tidak dapat dihapus karena masih memiliki {$taskCount} Task. Hapus atau pindahkan Task terlebih dahulu."
            );
        }

        $space->delete();

        return back()->with(
            'success',
            "Space \"{$space->name}\" berhasil dihapus."
        );
    }

    private function authorizeSpace(Space $space): void
    {
        abort_unless(
            (int) $space->workspace_id === $this->workspaceId &&
            (
                $space->owner_id === null ||
                (int) $space->owner_id === (int) auth()->id()
            ),
            403
        );
    }
}
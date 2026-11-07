<?php

namespace App\Http\Controllers;

use App\Models\Space;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SpaceMemberController extends Controller
{
    private int $workspaceId = 1;

    public function index(Space $space)
    {
        $this->authorizeOwner($space);

        $members = $space->memberships()
            ->with(['user', 'inviter'])
            ->get();

        $availableUsers = User::query()
            ->whereDoesntHave('spaces', function ($query) use ($space) {
                $query->where('spaces.id', $space->id);
            })
            ->where('id', '!=', $space->owner_id)
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        return view('spaces.members', compact('space', 'members', 'availableUsers'));
    }

    public function store(Request $request, Space $space)
    {
        $this->authorizeOwner($space);

        $data = $request->validate([
            'user_id' => [
                'required',
                'integer',
                Rule::exists('users', 'id'),
                Rule::unique('space_members', 'user_id')
                    ->where(fn ($query) => $query->where('space_id', $space->id)),
                Rule::notIn([$space->owner_id]),
            ],
        ], [
            'user_id.unique' => 'Pengguna tersebut sudah menjadi anggota Space ini.',
            'user_id.not_in' => 'Owner Space tidak perlu ditambahkan sebagai anggota.',
        ]);

        $space->members()->attach($data['user_id'], [
            'invited_by' => auth()->id(),
        ]);

        return back()->with('success', 'Anggota berhasil ditambahkan.');
    }

    public function destroy(Space $space, User $user)
    {
        $this->authorizeOwner($space);

        abort_unless(
            $space->members()->whereKey($user->id)->exists(),
            404
        );

        $space->members()->detach($user->id);

        return back()->with('success', 'Anggota berhasil dikeluarkan dari Space.');
    }

    private function authorizeOwner(Space $space): void
    {
        abort_unless(
            (int) $space->workspace_id === $this->workspaceId &&
            (int) $space->owner_id === (int) auth()->id(),
            403
        );
    }
}

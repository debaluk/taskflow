<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceMember;
use Illuminate\Http\Request;

class WorkspaceMemberController extends Controller
{
    public function index(Workspace $workspace)
    {
        $members = $workspace->members()
            ->with('user')
            ->get();

        $users = User::orderBy('name')->get();

        return view('workspaces.members', compact(
            'workspace',
            'members',
            'users'
        ));
    }

    public function store(Request $request, Workspace $workspace)
    {
        $data = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'role' => ['required', 'in:admin,member'],
        ]);

        $exists = WorkspaceMember::where('workspace_id', $workspace->id)
            ->where('user_id', $data['user_id'])
            ->exists();

        if ($exists) {
            return back()
                ->with('error', 'User sudah menjadi member workspace ini.')
                ->withInput();
        }

        WorkspaceMember::create([
            'workspace_id' => $workspace->id,
            'user_id' => $data['user_id'],
            'role' => $data['role'],
            'is_active' => true,
        ]);

        return redirect()
            ->route('workspace.members', $workspace)
            ->with('success', 'Member berhasil ditambahkan.');
    }

    public function toggle(
        Workspace $workspace,
        WorkspaceMember $member
    ) {
        abort_unless(
            $member->workspace_id === $workspace->id,
            404
        );

        $member->update([
            'is_active' => ! $member->is_active,
        ]);

        return back()->with(
            'success',
            'Status member berhasil diubah.'
        );
    }
}
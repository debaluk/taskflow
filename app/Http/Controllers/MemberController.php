<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class MemberController extends Controller
{
    public function index()
	{
		$members = Member::with(['user', 'inviter'])
			->latest()
			->paginate(20);

		return view('members.index', compact('members'));
	}

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => [
                'required',
                'exists:users,id',
                Rule::unique('members', 'user_id'),
            ],
        ]);

        Member::create([
            'user_id' => $validated['user_id'],
            'invited_by' => Auth::id(),
        ]);

        return redirect()
            ->route('members.index')
            ->with('success', 'Member berhasil ditambahkan.');
    }

    public function destroy(Member $member)
    {
        $member->delete();

        return redirect()
            ->route('members.index')
            ->with('success', 'Member berhasil dihapus.');
    }

	public function searchUsers(Request $request)
	{
		$search = trim($request->get('q', ''));

		$users = User::query()
			->whereDoesntHave('member')
			->when($search !== '', function ($query) use ($search) {
				$query->where(function ($query) use ($search) {
					$query->where('name', 'like', "%{$search}%")
						->orWhere('email', 'like', "%{$search}%");
				});
			})
			->orderBy('name')
			->limit(20)
			->get([
				'id',
				'name',
				'email',
			]);

		return response()->json($users);
	}
}

<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use Illuminate\Http\Request;

class ActivityController extends Controller
{
    public function index(Request $request)
    {
        $workspaceIds = auth()->user()
            ->workspaceMembers()
            ->where('is_active', true)
            ->pluck('workspace_id');

        $activities = Activity::with([
                'user',
                'workspace',
                'subject',
            ])
            ->whereIn('workspace_id', $workspaceIds)
            ->latest()
            ->paginate(30);

        return view('manage.activity', compact('activities'));
    }
}
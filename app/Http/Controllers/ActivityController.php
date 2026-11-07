<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Space;
use App\Models\Task;
use Illuminate\Http\Request;

class ActivityController extends Controller
{
    public function index(Request $request)
    {
        $accessibleSpaceIds = Space::query()
            ->where('workspace_id', 1)
            ->where('is_active', true)
            ->accessibleTo((int) auth()->id())
            ->select('id');

        $accessibleTaskIds = Task::query()
            ->where('workspace_id', 1)
            ->whereIn('space_id', $accessibleSpaceIds)
            ->select('id');

        $activities = Activity::with([
                'user',
                'workspace',
            ])
            ->where('workspace_id', 1)
            ->where('subject_type', Task::class)
            ->whereIn('subject_id', $accessibleTaskIds)
            ->latest()
            ->paginate(30);

        return view('manage.activity', compact('activities'));
    }
}

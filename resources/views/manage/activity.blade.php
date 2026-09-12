@extends('layouts.task-manager')

@section('title', 'Activity')

@section('content')

<div class="page-header">
    <div>
        <h1>Activity</h1>
        <p>Aktivitas terbaru dari seluruh workspace yang dapat kamu akses.</p>
    </div>
</div>

<div class="panel">

    <div class="panel-head">
        <div>
            <h2>Activity Feed</h2>
            <p>Semua aktivitas workspace</p>
        </div>
    </div>

    <div class="activity-feed">

        @forelse ($activities as $activity)

            <div class="activity-item">

                <div class="activity-avatar">
                    {{ strtoupper(substr($activity->user->name ?? '?', 0, 1)) }}
                </div>

                <div class="activity-content">

                    <div class="activity-text">
                        <strong>{{ $activity->user->name ?? 'Unknown User' }}</strong>
                        {{ $activity->description }}
                    </div>

                    <div class="activity-meta">
                        <span>
                            {{ $activity->workspace->name ?? 'Unknown Workspace' }}
                        </span>

                        <span>•</span>

                        <span>
                            {{ $activity->created_at->diffForHumans() }}
                        </span>
                    </div>

                </div>

            </div>

        @empty

            <div class="empty-state">
                Belum ada aktivitas.
            </div>

        @endforelse

    </div>

    @if ($activities->hasPages())
        <div class="activity-pagination">
            {{ $activities->links() }}
        </div>
    @endif

</div>

@endsection
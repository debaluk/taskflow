<?php

namespace Tests\Feature;

use App\Models\Space;
use App\Models\SpaceMember;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class SpaceMembershipFoundationTest extends TestCase
{
    use RefreshDatabase;

    public function test_space_owner_is_mass_assignable_and_resolves_relationships(): void
    {
        $owner = User::factory()->create();
        $workspace = $this->createWorkspace($owner);

        $space = Space::create([
            'uuid' => (string) Str::uuid(),
            'workspace_id' => $workspace->id,
            'owner_id' => $owner->id,
            'name' => 'Ruang Kerja',
            'slug' => 'ruang-kerja',
            'is_active' => true,
        ]);

        $this->assertSame($owner->id, $space->fresh()->owner->id);
        $this->assertTrue($owner->ownedSpaces()->whereKey($space->id)->exists());
    }

    public function test_space_membership_records_member_and_inviter_relationships(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $workspace = $this->createWorkspace($owner);

        $space = Space::create([
            'uuid' => (string) Str::uuid(),
            'workspace_id' => $workspace->id,
            'owner_id' => $owner->id,
            'name' => 'Ruang Tim',
            'slug' => 'ruang-tim',
            'is_active' => true,
        ]);

        $membership = SpaceMember::create([
            'space_id' => $space->id,
            'user_id' => $member->id,
            'invited_by' => $owner->id,
        ]);

        $this->assertSame($member->id, $membership->user->id);
        $this->assertSame($owner->id, $membership->inviter->id);
        $this->assertTrue($space->members()->whereKey($member->id)->exists());
        $this->assertTrue($member->spaces()->whereKey($space->id)->exists());
    }

    private function createWorkspace(User $owner): Workspace
    {
        return Workspace::create([
            'uuid' => (string) Str::uuid(),
            'name' => 'Workspace Internal',
            'slug' => 'workspace-internal-' . Str::lower(Str::random(6)),
            'owner_id' => $owner->id,
            'is_active' => true,
        ]);
    }
}

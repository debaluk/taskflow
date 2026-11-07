<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class WorkspaceContextTest extends TestCase
{
    public function test_legacy_workspace_management_routes_are_disabled(): void
    {
        $this->assertFalse(Route::has('workspaces.index'));
        $this->assertFalse(Route::has('workspaces.store'));
        $this->assertFalse(Route::has('workspaces.update'));
        $this->assertFalse(Route::has('workspaces.toggle'));
        $this->assertFalse(Route::has('workspaces.destroy'));
        $this->assertFalse(Route::has('workspaces.show'));
        $this->assertFalse(Route::has('workspace.switch'));
        $this->assertFalse(Route::has('workspace.clear'));
    }

    public function test_folder_routes_remain_available_for_internal_workspace_scope(): void
    {
        $this->assertTrue(Route::has('folders.index'));
        $this->assertTrue(Route::has('folders.store'));
        $this->assertTrue(Route::has('folders.update'));
        $this->assertTrue(Route::has('folders.toggle'));
        $this->assertTrue(Route::has('folders.destroy'));
    }
}

<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class RouteDefinitionTest extends TestCase
{
    public function test_task_detail_route_is_registered_only_once(): void
    {
        $routes = collect(Route::getRoutes())
            ->filter(function ($route) {
                return $route->uri() === 'tasks/{task}' &&
                    in_array('GET', $route->methods(), true);
            });

        $this->assertCount(1, $routes);
        $this->assertSame('tasks.show', $routes->first()->getName());
    }
}

<?php

namespace Tests\Feature;

use App\Http\Controllers\VideoController;
use App\Models\Course;
use App\Models\User;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use ReflectionMethod;
use RuntimeException;
use Tests\TestCase;

class SecurityRegressionTest extends TestCase
{
    use RefreshDatabase;

    public function test_debug_and_test_publish_routes_are_not_registered(): void
    {
        $uris = collect(Route::getRoutes()->getRoutes())
            ->map(fn ($route) => $route->uri())
            ->all();

        $this->assertNotContains('debug-https', $uris);
        $this->assertNotContains('test-publish/{id}', $uris);
    }

    public function test_video_api_routes_reference_existing_controller_methods(): void
    {
        foreach (['upload', 'play', 'status'] as $method) {
            $this->assertTrue(
                method_exists(VideoController::class, $method),
                "Missing VideoController::{$method} method."
            );
            $this->assertTrue((new ReflectionMethod(VideoController::class, $method))->isPublic());
        }
    }

    public function test_video_status_route_uses_implicit_model_binding_parameter(): void
    {
        $route = collect(Route::getRoutes()->getRoutes())
            ->first(fn ($route) => $route->uri() === 'api/videos/{video}/status');

        $this->assertNotNull($route);
        $this->assertSame('api/videos/{video}/status', $route->uri());
    }

    public function test_course_policy_is_discovered_at_runtime(): void
    {
        $user = new User;
        $user->tenant_id = 'tenant-a';

        $course = new Course([
            'tenant_id' => 'tenant-a',
            'published' => true,
        ]);

        $this->assertTrue(Gate::forUser($user)->allows('view', $course));

        $course->tenant_id = 'tenant-b';

        $this->assertFalse(Gate::forUser($user)->allows('view', $course));
    }

    public function test_production_json_errors_do_not_expose_exception_details(): void
    {
        config()->set('app.debug', false);

        $request = Request::create(
            '/api/failing-endpoint',
            'GET',
            server: ['HTTP_ACCEPT' => 'application/json']
        );

        $response = app(ExceptionHandler::class)->render(
            $request,
            new RuntimeException('database password and internal path')
        );

        $this->assertSame(500, $response->getStatusCode());
        $this->assertSame(
            ['message' => 'Server Error', 'status' => 500],
            json_decode($response->getContent(), true)
        );
        $this->assertStringNotContainsString('database password', $response->getContent());
    }
}

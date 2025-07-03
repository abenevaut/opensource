<?php

namespace LaravelSqlMap\Tests\Unit;

use LaravelSqlMap\Tests\TestCase;
use LaravelSqlMap\Console\Commands\GenerateSqlMapFiles;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Foundation\Http\FormRequest;

class GenerateSqlMapFilesUnitTest extends TestCase
{
    private GenerateSqlMapFiles $command;
    private string $outputPath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->command = new GenerateSqlMapFiles();
        $this->outputPath = storage_path('test-sqlmap');

        // Ensure clean state
        if (File::exists($this->outputPath)) {
            File::deleteDirectory($this->outputPath);
        }
    }

    protected function tearDown(): void
    {
        if (File::exists($this->outputPath)) {
            File::deleteDirectory($this->outputPath);
        }

        parent::tearDown();
    }

    public function test_extract_route_parameters()
    {
        $reflection = new \ReflectionClass($this->command);
        $method = $reflection->getMethod('extractRouteParameters');
        $method->setAccessible(true);

        // Test simple parameter
        $result = $method->invoke($this->command, '/users/{id}');
        $this->assertEquals(['id'], $result);

        // Test multiple parameters
        $result = $method->invoke($this->command, '/users/{userId}/posts/{postId}');
        $this->assertEquals(['userId', 'postId'], $result);

        // Test optional parameter
        $result = $method->invoke($this->command, '/users/{id?}');
        $this->assertEquals(['id'], $result);

        // Test no parameters
        $result = $method->invoke($this->command, '/users');
        $this->assertEquals([], $result);
    }

    public function test_generate_filename()
    {
        $reflection = new \ReflectionClass($this->command);
        $method = $reflection->getMethod('generateFilename');
        $method->setAccessible(true);

        $routeInfo = [
            'uri' => '/users/{id}',
            'name' => 'users.show'
        ];

        $result = $method->invoke($this->command, $routeInfo, 'GET');
        $this->assertEquals('get_users_show.http', $result);

        // Test without route name
        $routeInfo = [
            'uri' => '/api/posts',
            'name' => null
        ];

        $result = $method->invoke($this->command, $routeInfo, 'POST');
        $this->assertEquals('post_api_posts.http', $result);

        // Test complex URI
        $routeInfo = [
            'uri' => '/admin/users/{userId}/profile',
            'name' => 'admin.users.profile'
        ];

        $result = $method->invoke($this->command, $routeInfo, 'PUT');
        $this->assertEquals('put_admin_users_profile.http', $result);
    }

    public function test_get_test_value_for_parameter()
    {
        $reflection = new \ReflectionClass($this->command);
        $method = $reflection->getMethod('getTestValueForParameter');
        $method->setAccessible(true);

        // Test known parameters
        $this->assertEquals('1', $method->invoke($this->command, 'id'));
        $this->assertEquals('test@example.com', $method->invoke($this->command, 'email'));
        $this->assertEquals('password123', $method->invoke($this->command, 'password'));

        // Test unknown parameter
        $this->assertEquals('test_value', $method->invoke($this->command, 'unknown_param'));
    }

    public function test_should_test_route()
    {
        $reflection = new \ReflectionClass($this->command);
        $method = $reflection->getMethod('shouldTestRoute');
        $method->setAccessible(true);

        // Route with parameters should be tested
        $routeInfo = [
            'uri' => '/users/{id}',
            'route_parameters' => ['id'],
            'form_parameters' => []
        ];
        $this->assertTrue($method->invoke($this->command, $routeInfo));

        // Route with form parameters should be tested
        $routeInfo = [
            'uri' => '/users',
            'route_parameters' => [],
            'form_parameters' => ['name', 'email']
        ];
        $this->assertTrue($method->invoke($this->command, $routeInfo));

        // Route without parameters should not be tested
        $routeInfo = [
            'uri' => '/users',
            'route_parameters' => [],
            'form_parameters' => []
        ];
        $this->assertFalse($method->invoke($this->command, $routeInfo));

        // Excluded routes should not be tested
        $routeInfo = [
            'uri' => 'telescope/requests',
            'route_parameters' => ['id'],
            'form_parameters' => []
        ];
        $this->assertFalse($method->invoke($this->command, $routeInfo));
    }

    public function test_generate_get_request()
    {
        $reflection = new \ReflectionClass($this->command);
        $method = $reflection->getMethod('generateGetRequest');
        $method->setAccessible(true);

        // Set base URL
        $baseUrlProperty = $reflection->getProperty('baseUrl');
        $baseUrlProperty->setAccessible(true);
        $baseUrlProperty->setValue($this->command, 'http://localhost:8000');

        $routeInfo = [
            'form_parameters' => ['search', 'filter']
        ];

        $result = $method->invoke($this->command, 'http://localhost:8000/users', $routeInfo);

        $this->assertStringContainsString('GET http://localhost:8000/users?search=test&filter=active HTTP/1.1', $result);
        $this->assertStringContainsString('Host: localhost', $result);
        $this->assertStringContainsString('User-Agent: sqlmap/', $result);
        $this->assertStringContainsString('Accept:', $result);
    }

    public function test_generate_post_request_form_data()
    {
        $reflection = new \ReflectionClass($this->command);
        $method = $reflection->getMethod('generatePostRequest');
        $method->setAccessible(true);

        // Set base URL
        $baseUrlProperty = $reflection->getProperty('baseUrl');
        $baseUrlProperty->setAccessible(true);
        $baseUrlProperty->setValue($this->command, 'http://localhost:8000');

        $routeInfo = [
            'uri' => '/users',
            'action' => 'App\\Http\\Controllers\\UserController@store',
            'form_parameters' => ['name', 'email'],
            'middleware' => []
        ];

        $result = $method->invoke($this->command, 'http://localhost:8000/users', $routeInfo, 'POST');

        $this->assertStringContainsString('POST http://localhost:8000/users HTTP/1.1', $result);
        $this->assertStringContainsString('Content-Type: application/x-www-form-urlencoded', $result);
        $this->assertStringContainsString('name=Test+User&email=test%40example.com', $result);
    }

    public function test_generate_post_request_json()
    {
        $reflection = new \ReflectionClass($this->command);
        $method = $reflection->getMethod('generatePostRequest');
        $method->setAccessible(true);

        // Set base URL
        $baseUrlProperty = $reflection->getProperty('baseUrl');
        $baseUrlProperty->setAccessible(true);
        $baseUrlProperty->setValue($this->command, 'http://localhost:8000');

        $routeInfo = [
            'uri' => 'api/users',
            'action' => 'App\\Http\\Controllers\\Api\\UserController@store',
            'form_parameters' => ['name', 'email'],
            'middleware' => ['api']
        ];

        $result = $method->invoke($this->command, 'http://localhost:8000/api/users', $routeInfo, 'POST');

        $this->assertStringContainsString('POST http://localhost:8000/api/users HTTP/1.1', $result);
        $this->assertStringContainsString('Content-Type: application/json', $result);
        $this->assertStringContainsString('"name":"Test User"', $result);
        $this->assertStringContainsString('"email":"test@example.com"', $result);
    }

    public function test_analyze_route()
    {
        // Create a mock route using Laravel's Route facade
        Route::get('/test/{id}', function ($id) {
            return response()->json(['id' => $id]);
        })->name('test.show');

        // Get the route collection and find our route
        $routes = Route::getRoutes();
        $route = null;

        foreach ($routes as $r) {
            if ($r->getName() === 'test.show') {
                $route = $r;
                break;
            }
        }

        $this->assertNotNull($route, 'Test route should be found');

        $reflection = new \ReflectionClass($this->command);
        $method = $reflection->getMethod('analyzeRoute');
        $method->setAccessible(true);

        $result = $method->invoke($this->command, $route);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('methods', $result);
        $this->assertArrayHasKey('uri', $result);
        $this->assertArrayHasKey('route_parameters', $result);
        $this->assertArrayHasKey('form_parameters', $result);
        $this->assertContains('GET', $result['methods']);
        $this->assertEquals('test/{id}', $result['uri']);
        $this->assertEquals(['id'], $result['route_parameters']);
    }
}

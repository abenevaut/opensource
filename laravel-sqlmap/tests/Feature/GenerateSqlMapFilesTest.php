<?php

namespace LaravelSqlMap\Tests\Feature;

use LaravelSqlMap\Tests\TestCase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Artisan;

class GenerateSqlMapFilesTest extends TestCase
{
    protected string $outputPath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->outputPath = storage_path('test-sqlmap');

        // Clean up before each test
        if (File::exists($this->outputPath)) {
            File::deleteDirectory($this->outputPath);
        }

        // Setup test routes with different scenarios
        $this->setupTestRoutes();
    }

    protected function tearDown(): void
    {
        // Clean up after each test
        if (File::exists($this->outputPath)) {
            File::deleteDirectory($this->outputPath);
        }

        parent::tearDown();
    }

    private function setupTestRoutes()
    {
        // Simple GET route with parameter
        Route::get('/users/{id}', function ($id) {
            return response()->json(['id' => $id]);
        })->name('users.show');

        // POST route with form data
        Route::post('/users', function (Request $request) {
            return response()->json($request->all());
        })->name('users.store');

        // API route (should generate JSON)
        Route::post('/api/users', function (Request $request) {
            return response()->json($request->all());
        })->name('api.users.store');

        // Route with multiple parameters
        Route::put('/users/{userId}/posts/{postId}', function ($userId, $postId) {
            return response()->json(['user_id' => $userId, 'post_id' => $postId]);
        })->name('users.posts.update');

        // Route that should be skipped (contains 'telescope')
        Route::get('/telescope/requests', function () {
            return 'telescope';
        });

        // Route with FormRequest (mock)
        Route::post('/users/create', [TestController::class, 'store'])->name('users.create');
    }

    public function test_command_generates_sqlmap_files_successfully()
    {
        $this->artisan('sqlmap:generate', ['--output' => $this->outputPath])
            ->expectsOutput('🔍 Analysing Laravel routes for SQLMap generation...')
            ->assertExitCode(0);

        // Verify output directory was created
        $this->assertTrue(File::exists($this->outputPath));

        // Verify HTTP files were generated
        $httpFiles = File::glob($this->outputPath . '/*.http');
        $this->assertNotEmpty($httpFiles, 'No HTTP files were generated');

        // Verify run script was generated
        $this->assertTrue(File::exists($this->outputPath . '/run_sqlmap.sh'));
        $this->assertTrue(is_executable($this->outputPath . '/run_sqlmap.sh'));

        // Verify documentation was generated
        $this->assertTrue(File::exists($this->outputPath . '/README.md'));
    }

    public function test_generated_files_have_correct_http_format()
    {
        $this->artisan('sqlmap:generate', ['--output' => $this->outputPath])
            ->assertExitCode(0);

        $httpFiles = File::glob($this->outputPath . '/*.http');

        foreach ($httpFiles as $file) {
            $content = File::get($file);

            // Verify HTTP format
            $this->assertStringContainsString('HTTP/1.1', $content, "File {$file} missing HTTP version");
            $this->assertStringContainsString('Host:', $content, "File {$file} missing Host header");
            $this->assertStringContainsString('User-Agent: sqlmap/', $content, "File {$file} missing SQLMap User-Agent");

            // Verify proper line endings
            $this->assertStringContainsString("\r\n", $content, "File {$file} missing proper HTTP line endings");
        }
    }

    public function test_get_requests_include_query_parameters()
    {
        $this->artisan('sqlmap:generate', ['--output' => $this->outputPath])
            ->assertExitCode(0);

        $getFiles = File::glob($this->outputPath . '/get_*.http');

        foreach ($getFiles as $file) {
            $content = File::get($file);

            if (str_contains($content, '?')) {
                // If query parameters exist, verify format
                $this->assertMatchesRegularExpression('/GET [^\s]+\?[^\s]+ HTTP\/1\.1/', $content);
            }
        }
    }

    public function test_post_requests_have_proper_content_type_and_body()
    {
        $this->artisan('sqlmap:generate', ['--output' => $this->outputPath])
            ->assertExitCode(0);

        $postFiles = File::glob($this->outputPath . '/post_*.http');

        foreach ($postFiles as $file) {
            $content = File::get($file);
            $filename = basename($file);

            $this->assertStringContainsString('Content-Type:', $content, "File {$filename} missing Content-Type");
            $this->assertStringContainsString('Content-Length:', $content, "File {$filename} missing Content-Length");

            // Check if it's JSON or form-data based on filename
            if (str_contains($filename, 'api_')) {
                $this->assertStringContainsString('application/json', $content, "API route should use JSON");
            } else {
                $this->assertStringContainsString('application/x-www-form-urlencoded', $content, "Web route should use form-data");
            }
        }
    }

    public function test_route_parameters_are_replaced_with_test_values()
    {
        $this->artisan('sqlmap:generate', ['--output' => $this->outputPath])
            ->assertExitCode(0);

        $httpFiles = File::glob($this->outputPath . '/*.http');

        foreach ($httpFiles as $file) {
            $content = File::get($file);

            // Verify no route parameters remain in URLs
            $this->assertStringNotContainsString('{', $content, "File contains unreplaced route parameters");
            $this->assertStringNotContainsString('}', $content, "File contains unreplaced route parameters");
        }
    }

    public function test_skipped_routes_are_not_generated()
    {
        $this->artisan('sqlmap:generate', ['--output' => $this->outputPath])
            ->assertExitCode(0);

        $httpFiles = File::glob($this->outputPath . '/*.http');
        $fileContents = [];

        foreach ($httpFiles as $file) {
            $fileContents[] = File::get($file);
        }

        $allContent = implode(' ', $fileContents);

        // Verify telescope route was skipped
        $this->assertStringNotContainsString('telescope', $allContent, "Telescope routes should be skipped");
    }

    public function test_run_script_contains_correct_commands()
    {
        $this->artisan('sqlmap:generate', ['--output' => $this->outputPath])
            ->assertExitCode(0);

        $scriptPath = $this->outputPath . '/run_sqlmap.sh';
        $scriptContent = File::get($scriptPath);

        // Verify script structure
        $this->assertStringContainsString('#!/bin/bash', $scriptContent);
        $this->assertStringContainsString('SQLMAP_PATH=', $scriptContent);
        $this->assertStringContainsString('OUTPUT_DIR=', $scriptContent);

        // Verify it references generated files
        $httpFiles = File::glob($this->outputPath . '/*.http');
        foreach ($httpFiles as $file) {
            $filename = basename($file);
            $this->assertStringContainsString($filename, $scriptContent, "Script should reference {$filename}");
        }
    }

    public function test_documentation_file_contains_useful_information()
    {
        $this->artisan('sqlmap:generate', ['--output' => $this->outputPath])
            ->assertExitCode(0);

        $docPath = $this->outputPath . '/README.md';
        $docContent = File::get($docPath);

        $this->assertStringContainsString('# SQLMap Files Documentation', $docContent);
        $this->assertStringContainsString('Generated on:', $docContent);
        $this->assertStringContainsString('Total files:', $docContent);
        $this->assertStringContainsString('## Usage', $docContent);
        $this->assertStringContainsString('## Files Generated', $docContent);
    }

    public function test_command_with_custom_options()
    {
        $customUrl = 'https://example.com';

        $this->artisan('sqlmap:generate', [
            '--output' => $this->outputPath,
            '--url' => $customUrl,
            '--methods' => ['GET', 'POST']
        ])->assertExitCode(0);

        $httpFiles = File::glob($this->outputPath . '/*.http');

        foreach ($httpFiles as $file) {
            $content = File::get($file);

            // Verify custom URL is used
            $this->assertStringContainsString('example.com', $content);
            $this->assertStringContainsString('Host: example.com', $content);
        }
    }

    public function test_command_with_include_exclude_patterns()
    {
        $this->artisan('sqlmap:generate', [
            '--output' => $this->outputPath,
            '--include' => ['users/*'],
            '--exclude' => ['*/posts/*']
        ])->assertExitCode(0);

        $httpFiles = File::glob($this->outputPath . '/*.http');
        $allContent = '';

        foreach ($httpFiles as $file) {
            $allContent .= File::get($file);
        }

        // Should include user routes
        $this->assertStringContainsString('/users', $allContent);

        // Should exclude post routes
        $this->assertStringNotContainsString('/posts/', $allContent);
    }

    public function test_empty_routes_returns_error()
    {
        // Créer un test avec un filtrage qui exclut toutes les routes
        // Ceci garantit que getFilteredRoutes() retourne une collection vide
        $this->artisan('sqlmap:generate', [
            '--output' => $this->outputPath,
            '--methods' => ['INVALID_METHOD'] // Méthode HTTP inexistante
        ])
            ->expectsOutput('❌ No testable routes found')
            ->assertExitCode(1);
    }

    public function test_progress_bar_is_displayed()
    {
        $this->artisan('sqlmap:generate', ['--output' => $this->outputPath])
            ->expectsOutput('🔍 Analysing Laravel routes for SQLMap generation...')
            ->assertExitCode(0);
    }
}

// Mock controller for testing FormRequest detection
class TestController
{
    public function store(TestFormRequest $request)
    {
        return response()->json($request->validated());
    }
}

// Mock FormRequest for testing
class TestFormRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'password' => 'required|min:8',
        ];
    }
}

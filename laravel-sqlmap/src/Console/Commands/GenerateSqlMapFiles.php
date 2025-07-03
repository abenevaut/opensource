<?php

namespace LaravelSqlMap\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\File;
use ReflectionClass;
use ReflectionMethod;
use Illuminate\Http\Request;
use Illuminate\Foundation\Http\FormRequest;

class GenerateSqlMapFiles extends Command
{
    protected $signature = 'sqlmap:generate 
                            {--output= : Output directory for SQLMap files}
                            {--url= : Base URL for the application}
                            {--methods=* : HTTP methods to include (default: GET,POST)}
                            {--include=* : Route patterns to include}
                            {--exclude=* : Route patterns to exclude}';

    protected $description = 'Generate SQLMap files for Laravel routes with SQL injection testing';

    private $outputPath;
    private $baseUrl;

    public function handle()
    {
        $this->outputPath = $this->option('output') ?? config('sqlmap.output_path', storage_path('sqlmap'));
        $this->baseUrl = $this->option('url') ?? config('sqlmap.base_url', config('app.url', 'http://127.0.0.1:8000'));

        $this->info("🔍 Analysing Laravel routes for SQLMap generation...");
        $this->info("📁 Output directory: {$this->outputPath}");
        $this->info("🌐 Base URL: {$this->baseUrl}");

        // Create output directory
        if (!File::exists($this->outputPath)) {
            File::makeDirectory($this->outputPath, 0755, true);
            $this->info("✅ Created output directory");
        }

        // Clean previous files
        $this->cleanOutputDirectory();

        $routes = $this->getFilteredRoutes();

        if (empty($routes)) {
            $this->error("❌ No testable routes found");
            return 1;
        }

        $this->info("🎯 Found " . count($routes) . " routes to analyze");

        $generatedFiles = 0;
        $skippedRoutes = 0;

        $progressBar = $this->output->createProgressBar(count($routes));
        $progressBar->start();

        foreach ($routes as $route) {
            try {
                $routeInfo = $this->analyzeRoute($route);

                if ($routeInfo && $this->shouldTestRoute($routeInfo)) {
                    $this->generateSqlMapFiles($routeInfo);
                    $generatedFiles++;
                } else {
                    $skippedRoutes++;
                }
            } catch (\Throwable $e) {
                $this->warn("⚠️  Skipped route {$route->uri()}: " . $e->getMessage());
                $skippedRoutes++;
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);

        // Check if no files were actually generated
        if ($generatedFiles === 0) {
            $this->error("❌ No testable routes found");
            return 1;
        }

        $this->info("✅ Generated {$generatedFiles} SQLMap files");
        $this->info("⏭️  Skipped {$skippedRoutes} routes");

        // Generate run script and documentation
        $this->generateRunScript();
        $this->generateDocumentation($generatedFiles);

        $this->info("🚀 SQLMap files generation complete!");

        return 0;
    }

    private function getFilteredRoutes()
    {
        $routes = collect(Route::getRoutes());
        $methods = $this->option('methods') ?: ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'];
        $includePatterns = $this->option('include') ?: [];
        $excludePatterns = $this->option('exclude') ?: [];

        return $routes->filter(function ($route) use ($methods, $includePatterns, $excludePatterns) {
            // Filter by HTTP methods
            $routeMethods = $route->methods();
            if (!array_intersect($routeMethods, $methods)) {
                return false;
            }

            $uri = $route->uri();

            // Apply include patterns
            if (!empty($includePatterns)) {
                $included = false;
                foreach ($includePatterns as $pattern) {
                    if (str_contains($uri, $pattern) || fnmatch($pattern, $uri)) {
                        $included = true;
                        break;
                    }
                }
                if (!$included) {
                    return false;
                }
            }

            // Apply exclude patterns
            foreach ($excludePatterns as $pattern) {
                if (str_contains($uri, $pattern) || fnmatch($pattern, $uri)) {
                    return false;
                }
            }

            return true;
        });
    }

    private function cleanOutputDirectory()
    {
        $files = File::glob($this->outputPath . '/*.http');
        foreach ($files as $file) {
            File::delete($file);
        }

        if (File::exists($this->outputPath . '/run_sqlmap.sh')) {
            File::delete($this->outputPath . '/run_sqlmap.sh');
        }
    }

    private function analyzeRoute($route)
    {
        $methods = array_intersect($route->methods(), ['GET', 'POST', 'PUT', 'PATCH', 'DELETE']);

        if (empty($methods)) {
            return null;
        }

        $uri = $route->uri();
        $action = $route->getActionName();

        // Extract route parameters from URI
        $routeParameters = $this->extractRouteParameters($uri);

        // Get controller parameters via reflection
        $controllerParameters = $this->getControllerParameters($action);

        return [
            'methods' => $methods,
            'uri' => $uri,
            'action' => $action,
            'route_parameters' => $routeParameters,
            'form_parameters' => $controllerParameters,
            'name' => $route->getName(),
            'middleware' => $route->gatherMiddleware(),
        ];
    }

    private function extractRouteParameters($uri)
    {
        preg_match_all('/\{([^}]+)\}/', $uri, $matches);
        return array_map(function($param) {
            return str_replace('?', '', $param); // Remove optional indicator
        }, $matches[1] ?? []);
    }

    private function getControllerParameters($action)
    {
        $parameters = [];

        if (!$action || $action === 'Closure' || !str_contains($action, '@')) {
            return $parameters;
        }

        try {
            [$class, $method] = explode('@', $action);

            if (!class_exists($class)) {
                return $parameters;
            }

            $reflectionMethod = new ReflectionMethod($class, $method);

            foreach ($reflectionMethod->getParameters() as $parameter) {
                $parameterType = $parameter->getType();

                if ($parameterType && !$parameterType->isBuiltin()) {
                    $typeName = $parameterType->getName();

                    if (class_exists($typeName)) {
                        $reflectionClass = new ReflectionClass($typeName);

                        // Handle FormRequest classes
                        if ($reflectionClass->isSubclassOf(FormRequest::class)) {
                            $formParameters = $this->getFormRequestParameters($reflectionClass);
                            $parameters = array_merge($parameters, $formParameters);
                        }
                        // Handle Request class
                        elseif ($reflectionClass->isSubclassOf(Request::class) || $typeName === Request::class) {
                            $parameters = array_merge($parameters, ['search', 'filter', 'sort', 'page']);
                        }
                    }
                }
            }
        } catch (\Throwable $e) {
            // Ignore reflection errors
        }

        return array_unique($parameters);
    }

    private function getFormRequestParameters(ReflectionClass $reflectionClass)
    {
        $parameters = [];

        try {
            $instance = $reflectionClass->newInstanceWithoutConstructor();

            if (method_exists($instance, 'rules')) {
                $rules = $instance->rules();
                $parameters = array_keys($rules);

                // Also try to get validation messages for hints
                if (method_exists($instance, 'messages')) {
                    $messages = $instance->messages();
                    $parameters = array_merge($parameters, array_keys($messages));
                }
            }
        } catch (\Throwable $e) {
            // Fallback to common parameters
            $parameters = ['email', 'password', 'name', 'id'];
        }

        return array_unique($parameters);
    }

    private function shouldTestRoute($routeInfo)
    {
        $uri = $routeInfo['uri'];

        // Skip routes from configuration
        $skipPatterns = array_merge(
            config('sqlmap.skip_routes', []),
            ['api/documentation', 'telescope', 'horizon', 'debugbar', '_ignition', 'livewire']
        );

        foreach ($skipPatterns as $pattern) {
            if (str_contains($uri, $pattern) || fnmatch($pattern, $uri)) {
                return false;
            }
        }

        // Only test routes that have parameters to test
        return !empty($routeInfo['route_parameters']) || !empty($routeInfo['form_parameters']);
    }

    private function generateSqlMapFiles($routeInfo)
    {
        foreach ($routeInfo['methods'] as $method) {
            $filename = $this->generateFilename($routeInfo, $method);
            $content = $this->generateSqlMapContent($routeInfo, $method);

            $filepath = $this->outputPath . '/' . $filename;
            File::put($filepath, $content);
        }
    }

    private function generateFilename($routeInfo, $method)
    {
        $uri = str_replace(['/', '{', '}', '?'], ['_', '', '', ''], $routeInfo['uri']);
        $uri = trim($uri, '_');
        $name = $routeInfo['name'] ? str_replace('.', '_', $routeInfo['name']) : $uri;

        if (empty($name) || $name === '_') {
            $name = 'root';
        }

        return strtolower($method) . '_' . $name . '.http';
    }

    private function generateSqlMapContent($routeInfo, $method)
    {
        $url = rtrim($this->baseUrl, '/') . '/' . ltrim($routeInfo['uri'], '/');

        // Replace route parameters with test values
        foreach ($routeInfo['route_parameters'] as $param) {
            $testValue = $this->getTestValueForParameter($param);
            $url = str_replace('{' . $param . '}', $testValue, $url);
            $url = str_replace('{' . $param . '?}', $testValue, $url);
        }

        if ($method === 'GET') {
            return $this->generateGetRequest($url, $routeInfo);
        } else {
            return $this->generatePostRequest($url, $routeInfo, $method);
        }
    }

    private function generateGetRequest($url, $routeInfo)
    {
        $params = [];
        foreach ($routeInfo['form_parameters'] as $param) {
            $params[] = $param . '=' . urlencode($this->getTestValueForParameter($param));
        }

        if (!empty($params)) {
            $url .= '?' . implode('&', $params);
        }

        $host = parse_url($this->baseUrl, PHP_URL_HOST) ?: '127.0.0.1';

        return "GET {$url} HTTP/1.1\r\n" .
               "Host: {$host}\r\n" .
               "User-Agent: sqlmap/1.8.4.7#dev (http://sqlmap.org)\r\n" .
               "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8\r\n" .
               "Accept-Language: en-US,en;q=0.5\r\n" .
               "Accept-Encoding: gzip, deflate\r\n" .
               "Connection: keep-alive\r\n" .
               "Cache-Control: no-cache\r\n\r\n";
    }

    private function generatePostRequest($url, $routeInfo, $method)
    {
        $params = [];
        foreach ($routeInfo['form_parameters'] as $param) {
            $params[$param] = $this->getTestValueForParameter($param);
        }

        // Determine if this should be JSON or form data
        $isJson = str_contains($routeInfo['uri'], 'api/') ||
                  str_contains($routeInfo['action'] ?? '', 'Api\\') ||
                  in_array('api', $routeInfo['middleware'] ?? []);

        $host = parse_url($this->baseUrl, PHP_URL_HOST) ?: '127.0.0.1';

        if ($isJson) {
            $body = json_encode($params, JSON_UNESCAPED_SLASHES);
            $contentType = 'application/json';
        } else {
            $body = http_build_query($params);
            $contentType = 'application/x-www-form-urlencoded';
        }

        return "{$method} {$url} HTTP/1.1\r\n" .
               "Host: {$host}\r\n" .
               "Content-Type: {$contentType}\r\n" .
               "Content-Length: " . strlen($body) . "\r\n" .
               "User-Agent: sqlmap/1.8.4.7#dev (http://sqlmap.org)\r\n" .
               "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8\r\n" .
               "Accept-Language: en-US,en;q=0.5\r\n" .
               "Accept-Encoding: gzip, deflate\r\n" .
               "Connection: keep-alive\r\n" .
               "Cache-Control: no-cache\r\n\r\n" .
               $body;
    }

    private function getTestValueForParameter($param)
    {
        $testValues = config('sqlmap.test_parameters', []);

        // Add some smart defaults based on parameter names
        $smartDefaults = [
            'id' => '1',
            'user_id' => '1',
            'email' => 'test@example.com',
            'password' => 'password123',
            'name' => 'Test User',
            'username' => 'testuser',
            'search' => 'test',
            'filter' => 'active',
            'sort' => 'created_at',
            'page' => '1',
            'limit' => '10',
            'category_id' => '1',
            'status' => 'active',
            'token' => 'test_token',
        ];

        $allValues = array_merge($smartDefaults, $testValues);

        return $allValues[$param] ?? 'test_value';
    }

    private function generateRunScript()
    {
        $sqlmapOptions = config('sqlmap.sqlmap_options', []);
        $level = $sqlmapOptions['level'] ?? 3;
        $risk = $sqlmapOptions['risk'] ?? 2;

        $scriptContent = "#!/bin/bash\n\n";
        $scriptContent .= "# SQLMap test runner for Laravel application\n";
        $scriptContent .= "# Generated on " . date('Y-m-d H:i:s') . "\n";
        $scriptContent .= "# Usage: ./run_sqlmap.sh [sqlmap_path]\n\n";

        $scriptContent .= "SQLMAP_PATH=\"\${1:-sqlmap}\"\n";
        $scriptContent .= "OUTPUT_DIR=\"./results\"\n";
        $scriptContent .= "CURRENT_DIR=\"\$(dirname \"\$0\")\"\n\n";

        $scriptContent .= "echo \"🚀 Starting SQLMap tests...\"\n";
        $scriptContent .= "echo \"📁 Output directory: \$OUTPUT_DIR\"\n";
        $scriptContent .= "echo \"🔧 SQLMap path: \$SQLMAP_PATH\"\n\n";

        $scriptContent .= "mkdir -p \"\$OUTPUT_DIR\"\n\n";

        $files = File::glob($this->outputPath . '/*.http');

        foreach ($files as $file) {
            $filename = basename($file);
            $baseName = pathinfo($filename, PATHINFO_FILENAME);

            $scriptContent .= "echo \"🎯 Testing {$filename}...\"\n";
            $scriptContent .= "\"\$SQLMAP_PATH\" -r \"\$CURRENT_DIR/{$filename}\" ";
            $scriptContent .= "--batch --level={$level} --risk={$risk} ";
            $scriptContent .= "--output-dir=\"\$OUTPUT_DIR/{$baseName}\" ";
            $scriptContent .= "--flush-session --fresh-queries ";
            $scriptContent .= "--technique=BEUSTQ --threads=5\n";
            $scriptContent .= "echo \"✅ Completed {$filename}\"\n\n";
        }

        $scriptContent .= "echo \"🎉 All SQLMap tests completed!\"\n";
        $scriptContent .= "echo \"📊 Check results in: \$OUTPUT_DIR\"\n";

        $scriptPath = $this->outputPath . '/run_sqlmap.sh';
        File::put($scriptPath, $scriptContent);
        chmod($scriptPath, 0755);

        $this->info("📜 Generated execution script: {$scriptPath}");
    }

    private function generateDocumentation($fileCount)
    {
        $docContent = "# SQLMap Files Documentation\n\n";
        $docContent .= "Generated on: " . date('Y-m-d H:i:s') . "\n";
        $docContent .= "Total files: {$fileCount}\n";
        $docContent .= "Base URL: {$this->baseUrl}\n\n";

        $docContent .= "## Usage\n\n";
        $docContent .= "1. Install SQLMap: `pip install sqlmap`\n";
        $docContent .= "2. Run all tests: `./run_sqlmap.sh`\n";
        $docContent .= "3. Run specific test: `sqlmap -r filename.http --batch`\n\n";

        $docContent .= "## Configuration\n\n";
        $docContent .= "- Level: " . (config('sqlmap.sqlmap_options.level') ?? 3) . "\n";
        $docContent .= "- Risk: " . (config('sqlmap.sqlmap_options.risk') ?? 2) . "\n";
        $docContent .= "- CSRF bypass: " . (config('sqlmap.disable_csrf') ? 'enabled' : 'disabled') . "\n\n";

        $docContent .= "## Files Generated\n\n";

        $files = File::glob($this->outputPath . '/*.http');
        foreach ($files as $file) {
            $filename = basename($file);
            $docContent .= "- `{$filename}`\n";
        }

        $docPath = $this->outputPath . '/README.md';
        File::put($docPath, $docContent);

        $this->info("📚 Generated documentation: {$docPath}");
    }
}

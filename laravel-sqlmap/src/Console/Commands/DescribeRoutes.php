<?php

namespace App\Console\Commands;

use Illuminate\Foundation\Console\RouteListCommand;
use Symfony\Component\Console\Attribute\AsCommand;
use ReflectionClass;
use ReflectionMethod;
use ReflectionParameter;
use Illuminate\Support\Facades\File;
use Illuminate\Http\Request;
use Illuminate\Foundation\Http\FormRequest;

#[AsCommand(name: 'sqlmap:describe-routes')]
class DescribeRoutes extends RouteListCommand
{
    protected $name = 'sqlmap:describe-routes';
    protected $description = 'Generate SQLMap files for all Laravel routes with SQL injection testing';

    private $outputPath;
    private $baseUrl;

    public function handle()
    {
        $this->outputPath = $this->option('output') ?? storage_path('sqlmap');
        $this->baseUrl = $this->option('url') ?? config('app.url', 'http://127.0.0.1:8000');

        $this->info("Generating SQLMap files...");
        $this->info("Output directory: {$this->outputPath}");
        $this->info("Base URL: {$this->baseUrl}");

        // Create output directory if it doesn't exist
        if (!File::exists($this->outputPath)) {
            File::makeDirectory($this->outputPath, 0755, true);
        }

        if (! $this->router->getRoutes()->count()) {
            return $this->components->error("Your application doesn't have any routes.");
        }

        if (empty($routes = $this->getRoutes())) {
            return $this->components->error("Your application doesn't have any routes matching the given criteria.");
        }

        $generatedFiles = 0;
        $skippedRoutes = 0;

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
                $this->warn("Skipped route {$route['uri']}: " . $e->getMessage());
                $skippedRoutes++;
            }
        }

        $this->info("Generated {$generatedFiles} SQLMap files");
        $this->info("Skipped {$skippedRoutes} routes");

        // Generate run script
        $this->generateRunScript();

        return 0;
    }

    protected function getOptions()
    {
        return array_merge(parent::getOptions(), [
            [
                'output',
                'o',
                \Symfony\Component\Console\Input\InputOption::VALUE_OPTIONAL,
                'Output directory for SQLMap files'
            ],
            [
                'url',
                'u',
                \Symfony\Component\Console\Input\InputOption::VALUE_OPTIONAL,
                'Base URL for the application'
            ],
        ]);
    }

    private function analyzeRoute($route)
    {
        $methods = is_string($route['method']) ? explode('|', $route['method']) : [$route['method']];
        $methods = array_filter($methods, fn($m) => in_array($m, ['GET', 'POST', 'PUT', 'PATCH', 'DELETE']));

        if (empty($methods)) {
            return null;
        }

        $uri = $route['domain'] ? ($route['domain'] . '/' . ltrim($route['uri'], '/')) : $route['uri'];
        $action = $route['action'] ?? null;

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
            'name' => $route['name'] ?? null,
        ];
    }

    private function extractRouteParameters($uri)
    {
        preg_match_all('/\{([^}]+)\}/', $uri, $matches);
        return array_map(function ($param) {
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
                        } elseif ($reflectionClass->isSubclassOf(Request::class) || $typeName === Request::class) {
                            // For basic Request, we'll add common parameters
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
            }
        } catch (\Throwable $e) {
            // If we can't instantiate, try to parse the rules method statically
            if ($reflectionClass->hasMethod('rules')) {
                $rulesMethod = $reflectionClass->getMethod('rules');
                $rulesMethod->setAccessible(true);

                // This is a basic attempt - might not work for complex rules
                $parameters = ['email', 'password', 'name', 'id']; // Common fallback
            }
        }

        return $parameters;
    }

    private function shouldTestRoute($routeInfo)
    {
        $uri = $routeInfo['uri'];

        // Skip certain routes that shouldn't be tested
        $skipPatterns = [
            'api/documentation',
            'telescope',
            'horizon',
            'debugbar',
            '_ignition',
            'livewire',
        ];

        foreach ($skipPatterns as $pattern) {
            if (str_contains($uri, $pattern)) {
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

            $this->line("Generated: {$filename}");
        }
    }

    private function generateFilename($routeInfo, $method)
    {
        $uri = str_replace(['/', '{', '}', '?'], ['_', '', '', ''], $routeInfo['uri']);
        $uri = trim($uri, '_');
        $name = $routeInfo['name'] ? str_replace('.', '_', $routeInfo['name']) : $uri;

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
            $params[] = $param . '=' . $this->getTestValueForParameter($param);
        }

        if (!empty($params)) {
            $url .= '?' . implode('&', $params);
        }

        return "GET {$url} HTTP/1.1\r\n" .
               "Host: " . parse_url($this->baseUrl, PHP_URL_HOST) . "\r\n" .
               "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36\r\n" .
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

        $isJson = str_contains($routeInfo['uri'], 'api/') ||
                  str_contains($routeInfo['action'] ?? '', 'Api\\');

        if ($isJson) {
            $body = json_encode($params);
            $contentType = 'application/json';
        } else {
            $body = http_build_query($params);
            $contentType = 'application/x-www-form-urlencoded';
        }

        return "{$method} {$url} HTTP/1.1\r\n" .
               "Host: " . parse_url($this->baseUrl, PHP_URL_HOST) . "\r\n" .
               "Content-Type: {$contentType}\r\n" .
               "Content-Length: " . strlen($body) . "\r\n" .
               "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36\r\n" .
               "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8\r\n" .
               "Accept-Language: en-US,en;q=0.5\r\n" .
               "Accept-Encoding: gzip, deflate\r\n" .
               "Connection: keep-alive\r\n" .
               "Cache-Control: no-cache\r\n\r\n" .
               $body;
    }

    private function getTestValueForParameter($param)
    {
        // Generate test values based on parameter names
        $testValues = [
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
        ];

        return $testValues[$param] ?? 'test_value';
    }

    private function generateRunScript()
    {
        $scriptContent = "#!/bin/bash\n\n";
        $scriptContent .= "# SQLMap test runner for Laravel application\n";
        $scriptContent .= "# Generated on " . date('Y-m-d H:i:s') . "\n\n";
        $scriptContent .= "SQLMAP_PATH=\"sqlmap\"\n";
        $scriptContent .= "OUTPUT_DIR=\"./results\"\n\n";
        $scriptContent .= "mkdir -p \$OUTPUT_DIR\n\n";

        $files = File::glob($this->outputPath . '/*.http');

        foreach ($files as $file) {
            $filename = basename($file);
            $baseName = pathinfo($filename, PATHINFO_FILENAME);

            $scriptContent .= "echo \"Testing {$filename}...\"\n";
            $scriptContent .= "\$SQLMAP_PATH -r \"{$filename}\" --batch --level=3 --risk=2 ";
            $scriptContent .= "--output-dir=\"\$OUTPUT_DIR/{$baseName}\" --flush-session --fresh-queries\n\n";
        }

        $scriptPath = $this->outputPath . '/run_sqlmap.sh';
        File::put($scriptPath, $scriptContent);
        chmod($scriptPath, 0755);

        $this->info("Generated run script: {$scriptPath}");
    }
}

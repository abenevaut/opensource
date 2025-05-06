<?php

namespace App\Console\Commands;

use Illuminate\Foundation\Console\RouteListCommand;
use Symfony\Component\Console\Attribute\AsCommand;
use ReflectionClass;
use ReflectionMethod;

#[AsCommand(name: 'sqlmap:describe-routes')]
class DescribeRoutes extends RouteListCommand
{
    protected $name = 'sqlmap:describe-routes';
    protected $description = 'Generate a list of routes for SQLMap to target';

    public function handle()
    {
        if (! $this->output->isVeryVerbose()) {
            $this->router->flushMiddlewareGroups();
        }

        if (! $this->router->getRoutes()->count()) {
            return $this->components->error("Your application doesn't have any routes.");
        }

        if (empty($routes = $this->getRoutes())) {
            return $this->components->error("Your application doesn't have any routes matching the given criteria.");
        }

        $routes = collect($routes)
                ->map(function ($route) {
//                    try { // @todo remove me
                        $method = $route['method'] == 'GET|HEAD|POST|PUT|PATCH|DELETE|OPTIONS' ? 'ANY' : $route['method'];
                        $uri = $route['domain'] ? ($route['domain'].'/'.ltrim($route['uri'], '/')) : $route['uri'];
                        $action = $route['action'] ?? 'N/A';
                        $parameters = $this->getExpectedParameters($action, $method);

                        return [
                            'method' => explode('|', $method),
                            'uri' => $uri,
                            'action' => $action,
                            'parameters' => $parameters,
                        ];
//                    }
//                    catch (\Throwable $e) {
//                        return null;
//                    }
                })
                ->filter(fn ($route) => $route !== null);

        dd($routes);
    }

    private function getExpectedParameters($controller, $requestMethod)
    {
        $parameters = [];

        if ($controller !== 'N/A' && $controller !== 'Closure') {
            [$class, $method] = explode('@', $controller);
            $reflectionMethod = new ReflectionMethod($class, $method);

            foreach ($reflectionMethod->getParameters() as $parameter) {
                $parameterType = $parameter->getType();

                if ($parameterType && !$parameterType->isBuiltin()) {
                    $reflectionClass = new ReflectionClass($parameterType->getName());

                    if ($reflectionClass->isSubclassOf('\Illuminate\Foundation\Http\FormRequest')) {
                        /** @var \Illuminate\Foundation\Http\FormRequest $formRequestInstance */
                        $formRequestInstance = $reflectionClass->newInstanceWithoutConstructor();

                        /** @var \Illuminate\Foundation\Http\FormRequest $formRequestInstance */
                        $this->mockUserMethod($formRequestInstance);


                        $parameters = array_merge($parameters, array_keys($formRequestInstance->rules()));
                    }
                }
            }
        }

        // @todo remove me at the end
//        if (empty($parameters)) {
//            throw new \Exception('No parameters found');
//        }

        return $parameters;
    }

    private function mockUserMethod(\Illuminate\Foundation\Http\FormRequest $formRequestInstance)
    {
        $reflectionClass = new ReflectionClass($formRequestInstance);
        if ($reflectionClass->hasMethod('user')) {
            $userMethod = $reflectionClass->getMethod('user');
            $userMethod->setAccessible(true);

            $closure = function() {
                return new \App\Domain\Users\Users\User(['id' => 1]);
            };

            $userMethod->invokeArgs($formRequestInstance, []);

            // Bind the closure to the form request instance
            $boundClosure = $closure->bindTo($formRequestInstance, get_class($formRequestInstance));
            $userMethod->invoke($formRequestInstance, $boundClosure);

            // Ensure the user method returns the mock user
            $formRequestInstance->user = $boundClosure;

            dd($formRequestInstance->user()->id);
        }
    }
}

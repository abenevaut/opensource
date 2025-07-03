<?php

namespace LaravelSqlMap\Tests\Unit;

use LaravelSqlMap\Tests\TestCase;
use LaravelSqlMap\SqlMapServiceProvider;
use Illuminate\Support\Facades\File;

class SqlMapServiceProviderTest extends TestCase
{
    public function test_service_provider_registers_config()
    {
        $this->assertNotNull(config('sqlmap'));
        $this->assertIsArray(config('sqlmap'));
    }

    public function test_service_provider_registers_command()
    {
        $commands = $this->app['Illuminate\Contracts\Console\Kernel']->all();

        $this->assertArrayHasKey('sqlmap:generate', $commands);
    }

    public function test_config_has_expected_keys()
    {
        $config = config('sqlmap');

        $expectedKeys = [
            'enabled',
            'output_path',
            'base_url',
            'disable_csrf',
            'bypassed_user_agents',
            'skip_routes',
            'test_parameters',
            'sqlmap_options'
        ];

        foreach ($expectedKeys as $key) {
            $this->assertArrayHasKey($key, $config, "Config missing key: {$key}");
        }
    }

    public function test_default_config_values()
    {
        $config = config('sqlmap');

        // Check actual default values from the test environment
        $this->assertTrue($config['enabled']); // Set to true in TestCase
        $this->assertTrue($config['disable_csrf']); // Set to true in TestCase
        $this->assertIsArray($config['bypassed_user_agents']);
        $this->assertIsArray($config['skip_routes']);
        $this->assertIsArray($config['test_parameters']);
        $this->assertIsArray($config['sqlmap_options']);
    }

    public function test_bypassed_user_agents_contains_sqlmap()
    {
        $userAgents = config('sqlmap.bypassed_user_agents');

        $hasSqlMapAgent = false;
        foreach ($userAgents as $agent) {
            if (str_contains(strtolower($agent), 'sqlmap')) {
                $hasSqlMapAgent = true;
                break;
            }
        }

        $this->assertTrue($hasSqlMapAgent, 'Config should include SQLMap user agents');
    }

    public function test_skip_routes_contains_common_exclusions()
    {
        $skipRoutes = config('sqlmap.skip_routes');

        $expectedPatterns = ['telescope', 'horizon', 'debugbar'];

        foreach ($expectedPatterns as $pattern) {
            $this->assertContains($pattern, $skipRoutes, "Should skip {$pattern} routes");
        }
    }

    public function test_test_parameters_has_common_fields()
    {
        $testParams = config('sqlmap.test_parameters');

        $expectedParams = ['id', 'email', 'password', 'name'];

        foreach ($expectedParams as $param) {
            $this->assertArrayHasKey($param, $testParams, "Should have test value for {$param}");
        }
    }

    public function test_sqlmap_options_has_reasonable_defaults()
    {
        $options = config('sqlmap.sqlmap_options');

        $this->assertArrayHasKey('level', $options);
        $this->assertArrayHasKey('risk', $options);
        $this->assertArrayHasKey('batch', $options);

        // Test reasonable values
        $this->assertGreaterThanOrEqual(1, $options['level']);
        $this->assertLessThanOrEqual(5, $options['level']);
        $this->assertGreaterThanOrEqual(1, $options['risk']);
        $this->assertLessThanOrEqual(3, $options['risk']);
        $this->assertTrue($options['batch']);
    }
}

<?php

declare(strict_types=1);

namespace RH\AdminUtils\Tests\Pest;

use RH\AdminUtils\Vendor\Symfony\Component\VarDumper\Cloner\VarCloner;
use RH\AdminUtils\Vendor\Symfony\Component\VarDumper\Dumper\CliDumper;

use function RH\AdminUtils\dump;

/**
 * `verify:prefixed` inspects the prefixed files; this exercises them, so a reference
 * Strauss could not rewrite fails here instead of in production.
 */
class PrefixedDependenciesTest extends IntegrationTestCase
{
    public function test_the_prefixed_var_dumper_runs(): void
    {
        /** Instantiating is not enough: casters resolve classes lazily */
        $output = (new CliDumper())->dump((new VarCloner())->cloneVar(['answer' => 42]), true);

        $this->assertStringContainsString('answer', (string) $output);
        $this->assertStringContainsString('42', (string) $output);
    }

    public function test_the_unprefixed_namespace_is_not_registered(): void
    {
        $this->assertFalse(
            class_exists('Symfony\Component\VarDumper\Cloner\VarCloner', false),
            'The unprefixed var-dumper leaked into the global namespace',
        );
    }

    public function test_the_namespaced_helper_routes_to_the_prefixed_copy(): void
    {
        /** Output is not asserted: under CLI, VarDumper bypasses ob_start() */
        $this->assertSame('hello', dump('hello'));
    }

    public function test_the_global_helpers_are_available(): void
    {
        /** Here require-dev's unprefixed copy wins; only their presence matters */
        $this->assertTrue(function_exists('dd'));
        $this->assertSame('hello', \dump('hello'));
    }

    public function test_the_plugin_serves_the_globals_when_nothing_else_does(): void
    {
        /** Out of process: in-suite the unprefixed copy always loads first */
        $output = $this->runWithoutDevAutoloader(
            '$returned = dump("hello");'
                . ' echo "###", (new ReflectionFunction("dump"))->getFileName(), "###", $returned, "###";',
        );

        /** dump() writes to STDOUT ahead of the echo, hence the markers */
        preg_match('/###(.*?)###(.*?)###/s', $output, $matches);

        $this->assertNotEmpty($matches, "Unexpected output: $output");
        $this->assertSame(dirname(__DIR__, 2) . '/src/global-functions.php', $matches[1]);
        $this->assertSame('hello', $matches[2]);
        $this->assertStringContainsString('"hello"', $output);
    }

    public function test_the_opt_out_constant_suppresses_the_globals(): void
    {
        $output = $this->runWithoutDevAutoloader(
            'echo function_exists("dump") ? "defined" : "absent";',
            'define("RHAU_GLOBAL_DEBUG_FUNCTIONS", false);',
        );

        $this->assertSame('absent', trim($output));
    }

    public function test_the_mbstring_polyfill_kept_its_global_function_names(): void
    {
        /** A polyfill must keep the real global names or it polyfills nothing */
        $this->assertTrue(function_exists('mb_strlen'));
        $this->assertFalse(function_exists('rhau_vendor_mb_strlen'));
    }

    /**
     * Boot just the prefixed dependencies and the helpers in a subprocess,
     * mirroring the guard in the main plugin file.
     */
    private function runWithoutDevAutoloader(string $assertion, string $before = ''): string
    {
        $dir = dirname(__DIR__, 2);

        $script = sprintf(
            '%s require "%s/vendor-prefixed/autoload.php"; require "%s/src/functions.php";'
                . ' if (!defined("RHAU_GLOBAL_DEBUG_FUNCTIONS") || RHAU_GLOBAL_DEBUG_FUNCTIONS)'
                . ' { require "%s/src/global-functions.php"; } %s',
            $before,
            $dir,
            $dir,
            $dir,
            $assertion,
        );

        return (string) shell_exec(sprintf('php -r %s 2>&1', escapeshellarg($script)));
    }
}

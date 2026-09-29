<?php

declare(strict_types=1);

namespace RH\AdminUtils\Tests\Pest;

use RH\AdminUtils\Vendor\Symfony\Component\VarDumper\Cloner\VarCloner;
use RH\AdminUtils\Vendor\Symfony\Component\VarDumper\Dumper\CliDumper;

use function RH\AdminUtils\dump;

/**
 * The prefixed dependencies are build output committed to the repo, and prefixing
 * can miss symbols a static rewriter cannot resolve. `config/cli/cli.js verify:prefixed`
 * catches that by inspecting the files; this exercises them instead, so a broken
 * reference surfaces here rather than in production.
 */
class PrefixedDependenciesTest extends IntegrationTestCase
{
    public function test_dependencies_are_loaded_under_the_prefixed_namespace(): void
    {
        $this->assertTrue(class_exists(VarCloner::class));
        $this->assertTrue(function_exists('rhau_vendor_dump'));
    }

    public function test_the_unprefixed_namespace_is_not_registered(): void
    {
        $this->assertFalse(
            class_exists('Symfony\Component\VarDumper\Cloner\VarCloner', false),
            'The unprefixed var-dumper leaked into the global namespace',
        );
    }

    public function test_var_dumper_actually_runs(): void
    {
        /** Instantiating is not enough: the casters resolve classes lazily */
        $data = (new VarCloner())->cloneVar(['answer' => 42]);
        $output = (new CliDumper())->dump($data, true);

        $this->assertIsString($output);
        $this->assertStringContainsString('answer', $output);
        $this->assertStringContainsString('42', $output);
    }

    public function test_the_namespaced_dump_helper_delegates_to_the_prefixed_copy(): void
    {
        /**
         * Only the return value is asserted: under the CLI SAPI VarDumper writes
         * straight to STDOUT, so ob_start() would capture nothing. That the call
         * resolves and returns at all is what proves the delegation.
         */
        $this->assertSame('hello', dump('hello'));
    }

    public function test_global_debug_helpers_are_available(): void
    {
        /**
         * Which copy wins depends on the site: if the root composer project already
         * loaded an unprefixed var-dumper before plugins load — as it does here, via
         * require-dev — that one is kept and the plugin does not redeclare it. What
         * matters either way is that the globals exist.
         */
        $this->assertTrue(function_exists('dump'));
        $this->assertTrue(function_exists('dd'));
        $this->assertSame('hello', dump('hello'));
    }

    public function test_the_plugin_serves_the_globals_when_nothing_else_does(): void
    {
        /**
         * Run out of process without the dev autoloader, which is the only way to
         * reach the branch that actually declares them — in this suite symfony's own
         * unprefixed copy is always loaded first.
         */
        $pluginDir = dirname(__DIR__, 2);

        $script = sprintf(
            'require %s; require %s; require %s;'
                . ' $returned = dump("hello");'
                . ' echo "###", (new ReflectionFunction("dump"))->getFileName(), "###", $returned, "###";',
            var_export("$pluginDir/vendor-prefixed/autoload.php", true),
            var_export("$pluginDir/src/functions.php", true),
            var_export("$pluginDir/src/global-functions.php", true),
        );

        /** dump() writes to STDOUT ahead of the echo, so pick the markers back out */
        $output = (string) shell_exec(sprintf('php -r %s 2>&1', escapeshellarg($script)));
        preg_match('/###(.*?)###(.*?)###/s', $output, $matches);

        $this->assertNotEmpty($matches, "Unexpected subprocess output: $output");
        $this->assertSame("$pluginDir/src/global-functions.php", $matches[1]);
        $this->assertSame('hello', $matches[2]);

        /** The rendered dump proves it reached the prefixed var-dumper, not just a stub */
        $this->assertStringContainsString('"hello"', $output);
    }

    public function test_the_opt_out_constant_suppresses_the_globals(): void
    {
        $pluginDir = dirname(__DIR__, 2);

        /** Mirrors the guard in the main plugin file */
        $script = sprintf(
            'define("RHAU_GLOBAL_DEBUG_FUNCTIONS", false);'
                . ' require %s; require %s;'
                . ' if (!defined("RHAU_GLOBAL_DEBUG_FUNCTIONS") || RHAU_GLOBAL_DEBUG_FUNCTIONS) { require %s; }'
                . ' echo function_exists("dump") ? "defined" : "absent";',
            var_export("$pluginDir/vendor-prefixed/autoload.php", true),
            var_export("$pluginDir/src/functions.php", true),
            var_export("$pluginDir/src/global-functions.php", true),
        );

        $output = trim((string) shell_exec(sprintf('php -r %s 2>&1', escapeshellarg($script))));

        $this->assertSame('absent', $output);
    }

    public function test_the_mbstring_polyfill_kept_its_global_function_names(): void
    {
        /**
         * A polyfill must define the real global names or it polyfills nothing.
         * Strauss prefixes the backing class but leaves these alone.
         */
        $this->assertTrue(function_exists('mb_strlen'));
        $this->assertFalse(function_exists('rhau_vendor_mb_strlen'));
    }
}

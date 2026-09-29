<?php

declare(strict_types=1);

namespace RH\AdminUtils\Tests\Pest;

use RH\AdminUtils\Vendor\Symfony\Component\VarDumper\Cloner\VarCloner;
use RH\AdminUtils\Vendor\Symfony\Component\VarDumper\Dumper\CliDumper;

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

    public function test_the_global_debug_helpers_are_available(): void
    {
        /** Here require-dev's own copy wins; only their presence matters */
        $this->assertTrue(function_exists('dd'));
        $this->assertSame('hello', dump('hello'));
    }

    public function test_the_prefixed_copy_serves_the_globals_unprefixed(): void
    {
        /** Out of process: in-suite require-dev's copy always loads first */
        $output = $this->runWithoutDevAutoloader(
            '$returned = dump("hello");'
                . ' echo "###", (new ReflectionFunction("dump"))->getFileName(), "###", $returned, "###";',
        );

        /** dump() writes to STDOUT ahead of the echo, hence the markers */
        preg_match('/###(.*?)###(.*?)###/s', $output, $matches);

        $this->assertNotEmpty($matches, "Unexpected output: $output");
        $this->assertStringStartsWith(dirname(__DIR__, 2) . '/vendor-prefixed/', $matches[1]);
        $this->assertSame('hello', $matches[2]);
        $this->assertStringContainsString('"hello"', $output);
    }

    public function test_the_shared_global_functions_stay_unprefixed(): void
    {
        /**
         * `function_prefix: false` keeps these under their real names, so the polyfill
         * still polyfills and other code can call them. Each is function_exists-guarded
         * in its own file, so a second copy on the site cannot collide.
         */
        $output = $this->runWithoutDevAutoloader(
            'foreach (["dump", "dd", "trigger_deprecation", "mb_strlen"] as $f)'
                . ' { echo $f, "=", function_exists($f) ? "y" : "n", ";"; }'
                . ' echo "prefixed=", function_exists("rhau_vendor_dump") ? "y" : "n";',
        );

        $this->assertStringContainsString('dump=y;dd=y;trigger_deprecation=y;mb_strlen=y;', $output);
        $this->assertStringContainsString('prefixed=n', $output);
    }

    /** Boot only the prefixed dependencies, without the site's own copies */
    private function runWithoutDevAutoloader(string $assertion): string
    {
        $script = sprintf(
            'require "%s/vendor-prefixed/autoload.php"; %s',
            dirname(__DIR__, 2),
            $assertion,
        );

        return (string) shell_exec(sprintf('php -r %s 2>&1', escapeshellarg($script)));
    }
}

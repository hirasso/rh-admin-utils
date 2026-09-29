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
